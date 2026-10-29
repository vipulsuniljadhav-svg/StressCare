<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "stress_management";
$port = 3307;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli(
        $host,
        $user,
        $password,
        $database,
        $port
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    die(
        "Database connection failed.<br><br>" .
        htmlspecialchars($e->getMessage())
    );
}

?>