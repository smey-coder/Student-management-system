<?php
require_once "database.php";

// Set content type to PNG image
header('Content-Type: image/png');

// Get student ID from URL parameter
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    
    // Fetch student details
    $stmt = $conn->prepare("SELECT id, FirstName, LastName FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Include QR code library
        require_once 'vendor/autoload.php'; // If using Composer
        // Or use include path for standalone QR library
        include 'phpqrcode/qrlib.php';
        
        // Data to encode in QR - just student ID for simplicity
        $qrData = $student['id']; // Or use: $student['StudentID']
        
        // Generate QR code
        QRcode::png($qrData, false, QR_ECLEVEL_L, 10, 2);
    } else {
        // Student not found - generate error QR
        require_once 'phpqrcode/qrlib.php';
        QRcode::png('STUDENT_NOT_FOUND', false, QR_ECLEVEL_L, 10, 2);
    }
} else {
    // No student ID - generate error QR
    require_once 'phpqrcode/qrlib.php';
    QRcode::png('NO_STUDENT_ID', false, QR_ECLEVEL_L, 10, 2);
}
?>