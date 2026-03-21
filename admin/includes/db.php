<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost');

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'grayy');
    define('SITE_URL', 'http://localhost/gray');
    define('UPLOAD_URL', 'http://localhost/gray/admin/uploads/');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'gray_db');
    define('DB_PASS', 'Ef~!Px_0Bp5-m7zL'); 
    define('DB_NAME', 'gray_db');
    define('SITE_URL', 'https://gray.ninjamarketing360.com/'); 
    define('UPLOAD_URL', 'https://gray.ninjamarketing360.com/admin/uploads/');
}

define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', dirname(dirname(__DIR__)) . '/public/');
define('ADMIN_UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="padding:20px;background:#ffeded;color:#c00;border:1px solid #c00;margin:20px;border-radius:8px;font-family:sans-serif"><strong>Database Error:</strong> ' . $conn->connect_error . '</div>');
}
$conn->set_charset("utf8mb4");

function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}
function redirect($url) { header("Location: $url"); exit(); }
function showAlert($msg, $type = 'success') { $_SESSION['alert'] = ['msg' => $msg, 'type' => $type]; }
function isLoggedIn() { return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']); }
function requireLogin() {
    if (!isLoggedIn()) { redirect(ADMIN_URL . '/auth/login'); exit(); }
}
function getCurrentAdmin() {
    global $conn;
    if (!isLoggedIn()) return ['username' => 'Admin', 'full_name' => 'Admin'];
    $id = (int)$_SESSION['admin_id'];
    $r = $conn->query("SELECT * FROM admin_users WHERE id=$id");
    return ($r && $r->num_rows > 0) ? $r->fetch_assoc() : ['username' => 'Admin', 'full_name' => 'Admin'];
}