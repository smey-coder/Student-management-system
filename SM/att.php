<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database and helper functions
require_once 'database.php';
require_once 'function.php'; // fetch_all() function

// Check if student is logged in
if (!isset($_SESSION['id'])) {
    header('Location: user_page.php');
    exit();
}

$student_id = $_SESSION['id'];

// Handle filter parameters
$start_date = $_GET['start'] ?? '';
$end_date = $_GET['end'] ?? '';
$status_filter = $_GET['status'] ?? '';
$error_message = '';

// Validate date range
if ($start_date && $end_date && $start_date > $end_date) {
    $error_message = "Start date cannot be after end date.";
}

// Build SQL query
$sql = "SELECT date, status FROM attendance WHERE id = ?";
$types = "i";
$params = [$student_id];

if (!$error_message) {
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND date BETWEEN ? AND ?";
        $types .= "ss";
        $params[] = $start_date;
        $params[] = $end_date;
    }
    if (!empty($status_filter)) {
        $sql .= " AND status = ?";
        $types .= "s";
        $params[] = $status_filter;
    }
    $sql .= " ORDER BY date DESC";
    $att = fetch_all($conn, $sql, $types, $params);
} else {
    $att = [];
}
$total_records = count($att);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="user_page1.css">
    <style>
        /* Attendance table specific */
        .status-present { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-absent { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-late { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-excused { background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-unknown { background: #d6d8d9; color: #1b1e21; padding: 4px 8px; border-radius: 4px; font-weight: bold; }

        .filter-form { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
        .filter-form input, .filter-form select, .filter-form button { padding: 6px 10px; border-radius: 4px; border: 1px solid #ddd; }
        .filter-form button { background: #2563eb; color: #fff; border: none; cursor: pointer; }
        .filter-form button:hover { background: #1e40af; }
        .btn-clear { background: #dc2626; color: #fff; text-decoration: none; padding: 6px 10px; border-radius: 4px; }
        .btn-clear:hover { background: #b91c1c; }

        .summary { background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .no-data { text-align: center; padding: 30px; color: #6b7280; }

        table { width: 100%; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; }
        table th { background: #2563eb; color: #fff; font-weight: 600; padding: 12px; text-align: left; }
        table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        table tbody tr:hover { background: #f9fafb; }
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

        <?php if (!empty($error_message)): ?>
            <div class="alert-box"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form class="filter-form" method="get">
            <input type="hidden" name="page" value="att">
            <label>Start:</label>
            <input type="date" name="start" value="<?= htmlspecialchars($start_date) ?>">
            <label>End:</label>
            <input type="date" name="end" value="<?= htmlspecialchars($end_date) ?>">
            <label>Status:</label>
            <select name="status">
                <option value="">All</option>
                <option value="Present" <?= $status_filter === 'Present' ? 'selected' : '' ?>>Present</option>
                <option value="Absent" <?= $status_filter === 'Absent' ? 'selected' : '' ?>>Absent</option>
                <option value="Late" <?= $status_filter === 'Late' ? 'selected' : '' ?>>Late</option>
                <option value="Excused" <?= $status_filter === 'Excused' ? 'selected' : '' ?>>Excused</option>
            </select>
            <button type="submit">Filter</button>
            <?php if ($start_date || $end_date || $status_filter): ?>
                <a href="att.php" class="btn-clear">Clear</a>
            <?php endif; ?>
        </form>

        <?php if ($total_records > 0): 
            $present_count = $absent_count = $late_count = $excused_count = 0;
            foreach ($att as $record) {
                switch (strtolower($record['status'])) {
                    case 'present': $present_count++; break;
                    case 'absent': $absent_count++; break;
                    case 'late': $late_count++; break;
                    case 'excused': $excused_count++; break;
                }
            }
            $attendance_rate = round(($present_count / $total_records) * 100, 1);
        ?>
            <div class="summary">
                <strong>Total Records:</strong> <?= $total_records ?> |
                <strong>Present:</strong> <?= $present_count ?> (<?= $attendance_rate ?>%) |
                <strong>Absent:</strong> <?= $absent_count ?> |
                <strong>Late:</strong> <?= $late_count ?> |
                <strong>Excused:</strong> <?= $excused_count ?>
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
                <?php foreach ($att as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($record['date']))) ?></td>
                        <td><?= date('l', strtotime($record['date'])) ?></td>
                        <td>
                            <?php
                            $status_class = match(strtolower($record['status'])) {
                                'present' => 'status-present',
                                'absent' => 'status-absent',
                                'late' => 'status-late',
                                'excused' => 'status-excused',
                                default => 'status-unknown',
                            };
                            echo '<span class="' . $status_class . '">' . htmlspecialchars($record['status']) . '</span>';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>No attendance records found</h3>
                <p><?= ($start_date || $end_date || $status_filter) ? 'Try adjusting your filters.' : 'No attendance data available.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
