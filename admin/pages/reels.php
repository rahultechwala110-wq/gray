<?php
require_once '../includes/db.php';
requireLogin();

// Create tables
$conn->query("CREATE TABLE IF NOT EXISTS reels (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    video      VARCHAR(300) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS reels_settings (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    marquee_text    VARCHAR(200) DEFAULT 'GRAY',
    marquee_color   VARCHAR(20)  DEFAULT '#000000',
    marquee_opacity INT          DEFAULT 20,
    marquee_enabled TINYINT(1)   DEFAULT 1
)");

$check = $conn->query("SELECT id FROM reels_settings WHERE id=1");
if (!$check || $check->num_rows === 0) {
    $conn->query("INSERT INTO reels_settings (id) VALUES (1)");
}

$conn->query("ALTER TABLE reels ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1");

$tab = $_GET['tab'] ?? 'settings';

// Delete
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $row = $conn->query("SELECT video FROM reels WHERE id=$id")->fetch_assoc();
    if ($row) {
        $path = ADMIN_UPLOAD_PATH . 'reels/' . $row['video'];
        if (file_exists($path)) unlink($path);
        $conn->query("DELETE FROM reels WHERE id=$id");
    }
    showAlert('Reel deleted!', 'danger');
    redirect('reels?tab=reels');
}

// Toggle status
if (($_GET['action'] ?? '') === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE reels SET is_active=1-is_active WHERE id=$id");
    redirect('reels?tab=reels');
}

// Edit fetch
$editReel = null;
if (($_GET['action'] ?? '') === 'edit' && isset($_GET['id'])) {
    $editReel = $conn->query("SELECT * FROM reels WHERE id=" . (int)$_GET['id'])->fetch_assoc();
    $tab = 'reels';
}

// Save marquee settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $text    = $conn->real_escape_string(trim($_POST['marquee_text'] ?? 'GRAY'));
    $color   = $conn->real_escape_string($_POST['marquee_color']     ?? '#000000');
    $opacity = max(0, min(100, (int)($_POST['marquee_opacity']       ?? 20)));
    $enabled = isset($_POST['marquee_enabled']) ? 1 : 0;

    $conn->query("UPDATE reels_settings SET
        marquee_text='$text',
        marquee_color='$color',
        marquee_opacity=$opacity,
        marquee_enabled=$enabled
        WHERE id=1");

    showAlert('Settings updated! ✅');
    redirect('reels?tab=settings');
}

// Upload / Replace reel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $eid     = (int)($_POST['edit_id'] ?? 0);
    $errCode = $_FILES['video']['error'];

    if ($errCode === 4) {
        showAlert('Please select a video file.', 'danger');
        redirect('reels?tab=reels');
    }

    if ($errCode !== 0) {
        $codes = [
            1 => 'File too large (php.ini limit). Increase upload_max_filesize in php.ini.',
            2 => 'File too large (form limit).',
            3 => 'File was only partially uploaded.',
            6 => 'No temp folder found.',
            7 => 'Cannot write file to disk.',
        ];
        showAlert('Upload error: ' . ($codes[$errCode] ?? 'Unknown error #' . $errCode), 'danger');
        redirect('reels?tab=reels');
    }

    $file    = $_FILES['video'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['mp4', 'webm', 'mov'];

    if (!in_array($ext, $allowed)) {
        showAlert('Only MP4, WebM, MOV allowed.', 'danger');
        redirect('reels?tab=reels');
    }

    if ($file['size'] > 100 * 1024 * 1024) {
        showAlert('Max file size is 100MB.', 'danger');
        redirect('reels?tab=reels');
    }

    $dir = ADMIN_UPLOAD_PATH . 'reels/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!is_writable($dir)) {
        showAlert('Upload folder is not writable: ' . $dir, 'danger');
        redirect('reels?tab=reels');
    }

    $filename = 'reel-' . time() . '-' . rand(100, 999) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        if ($eid) {
            $old = $conn->query("SELECT video FROM reels WHERE id=$eid")->fetch_assoc();
            if ($old && file_exists(ADMIN_UPLOAD_PATH . 'reels/' . $old['video'])) {
                unlink(ADMIN_UPLOAD_PATH . 'reels/' . $old['video']);
            }
            $f = $conn->real_escape_string($filename);
            $conn->query("UPDATE reels SET video='$f' WHERE id=$eid");
            showAlert('Reel replaced successfully! ✅');
        } else {
            $sort = (int)($conn->query("SELECT COUNT(*) as c FROM reels")->fetch_assoc()['c']);
            $f    = $conn->real_escape_string($filename);
            $conn->query("INSERT INTO reels (video, sort_order) VALUES ('$f', $sort)");
            showAlert('Reel uploaded successfully! ✅');
        }
    } else {
        showAlert('Upload failed. Dir writable: ' . (is_writable($dir) ? 'YES' : 'NO'), 'danger');
    }
    redirect('reels?tab=reels');
}

