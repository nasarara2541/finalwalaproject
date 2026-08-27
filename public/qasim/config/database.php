<?php
// Qasim's original getDbConnection() hardcoded his own PC's SQL Server
// (DESKTOP-Q2553V3\SQLEXPRESS) and read $_SESSION['selected_db'], a key this
// app never sets (this app's real login flow sets 'active_db' to the literal
// database name). His Models call sqlsrv_query($conn, ...) directly -- same
// procedural sqlsrv style this whole app already uses -- so this just makes
// getDbConnection() return this app's real, already-working connection
// (session-aware Water/MedStock switching, login-gated, UTF-8) instead of a
// second, parallel one.
function getDbConnection()
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }
    require __DIR__ . '/../../includes/db.php';
    return $conn;
}
