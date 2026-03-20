<?php
require_once '../includes/db.php';
requireLogin();

$conn->query("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS video_file VARCHAR(300) DEFAULT ''");
$conn->query("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS overlay_opacity DECIMAL(3,2) DEFAULT 0.50");
$conn->query("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS show_sound_btn TINYINT(1) DEFAULT 1");
$conn->query("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS top_gradient TINYINT(1) DEFAULT 1");
$conn->query("ALTER TABLE hero_section ADD COLUMN IF NOT EXISTS bottom_gradient TINYINT(1) DEFAULT 1");

$hero = $conn->query("SELECT * FROM hero_section WHERE id=1")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $video_file = $hero['video_file'] ?? '';

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
        $file    = $_FILES['video_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4','webm','ogg'];

        if (!in_array($ext, $allowed)) {
            showAlert('Only MP4, WebM or OGG video is allowed!', 'danger');
            redirect('hero');
        }
        if ($file['size'] > 100 * 1024 * 1024) {
            showAlert('Video size must not exceed 100MB!', 'danger');
            redirect('hero');
        }

        $upload_dir = ADMIN_UPLOAD_PATH . 'hero-section/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $filename = 'banner.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $video_file = $filename;
        } else {
            showAlert('Video upload failed! Check folder permissions.', 'danger');
            redirect('hero');
        }
    }

    $overlay_opacity = number_format(min(1, max(0, floatval($_POST['overlay_opacity'] ?? 0.5))), 2);
    $show_sound_btn  = isset($_POST['show_sound_btn'])  ? 1 : 0;
    $top_gradient    = isset($_POST['top_gradient'])    ? 1 : 0;
    $bottom_gradient = isset($_POST['bottom_gradient']) ? 1 : 0;
    $vf              = $conn->real_escape_string($video_file);

    $conn->query("UPDATE hero_section SET
        video_file='$vf', overlay_opacity=$overlay_opacity,
        show_sound_btn=$show_sound_btn,
        top_gradient=$top_gradient, bottom_gradient=$bottom_gradient
        WHERE id=1");

    showAlert('Hero section updated successfully! ✅');
    redirect('hero');
}

require_once '../includes/header.php';

$video_path = ADMIN_UPLOAD_PATH . 'hero-section/banner.mp4';
$video_url  = UPLOAD_URL . 'hero-section/banner.mp4';
$has_video  = file_exists($video_path);
?>

<div class="page-header">
    <h2><i class="fas fa-film"></i> Hero Section</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Hero Section
</div>

