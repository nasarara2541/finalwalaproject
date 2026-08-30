<?php

require_once '../db.php';
header('Content-Type: application/json');

// This manages the same Interface_User table every login in this app
// authenticates against, so it needs the same admin gate as this app's own
// user-management screen (admin_users.php), not just "logged in".
if (empty($_SESSION['emp_is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_roles') {
        // Distinct roles currently in use, for the filter checkbox list
        $stmt = $conn->query("SELECT DISTINCT User_desc FROM Interface_User WHERE User_desc IS NOT NULL AND LTRIM(RTRIM(User_desc)) <> '' ORDER BY User_desc ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($roles);
        exit;
    }

    if ($method === 'GET') {
        // GET USERS - supports name search, ID search, role filter, and paging
        $nameQ  = trim($_GET['name'] ?? '');
        $idQ    = trim($_GET['id'] ?? '');
        $roles  = isset($_GET['roles']) ? explode(',', $_GET['roles']) : [];
        $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
        $limit  = 100;

        // Sentinel from the frontend meaning "no roles checked" - should
        // return zero rows, not "no role filter applied at all".
        if ($roles === ['__none__']) {
            echo json_encode(['results' => [], 'hasMore' => false]);
            exit;
        }

        $where  = [];
        $params = [];

        if ($nameQ !== '') {
            $where[] = "u.User_name LIKE ?";
            $params[] = "%$nameQ%";
        }
        if ($idQ !== '') {
            $where[] = "CAST(u.User_id AS VARCHAR(50)) LIKE ?";
            $params[] = "%$idQ%";
        }
        if (count($roles) > 0) {
            $rolePlaceholders = implode(',', array_fill(0, count($roles), '?'));
            $where[] = "u.User_desc IN ($rolePlaceholders)";
            foreach ($roles as $r) { $params[] = $r; }
        }

        $whereSql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $conn->prepare("
            SELECT
                u.User_id,
                u.User_name,
                u.User_desc,
                u.Login_status
            FROM Interface_User u
            $whereSql
            ORDER BY u.User_id ASC
            OFFSET $offset ROWS FETCH NEXT " . ($limit + 1) . " ROWS ONLY
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasMore = count($users) > $limit;
        if ($hasMore) { array_pop($users); }

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user['User_id'],
                'name' => $user['User_name'],
                'role' => $user['User_desc'] ?? 'User',
                'status' => ($user['Login_status'] === 'Y') ? 'Active' : 'Disabled'
            ];
        }
        echo json_encode(['results' => $results, 'hasMore' => $hasMore]);
        exit;

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'toggle_status') {
            // TOGGLE USER STATUS
            $userId = $data['id'] ?? null;
            $newStatus = $data['status'] ?? null;

            if (!$userId || !in_array($newStatus, ['Y', 'N'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }

            if ($newStatus === 'N') {
                $checkAdminStmt = $conn->prepare("SELECT User_desc FROM Interface_User WHERE User_id = ?");
                $checkAdminStmt->execute([$userId]);
                $targetRole = strtolower(trim($checkAdminStmt->fetchColumn() ?: ''));
                
                if ($targetRole === 'admin') {
                    $countAdminsStmt = $conn->query("SELECT COUNT(*) FROM Interface_User WHERE LOWER(LTRIM(RTRIM(User_desc))) = 'admin' AND Login_status = 'Y'");
                    $adminCount = (int)$countAdminsStmt->fetchColumn();
                    
                    if ($adminCount <= 1) {
                        echo json_encode(['success' => false, 'error' => 'Cannot disable the last active admin.']);
                        exit;
                    }
                }
            }
            $stmt = $conn->prepare("UPDATE Interface_User SET Login_status = ? WHERE User_id = ?");
            $stmt->execute([$newStatus, $userId]);
            echo json_encode(['success' => true]);
            exit;

        } elseif ($action === 'save_user') {
            // SAVE USER
            if (empty($data['user_id']) || empty($data['user_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'User ID and User Name are required']);
                exit;
            }

            $isNew = $data['is_new'] ?? false;
            
            if ($isNew) {
                if (empty($data['password'])) {
                    echo json_encode(['error' => 'Password is required for new users']);
                    exit;
                }
                
                // Stored as plain text, per explicit instruction -- be aware this
                // means a user created here cannot log in through this app's real
                // login (user_login.php), which only ever matches a bcrypt hash via
                // password_verify(). Confirmed and accepted as a known tradeoff.
                $stmt = $conn->prepare("
                    INSERT INTO Interface_User (User_id, User_name, User_desc, User_password, Login_status)
                    VALUES (?, ?, ?, ?, 'Y')
                ");
                $stmt->execute([
                    $data['user_id'],
                    $data['user_name'],
                    $data['role'] ?? 'User',
                    $data['password']
                ]);
            } else {
                $newRole = strtolower(trim($data['role'] ?? 'User'));
                
                // Check if trying to demote the last admin
                $checkAdminStmt = $conn->prepare("SELECT User_desc FROM Interface_User WHERE User_id = ?");
                $checkAdminStmt->execute([$data['user_id']]);
                $targetRole = strtolower(trim($checkAdminStmt->fetchColumn() ?: ''));
                
                if ($targetRole === 'admin' && $newRole !== 'admin') {
                    $countAdminsStmt = $conn->query("SELECT COUNT(*) FROM Interface_User WHERE LOWER(LTRIM(RTRIM(User_desc))) = 'admin' AND Login_status = 'Y'");
                    $adminCount = (int)$countAdminsStmt->fetchColumn();
                    
                    if ($adminCount <= 1) {
                        echo json_encode(['success' => false, 'error' => 'Cannot change role of the last active admin.']);
                        exit;
                    }
                }
                
                if (!empty($data['password'])) {
                    // Plain text, same tradeoff as the insert path above.
                    $stmt = $conn->prepare("
                        UPDATE Interface_User
                        SET User_name = ?, User_desc = ?, User_password = ?
                        WHERE User_id = ?
                    ");
                    $stmt->execute([
                        $data['user_name'],
                        $data['role'] ?? 'User',
                        $data['password'],
                        $data['user_id']
                    ]);
                } else {
                    $stmt = $conn->prepare("
                        UPDATE Interface_User 
                        SET User_name = ?, User_desc = ?
                        WHERE User_id = ?
                    ");
                    $stmt->execute([
                        $data['user_name'],
                        $data['role'] ?? 'User',
                        $data['user_id']
                    ]);
                }
            }
            echo json_encode(['success' => true]);
            exit;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
        }
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

} catch(PDOException $e) {
    http_response_code(500);
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Violation of PRIMARY KEY') !== false) {
        echo json_encode(['error' => 'User ID already exists.']);
    } else {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

