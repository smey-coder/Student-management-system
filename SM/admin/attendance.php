<?php
require_once "database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$attendance_added = false;
$attendance_updated = false;
$attendance_deleted = false;

// Fetch all students and courses for dropdowns
$students_result = $conn->query("SELECT id, FirstName, LastName, Email, Phone FROM students ORDER BY FirstName, LastName");
$courses_result = $conn->query("SELECT id, CourseName as name FROM courses ORDER BY CourseName");

$students = [];
while($s = $students_result->fetch_assoc()) $students[] = $s;

$courses = [];
while($c = $courses_result->fetch_assoc()) $courses[] = $c;

// Handle Individual QR Code Generation
if (isset($_GET['generate_qr'])) {
    $student_id = intval($_GET['generate_qr']);
    generateStudentQRCode($student_id);
}

// Handle Bulk QR Download
if (isset($_GET['download_all_qr'])) {
    downloadAllStudentQRCodes();
}

// Handle Student QR Cards Page
if (isset($_GET['view_qr_cards'])) {
    displayAllStudentQRCards();
}

// Add Attendance
if (isset($_POST['add_attendance'])) {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $date = $_POST['attendance_date'];
    $status = $_POST['status'];

    if ($student_id && $course_id && $date && $status) {
        // Check if attendance already exists
        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND course_id = ? AND attendance_date = ?");
        $check_stmt->bind_param("iis", $student_id, $course_id, $date);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $student_id, $course_id, $date, $status);
            if ($stmt->execute()) $attendance_added = true;
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Edit Attendance
if (isset($_POST['edit_attendance'])) {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    
    $id = intval($_POST['attendance_id']);
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $date = $_POST['attendance_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE attendance SET student_id=?, course_id=?, attendance_date=?, status=? WHERE id=?");
    $stmt->bind_param("iissi", $student_id, $course_id, $date, $status, $id);
    if ($stmt->execute()) $attendance_updated = true;
    $stmt->close();
}

// Delete Attendance
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM attendance WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) $attendance_deleted = true;
    $stmt->close();
}

