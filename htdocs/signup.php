<?php
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

    // SQL query to check if the username or email already exists
    $sql = "SELECT * FROM users WHERE username = :username OR email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username, ':email' => $email]);

    if ($stmt->rowCount() > 0) {
        // Username or email already exists
        echo "Username or Email already taken. Please try again.";
    } else {
        // SQL query to insert new user
        $sql = "INSERT INTO users (username, password, email, full_name, phone_number, address)
                VALUES (:username, :password, :email, :full_name, :phone_number, :address)";
        
        // Prepare and execute the query
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone_number' => $phone_number,
            ':address' => $address
        ]);

        echo "Account successfully created! <a href='login.html'>Login here</a>";
    }
}
?>
