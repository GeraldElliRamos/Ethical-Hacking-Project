<?php
// Force session cookies to be HTTP Only and restrict to SameSite
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', 1);

// Security Headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:;");

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
