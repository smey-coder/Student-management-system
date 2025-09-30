<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$assignment_id = intval($_POST['assignment_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $assignment_id) {
    // Check if assignment exists and deadline hasn't passed
    $stmt = $conn->prepare("SELECT deadline FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $assignment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$assignment) {
        die("Assignment not found");
    }
    
    if (strtotime($assignment['deadline']) < time()) {
        die("Assignment deadline has passed");
    }
    
    // Check if already submitted
    $stmt = $conn->prepare("SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $assignment_id, $student_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($existing) {
        die("You have already submitted this assignment");
    }
    
    // Handle file upload
    $submitted_file = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/assignments/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
        $filename = "assignment_{$assignment_id}_student_{$student_id}_" . time() . "." . $file_extension;
        $file_path = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $file_path)) {
            $submitted_file = $file_path;
        }
    }
    
    $submission_text = $_POST['submission_text'] ?? '';
    
    // Insert submission
    $stmt = $conn->prepare("
        INSERT INTO assignment_submissions 
        (assignment_id, student_id, submitted_text, submitted_file) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $assignment_id, $student_id, $submission_text, $submitted_file);
    
    if ($stmt->execute()) {
        // Update assignment status
        $update_stmt = $conn->prepare("UPDATE assignments SET status = 'Submitted' WHERE id = ?");
        $update_stmt->bind_param("i", $assignment_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $_SESSION['message'] = "Assignment submitted successfully!";
        header("Location: view_assignment.php?id=" . $assignment_id);
        exit;
    } else {
        die("Error submitting assignment: " . $conn->error);
    }
} else {
    die("Invalid request");
}
?>