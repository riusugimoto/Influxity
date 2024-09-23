<?php

function db_log_in() {
    $host = '127.0.0.1';  // or 'localhost'
    $username = 'root';   // your MySQL username, usually 'root'
    $password = '';       // your MySQL password, often blank by default
    $dbname = 'my_database';  // your MySQL database name

    // Connect to the database using mysqli
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
