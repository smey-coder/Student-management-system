<?php
session_start();
require_once "database.php";
 // ✅ make sure connection exists

// Register
if (isset($_POST['register'])) {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = 'Email is already registered!';
        $_SESSION['activate_form'] = 'register';
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, dateCreated) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();

        $_SESSION['register_success'] = 'Registration successful! Please login.';
        $_SESSION['activate_form'] = 'login';
    }

    $stmt->close();
    header("Location: register_for_admin.php"); // ✅ redirect to login page
    exit();
}
?>
