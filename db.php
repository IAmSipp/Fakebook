<?php
    // DATABASE INFORMATION
    $host = 'localhost';
    $db = 'user_db';
    $user = 'root';
    $pass = '';

    // CONNECT TO DATABASE
    $conn = new mysqli($host, $user, $pass, $db);

    // CHECK CONNECTION
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>



