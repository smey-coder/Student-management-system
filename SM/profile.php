<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: profile.php");
    exit();
}
require_once 'database.php'; // Include your database connection

// Sample user info (replace with database query if needed)
$name = $_SESSION['name'] ?? '';
$email = $_SESSION['email']?? '';
$role = $_SESSION['role']?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="user_page1.css">
</head>
<body>
<div class="dashboard-container">

    <div class="main-content">
        <div class="welcome-card">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Icon">
            <div class="khmer-title">User Profile</div>
            <div class="subtitle">Manage your personal information</div>
        </div>

        <div class="alert-box">
            ⚠️ Make sure your information is up to date!
        </div>

        <div class="notice-board">
            <h3><i class="ri-user-fill"></i> Profile Information</h3>
            <table>
                <tr><th>Name</th><td><?php echo htmlspecialchars($name); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($email); ?></td></tr>
                <tr><th>Role</th><td><?php echo htmlspecialchars($role); ?></td></tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
