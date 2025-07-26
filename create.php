<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'auth_session.php';
require 'db.php';

$message = '';

// Check if user has admin or editor role for creating posts
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    die("Access Denied: Insufficient permissions");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Enhanced server-side validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters.";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required.";
    } elseif (strlen($content) > 65535) {
        $errors[] = "Content is too long.";
    }
    
    // Sanitize inputs
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            header("Location: index.php?message=Post created successfully");
            exit();
        } else {
            $message = "Error adding post.";
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
  <title>Add Post</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
      margin-top: 80px;
      max-width: 600px;
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
    }

    .navbar {
      background-color: #0f172a;
    }

    .navbar-brand, .nav-link {
      color: #f8fafc !important;
    }

    .btn-primary {
      background-color: #0ea5e9;
      border: none;
    }

    .error {
      color: #e11d48;
    }
  </style>
</head>
<body>

<nav class="navbar fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">ApexPlanet</a>
    <a class="btn btn-sm btn-outline-light" href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
  <div class="card p-4">
    <h3 class="mb-4">📝 Add New Blog Post</h3>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="create.php" id="createForm">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required maxlength="255" 
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        <div class="form-text">Maximum 255 characters</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" rows="5" class="form-control" required maxlength="65535"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        <div class="form-text">Maximum 65,535 characters</div>
      </div>

      <button type="submit" class="btn btn-primary">Publish</button>
    </form>
  </div>
</div>

<script>
// Client-side validation
document.getElementById('createForm').addEventListener('submit', function(e) {
    const title = document.querySelector('input[name="title"]').value.trim();
    const content = document.querySelector('textarea[name="content"]').value.trim();
    
    if (title.length === 0) {
        e.preventDefault();
        alert('Title is required.');
        return false;
    }
    
    if (title.length > 255) {
        e.preventDefault();
        alert('Title must be less than 255 characters.');
        return false;
    }
    
    if (content.length === 0) {
        e.preventDefault();
        alert('Content is required.');
        return false;
    }
    
    if (content.length > 65535) {
        e.preventDefault();
        alert('Content is too long.');
        return false;
    }
});
</script>

</body>
</html>
