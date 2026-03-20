<?php
require_once '../includes/db.php';
requireLogin();

// ── Create Tables ─────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS collections_hero (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(200) DEFAULT 'New Arrivals',
    btn_text      VARCHAR(100) DEFAULT 'Shop Collection',
    btn_link      VARCHAR(300) DEFAULT '#',
    video_file    VARCHAR(300) DEFAULT '',
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS collections_products (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(200) NOT NULL,
    image      VARCHAR(300) DEFAULT '',
    link       VARCHAR(300) DEFAULT '#',
    btn_text   VARCHAR(100) DEFAULT 'Shop Essential',
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default hero row
$heroCheck = $conn->query("SELECT id FROM collections_hero WHERE id=1");
if ($heroCheck->num_rows === 0) {
    $conn->query("INSERT INTO collections_hero (id, title, btn_text, btn_link) VALUES (1,'New Arrivals','Shop Collection','#')");
}

// ── Upload Helper ─────────────────────────────────────────────
function uploadCollectionFile($fileKey, $subfolder, $saveName, $allowedExt) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $file = $_FILES[$fileKey];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) return false;
    if ($file['size'] > 100 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . $subfolder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $saveName . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) return $filename;
    return false;
}

// ── Actions ───────────────────────────────────────────────────
$tab    = $_GET['tab']    ?? 'hero';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// DELETE product
if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM collections_products WHERE id=$id");
    showAlert('Product deleted!', 'danger');
    redirect('collections?tab=products');
}

// TOGGLE product
if ($action === 'toggle' && $id) {
    $conn->query("UPDATE collections_products SET is_active=1-is_active WHERE id=$id");
    redirect('collections?tab=products');
}

// ── SAVE HERO ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'hero') {
    $title    = sanitize($conn, $_POST['title']    ?? 'New Arrivals');
    $btn_text = sanitize($conn, $_POST['btn_text'] ?? 'Shop Collection');
    $btn_link = sanitize($conn, $_POST['btn_link'] ?? '#');

    $hero      = $conn->query("SELECT * FROM collections_hero WHERE id=1")->fetch_assoc();
    $video_file = $hero['video_file'] ?? '';

    $newVideo = uploadCollectionFile('video_file', 'collections', 'new-arrival', ['mp4','webm','ogg']);
    if ($newVideo !== false) $video_file = $newVideo;

    $vf = $conn->real_escape_string($video_file);
    $conn->query("UPDATE collections_hero SET title='$title', btn_text='$btn_text', btn_link='$btn_link', video_file='$vf' WHERE id=1");
    showAlert('Hero section updated! ✅');
    redirect('collections?tab=hero');
}

// ── SAVE PRODUCT ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'products') {
    $name     = sanitize($conn, $_POST['name']     ?? '');
    $link     = sanitize($conn, $_POST['link']     ?? '#');
    $btn_text = sanitize($conn, $_POST['btn_text'] ?? 'Shop Essential');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $eid      = (int)($_POST['edit_id']    ?? 0);
    $imgSlug  = strtolower(preg_replace('/[^a-z0-9]/i', '-', $name)) . '-' . time();

    if ($eid) {
        $existing = $conn->query("SELECT image FROM collections_products WHERE id=$eid")->fetch_assoc();
        $image    = $existing['image'] ?? '';
        $newImg   = uploadCollectionFile('image', 'collections', $imgSlug, ['jpg','jpeg','png','webp','avif']);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("UPDATE collections_products SET name='$name', link='$link', btn_text='$btn_text', sort_order=$sort, image='$img' WHERE id=$eid");
        showAlert('Product updated! ✅');
    } else {
        $image  = '';
        $newImg = uploadCollectionFile('image', 'collections', $imgSlug, ['jpg','jpeg','png','webp','avif']);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("INSERT INTO collections_products (name, link, btn_text, sort_order, image) VALUES ('$name','$link','$btn_text',$sort,'$img')");
        showAlert('Product added! ✅');
    }
    redirect('collections?tab=products');
}

// Edit fetch
$editProd = null;
if ($action === 'edit' && $id) {
    $editProd = $conn->query("SELECT * FROM collections_products WHERE id=$id")->fetch_assoc();
}

require_once '../includes/header.php';

