<?php
  session_start();

  $errors = [
    'login' => $_SESSION['login_error'] ?? '',
  ];

  $activateForm = $_SESSION['activate_form'] ?? 'login';

  session_unset();

  function showError($error){
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
  }

  function isActivateForm($forName, $activateForm){
    return $forName === $activateForm ? 'active' : '';
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGN IN</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="form-box <?= isActivateForm('login', $activateForm); ?>" id="login-form">
             <form action="login.php" method="post">
                <h2>SIGN IN</h2>
                <img src="image/logo.png" alt="logo" class="logo">
                <p>NORTON UNIVERSITY</p>
                <p>Student System</p>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
             </form> 
        </div>
    </div>
    <script src="index.js"></script>
</body>
</html>