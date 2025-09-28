<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}
require_once "database.php";
require_once "function.php";

$page = $_GET['page'] ?? 'dashboard';
$name = $_SESSION['name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>User Dashboard</title>
<!-- Image web -->
 <!-- ICO format -->
  <link rel="icon" type="image/x-icon" href="image/favicon.ico">

  <!-- PNG alternative -->
  <link rel="icon" type="image/png" href="image/favicon.png">

  <!-- SVG (modern browsers) -->
  <link rel="icon" href="favicon.svg" type="image/svg+xml">

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
<link rel="stylesheet" href="user_page1.css">
</head>
<body>
<div class="dashboard-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <h2>NORTON STUDENT</h2>
    <ul>
      <?php
      $menu = [
        'dashboard'  => ['Dashboard','ri-home-4-fill'],
        'assignment' => ['Assignment','ri-book-fill'],
        'task'       => ['Other Task','ri-briefcase-fill'],
        'att'        => ['My Attendance','ri-calendar-check-fill'],
        'classmate'  => ['Classmate','ri-team-fill'],
        'course'     => ['My Course','ri-database-2-fill'],
        'request'    => ['Request','ri-mail-fill'],
      ];
      foreach ($menu as $key => [$label,$icon]) {
          $active = ($page === $key) ? 'active' : '';
          echo "<li class='$active'><a href='user_page.php?page=$key'><i class='$icon'></i><span>$label</span></a></li>";
      }
      ?>
      <li><a href="logout.php"><i class="ri-logout-box-r-fill"></i><span>Sign Out</span></a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <?php
    $file = __DIR__ . "/{$page}.php";
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
