<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$name = $_SESSION['name'] ?? 'Admin';
$requests = [];

try {
    // Fetch requests with student names
    $result = $conn->query("
        SELECT r.*, CONCAT(s.FirstName,' ',s.LastName) AS student_name
        FROM requests r
        JOIN students s ON r.student_id = s.id
        ORDER BY r.created_date DESC
    ");
    
    if ($result) {
        $requests = $result;
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error = "Failed to load requests. Please try again.";
}
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
/* Your existing CSS remains the same */
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
    margin-right:5px; 
    transition: all 0.3s ease; }
.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
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
.error-message {
    background:#ef4444;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}
@media(max-width:768px){
    .requests-container {padding:15px;}
    table th, table td {padding:8px;}
    .action-btn {padding:5px 10px; font-size:14px;}
}
.dashboard .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
    padding: 15px 20px;
    border-bottom: 2px solid #e5e7eb;
}

.dashboard .dashboard-header .user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.dashboard .dashboard-header .user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(145deg, #6366f1, #8b5cf6);
    color: #fff;
    font-weight: 600;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dashboard .dashboard-header .user-details h1 {
    font-size: 22px;
    color: #0758da;
    margin: 0;
}

.dashboard .dashboard-header .user-details p {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}
</style>
</head>
<body>
<div class="dashboard">
    <div class="dashboard-header">
        <div class="user-info">
            <div class="user-avatar"><i class="fas fa-paper-plane"></i></div>
            <div class="user-details">
                <h1>Hello, <?php echo htmlspecialchars($name); ?></h1>
                <p>Request Management</p>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <?php if ($_SESSION['message'] === "approve_success"): ?>
        <script>alert("✅ Approve success!");</script>
        <?php else: ?>
            <div class="flash-message">
                <?= htmlspecialchars($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?= htmlspecialchars($error); ?>
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
            <?php if(isset($requests) && $requests->num_rows > 0): ?>
                <?php while($row = $requests->fetch_assoc()): ?>
                    <tr data-id="<?= $row['id'] ?>">
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
                            <?php if ($row['status'] === 'Pending'): ?>
                                <button class="action-btn approve" onclick="processRequest(<?= $row['id'] ?>, 'approve')">Approve</button>
                                <button class="action-btn reject" onclick="processRequest(<?= $row['id'] ?>, 'reject')">Reject</button>
                            <?php else: ?>
                                <button class="action-btn" disabled>Processed</button>
                            <?php endif; ?>
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
<script>
function processRequest(id, action) {
    if (!confirm(`Are you sure you want to ${action} request #${id}?`)) {
        return;
    }
    
    // Disable buttons immediately to prevent double-clicks
    const row = document.querySelector(`tr[data-id='${id}']`);
    const buttons = row.querySelectorAll('.action-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch("action/process_request.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "id=" + id + "&action=" + action
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    })
    .then(data => {
        alert(data.message);
        
        if (data.status === "success") {
            // Update the status in the table
            const statusCell = row.querySelector(".status");
            const newStatus = action === 'approve' ? 'Approved' : 'Rejected';
            statusCell.textContent = newStatus;
            statusCell.className = 'status ' + (action === 'approve' ? 'status-approved' : 'status-rejected');
            
            // Show success message
            showFlashMessage(`Request #${id} ${newStatus.toLowerCase()} successfully!`, 'success');
        } else {
            // Re-enable buttons if failed
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("❌ Error processing request");
        // Re-enable buttons on error
        buttons.forEach(btn => btn.disabled = false);
    });
}

function showFlashMessage(message, type = 'success') {
    const flashDiv = document.createElement('div');
    flashDiv.className = type === 'success' ? 'flash-message' : 'error-message';
    flashDiv.textContent = message;
    
    document.querySelector('.dashboard').insertBefore(flashDiv, document.querySelector('.requests-container'));
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        flashDiv.remove();
    }, 5000);
}
</script>
</body>
</html>

<?php
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?>