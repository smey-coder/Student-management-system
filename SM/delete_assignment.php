<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = intval($_POST['id'] ?? 0);
    
    if ($assignment_id) {
        // Check if there are submissions
        $check_stmt = $conn->prepare("SELECT COUNT(*) as submission_count FROM assignment_submissions WHERE assignment_id = ?");
        $check_stmt->bind_param("i", $assignment_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();
        
        if ($result['submission_count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete assignment with existing submissions']);
            exit;
        }
        
        // Delete assignment
        $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->bind_param("i", $assignment_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting assignment: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid assignment ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>