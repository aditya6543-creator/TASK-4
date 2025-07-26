<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'auth_session.php';
require 'db.php';

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE title LIKE ? OR content LIKE ?" : '';
$sql = "SELECT * FROM posts $searchQuery ORDER BY created_at DESC LIMIT ? OFFSET ?";
$countSql = "SELECT COUNT(*) as total FROM posts $searchQuery";

$stmt = $conn->prepare($sql);
$countStmt = $conn->prepare($countSql);

if ($search) {
    $likeSearch = "%$search%";
    $stmt->bind_param("ssii", $likeSearch, $likeSearch, $limit, $offset);
    $countStmt->bind_param("ss", $likeSearch, $likeSearch);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$totalPosts = $totalResult['total'];
$totalPages = ceil($totalPosts / $limit);

// Get success/error messages
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🪄 ApexPlanet Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(145deg, #dfe9f3, #ffffff);
      font-family: 'Segoe UI', sans-serif;
    }
    .glass-card {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.75);
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      transition: 0.3s ease;
    }
    .glass-card:hover {
      transform: scale(1.01);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }
    .navbar {
      background-color: #0d6efd;
    }
    .navbar-brand, .nav-link, .btn-outline-light {
      color: white !important;
    }
    .form-control:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    .pagination .page-link {
      border-radius: 10px;
      transition: 0.2s ease-in-out;
    }
    .pagination .page-link:hover {
      background-color: #0d6efd;
      color: white;
    }
    .role-badge {
      font-size: 0.8em;
      padding: 0.25em 0.5em;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-journal-richtext"></i> ApexPlanet</a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 
        <span class="badge bg-light text-dark role-badge"><?= ucfirst($_SESSION['role']) ?></span>
      </span>
      <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
        <a href="create.php" class="btn btn-success me-2"><i class="bi bi-plus-circle"></i> New Post</a>
      <?php endif; ?>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin_dashboard.php" class="btn btn-warning me-2"><i class="bi bi-gear"></i> Admin</a>
      <?php endif; ?>
      <a href="logout.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h2 class="mb-4 text-primary"><i class="bi bi-pencil-square"></i> Blog Posts</h2>

  <!-- Success/Error Messages -->
  <?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle"></i> <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Search -->
  <form class="input-group mb-5 shadow-sm" method="GET">
    <input type="text" class="form-control" name="search" placeholder="🔍 Search by title or content..." value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-primary" type="submit">Search</button>
  </form>

  <!-- Posts -->
  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="glass-card p-4 mb-4">
        <h4 class="text-dark"><?= htmlspecialchars($row['title']) ?></h4>
        <p class="text-muted"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
        <div class="d-flex justify-content-between align-items-center">
          <small class="text-muted">
            <i class="bi bi-calendar"></i> <?= date('F j, Y', strtotime($row['created_at'])) ?>
          </small>
          <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'editor'): ?>
            <div>
              <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm me-2">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" 
                 onclick="return confirm('Are you sure you want to delete this post?')">
                <i class="bi bi-trash"></i> Delete
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="text-center py-5">
      <i class="bi bi-journal-x display-1 text-muted"></i>
      <h4 class="text-muted mt-3">No posts found</h4>
      <?php if ($search): ?>
        <p class="text-muted">No posts match your search criteria.</p>
      <?php else: ?>
        <p class="text-muted">No posts have been created yet.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <nav>
      <ul class="pagination justify-content-center mt-5">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
