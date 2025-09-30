<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php"; // $conn = mysqli connection

$student_added = false;
$student_updated = false;
$student_deleted = false;

// Fetch all users for the user_id dropdown
$users_result = $conn->query("SELECT id, name FROM users ORDER BY name ASC");

// --- ADD STUDENT ---
if (isset($_POST['add_student'])) {
    $fname = trim($_POST['FirstName']);
    $lname = trim($_POST['LastName']);
    $gender = trim($_POST['Gender']);
    $dob = trim($_POST['DateOfBirth']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);
    $user_id = intval($_POST['user_id']);
    $enrollment_date = date('Y-m-d');

    if ($fname && $lname && $gender && $dob && $email && $phone && $address && $user_id) {
        $stmt = $conn->prepare("INSERT INTO students (FirstName, LastName, Gender, DateOfBirth, Email, Phone, Address, EnrollmentDate, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $fname, $lname, $gender, $dob, $email, $phone, $address, $enrollment_date, $user_id);
        if ($stmt->execute()) $student_added = true;
        $stmt->close();
    }
}

// --- EDIT STUDENT ---
if (isset($_POST['edit_student'])) {
    $id = intval($_POST['id']);
    $fname = trim($_POST['FirstName']);
    $lname = trim($_POST['LastName']);
    $gender = trim($_POST['Gender']);
    $dob = trim($_POST['DateOfBirth']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);
    $user_id = intval($_POST['user_id']);

    $stmt = $conn->prepare("UPDATE students SET FirstName=?, LastName=?, Gender=?, DateOfBirth=?, Email=?, Phone=?, Address=?, user_id=? WHERE id=?");
    $stmt->bind_param("sssssssii", $fname, $lname, $gender, $dob, $email, $phone, $address, $user_id, $id);
    if ($stmt->execute()) $student_updated = true;
    $stmt->close();
}

// --- DELETE STUDENT ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Check for dependent requests
    $res = $conn->query("SELECT COUNT(*) as cnt FROM requests WHERE user_id=$id");
    $row = $res->fetch_assoc();
    if ($row['cnt'] > 0) {
        echo "<script>alert('Cannot delete student: associated requests exist.'); window.location='students.php';</script>";
        exit();
    }

    // Delete student safely
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $student_deleted = true;
    $stmt->close();
}

// --- FETCH ALL STUDENTS ---
$result = $conn->query("SELECT s.*, u.name FROM students s LEFT JOIN users u ON s.user_id = u.id");
?>

<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
  <h2>Manage Students</h2>

  <!-- Add Student Button -->
  <button class="btn" style="background:#1a237e; color:white;" onclick="openAddForm()">➕ Add Student</button>

  <!-- Student List -->
  <table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%;">
    <tr style="background:#1a237e; color:white;">
      <th>ID</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Gender</th>
      <th>DOB</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Address</th>
      <th>EnrollmentDate</th>
      <th>User</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['FirstName']) ?></td>
        <td><?= htmlspecialchars($row['LastName']) ?></td>
        <td><?= htmlspecialchars($row['Gender']) ?></td>
        <td><?= htmlspecialchars($row['DateOfBirth']) ?></td>
        <td><?= htmlspecialchars($row['Email']) ?></td>
        <td><?= htmlspecialchars($row['Phone']) ?></td>
        <td><?= htmlspecialchars($row['Address']) ?></td>
        <td><?= htmlspecialchars($row['EnrollmentDate']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>
          <button class="btn" style="background:green; color:white;" onclick='openEditForm(<?= json_encode($row) ?>)'>Edit</button>
          <a href="admin_page.php?page=students&delete=<?= $row['id'] ?>" onclick="return confirmDelete(event)" class="btn" style="background:red; color:white; padding:5px 10px; text-decoration:none;">Delete</a>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>

<script>
// Confirm delete with SweetAlert
function confirmDelete(e) {
  e.preventDefault();
  let url = e.currentTarget.getAttribute("href");

  Swal.fire({
    title: "Are you sure?",
    text: "This student will be permanently deleted!",
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

// Users array for dropdown
let users = <?php
$users_result->data_seek(0);
$users_arr = [];
while ($u = $users_result->fetch_assoc()) $users_arr[] = $u;
echo json_encode($users_arr);
?>;

// --- Add Student Form ---
function openAddForm() {
  let optionsHtml = users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');

  Swal.fire({
    title: 'Add New Student',
    html: `
      <form id="addForm" method="POST">
        <input type="hidden" name="add_student" value="1">
        <input class="swal2-input" type="text" name="FirstName" placeholder="First Name" required>
        <input class="swal2-input" type="text" name="LastName" placeholder="Last Name" required>
        <select class="swal2-input" name="Gender" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <input class="swal2-input" type="date" name="DateOfBirth" required>
        <input class="swal2-input" type="email" name="Email" placeholder="Email" required>
        <input class="swal2-input" type="text" name="Phone" placeholder="Phone" required>
        <input class="swal2-input" type="text" name="Address" placeholder="Address" required>
        <select class="swal2-input" name="user_id" required>
          <option value="">Select User</option>
          ${optionsHtml}
        </select>
      </form>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Add Student',
    preConfirm: () => { document.getElementById('addForm').submit(); }
  });
}

// --- Edit Student Form ---
function openEditForm(student) {
  let optionsHtml = users.map(u => `<option value="${u.id}" ${u.id==student.user_id?'selected':''}>${u.name}</option>`).join('');

  Swal.fire({
    title: 'Edit Student',
    html: `
      <form id="editForm" method="POST">
        <input type="hidden" name="edit_student" value="1">
        <input type="hidden" name="id" value="${student.id}">
        <input class="swal2-input" type="text" name="FirstName" value="${student.FirstName}" required>
        <input class="swal2-input" type="text" name="LastName" value="${student.LastName}" required>
        <select class="swal2-input" name="Gender" required>
          <option value="Male" ${student.Gender === 'Male' ? 'selected' : ''}>Male</option>
          <option value="Female" ${student.Gender === 'Female' ? 'selected' : ''}>Female</option>
        </select>
        <input class="swal2-input" type="date" name="DateOfBirth" value="${student.DateOfBirth}" required>
        <input class="swal2-input" type="email" name="Email" value="${student.Email}" required>
        <input class="swal2-input" type="text" name="Phone" value="${student.Phone}" required>
        <input class="swal2-input" type="text" name="Address" value="${student.Address}" required>
        <select class="swal2-input" name="user_id" required>
          <option value="">Select User</option>
          ${optionsHtml}
        </select>
      </form>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Save Changes',
    preConfirm: () => { document.getElementById('editForm').submit(); }
  });
}
</script>

<?php if ($student_added): ?>
<script>Swal.fire({ icon: 'success', title: 'Student Added', text: 'The student has been added successfully!', confirmButtonColor: '#1a237e' });</script>
<?php endif; ?>
<?php if ($student_updated): ?>
<script>Swal.fire({ icon: 'success', title: 'Student Updated', text: 'The student has been updated successfully!', confirmButtonColor: '#1a237e' });</script>
<?php endif; ?>
<?php if ($student_deleted): ?>
<script>Swal.fire({ icon: 'success', title: 'Student Deleted', text: 'The student has been removed successfully!', confirmButtonColor: '#1a237e' });</script>
<?php endif; ?>

<?php $conn->close(); ?>
<script>