<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php"; // $conn = mysqli connection

// ---------- CHECK LOGIN ----------
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // redirect to login if not logged in
    exit();
}

$student_id = intval($_SESSION['id']);
$message = '';
$message_class = '';

// ---------- VERIFY STUDENT EXISTS AND GET DETAILS ----------
$student_check = $conn->prepare("SELECT id, email FROM students WHERE id = ?");
$student_check->bind_param("i", $student_id);
$student_check->execute();
$student_result = $student_check->get_result();

if ($student_result->num_rows === 0) {
    // If student doesn't exist, destroy session and redirect
    session_destroy();
    header("Location: login.php?error=invalid_student");
    exit();
}

$student_data = $student_result->fetch_assoc();
$student_check->close();

// ---------- HANDLE SUBMISSION ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    
    // Students can only submit requests as 'pending'
    $status = 'pending';

    if ($title !== '' && $body !== '') {
        // Validate input length
        if (strlen($title) > 100) {
            $message = "❌ Title must be 100 characters or less.";
            $message_class = "alert";
        } elseif (strlen($body) > 1000) {
            $message = "❌ Request details must be 1000 characters or less.";
            $message_class = "alert";
        } else {
            // Debug: Check what student_id we're using
            error_log("Attempting to insert request for student_id: " . $student_id);
            
            $stmt = $conn->prepare(
                "INSERT INTO requests (student_id, request_type, request_detail, status, created_date)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param("isss", $student_id, $title, $body, $status);

            if ($stmt->execute()) {
                $message = "✅ Request submitted successfully!";
                $message_class = "success";
                // Clear form
                $title = $body = '';
            } else {
                $error_message = $conn->error;
                $message = "❌ Error submitting request: " . htmlspecialchars($error_message);
                $message_class = "alert";
                
                // Log detailed error for debugging
                error_log("MySQL Error: " . $error_message);
                error_log("Student ID: " . $student_id);
                error_log("Title: " . $title);
            }
            $stmt->close();
        }
    } else {
        $message = "⚠ Please fill in all required fields.";
        $message_class = "alert";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit Request | Student Portal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --secondary: #7209b7;
    --accent: #f72585;
    --success: #4cc9f0;
    --warning: #f8961e;
    --danger: #e63946;
    --dark: #1a1a2e;
    --light: #f8f9fa;
    --gray: #6c757d;
    --border: #e2e8f0;
    --sidebar-width: 280px;
    --header-height: 70px;
    --border-radius: 12px;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* ===== RESET & BASE STYLES ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    color: #333;
    line-height: 1.6;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* ===== SIDEBAR STYLES ===== */
.sidebar {
    width: var(--sidebar-width);
    background: linear-gradient(180deg, var(--dark) 0%, #16213e 100%);
    color: white;
    padding: 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-header {
    padding: 30px 25px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.sidebar-header h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(45deg, var(--primary), var(--success));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.8);
}

.user-info::before {
    content: "👋";
    font-size: 16px;
}

.sidebar ul {
    list-style: none;
    padding: 20px 0;
}

.sidebar li {
    margin: 5px 15px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.sidebar li:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}

.sidebar li.active {
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    box-shadow: var(--shadow);
}

.sidebar li a {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.sidebar li.active a {
    color: white;
    font-weight: 600;
}

.sidebar li a i {
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.sidebar li:last-child {
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 20px;
}

.sidebar li:last-child a {
    color: var(--accent);
}

/* ===== MAIN CONTENT STYLES ===== */
.main-content {
    flex: 1;
    margin-left: var(--sidebar-width);
    padding: 30px;
    background: rgba(126, 174, 222, 0.95);
    min-height: 100vh;
    width: 1250px;
}

/* ===== PAGE HEADER STYLES ===== */
.page-header {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    border-left: 4px solid var(--primary);
}

.page-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 8px;
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-header p {
    color: var(--gray);
    font-size: 16px;
    margin: 0;
}

/* ===== REQUEST FORM STYLES ===== */
.student-info {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 25px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    box-shadow: var(--shadow);
    border: none;
}

.student-info h3 {
    font-size: 18px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.student-info h3::before {
    content: "👤";
    font-size: 20px;
}

.student-info p {
    margin: 8px 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.student-info strong {
    min-width: 80px;
    display: inline-block;
}

.request-form {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark);
    font-size: 14px;
}

label.required::after {
    content: " *";
    color: var(--danger);
}

input, textarea, select {
    width: 100%;
    padding: 15px;
    border: 2px solid var(--border);
    border-radius: var(--border-radius);
    font-size: 16px;
    transition: all 0.3s ease;
    background: white;
    font-family: inherit;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    transform: translateY(-2px);
}

textarea {
    resize: vertical;
    min-height: 120px;
}

.char-counter {
    text-align: right;
    font-size: 12px;
    color: var(--gray);
    margin-top: 5px;
}

/* ===== BUTTON STYLES ===== */
button {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    border: none;
    padding: 18px 35px;
    border-radius: var(--border-radius);
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 10px;
    width: 100%;
    box-shadow: var(--shadow);
}

button:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
}

button:active {
    transform: translateY(-1px);
}

button i {
    font-size: 18px;
}

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
}

.stat-info h3 {
    font-size: 14px;
    color: var(--gray);
    margin-bottom: 5px;
    font-weight: 600;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--dark);
    display: block;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: var(--gray);
    font-weight: 500;
}

/* ===== ALERT MESSAGES ===== */
.alert, .success {
    padding: 20px;
    border-radius: var(--border-radius);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: 500;
    box-shadow: var(--shadow);
}

.alert {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.success {
    background: #dcfce7;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* ===== FORM FOOTER ===== */
.form-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid var(--border);
}

.form-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.form-footer a:hover {
    color: var(--secondary);
    transform: translateX(3px);
}

/* ===== PAGE NOT FOUND ===== */
.page-not-found {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.page-not-found h1 {
    font-size: 48px;
    color: var(--dark);
    margin-bottom: 20px;
}

.page-not-found p {
    font-size: 18px;
    color: var(--gray);
    margin-bottom: 10px;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 1024px) {
    .sidebar {
        width: 250px;
    }
    
    .main-content {
        margin-left: 250px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        padding: 20px;
    }
    
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-header {
        padding: 20px;
    }
    
    .sidebar ul {
        padding: 15px 0;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 15px;
    }
    
    .page-header {
        padding: 20px;
    }
    
    .request-form {
        padding: 20px;
    }
    
    .student-info {
        padding: 20px;
    }
    
    .stat-card {
        padding: 20px;
        flex-direction: column;
        text-align: center;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.main-content > * {
    animation: fadeIn 0.5s ease-out;
}

/* ===== SCROLLBAR STYLING ===== */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}
.img {
    width: 150px;
    height: 120px;
    padding: 0; /* or specific values like 5px */
    display: block;
    margin: auto; /* This centers the image */
}
.avatar-img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto;
}
</style>
</head>
<body>
<div class="dashboard-container">
  <div class="card">
    <div class="card-header">
      <div class="card-header-content">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Request Icon" class="img">
        <h1>Submit Request</h1>
        <p>Send your requests or messages to the admin</p>
      </div>
    </div>
    <div class="card-body">
      <!-- Student Information -->
      <div class="student-info">
        <h3>Student Information</h3>
        <p><strong>ID:</strong> <?= htmlspecialchars($student_data['id']) ?></p>
        <!-- <p><strong>Name:</strong> <?= htmlspecialchars($student_data['FistName']) ?></p> -->
        <p><strong>Email:</strong> <?= htmlspecialchars($student_data['email']) ?></p>
      </div>

      <?php if ($message): ?>
        <div class="<?= htmlspecialchars($message_class) ?>">
          <i class="<?= $message_class === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle' ?>"></i>
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="request-form" novalidate>
        <div class="form-group">
          <label for="title" class="required">Request Title</label>
          <input type="text" name="title" id="title" maxlength="100" required 
                 value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
                 placeholder="Enter a brief title for your request">
          <div class="char-counter"><span id="title-counter">0</span>/100</div>
        </div>

        <div class="form-group">
          <label for="body" class="required">Request Details</label>
          <textarea name="body" id="body" rows="5" maxlength="1000" required 
                    placeholder="Please provide detailed information about your request"><?= isset($_POST['body']) ? htmlspecialchars($_POST['body']) : '' ?></textarea>
          <div class="char-counter"><span id="body-counter">0</span>/1000</div>
        </div>

        <button type="submit" id="submit-btn">
          <i class="fas fa-paper-plane"></i>
          <span id="submit-text">Submit Request</span>
        </button>
      </form>
      
      <div class="form-footer">
        <p>Need assistance? <a href="help.php"><i class="fas fa-life-ring"></i> Contact Support</a></p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const bodyTextarea = document.getElementById('body');
    const titleCounter = document.getElementById('title-counter');
    const bodyCounter = document.getElementById('body-counter');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.querySelector('.request-form');

    // Initialize counters
    titleCounter.textContent = titleInput.value.length;
    bodyCounter.textContent = bodyTextarea.value.length;

    // Update character counters
    titleInput.addEventListener('input', function() {
        titleCounter.textContent = this.value.length;
    });

    bodyTextarea.addEventListener('input', function() {
        bodyCounter.textContent = this.value.length;
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const title = titleInput.value.trim();
        const body = bodyTextarea.value.trim();

        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // Validate title
        if (title === '') {
            showError(titleInput, 'Title is required');
            isValid = false;
        } else if (title.length > 100) {
            showError(titleInput, 'Title must be 100 characters or less');
            isValid = false;
        }

        // Validate body
        if (body === '') {
            showError(bodyTextarea, 'Request details are required');
            isValid = false;
        } else if (body.length > 1000) {
            showError(bodyTextarea, 'Request details must be 1000 characters or less');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    function showError(input, message) {
        input.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#dc2626';
        errorDiv.style.fontSize = '14px';
        errorDiv.style.marginTop = '5px';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        input.parentNode.appendChild(errorDiv);
    }

    // Add error styling to CSS
    const style = document.createElement('style');
    style.textContent = `
        .error {
            border-color: #dc2626 !important;
            background-color: #fef2f2;
        }
    `;
    document.head.appendChild(style);
});
</script>
</body>
</html>