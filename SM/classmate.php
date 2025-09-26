<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "function.php"; // includes $conn and fetch_all

$student_id = $_SESSION['id'] ?? null;

if (!$student_id) {
    echo "You must be logged in to view classmates.";
    exit();
}

$classmates = fetch_all($conn,
    "SELECT name, class, email
     FROM students");
?>

<h1>Classmates</h1>
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr><th>Name</th><th>Class</th><th>Email</th></tr>
  </thead>
  <tbody>
<?php if ($classmates): foreach ($classmates as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['name']) ?></td>
        <td><?= htmlspecialchars($c['class']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
    </tr>
<?php endforeach; else: ?>
    <tr><td colspan="2">No classmates found.</td></tr>
<?php endif; ?>
  </tbody>
</table>
