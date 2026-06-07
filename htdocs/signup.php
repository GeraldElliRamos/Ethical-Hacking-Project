<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection file
include('config.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get user input
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    // Basic Validation
    if (empty($username) || empty($email) || empty($password)) {
        die("Username, Email, and Password are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

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
