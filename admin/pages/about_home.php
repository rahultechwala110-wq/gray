<?php
require_once '../includes/db.php';
requireLogin();

$conn->query("CREATE TABLE IF NOT EXISTS about_home (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    label       VARCHAR(200)  DEFAULT 'About Us',
    heading     TEXT,
    description TEXT,
    btn_text    VARCHAR(100)  DEFAULT 'Our Story',
    btn_link    VARCHAR(300)  DEFAULT '/about',
    small_image VARCHAR(300)  DEFAULT '',
    large_image VARCHAR(300)  DEFAULT '',
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$check = $conn->query("SELECT id FROM about_home WHERE id=1");
if (!$check || $check->num_rows === 0) {
    $conn->query("INSERT INTO about_home (id, label, heading, description, btn_text, btn_link)
                  VALUES (1, 'About Us', 'Lorem ipsum dolor sit amet,\nconsectetur adipiscing elit,',
                  'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                  'Our Story', '/about')");
}

function doUpload($fileKey, $folder, $saveName) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $file    = $_FILES[$fileKey];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($file['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $saveName . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $dir . $filename) ? $filename : false;
}

if (($_GET['action'] ?? '') === 'clear_images') {
    $conn->query("UPDATE about_home SET small_image='', large_image='' WHERE id=1");
    showAlert('Images cleared!', 'danger');
    redirect('about_home');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $existing = $conn->query("SELECT * FROM about_home WHERE id=1")->fetch_assoc();

    $label       = sanitize($conn, $_POST['label']       ?? 'About Us');
    $heading     = $conn->real_escape_string(trim($_POST['heading']     ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $btn_text    = sanitize($conn, $_POST['btn_text'] ?? 'Our Story');
    $btn_link    = sanitize($conn, $_POST['btn_link'] ?? '/about');

    $small_image = $existing['small_image'] ?? '';
    $large_image = $existing['large_image'] ?? '';

    $newSmall = doUpload('small_image', 'about', 'small-image');
    if ($newSmall !== false) $small_image = $newSmall;

    $newLarge = doUpload('large_image', 'about', 'large-image');
    if ($newLarge !== false) $large_image = $newLarge;

    $si = $conn->real_escape_string($small_image);
    $li = $conn->real_escape_string($large_image);

    $conn->query("UPDATE about_home SET
        label='$label', heading='$heading', description='$description',
        btn_text='$btn_text', btn_link='$btn_link',
        small_image='$si', large_image='$li'
        WHERE id=1");

    showAlert('About Home updated successfully! ✅');
    redirect('about_home');
}

require_once '../includes/header.php';

$data      = $conn->query("SELECT * FROM about_home WHERE id=1")->fetch_assoc();
$small_url = !empty($data['small_image']) ? UPLOAD_URL . 'about/' . $data['small_image'] : '';
$large_url = !empty($data['large_image']) ? UPLOAD_URL . 'about/' . $data['large_image'] : '';
?>

<div class="page-header">
    <h2><i class="fas fa-feather-alt"></i> About Home</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    About Home
</div>

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-edit"></i> Edit About Home Section</div>
    </div>

    <form method="POST" enctype="multipart/form-data" id="editForm">

        <div class="form-group">
            <label class="form-label">Label <span style="font-size:0.75rem;color:var(--text-light)">(e.g. "About Us")</span></label>
            <input type="text" name="label" class="form-control"
                   value="<?= htmlspecialchars($data['label'] ?? 'About Us') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Heading</label>
            <textarea name="heading" class="form-control" rows="3"><?= htmlspecialchars($data['heading'] ?? '') ?></textarea>
            <p style="font-size:0.75rem;color:var(--text-light);margin-top:4px"><i class="fas fa-info-circle"></i> New line = line break on website</p>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Button Text</label>
                <input type="text" name="btn_text" class="form-control"
                       value="<?= htmlspecialchars($data['btn_text'] ?? 'Our Story') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Button Link</label>
                <input type="text" name="btn_link" class="form-control"
                       value="<?= htmlspecialchars($data['btn_link'] ?? '/about') ?>">
            </div>
        </div>

        <div class="form-row" style="gap:24px;align-items:flex-start;margin-top:8px">

            <!-- ── Small Image ── -->
            <div class="form-group" style="flex:1">
                <label class="form-label"><i class="fas fa-image"></i> Small Image <span style="font-size:0.75rem;color:var(--text-light)">(Left — 4:5)</span></label>

                <?php if ($small_url): ?>
                <div style="margin-bottom:10px">
                    <img src="<?= $small_url ?>?v=<?= time() ?>"
                         style="height:90px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover">
                    <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($data['small_image']) ?>
                    </span>
                </div>
                <?php endif; ?>

                <div onclick="document.getElementById('smallInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                     ondragleave="this.style.borderColor='var(--cream-deep)'"
                     ondrop="handleDrop(event,'smallInput','smallPrev')"
                     style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
                    <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)"><?= $small_url ? 'Replace image' : 'Click or drag & drop' ?></p>
                    <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
                    <input type="file" name="small_image" id="smallInput" accept="image/*"
                           style="display:none" onchange="prevImg(this,'smallPrev')">
                </div>
                <div id="smallPrev" style="display:none;margin-top:8px">
                    <img id="smallPrevImg" style="max-width:160px;border-radius:8px;border:2px solid var(--success);display:block">
                    <span id="smallPrevName" style="font-size:0.75rem;color:var(--success);margin-top:3px;display:block;font-weight:600"></span>
                </div>
            </div>

            <!-- ── Large Image ── -->
            <div class="form-group" style="flex:1">
                <label class="form-label"><i class="fas fa-expand"></i> Large Image <span style="font-size:0.75rem;color:var(--text-light)">(Right — 4:5)</span></label>

                <?php if ($large_url): ?>
                <div style="margin-bottom:10px">
                    <img src="<?= $large_url ?>?v=<?= time() ?>"
                         style="height:90px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover">
                    <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($data['large_image']) ?>
                    </span>
                </div>
                <?php endif; ?>

                <div onclick="document.getElementById('largeInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                     ondragleave="this.style.borderColor='var(--cream-deep)'"
                     ondrop="handleDrop(event,'largeInput','largePrev')"
                     style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
                    <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)"><?= $large_url ? 'Replace image' : 'Click or drag & drop' ?></p>
                    <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
                    <input type="file" name="large_image" id="largeInput" accept="image/*"
                           style="display:none" onchange="prevImg(this,'largePrev')">
                </div>
                <div id="largePrev" style="display:none;margin-top:8px">
                    <img id="largePrevImg" style="max-width:160px;border-radius:8px;border:2px solid var(--success);display:block">
                    <span id="largePrevName" style="font-size:0.75rem;color:var(--success);margin-top:3px;display:block;font-weight:600"></span>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </form>
</div>

<script>
function prevImg(input, id) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById(id + 'Img').src = URL.createObjectURL(file);
    document.getElementById(id + 'Name').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById(id).style.display = 'block';
}
function handleDrop(e, inputId, prevId) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById(inputId).files = dt.files;
    prevImg(document.getElementById(inputId), prevId);
}
function scrollToForm() {
    document.getElementById('editForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php require_once '../includes/footer.php'; ?>