require_once '../includes/header.php';
$reels = $conn->query("SELECT * FROM reels ORDER BY sort_order ASC, id ASC")->fetch_all(MYSQLI_ASSOC);
$s     = $conn->query("SELECT * FROM reels_settings WHERE id=1")->fetch_assoc();
?>

<div class="page-header">
    <h2><i class="fas fa-film"></i> Reels</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Reels
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=settings" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='settings'?'var(--gold)':'transparent' ?>;color:<?= $tab==='settings'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-text-width"></i> Marquee Settings
    </a>
    <a href="?tab=reels" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='reels'?'var(--gold)':'transparent' ?>;color:<?= $tab==='reels'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-film"></i> Reels (<?= count($reels) ?>)
    </a>
</div>

<?php if ($tab === 'settings'): ?>
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-text-width"></i> Marquee Strip Settings</div>
    </div>
    <form method="POST">
        <input type="hidden" name="save_settings" value="1">
        <div class="form-row">
            <div class="form-group" style="flex:2">
                <label class="form-label">Marquee Text</label>
                <input type="text" name="marquee_text" class="form-control"
                       value="<?= htmlspecialchars($s['marquee_text'] ?? 'GRAY') ?>" placeholder="GRAY">
                <p style="font-size:0.75rem;color:var(--text-light);margin-top:4px">
                    <i class="fas fa-info-circle"></i> This text will repeat across the strip
                </p>
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">Color</label>
                <div style="display:flex;align-items:center;gap:8px">
                    <input type="color" name="marquee_color"
                           value="<?= htmlspecialchars($s['marquee_color'] ?? '#000000') ?>"
                           style="width:48px;height:38px;border:1px solid var(--cream-deep);border-radius:6px;cursor:pointer;padding:2px"
                           oninput="document.getElementById('colorHex').value=this.value">
                    <input type="text" id="colorHex" class="form-control"
                           value="<?= htmlspecialchars($s['marquee_color'] ?? '#000000') ?>"
                           style="font-family:monospace"
                           oninput="document.querySelector('[name=marquee_color]').value=this.value">
                </div>
            </div>
            <div class="form-group" style="flex:1">
                <label class="form-label">
                    Opacity <span id="opacityVal" style="color:var(--gold);font-weight:700"><?= $s['marquee_opacity'] ?? 20 ?>%</span>
                </label>
                <input type="range" name="marquee_opacity" min="0" max="100"
                       value="<?= $s['marquee_opacity'] ?? 20 ?>"
                       style="width:100%;accent-color:var(--gold)"
                       oninput="document.getElementById('opacityVal').textContent=this.value+'%'">
            </div>
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="checkbox" name="marquee_enabled" value="1"
                       <?= ($s['marquee_enabled'] ?? 1) ? 'checked' : '' ?>
                       style="width:18px;height:18px;accent-color:var(--gold)">
                <span class="form-label" style="margin:0">Enable Marquee Strip</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>