// Fetch all attendance records with proper ordering
$result = $conn->query("SELECT a.id, s.FirstName, s.LastName, c.CourseName, a.attendance_date, a.status, a.student_id, a.course_id
                        FROM attendance a
                        JOIN students s ON a.student_id = s.id
                        JOIN courses c ON a.course_id = c.id
                        ORDER BY a.attendance_date DESC, s.FirstName, s.LastName");

// Individual Student QR Code Generation Function
function generateStudentQRCode($student_id) {
    global $conn;
    
    // Fetch student details
    $stmt = $conn->prepare("SELECT id, FirstName, LastName, StudentID, Email, Phone FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        
        // Generate QR code using Google Charts API
        $qrData = $student['id']; // Using student ID as QR data
        $qrUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . $qrData . "&choe=UTF-8";
        
        // Display QR code in a beautiful layout
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code for {$student['FirstName']} {$student['LastName']}</title>
            <style>
                body { 
                    text-align: center; 
                    font-family: 'Arial', sans-serif; 
                    padding: 40px; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                }
                .qr-container { 
                    background: white; 
                    margin: 20px auto; 
                    padding: 40px; 
                    border-radius: 20px; 
                    display: inline-block;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    max-width: 500px;
                    text-align: center;
                }
                .student-info { 
                    margin: 25px 0; 
                    font-size: 18px; 
                    color: #333;
                    text-align: left;
                    display: inline-block;
                }
                .info-item {
                    margin: 10px 0;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                .student-name {
                    font-size: 28px;
                    font-weight: bold;
                    color: #1a237e;
                    margin-bottom: 20px;
                    text-align: center;
                    border-bottom: 3px solid #1a237e;
                    padding-bottom: 15px;
                }
                .btn { 
                    background: #1a237e; 
                    color: white; 
                    padding: 12px 25px; 
                    border: none; 
                    border-radius: 8px; 
                    cursor: pointer; 
                    margin: 10px; 
                    text-decoration: none; 
                    display: inline-block;
                    font-size: 16px;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    background: #303f9f;
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
                .btn-print {
                    background: #4caf50;
                }
                .btn-print:hover {
                    background: #45a049;
                }
                .qr-image {
                    border: 3px solid #1a237e;
                    border-radius: 15px;
                    padding: 15px;
                    background: white;
                    margin: 20px auto;
                }
            </style>
        </head>
        <body>
            <div class='qr-container'>
                <h1 style='color: #1a237e; margin-bottom: 30px;'>Student QR Code</h1>
                
                <div class='qr-image'>
                    <img src='{$qrUrl}' alt='QR Code for {$student['FirstName']}' width='300' height='300'>
                </div>
                
                <div class='student-info'>
                    <div class='student-name'>{$student['FirstName']} {$student['LastName']}</div>
                    <div class='info-item'><strong>Student ID:</strong> {$student['StudentID']}</div>
                    <div class='info-item'><strong>Database ID:</strong> {$student['id']}</div>
                    <div class='info-item'><strong>Email:</strong> " . ($student['Email'] ?: 'N/A') . "</div>
                    <div class='info-item'><strong>Phone:</strong> " . ($student['Phone'] ?: 'N/A') . "</div>
                </div>
                
                <div style='margin-top: 30px;'>
                    <button class='btn btn-print' onclick='window.print()'>🖨️ Print QR Code</button>
                    <a href='attendance.php' class='btn'>← Back to Attendance</a>
                </div>
                
                <div style='margin-top: 20px; color: #666; font-size: 14px;'>
                    <p><strong>Usage:</strong> Show this QR code to the camera when marking attendance</p>
                </div>
            </div>
        </body>
        </html>";
        exit;
    } else {
        echo "<script>alert('Student not found!'); window.location.href='attendance.php';</script>";
    }
}

// Bulk QR Codes Download Function
function downloadAllStudentQRCodes() {
    global $conn;
    
    // Fetch all students
    $students_result = $conn->query("SELECT id, FirstName, LastName, StudentID FROM students ORDER BY FirstName, LastName");
    
    // Create HTML page with all QR codes
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>All Student QR Codes</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background: #f5f5f5; 
            }
            .container { 
                max-width: 1200px; 
                margin: 0 auto; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                background: white; 
                padding: 30px; 
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            .qr-grid { 
                display: grid; 
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
                gap: 25px; 
                margin-bottom: 30px; 
            }
            .qr-card { 
                background: white; 
                border-radius: 15px; 
                padding: 20px; 
                text-align: center; 
                break-inside: avoid; 
                border: 1px solid #e0e0e0;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
            }
            .qr-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            }
            .qr-image { 
                width: 180px; 
                height: 180px; 
                margin: 0 auto 15px; 
                border: 2px solid #1a237e;
                border-radius: 10px;
                padding: 10px;
                background: white;
            }
            .student-name { 
                font-weight: bold; 
                color: #1a237e; 
                margin-bottom: 8px; 
                font-size: 16px; 
            }
            .student-id { 
                color: #666; 
                font-size: 14px; 
                margin-bottom: 10px; 
            }
            .action-buttons { 
                text-align: center; 
                margin: 30px 0; 
            }
            .btn { 
                background: #1a237e; 
                color: white; 
                padding: 12px 25px; 
                border: none; 
                border-radius: 8px; 
                cursor: pointer; 
                margin: 10px; 
                text-decoration: none; 
                display: inline-block;
                font-size: 16px;
            }
            .btn-print {
                background: #4caf50;
            }
            @media print { 
                body { background: white; } 
                .action-buttons { display: none; } 
                .qr-card { 
                    border: 2px solid #000; 
                    margin-bottom: 20px;
                    box-shadow: none;
                }
                .qr-grid { 
                    grid-template-columns: repeat(3, 1fr);
                    gap: 15px;
                }
                .header {
                    box-shadow: none;
                    border: 2px solid #000;
                }
            }
            .search-box {
                padding: 12px;
                width: 100%;
                max-width: 400px;
                border: 2px solid #ddd;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='color: #1a237e; margin-bottom: 10px;'>All Student QR Codes</h1>
                <p style='color: #666; font-size: 16px;'>Generated on " . date('F j, Y') . " - " . $students_result->num_rows . " students</p>
            </div>
            
            <div class='action-buttons'>
                <button class='btn btn-print' onclick='window.print()'>🖨️ Print All Cards</button>
                <a href='attendance.php' class='btn'>← Back to Attendance</a>
            </div>
            
            <div class='qr-grid'>";
    
    while ($student = $students_result->fetch_assoc()) {
        $qrUrl = "https://chart.googleapis.com/chart?chs=180x180&cht=qr&chl=" . $student['id'] . "&choe=UTF-8";
        echo "
                <div class='qr-card'>
                    <div class='qr-image'>
                        <img src='{$qrUrl}' alt='QR Code for {$student['FirstName']}' width='180' height='180'>
                    </div>
                    <div class='student-info'>
                        <div class='student-name'>{$student['FirstName']} {$student['LastName']}</div>
                        <div class='student-id'>ID: {$student['StudentID']}</div>
                        <div style='margin-top: 10px;'>
                            <a href='attendance.php?generate_qr={$student['id']}' class='btn' style='padding: 8px 15px; font-size: 14px;'>
                                View Large QR
                            </a>
                        </div>
                    </div>
                </div>";
    }
    
    echo "
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Display All Student QR Cards in Grid
function displayAllStudentQRCards() {
    global $conn;
    
    // Fetch all students
    $students_result = $conn->query("SELECT id, FirstName, LastName, StudentID, Email FROM students ORDER BY FirstName, LastName");
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Student QR Cards</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                background: #f5f5f5; 
            }
            .container { 
                max-width: 1400px; 
                margin: 0 auto; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                background: white; 
                padding: 30px; 
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            .filters {
                background: white;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 25px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                text-align: center;
            }
            .qr-grid { 
                display: grid; 
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
                gap: 25px; 
                margin-bottom: 40px; 
            }
            .qr-card { 
                background: white; 
                border-radius: 15px; 
                padding: 25px; 
                text-align: center; 
                break-inside: avoid; 
                border: 1px solid #e0e0e0;
                box-shadow: 0 3px 15px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
            }
            .qr-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }
            .qr-image { 
                width: 200px; 
                height: 200px; 
                margin: 0 auto 20px; 
                border: 3px solid #1a237e;
                border-radius: 12px;
                padding: 12px;
                background: white;
            }
            .student-name { 
                font-weight: bold; 
                color: #1a237e; 
                margin-bottom: 8px; 
                font-size: 18px; 
            }
            .student-id { 
                color: #666; 
                font-size: 14px; 
                margin-bottom: 5px; 
            }
            .student-email {
                color: #888;
                font-size: 12px;
                margin-bottom: 15px;
            }
            .action-buttons { 
                text-align: center; 
                margin: 30px 0; 
            }
            .btn { 
                background: #1a237e; 
                color: white; 
                padding: 12px 25px; 
                border: none; 
                border-radius: 8px; 
                cursor: pointer; 
                margin: 8px; 
                text-decoration: none; 
                display: inline-block;
                font-size: 16px;
                transition: all 0.3s ease;
            }
            .btn:hover {
                background: #303f9f;
                transform: translateY(-2px);
            }
            .btn-print {
                background: #4caf50;
            }
            .btn-download {
                background: #2196f3;
            }
            .btn-qr {
                background: #9c27b0;
            }
            .search-box {
                padding: 12px 20px;
                width: 100%;
                max-width: 500px;
                border: 2px solid #ddd;
                border-radius: 8px;
                margin-bottom: 20px;
                font-size: 16px;
                transition: border-color 0.3s ease;
            }
            .search-box:focus {
                border-color: #1a237e;
                outline: none;
            }
            @media print { 
                body { background: white; } 
                .filters, .action-buttons { display: none; } 
                .qr-card { 
                    border: 2px solid #000; 
                    margin-bottom: 20px;
                    box-shadow: none;
                }
                .qr-grid { 
                    grid-template-columns: repeat(3, 1fr);
                    gap: 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='color: #1a237e; margin-bottom: 10px;'>Student QR Code Cards</h1>
                <p style='color: #666; font-size: 16px;'>Printable QR codes for student attendance - " . $students_result->num_rows . " students</p>
            </div>

            <div class='filters'>
                <input type='text' id='searchStudent' class='search-box' placeholder='🔍 Search students by name or ID...'>
                <div class='action-buttons'>
                    <button class='btn btn-print' onclick='window.print()'>🖨️ Print All Cards</button>
                    <a href='attendance.php?download_all_qr=1' class='btn btn-download'>📥 Download All QR Codes</a>
                    <a href='attendance.php' class='btn'>← Back to Attendance</a>
                </div>
            </div>
            
            <div class='qr-grid' id='qrGrid'>";
    
    while ($student = $students_result->fetch_assoc()) {
        $qrUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . $student['id'] . "&choe=UTF-8";
        $emailDisplay = $student['Email'] ?: 'No email';
        echo "
                <div class='qr-card' data-name='{$student['FirstName']} {$student['LastName']}' data-id='{$student['StudentID']}'>
                    <div class='qr-image'>
                        <img src='{$qrUrl}' alt='QR Code for {$student['FirstName']}' width='200' height='200'>
                    </div>
                    <div class='student-info'>
                        <div class='student-name'>{$student['FirstName']} {$student['LastName']}</div>
                        <div class='student-id'>Student ID: {$student['StudentID']}</div>
                        <div class='student-email'>{$emailDisplay}</div>
                        <div style='margin-top: 15px;'>
                            <a href='attendance.php?generate_qr={$student['id']}' class='btn' style='padding: 8px 16px; font-size: 14px; background: #9c27b0;'>
                                📱 Large QR
                            </a>
                        </div>
                    </div>
                </div>";
    }
    
    echo "
            </div>
        </div>

        <script>
            // Search functionality
            document.getElementById('searchStudent').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const cards = document.querySelectorAll('.qr-card');
                
                cards.forEach(card => {
                    const name = card.getAttribute('data-name').toLowerCase();
                    const id = card.getAttribute('data-id').toLowerCase();
                    
                    if (name.includes(searchTerm) || id.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        </script>
    </body>
    </html>";
    exit;
}
?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- html5-qrcode -->
<script src="https://unpkg.com/html5-qrcode"></script>

<div class="card">
  <h2>Attendance Management</h2>

  <!-- Action Buttons -->
  <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
    <button class="btn" style="background:#4caf50; color:white;" onclick="openQrScanner()">
      📱 Scan Student QR
    </button>
    <button class="btn" style="background:#1a237e; color:white;" onclick="openAddAttendanceForm()">
      ➕ Add Attendance
    </button>
    <button class="btn" style="background:#9c27b0; color:white;" onclick="openQRManagement()">
      📋 Student QR Codes
    </button>
    <a href="attendance.php?view_qr_cards=1" class="btn" style="background:#ff9800; color:white; text-decoration: none;">
      🎴 View QR Cards
    </a>
    <a href="attendance.php?download_all_qr=1" class="btn" style="background:#2196f3; color:white; text-decoration: none;">
      📥 Download All QR
    </a>
  </div>

  <!-- Attendance Table -->
  <div style="overflow-x: auto;">
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; margin-top:15px; border-collapse: collapse;">
      <thead>
        <tr style="background:#1a237e; color:white;">
          <th>ID</th>
          <th>Student</th>
          <th>Course</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr style="background: #f9f9f9;">
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td>
              <div style="display: flex; align-items: center; gap: 10px;">
                <div>
                  <strong><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></strong>
                  <br>
                  <small style="color: #666;">ID: <?= $row['student_id'] ?></small>
                </div>
                <a href="attendance.php?generate_qr=<?= $row['student_id'] ?>" 
                   style="color: #9c27b0; text-decoration: none; font-size: 12px; border: 1px solid #9c27b0; padding: 2px 8px; border-radius: 4px;"
                   title="Get Student QR Code">
                  📱 QR
                </a>
              </div>
            </td>
            <td><?= htmlspecialchars($row['CourseName']) ?></td>
            <td><?= htmlspecialchars($row['attendance_date']) ?></td>
            <td>
              <span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; 
                  <?= $row['status'] == 'Present' ? 'background:#4caf50; color:white;' : 'background:#f44336; color:white;' ?>">
                <?= htmlspecialchars($row['status']) ?>
              </span>
            </td>
            <td>
              <button onclick="openEditAttendanceForm(<?= $row['id'] ?>, <?= $row['student_id'] ?>, <?= $row['course_id'] ?>, '<?= $row['attendance_date'] ?>', '<?= $row['status'] ?>')" 
                      style="background:orange; color:white; padding:5px 10px; border:none; border-radius:3px; cursor:pointer; margin-right:5px;">
                Edit
              </button>
              <a href="attendance.php?delete=<?= $row['id'] ?>" 
                 onclick="return confirmDelete(event)" 
                 style="background:red; color:white; padding:5px 10px; text-decoration:none; border-radius:3px;">
                Delete
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
              No attendance records found. Add your first attendance record above.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Hidden Forms for submission -->
<form id="addAttendanceForm" method="POST" style="display:none;">
  <input type="hidden" name="add_attendance" value="1">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <input type="number" name="student_id" id="addStudent">
  <input type="number" name="course_id" id="addCourse">
  <input type="date" name="attendance_date" id="addDate">
  <select name="status" id="addStatus">
    <option value="Present">Present</option>
    <option value="Absent">Absent</option>
  </select>
</form>

<form id="editAttendanceForm" method="POST" style="display:none;">
  <input type="hidden" name="edit_attendance" value="1">
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <input type="hidden" name="attendance_id" id="editAttendanceId">
  <input type="number" name="student_id" id="editStudent">
  <input type="number" name="course_id" id="editCourse">
  <input type="date" name="attendance_date" id="editDate">
  <select name="status" id="editStatus">
    <option value="Present">Present</option>
    <option value="Absent">Absent</option>
  </select>
</form>

<script>
// Prepare JS arrays for dropdowns
const students = <?= json_encode($students) ?>;
const courses = <?= json_encode($courses) ?>;

// Set default date to today
const today = new Date().toISOString().split('T')[0];

// [Previous JavaScript functions remain the same: openAddAttendanceForm, openEditAttendanceForm, confirmDelete, openQrScanner]

// Open Add Attendance form
function openAddAttendanceForm(studentIdFromQR = null) {
  let studentOptions = students.map(s => 
    `<option value="${s.id}" ${studentIdFromQR && s.id == studentIdFromQR ? 'selected' : ''}>${s.FirstName} ${s.LastName} (ID: ${s.StudentID})</option>`
  ).join('');
  
  let courseOptions = courses.map(c => 
    `<option value="${c.id}">${c.name}</option>`
  ).join('');

  Swal.fire({
    title: 'Add Attendance Record',
    html:
      `<label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Student:</label>
       <select id="swalStudent" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;">${studentOptions}</select>
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Course:</label>
       <select id="swalCourse" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;">${courseOptions}</select>
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Date:</label>
       <input id="swalDate" type="date" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;" value="${today}">
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Status:</label>
       <select id="swalStatus" class="swal2-input" style="display:block; width:100%;">
         <option value="Present">Present</option>
         <option value="Absent">Absent</option>
       </select>`,
    showCancelButton: true,
    confirmButtonText: 'Save Attendance',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#1a237e',
    width: '500px',
    preConfirm: () => {
      const student = document.getElementById('swalStudent').value;
      const course = document.getElementById('swalCourse').value;
      const date = document.getElementById('swalDate').value;
      const status = document.getElementById('swalStatus').value;
      
      if (!student || !course || !date || !status) {
        Swal.showValidationMessage('Please fill all fields');
        return false;
      }
      
      document.getElementById('addStudent').value = student;
      document.getElementById('addCourse').value = course;
      document.getElementById('addDate').value = date;
      document.getElementById('addStatus').value = status;
      document.getElementById('addAttendanceForm').submit();
    }
  });
}

// Open Edit Attendance form
function openEditAttendanceForm(id, studentId, courseId, date, status) {
  let studentOptions = students.map(s => 
    `<option value="${s.id}" ${s.id == studentId ? 'selected' : ''}>${s.FirstName} ${s.LastName} (ID: ${s.StudentID})</option>`
  ).join('');
  
  let courseOptions = courses.map(c => 
    `<option value="${c.id}" ${c.id == courseId ? 'selected' : ''}>${c.name}</option>`
  ).join('');

  Swal.fire({
    title: 'Edit Attendance Record',
    html:
      `<label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Student:</label>
       <select id="swalEditStudent" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;">${studentOptions}</select>
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Course:</label>
       <select id="swalEditCourse" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;">${courseOptions}</select>
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Date:</label>
       <input id="swalEditDate" type="date" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;" value="${date}">
       
       <label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Status:</label>
       <select id="swalEditStatus" class="swal2-input" style="display:block; width:100%;">
         <option value="Present" ${status === 'Present' ? 'selected' : ''}>Present</option>
         <option value="Absent" ${status === 'Absent' ? 'selected' : ''}>Absent</option>
       </select>`,
    showCancelButton: true,
    confirmButtonText: 'Update Attendance',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#1a237e',
    width: '500px',
    preConfirm: () => {
      const student = document.getElementById('swalEditStudent').value;
      const course = document.getElementById('swalEditCourse').value;
      const date = document.getElementById('swalEditDate').value;
      const status = document.getElementById('swalEditStatus').value;
      
      if (!student || !course || !date || !status) {
        Swal.showValidationMessage('Please fill all fields');
        return false;
      }
      
      document.getElementById('editAttendanceId').value = id;
      document.getElementById('editStudent').value = student;
      document.getElementById('editCourse').value = course;
      document.getElementById('editDate').value = date;
      document.getElementById('editStatus').value = status;
      document.getElementById('editAttendanceForm').submit();
    }
  });
}

// Open QR Management Modal
function openQRManagement() {
  let studentOptions = students.map(s => 
    `<option value="${s.id}">${s.FirstName} ${s.LastName} (ID: ${s.StudentID})</option>`
  ).join('');

  Swal.fire({
    title: 'Generate Student QR Code',
    html:
      `<label style="display:block; text-align:left; margin-bottom:5px; font-weight:bold;">Select Student:</label>
       <select id="swalQRStudent" class="swal2-input" style="display:block; width:100%; margin-bottom:15px;">
         <option value="">-- Select a Student --</option>
         ${studentOptions}
       </select>
       <div style="text-align: center; margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
         <small>This will generate a printable QR code for the selected student</small>
       </div>`,
    showCancelButton: true,
    confirmButtonText: 'Generate QR Code',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#9c27b0',
    width: '500px',
    preConfirm: () => {
      const studentId = document.getElementById('swalQRStudent').value;
      if (!studentId) {
        Swal.showValidationMessage('Please select a student');
        return false;
      }
      window.location.href = `attendance.php?generate_qr=${studentId}`;
    }
  });
}

// Confirm Delete
function confirmDelete(e) {
  e.preventDefault();
  let url = e.currentTarget.getAttribute("href");
  Swal.fire({
    title: "Are you sure?",
    text: "This attendance record will be permanently deleted!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel"
  }).then((result) => { 
    if (result.isConfirmed) { 
      window.location.href = url; 
    }
  });
}

// QR Scanner Function
function openQrScanner() {
  Swal.fire({
    title: 'Scan Student QR Code',
    html: `
      <div style="text-align: center;">
        <div id="qr-reader" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
        <div id="qr-error" style="color: red; margin-top: 10px; font-weight: bold;"></div>
        <div style="margin-top: 10px; color: #666; font-size: 14px;">
          Point your camera at the student's QR code
        </div>
      </div>
    `,
    showCancelButton: true,
    showConfirmButton: false,
    cancelButtonText: 'Close Scanner',
    width: '500px',
    willOpen: () => {
      const qrScanner = new Html5Qrcode("qr-reader");
      const errorDiv = document.getElementById('qr-error');
      
      qrScanner.start(
        { facingMode: "environment" },
        { 
          fps: 10, 
          qrbox: { width: 250, height: 250 },
          aspectRatio: 1.0
        },
        (decodedText) => {
          // Validate if decoded text is a valid student ID
          const studentId = parseInt(decodedText);
          const isValidStudent = students.find(s => s.id == studentId);
          
          if (!studentId || !isValidStudent) {
            errorDiv.textContent = 'Invalid QR code. Please scan a valid student QR code.';
            return;
          }
          
          qrScanner.stop().then(() => {
            Swal.close();
            // Show success message before opening form
            Swal.fire({
              icon: 'success',
              title: 'QR Scanned Successfully!',
              text: `Student: ${isValidStudent.FirstName} ${isValidStudent.LastName}`,
              confirmButtonColor: '#1a237e',
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              openAddAttendanceForm(studentId);
            });
          }).catch(err => {
            console.error('QR scanner stop error:', err);
            errorDiv.textContent = 'Scanner error. Please try again.';
          });
        },
        (errorMessage) => {
          // Silent error handling - don't show every minor error
        }
      ).catch(err => {
        errorDiv.textContent = 'Cannot access camera. Please check permissions.';
        console.error('QR Scanner initialization error:', err);
      });
      
      // Store scanner instance to stop it when modal closes
      Swal.getPopup().setAttribute('data-qr-scanner', qrScanner);
    },
    willClose: () => {
      const scanner = Swal.getPopup().getAttribute('data-qr-scanner');
      if (scanner) {
        scanner.stop().catch(err => console.error('Error stopping scanner:', err));
      }
    }
  });
}

// Success notifications
<?php if($attendance_added): ?>
Swal.fire({
  icon: 'success',
  title: 'Attendance Added',
  text: 'Attendance record has been added successfully',
  confirmButtonColor: '#1a237e'
});
<?php endif; ?>

<?php if($attendance_updated): ?>
Swal.fire({
  icon: 'success',
  title: 'Attendance Updated',
  text: 'Attendance record has been updated successfully',
  confirmButtonColor: '#1a237e'
});
<?php endif; ?>

<?php if($attendance_deleted): ?>
Swal.fire({
  icon: 'success',
  title: 'Attendance Deleted',
  text: 'Attendance record has been deleted successfully',
  confirmButtonColor: '#1a237e'
});
<?php endif; ?>
</script>