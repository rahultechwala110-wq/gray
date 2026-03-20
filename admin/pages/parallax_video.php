<?php
require_once '../includes/db.php';
requireLogin();

$conn->query("CREATE TABLE IF NOT EXISTS parallax_video (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    video_file   VARCHAR(300) DEFAULT '',
    opacity      DECIMAL(3,2) DEFAULT 0.80,
    height       INT DEFAULT 520,
    border_radius INT DEFAULT 40,
    show_sound_btn TINYINT(1) DEFAULT 1,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

if ($conn->query("SELECT id FROM parallax_video WHERE id=1")->num_rows === 0) {
    $conn->query("INSERT INTO parallax_video (id) VALUES (1)");
}

$action = $_GET['action'] ?? '';

// Clear video
if ($action === 'clear_video') {
    $conn->query("UPDATE parallax_video SET video_file='' WHERE id=1");
    showAlert('Video cleared!', 'danger');
    redirect('parallax_video');
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data         = $conn->query("SELECT * FROM parallax_video WHERE id=1")->fetch_assoc();
    $opacity      = min(1, max(0, (float)($_POST['opacity'] ?? 0.80)));
    $height       = (int)($_POST['height']        ?? 520);
    $br           = (int)($_POST['border_radius']  ?? 40);
    $sound        = isset($_POST['show_sound_btn']) ? 1 : 0;
    $video_file   = $data['video_file'] ?? '';
    $alertMsg     = '';

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
        $f   = $_FILES['video_file'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4','webm','ogg']) && $f['size'] <= 100*1024*1024) {
            $dir = ADMIN_UPLOAD_PATH . 'parallax/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $dest = $dir . 'video.' . $ext;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $video_file = 'video.' . $ext;
                $alertMsg   = 'Video uploaded! ✅';
            } else {
                showAlert('Upload failed. Dir writable: ' . (is_writable($dir)?'YES':'NO'), 'danger');
                redirect('parallax_video');
            }
        } else {
            showAlert('Invalid file. MP4/WebM/OGG only, max 100MB.', 'danger');
            redirect('parallax_video');
        }
    } elseif (isset($_FILES['video_file']) && $_FILES['video_file']['error'] !== 4) {
        $codes = [1=>'File too large (php.ini)',2=>'File too large',3=>'Partial upload',6=>'No temp folder',7=>'Cannot write'];
        showAlert('Upload error: ' . ($codes[$_FILES['video_file']['error']] ?? 'Unknown'), 'danger');
        redirect('parallax_video');
    }

    $vf = $conn->real_escape_string($video_file);
    $conn->query("UPDATE parallax_video SET video_file='$vf', opacity=$opacity, height=$height, border_radius=$br, show_sound_btn=$sound WHERE id=1");
    showAlert($alertMsg ?: 'Settings saved! ✅');
    redirect('parallax_video');
}

require_once '../includes/header.php';
$data     = $conn->query("SELECT * FROM parallax_video WHERE id=1")->fetch_assoc();
$videoUrl = $data['video_file'] ? UPLOAD_URL . 'parallax/' . $data['video_file'] : '';
?>

<div class="page-header">
    <h2><i class="fas fa-film"></i> Parallax Video</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Parallax Video
</div>

