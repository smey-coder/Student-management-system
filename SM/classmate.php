<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php"; // includes $conn
require_once "function.php"; // includes $conn and fetch_all

$student_id = $_SESSION['id'] ?? '';

if ($student_id) {
    echo "You must be logged in to view classmates.";
    exit();
}

$classmates = fetch_all($conn,
    "SELECT id, FirstName, LastName, Gender, DateOfBirth,
    Email, Phone, Address
     FROM students");
?>

<h1>Classmates</h1>
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Gender</th><th>DateOfBirth</th><th>Email</th><th>Phone</th><th>Address</th></tr>
  </thead>
  <tbody>
<?php if ($classmates): foreach ($classmates as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($c['FirstName']) ?></td>
        <td><?= htmlspecialchars($c['LastName']) ?></td>
        <td><?= htmlspecialchars($c['Gender']) ?></td>
        <td><?= htmlspecialchars($c['DateOfBirth']) ?></td>
        <td><?= htmlspecialchars($c['Email']) ?></td>
        <td><?= htmlspecialchars($c['Phone']) ?></td>
        <td><?= htmlspecialchars($c['Address']) ?></td>
        
    </tr>
<?php endforeach; else: ?>
    <tr><td colspan="2">No classmates found.</td></tr>
<?php endif; ?>
  </tbody>
</table>
