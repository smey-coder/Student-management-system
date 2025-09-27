<?php
$rows = fetch_all($conn,
  "SELECT * From assignments");
?>
<h1>Assignments</h1>
<table>
  <thead><tr><th>ID</th><th>Subject</th><th>Lecturer</th><th>Status</th><th>Deadline</th></tr></thead>
  <tbody>
<?php if ($rows): foreach ($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['id']) ?></td>
      <td><?= htmlspecialchars($r['subject']) ?></td>
      <td><?= htmlspecialchars($r['lecturer']) ?></td>
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td><?= htmlspecialchars($r['deadline']) ?></td>
    </tr>
<?php endforeach; else: ?>
    <tr><td colspan="5">No assignments found.</td></tr>
<?php endif; ?>
  </tbody>
</table>
