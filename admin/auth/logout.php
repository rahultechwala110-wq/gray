<?php
require_once '../includes/db.php';
session_destroy();
redirect(ADMIN_URL . '/auth/login');
