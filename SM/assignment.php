<?php
$rows = fetch_all($conn, "SELECT * FROM assignments ORDER BY deadline ASC");
?>

<div class="assignments-container">
    <div class="assignments-header">
        <h1><i class="fas fa-tasks"></i> Assignments</h1>
        <div class="assignment-actions">
            <button class="btn btn-primary" onclick="exportAssignments()">
                <i class="fas fa-download"></i> Export
            </button>
            <button class="btn btn-secondary" onclick="refreshAssignments()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <div class="table-container">
        <table class="assignments-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Lecturer</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Days Left</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($rows): ?>
                <?php foreach ($rows as $r): 
                    $deadline = new DateTime($r['deadline']);
                    $today = new DateTime();
                    $daysLeft = $today->diff($deadline)->days;
                    $isOverdue = $deadline < $today;
                    $isUrgent = $daysLeft <= 3 && !$isOverdue;
                ?>
                    <tr class="<?= $isOverdue ? 'overdue' : ($isUrgent ? 'urgent' : '') ?>">
                        <td class="assignment-id">#<?= htmlspecialchars($r['id']) ?></td>
                        <td class="subject">
                            <i class="fas fa-book"></i>
                            <?= htmlspecialchars($r['subject']) ?>
                        </td>
                        <td class="lecturer">
                            <i class="fas fa-user-tie"></i>
                            <?= htmlspecialchars($r['lecturer']) ?>
                        </td>
                        <td class="status">
                            <span class="status-badge status-<?= strtolower(htmlspecialchars($r['status'])) ?>">
                                <?= htmlspecialchars($r['status']) ?>
                            </span>
                        </td>
                        <td class="deadline">
                            <i class="fas fa-calendar-alt"></i>
                            <?= htmlspecialchars(date('M j, Y', strtotime($r['deadline']))) ?>
                        </td>
                        <td class="days-left">
                            <?php if ($isOverdue): ?>
                                <span class="days-badge overdue">Overdue by <?= $daysLeft ?> days</span>
                            <?php elseif ($daysLeft == 0): ?>
                                <span class="days-badge urgent">Due Today</span>
                            <?php else: ?>
                                <span class="days-badge <?= $isUrgent ? 'urgent' : 'normal' ?>">
                                    <?= $daysLeft ?> days left
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button class="btn-action view" onclick="viewAssignment(<?= $r['id'] ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action edit" onclick="editAssignment(<?= $r['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action delete" onclick="deleteAssignment(<?= $r['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-data">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h3>No Assignments Found</h3>
                            <p>There are no assignments available at the moment.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Summary Stats -->
    <div class="assignment-stats">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-info">
                <h3><?= count($rows) ?></h3>
                <p>Total Assignments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?= count(array_filter($rows, function($r) { return $r['status'] === 'Pending'; })) ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon urgent">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3><?= count(array_filter($rows, function($r) { 
                    $deadline = new DateTime($r['deadline']);
                    $today = new DateTime();
                    $daysLeft = $today->diff($deadline)->days;
                    return $daysLeft <= 3 && $deadline >= $today;
                })) ?></h3>
                <p>Urgent</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon overdue">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-info">
                <h3><?= count(array_filter($rows, function($r) { 
                    return new DateTime($r['deadline']) < new DateTime();
                })) ?></h3>
                <p>Overdue</p>
            </div>
        </div>
    </div>
</div>

<script>
// View Assignment - Redirect to view page
function viewAssignment(id) {
    window.location.href = 'view_assignment.php?id=' + id;
}

// Edit Assignment - Redirect to edit page
function editAssignment(id) {
    window.location.href = 'edit_assignment.php?id=' + id;
}

