<div class="dashboard-wrapper">

  <!-- Header Section -->
  <div class="dashboard-header">
    <div class="header-left">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVYYd_Kwf-J-HYY-PV50vJnhyPx7SQHn-Vkg&s" alt="Logo" class="logo">
      <div class="title">
        <h1>សាកលវិទ្យាល័យ ន័រតុន</h1>
        <p>ជាសាកលវិទ្យាល័យឯកជនកំពូលក្នុងប្រទេសកម្ពុជា</p>
      </div>
    </div>
    
    <div class="profile">
      <img src="https://cdn-icons-png.flaticon.com/512/4196/4196591.png" alt="User">
      <span class="username"><?php echo htmlspecialchars($name ?? 'User'); ?></span>
      
      <div class="profile-dropdown">
        <select name="role" onchange="handleProfileOption(this.value)">
          <option value="" selected disabled>-- ជ្រើសរើស --</option>
          <option value="profile">👤 Profile</option>
          <option value="setting">⚙️ Setting</option>
          <option value="logout">🚪 Logout</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Time + Alert -->
  <div class="info-section">
    <div class="time-box">
      <i class="fas fa-clock"></i>
      <span id="live-time">
        <?php
        date_default_timezone_set("Asia/Phnom_Penh");
        echo date("H:i:s | D, d F Y");
        ?>
      </span>
    </div>

    <div class="alert warning">
      <i class="fas fa-exclamation-triangle"></i>
      <span>Your class is invalid. Please contact support team.</span>
    </div>
  </div>

  <!-- Content with Notice Board -->
  <div class="content-row">
    <div class="main-info">
      <!-- Add your dashboard cards here -->
      <div class="welcome-card">
        <h3>Welcome back, <?php echo htmlspecialchars($name ?? 'Admin'); ?>! 👋</h3>
        <p>Here's what's happening today.</p>
      </div>
    </div>
    
    <div class="notice-board">
      <h3><i class="fas fa-bullhorn"></i> Notice Board</h3>
      <div class="notice-empty">
        <img src="https://cdn-icons-png.flaticon.com/256/6195/6195678.png" alt="No Data">
        <p>No announcements found</p>
        <small>Check back later for updates</small>
      </div>
    </div>
  </div>
</div>

<script>
// Live time update
function updateLiveTime() {
    const now = new Date();
    const options = { 
        timeZone: 'Asia/Phnom_Penh',
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        weekday: 'short',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    const formatter = new Intl.DateTimeFormat('en-US', options);
    const timeString = formatter.format(now).replace(/,/g, ' |');
    
    document.getElementById('live-time').textContent = timeString;
}

// Update time every second
setInterval(updateLiveTime, 1000);

// Profile dropdown handler
function handleProfileOption(value) {
    if (!value) return;

    switch (value) {
        case 'profile':
            window.location.href = 'profile.php?page=profile';
            break;
        case 'setting':
            window.location.href = 'setting.php?page=setting';
            break;
        case 'logout':
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
            break;
    }

    // Reset select to default
    const select = document.querySelector('select[name="role"]');
    if (select) {
        select.value = '';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const profileSection = document.querySelector('.profile');
    const dropdown = document.querySelector('.profile-dropdown');
    
    if (!profileSection.contains(event.target)) {
        const select = document.querySelector('select[name="role"]');
        if (select) {
            select.value = '';
        }
    }
});
</script>

<style>
.dashboard-wrapper {
    padding: 20px;
    background: #f8fafc;
    min-height: 100vh;
}

/* Header Styles */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.logo {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
}

.title h1 {
    margin: 0;
    font-size: 18px;
    color: #1e293b;
    font-weight: 600;
}

.title p {
    margin: 0;
    font-size: 12px;
    color: #64748b;
}

/* Profile Styles */
.profile {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
}

.username {
    font-weight: 500;
    color: #374151;
}

.profile-dropdown select {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: white;
    color: #374151;
    cursor: pointer;
    font-size: 14px;
    min-width: 120px;
    transition: all 0.3s ease;
}

.profile-dropdown select:hover {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.profile-dropdown select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Info Section */
.info-section {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.time-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.alert {
    background: #fef3c7;
    color: #d97706;
    padding: 12px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid #f59e0b;
    flex-grow: 1;
}

.alert.warning {
    background: #fef3c7;
    color: #d97706;
    border-left-color: #f59e0b;
}

/* Content Row */
.content-row {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
}

.main-info {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
}

.welcome-card h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
}

.welcome-card p {
    margin: 0;
    opacity: 0.9;
}

.notice-board {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
}

.notice-board h3 {
    margin: 0 0 15px 0;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.notice-empty {
    text-align: center;
    padding: 30px 20px;
    color: #64748b;
}

.notice-empty img {
    width: 80px;
    height: 80px;
    opacity: 0.5;
    margin-bottom: 15px;
}

.notice-empty p {
    margin: 0 0 5px 0;
    font-weight: 500;
}

.notice-empty small {
    font-size: 12px;
    opacity: 0.7;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .header-left {
        flex-direction: column;
        text-align: center;
    }
    
    .content-row {
        grid-template-columns: 1fr;
    }
    
    .info-section {
        flex-direction: column;
    }
    
    .profile {
        justify-content: center;
    }
}
</style>