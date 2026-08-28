<?php
// Zeeshan's original db.php had two real problems, not just an unreachable
// server like Anoosha's: (1) DB_NAME_WATER/DB_NAME_MEDSTOCK were swapped
// relative to this project's actual convention (his had WATER=MargallaTesting,
// MEDSTOCK=MargallaProd -- backwards), and (2) it read $_SESSION['database_name'],
// a key this app never sets (this app's real login flow sets 'active_db' to
// the literal database name). His API files all use PDO ($conn->prepare(),
// ->query(), PDO::FETCH_ASSOC), not this app's usual sqlsrv_* calls, so this
// keeps that same PDO shape -- just pointed at the real server/credentials
// and this app's real session-driven active database, with the same login
// gate every other API in this app enforces via includes/db.php.
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['emp_user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$env = parse_ini_file(__DIR__ . '/../../.env');
if ($env === false) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => '.env file not found — expected one level above the public/ folder']);
    exit;
}
$serverName   = $env['DB_SERVER'] ?? '';
$dbUser       = $env['DB_USER'] ?? '';
$dbPass       = $env['DB_PASSWORD'] ?? '';
$databaseName = $_SESSION['active_db'] ?? ($env['DB_NAME_WATER'] ?? '');

try {
    $dsn = "sqlsrv:Server=$serverName;Database=$databaseName;Encrypt=false;TrustServerCertificate=true";
    $conn = empty($dbUser) ? new PDO($dsn, null, null) : new PDO($dsn, $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database Connection Failed', 'details' => $e->getMessage()]);
    exit;
}
