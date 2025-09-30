<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

$result = $conn->query("
    SELECT r.id, CONCAT(s.FirstName,' ',s.LastName) AS student_name, r.request_type
    FROM requests r
    JOIN students s ON r.user_id = s.id
    WHERE r.id > $last_id
    ORDER BY r.id ASC
");

$new_requests = [];
while($row = $result->fetch_assoc()) {
    $new_requests[] = [
        'id' => $row['id'],
        'student_name' => $row['student_name'],
        'request_type' => $row['request_type']
    ];
}

echo json_encode($new_requests);