$hero     = $conn->query("SELECT * FROM collections_hero WHERE id=1")->fetch_assoc();
$products = $conn->query("SELECT * FROM collections_products ORDER BY sort_order, id")->fetch_all(MYSQLI_ASSOC);
$videoUrl = ($hero['video_file'] ?? '') ? UPLOAD_URL . 'collections/' . $hero['video_file'] : '';
?>

<div class="page-header">
    <h2><i class="fas fa-th-large"></i> Collections Grid</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Collections Grid
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=hero" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='hero' ? 'var(--gold)' : 'transparent' ?>;color:<?= $tab==='hero' ? 'var(--gold)' : 'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-video"></i> Left Card (Video)
    </a>
    <a href="?tab=products" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='products' ? 'var(--gold)' : 'transparent' ?>;color:<?= $tab==='products' ? 'var(--gold)' : 'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-images"></i> Right Card (Products)
    </a>
</div>

<?php if ($tab === 'hero'): ?>
<!-- ═══════════════════════ HERO / LEFT CARD ═══════════════════════ -->

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-edit"></i> Edit Left Card</div>
    </div>
    <form method="POST" action="?tab=hero" enctype="multipart/form-data">

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control"
                       value="<?= htmlspecialchars($hero['title'] ?? 'New Arrivals') ?>"
                       placeholder="New Arrivals">
            </div>
            <div class="form-group">
                <label class="form-label">Button Text</label>
                <input type="text" name="btn_text" class="form-control"
                       value="<?= htmlspecialchars($hero['btn_text'] ?? 'Shop Collection') ?>"
                       placeholder="Shop Collection">
            </div>
            <div class="form-group">
                <label class="form-label">Button Link</label>
                <input type="text" name="btn_link" class="form-control"
                       value="<?= htmlspecialchars($hero['btn_link'] ?? '#') ?>"
                       placeholder="/collection">
            </div>
        </div>

        <!-- Video Upload -->
        <div class="form-group">
            <label class="form-label"><i class="fas fa-video"></i> Background Video
                <span style="font-size:0.75rem;color:var(--text-light)"> — saves as /public/collections/new-arrival.mp4</span>
            </label>
            <?php if ($videoUrl): ?>
            <div style="margin-bottom:12px;padding:12px;background:var(--cream);border-radius:10px;border:1px solid var(--cream-deep)">
                <video src="<?= $videoUrl ?>?v=<?= time() ?>" controls
                       style="max-width:300px;border-radius:8px;display:block"></video>
                <span style="font-size:0.75rem;color:var(--success);margin-top:6px;display:block;font-weight:600">
                    <i class="fas fa-check-circle"></i> Current: <?= $hero['video_file'] ?>
                </span>
            </div>
            <?php endif; ?>
            <div onclick="document.getElementById('videoInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                 ondragleave="this.style.borderColor='var(--cream-deep)'"
                 ondrop="handleDrop(event,'videoInput','videoPrev')"
                 style="border:2px dashed var(--cream-deep);border-radius:10px;padding:20px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                <i class="fas fa-film" style="font-size:1.8rem;color:var(--gold);display:block;margin-bottom:8px"></i>
                <p style="font-size:0.85rem;font-weight:600;color:var(--text-mid)">Click or drag & drop video</p>
                <p style="font-size:0.75rem;color:var(--text-light);margin-top:4px">MP4, WebM, OGG • Max 100MB</p>
                <input type="file" name="video_file" id="videoInput" accept="video/*"
                       style="display:none" onchange="prevVideo(this,'videoPrev')">
            </div>
            <div id="videoPrev" style="display:none;margin-top:10px">
                <video id="videoPrevEl" controls style="max-width:300px;border-radius:8px;border:2px solid var(--success);display:block"></video>
                <span id="videoPrevName" style="font-size:0.75rem;color:var(--success);margin-top:4px;display:block;font-weight:600"></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Left Card</button>
    </form>
</div>

<?php
// Handle clear video
if (($action ?? '') === 'clear_video') {
    $conn->query("UPDATE collections_hero SET video_file='' WHERE id=1");
    showAlert('Video cleared!', 'danger');
    redirect('collections?tab=hero');
}
?>

