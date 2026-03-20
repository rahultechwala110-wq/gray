<?php
require_once '../includes/db.php';
requireLogin();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM contact_messages WHERE id=$id");
    showAlert('Message deleted!', 'danger');
    redirect('messages.php');
}
if ($action === 'read' && $id) {
    $conn->query("UPDATE contact_messages SET is_read=1 WHERE id=$id");
}

require_once '../includes/header.php';

$view = null;
if ($action === 'view' && $id) {
    $conn->query("UPDATE contact_messages SET is_read=1 WHERE id=$id");
    $view = $conn->query("SELECT * FROM contact_messages WHERE id=$id")->fetch_assoc();
}

$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-envelope"></i> Contact Messages</h2></div>
<div class="breadcrumb"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> Messages</div>

<?php if ($view): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-envelope-open"></i> Message Detail</div>
        <a href="messages.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        <div><label class="form-label">From</label><p><strong><?= htmlspecialchars($view['name']) ?></strong></p></div>
        <div><label class="form-label">Email</label><p><a href="mailto:<?= $view['email'] ?>"><?= htmlspecialchars($view['email']) ?></a></p></div>
        <div><label class="form-label">Phone</label><p><?= htmlspecialchars($view['phone']?:'—') ?></p></div>
        <div><label class="form-label">Date</label><p><?= date('d M Y, h:i A', strtotime($view['created_at'])) ?></p></div>
    </div>
    <div class="form-group"><label class="form-label">Subject</label><p><strong><?= htmlspecialchars($view['subject']) ?></strong></p></div>
    <div class="form-group"><label class="form-label">Message</label>
        <div style="background:var(--cream);border:2px solid var(--cream-deep);border-radius:8px;padding:16px;line-height:1.7;">
            <?= nl2br(htmlspecialchars($view['message'])) ?>
        </div>
    </div>
    <a href="mailto:<?= $view['email'] ?>?subject=Re: <?= urlencode($view['subject']) ?>" class="btn btn-primary"><i class="fas fa-reply"></i> Reply via Email</a>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-inbox"></i> All Messages (<?= count($messages) ?>)</div></div>
    <?php if (empty($messages)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><h3>No messages received</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $i => $m): ?>
                <tr style="<?= !$m['is_read']?'font-weight:700;':'' ?>">
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($m['name']) ?> <?= !$m['is_read']?'<span class="badge badge-warning" style="font-size:.65rem">NEW</span>':'' ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><?= htmlspecialchars(substr($m['subject'],0,40)) ?>...</td>
                    <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                    <td><span class="badge <?= $m['is_read']?'badge-success':'badge-warning' ?>"><?= $m['is_read']?'Read':'Unread' ?></span></td>
                    <td style="display:flex;gap:5px;">
                        <a href="?action=view&id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-eye"></i></a>
                        <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete message?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>