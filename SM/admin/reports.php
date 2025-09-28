<?php
$result = $conn->query("SELECT s.FirstName, s.LastName, c.CourseName, a.attendance_date, a.status
                        FROM attendance a
                        JOIN students s ON a.student_id=s.id
                        JOIN courses c ON a.course_id=c.id
                        ORDER BY a.attendance_date DESC");
?>
<div class="card">
  <h2>Reports</h2>
  <table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:15px;">
    <tr style="background:#1a237e; color:white;">
      <th>Student</th>
      <th>Course</th>
      <th>Date</th>
      <th>Status</th>
    </tr>
    <?php while($row=$result->fetch_assoc()){ ?>
    <tr>
      <td><?= htmlspecialchars($row['FirstName'].' '.$row['LastName']) ?></td>
      <td><?= htmlspecialchars($row['CourseName']) ?></td>
      <td><?= htmlspecialchars($row['attendance_date']) ?></td>
      <td><?= htmlspecialchars($row['status']) ?></td>
    </tr>
    <?php } ?>
  </table>
</div>
