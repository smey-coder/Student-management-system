<?php
// Change this to the exact URL of your attendance page
$attendanceURL = 'https://yourdomain.com/attendance.php';

// You can adjust size (e.g., 200x200)
$qrAPI = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($attendanceURL) . '&size=200x200';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Attendance QR Code</title>
  <style>
    body {font-family: Arial, sans-serif; text-align:center; padding:40px;}
    h1 {color:#1a237e;}
  </style>
</head>
<body>
  <h1>Attendance QR Code</h1>
  <p>Scan this QR code to open the attendance page:</p>
  <img src="<?= $qrAPI ?>" alt="Attendance QR Code">
  <p><a href="<?= $attendanceURL ?>">Open Attendance Page</a></p>
</body>
</html>
