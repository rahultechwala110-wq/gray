<?php
require_once 'includes/db.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE admin_users SET password='$hash', is_active=1 WHERE username='admin'");
echo '<div style="font-family:sans-serif;padding:30px;background:#f5f0e8;max-width:500px;margin:40px auto;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1)">';
echo '<h2 style="color:green">✅ Password Reset Ho Gaya!</h2>';
echo '<p style="margin:10px 0"><strong>Username:</strong> admin</p>';
echo '<p style="margin:10px 0"><strong>Password:</strong> admin123</p>';
echo '<p style="margin:10px 0"><strong>Updated Rows:</strong> ' . $conn->affected_rows . '</p>';
echo '<br><a href="' . ADMIN_URL . '/auth/login" style="background:#c8952a;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold">→ Login Page</a>';
echo '</div>';
echo '<p style="font-family:sans-serif;text-align:center;color:red;margin-top:10px">⚠️ Is file ko use ke baad DELETE kar do!</p>';
