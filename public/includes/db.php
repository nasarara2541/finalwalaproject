<?php
$env = parse_ini_file('C:/xampp/htdocs/Margalla3M-SellH2O/.env');
$conn = sqlsrv_connect($env['DB_SERVER'], [
    "Database"             => $env['DB_NAME'],
    "UID"                  => $env['DB_USER'],
    "PWD"                  => $env['DB_PASSWORD'],
    "TrustServerCertificate" => true,
]);
if ($conn === false) {
    http_response_code(500);
    echo json_encode(['error' => sqlsrv_errors()[0]['message']]);
    exit;
}
