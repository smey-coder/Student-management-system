<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$assignment_id = intval($_GET['id'] ?? 0);

// Fetch assignment
if ($assignment_id) {
    $stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $assignment_id);
    $stmt->execute();
    $assignment = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$assignment) {
        die("Assignment not found");
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $lecturer = $_POST['lecturer'];
    $instructions = $_POST['instructions'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];
    $max_points = intval($_POST['max_points']);
    $course_code = $_POST['course_code'];
    
    if ($assignment_id) {
        // Update existing assignment
        $stmt = $conn->prepare("
            UPDATE assignments SET 
            title=?, subject=?, lecturer=?, instructions=?, deadline=?, 
            priority=?, max_points=?, course_code=?, updated_at=NOW() 
            WHERE id=?
        ");
        $stmt->bind_param("ssssssisi", $title, $subject, $lecturer, $instructions, 
                         $deadline, $priority, $max_points, $course_code, $assignment_id);
    } else {
        // Create new assignment
        $stmt = $conn->prepare("
            INSERT INTO assignments 
            (title, subject, lecturer, instructions, deadline, priority, max_points, course_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssssis", $title, $subject, $lecturer, $instructions, 
                         $deadline, $priority, $max_points, $course_code);
    }
    
    if ($stmt->execute()) {
        $message = $assignment_id ? "Assignment updated successfully!" : "Assignment created successfully!";
        $_SESSION['message'] = $message;
        header("Location: assignments.php");
        exit;
    } else {
        $error = "Error saving assignment: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $assignment_id ? 'Edit' : 'Create' ?> Assignment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #6366f1;
            color: white;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><i class="fas fa-<?= $assignment_id ? 'edit' : 'plus' ?>"></i> 
            <?= $assignment_id ? 'Edit Assignment' : 'Create New Assignment' ?>
        </h1>
        
        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="title">Assignment Title *</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($assignment['title'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="subject">Subject *</label>
                <input type="text" id="subject" name="subject" class="form-control" 
                       value="<?= htmlspecialchars($assignment['subject'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="course_code">Course Code</label>
                <input type="text" id="course_code" name="course_code" class="form-control" 
                       value="<?= htmlspecialchars($assignment['course_code'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="lecturer">Lecturer *</label>
                <input type="text" id="lecturer" name="lecturer" class="form-control" 
                       value="<?= htmlspecialchars($assignment['lecturer'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" class="form-control"><?= htmlspecialchars($assignment['instructions'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="deadline">Deadline *</label>
                <input type="datetime-local" id="deadline" name="deadline" class="form-control" 
                       value="<?= isset($assignment['deadline']) ? date('Y-m-d\TH:i', strtotime($assignment['deadline'])) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="Low" <?= ($assignment['priority'] ?? '') === 'Low' ? 'selected' : '' ?>>Low</option>
                    <option value="Medium" <?= ($assignment['priority'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="High" <?= ($assignment['priority'] ?? '') === 'High' ? 'selected' : '' ?>>High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="max_points">Maximum Points</label>
                <input type="number" id="max_points" name="max_points" class="form-control" 
                       value="<?= $assignment['max_points'] ?? 100 ?>" min="1" max="1000">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Assignment
                </button>
                <a href="assignments.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>