<div class="dashboard-wrapper">

  <!-- Header Section -->
  <div class="dashboard-header">
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVYYd_Kwf-J-HYY-PV50vJnhyPx7SQHn-Vkg&s" alt="Logo" class="logo">
    <div class="title">
      <h1>សាកលវិទ្យាល័យ ន័រតុន</h1>
      <p>ជាសាកលវិទ្យាល័យឯកជនកំពូលក្នុងប្រទេសកម្ពុជា</p>
    </div>
    <div class="profile">
      <img src="https://cdn-icons-png.flaticon.com/512/4196/4196591.png" alt="User">
      <span><?php echo htmlspecialchars($name); ?></span>

      <select name="role" onchange="handleProfileOption(this.value)" required>
        <option value="">-- Select --</option>
        <option value="profile">Profile</option>
        <option value="setting">Setting</option>
        <option value="logout">Logout</option>
      </select>
    </div>
  </div>

  <!-- Time + Alert -->
  <div class="time-box">
    <i class="ri-time-line"></i>
    <span><?php echo date("H:i:s | D, d F Y"); ?></span>
  </div>

  <div class="alert">
    <i class="ri-error-warning-fill"></i>
    Your class is invalid. Please contact support team.
  </div>

  <!-- Content with Notice Board -->
  <div class="content-row">
    <div class="main-info">
      <!-- You can add cards here -->
    </div>
    <div class="notice-board">
      <h3><i class="ri-notification-2-fill"></i> Notice Board</h3>
      <div class="notice-empty">
        <img src="https://cdn-icons-png.flaticon.com/256/6195/6195678.png" alt="No Data">
        <p>No Data Found</p>
      </div>
    </div>
  </div>
</div>

<script>
const profileSelect = document.querySelector('select[name="role"]');

if (profileSelect) {
  profileSelect.addEventListener('change', function() {
    const value = this.value;
    if (!value) return;

    switch (value) {
      case 'profile':
        window.location.href = 'profile.php?page=profile';
        break;
      case 'setting':
        window.location.href = 'setting.php?page=setting';
        break;
      case 'logout':
        window.location.href = 'logout.php';
        break;
    }

    // Reset select to default
    this.value = '';
  });
}
</script>

<style>
  .profile {
  display: flex;
  align-items: center;
  gap: 8px;
}

.profile img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.profile select {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #374151;
  cursor: pointer;
}

</style>
