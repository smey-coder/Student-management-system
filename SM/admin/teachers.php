<?php
require_once "database.php"; 

$teacher_added = false;
$teacher_updated = false;
$teacher_deleted = false;

// ✅ Add Teacher
if (isset($_POST['add_teacher'])) {
    $lname = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department_id = intval($_POST['Department_id']);
    $hire_date = date('Y-m-d');

    if ($fname && $lname && $email && $phone && $department_id) {
        $stmt = $conn->prepare("INSERT INTO teachers (name, email, phone, HireDate, Department_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $fname, $lname, $email, $phone, $hire_date, $department_id);
        if ($stmt->execute()) {
            $teacher_added = true;
        }
        $stmt->close();
    }
}

// ✅ Edit Teacher
if (isset($_POST['edit_teacher'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $hire_date = trim($_POST['HireDate']);
    $department_id = intval($_POST['Department_id']);

    if ($fname && $lname && $email && $phone && $department_id) {
        $stmt = $conn->prepare("UPDATE teachers SET name=?, email=?, phone=?, HireDate=?, Department_id=? WHERE id=?");
        $stmt->bind_param("ssssii", $name, $email, $phone, $hire_date, $department_id, $id);
        if ($stmt->execute()) {
            $teacher_updated = true;
        }
        $stmt->close();
    }
}

// ✅ Delete Teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM teachers WHERE id=$id")) {
        $teacher_deleted = true;
    }
}

// Fetch all teachers
$result = $conn->query("SELECT * FROM teachers");
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
  <h2>Manage Teachers</h2>

  <!-- Add Teacher Button -->
  <button class="btn" style="margin-bottom:15px; background:#1a237e; color:white;" onclick="openAddTeacherForm()">+ Add Teacher</button>

  <!-- Teacher List -->
  <table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%;">
    <tr style="background:#1a237e; color:white;">
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Department</th>
      <th>Hire Date</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['Department_id']) ?></td>
        <td><?= htmlspecialchars($row['HireDate']) ?></td>
        <td>
          <a href="#" 
             onclick="openEditTeacherForm(<?= $row['id'] ?>,'<?= htmlspecialchars($row['name']) ?>','<?= htmlspecialchars($row['email']) ?>','<?= htmlspecialchars($row['phone']) ?>','<?= htmlspecialchars($row['HireDate']) ?>','<?= $row['Department_id'] ?>')" 
             class="btn" style="background:orange; color:white; padding:5px 10px; text-decoration:none;">
             Edit
          </a>
          <a href="admin_page.php?page=teachers&delete=<?= $row['id'] ?>" 
             onclick="return confirmDelete(event)" 
             class="btn" style="background:red; color:white; padding:5px 10px; text-decoration:none;">
             Delete
          </a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>

<!-- Hidden Forms -->
<form id="addTeacherForm" method="POST" style="display:none;">
  <input type="hidden" name="add_teacher" value="1">
  <input type="text" name="FirstName" id="addFname">
  <input type="text" name="LastName" id="addLname">
  <input type="email" name="Email" id="addEmail">
  <input type="text" name="Phone" id="addPhone">
  <input type="number" name="Department_id" id="addDept">
</form>

<form id="editTeacherForm" method="POST" style="display:none;">
  <input type="hidden" name="edit_teacher" value="1">
  <input type="hidden" name="teacher_id" id="editTeacherId">
  <input type="text" name="FirstName" id="editFname">
  <input type="text" name="LastName" id="editLname">
  <input type="email" name="Email" id="editEmail">
  <input type="text" name="Phone" id="editPhone">
  <input type="number" name="Department_id" id="editDept">
</form>

<script>
// ✅ Add Teacher Popup
function openAddTeacherForm() {
  Swal.fire({
    title: 'Add New Teacher',
    html:
      '<input id="swalFname" class="swal2-input" placeholder="First Name">' +
      '<input id="swalLname" class="swal2-input" placeholder="Last Name">' +
      '<input id="swalEmail" type="email" class="swal2-input" placeholder="Email">' +
      '<input id="swalPhone" class="swal2-input" placeholder="Phone">' +
      '<input id="swalDept" type="number" class="swal2-input" placeholder="Department ID">',
    showCancelButton: true,
    confirmButtonText: 'Save',
    preConfirm: () => {
      document.getElementById("addFname").value = document.getElementById("swalFname").value;
      document.getElementById("addLname").value = document.getElementById("swalLname").value;
      document.getElementById("addEmail").value = document.getElementById("swalEmail").value;
      document.getElementById("addPhone").value = document.getElementById("swalPhone").value;
      document.getElementById("addDept").value = document.getElementById("swalDept").value;
      document.getElementById("addTeacherForm").submit();
    }
  });
}

// ✅ Edit Teacher Popup
function openEditTeacherForm(id, fname, lname, email, phone, dept) {
  Swal.fire({
    title: 'Edit Teacher',
    html:
      `<input id="swalEditFname" class="swal2-input" value="${fname}" placeholder="First Name">` +
      `<input id="swalEditLname" class="swal2-input" value="${lname}" placeholder="Last Name">` +
      `<input id="swalEditEmail" type="email" class="swal2-input" value="${email}" placeholder="Email">` +
      `<input id="swalEditPhone" class="swal2-input" value="${phone}" placeholder="Phone">` +
      `<input id="swalEditDept" type="number" class="swal2-input" value="${dept}" placeholder="Department ID">`,
    showCancelButton: true,
    confirmButtonText: 'Update',
    preConfirm: () => {
      document.getElementById("editTeacherId").value = id;
      document.getElementById("editFname").value = document.getElementById("swalEditFname").value;
      document.getElementById("editLname").value = document.getElementById("swalEditLname").value;
      document.getElementById("editEmail").value = document.getElementById("swalEditEmail").value;
      document.getElementById("editPhone").value = document.getElementById("swalEditPhone").value;
      document.getElementById("editDept").value = document.getElementById("swalEditDept").value;
      document.getElementById("editTeacherForm").submit();
    }
  });
}

// ✅ Delete confirm
function confirmDelete(e) {
  e.preventDefault();
  let url = e.currentTarget.getAttribute("href");

  Swal.fire({
    title: "Are you sure?",
    text: "This teacher will be permanently deleted!",
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

<?php if ($teacher_added): ?>
<script>
Swal.fire({ icon: 'success', title: 'Teacher Added', text: 'The teacher has been added successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($teacher_updated): ?>
<script>
Swal.fire({ icon: 'success', title: 'Teacher Updated', text: 'The teacher has been updated successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($teacher_deleted): ?>
<script>
Swal.fire({ icon: 'success', title: 'Teacher Deleted', text: 'The teacher has been removed successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>