<form method="POST" enctype="multipart/form-data">

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-video"></i> Background Video</div>
        <span style="font-size:0.75rem;color:var(--text-light);background:var(--cream-dark);padding:4px 10px;border-radius:20px">
            <i class="fas fa-folder"></i> saves as: admin/uploads/hero-section/banner.mp4
        </span>
    </div>

    <?php if ($has_video): ?>
    <div style="margin-bottom:20px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
            <span style="background:rgba(76,175,80,0.12);color:var(--success);padding:4px 12px;border-radius:20px;font-size:0.8rem;font-weight:600">
                <i class="fas fa-check-circle"></i> Video Uploaded
            </span>
            <span style="font-size:0.8rem;color:var(--text-light)">banner.mp4</span>
        </div>
        <video controls playsinline
               style="width:100%;max-width:640px;border-radius:12px;border:2px solid var(--cream-deep);display:block">
            <source src="<?= $video_url ?>?v=<?= time() ?>" type="video/mp4">
        </video>
    </div>
    <?php else: ?>
    <div style="background:var(--cream-dark);border:2px dashed var(--cream-deep);border-radius:12px;padding:40px 20px;text-align:center;margin-bottom:20px">
        <i class="fas fa-film" style="font-size:3rem;color:var(--text-light);display:block;margin-bottom:12px"></i>
        <p style="color:var(--text-light);font-weight:600">No video uploaded yet</p>
        <p style="font-size:0.8rem;color:var(--text-light);margin-top:4px">Upload a video below</p>
    </div>
    <?php endif; ?>

    <div class="form-group" style="margin-bottom:0">
        <label class="form-label">
            <i class="fas fa-upload"></i>
            <?= $has_video ? 'Upload New Video (Replaces Current)' : 'Upload Video' ?>
        </label>
        <div id="dropZone"
             onclick="document.getElementById('videoInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--gold)';this.style.background='rgba(200,149,42,0.05)'"
             ondragleave="this.style.borderColor='var(--cream-deep)';this.style.background='var(--cream)'"
             ondrop="handleDrop(event)"
             style="border:2px dashed var(--cream-deep);border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
            <i class="fas fa-cloud-upload-alt" style="font-size:2.2rem;color:var(--gold);display:block;margin-bottom:10px"></i>
            <p style="font-weight:700;color:var(--text-mid);margin-bottom:4px">Click or drag & drop to upload</p>
            <p style="font-size:0.78rem;color:var(--text-light)">
                MP4, WebM, OGG &bull; Max 100MB &bull;
                Saved as <strong style="color:var(--gold)">banner.mp4</strong>
            </p>
            <input type="file" name="video_file" id="videoInput"
                   accept="video/mp4,video/webm,video/ogg"
                   style="display:none" onchange="previewVideo(this)">
        </div>

        <div id="newPreview" style="display:none;margin-top:14px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                <span style="background:rgba(76,175,80,0.12);color:var(--success);padding:4px 12px;border-radius:20px;font-size:0.8rem;font-weight:600">
                    <i class="fas fa-check"></i> Selected:
                </span>
                <span id="vFileName" style="font-size:0.85rem;color:var(--text-mid);font-weight:600"></span>
            </div>
            <video id="vPreview" controls muted playsinline
                   style="width:100%;max-width:580px;border-radius:10px;border:2px solid var(--success);display:block">
            </video>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-sliders-h"></i> Video & Overlay Settings</div>
    </div>

    <div class="form-group">
        <label class="form-label">
            Dark Overlay Opacity &nbsp;
            <span id="opacityDisplay"
                  style="background:var(--gold);color:white;padding:2px 10px;border-radius:20px;font-size:0.82rem;font-weight:700">
                <?= round(($hero['overlay_opacity'] ?? 0.5) * 100) ?>%
            </span>
        </label>
        <input type="range" name="overlay_opacity"
               min="0" max="1" step="0.05"
               value="<?= $hero['overlay_opacity'] ?? 0.5 ?>"
               oninput="document.getElementById('opacityDisplay').textContent = Math.round(this.value*100)+'%'"
               style="width:100%;accent-color:var(--gold);height:8px;border-radius:4px;margin:8px 0">
        <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-light)">
            <span>0% — No overlay</span>
            <span>50% — Medium dark</span>
            <span>100% — Full black</span>
        </div>
    </div>

    <div class="form-row" style="gap:16px">
        <div class="form-group" style="background:var(--cream);border:1.5px solid var(--cream-deep);border-radius:10px;padding:16px">
            <label class="form-label" style="margin-bottom:10px">
                <i class="fas fa-volume-up" style="color:var(--gold)"></i> Sound Button
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:400">
                <input type="checkbox" name="show_sound_btn" value="1"
                       <?= ($hero['show_sound_btn'] ?? 1) ? 'checked' : '' ?>
                       style="width:20px;height:20px;accent-color:var(--gold);cursor:pointer">
                <div>
                    <span style="font-size:0.88rem;font-weight:600;display:block">Show mute/unmute button</span>
                    <span style="font-size:0.75rem;color:var(--text-light)">Displayed at bottom-right</span>
                </div>
            </label>
        </div>

        <div class="form-group" style="background:var(--cream);border:1.5px solid var(--cream-deep);border-radius:10px;padding:16px">
            <label class="form-label" style="margin-bottom:10px">
                <i class="fas fa-arrow-up" style="color:var(--gold)"></i> Top Gradient
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:400">
                <input type="checkbox" name="top_gradient" value="1"
                       <?= ($hero['top_gradient'] ?? 1) ? 'checked' : '' ?>
                       style="width:20px;height:20px;accent-color:var(--gold);cursor:pointer">
                <div>
                    <span style="font-size:0.88rem;font-weight:600;display:block">Show top black gradient</span>
                    <span style="font-size:0.75rem;color:var(--text-light)">from-black to-transparent</span>
                </div>
            </label>
        </div>

        <div class="form-group" style="background:var(--cream);border:1.5px solid var(--cream-deep);border-radius:10px;padding:16px">
            <label class="form-label" style="margin-bottom:10px">
                <i class="fas fa-arrow-down" style="color:var(--gold)"></i> Bottom Gradient
            </label>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:400">
                <input type="checkbox" name="bottom_gradient" value="1"
                       <?= ($hero['bottom_gradient'] ?? 1) ? 'checked' : '' ?>
                       style="width:20px;height:20px;accent-color:var(--gold);cursor:pointer">
                <div>
                    <span style="font-size:0.88rem;font-weight:600;display:block">Show bottom black gradient</span>
                    <span style="font-size:0.75rem;color:var(--text-light)">from-black to-transparent</span>
                </div>
            </label>
        </div>
    </div>
</div>

<div style="display:flex;gap:14px;align-items:center;padding-bottom:20px;flex-wrap:wrap">
    <button type="submit" class="btn btn-primary" style="padding:12px 36px;font-size:1rem">
        <i class="fas fa-save"></i> Save Changes
    </button>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>

</form>

<script>
function handleDrop(e) {
    e.preventDefault();
    const dz = document.getElementById('dropZone');
    dz.style.borderColor = 'var(--cream-deep)';
    dz.style.background  = 'var(--cream)';
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('videoInput').files = dt.files;
    previewVideo(document.getElementById('videoInput'));
}

function previewVideo(input) {
    const file = input.files[0];
    if (!file) return;
    const sizeMB = (file.size / 1024 / 1024).toFixed(1);
    document.getElementById('vFileName').textContent = file.name + ' (' + sizeMB + ' MB)';
    document.getElementById('vPreview').src = URL.createObjectURL(file);
    document.getElementById('newPreview').style.display = 'block';
    const dz = document.getElementById('dropZone');
    dz.style.borderColor = 'var(--success)';
    dz.style.background  = 'rgba(76,175,80,0.04)';
}
</script>

<?php require_once '../includes/footer.php'; ?>