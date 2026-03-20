<?php
require_once '../includes/db.php';
requireLogin();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM team_members WHERE id=$id");
    showAlert('Member deleted!', 'danger');
    redirect('team.php');
}
if ($action === 'toggle' && $id) {
    $conn->query("UPDATE team_members SET is_active = 1-is_active WHERE id=$id");
    redirect('team.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($conn, $_POST['name'] ?? '');
    $desig = sanitize($conn, $_POST['designation'] ?? '');
    $bio   = sanitize($conn, $_POST['bio'] ?? '');
    $fb    = sanitize($conn, $_POST['facebook'] ?? '');
    $tw    = sanitize($conn, $_POST['twitter'] ?? '');
    $li    = sanitize($conn, $_POST['linkedin'] ?? '');
    $sort  = (int)($_POST['sort_order'] ?? 0);
    $eid   = (int)($_POST['edit_id'] ?? 0);
    if ($eid) {
        $conn->query("UPDATE team_members SET name='$name', designation='$desig', bio='$bio', facebook='$fb', twitter='$tw', linkedin='$li', sort_order=$sort WHERE id=$eid");
        showAlert('Member updated!');
    } else {
        $conn->query("INSERT INTO team_members (name, designation, bio, facebook, twitter, linkedin, sort_order) VALUES ('$name','$desig','$bio','$fb','$tw','$li',$sort)");
        showAlert('Member added!');
    }
    redirect('team.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $edit = $conn->query("SELECT * FROM team_members WHERE id=$id")->fetch_assoc();
}

require_once '../includes/header.php';
$members = $conn->query("SELECT * FROM team_members ORDER BY sort_order, id")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-users"></i> Team Members</h2></div>
<div class="breadcrumb"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> Team</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $edit?'edit':'plus' ?>"></i> <?= $edit?'Edit':'Add' ?> Member</div>
        <?php if ($edit): ?><a href="team.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif; ?>
    </div>
    <form method="POST">
        <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit['name']??'') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Designation</label>
                <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($edit['designation']??'') ?>" placeholder="CEO, Developer...">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control"><?= htmlspecialchars($edit['bio']??'') ?></textarea>
        </div>
        <div class="form-row-3">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook URL</label>
                <input type="url" name="facebook" class="form-control" value="<?= htmlspecialchars($edit['facebook']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-twitter" style="color:#1da1f2"></i> Twitter URL</label>
                <input type="url" name="twitter" class="form-control" value="<?= htmlspecialchars($edit['twitter']??'') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn URL</label>
                <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($edit['linkedin']??'') ?>">
            </div>
        </div>
        <div class="form-group" style="max-width:200px">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= $edit['sort_order']??0 ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit?'Update':'Add' ?> Member</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Members (<?= count($members) ?>)</div></div>
    <?php if (empty($members)): ?>
        <div class="empty-state"><i class="fas fa-users"></i><h3>No team members yet</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Name</th><th>Designation</th><th>Socials</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($members as $i => $m): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                    <td><?= htmlspecialchars($m['designation']?:'—') ?></td>
                    <td>
                        <?php if($m['facebook']): ?><a href="<?= $m['facebook'] ?>" target="_blank" style="color:#1877f2;margin-right:6px"><i class="fab fa-facebook"></i></a><?php endif; ?>
                        <?php if($m['twitter']): ?><a href="<?= $m['twitter'] ?>" target="_blank" style="color:#1da1f2;margin-right:6px"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if($m['linkedin']): ?><a href="<?= $m['linkedin'] ?>" target="_blank" style="color:#0077b5"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                    </td>
                    <td><a href="?action=toggle&id=<?= $m['id'] ?>"><span class="badge <?= $m['is_active']?'badge-success':'badge-danger' ?>"><?= $m['is_active']?'Active':'Inactive' ?></span></a></td>
                    <td style="display:flex;gap:6px;">
                        <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                        <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>