// Delete Assignment with AJAX
function deleteAssignment(id) {
    if (confirm('Are you sure you want to delete assignment #' + id + '? This action cannot be undone.')) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        button.disabled = true;
        
        fetch('delete_assignment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Assignment deleted successfully!', 'success');
                // Remove the row from table
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.style.backgroundColor = '#fee2e2';
                    setTimeout(() => {
                        row.remove();
                        // Check if table is empty
                        checkEmptyTable();
                    }, 1000);
                }
            } else {
                throw new Error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting assignment: ' + error.message, 'error');
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Export Assignments to CSV
function exportAssignments() {
    // Show loading
    showNotification('Preparing export...', 'info');
    
    fetch('export_assignments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Export failed');
        }
        return response.blob();
    })
    .then(blob => {
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `assignments_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        showNotification('Export completed successfully!', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error exporting assignments: ' + error.message, 'error');
    });
}

// Refresh Assignments with smooth animation
function refreshAssignments() {
    const refreshBtn = event.target;
    const originalHtml = refreshBtn.innerHTML;
    
    // Add spinning animation
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;
    
    // Add fade out animation to table
    const table = document.querySelector('.assignments-table');
    table.style.opacity = '0.7';
    table.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        window.location.reload();
    }, 800);
}

// Enhanced Search Functionality
function searchAssignments() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.assignments-table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const isVisible = text.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
        
        if (isVisible) {
            visibleCount++;
            // Highlight search term
            if (searchTerm) {
                highlightSearchTerm(row, searchTerm);
            }
        }
    });
    
    // Update search results counter
    updateSearchResults(visibleCount, rows.length);
}

// Highlight search terms in table
function highlightSearchTerm(row, searchTerm) {
    const cells = row.querySelectorAll('td:not(.actions)');
    cells.forEach(cell => {
        const originalText = cell.textContent;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        const highlightedText = originalText.replace(regex, '<mark>$1</mark>');
        cell.innerHTML = highlightedText;
    });
}

// Update search results counter
function updateSearchResults(visible, total) {
    let counter = document.getElementById('searchResultsCounter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'searchResultsCounter';
        counter.style.cssText = 'margin: 10px 0; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; font-size: 14px;';
        const searchContainer = document.querySelector('.search-container') || document.querySelector('.assignment-actions');
        if (searchContainer) {
            searchContainer.parentNode.insertBefore(counter, searchContainer.nextSibling);
        }
    }
    
    if (visible === total) {
        counter.style.display = 'none';
    } else {
        counter.style.display = 'block';
        counter.innerHTML = `Showing ${visible} of ${total} assignments`;
    }
}

// Filter by Status
function filterByStatus(status) {
    const rows = document.querySelectorAll('.assignments-table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const statusCell = row.querySelector('.status-badge');
        const rowStatus = statusCell ? statusCell.textContent.toLowerCase() : '';
        const isVisible = status === 'all' || rowStatus === status.toLowerCase();
        row.style.display = isVisible ? '' : 'none';
        
        if (isVisible) visibleCount++;
    });
    
    updateSearchResults(visibleCount, rows.length);
}

// Sort Table
function sortTable(columnIndex, type = 'text') {
    const table = document.querySelector('.assignments-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const header = table.querySelectorAll('th')[columnIndex];
    const isAscending = !header.classList.contains('asc');
    
    // Remove sort indicators from all headers
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('asc', 'desc');
    });
    
    // Add sort indicator to current header
    header.classList.add(isAscending ? 'asc' : 'desc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aCell = a.cells[columnIndex];
        const bCell = b.cells[columnIndex];
        let aValue = aCell.textContent.trim();
        let bValue = bCell.textContent.trim();
        
        if (type === 'number') {
            aValue = parseInt(aValue) || 0;
            bValue = parseInt(bValue) || 0;
        } else if (type === 'date') {
            aValue = new Date(aCell.getAttribute('data-sort') || aValue);
            bValue = new Date(bCell.getAttribute('data-sort') || bValue);
        }
        
        if (isAscending) {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });
    
    // Reappend sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// Check if table is empty after deletion
function checkEmptyTable() {
    const tbody = document.querySelector('.assignments-table tbody');
    const visibleRows = tbody.querySelectorAll('tr:not([style*="display: none"])');
    
    if (visibleRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="8" class="no-data">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Assignments Found</h3>
                    <p>There are no assignments matching your criteria.</p>
                </div>
            </td>
        `;
        tbody.appendChild(emptyRow);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Initialize table functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add search input if it doesn't exist
    if (!document.getElementById('searchInput')) {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'search-container';
        searchContainer.style.cssText = 'margin: 15px 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;';
        
        searchContainer.innerHTML = `
            <div style="position: relative; flex: 1; max-width: 300px;">
                <input type="text" id="searchInput" placeholder="Search assignments..." 
                       style="width: 100%; padding: 8px 12px 8px 35px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280;"></i>
            </div>
            <select onchange="filterByStatus(this.value)" style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="submitted">Submitted</option>
            </select>
        `;
        
        const tableContainer = document.querySelector('.table-container');
        if (tableContainer) {
            tableContainer.parentNode.insertBefore(searchContainer, tableContainer);
        }
    }
    
    // Add event listener for search input
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', searchAssignments);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchAssignments();
            }
        });
    }
    
    // Make table headers sortable
    const tableHeaders = document.querySelectorAll('.assignments-table th');
    tableHeaders.forEach((header, index) => {
        if (!header.querySelector('.actions')) {
            header.style.cursor = 'pointer';
            header.title = 'Click to sort';
            header.addEventListener('click', () => {
                const types = ['number', 'text', 'text', 'text', 'date', 'number', 'text'];
                sortTable(index, types[index] || 'text');
            });
        }
    });
});

