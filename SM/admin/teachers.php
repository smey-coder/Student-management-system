<?php
require_once "database.php";

$teacher_added   = false;
$teacher_updated = false;
$teacher_deleted = false;

/* --------------------  DELETE  -------------------- */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $teacher_deleted = true;
    }
    $stmt->close();
}

/* --------------------  ADD  -------------------- */
if (isset($_POST['add_teacher'])) {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $department_id = intval($_POST['Department_id'] ?? 0);
    $hire_date     = date('Y-m-d');

    if ($name && $email && $phone && $department_id) {
        $stmt = $conn->prepare(
            "INSERT INTO teachers (name, email, phone, HireDate, Department_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssi", $name, $email, $phone, $hire_date, $department_id);
        if ($stmt->execute()) {
            $teacher_added = true;
        }
        $stmt->close();
    }
}

/* --------------------  EDIT  -------------------- */
if (isset($_POST['edit_teacher'])) {
    $id           = intval($_POST['id'] ?? 0);
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $hire_date    = trim($_POST['HireDate'] ?? date('Y-m-d'));
    $department_id= intval($_POST['Department_id'] ?? 0);

    if ($id && $name && $email && $phone && $department_id) {
        $stmt = $conn->prepare(
            "UPDATE teachers
             SET name=?, email=?, phone=?, HireDate=?, Department_id=?
             WHERE id=?"
        );
        $stmt->bind_param("ssssii", $name, $email, $phone, $hire_date, $department_id, $id);
        if ($stmt->execute()) {
            $teacher_updated = true;
        }
        $stmt->close();
    }
}

/* --------------------  FETCH ALL TEACHERS  -------------------- */
$result = $conn->query("SELECT * FROM teachers");
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
  <h2>Manage Teachers</h2>
  <button class="btn" style="margin-bottom:15px; background:#1a237e; color:white;"
          onclick="openAddTeacherForm()">+ Add Teacher</button>

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
             onclick="openEditTeacherForm(
               <?= $row['id'] ?>,
               '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>',
               '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>',
               '<?= htmlspecialchars($row['phone'], ENT_QUOTES) ?>',
               '<?= htmlspecialchars($row['HireDate'], ENT_QUOTES) ?>',
               '<?= htmlspecialchars($row['Department_id'], ENT_QUOTES) ?>')"
             class="btn"
             style="background:orange; color:white; padding:5px 10px; text-decoration:none;">Edit</a>

          <a href="admin_page.php?page=teachers&delete=<?= $row['id'] ?>"
          onclick="return confirmDelete(event, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')"
          class="btn"
          style="background:red; color:white; padding:5px 10px; text-decoration:none;">
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
  <input type="text" name="name" id="addName">
  <input type="email" name="email" id="addEmail">
  <input type="text" name="phone" id="addPhone">
  <input type="number" name="Department_id" id="addDept">
</form>

<form id="editTeacherForm" method="POST" style="display:none;">
  <input type="hidden" name="edit_teacher" value="1">
  <input type="hidden" name="id" id="editTeacherId">
  <input type="text" name="name" id="editName">
  <input type="email" name="email" id="editEmail">
  <input type="text" name="phone" id="editPhone">
  <input type="number" name="Department_id" id="editDept">
  <input type="date" name="HireDate" id="editHireDate">
</form>

<script>
// Add Teacher
function openAddTeacherForm() {
  Swal.fire({
    title: 'Add New Teacher',
    html:
      '<input id="swalName" class="swal2-input" placeholder="Name" required>' +
      '<input id="swalEmail" type="email" class="swal2-input" placeholder="Email" required>' +
      '<input id="swalPhone" class="swal2-input" placeholder="Phone" required>' +
      '<input id="swalDept" type="number" class="swal2-input" placeholder="Department ID" required>',
    showCancelButton: true,
    confirmButtonText: 'Save',
    preConfirm: () => {
      const name = document.getElementById("swalName").value.trim();
      const email = document.getElementById("swalEmail").value.trim();
      const phone = document.getElementById("swalPhone").value.trim();
      const dept = document.getElementById("swalDept").value.trim();
      if (!name || !email || !phone || !dept) {
        Swal.showValidationMessage('Please fill all fields');
        return false;
      }
      document.getElementById("addName").value  = name;
      document.getElementById("addEmail").value = email;
      document.getElementById("addPhone").value = phone;
      document.getElementById("addDept").value  = dept;
      document.getElementById("addTeacherForm").submit();
    }
  });
}

// Edit Teacher
function openEditTeacherForm(id, name, email, phone, hireDate, dept) {
  Swal.fire({
    title: 'Edit Teacher',
    html:
      `<input id="swalEditName" class="swal2-input" value="${name}" placeholder="Name" required>` +
      `<input id="swalEditEmail" type="email" class="swal2-input" value="${email}" placeholder="Email" required>` +
      `<input id="swalEditPhone" class="swal2-input" value="${phone}" placeholder="Phone" required>` +
      `<input id="swalEditDept" type="number" class="swal2-input" value="${dept}" placeholder="Department ID" required>` +
      `<input id="swalEditHireDate" type="date" class="swal2-input" value="${hireDate}" placeholder="Hire Date" required>`,
    showCancelButton: true,
    confirmButtonText: 'Update',
    preConfirm: () => {
      const n = document.getElementById("swalEditName").value.trim();
      const e = document.getElementById("swalEditEmail").value.trim();
      const p = document.getElementById("swalEditPhone").value.trim();
      const d = document.getElementById("swalEditDept").value.trim();
      const h = document.getElementById("swalEditHireDate").value.trim();
      if (!n || !e || !p || !d || !h) {
        Swal.showValidationMessage('Please fill all fields');
        return false;
      }
      document.getElementById("editTeacherId").value = id;
      document.getElementById("editName").value     = n;
      document.getElementById("editEmail").value    = e;
      document.getElementById("editPhone").value    = p;
      document.getElementById("editDept").value     = d;
      document.getElementById("editHireDate").value = h;
      document.getElementById("editTeacherForm").submit();
    }
  });
}

// Delete confirm
function confirmDelete(e, teacherName) {
  e.preventDefault();
  const url = e.currentTarget.getAttribute("href");
  Swal.fire({
    title: "Are you sure?",
    html: `You are about to delete <b>${teacherName}</b>. This action cannot be undone!`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!"
  }).then((result) => {
    if (result.isConfirmed) window.location.href = url;
  });
}

// Success notifications
<?php if ($teacher_added): ?>
Swal.fire({ icon: 'success', title: 'Teacher Added', text: 'Teacher added successfully!', confirmButtonColor: '#1a237e' });
<?php endif; ?>

<?php if ($teacher_updated): ?>
Swal.fire({ icon: 'success', title: 'Teacher Updated', text: 'Teacher updated successfully!', confirmButtonColor: '#1a237e' });
<?php endif; ?>

<?php if ($teacher_deleted): ?>
Swal.fire({ icon: 'success', title: 'Teacher Deleted', text: 'Teacher deleted successfully!', confirmButtonColor: '#1a237e' });
<?php endif; ?>
</script>
