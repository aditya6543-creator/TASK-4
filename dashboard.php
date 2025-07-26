<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

// Get logged-in user's username
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <p>
        <a href="index.php">View Posts</a> |
        <a href="create.php">Create New Post</a> |
        <a href="logout.php">Logout</a>
    </p>

</body>
</html>
