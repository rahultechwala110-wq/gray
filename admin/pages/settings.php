<?php
require_once '../includes/db.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $k = sanitize($conn, $key);
        $v = sanitize($conn, $value);
        $conn->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE setting_value='$v'");
    }
    showAlert('Settings saved successfully!');
    redirect('settings.php');
}

require_once '../includes/header.php';
$settings = [];
$res = $conn->query("SELECT * FROM site_settings");
while ($row = $res->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
function s($settings, $key) { return htmlspecialchars($settings[$key] ?? ''); }
?>
<div class="page-header"><h2><i class="fas fa-wrench"></i> Site Settings</h2></div>
<div class="breadcrumb"><a href="dashboard.php">Dashboard</a> <i class="fas fa-chevron-right" style="font-size:.7rem"></i> Settings</div>

<form method="POST">
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-globe"></i> General Settings</div></div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Site Name</label>
            <input type="text" name="site_name" class="form-control" value="<?= s($settings,'site_name') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Site Tagline</label>
            <input type="text" name="site_tagline" class="form-control" value="<?= s($settings,'site_tagline') ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="form-label">Meta Description</label>
        <textarea name="meta_description" class="form-control" style="min-height:80px"><?= s($settings,'meta_description') ?></textarea>
    </div>
    <div class="form-group">
        <label class="form-label">Meta Keywords</label>
        <input type="text" name="meta_keywords" class="form-control" value="<?= s($settings,'meta_keywords') ?>">
    </div>
    <div class="form-group">
        <label class="form-label">Footer Text</label>
        <input type="text" name="footer_text" class="form-control" value="<?= s($settings,'footer_text') ?>">
    </div>
    <div class="form-group">
        <label class="form-label">Google Analytics ID</label>
        <input type="text" name="google_analytics" class="form-control" value="<?= s($settings,'google_analytics') ?>" placeholder="G-XXXXXXXXXX">
    </div>
    <div class="form-group" style="display:flex;align-items:center;gap:12px;">
        <label class="toggle-switch">
            <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode']??0)?'checked':'' ?>>
            <span class="toggle-slider"></span>
        </label>
        <label class="form-label" style="margin:0;color:var(--danger)"><i class="fas fa-tools"></i> Maintenance Mode</label>
    </div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
</form>
<?php require_once '../includes/footer.php'; ?>