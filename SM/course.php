<?php
$courses = fetch_all($conn,
   "SELECT id, course_code, course_name, lecturer, credit
    FROM courses ");
?>
<h1>My Courses</h1>
<table>
  <thead>
    <tr>
      <th>Code</th>
      <th>Name</th>
      <th>Lecturer</th>
      <th>Credit</th>
    </tr>
  </thead>
  <tbody>
<?php if ($courses && count($courses) > 0): ?>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['course_code']) ?></td>
        <td><?= htmlspecialchars($c['course_name']) ?></td>
        <td><?= htmlspecialchars($c['lecturer']) ?></td>
        <td><?= htmlspecialchars($c['credit']) ?></td>
      </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
      <td colspan="3">No enrolled courses.</td>
    </tr>
<?php endif; ?>
  </tbody>
</table>
