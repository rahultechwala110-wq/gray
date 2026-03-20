<?php
requireLogin();
$admin = getCurrentAdmin();
$page = basename($_SERVER['PHP_SELF'], '.php');
$alert = '';
if (isset($_SESSION['alert'])) {
    $a = $_SESSION['alert'];
    $icon = $a['type'] === 'success' ? 'check-circle' : 'exclamation-circle';
    $alert = "<div class='alert alert-{$a['type']}'><i class='fas fa-$icon'></i> {$a['msg']}</div>";
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel — <?= ucfirst(str_replace(['_','-'],' ',$page)) ?></title>
<link rel="stylesheet" href="<?= ADMIN_URL ?>/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ── Logout Confirmation Modal ── -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
        <button class="logout-modal-close" onclick="closeLogoutModal()" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
        <div class="logout-modal-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h2 id="logoutTitle">Sign Out?</h2>
        <p>Are you sure you want to log out of the admin panel? Any unsaved changes will be lost.</p>
        <div class="logout-modal-actions">
            <button class="btn-modal-cancel" onclick="closeLogoutModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <a href="<?= ADMIN_URL ?>/auth/logout" class="btn-modal-confirm">
                <i class="fas fa-sign-out-alt"></i> Yes, Logout
            </a>
        </div>
    </div>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
    <img src="<?= ADMIN_URL ?>/images/logo-white.png" alt="Logo" style="max-height:45px; object-fit:contain;">
</div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="<?= ADMIN_URL ?>/pages/dashboard" class="nav-item <?= $page=='dashboard'?'active':'' ?>">
            <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
        </a>

        <div class="nav-label">Home Page</div>
        <a href="<?= ADMIN_URL ?>/pages/hero" class="nav-item <?= $page=='hero'?'active':'' ?>">
            <i class="fas fa-image"></i><span>Hero Section</span>
        </a>
         <a href="<?= ADMIN_URL ?>/pages/fragrances" class="nav-item <?= $page=='fragrances'?'active':'' ?>">
            <i class="fas fa-spray-can"></i><span>Featured Fragrances</span>
        </a>
         <a href="<?= ADMIN_URL ?>/pages/about_home" class="nav-item <?= $page=='about_home'?'active':'' ?>">
            <i class="fas fa-feather-alt"></i><span>About</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/collections" class="nav-item <?= $page=='collections'?'active':'' ?>">
            <i class="fas fa-th-large"></i><span>Collections</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/featured_product" class="nav-item <?= $page=='featured_product'?'active':'' ?>">
            <i class="fas fa-star"></i><span>Featured Product</span>
        </a>
         <a href="<?= ADMIN_URL ?>/pages/parallax_video" class="nav-item <?= $page=='parallax_video'?'active':'' ?>">
            <i class="fas fa-film"></i><span>Video</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/testimonials" class="nav-item <?= $page=='testimonials'?'active':'' ?>">
            <i class="fas fa-quote-left"></i><span>Testimonials</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/reels" class="nav-item <?= $page=='reels'?'active':'' ?>">
             <i class="fas fa-film"></i><span>Reels</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/instagram" class="nav-item <?= $page=='instagram'?'active':'' ?>">
           <i class="fab fa-instagram"></i><span>Instagram</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/blog" class="nav-item <?= $page=='blog'?'active':'' ?>">
            <i class="fas fa-blog"></i><span>Blog / News</span>
        </a>

        <div class="nav-label">About Page</div>
        <a href="<?= ADMIN_URL ?>/pages/about" class="nav-item <?= $page=='about'?'active':'' ?>">
           <i class="fas fa-feather-alt"></i><span>About Section</span>
        </a>

        <div class="nav-label">Product Page</div>
        <a href="<?= ADMIN_URL ?>/pages/products_showcase" class="nav-item <?= $page=='products_showcase'?'active':'' ?>">
            <i class="fas fa-box-open"></i><span>All Products</span>
        </a>

       <div class="nav-label">Product Details Page</div>
<a href="<?= ADMIN_URL ?>/pages/product_detail" class="nav-item <?= $page=='product_detail'?'active':'' ?>">
    <i class="fas fa-flask"></i><span>Product Detail Pages</span>
</a>

         <div class="nav-label">Contact Page</div>
        <a href="<?= ADMIN_URL ?>/pages/contact_info" class="nav-item <?= $page=='contact'?'active':'' ?>">
            <i class="fas fa-envelope"></i><span>Contact Section</span>
        </a>
        <!-- <a href="<?= ADMIN_URL ?>/pages/sliders" class="nav-item <?= $page=='sliders'?'active':'' ?>">
            <i class="fas fa-sliders-h"></i><span>Sliders / Banners</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/features" class="nav-item <?= $page=='features'?'active':'' ?>">
            <i class="fas fa-star"></i><span>Features</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/stats" class="nav-item <?= $page=='stats'?'active':'' ?>">
            <i class="fas fa-chart-bar"></i><span>Stats / Counters</span>
        </a>

        <div class="nav-label">Pages</div>
        <a href="<?= ADMIN_URL ?>/pages/about" class="nav-item <?= $page=='about'?'active':'' ?>">
            <i class="fas fa-info-circle"></i><span>About Page</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/team" class="nav-item <?= $page=='team'?'active':'' ?>">
            <i class="fas fa-users"></i><span>Team Members</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/services" class="nav-item <?= $page=='services'?'active':'' ?>">
            <i class="fas fa-cogs"></i><span>Services</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/portfolio" class="nav-item <?= $page=='portfolio'?'active':'' ?>">
            <i class="fas fa-briefcase"></i><span>Portfolio</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/blog" class="nav-item <?= $page=='blog'?'active':'' ?>">
            <i class="fas fa-blog"></i><span>Blog / News</span>
        </a>
        
        <a href="<?= ADMIN_URL ?>/pages/faqs" class="nav-item <?= $page=='faqs'?'active':'' ?>">
            <i class="fas fa-question-circle"></i><span>FAQs</span>
        </a>

        <div class="nav-label">Contact</div>
        <a href="<?= ADMIN_URL ?>/pages/contact_info" class="nav-item <?= $page=='contact_info'?'active':'' ?>">
            <i class="fas fa-map-marker-alt"></i><span>Contact Info</span>
        </a>
        <a href="<?= ADMIN_URL ?>/pages/messages" class="nav-item <?= $page=='messages'?'active':'' ?>">
            <i class="fas fa-envelope"></i><span>Messages</span>
        </a>

        <div class="nav-label">System</div>
        <a href="<?= ADMIN_URL ?>/pages/settings" class="nav-item <?= $page=='settings'?'active':'' ?>">
            <i class="fas fa-wrench"></i><span>Site Settings</span>
        </a>
        <a href="javascript:void(0)" onclick="openLogoutModal()" class="nav-item nav-logout">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a> -->
    </nav>
</div>

<div class="main-wrapper">
    <header class="topbar">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-title"><?= ucfirst(str_replace(['_','-'],' ',$page)) ?></div>
        <div class="topbar-right">
            <span class="admin-name">
                <i class="fas fa-user-circle"></i>
                <?= htmlspecialchars($admin['full_name'] ?? $admin['username'] ?? 'Admin') ?>
            </span>
            <button onclick="openLogoutModal()" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </header>
    <main class="content-area">
        <?= $alert ?>

<script>
function openLogoutModal() {
    document.getElementById('logoutOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLogoutModal() {
    document.getElementById('logoutOverlay').classList.remove('active');
    document.body.style.overflow = '';
}
// Close on overlay backdrop click
document.getElementById('logoutOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeLogoutModal();
});
// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLogoutModal();
});
</script>