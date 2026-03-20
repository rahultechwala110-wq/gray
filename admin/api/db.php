<?php
$host = "localhost";
$user = "db_user";       // ← আপনার cPanel DB username
$pass = "db_password";   // ← আপনার cPanel DB password
$db   = "db_name";       // ← আপনার cPanel DB name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "DB connection failed"]));
}
$conn->set_charset("utf8mb4");

// Base URL for uploads - আপনার domain দিন
define('BASE_URL', 'https://yourdomain.com/admin/uploads/');
?>