<?php else: ?>
<!-- ═══════════════════════ PRODUCTS / RIGHT CARD ═══════════════════════ -->

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $editProd ? 'edit' : 'plus' ?>"></i> <?= $editProd ? 'Edit' : 'Add' ?> Product</div>
        <?php if ($editProd): ?><a href="?tab=products" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST" action="?tab=products" enctype="multipart/form-data">
        <?php if ($editProd): ?><input type="hidden" name="edit_id" value="<?= $editProd['id'] ?>"><?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($editProd['name'] ?? '') ?>"
                       placeholder="e.g. B612" required>
            </div>
            <div class="form-group">
                <label class="form-label">Button Text</label>
                <input type="text" name="btn_text" class="form-control"
                       value="<?= htmlspecialchars($editProd['btn_text'] ?? 'Shop Essential') ?>"
                       placeholder="Shop Essential">
            </div>
            <div class="form-group">
                <label class="form-label">Product Link</label>
                <input type="text" name="link" class="form-control"
                       value="<?= htmlspecialchars($editProd['link'] ?? '#') ?>"
                       placeholder="/product/b612">
            </div>
            <div class="form-group" style="max-width:130px">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?= $editProd['sort_order'] ?? 0 ?>">
            </div>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
            <label class="form-label"><i class="fas fa-image"></i> Product Image
                <span style="font-size:0.75rem;color:var(--text-light)"> — saves to /public/collections/</span>
            </label>
            <?php if (!empty($editProd['image'])): ?>
            <div style="margin-bottom:10px">
                <img src="<?= UPLOAD_URL ?>collections/<?= $editProd['image'] ?>?v=<?= time() ?>"
                     style="height:80px;border-radius:8px;border:2px solid var(--cream-deep)">
                <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
                    <i class="fas fa-check-circle"></i> Current: <?= $editProd['image'] ?>
                </span>
            </div>
            <?php endif; ?>
            <div onclick="document.getElementById('imgInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                 ondragleave="this.style.borderColor='var(--cream-deep)'"
                 ondrop="handleDrop(event,'imgInput','imgPrev')"
                 style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
                <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
                <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
                <input type="file" name="image" id="imgInput" accept="image/*"
                       style="display:none" onchange="prevImg(this,'imgPrev')">
            </div>
            <div id="imgPrev" style="display:none;margin-top:8px">
                <img id="imgPrevImg" style="height:80px;border-radius:8px;border:2px solid var(--success)">
                <span id="imgPrevName" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $editProd ? 'Update' : 'Add' ?> Product
        </button>
    </form>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Products (<?= count($products) ?>)</div>
    </div>
    <?php if (empty($products)): ?>
    <div class="empty-state"><i class="fas fa-images"></i><h3>No products yet</h3><p>Add your first product above</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Image</th><th>Name</th><th>Button</th><th>Link</th><th>Sort</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products as $i => $p): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td>
                        <?php $imgUrl = $p['image'] ? UPLOAD_URL . 'collections/' . $p['image'] : ''; ?>
                        <img src="<?= $imgUrl ?: 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>' ?>"
                             style="width:55px;height:58px;object-fit:cover;border-radius:6px"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>'">
                    </td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td style="font-size:0.82rem"><?= htmlspecialchars($p['btn_text']) ?></td>
                    <td><small style="color:var(--text-light)"><?= htmlspecialchars($p['link']) ?></small></td>
                    <td><?= $p['sort_order'] ?></td>
                    <td>
                        <a href="?tab=products&action=toggle&id=<?= $p['id'] ?>">
                            <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?tab=products&action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                        <a href="?tab=products&action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete <?= htmlspecialchars($p['name']) ?>?')"><i class="fas fa-trash"></i></a>
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
function prevImg(input, id) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById(id + 'Img').src = URL.createObjectURL(file);
    document.getElementById(id + 'Name').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById(id).style.display = 'block';
}
function prevVideo(input, id) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('videoPrevEl').src = URL.createObjectURL(file);
    document.getElementById('videoPrevName').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById(id).style.display = 'block';
}
function handleDrop(e, inputId, prevId) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    const input = document.getElementById(inputId);
    input.files = dt.files;
    if (inputId === 'videoInput') prevVideo(input, prevId);
    else prevImg(input, prevId);
}
</script>

<?php require_once '../includes/footer.php'; ?>
