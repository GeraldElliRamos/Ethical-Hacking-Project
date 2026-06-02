<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file
include('config.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get user input
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $full_name = htmlspecialchars($_POST['full_name']);
    $phone_number = htmlspecialchars($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check whether the username or email already exists
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $email);
    if (!$stmt->execute()) {
        die("Select failed: " . $stmt->error);
    }
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        echo "Username or Email already taken. Please try again.";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (username, password, email, full_name, phone_number, address)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssss", $username, $hashed_password, $email, $full_name, $phone_number, $address);
        if (!$stmt->execute()) {
            die("Insert failed: " . $stmt->error);
        }

        header("Location: login.html");
        exit;
    }
}
?>
