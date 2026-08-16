<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Every api/*.php file requires this one file to get $conn, so it's the
// single choke point that keeps unauthenticated requests out even if
// someone calls an API endpoint directly instead of going through a
// logged-in screen. user_login.php itself needs a connection before a
// session exists, so it sets $SKIP_LOGIN_CHECK to bypass just this check.
if (empty($SKIP_LOGIN_CHECK) && empty($_SESSION['emp_user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Relative to this file (public/includes/db.php), not a hardcoded absolute
// path — otherwise this breaks the moment the app is copied to a PC where
// XAMPP isn't installed at this exact same drive/folder.
$env = parse_ini_file(__DIR__ . '/../../.env');
if ($env === false) {
    http_response_code(500);
    echo json_encode(['error' => '.env file not found — expected one level above the public/ folder']);
    exit;
}

$activeDb = $_SESSION['active_db'] ?? $env['DB_NAME_WATER'];

// A short LoginTimeout makes a misconfigured/unreachable DB_SERVER (wrong
// IP, SQL Server not listening for remote connections, firewall blocking
// the port) fail fast with a clear error in a few seconds, instead of the
// page silently hanging on "Loading…" for a long time before the driver's
// default timeout finally gives up.
$conn = sqlsrv_connect($env['DB_SERVER'], [
    "Database"               => $activeDb,
    "UID"                    => $env['DB_USER'],
    "PWD"                    => $env['DB_PASSWORD'],
    "TrustServerCertificate" => true,
    "CharacterSet"           => "UTF-8",
    "LoginTimeout"           => 5,
]);
if ($conn === false) {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message'] ?? 'Database connection failed — check DB_SERVER in .env and that SQL Server accepts remote connections']);
    exit;
}
