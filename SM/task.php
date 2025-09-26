<?php
// Make sure $student_id is defined
$student_id = $_SESSION['id'] ?? 0;

// Fetch tasks for current student only
$tasks = fetch_all($conn,
   "SELECT task_name, description, due_date, status FROM tasks");
?>

<h1>Other Tasks</h1>

<?php if ($tasks && count($tasks) > 0): ?>
  <table>
    <thead>
      <tr>
        <th>Task Name</th>
        <th>Description</th>
        <th>Due Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t['task_name']) ?></td>
          <td><?= htmlspecialchars($t['description']) ?></td>
          <td><?= htmlspecialchars(date('d-m-Y', strtotime($t['due_date']))) ?></td>
          <td>
            <?php 
              $status = htmlspecialchars($t['status']); 
              $color = 'gray';
              if (strtolower($status) === 'pending') $color = '#facc15';
              elseif (strtolower($status) === 'completed') $color = '#16a34a';
              elseif (strtolower($status) === 'overdue') $color = '#dc2626';
            ?>
            <span style="color: <?= $color ?>; font-weight: 600;"><?= $status ?></span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No extra tasks assigned.</p>
<?php endif; ?>
