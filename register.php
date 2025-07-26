<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $role = 'viewer'; // Default role for new registrations

    // Enhanced server-side validation
    $errors = [];
    
    // Username validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    } elseif (strlen($username) > 50) {
        $errors[] = "Username must be less than 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (strlen($password) > 255) {
        $errors[] = "Password is too long.";
    }
    
    // Confirm password validation
    if (empty($confirm)) {
        $errors[] = "Please confirm your password.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username already taken.";
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            if ($stmt->execute()) {
                header("Location: login.php?message=Registration successful! Please login.");
                exit();
            } else {
                $message = "Error registering user.";
            }
        }
        $stmt->close();
    } else {
        $message = implode(" ", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - ApexPlanet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .register-container {
      max-width: 420px;
      margin: auto;
      margin-top: 5%;
      background-color: #1e293b;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.3);
    }

    .form-control {
      background-color: #334155;
      color: #fff;
      border: none;
    }

    .form-control:focus {
      background-color: #475569;
      color: #fff;
      border: 1px solid #38bdf8;
      box-shadow: none;
    }

    .btn-custom {
      background-color: #38bdf8;
      color: #000;
      font-weight: bold;
    }

    .btn-custom:hover {
      background-color: #0ea5e9;
    }

    .form-title {
      font-size: 24px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: bold;
    }

    .error-message {
      color: #f87171;
      text-align: center;
    }

    .password-strength {
      margin-top: 5px;
      font-size: 0.875em;
    }

    .strength-weak { color: #f87171; }
    .strength-medium { color: #fbbf24; }
    .strength-strong { color: #34d399; }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="form-title">Create Account</div>

    <?php if ($message): ?>
      <p class="error-message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php" id="registerForm">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" name="username" id="username" 
               required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        <div class="form-text">3-50 characters, letters, numbers, and underscores only</div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" name="password" id="password" 
               required minlength="6" maxlength="255">
        <div class="password-strength" id="passwordStrength"></div>
        <div class="form-text">Minimum 6 characters</div>
      </div>

      <div class="mb-4">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" id="confirm_password" 
               required minlength="6" maxlength="255">
        <div class="form-text" id="confirmMessage"></div>
      </div>

      <div class="d-grid">
        <button type="submit" class="btn btn-custom">Register</button>
      </div>

      <p class="mt-3 text-center">
        Already registered? <a href="login.php" class="text-info">Login</a>
      </p>
    </form>
  </div>

  <script>
  // Client-side validation
  document.getElementById('registerForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    // Username validation
    if (username.length < 3) {
      e.preventDefault();
      alert('Username must be at least 3 characters long.');
      return false;
    }
    
    if (username.length > 50) {
      e.preventDefault();
      alert('Username must be less than 50 characters.');
      return false;
    }
    
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
      e.preventDefault();
      alert('Username can only contain letters, numbers, and underscores.');
      return false;
    }
    
    // Password validation
    if (password.length < 6) {
      e.preventDefault();
      alert('Password must be at least 6 characters long.');
      return false;
    }
    
    if (password !== confirm) {
      e.preventDefault();
      alert('Passwords do not match.');
      return false;
    }
  });

  // Password strength indicator
  document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    let strength = 0;
    let message = '';
    let className = '';
    
    if (password.length >= 6) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    if (strength < 2) {
      message = 'Weak password';
      className = 'strength-weak';
    } else if (strength < 4) {
      message = 'Medium strength password';
      className = 'strength-medium';
    } else {
      message = 'Strong password';
      className = 'strength-strong';
    }
    
    strengthDiv.textContent = message;
    strengthDiv.className = 'password-strength ' + className;
  });

  // Password confirmation check
  document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const messageDiv = document.getElementById('confirmMessage');
    
    if (confirm === '') {
      messageDiv.textContent = '';
    } else if (password === confirm) {
      messageDiv.textContent = 'Passwords match ✓';
      messageDiv.style.color = '#34d399';
    } else {
      messageDiv.textContent = 'Passwords do not match ✗';
      messageDiv.style.color = '#f87171';
    }
  });
  </script>
</body>
</html>