<?php else: ?>
<div class="card" style="margin-bottom:24px" id="uploadCard">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-<?= $editReel ? 'edit' : 'upload' ?>"></i>
            <?= $editReel ? 'Replace Reel #' . $editReel['id'] : 'Upload New Reel' ?>
        </div>
        <?php if ($editReel): ?>
        <a href="?tab=reels" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
        <?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <?php if ($editReel): ?>
        <input type="hidden" name="edit_id" value="<?= $editReel['id'] ?>">
        <div style="margin-bottom:12px;padding:12px;background:var(--cream);border-radius:10px;border:1px solid var(--cream-deep)">
            <video src="<?= UPLOAD_URL ?>reels/<?= htmlspecialchars($editReel['video']) ?>"
                   controls style="max-width:200px;border-radius:8px;display:block"></video>
            <span style="font-size:0.75rem;color:var(--success);margin-top:6px;display:block;font-weight:600">
                <i class="fas fa-check-circle"></i> Current: <?= $editReel['video'] ?>
            </span>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label class="form-label">Video File <span style="font-size:0.75rem;color:var(--text-light)">(MP4, WebM, MOV • Max 100MB)</span></label>
            <div onclick="document.getElementById('videoInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                 ondragleave="this.style.borderColor='var(--cream-deep)'"
                 ondrop="handleDrop(event)"
                 style="border:2px dashed var(--cream-deep);border-radius:10px;padding:28px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                <i class="fas fa-video" style="font-size:2rem;color:var(--gold);display:block;margin-bottom:8px"></i>
                <p style="font-size:0.85rem;font-weight:600;color:var(--text-mid)">Click or drag & drop your video</p>
                <p style="font-size:0.72rem;color:var(--text-light);margin-top:4px">MP4, WebM, MOV • Max 100MB</p>
                <input type="file" name="video" id="videoInput" accept="video/*"
                       style="display:none" onchange="previewVideo(this)">
            </div>
            <div id="videoPrev" style="display:none;margin-top:10px;align-items:center;gap:12px">
                <video id="videoEl" controls style="max-width:200px;border-radius:8px;border:2px solid var(--success)"></video>
                <span id="videoName" style="font-size:0.78rem;color:var(--success);font-weight:600"></span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-<?= $editReel ? 'sync' : 'upload' ?>"></i>
            <?= $editReel ? 'Replace Reel' : 'Upload Reel' ?>
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Reels (<?= count($reels) ?>)</div>
    </div>
    <?php if (empty($reels)): ?>
        <div style="padding:40px;text-align:center;color:var(--text-light)">
            <i class="fas fa-film" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:0.3"></i>
            <p>No reels uploaded yet.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Preview</th><th>Filename</th><th>Uploaded</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($reels as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <video src="<?= UPLOAD_URL ?>reels/<?= htmlspecialchars($r['video']) ?>"
                               style="width:50px;height:80px;object-fit:cover;border-radius:8px;background:#000;display:block"
                               muted playsinline
                               onmouseenter="this.play()" onmouseleave="this.pause();this.currentTime=0">
                        </video>
                    </td>
                    <td><strong style="font-size:0.85rem"><?= htmlspecialchars($r['video']) ?></strong></td>
                    <td><small style="color:var(--text-light)"><?= date('d M Y, h:i A', strtotime($r['created_at'])) ?></small></td>
                    <td>
                        <a href="?tab=reels&action=toggle&id=<?= $r['id'] ?>">
                            <span class="badge <?= ($r['is_active'] ?? 1) ? 'badge-success' : 'badge-danger' ?>">
                                <?= ($r['is_active'] ?? 1) ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?tab=reels&action=edit&id=<?= $r['id'] ?>"
                           class="btn btn-secondary btn-sm btn-icon"
                           onclick="setTimeout(()=>document.getElementById('uploadCard').scrollIntoView({behavior:'smooth'}),50)">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?tab=reels&action=delete&id=<?= $r['id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="return confirm('Delete this reel?')">
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
function previewVideo(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('videoEl').src = URL.createObjectURL(file);
    document.getElementById('videoName').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById('videoPrev').style.display = 'flex';
}
function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById('videoInput');
    input.files = dt.files;
    previewVideo(input);
}
</script>

<?php require_once '../includes/footer.php'; ?>