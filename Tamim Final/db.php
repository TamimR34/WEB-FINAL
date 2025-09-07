<?php

$DB_HOST = 'sql100.infinityfree.com';
$DB_USER = 'if0_39788671';      
$DB_PASS = 'TamimAlasmar04';          
$DB_NAME = 'if0_39788671_shoplite';


$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);


if ($conn->connect_errno) {
    die('Database connection failed: ' . $conn->connect_error);
}


$conn->set_charset('utf8mb4');
?>
