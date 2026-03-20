<?php
require_once '../includes/db.php';
requireLogin();

// Create tables
$conn->query("CREATE TABLE IF NOT EXISTS instagram_settings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    label        VARCHAR(200) DEFAULT 'Follow us',
    title        VARCHAR(200) DEFAULT 'let\'s connected',
    hashtag      VARCHAR(200) DEFAULT '#thegrayuniverse',
    scroll_speed INT          DEFAULT 30,
    is_enabled   TINYINT(1)   DEFAULT 1,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS instagram_images (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    image      VARCHAR(300) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$check = $conn->query("SELECT id FROM instagram_settings WHERE id=1");
if (!$check || $check->num_rows === 0) {
    $conn->query("INSERT INTO instagram_settings (id) VALUES (1)");
}

$tab = $_GET['tab'] ?? 'settings';

// Delete image
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $row = $conn->query("SELECT image FROM instagram_images WHERE id=$id")->fetch_assoc();
    if ($row) {
        $path = ADMIN_UPLOAD_PATH . 'instagram/' . $row['image'];
        if (file_exists($path)) unlink($path);
        $conn->query("DELETE FROM instagram_images WHERE id=$id");
    }
    showAlert('Image deleted!', 'danger');
    redirect('instagram?tab=images');
}

// Toggle image
if (($_GET['action'] ?? '') === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE instagram_images SET is_active=1-is_active WHERE id=$id");
    redirect('instagram?tab=images');
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $label   = $conn->real_escape_string(trim($_POST['label']   ?? 'Follow us'));
    $title   = $conn->real_escape_string(trim($_POST['title']   ?? "let's connected"));
    $hashtag = $conn->real_escape_string(trim($_POST['hashtag'] ?? '#thegrayuniverse'));
    $speed   = max(5, min(120, (int)($_POST['scroll_speed']     ?? 30)));
    $enabled = isset($_POST['is_enabled']) ? 1 : 0;

    $conn->query("UPDATE instagram_settings SET
        label='$label', title='$title', hashtag='$hashtag',
        scroll_speed=$speed, is_enabled=$enabled
        WHERE id=1");

    showAlert('Settings updated! ✅');
    redirect('instagram?tab=settings');
}

// Upload image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $errCode = $_FILES['image']['error'];

    if ($errCode === 4) {
        showAlert('Please select an image.', 'danger');
        redirect('instagram?tab=images');
    }

    if ($errCode !== 0) {
        $codes = [1=>'File too large (php.ini)',2=>'File too large',3=>'Partial upload',6=>'No temp folder',7=>'Cannot write'];
        showAlert('Upload error: ' . ($codes[$errCode] ?? 'Unknown #'.$errCode), 'danger');
        redirect('instagram?tab=images');
    }

    $file    = $_FILES['image'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','avif'];

    if (!in_array($ext, $allowed)) {
        showAlert('Only JPG, PNG, WebP allowed.', 'danger');
        redirect('instagram?tab=images');
    }

    if ($file['size'] > 10 * 1024 * 1024) {
        showAlert('Max file size is 10MB.', 'danger');
        redirect('instagram?tab=images');
    }

    $dir = ADMIN_UPLOAD_PATH . 'instagram/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!is_writable($dir)) {
        showAlert('Upload folder not writable: ' . $dir, 'danger');
        redirect('instagram?tab=images');
    }

    $filename = 'img-' . time() . '-' . rand(100,999) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        $sort = (int)($conn->query("SELECT COUNT(*) as c FROM instagram_images")->fetch_assoc()['c']);
        $f    = $conn->real_escape_string($filename);
        $conn->query("INSERT INTO instagram_images (image, sort_order) VALUES ('$f', $sort)");
        showAlert('Image uploaded! ✅');
    } else {
        showAlert('Upload failed.', 'danger');
    }
    redirect('instagram?tab=images');
}

