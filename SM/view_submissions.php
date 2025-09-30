<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$assignment_id = intval($_GET['assignment_id'] ?? 0);

// Fetch submissions
$stmt = $conn->prepare("
    SELECT s.*, CONCAT(st.FirstName, ' ', st.LastName) as student_name, 
           a.title as assignment_title, a.max_points
    FROM assignment_submissions s
    JOIN students st ON s.student_id = st.id
    JOIN assignments a ON s.assignment_id = a.id
    WHERE s.assignment_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$submissions = $stmt->get_result();
$stmt->close();
?>

<!-- Similar table structure to display submissions with grading options -->