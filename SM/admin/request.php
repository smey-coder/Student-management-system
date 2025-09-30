<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch requests with student names
$requests = $conn->query("
    SELECT r.*, CONCAT(s.FirstName,' ',s.LastName) AS student_name
    FROM requests r
    JOIN students s ON r.student_id = s.id
    ORDER BY r.created_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Requests</title>
<link rel="stylesheet" href="dashboard_style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Requests Table Styling */
.requests-container {
    background:#fff; 
    padding:25px; 
    border-radius:18px; 
    box-shadow:0 6px 20px rgba(0,0,0,0.08); 
    margin-top:20px;
}
.requests-container h2 {
    margin-bottom:20px; 
    font-size:22px; 
    color:#1f2937; 
    border-left:4px solid #6366f1; 
    padding-left:10px;
}
table { 
    width:100%; 
    border-collapse:collapse; }
table th, table td { 
    padding:12px; 
    text-align:left; 
    border-bottom:1px solid #e5e7eb; }
table th { 
    background:#f3f4f6; 
    color:#111827; }
.status { 
    padding:4px 10px; 
    border-radius:12px; 
    font-weight:500; 
    font-size:12px; }
.status-pending {
    background:#fef3c7; 
    color:#d97706;}
.status-approved {
    background:#d1fae5; 
    color:#065f46;}
.status-rejected {
    background:#fee2e2; 
    color:#dc2626;}
.action-btn { 
    padding:6px 12px; 
    border:none; 
    border-radius:6px; 
    cursor:pointer; 
    margin-right:5px; }
.approve {
    background:#10b981; 
    color:white;}
.reject {
    background:#ef4444; 
    color:white;}
.flash-message {
    background:#10b981;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}
</style>
</head>
<body>
<div class="dashboard">
    <div class="dashboard-header">
        <div class="user-info">
            <div class="user-avatar">NORTON UNIVERSITY <i class="fas fa-school"></i></div>
            <div class="user-details">
                <h1>Hello, <?php echo htmlspecialchars($name ?? 'Admin'); ?></h1>
                <p>Request Management</p>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="flash-message">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <div class="requests-container">
        <h2>Student Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Request Type</th>
                    <th>Request Details</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($requests->num_rows > 0): ?>
                <?php while($row = $requests->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['request_type']) ?></td>
                        <td><?= htmlspecialchars($row['request_detail']) ?></td>
                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($row['created_date']))) ?></td>
                        <td>
                            <?php
                                $status_class = match($row['status']) {
                                    'Pending' => 'status-pending',
                                    'Approved' => 'status-approved',
                                    'Rejected' => 'status-rejected',
                                    default => 'status-pending',
                                };
                            ?>
                            <span class="status <?= $status_class ?>"><?= $row['status'] ?></span>
                        </td>
                        <td>
                            <form action="process_request.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button class="action-btn approve" name="action" value="approve">Approve</button>
                                <button class="action-btn reject" name="action" value="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No requests found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="request.js"></script>
</body>
</html>
