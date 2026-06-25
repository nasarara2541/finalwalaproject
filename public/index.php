<?php

$env = parse_ini_file(__DIR__ . '/../.env');
$conn = sqlsrv_connect($env['DB_SERVER'], [
    "Database" => $env['DB_NAME'],
    "UID" => $env['DB_USER'],
    "PWD" => $env['DB_PASSWORD'],
    "TrustServerCertificate" => true
]);
?>

<?php
if ($conn === false) {
    $errors = sqlsrv_errors();
    echo $errors[0]['message'];
    die();
}
echo "<h3 style='color:green'>Connection Successful</h2>";

//insert dummy data into table for testing
$sql    = "INSERT INTO Manufacture (M_Name, City) VALUES (?, ?)";
$params = ["Margalla 3M Industries", "Islamabad"];
$stmt   = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo "<h3>Insert Failed</h3>";
} else {
    echo "<h3>Insert Success</h3>";
}
sqlsrv_close($conn);
?>