<?php
// Create
if (isset($_POST['enroll'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $conn->query("INSERT INTO enrollments (student_id, course_id) VALUES ($student_id, $course_id)");
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM enrollments WHERE id=$id");
}

// Fetch data
$students = $conn->query("SELECT * FROM students");
$courses = $conn->query("SELECT * FROM courses");
$enrollments = $conn->query("SELECT e.id, s.name as student, c.title as course 
                             FROM enrollments e 
                             JOIN students s ON e.student_id=s.id 
                             JOIN courses c ON e.course_id=c.id");
?>

<div class="card">
  <h2>Manage Enrollments</h2>

  <form method="POST">
    <select name="student_id" required>
      <option value="">Select Student</option>
      <?php while ($s = $students->fetch_assoc()) { ?>
        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
      <?php } ?>
    </select>

    <select name="course_id" required>
      <option value="">Select Course</option>
      <?php while ($c = $courses->fetch_assoc()) { ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
      <?php } ?>
    </select>

    <button type="submit" name="enroll" class="btn">Enroll</button>
  </form>

  <table>
    <tr><th>ID</th><th>Student</th><th>Course</th><th>Action</th></tr>
    <?php while ($row = $enrollments->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['student']) ?></td>
        <td><?= htmlspecialchars($row['course']) ?></td>
        <td>
          <a href="admin_page.php?page=enrollments&delete=<?= $row['id'] ?>" class="btn" style="background:red">Delete</a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>