// Add CSS for notifications and enhancements
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        min-width: 300px;
        max-width: 500px;
        animation: slideIn 0.3s ease;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-size: 14px;
    }
    
    .notification-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
    .notification-error { background: #fee2e2; color: #dc2626; border-left: 4px solid #ef4444; }
    .notification-warning { background: #fef3c7; color: #d97706; border-left: 4px solid #f59e0b; }
    .notification-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #6366f1; }
    
    .notification-content button {
        background: none;
        border: none;
        cursor: pointer;
        margin-left: auto;
        color: inherit;
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    mark {
        background: #fef3c7;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    .assignments-table th.asc::after {
        content: " ↑";
        font-weight: bold;
    }
    
    .assignments-table th.desc::after {
        content: " ↓";
        font-weight: bold;
    }
    
    .search-container {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);
</script>
<style>
.assignments-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.assignments-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.assignments-header h1 {
    margin: 0;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.assignment-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-primary:hover {
    background: rgba(255,255,255,0.3);
}

.btn-secondary {
    background: transparent;
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.1);
}

.table-container {
    overflow-x: auto;
}

.assignments-table {
    width: 100%;
    border-collapse: collapse;
}

.assignments-table th {
    background: #f8fafc;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.assignments-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e5e7eb;
}

.assignments-table tbody tr:hover {
    background: #f8fafc;
}

.assignments-table tbody tr.urgent {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
}

.assignments-table tbody tr.overdue {
    background: #fee2e2;
    border-left: 4px solid #ef4444;
}

.assignment-id {
    font-weight: 600;
    color: #6366f1;
}

.subject, .lecturer, .deadline {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-in-progress { background: #dbeafe; color: #1e40af; }
.status-submitted { background: #f3e8ff; color: #7c3aed; }

.days-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
}

.days-badge.normal { background: #d1fae5; color: #065f46; }
.days-badge.urgent { background: #fef3c7; color: #d97706; }
.days-badge.overdue { background: #fee2e2; color: #dc2626; }

.actions {
    display: flex;
    gap: 5px;
}

.btn-action {
    padding: 6px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: transparent;
}

.btn-action.view { color: #6366f1; }
.btn-action.edit { color: #10b981; }
.btn-action.delete { color: #ef4444; }

.btn-action:hover {
    transform: translateY(-2px);
    background: rgba(0,0,0,0.05);
}

.no-data {
    text-align: center;
    padding: 40px !important;
}

.empty-state {
    color: #6b7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    color: #374151;
}

.empty-state p {
    margin: 0;
    opacity: 0.7;
}

.assignment-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    padding: 20px;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.stat-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.stat-icon.total { background: #6366f1; color: white; }
.stat-icon.pending { background: #f59e0b; color: white; }
.stat-icon.urgent { background: #ef4444; color: white; }
.stat-icon.overdue { background: #dc2626; color: white; }

.stat-info h3 {
    margin: 0;
    font-size: 24px;
    color: #1f2937;
}

.stat-info p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .assignments-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .assignment-actions {
        justify-content: center;
    }
    
    .assignment-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .actions {
        flex-direction: column;
    }
}
</style>