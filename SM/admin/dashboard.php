<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Admin Name (from session or default) ----
$name = isset($_SESSION['name']) ? $_SESSION['name'] : "Admin";

// ---- Database counts ----
$total_users      = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$total_students   = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$total_courses    = $conn->query("SELECT COUNT(*) AS count FROM courses")->fetch_assoc()['count'];
$total_teachers   = $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
$total_attendance = $conn->query("SELECT COUNT(*) AS count FROM attendance")->fetch_assoc()['count']; // attendance
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * {margin:0; padding:0; box-sizing:border-box;}
    body {font-family:"Poppins", sans-serif; background:#e0e7ff; color:#1e293b;}
    .dashboard {max-width:1200px; margin:0 auto; padding:40px; background:#fff; border-radius:20px; box-shadow:0 8px 30px rgba(0,0,0,0.1);}
    .dashboard-header {display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:30px;}
    .user-info {display:flex; align-items:center; gap:15px;}
    .user-avatar {width:70px; height:70px; border-radius:50%; background:linear-gradient(145deg,#6366f1,#8b5cf6); color:white; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:600;}
    .user-details h1 {font-size:22px; color:#0758da;}
    .user-details p {font-size:14px; color:#6b7280;}
    .date-display {text-align:right; color:#0000FF;}
    .date-display .current-date {font-size:16px; font-weight:500;}
    .date-display .current-time {font-size:16px; color:#DC143C;}
    .stats-container {display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:40px;}
    .stat-box {background:#fff; padding:25px; border-radius:16px; text-align:center; box-shadow:5px 5px 15px rgba(0,0,0,0.1); transition:all 0.3s;}
    .stat-box:hover {transform:translateY(-8px); box-shadow:0 10px 25px rgba(0,0,0,0.15);}
    .stat-box h3 {margin-bottom:10px; color:#0758da; font-size:18px;}
    .stat-box p {font-size:36px; color:#ef0707; margin:0;}
    .charts-container {display:grid; grid-template-columns:1fr; gap:20px;}
    .chart-box {background:#fff; padding:25px; border-radius:18px; box-shadow:0 6px 20px rgba(0,0,0,0.08);}
    .chart-box h3 {margin-bottom:15px; font-size:18px; color:#0758da;}
    .chart-container {position:relative; height:300px; width:100%;}
    @media(max-width:768px){.dashboard-header{flex-direction:column; align-items:flex-start;}.date-display{text-align:left;}.stats-container{grid-template-columns:1fr;}}
    @media(max-width:480px){.user-avatar{width:60px; height:60px; font-size:16px;}.user-details h1{font-size:20px;}.stat-box p{font-size:28px;}.chart-container{height:250px;}}
  </style>
</head>
<body>
<div class="dashboard">
  <div class="dashboard-header">
    <div class="user-info">
      <div class="user-avatar">NU</div>
      <div class="user-details">
        <h1>Hello, <?= htmlspecialchars($name) ?></h1>
        <p>Welcome to Admin</p>
      </div>
    </div>
    <div class="date-display">
      <div class="current-date" id="current-date"></div>
      <div class="current-time" id="current-time"></div>
    </div>
  </div>

  <h2>Student Management System</h2>

  <!-- Stats -->
  <div class="stats-container">
    <div class="stat-box"><i class="fas fa-users fa-2x"></i><h3>Total Users</h3><p><?= $total_users ?></p></div>
    <div class="stat-box"><i class="fas fa-user-graduate fa-2x"></i><h3>Total Students</h3><p><?= $total_students ?></p></div>
    <div class="stat-box"><i class="fas fa-book-open fa-2x"></i><h3>Total Courses</h3><p><?= $total_courses ?></p></div>
    <div class="stat-box"><i class="fas fa-chalkboard-teacher fa-2x"></i><h3>Total Teachers</h3><p><?= $total_teachers ?></p></div>
    <div class="stat-box"><i class="fas fa-calendar-check fa-2x"></i><h3>Total Attendance</h3><p><?= $total_attendance ?></p></div>
  </div>

  <!-- Charts -->
  <div class="charts-container">
    <div class="chart-box">
      <h3>Weekly Productivity</h3>
      <div class="chart-container"><canvas id="productivityChart"></canvas></div>
    </div>
    <div class="chart-box">
      <h3>Task Distribution</h3>
      <div class="chart-container"><canvas id="taskChart"></canvas></div>
    </div>
  </div>
</div>

<script>
function updateDateTime(){
  const now=new Date();
  const options={weekday:'long',year:'numeric',month:'long',day:'numeric'};
  document.getElementById('current-date').textContent=now.toLocaleDateString('en-US',options);
  document.getElementById('current-time').textContent=now.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});
}
updateDateTime();setInterval(updateDateTime,60000);

// Productivity Line Chart
new Chart(document.getElementById('productivityChart').getContext('2d'),{
  type:'line',
  data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
    datasets:[{label:'Productivity (%)',data:[65,75,80,70,85,60,45],
    borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,0.1)',borderWidth:3,fill:true,tension:0.4}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true,max:100,grid:{color:'rgba(0,0,0,0.05)'}},x:{grid:{display:false}}}}
});

// Task Doughnut Chart
new Chart(document.getElementById('taskChart').getContext('2d'),{
  type:'doughnut',
  data:{labels:['Completed','In Progress','Pending'],
    datasets:[{data:[12,5,8],backgroundColor:['#10b981','#f59e0b','#ef4444'],borderWidth:0,hoverOffset:8}]},
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},cutout:'70%'}
});
</script>
</body>
</html>
