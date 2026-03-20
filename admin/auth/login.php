<?php
require_once '../includes/db.php';
if (isLoggedIn()) redirect(ADMIN_URL . '/pages/dashboard');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) {
        $error = 'Username and password both are required.';
    } else {
        $u = $conn->real_escape_string($username);
        $r = $conn->query("SELECT * FROM admin_users WHERE (username='$u' OR email='$u') AND is_active=1");
        $user = $r ? $r->fetch_assoc() : null;
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $conn->query("UPDATE admin_users SET last_login=NOW() WHERE id={$user['id']}");
            redirect(ADMIN_URL . '/pages/dashboard');
        } else {
            $error = 'Invalid username or password!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Admin Panel</title>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    background:var(--cream-dark);
    background-image: radial-gradient(ellipse at 20% 50%, rgba(200,149,42,0.1) 0%, transparent 60%),
                      radial-gradient(ellipse at 80% 20%, rgba(123,94,58,0.12) 0%, transparent 50%);
    font-family:'Nunito',sans-serif;
    min-height:100vh;
}
.login-wrap {
    width:100%; max-width:430px;
    padding:20px;
}
.login-box {
    background:#fff;
    border-radius:18px;
    padding:48px 40px;
    box-shadow:0 20px 60px rgba(123,94,58,0.18);
    border:1px solid var(--cream-deep);
    width:100%;
}
.login-logo { text-align:center; margin-bottom:30px; }
.login-logo i { font-size:2.8rem; color:var(--gold); }
.login-logo h1 {
    font-family:'Playfair Display',serif;
    font-size:1.7rem; color:var(--brown-dark);
    margin-top:10px;
}
.login-logo p { font-size:0.85rem; color:var(--text-light); margin-top:4px; }
.form-group { margin-bottom:18px; }
.form-group label {
    display:block; font-size:0.85rem; font-weight:600;
    color:var(--text-mid); margin-bottom:7px;
}
.form-control {
    width:100%; padding:11px 14px;
    border:1.5px solid var(--cream-deep);
    border-radius:8px; font-size:0.92rem;
    font-family:'Nunito',sans-serif;
    background:var(--cream); color:var(--text-dark);
    outline:none; transition:all 0.2s;
}
.form-control:focus { border-color:var(--gold); background:#fff; box-shadow:0 0 0 3px rgba(200,149,42,0.12); }
.btn-login {
    width:100%; padding:12px;
    background:linear-gradient(135deg, var(--gold), var(--brown));
    color:#fff; border:none; border-radius:8px;
    font-size:1rem; font-weight:700;
    cursor:pointer; font-family:'Nunito',sans-serif;
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:all 0.25s; margin-top:8px;
}
.btn-login:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(200,149,42,0.35); }
.alert-danger {
    background:#fff3f3; color:#c0392b;
    border:1.5px solid #f5c6c6; border-radius:8px;
    padding:11px 14px; font-size:0.88rem;
    margin-bottom:18px; display:flex; align-items:center; gap:8px;
}
.default-hint { text-align:center; margin-top:14px; font-size:0.78rem; color:var(--text-light); }
.pass-wrap { position:relative; }
.pass-toggle {
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer; color:var(--text-light); font-size:0.95rem;
}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <div class="login-logo">
            <img src="<?= ADMIN_URL ?>/images/logo-black.png" alt="Logo" style="max-width:130px; max-height:80px; object-fit:contain;">
        </div>

        <?php if ($error): ?>
        <div class="alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user" style="margin-right:5px"></i>Username / Email</label>
                <input type="text" name="username" class="form-control"
                       placeholder="Enter username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autofocus>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock" style="margin-right:5px"></i>Password</label>
                <div class="pass-wrap">
                    <input type="password" name="password" class="form-control"
                           id="passField" placeholder="Enter password"
                            style="padding-right:42px">
                    <button type="button" class="pass-toggle" onclick="togglePass()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
</div>
<script>
function togglePass() {
    const f = document.getElementById('passField');
    const i = document.getElementById('eyeIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>