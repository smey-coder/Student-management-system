<?php
require_once "database.php"; 

$course_added = false;
$course_updated = false;
$course_deleted = false;

// Create
if (isset($_POST['add_course'])) {
    $name = trim($_POST['CourseName']);
    $credits = intval($_POST['Credits']);
    $department_id = intval($_POST['Department_id']);

    if ($name && $credits && $department_id) {
        $stmt = $conn->prepare("INSERT INTO courses (CourseName, Credits, Department_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $name, $credits, $department_id);
        if ($stmt->execute()) {
            $course_added = true;
        }
        $stmt->close();
    }
}

// Update
if (isset($_POST['edit_course'])) {
    $id = intval($_POST['course_id']);
    $name = trim($_POST['CourseName']);
    $credits = intval($_POST['Credits']);
    $department_id = intval($_POST['Department_id']);

    if ($name && $credits && $department_id) {
        $stmt = $conn->prepare("UPDATE courses SET CourseName=?, Credits=?, Department_id=? WHERE id=?");
        $stmt->bind_param("siii", $name, $credits, $department_id, $id);
        if ($stmt->execute()) {
            $course_updated = true;
        }
        $stmt->close();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM courses WHERE id=$id")) {
        $course_deleted = true;
    }
}

// Fetch all
$result = $conn->query("SELECT * FROM courses");
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
  <h2>Manage Courses</h2>

  <!-- Button to open SweetAlert add form -->
  <button class="btn" style="margin-bottom:15px; background:#1a237e; color:white;" onclick="openAddCourseForm()">+ Add Course</button>

  <table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%;">
    <tr style="background:#1a237e; color:white;">
      <th>ID</th>
      <th>Course Name</th>
      <th>Credits</th>
      <th>Department</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['CourseName']) ?></td>
        <td><?= htmlspecialchars($row['Credits']) ?></td>
        <td><?= htmlspecialchars($row['Department_id']) ?></td>
        <td>
          <a href="#" 
             onclick="openEditCourseForm(<?= $row['id'] ?>,'<?= htmlspecialchars($row['CourseName']) ?>','<?= $row['Credits'] ?>','<?= $row['Department_id'] ?>')" 
             class="btn" style="background:orange; color:white; padding:5px 10px; text-decoration:none;">
             Edit
          </a>

          <a href="admin_page.php?page=courses&delete=<?= $row['id'] ?>" 
             onclick="return confirmDelete(event)" 
             class="btn" style="background:red; color:white; padding:5px 10px; text-decoration:none;">
             Delete
          </a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>

<!-- Hidden Add/Edit forms -->
<form id="addCourseForm" method="POST" style="display:none;">
  <input type="hidden" name="add_course" value="1">
  <input type="text" name="CourseName" id="addCourseName" required>
  <input type="number" name="Credits" id="addCredits" required>
  <input type="number" name="Department_id" id="addDept" required>
</form>

<form id="editCourseForm" method="POST" style="display:none;">
  <input type="hidden" name="edit_course" value="1">
  <input type="hidden" name="course_id" id="editCourseId">
  <input type="text" name="CourseName" id="editCourseName" required>
  <input type="number" name="Credits" id="editCredits" required>
  <input type="number" name="Department_id" id="editDept" required>
</form>

<script>
// Add Course with SweetAlert
function openAddCourseForm() {
  Swal.fire({
    title: 'Add New Course',
    html:
      '<input id="swalCourseName" class="swal2-input" placeholder="Course Name">' +
      '<input id="swalCredits" type="number" class="swal2-input" placeholder="Credits">' +
      '<input id="swalDept" type="number" class="swal2-input" placeholder="Department ID">',
    showCancelButton: true,
    confirmButtonText: 'Save',
    preConfirm: () => {
      document.getElementById("addCourseName").value = document.getElementById("swalCourseName").value;
      document.getElementById("addCredits").value = document.getElementById("swalCredits").value;
      document.getElementById("addDept").value = document.getElementById("swalDept").value;
      document.getElementById("addCourseForm").submit();
    }
  });
}

// Edit Course with SweetAlert
function openEditCourseForm(id, name, credits, dept) {
  Swal.fire({
    title: 'Edit Course',
    html:
      `<input id="swalEditCourseName" class="swal2-input" value="${name}" placeholder="Course Name">` +
      `<input id="swalEditCredits" type="number" class="swal2-input" value="${credits}" placeholder="Credits">` +
      `<input id="swalEditDept" type="number" class="swal2-input" value="${dept}" placeholder="Department ID">`,
    showCancelButton: true,
    confirmButtonText: 'Update',
    preConfirm: () => {
      document.getElementById("editCourseId").value = id;
      document.getElementById("editCourseName").value = document.getElementById("swalEditCourseName").value;
      document.getElementById("editCredits").value = document.getElementById("swalEditCredits").value;
      document.getElementById("editDept").value = document.getElementById("swalEditDept").value;
      document.getElementById("editCourseForm").submit();
    }
  });
}

// Delete confirm
function confirmDelete(e) {
  e.preventDefault();
  let url = e.currentTarget.getAttribute("href");

  Swal.fire({
    title: "Are you sure?",
    text: "This course will be permanently deleted!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!"
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
}
</script>

<?php if ($course_added): ?>
<script>
Swal.fire({ icon: 'success', title: 'Course Added', text: 'The course has been added successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($course_updated): ?>
<script>
Swal.fire({ icon: 'success', title: 'Course Updated', text: 'The course has been updated successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($course_deleted): ?>
<script>
Swal.fire({ icon: 'success', title: 'Course Deleted', text: 'The course has been removed successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>
