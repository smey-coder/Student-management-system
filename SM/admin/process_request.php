<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['id'], $_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    $status = ($action === 'approve') ? 'Approved' : 'Rejected';

    $stmt = $conn->prepare("UPDATE requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    $_SESSION['message'] = "Request $id has been $status.";
}

header("Location: requests.php");
exit;
