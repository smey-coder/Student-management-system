<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Fetch assignments
$rows = fetch_all($conn, "
    SELECT id, title, subject, lecturer, status, deadline, priority, created_at
    FROM assignments 
    ORDER BY created_at DESC
");

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=assignments_export_' . date('Y-m-d') . '.csv');

// Create CSV output
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Add headers
fputcsv($output, ['ID', 'Title', 'Subject', 'Lecturer', 'Status', 'Deadline', 'Priority', 'Created Date']);

// Add data
foreach ($rows as $row) {
    fputcsv($output, [
        $row['id'],
        $row['title'],
        $row['subject'],
        $row['lecturer'],
        $row['status'],
        date('Y-m-d H:i', strtotime($row['deadline'])),
        $row['priority'],
        date('Y-m-d H:i', strtotime($row['created_at']))
    ]);
}

fclose($output);
exit;
?>