require_once '../includes/header.php';
$images = $conn->query("SELECT * FROM instagram_images ORDER BY sort_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);
$s      = $conn->query("SELECT * FROM instagram_settings WHERE id=1")->fetch_assoc();
?>

<div class="page-header">
    <h2><i class="fab fa-instagram"></i> Instagram Section</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Instagram
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=settings" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='settings'?'var(--gold)':'transparent' ?>;color:<?= $tab==='settings'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-cog"></i> Settings
    </a>
    <a href="?tab=images" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='images'?'var(--gold)':'transparent' ?>;color:<?= $tab==='images'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-images"></i> Images (<?= count($images) ?>)
    </a>
</div>

<?php if ($tab === 'settings'): ?>
<!-- ═══════════════════ SETTINGS TAB ═══════════════════ -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-cog"></i> Section Settings</div>
    </div>
    <form method="POST">
        <input type="hidden" name="save_settings" value="1">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Label <span style="font-size:0.75rem;color:var(--text-light)">(small text above title)</span></label>
                <input type="text" name="label" class="form-control"
                       value="<?= htmlspecialchars($s['label'] ?? 'Follow us') ?>"
                       placeholder="Follow us">
            </div>
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($s['title'] ?? "let's connected") ?>"
                       placeholder="let's connected">
            </div>
            <div class="form-group">
                <label class="form-label">Hashtag</label>
                <input type="text" name="hashtag" class="form-control"
                       value="<?= htmlspecialchars($s['hashtag'] ?? '#thegrayuniverse') ?>"
                       placeholder="#thegrayuniverse">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="flex:1">
                <label class="form-label">
                    Scroll Speed <span id="speedVal" style="color:var(--gold);font-weight:700"><?= $s['scroll_speed'] ?? 30 ?>s</span>
                    <span style="font-size:0.72rem;color:var(--text-light)"> (lower = faster)</span>
                </label>
                <input type="range" name="scroll_speed" min="5" max="120"
                       value="<?= $s['scroll_speed'] ?? 30 ?>"
                       style="width:100%;accent-color:var(--gold)"
                       oninput="document.getElementById('speedVal').textContent=this.value+'s'">
            </div>
            <div class="form-group" style="flex:0">
                <label class="form-label">Enable Section</label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-top:8px">
                    <input type="checkbox" name="is_enabled" value="1"
                           <?= ($s['is_enabled'] ?? 1) ? 'checked' : '' ?>
                           style="width:18px;height:18px;accent-color:var(--gold)">
                    <span style="font-size:0.85rem;font-weight:600">Show on website</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>

<?php else: ?>
<!-- ═══════════════════ IMAGES TAB ═══════════════════ -->

<!-- Upload Card -->
<div class="card" style="margin-bottom:24px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-upload"></i> Upload New Image</div>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Image <span style="font-size:0.75rem;color:var(--text-light)">(JPG, PNG, WebP • Max 10MB)</span></label>
            <div onclick="document.getElementById('imgInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                 ondragleave="this.style.borderColor='var(--cream-deep)'"
                 ondrop="handleDrop(event)"
                 style="border:2px dashed var(--cream-deep);border-radius:10px;padding:28px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--gold);display:block;margin-bottom:8px"></i>
                <p style="font-size:0.85rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
                <p style="font-size:0.72rem;color:var(--text-light);margin-top:4px">JPG, PNG, WebP • Max 10MB</p>
                <input type="file" name="image" id="imgInput" accept="image/*"
                       style="display:none" onchange="previewImg(this)">
            </div>
            <div id="imgPrev" style="display:none;margin-top:10px;align-items:center;gap:12px">
                <img id="imgPrevEl" style="height:100px;border-radius:8px;border:2px solid var(--success)">
                <span id="imgName" style="font-size:0.78rem;color:var(--success);font-weight:600"></span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Image</button>
    </form>
</div>

<!-- Images List -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Images (<?= count($images) ?>)</div>
    </div>

    <?php if (empty($images)): ?>
        <div style="padding:40px;text-align:center;color:var(--text-light)">
            <i class="fas fa-images" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:0.3"></i>
            <p>No images uploaded yet.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Preview</th><th>Filename</th><th>Uploaded</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($images as $i => $img): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <img src="<?= UPLOAD_URL ?>instagram/<?= htmlspecialchars($img['image']) ?>"
                             style="width:60px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--cream-deep)">
                    </td>
                    <td><strong style="font-size:0.85rem"><?= htmlspecialchars($img['image']) ?></strong></td>
                    <td><small style="color:var(--text-light)"><?= date('d M Y, h:i A', strtotime($img['created_at'])) ?></small></td>
                    <td>
                        <a href="?tab=images&action=toggle&id=<?= $img['id'] ?>">
                            <span class="badge <?= ($img['is_active'] ?? 1) ? 'badge-success' : 'badge-danger' ?>">
                                <?= ($img['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td>
                        <a href="?tab=images&action=delete&id=<?= $img['id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete this image?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<script>
function previewImg(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('imgPrevEl').src = URL.createObjectURL(file);
    document.getElementById('imgName').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById('imgPrev').style.display = 'flex';
}
function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById('imgInput');
    input.files = dt.files;
    previewImg(input);
}
</script>

<?php require_once '../includes/footer.php'; ?>