<!-- ══════════ FORM ══════════ -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-edit"></i> Edit Section</div>
    </div>
    <form method="POST" enctype="multipart/form-data">

        <!-- Video Upload -->
        <div class="form-group">
            <label class="form-label"><i class="fas fa-video"></i> Background Video
                <span style="font-size:0.75rem;color:var(--text-light)"> — saves to /public/parallax/video.mp4</span>
            </label>
            <?php if ($videoUrl): ?>
            <div style="margin-bottom:12px;padding:12px;background:var(--cream);border-radius:10px;border:1px solid var(--cream-deep)">
                <video src="<?= $videoUrl ?>?v=<?= time() ?>" controls
                       style="max-width:340px;width:100%;border-radius:8px;display:block"></video>
                <span style="font-size:0.75rem;color:var(--success);margin-top:6px;display:block;font-weight:600">
                    <i class="fas fa-check-circle"></i> Current: <?= $data['video_file'] ?>
                </span>
            </div>
            <?php endif; ?>
            <div onclick="document.getElementById('videoInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                 ondragleave="this.style.borderColor='var(--cream-deep)'"
                 ondrop="handleDrop(event)"
                 style="border:2px dashed var(--cream-deep);border-radius:10px;padding:22px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                <i class="fas fa-film" style="font-size:2rem;color:var(--gold);display:block;margin-bottom:8px"></i>
                <p style="font-size:0.85rem;font-weight:600;color:var(--text-mid)">Click or drag & drop video</p>
                <p style="font-size:0.75rem;color:var(--text-light);margin-top:4px">MP4, WebM, OGG • Max 100MB</p>
                <input type="file" name="video_file" id="videoInput" accept="video/*"
                       style="display:none" onchange="prevVideo(this)">
            </div>
            <div id="videoPrev" style="display:none;margin-top:10px">
                <video id="videoPrevEl" controls style="max-width:340px;width:100%;border-radius:8px;border:2px solid var(--success);display:block"></video>
                <span id="videoPrevName" style="font-size:0.75rem;color:var(--success);margin-top:4px;display:block;font-weight:600"></span>
            </div>
        </div>

        <!-- Settings -->
        <div class="form-row" style="margin-top:8px">
            <div class="form-group">
                <label class="form-label">Video Opacity
                    <span style="font-size:0.75rem;color:var(--text-light)"> (0 = transparent, 1 = full)</span>
                </label>
                <div style="display:flex;align-items:center;gap:12px">
                    <input type="range" name="opacity" min="0" max="1" step="0.05"
                           value="<?= $data['opacity'] ?? 0.80 ?>"
                           style="flex:1;accent-color:var(--gold)"
                           oninput="document.getElementById('opacityVal').textContent=parseFloat(this.value).toFixed(2)">
                    <span id="opacityVal" style="font-weight:700;color:var(--gold);min-width:36px"><?= number_format($data['opacity']??0.80,2) ?></span>
                </div>
            </div>
            <div class="form-group" style="max-width:160px">
                <label class="form-label">Height (px)</label>
                <input type="number" name="height" class="form-control"
                       value="<?= $data['height'] ?? 520 ?>" min="200" max="900" step="10">
            </div>
            <div class="form-group" style="max-width:180px">
                <label class="form-label">Border Radius (px)</label>
                <input type="number" name="border_radius" class="form-control"
                       value="<?= $data['border_radius'] ?? 40 ?>" min="0" max="100">
            </div>
        </div>

        <!-- Sound Button Toggle -->
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;width:fit-content">
                <div style="position:relative;width:44px;height:24px">
                    <input type="checkbox" name="show_sound_btn" id="soundToggle"
                           <?= ($data['show_sound_btn']??1) ? 'checked' : '' ?>
                           style="opacity:0;width:0;height:0;position:absolute"
                           onchange="document.getElementById('toggleTrack').style.background=this.checked?'var(--gold)':'#ccc';document.getElementById('toggleThumb').style.left=this.checked?'22px':'2px'">
                    <div id="toggleTrack" style="position:absolute;inset:0;border-radius:12px;background:<?= ($data['show_sound_btn']??1)?'var(--gold)':'#ccc' ?>;transition:background 0.2s"></div>
                    <div id="toggleThumb" style="position:absolute;top:2px;left:<?= ($data['show_sound_btn']??1)?'22':'2' ?>px;width:20px;height:20px;border-radius:50%;background:white;box-shadow:0 1px 4px rgba(0,0,0,0.2);transition:left 0.2s"></div>
                </div>
                <span class="form-label" style="margin:0">Show Sound Button</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </form>
</div>

<script>
function prevVideo(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('videoPrevEl').src = URL.createObjectURL(file);
    document.getElementById('videoPrevName').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById('videoPrev').style.display = 'block';
}
function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById('videoInput');
    input.files = dt.files;
    prevVideo(input);
}
</script>

<?php require_once '../includes/footer.php'; ?>
