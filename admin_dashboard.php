<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'auth_session.php';
require 'db.php';

// Check if user has admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';
$error = '';

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];
    
    // Validate role
    $valid_roles = ['admin', 'editor', 'viewer'];
    if (!in_array($new_role, $valid_roles)) {
        $error = "Invalid role selected.";
    } else {
        // Prevent admin from changing their own role
        if ($user_id == $_SESSION['user_id']) {
            $error = "You cannot change your own role.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            if ($stmt->execute()) {
                $message = "User role updated successfully.";
            } else {
                $error = "Failed to update user role.";
            }
            $stmt->close();
        }
    }
}

// Get system statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_users'] = $result->fetch_assoc()['total'];
$stmt->close();

// Users by role
$stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
$result = $stmt->get_result();
$role_counts = [];
while ($row = $result->fetch_assoc()) {
    $role_counts[$row['role']] = $row['count'];
}
$stmt->close();

// Total posts
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM posts");
$stmt->execute();
$result = $stmt->get_result();
$stats['total_posts'] = $result->fetch_assoc()['total'];
$stmt->close();

// Get all users
$stmt = $conn->prepare("SELECT id, username, role FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ApexPlanet</title>
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
        .navbar {
            background-color: #0d6efd;
        }
        .navbar-brand, .nav-link, .btn-outline-light {
            color: white !important;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .role-badge {
            font-size: 0.8em;
            padding: 0.25em 0.5em;
        }
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-journal-richtext"></i> ApexPlanet
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                Admin Dashboard
                <span class="badge bg-warning text-dark role-badge"><?= ucfirst($_SESSION['role']) ?></span>
            </span>
            <a href="index.php" class="btn btn-outline-light me-2">
                <i class="bi bi-house"></i> Home
            </a>
            <a href="logout.php" class="btn btn-outline-light">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-4 text-primary">
        <i class="bi bi-gear"></i> Admin Dashboard
    </h2>

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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= $stats['total_users'] ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <i class="bi bi-people display-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= $stats['total_posts'] ?></h3>
                        <p class="mb-0">Total Posts</p>
                    </div>
                    <i class="bi bi-journal-text display-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><?= count($role_counts) ?></h3>
                        <p class="mb-0">User Roles</p>
                    </div>
                    <i class="bi bi-shield-check display-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Distribution -->
    <div class="glass-card p-4 mb-4">
        <h4 class="mb-3">
            <i class="bi bi-pie-chart"></i> User Role Distribution
        </h4>
        <div class="row">
            <?php foreach ($role_counts as $role => $count): ?>
                <div class="col-md-4 mb-2">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                        <span class="badge bg-primary"><?= ucfirst($role) ?></span>
                        <span class="fw-bold"><?= $count ?> users</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- User Management -->
    <div class="glass-card p-4">
        <h4 class="mb-3">
            <i class="bi bi-people-fill"></i> User Management
        </h4>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Current Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($user['username']) ?>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-info">You</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'editor' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary change-role-btn" 
                                            data-user-id="<?= $user['id'] ?>"
                                            data-username="<?= htmlspecialchars($user['username']) ?>"
                                            data-current-role="<?= $user['role'] ?>">
                                        <i class="bi bi-pencil"></i> Change Role
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">Cannot change own role</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Change Modal -->
<div class="modal fade" id="roleChangeModal" tabindex="-1" aria-labelledby="roleChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleChangeModalLabel">Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="modalUserId">
                    <div class="mb-3">
                        <label class="form-label">Username:</label>
                        <input type="text" class="form-control" id="modalUsername" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select New Role:</label>
                        <select name="new_role" class="form-select" required>
                            <option value="viewer">Viewer</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Role Permissions:</strong><br>
                        • <strong>Viewer:</strong> Can only view posts<br>
                        • <strong>Editor:</strong> Can create, edit, and delete posts<br>
                        • <strong>Admin:</strong> Full access including user management
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_role" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const roleChangeModal = new bootstrap.Modal(document.getElementById('roleChangeModal'));
    
    // Add click event to all change role buttons
    document.querySelectorAll('.change-role-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const currentRole = this.getAttribute('data-current-role');
            
            // Set modal values
            document.getElementById('modalUserId').value = userId;
            document.getElementById('modalUsername').value = username;
            document.querySelector('select[name="new_role"]').value = currentRole;
            
            // Show modal
            roleChangeModal.show();
        });
    });
    
    // Debug: Check if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap loaded successfully');
    } else {
        console.error('Bootstrap not loaded');
    }
    
    // Debug: Check if modal element exists
    const modalElement = document.getElementById('roleChangeModal');
    if (modalElement) {
        console.log('Modal element found');
    } else {
        console.error('Modal element not found');
    }
});
</script>

</body>
</html> 