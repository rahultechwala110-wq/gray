<?php
require_once '../includes/db.php';
requireLogin();
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// DELETE
if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM services WHERE id=$id");
    showAlert('Service deleted!', 'danger');
    redirect('services.php');
}

// TOGGLE
if ($action === 'toggle' && $id) {
    $conn->query("UPDATE services SET is_active = 1 - is_active WHERE id=$id");
    redirect('services.php');
}

// SAVE (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = sanitize($conn, $_POST['title'] ?? '');
    $short  = sanitize($conn, $_POST['short_description'] ?? '');
    $full   = sanitize($conn, $_POST['full_description'] ?? '');
    $icon   = sanitize($conn, $_POST['icon'] ?? '');
    $price  = sanitize($conn, $_POST['price'] ?? '');
    $sort   = (int)($_POST['sort_order'] ?? 0);
    $feat   = isset($_POST['is_featured']) ? 1 : 0;
    $eid    = (int)($_POST['edit_id'] ?? 0);

    if ($eid) {
        $conn->query("UPDATE services SET title='$title', short_description='$short', full_description='$full', icon='$icon', price='$price', sort_order=$sort, is_featured=$feat WHERE id=$eid");
        showAlert('Service updated!');
    } else {
        $conn->query("INSERT INTO services (title, short_description, full_description, icon, price, sort_order, is_featured) VALUES ('$title','$short','$full','$icon','$price',$sort,$feat)");
        showAlert('Service added!');
    }
    redirect('services.php');
}

$edit = null;
if ($action === 'edit' && $id) {
    $edit = $conn->query("SELECT * FROM services WHERE id=$id")->fetch_assoc();
}

require_once '../includes/header.php';
$services = $conn->query("SELECT * FROM services ORDER BY sort_order, id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header">
    <h2><i class="fas fa-cogs"></i> Services</h2>
</div>
<div class="breadcrumb"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> Services</div>

<!-- Add/Edit Form -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $edit?'edit':'plus' ?>"></i> <?= $edit?'Edit':'Add New' ?> Service</div>
        <?php if ($edit): ?><a href="services.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST">
        <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Service Title *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit['title']??'') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Icon (Font Awesome class)</label>
                <input type="text" name="icon" class="form-control" value="<?= htmlspecialchars($edit['icon']??'') ?>" placeholder="fas fa-star">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Short Description</label>
            <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($edit['short_description']??'') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Full Description</label>
            <textarea name="full_description" class="form-control"><?= htmlspecialchars($edit['full_description']??'') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Price (optional)</label>
                <input type="text" name="price" class="form-control" value="<?= htmlspecialchars($edit['price']??'') ?>" placeholder="₹5000 / month">
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= $edit['sort_order']??0 ?>">
            </div>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
            <label class="toggle-switch">
                <input type="checkbox" name="is_featured" <?= ($edit['is_featured']??0)?'checked':'' ?>>
                <span class="toggle-slider"></span>
            </label>
            <label class="form-label" style="margin:0;">Featured Service?</label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit?'Update':'Add' ?> Service</button>
    </form>
</div>

<!-- List -->
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Services (<?= count($services) ?>)</div></div>
    <?php if (empty($services)): ?>
        <div class="empty-state"><i class="fas fa-cogs"></i><h3>No services added yet</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Icon</th><th>Title</th><th>Price</th><th>Featured</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($services as $i => $s): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><i class="<?= htmlspecialchars($s['icon']) ?>" style="font-size:1.3rem;color:var(--gold)"></i></td>
                    <td><strong><?= htmlspecialchars($s['title']) ?></strong><br><small style="color:var(--text-light)"><?= substr(htmlspecialchars($s['short_description']),0,50) ?>...</small></td>
                    <td><?= htmlspecialchars($s['price']?:'—') ?></td>
                    <td><span class="badge <?= $s['is_featured']?'badge-info':'badge-warning' ?>"><?= $s['is_featured']?'Yes':'No' ?></span></td>
                    <td><a href="?action=toggle&id=<?= $s['id'] ?>"><span class="badge <?= $s['is_active']?'badge-success':'badge-danger' ?>"><?= $s['is_active']?'Active':'Inactive' ?></span></a></td>
                    <td style="display:flex;gap:6px;">
                        <a href="?action=edit&id=<?= $s['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                        <a href="?action=delete&id=<?= $s['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete this service?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>