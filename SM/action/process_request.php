<?php 
require_once "../database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "❌ Unauthorized access"]);
    exit;
}

// Always return JSON
header("Content-Type: application/json");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['id'], $_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    
    // Validate action
    if (!in_array($action, ['approve', 'reject'])) {
        echo json_encode(["status" => "error", "message" => "❌ Invalid action"]);
        exit;
    }
    
    try {
        // Check if request exists
        $check_stmt = $conn->prepare("SELECT id FROM requests WHERE id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "❌ Request not found"]);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();
        
        if ($action === 'approve') {
            // ✅ Update status to Approved
            $stmt = $conn->prepare("UPDATE requests SET status = 'Approved', processed_date = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "✅ Request #$id approved successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "❌ Failed to approve request #$id"]);
            }
            $stmt->close();
            
        } elseif ($action === 'reject') {
            // ❌ Update status to Rejected
            $stmt = $conn->prepare("UPDATE requests SET status = 'Rejected', processed_date = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "❌ Request #$id rejected successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "❌ Failed to reject request #$id"]);
            }
            $stmt->close();
        }
        
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "❌ Database error: " . $e->getMessage()]);
    }
    
} else {
    echo json_encode(["status" => "error", "message" => "❌ No request data received"]);
}

$conn->close();
?>