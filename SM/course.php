<?php
$courses = fetch_all($conn,
   "SELECT id, CourseName, Credits, Department_id
    FROM courses ");
?>
<h1>My Courses</h1>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th> Course Name</th>
      <th>Credits</th>
      <th>DepartmetID</th>
    </tr>
  </thead>
  <tbody>
<?php if ($courses && count($courses) > 0): ?>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['id']) ?></td>
        <td><?= htmlspecialchars($c['CourseName']) ?></td>
        <td><?= htmlspecialchars($c['Credits']) ?></td>
        <td><?= htmlspecialchars($c['Department_id']) ?></td>
      </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
      <td colspan="3">No enrolled courses.</td>
    </tr>
<?php endif; ?>
  </tbody>
</table>
