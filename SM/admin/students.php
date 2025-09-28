<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php"; // $conn must exist

$student_added = false;
$student_deleted = false;
$student_updated = false;

// Create student
if (isset($_POST['add_student'])) {
    $fname = trim($_POST['FirstName']);
    $lname = trim($_POST['LastName']);
    $gender = trim($_POST['Gender']);
    $dob = trim($_POST['DateOfBirth']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);
    $enrollment_date = date('Y-m-d');

    if ($fname && $lname && $gender && $dob && $email && $phone && $address) {
        $stmt = $conn->prepare("INSERT INTO students (FirstName, LastName, Gender, DateOfBirth, Email, Phone, Address, EnrollmentDate) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fname, $lname, $gender, $dob, $email, $phone, $address, $enrollment_date);
        if ($stmt->execute()) {
            $student_added = true;
        }
        $stmt->close();
    }
}

// Update student
if (isset($_POST['edit_student'])) {
    $id = intval($_POST['id']);
    $fname = trim($_POST['FirstName']);
    $lname = trim($_POST['LastName']);
    $gender = trim($_POST['Gender']);
    $dob = trim($_POST['DateOfBirth']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);

    $stmt = $conn->prepare("UPDATE students 
                            SET FirstName=?, LastName=?, Gender=?, DateOfBirth=?, Email=?, Phone=?, Address=? 
                            WHERE id=?");
    $stmt->bind_param("sssssssi", $fname, $lname, $gender, $dob, $email, $phone, $address, $id);
    if ($stmt->execute()) {
        $student_updated = true;
    }
    $stmt->close();
}

// Delete student
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $student_deleted = true;
    }
    $stmt->close();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students");
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
        <td>
          <button class="btn" style="background:green; color:white;" 
            onclick='openEditForm(<?= json_encode($row) ?>)'>Edit</button>
          <a href="admin_page.php?page=students&delete=<?= $row['id'] ?>" 
             onclick="return confirmDelete(event)" 
             class="btn" style="background:red; color:white; padding:5px 10px; text-decoration:none;">
             Delete
          </a>
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

// Open Add Student Form
function openAddForm() {
  Swal.fire({
    title: 'Add New Student',
    html: `
      <form id="addForm" method="POST">
        <input type="hidden" name="add_student" value="1">
        <input type="text" name="FirstName" placeholder="First Name" class="swal2-input" required>
        <input type="text" name="LastName" placeholder="Last Name" class="swal2-input" required>
        <select name="Gender" class="swal2-input" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <input type="date" name="DateOfBirth" class="swal2-input" required>
        <input type="email" name="Email" placeholder="Email" class="swal2-input" required>
        <input type="text" name="Phone" placeholder="Phone" class="swal2-input" required>
        <input type="text" name="Address" placeholder="Address" class="swal2-input" required>
      </form>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Add Student',
    preConfirm: () => {
      document.getElementById('addForm').submit();
    }
  });
}

// Open Edit Student Form
function openEditForm(student) {
  Swal.fire({
    title: 'Edit Student',
    html: `
      <form id="editForm" method="POST">
        <input type="hidden" name="edit_student" value="1">
        <input type="hidden" name="id" value="${student.id}">
        <input type="text" name="FirstName" value="${student.FirstName}" class="swal2-input" required>
        <input type="text" name="LastName" value="${student.LastName}" class="swal2-input" required>
        <select name="Gender" class="swal2-input" required>
          <option value="Male" ${student.Gender === 'Male' ? 'selected' : ''}>Male</option>
          <option value="Female" ${student.Gender === 'Female' ? 'selected' : ''}>Female</option>
        </select>
        <input type="date" name="DateOfBirth" value="${student.DateOfBirth}" class="swal2-input" required>
        <input type="email" name="Email" value="${student.Email}" class="swal2-input" required>
        <input type="text" name="Phone" value="${student.Phone}" class="swal2-input" required>
        <input type="text" name="Address" value="${student.Address}" class="swal2-input" required>
      </form>`,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Save Changes',
    preConfirm: () => {
      document.getElementById('editForm').submit();
    }
  });
}
</script>

<?php if ($student_added): ?>
<script>
Swal.fire({ icon: 'success', title: 'Student Added', text: 'The student has been added successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($student_updated): ?>
<script>
Swal.fire({ icon: 'success', title: 'Student Updated', text: 'The student has been updated successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>

<?php if ($student_deleted): ?>
<script>
Swal.fire({ icon: 'success', title: 'Student Deleted', text: 'The student has been removed successfully!', confirmButtonColor: '#1a237e' });
</script>
<?php endif; ?>
