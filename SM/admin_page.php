<?php
// admin_page.php
session_start();
require_once "database.php"; // include DB connection

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_page.css">
</head>
<body>

<header>
  <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?></h1>
</header>

<nav>
  <a href="admin_page.php?page=dashboard">Dashboard</a>
  <a href="admin_page.php?page=students">Manage Students</a>
  <a href="admin_page.php?page=courses">Manage Courses</a>
  <a href="admin_page.php?page=enrollments">Enrollments</a>
  <a href="logout.php">Logout</a>
</nav>

<div class="container">
  <?php
  $page = $_GET['page'] ?? 'dashboard';

  if ($page === 'dashboard') {
      echo '<div class="card"><h2>Dashboard</h2><p>Overview of the system.</p></div>';
  } elseif ($page === 'students') {
      include "students_admin.php";
  } elseif ($page === 'courses') {
      include "course_admin.php";
  } elseif ($page === 'enrollments') {
      include "enrollment_admin.php";
  } else {
      echo '<div class="card"><h2>404</h2><p>Page not found.</p></div>';
  }
  ?>
</div>

</body>
</html>
