<?php
session_start();
require_once 'database.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Use prepared statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role']; // ✅ fix here

                header("Location: admin_page.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid password!";
            }
        } else {
            $_SESSION['login_error'] = "Email not found!";
        }

        $stmt->close();
    } else {
        $_SESSION['login_error'] = "All fields are required!";
    }

    $_SESSION['activate_form'] = 'login';
    header("Location: index.php");
    exit();
}
?>
