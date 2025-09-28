<?php
session_start();

// ✅ Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once "database.php";
require_once "function.php";

// Default page
$page = $_GET['page'] ?? 'dashboard';
$name = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin Dashboard</title>

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="image/favicon.ico">
<link rel="icon" type="image/png" href="image/favicon.png">
<link rel="icon" href="image/favicon.svg" type="image/svg+xml">

<!-- Icons + CSS -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
<link rel="stylesheet" href="user_page1.css">
</head>
<body>
<div class="dashboard-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>ADMIN PANEL</h2>
    <ul>
      <?php
      // ✅ Menu items for admin
      $menu = [
        'dashboard'  => ['Dashboard','ri-home-4-fill'],
        'students'   => ['Manage Students','ri-team-fill'],
        'courses'    => ['Manage Courses','ri-book-2-fill'],
        'teachers'   => ['Manage Teachers','ri-user-2-fill'],
        'attendance' => ['Attendance','ri-calendar-check-fill'],
        'reports'    => ['Reports','ri-bar-chart-box-fill'],
        'settings'   => ['Settings','ri-settings-3-fill'],
      ];
      foreach ($menu as $key => [$label,$icon]) {
          $active = ($page === $key) ? 'active' : '';
          echo "<li class='$active'><a href='admin_page.php?page=$key'><i class='$icon'></i><span>$label</span></a></li>";
      }
      ?>
      <li><a href="logout.php"><i class="ri-logout-box-r-fill"></i><span>Sign Out</span></a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <?php
    $file = __DIR__ . "/admin/{$page}.php"; // 👉 separate folder for admin pages
    if (file_exists($file)) {
        include $file;
    } else {
        echo "<h1>Page Not Found</h1>";
    }
    ?>
  </main>

</div>
</body>
</html>
