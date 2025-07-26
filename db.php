<?php
$conn = new mysqli("localhost", "root", "tiger", "blog");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
