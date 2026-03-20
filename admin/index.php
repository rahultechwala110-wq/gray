<?php
require_once 'includes/db.php';
redirect(isLoggedIn() ? ADMIN_URL . '/pages/dashboard' : ADMIN_URL . '/auth/login');
