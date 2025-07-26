<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

$message = '';
$success_message = '';

// Check for success message from registration
if (isset($_GET['message'])) {
    $success_message = $_GET['message'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Enhanced validation
    if (empty($username) || empty($password)) {
        $message = "Both username and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: index.php");
                    exit();
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "User not found.";
            }
        } else {
            $message = "Database error.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - ApexPlanet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #fff;
      height: 100vh;
    }

    .login-container {
      max-width: 400px;
      margin: auto;
      margin-top: 10%;
      background: #1e293b;
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

    .success-message {
      color: #34d399;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="form-title">Login to ApexPlanet</div>
  
  <?php if ($success_message): ?>
    <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
  <?php endif; ?>

  <?php if ($message): ?>
    <p class="error-message"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <form method="POST" action="login.php" id="loginForm">
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" name="username" id="username" 
             required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>

    <div class="mb-4">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" name="password" id="password" required>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-custom">Login</button>
    </div>

    <p class="mt-3 text-center">
      Don't have an account? <a href="register.php" class="text-info">Register</a>
    </p>
  </form>
</div>

<script>
// Client-side validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    if (username.length === 0) {
        e.preventDefault();
        alert('Username is required.');
        return false;
    }
    
    if (password.length === 0) {
        e.preventDefault();
        alert('Password is required.');
        return false;
    }
});
</script>

</body>
</html>
