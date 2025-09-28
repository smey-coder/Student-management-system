<?php
require_once "database.php";

// Fetch all students
$students_result = $conn->query("SELECT id, FirstName, LastName, StudentID, Email FROM students ORDER BY FirstName, LastName");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student QR Cards</title>
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
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .qr-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            break-inside: avoid;
        }
        .qr-image {
            width: 150px;
            height: 150px;
            margin: 0 auto 15px;
            border: 2px solid #1a237e;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }
        .student-info {
            margin-top: 10px;
        }
        .student-name {
            font-weight: bold;
            font-size: 16px;
            color: #1a237e;
            margin-bottom: 5px;
        }
        .student-id {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .btn {
            background: #1a237e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn-print {
            background: #4caf50;
        }
        .btn-download {
            background: #2196f3;
        }
        .action-buttons {
            text-align: center;
            margin: 20px 0;
        }
        @media print {
            body { background: white; }
            .filters, .action-buttons { display: none; }
            .qr-card { box-shadow: none; border: 1px solid #ccc; }
        }
        .search-box {
            padding: 10px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student QR Code Cards</h1>
            <p>Print or download QR codes for student attendance</p>
        </div>

        <div class="filters">
            <input type="text" id="searchStudent" class="search-box" placeholder="Search students by name or ID...">
            <div class="action-buttons">
                <button class="btn btn-print" onclick="window.print()">🖨️ Print All Cards</button>
                <button class="btn btn-download" onclick="downloadAllQRCodes()">📥 Download All QR Codes</button>
                <a href="admin_page.php?page=attendance" class="btn">← Back to Attendance</a>
            </div>
        </div>

        <div class="qr-grid" id="qrGrid">
            <?php while ($student = $students_result->fetch_assoc()): ?>
            <div class="qr-card" data-name="<?= htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']) ?>" data-id="<?= htmlspecialchars($student['StudentID']) ?>">
                <div class="qr-image">
                    <img src="generate_qr.php?student_id=<?= $student['id'] ?>" alt="QR Code for <?= htmlspecialchars($student['FirstName']) ?>" width="150" height="150">
                </div>
                <div class="student-info">
                    <div class="student-name"><?= htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']) ?></div>
                    <div class="student-id">ID: <?= htmlspecialchars($student['StudentID']) ?></div>
                    <div style="margin-top: 10px;">
                        <button class="btn" onclick="downloadQRCode(<?= $student['id'] ?>, '<?= htmlspecialchars($student['FirstName'] . '_' . $student['LastName']) ?>')">
                            📥 Download
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
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

        // Download individual QR code
        function downloadQRCode(studentId, studentName) {
            const link = document.createElement('a');
            link.href = `generate_qr.php?student_id=${studentId}&download=1`;
            link.download = `QR_${studentName}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Download all QR codes (this would need server-side implementation)
        function downloadAllQRCodes() {
            alert('This feature would download ZIP file with all QR codes. Requires server-side implementation.');
            // Implementation would require creating a ZIP file with all QR codes on the server
        }
    </script>
</body>
</html>