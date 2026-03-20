<?php
require_once '../includes/db.php';
requireLogin();
require_once '../includes/header.php';

// Get counts
$counts = [];
$tables = ['services','blog_posts','team_members','contact_messages','testimonials','portfolio_items','faqs','sliders'];
foreach ($tables as $t) {
    $r = $conn->query("SELECT COUNT(*) as c FROM $t");
    $counts[$t] = $r ? $r->fetch_assoc()['c'] : 0;
}
$unread = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'];
$recent = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    <span style="font-size:0.85rem;color:var(--text-light);"><?= date('l, d M Y') ?></span>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-cogs"></i></div>
        <div class="stat-info"><h3><?= $counts['services'] ?></h3><p>Services</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-blog"></i></div>
        <div class="stat-info"><h3><?= $counts['blog_posts'] ?></h3><p>Blog Posts</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info"><h3><?= $counts['team_members'] ?></h3><p>Team Members</p></div>
    </div>
    <div class="stat-card" style="border-left-color:var(--danger);">
        <div class="stat-icon" style="color:var(--danger);"><i class="fas fa-envelope"></i></div>
        <div class="stat-info"><h3><?= $counts['contact_messages'] ?></h3><p>Messages <span style="color:var(--danger);font-size:0.75rem;">(<?= $unread ?> unread)</span></p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-quote-left"></i></div>
        <div class="stat-info"><h3><?= $counts['testimonials'] ?></h3><p>Testimonials</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
        <div class="stat-info"><h3><?= $counts['portfolio_items'] ?></h3><p>Portfolio Items</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
        <div class="stat-info"><h3><?= $counts['faqs'] ?></h3><p>FAQs</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-images"></i></div>
        <div class="stat-info"><h3><?= $counts['sliders'] ?></h3><p>Sliders</p></div>
    </div>
</div>

<!-- Quick Links -->
<div class="form-row" style="margin-bottom:24px;">
    <div class="card" style="margin:0;">
        <div class="card-header"><div class="card-title"><i class="fas fa-bolt"></i> Quick Actions</div></div>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <a href="services.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Service</a>
            <a href="blog.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Blog Post</a>
            <a href="team.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Team Member</a>
            <a href="messages.php" class="btn btn-secondary btn-sm"><i class="fas fa-envelope"></i> View Messages</a>
            <a href="settings.php" class="btn btn-secondary btn-sm"><i class="fas fa-wrench"></i> Site Settings</a>
        </div>
    </div>
    <div class="card" style="margin:0;">
        <div class="card-header"><div class="card-title"><i class="fas fa-link"></i> Page Sections</div></div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <a href="hero.php" class="btn btn-secondary btn-sm">Hero</a>
            <a href="about.php" class="btn btn-secondary btn-sm">About</a>
            <a href="features.php" class="btn btn-secondary btn-sm">Features</a>
            <a href="stats.php" class="btn btn-secondary btn-sm">Stats</a>
            <a href="contact_info.php" class="btn btn-secondary btn-sm">Contact</a>
            <a href="faqs.php" class="btn btn-secondary btn-sm">FAQs</a>
        </div>
    </div>
</div>

<!-- Recent Messages -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-envelope-open-text"></i> Recent Messages</div>
        <a href="messages.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <?php if (empty($recent)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><h3>No messages yet</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><?= htmlspecialchars(substr($m['subject'],0,40)) ?>...</td>
                    <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                    <td><span class="badge <?= $m['is_read']?'badge-success':'badge-warning' ?>"><?= $m['is_read']?'Read':'Unread' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>