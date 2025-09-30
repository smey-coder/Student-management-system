<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Set default role if not set (for backward compatibility)
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'user';
}

require_once "database.php";
require_once "function.php";

// Define allowed pages for security
$allowed_pages = [
    'dashboard'  => 'dashboard.php',
    'assignment' => 'assignment.php', 
    'task'       => 'task.php',
    'att'        => 'att.php',
    'classmate'  => 'classmates.php',
    'course'     => 'course.php',
    'request'    => 'request.php'
];

$page = $_GET['page'] ?? 'dashboard';
$name = $_SESSION['name'] ?? 'Student';

// Security: Only allow predefined pages
if (!array_key_exists($page, $allowed_pages)) {
    $page = 'dashboard';
}

$page_file = $allowed_pages[$page];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>User Dashboard | <?php echo htmlspecialchars(ucfirst($page)); ?></title>
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="image/favicon.ico">
<link rel="icon" type="image/png" href="image/favicon.png">
<link rel="icon" href="favicon.svg" type="image/svg+xml">

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="user_page1.css">
<style>
:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --secondary: #7209b7;
    --accent: #f72585;
    --success: #4cc9f0;
    --warning: #f8961e;
    --danger: #e63946;
    --dark: #1a1a2e;
    --light: #f8f9fa;
    --gray: #6c757d;
    --border: #e2e8f0;
    --sidebar-width: 280px;
    --header-height: 70px;
    --border-radius: 12px;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* ===== RESET & BASE STYLES ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #333;
    line-height: 1.6;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}
.img{
    width: 20px;
    height: 45;
}

/* ===== SIDEBAR STYLES ===== */
.sidebar {
    width: var(--sidebar-width);
    background: linear-gradient(180deg, var(--dark) 0%, #16213e 100%);
    color: white;
    padding: 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.sidebar-header h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(45deg, var(--primary), var(--success));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
}

.user-info::before {
    content: "👋";
    font-size: 16px;
}

.sidebar ul {
    list-style: none;
    padding: 20px 0;
}

.sidebar li {
    margin: 5px 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.sidebar li:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.sidebar li.active {
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    box-shadow: var(--shadow);
}

.sidebar li a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.sidebar li.active a {
    color: white;
    font-weight: 600;
}

.sidebar li a i {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.sidebar li:last-child {
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
}

.sidebar li:last-child a {
    color: var(--accent);
}

/* ===== MAIN CONTENT STYLES ===== */
.main-content {
    flex: 1;
    margin-left: var(--sidebar-width);
    padding: 30px;
    background: rgba(248, 250, 252, 0.95);
    min-height: 100vh;
}

/* ===== PAGE HEADER STYLES ===== */
.page-header {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    border-left: 4px solid var(--primary);
}

.page-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 8px;
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    color: var(--gray);
    font-size: 16px;
    margin: 0;
}

/* ===== REQUEST FORM STYLES ===== */
.student-info {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 25px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    box-shadow: var(--shadow);
    border: none;
}

.student-info h3 {
    font-size: 18px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.student-info h3::before {
    content: "👤";
    font-size: 20px;
}

.student-info p {
    margin: 8px 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.student-info strong {
    min-width: 80px;
    display: inline-block;
}

.request-form {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark);
    font-size: 14px;
}

label.required::after {
    content: " *";
    color: var(--danger);
}

input, textarea, select {
    width: 100%;
    padding: 15px;
    border: 2px solid var(--border);
    border-radius: var(--border-radius);
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    transform: translateY(-2px);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

.char-counter {
    text-align: right;
    font-size: 12px;
    color: var(--gray);
    margin-top: 5px;
}

/* ===== BUTTON STYLES ===== */
button {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    padding: 18px 35px;
    border-radius: var(--border-radius);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 10px;
    width: 100%;
    box-shadow: var(--shadow);
}

button:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
}

button:active {
    transform: translateY(-1px);
}

button i {
    font-size: 18px;
}

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.stat-info h3 {
    font-size: 14px;
    color: var(--gray);
    margin-bottom: 5px;
    font-weight: 600;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark);
    display: block;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: var(--gray);
    font-weight: 500;
}

/* ===== ALERT MESSAGES ===== */
.alert, .success {
    padding: 20px;
    border-radius: var(--border-radius);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: 500;
    box-shadow: var(--shadow);
}

.alert {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.success {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* ===== FORM FOOTER ===== */
.form-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid var(--border);
}

.form-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.form-footer a:hover {
    color: var(--secondary);
    transform: translateX(3px);
}

/* ===== PAGE NOT FOUND ===== */
.page-not-found {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.page-not-found h1 {
    font-size: 48px;
    color: var(--dark);
    margin-bottom: 20px;
}

.page-not-found p {
    font-size: 18px;
    color: var(--gray);
    margin-bottom: 10px;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 1024px) {
    .sidebar {
        width: 250px;
    }
    
    .main-content {
        margin-left: 250px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 20px;
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-header {
        padding: 20px;
    }
    
    .sidebar ul {
        padding: 15px 0;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 15px;
    }
    
    .page-header {
        padding: 20px;
    }
    
    .request-form {
        padding: 20px;
    }
    
    .student-info {
        padding: 20px;
    }
    
    .stat-card {
        padding: 20px;
        flex-direction: column;
        text-align: center;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.main-content > * {
    animation: fadeIn 0.5s ease-out;
}

/* ===== SCROLLBAR STYLING ===== */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}
</style>
</head>
<body>
<div class="dashboard-container">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>NORTON STUDENT</h2>
      <div class="user-info">
        <span>Welcome, <?php echo htmlspecialchars($name); ?></span>
      </div>
    </div>
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
    $file_path = __DIR__ . "/" . $page_file;
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<div class='page-not-found'>";
        echo "<h1>Page Under Construction</h1>";
        echo "<p>The <strong>" . htmlspecialchars($page) . "</strong> page is currently being developed.</p>";
        echo "<p>Please check back later.</p>";
        echo "</div>";
    }
    ?>
  </main>

</div>
</body>
</html>