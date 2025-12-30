<?php
// Database connection for admin panel
$host = 'localhost';
$db   = 'dunzo_db';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?> 