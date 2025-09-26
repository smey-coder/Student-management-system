<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}
require_once "database.php";
require_once "functions.php";

$student_id = $_SESSION['user_id'] ?? 0;

$sql = "SELECT date, status FROM attendance WHERE student_id = ?";
$types = 'i';
$params = [$student_id];

if (!empty($_GET['start']) && !empty($_GET['end'])) {
    $sql .= " AND date BETWEEN ? AND ?";
    $types .= 'ss';
    $params[] = $_GET['start'];
    $params[] = $_GET['end'];
}
if (!empty($_GET['status'])) {
    $sql .= " AND status = ?";
    $types .= 's';
    $params[] = $_GET['status'];
}
$sql .= " ORDER BY date DESC";

$rows = fetch_all($conn, $sql, $types, $params);

$filename = "attendance_{$student_id}_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($out, ['Date', 'Status']);
foreach ($rows as $r) {
    fputcsv($out, [$r['date'], $r['status']]);
}
fclose($out);
exit();
