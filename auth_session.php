<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
