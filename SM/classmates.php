<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "database.php";
require_once "function.php";

$student_id = $_SESSION['id'] ?? '';

if (!$student_id) {
    echo "You must be logged in to view classmates.";
    exit();
}

// Handle search
$search = $_GET['search'] ?? '';
$search_where = "";
$search_params = [];
$search_types = "";

if (!empty($search)) {
    $search_term = "%$search%";
    $search_where = " AND (FirstName LIKE ? OR LastName LIKE ? OR Email LIKE ? OR Phone LIKE ? OR Address LIKE ?)";
    $search_types = "sssss";
    $search_params = array_fill(0, 5, $search_term);
}

$classmates = fetch_all($conn,
    "SELECT id, FirstName, LastName, Gender, DateOfBirth, Email, Phone, Address 
     FROM students 
     WHERE id != ? $search_where
     ORDER BY FirstName, LastName", 
    "i" . $search_types, array_merge([$student_id], $search_params));

$total_classmates = count($classmates);

// Define highlightText function once outside the loop
function highlightText($text, $search) {
    if (empty($search) || empty(trim($text))) {
        return htmlspecialchars($text);
    }
    $pattern = '/(' . preg_quote($search, '/') . ')/i';
    return preg_replace($pattern, '<span class="highlight">$1</span>', htmlspecialchars($text));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classmates</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4776E6, #8E54E9);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: #1e40af;
        }

        .search-container {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-button:hover {
            background: #1e40af;
        }

        .clear-search {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #6b7280;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .clear-search:hover {
            background: #4b5563;
        }

        .classmates-count {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
            font-weight: 600;
            color: #1e40af;
        }

        .search-results-info {
            background: #fff3cd;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 800px;
        }

        table th {
            background: #2563eb;
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            border: none;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        table tbody tr:hover {
            background: #f8fafc;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        .gender-male { color: #2563eb; }
        .gender-female { color: #dc2626; }
        .gender-other { color: #7c3aed; }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .student-name {
            display: flex;
            align-items: center;
        }

        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            background: #f9fafb;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #9ca3af;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-container {
                max-width: none;
            }
            
            table th,
            table td {
                padding: 10px 8px;
                font-size: 13px;
            }
        }

        .email-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .phone-cell {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="ri-team-fill"></i> Classmates</h1>
            <p>Connect with your fellow students</p>
        </div>

        <div class="content">
            <div class="action-bar">
                <a href="user_page.php" class="back-link">
                    <i class="ri-arrow-left-line"></i> Back to Dashboard
                </a>
                
                <!-- Fixed form - explicitly set action to current page -->
                <form method="GET" action="" class="search-container">
                    <div class="search-box">
                        <input 
                            type="text" 
                            name="search" 
                            class="search-input" 
                            placeholder="Search by name, email, phone, or address..." 
                            value="<?= htmlspecialchars($search) ?>"
                        >
                        <button type="submit" class="search-button">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                        <a href="classmates.php" class="clear-search">
                            <i class="ri-close-line"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($search)): ?>
                <div class="search-results-info">
                    <i class="ri-search-eye-line"></i>
                    Showing results for: "<strong><?= htmlspecialchars($search) ?></strong>"
                    <br><small>Found <?= $total_classmates ?> classmate(s)</small>
                </div>
            <?php else: ?>
                <div class="classmates-count">
                    <i class="ri-user-shared-line"></i> 
                    Total Classmates: <?= $total_classmates ?>
                </div>
            <?php endif; ?>

            <?php if ($classmates): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classmates as $c): 
                                $initials = substr($c['FirstName'], 0, 1) . substr($c['LastName'], 0, 1);
                                $gender_class = 'gender-' . strtolower($c['Gender']);
                            ?>
                                <tr>
                                    <td>
                                        <div class="student-name">
                                            <div class="avatar"><?= strtoupper($initials) ?></div>
                                            <div>
                                                <strong><?= !empty($search) ? highlightText($c['FirstName'] . ' ' . $c['LastName'], $search) : htmlspecialchars($c['FirstName'] . ' ' . $c['LastName']) ?></strong>
                                                <div style="font-size: 12px; color: #6b7280;">ID: <?= htmlspecialchars($c['id']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="<?= $gender_class ?>">
                                            <i class="ri-<?= strtolower($c['Gender']) === 'male' ? 'men' : 'women' ?>-line"></i>
                                            <?= htmlspecialchars($c['Gender']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($c['DateOfBirth']))) ?></td>
                                    <td class="email-cell">
                                        <a href="mailto:<?= htmlspecialchars($c['Email']) ?>" style="color: #2563eb; text-decoration: none;">
                                            <?= !empty($search) ? highlightText($c['Email'], $search) : htmlspecialchars($c['Email']) ?>
                                        </a>
                                    </td>
                                    <td class="phone-cell">
                                        <?= !empty($search) ? highlightText($c['Phone'], $search) : htmlspecialchars($c['Phone']) ?>
                                    </td>
                                    <td>
                                        <?= !empty($search) ? highlightText($c['Address'], $search) : htmlspecialchars($c['Address']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="ri-user-unfollow-line"></i>
                    <h3>No classmates found</h3>
                    <p>
                        <?php if (!empty($search)): ?>
                            No classmates found matching "<?= htmlspecialchars($search) ?>".
                            <br><a href="classmates.php" style="color: #2563eb; margin-top: 10px; display: inline-block;">View all classmates</a>
                        <?php else: ?>
                            There are no other students in the system.
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Focus search input on page load if there's a search term
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput.value) {
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            }
        });

        // Clear search when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const searchInput = document.querySelector('.search-input');
                if (searchInput.value) {
                    window.location.href = 'classmates.php';
                }
            }
        });

        // Prevent default form submission and handle it properly
        document.querySelector('form').addEventListener('submit', function(e) {
            const searchInput = document.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                window.location.href = 'classmates.php';
            }
        });

        // Auto-submit form when pressing Enter in search
        document.querySelector('.search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    </script>
</body>
</html>