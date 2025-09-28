<div class="card">
  <h2>Settings</h2>
  <form method="POST">
    <label>School Name</label>
    <input type="text" name="school_name" placeholder="Enter School Name">
    <label>Academic Year</label>
    <input type="text" name="academic_year" placeholder="e.g. 2025-2026">
    <button class="btn" style="background:#1a237e; color:white;">Save Settings</button>
  </form>
</div>
<div class="card" style="margin-top:20px;">
  <h2>Manage Students</h2>

  <!-- Add Student Button -->
  <button class="btn" style="margin-bottom:15px; background:#1a237e; color:white;" onclick="openAddForm()">+ Add Student</button>

  <!-- Student List -->
  <table border="1" cellpadding="8" cellspacing="0" style="margin-top:15px; width:100%;">
    <tr style="background:#1a237e; color:white;">
      <th>ID</th>
      <th>First Name</th>
      <th>Last Name</th>