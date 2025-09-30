<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <link rel="stylesheet" href="css/dashboard_style.css"> <!-- external CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard">
    <div class="dashboard-header">
        <div class="user-info">
            <div class="user-avatar">NORTON UNIVERSITY <i class="fas fa-school"></i></div>
            <div class="user-details">
                <h1>Hello,<?php echo htmlspecialchars($name); ?></h1>
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
        <div class="stat-box"><div class="stat-icon"><i class="fas fa-users"></i></div><h3>Total Users</h3><p><?= $total_users ?></p></div>
        <div class="stat-box"><div class="stat-icon"><i class="fas fa-user-graduate"></i></div><h3>Total Students</h3><p><?= $total_students ?></p></div>
        <div class="stat-box"><div class="stat-icon"><i class="fas fa-book-open"></i></div><h3>Total Courses</h3><p><?= $total_courses ?></p></div>
        <div class="stat-box"><div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div><h3>Total Teachers</h3><p><?= $total_teachers ?></p></div>
        <div class="stat-box"><div class="stat-icon"><i class="fas fa-calendar-check"></i></div><h3>Total Attendance</h3><p><?= $total_attendance ?></p></div>
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

new Chart(document.getElementById('productivityChart').getContext('2d'),{
    type:'line',
    data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets:[{label:'Productivity (%)',data:[65,75,80,70,85,60,45],
        borderColor:'#6366f1',backgroundColor:'rgba(99,102,241,0.1)',borderWidth:3,fill:true,tension:0.4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true,max:100,grid:{color:'rgba(0,0,0,0.05)'}},x:{grid:{display:false}}}}
});

new Chart(document.getElementById('taskChart').getContext('2d'),{
    type:'doughnut',
    data:{labels:['Completed','In Progress','Pending'],
        datasets:[{data:[12,5,8],backgroundColor:['#10b981','#f59e0b','#ef4444'],borderWidth:0,hoverOffset:8}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},cutout:'70%'}
});
</script>
</body>
</html>
