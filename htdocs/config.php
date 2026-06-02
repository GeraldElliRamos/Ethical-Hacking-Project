<?php
$host = 'sql311.infinityfree.com';  // Database host
$dbname = 'if0_38857228_SummitGear';  // Database name
$username = 'if0_38857228';  // Database username
$password = 'Ninjago108';  // Database password

// Create PDO instance for database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
