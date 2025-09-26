<?php
// Create
if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $conn->query("INSERT INTO students (name, email) VALUES ('$name', '$email')");
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
}

// Fetch all students
$result = $conn->query("SELECT * FROM students");
?>

<div class="card">
  <h2>Manage Students</h2>

  <form method="POST">
    <input type="text" name="name" placeholder="Student Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit" name="add_student" class="btn">Add Student</button>
  </form>

  <table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td>
          <a href="admin_page.php?page=students&delete=<?= $row['id'] ?>" class="btn" style="background:red">Delete</a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>
