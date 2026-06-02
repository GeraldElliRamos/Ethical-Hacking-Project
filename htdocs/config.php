<?php
$host = '127.0.0.1';  // Local Laragon database host
$dbname = 'summit_gear';  // Local database name
$username = 'root';  // Laragon default database username
$password = '';  // Laragon default database password

// Create MySQLi connection for database access
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Could not connect to the database: " . $conn->connect_error);
}
?>
