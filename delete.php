<?php
require 'auth_session.php';
require 'db.php';

// Check if user has admin or editor role for deletion
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    die("Access Denied: Insufficient permissions");
}

// Validate and sanitize the ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Success
    header("Location: index.php?message=Post deleted successfully");
} else {
    // Error
    header("Location: index.php?error=Failed to delete post");
}
$stmt->close();
exit();
?>
