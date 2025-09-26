<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "function.php"; // make sure $conn & prepare_and_execute() exist

// Check if student is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['id'];
$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');

    if ($title && $body) {
        $stmt = prepare_and_execute(
            $conn,
            "INSERT INTO requests (student_id, request_type, request_detail, status) 
             VALUES (?, ?, ?, 'Pending')",
            'iss',
            [$student_id, $title, $body]
        );

        if ($stmt) {
            $message = "✅ Request submitted successfully.";
            $message_class = "success";
        } else {
            $message = "❌ Error submitting request.";
            $message_class = "alert";
        }
    } else {
        $message = "⚠ Please fill in all fields.";
        $message_class = "alert";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Request</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .request-form { max-width: 500px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);}
    .request-form label { display: block; margin-top: 15px; font-weight: 500; }
    .request-form input, .request-form textarea {
      width: 100%; padding: 10px; margin-top: 6px; border-radius: 8px; border: 1px solid #ccc;
      font-family: inherit;
    }
    .request-form button {
      margin-top: 15px; padding: 10px 20px;
      background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer;
      font-weight: 500;
    }
    .request-form button:hover { background: #1e40af; }
    .success { color: green; font-weight: bold; margin-bottom: 10px; }
    .alert { color: red; font-weight: bold; margin-bottom: 10px; }
  </style>
</head>
<body>
<div class="dashborad-container">

    <div class="main-content">
        <div class="welcome-card">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Request Icon">
            <div class="khmer-title">Submit Request</div>
            <div class="subtitle">Send your requests or messages to the admin</div>
        </div>

        <?php if ($message): ?>
            <div class="<?= $message_class ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" class="request-form">
            <label>Title:
                <input type="text" name="title" required>
            </label>
            <label>Message:
                <textarea name="body" rows="5" required></textarea>
            </label>
            <button type="submit">Send Request</button>
        </form>
    </div>
</div>
</body>
</html>
