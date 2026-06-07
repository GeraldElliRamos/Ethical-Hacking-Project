<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        die('Please enter your username/email and password.');
    }

    $sql = "SELECT id, username, email, password FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $identifier, $identifier);
    if (!$stmt->execute()) {
        die("Select failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    if (!$user) {
        sleep(2); // Slow down brute force attempts
        die('No account found for that username or email.');
    }

    if (!password_verify($password, $user['password'])) {
        sleep(2); // Slow down brute force attempts
        die('Incorrect password.');
    }

    // Prevent Session Fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];

    header('Location: profile.php');
    exit;
}

header('Location: login.html');
exit;
