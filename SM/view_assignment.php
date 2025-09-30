<?php
require_once "database.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$assignment_id = intval($_GET['id'] ?? 0);
$user_type = $_SESSION['user_type']; // 'admin' or 'student'

// Fetch assignment details
$stmt = $conn->prepare("
    SELECT a.*, 
           CONCAT(s.FirstName, ' ', s.LastName) as student_name,
           u.name as graded_by_name
    FROM assignments a 
    LEFT JOIN students s ON a.student_id = s.id 
    LEFT JOIN users u ON a.graded_by = u.id 
    WHERE a.id = ?
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    die("Assignment not found");
}

// Fetch submission if student
$submission = null;
if ($user_type === 'student') {
    $stmt = $conn->prepare("
        SELECT * FROM assignment_submissions 
        WHERE assignment_id = ? AND student_id = ?
    ");
    $stmt->bind_param("ii", $assignment_id, $_SESSION['user_id']);
    $stmt->execute();
    $submission = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment - <?= htmlspecialchars($assignment['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .assignment-container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .assignment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
        }
        .assignment-header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .assignment-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .assignment-body {
            padding: 25px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .instructions {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }
        .submission-form {
            background: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #0ea5e9;
        }
        .form-group {
            margin-bottom: 15px;
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
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .submission-info {
            background: #d1fae5;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }
        .grade-info {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }
        .file-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            color: #374151;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="assignment-container">
        <div class="assignment-header">
            <h1><?= htmlspecialchars($assignment['title']) ?></h1>
            <p><strong>Subject:</strong> <?= htmlspecialchars($assignment['subject']) ?></p>
            <div class="assignment-meta">
                <div class="meta-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Lecturer: <?= htmlspecialchars($assignment['lecturer']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Deadline: <?= date('M j, Y H:i', strtotime($assignment['deadline'])) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-flag"></i>
                    <span>Priority: <?= htmlspecialchars($assignment['priority']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span>Status: <?= htmlspecialchars($assignment['status']) ?></span>
                </div>
            </div>
        </div>

        <div class="assignment-body">
            <!-- Instructions Section -->
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> Instructions</h3>
                <div class="instructions">
                    <?= nl2br(htmlspecialchars($assignment['instructions'] ?? 'No specific instructions provided.')) ?>
                </div>
            </div>

            <!-- Attachment Section -->
            <?php if ($assignment['attachment_path']): ?>
            <div class="section">
                <h3><i class="fas fa-paperclip"></i> Assignment Files</h3>
                <a href="<?= htmlspecialchars($assignment['attachment_path']) ?>" class="file-download" download>
                    <i class="fas fa-download"></i>
                    Download Assignment File
                </a>
            </div>
            <?php endif; ?>

            <!-- Student Submission Section -->
            <?php if ($user_type === 'student'): ?>
                <div class="section">
                    <h3><i class="fas fa-upload"></i> Your Submission</h3>
                    
                    <?php if ($submission): ?>
                        <div class="submission-info">
                            <h4><i class="fas fa-check-circle"></i> Submitted</h4>
                            <p><strong>Submitted on:</strong> <?= date('M j, Y H:i', strtotime($submission['submitted_at'])) ?></p>
                            
                            <?php if ($submission['submitted_text']): ?>
                                <p><strong>Your Answer:</strong><br><?= nl2br(htmlspecialchars($submission['submitted_text'])) ?></p>
                            <?php endif; ?>
                            
                            <?php if ($submission['submitted_file']): ?>
                                <p>
                                    <strong>Submitted File:</strong><br>
                                    <a href="<?= htmlspecialchars($submission['submitted_file']) ?>" class="file-download" download>
                                        <i class="fas fa-download"></i>
                                        Download Your Submission
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if ($submission['grade'] !== null): ?>
                                <div class="grade-info">
                                    <h4><i class="fas fa-star"></i> Grading</h4>
                                    <p><strong>Grade:</strong> <?= $submission['grade'] ?>/<?= $assignment['max_points'] ?? 100 ?></p>
                                    <?php if ($submission['feedback']): ?>
                                        <p><strong>Feedback:</strong><br><?= nl2br(htmlspecialchars($submission['feedback'])) ?></p>
                                    <?php endif; ?>
                                    <p><strong>Graded on:</strong> <?= date('M j, Y H:i', strtotime($submission['graded_at'])) ?></p>
                                </div>
                            <?php else: ?>
                                <p><em>Your submission is pending review by the lecturer.</em></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="submission-form">
                            <h4>Submit Your Assignment</h4>
                            <form action="submit_assignment.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
                                
                                <div class="form-group">
                                    <label for="submission_text">Your Answer (Text):</label>
                                    <textarea name="submission_text" id="submission_text" class="form-control" 
                                              placeholder="Type your answer here..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="submission_file">Or Upload File:</label>
                                    <input type="file" name="submission_file" id="submission_file" class="form-control"
                                           accept=".pdf,.doc,.docx,.txt,.zip,.rar">
                                    <small>Accepted formats: PDF, Word, Text, ZIP (Max: 10MB)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit Assignment
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Admin Actions -->
            <?php if ($user_type === 'admin'): ?>
                <div class="section">
                    <h3><i class="fas fa-cog"></i> Administration</h3>
                    <div class="action-buttons">
                        <a href="edit_assignment.php?id=<?= $assignment_id ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Assignment
                        </a>
                        <button onclick="deleteAssignment(<?= $assignment_id ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Assignment
                        </button>
                        <a href="view_submissions.php?assignment_id=<?= $assignment_id ?>" class="btn btn-secondary">
                            <i class="fas fa-list"></i> View Submissions
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function deleteAssignment(id) {
        if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
            fetch('delete_assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Assignment deleted successfully!');
                    window.location.href = 'assignments.php';
                } else {
                    alert('Error deleting assignment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting assignment');
            });
        }
    }
    </script>
</body>
</html>