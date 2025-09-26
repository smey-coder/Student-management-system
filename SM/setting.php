<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: setting.php");
    exit();
}
require_once 'database.php'; // Include your database connection

// Example: user settings
$name = $_SESSION['name'] ?? '';
$email = $_SESSION['email'] ?? '';
$role = $_SESSION['role'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normally update settings in database
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];

    // Example: just update session for demo
    $_SESSION['name'] = $new_name;
    $_SESSION['email'] = $new_email;

    $message = "Settings updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="user_page1.css">
</head>
<body>
<div class="dashboard-container">

    <div class="main-content">
        <div class="welcome-card">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Settings Icon">
            <div class="khmer-title">User Settings</div>
            <div class="subtitle">Update your profile and preferences</div>
        </div>

        <?php if (isset($message)) : ?>
            <div class="alert-box"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="notice-board">
            <h3><i class="ri-settings-3-fill"></i> Update Settings</h3>
            <form method="POST">
                <table>
                    <tr>
                        <th>Name</th>
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?php echo htmlspecialchars($role); ?></td>
                    </tr>
                </table>
                <br>
                <button type="submit" style="padding:8px 16px; border:none; background:#2563eb; color:#fff; border-radius:6px; cursor:pointer;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<div class="main-content">
  <div class="dashboard-header">
    <div class="logo">NORTON STUDENT</div>
    <div class="profile">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Picture" class="img">
      <select name="role" onchange="handleProfileOption(this.value)">
        <option value="">Hello, <?php echo htmlspecialchars($name); ?></option>
        <option value="profile">Profile</option>
        <option value="setting">Settings</option>
        <option value="logout">Logout</option>
      </select>
    </div>
  </div>

  <div class="welcome-card">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Welcome Icon">
    <div class="khmer-title">Dashboard</div>
    <div class="subtitle">Welcome to Student Management System</div>
  </div>