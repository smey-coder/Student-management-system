<?php
// Create
if (isset($_POST['add_course'])) {
    $code = $_POST['code'];
    $title = $_POST['title'];
    $conn->query("INSERT INTO courses (code, title) VALUES ('$code', '$title')");
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM courses WHERE id=$id");
}

// Fetch all courses
$result = $conn->query("SELECT * FROM courses");
?>

<div class="card">
  <h2>Manage Courses</h2>

  <form method="POST">
    <input type="text" name="code" placeholder="Course Code" required>
    <input type="text" name="title" placeholder="Course Title" required>
    <button type="submit" name="add_course" class="btn">Add Course</button>
  </form>

  <table>
    <tr><th>ID</th><th>Code</th><th>Title</th><th>Action</th></tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['code']) ?></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td>
          <a href="admin_page.php?page=courses&delete=<?= $row['id'] ?>" class="btn" style="background:red">Delete</a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>
