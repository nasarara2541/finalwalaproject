<?php
require_once __DIR__ . '/database.php';

// Client table already exists in this app's real schema with exactly the
// columns Qasim's version expects (Client_name, Address, City, Email,
// Contact_no) -- only the connection source changed, the query is his own.
function getClientInfo()
{
    static $client = null;
    if ($client !== null) return $client;

    $conn = getDbConnection();
    $stmt = sqlsrv_query($conn, "SELECT TOP 1 Client_name, Address, City, Email, Contact_no FROM Client");
    if ($stmt === false) return null;

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $client = $row ?: null;
    return $client;
}
