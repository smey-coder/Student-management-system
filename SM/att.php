<?php
//----------------------------------------------------------
//  Start session and load helpers
//----------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    ob_start(); // Start output buffering
}

//----------------------------------------------------------
//  Require student login - MUST BE BEFORE ANY OUTPUT
//----------------------------------------------------------
if (!isset($_SESSION['id'])) {
    // Clear any output that might have been sent
    if (ob_get_length()) ob_clean();
    
    if (!headers_sent()) {
        header('Location: login.php');
        exit();
    } else {
        die('<script>window.location.href = "login.php";</script>');
    }
}

require_once 'database.php';
require_once 'function.php';

$student_id = $_SESSION['id'];

//----------------------------------------------------------
//  Handle filters
//----------------------------------------------------------
$start_date    = $_GET['start']  ?? '';
$end_date      = $_GET['end']    ?? '';
$status_filter = $_GET['status'] ?? '';
$error_message = '';

if ($start_date && $end_date && $start_date > $end_date) {
    $error_message = "Start date cannot be after end date.";
}

//----------------------------------------------------------
//  Build SQL
//----------------------------------------------------------
$sql    = "SELECT attendance_date, status FROM attendance WHERE student_id = ?";
$types  = "i";  // for student_id
$params = [$student_id];

if (!$error_message) {
    if (!empty($start_date)) {
        $sql   .= " AND attendance_date >= ?";
        $types .= "s";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $sql   .= " AND attendance_date <= ?";
        $types .= "s";
        $params[] = $end_date;
    }
    if (!empty($status_filter)) {
        $sql   .= " AND status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY attendance_date DESC";

    $att = fetch_all($conn, $sql, $types, $params);
} else {
    $att = [];
}

$total_records = count($att);

// End output buffering and send content
if (ob_get_length()) ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="user_page1.css">
<style>
/* Your existing CSS styles here */
.status-present { background:#d4edda; color:#155724; padding:4px 8px; border-radius:4px; font-weight:bold; }
.status-absent  { background:#f8d7da; color:#721c24; padding:4px 8px; border-radius:4px; font-weight:bold; }
.status-late    { background:#fff3cd; color:#856404; padding:4px 8px; border-radius:4px; font-weight:bold; }
.status-excused { background:#e2e3e5; color:#383d41; padding:4px 8px; border-radius:4px; font-weight:bold; }
.status-unknown { background:#d6d8d9; color:#1b1e21; padding:4px 8px; border-radius:4px; font-weight:bold; }

.filter-form { 
    display:flex; 
    gap:10px; 
    flex-wrap:wrap; 
    margin-bottom:20px; 
    align-items: center;
}
.filter-form label { 
    font-weight: 600;
    color: #374151;
}
.filter-form input, .filter-form select, .filter-form button {
    padding:8px 12px; 
    border-radius:6px; 
    border:1px solid #d1d5db;
    font-size: 14px;
}
.filter-form button { 
    background:#2563eb; 
    color:#fff; 
    border:none; 
    cursor:pointer;
    transition: background 0.2s;
}
.filter-form button:hover { 
    background:#1e40af; 
}
.btn-clear { 
    background:#dc2626; 
    color:#fff; 
    text-decoration:none; 
    padding:8px 16px; 
    border-radius:6px;
    display: inline-block;
    transition: background 0.2s;
}
.btn-clear:hover { 
    background:#b91c1c; 
}

.alert-box {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.summary { 
    background:#e7f3ff; 
    padding:15px; 
    border-radius:8px; 
    margin-bottom:20px;
    border-left: 4px solid #2563eb;
}

.no-data { 
    text-align:center; 
    padding:40px; 
    color:#6b7280;
    background: #f9fafb;
    border-radius: 8px;
}

table { 
    width:100%; 
    border-collapse:collapse; 
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
    border-radius:8px; 
    overflow:hidden;
    background: white;
}
table th { 
    background:#2563eb; 
    color:#fff; 
    font-weight:600; 
    padding:12px; 
    text-align:left;
}
table td { 
    padding:12px; 
    border-bottom:1px solid #e5e7eb;
}
table tbody tr:hover { 
    background:#f9fafb; 
}

@media (max-width: 768px) {
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
</head>
<body>
<div class="dashborad-container">
    <div class="main-content">
        <div class="welcome-card">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Attendance Icon">
            <div class="khmer-title">My Attendance</div>
            <div class="subtitle">View your attendance history and summary</div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert-box"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form class="filter-form" method="get">
            <input type="hidden" name="page" value="att">
            <div>
                <label>Start Date:</label>
                <input type="date" name="start" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div>
                <label>End Date:</label>
                <input type="date" name="end" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div>
                <label>Status:</label>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Present" <?= $status_filter === 'Present' ? 'selected' : '' ?>>Present</option>
                    <option value="Absent"  <?= $status_filter === 'Absent'  ? 'selected' : '' ?>>Absent</option>
                    <option value="Late"    <?= $status_filter === 'Late'    ? 'selected' : '' ?>>Late</option>
                    <option value="Excused" <?= $status_filter === 'Excused' ? 'selected' : '' ?>>Excused</option>
                </select>
            </div>
            <button type="submit">Apply Filters</button>
            <?php if ($start_date || $end_date || $status_filter): ?>
                <a href="att.php" class="btn-clear">Clear Filters</a>
            <?php endif; ?>
        </form>

        <?php if ($total_records > 0):
            $present = $absent = $late = $excused = 0;
            foreach ($att as $rec) {
                switch (strtolower($rec['status'])) {
                    case 'present': $present++; break;
                    case 'absent':  $absent++;  break;
                    case 'late':    $late++;    break;
                    case 'excused': $excused++; break;
                }
            }
            $attendance_rate = $total_records > 0 ? round(($present / $total_records) * 100, 1) : 0;
        ?>
            <div class="summary">
                <strong>Total Records:</strong> <?= $total_records ?> |
                <strong>Present:</strong> <?= $present ?> (<?= $attendance_rate ?>%) |
                <strong>Absent:</strong> <?= $absent ?> |
                <strong>Late:</strong> <?= $late ?> |
                <strong>Excused:</strong> <?= $excused ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($att as $rec): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($rec['attendance_date']))) ?></td>
                        <td><?= date('l', strtotime($rec['attendance_date'])) ?></td>
                        <td>
                            <?php
                            $cls = match(strtolower($rec['status'])) {
                                'present' => 'status-present',
                                'absent'  => 'status-absent',
                                'late'    => 'status-late',
                                'excused' => 'status-excused',
                                default   => 'status-unknown',
                            };
                            echo '<span class="'.$cls.'">'.htmlspecialchars($rec['status']).'</span>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>No attendance records found</h3>
                <p><?= ($start_date || $end_date || $status_filter)
                       ? 'Try adjusting your filters.'
                       : 'No attendance data available.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>