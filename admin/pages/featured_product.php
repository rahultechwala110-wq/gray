<?php
require_once '../includes/db.php';
requireLogin();

// ── Create Tables ─────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS featured_products (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(200) NOT NULL,
    image      VARCHAR(300) DEFAULT '',
    floral     VARCHAR(300) DEFAULT '',
    href       VARCHAR(300) DEFAULT '/product',
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS featured_product_features (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    icon       VARCHAR(50)  DEFAULT 'Leaf',
    title      VARCHAR(200) DEFAULT '',
    description TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES featured_products(id) ON DELETE CASCADE
)");

// ── Upload Helper ─────────────────────────────────────────────
function uploadFeaturedImg($fileKey, $saveName) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $file    = $_FILES[$fileKey];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','avif'];
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'products/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $saveName . '-' . time() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $filename)) return $filename;
    return false;
}

// ── Actions ───────────────────────────────────────────────────
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// Delete product
if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM featured_products WHERE id=$id");
    showAlert('Product deleted!', 'danger');
    redirect('featured_product');
}

// Toggle
if ($action === 'toggle' && $id) {
    $conn->query("UPDATE featured_products SET is_active=1-is_active WHERE id=$id");
    redirect('featured_product');
}

// ── SAVE ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = sanitize($conn, $_POST['name']  ?? '');
    $href  = sanitize($conn, $_POST['href']  ?? '/product');
    $sort  = (int)($_POST['sort_order'] ?? 0);
    $eid   = (int)($_POST['edit_id']    ?? 0);

    // Features arrays
    $fIcons  = $_POST['feat_icon']  ?? [];
    $fTitles = $_POST['feat_title'] ?? [];
    $fDescs  = $_POST['feat_desc']  ?? [];

    if ($eid) {
        $existing = $conn->query("SELECT image, floral FROM featured_products WHERE id=$eid")->fetch_assoc();
        $image    = $existing['image']  ?? '';
        $floral   = $existing['floral'] ?? '';

        $newImg    = uploadFeaturedImg('image',  strtolower(preg_replace('/[^a-z0-9]/i','-',$name)).'-img');
        $newFloral = uploadFeaturedImg('floral', strtolower(preg_replace('/[^a-z0-9]/i','-',$name)).'-floral');
        if ($newImg    !== false) $image  = $newImg;
        if ($newFloral !== false) $floral = $newFloral;

        $img = $conn->real_escape_string($image);
        $fl  = $conn->real_escape_string($floral);
        $conn->query("UPDATE featured_products SET name='$name', href='$href', sort_order=$sort, image='$img', floral='$fl' WHERE id=$eid");

        // Update features — delete old, insert new
        $conn->query("DELETE FROM featured_product_features WHERE product_id=$eid");
        foreach ($fTitles as $fi => $ftitle) {
            $ftitle = $conn->real_escape_string(trim($ftitle));
            $fdesc  = $conn->real_escape_string(trim($fDescs[$fi]  ?? ''));
            $ficon  = $conn->real_escape_string(trim($fIcons[$fi]  ?? 'Leaf'));
            $fsort  = $fi + 1;
            if ($ftitle) $conn->query("INSERT INTO featured_product_features (product_id, icon, title, description, sort_order) VALUES ($eid,'$ficon','$ftitle','$fdesc',$fsort)");
        }
        showAlert('Product updated! ✅');
    } else {
        $image  = ''; $floral = '';
        $newImg    = uploadFeaturedImg('image',  strtolower(preg_replace('/[^a-z0-9]/i','-',$name)).'-img');
        $newFloral = uploadFeaturedImg('floral', strtolower(preg_replace('/[^a-z0-9]/i','-',$name)).'-floral');
        if ($newImg    !== false) $image  = $newImg;
        if ($newFloral !== false) $floral = $newFloral;

        $img = $conn->real_escape_string($image);
        $fl  = $conn->real_escape_string($floral);
        $conn->query("INSERT INTO featured_products (name, href, sort_order, image, floral) VALUES ('$name','$href',$sort,'$img','$fl')");
        $newId = $conn->insert_id;

        foreach ($fTitles as $fi => $ftitle) {
            $ftitle = $conn->real_escape_string(trim($ftitle));
            $fdesc  = $conn->real_escape_string(trim($fDescs[$fi]  ?? ''));
            $ficon  = $conn->real_escape_string(trim($fIcons[$fi]  ?? 'Leaf'));
            $fsort  = $fi + 1;
            if ($ftitle) $conn->query("INSERT INTO featured_product_features (product_id, icon, title, description, sort_order) VALUES ($newId,'$ficon','$ftitle','$fdesc',$fsort)");
        }
        showAlert('Product added! ✅');
    }
    redirect('featured_product');
}

// Edit fetch
$editProd  = null;
$editFeats = [];
if ($action === 'edit' && $id) {
    $editProd  = $conn->query("SELECT * FROM featured_products WHERE id=$id")->fetch_assoc();
    $editFeats = $conn->query("SELECT * FROM featured_product_features WHERE product_id=$id ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
    // Pad to 3
    while (count($editFeats) < 3) $editFeats[] = ['icon'=>'Leaf','title'=>'','description'=>'','sort_order'=>count($editFeats)+1];
}

require_once '../includes/header.php';

$products = $conn->query("SELECT p.*, (SELECT COUNT(*) FROM featured_product_features f WHERE f.product_id=p.id) as feat_count FROM featured_products p ORDER BY sort_order, id")->fetch_all(MYSQLI_ASSOC);

$iconOptions = ['Leaf','Droplets','Sparkles','Star','Heart','Zap','Shield','Award','Gift','Wind','Sun','Moon'];
?>

<div class="page-header">
    <h2><i class="fas fa-star"></i> Featured Product</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Featured Product
</div>

<!-- ══════════════ FORM ══════════════ -->
<div class="card" style="margin-bottom:20px" id="formCard">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-<?= $editProd ? 'edit' : 'plus' ?>"></i>
            <?= $editProd ? 'Edit: ' . htmlspecialchars($editProd['name']) : 'Add New Product' ?>
        </div>
        <?php if ($editProd): ?>
        <a href="<?= ADMIN_URL ?>/pages/featured_product" class="btn btn-secondary btn-sm">
            <i class="fas fa-times"></i> Cancel
        </a>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($editProd): ?><input type="hidden" name="edit_id" value="<?= $editProd['id'] ?>"><?php endif; ?>

        <!-- Basic Info -->
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($editProd['name'] ?? '') ?>"
                       placeholder="e.g. Bold" required>
            </div>
            <div class="form-group">
                <label class="form-label">Product Link</label>
                <input type="text" name="href" class="form-control"
                       value="<?= htmlspecialchars($editProd['href'] ?? '/product') ?>"
                       placeholder="/product">
            </div>
            <div class="form-group" style="max-width:130px">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?= $editProd['sort_order'] ?? 0 ?>">
            </div>
        </div>

        <!-- Images -->
        <div class="form-row" style="gap:24px;align-items:flex-start">
            <!-- Product Image -->
            <div class="form-group" style="flex:1">
                <label class="form-label"><i class="fas fa-image"></i> Product Image
                    <span style="font-size:0.75rem;color:var(--text-light)"> — /public/products/</span>
                </label>
                <?php if (!empty($editProd['image'])): ?>
                <div style="margin-bottom:10px">
                    <img src="<?= UPLOAD_URL ?>products/<?= $editProd['image'] ?>?v=<?= time() ?>"
                         style="height:90px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:contain;background:var(--cream)">
                    <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
                        <i class="fas fa-check-circle"></i> <?= $editProd['image'] ?>
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
                    <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">PNG, WebP recommended • Max 10MB</p>
                    <input type="file" name="image" id="imgInput" accept="image/*" style="display:none" onchange="prevImg(this,'imgPrev')">
                </div>
                <div id="imgPrev" style="display:none;margin-top:8px">
                    <img id="imgPrevImg" style="height:80px;border-radius:8px;border:2px solid var(--success)">
                    <span id="imgPrevName" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
                </div>
            </div>

            <!-- Floral Image -->
            <div class="form-group" style="flex:1">
                <label class="form-label"><i class="fas fa-seedling"></i> Floral / Background Image
                    <span style="font-size:0.75rem;color:var(--text-light)"> — decorative background</span>
                </label>
                <?php if (!empty($editProd['floral'])): ?>
                <div style="margin-bottom:10px">
                    <img src="<?= UPLOAD_URL ?>products/<?= $editProd['floral'] ?>?v=<?= time() ?>"
                         style="height:90px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:contain;background:var(--cream)">
                    <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
                        <i class="fas fa-check-circle"></i> <?= $editProd['floral'] ?>
                    </span>
                </div>
                <?php endif; ?>
                <div onclick="document.getElementById('floralInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
                     ondragleave="this.style.borderColor='var(--cream-deep)'"
                     ondrop="handleDrop(event,'floralInput','floralPrev')"
                     style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
                    <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
                    <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">PNG with transparency recommended</p>
                    <input type="file" name="floral" id="floralInput" accept="image/*" style="display:none" onchange="prevImg(this,'floralPrev')">
                </div>
                <div id="floralPrev" style="display:none;margin-top:8px">
                    <img id="floralPrevImg" style="height:80px;border-radius:8px;border:2px solid var(--success)">
                    <span id="floralPrevName" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div style="margin-top:8px">
            <label class="form-label" style="margin-bottom:12px;display:block">
                <i class="fas fa-list-ul"></i> Features
                <span style="font-size:0.75rem;color:var(--text-light);margin-left:8px">(3 features per product)</span>
            </label>
            <div style="display:flex;flex-direction:column;gap:14px" id="featuresContainer">
                <?php
                $feats = $editFeats ?: [
                    ['icon'=>'Leaf',     'title'=>'','description'=>''],
                    ['icon'=>'Droplets', 'title'=>'','description'=>''],
                    ['icon'=>'Sparkles', 'title'=>'','description'=>''],
                ];
                foreach ($feats as $fi => $feat):
                ?>
                <div style="background:var(--cream);border:1px solid var(--cream-deep);border-radius:10px;padding:16px">
                    <div style="font-size:0.75rem;font-weight:700;color:var(--text-light);margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em">
                        Feature <?= $fi+1 ?>
                    </div>
                    <div class="form-row" style="margin-bottom:0">
                        <div class="form-group" style="max-width:160px">
                            <label class="form-label" style="font-size:0.8rem">Icon</label>
                            <select name="feat_icon[]" class="form-control" style="font-size:0.85rem">
                                <?php foreach ($iconOptions as $ico): ?>
                                <option value="<?= $ico ?>" <?= ($feat['icon'] ?? 'Leaf') === $ico ? 'selected' : '' ?>><?= $ico ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size:0.8rem">Title</label>
                            <input type="text" name="feat_title[]" class="form-control" style="font-size:0.85rem"
                                   value="<?= htmlspecialchars($feat['title'] ?? '') ?>"
                                   placeholder="e.g. Strong Sillage">
                        </div>
                        <div class="form-group" style="flex:2">
                            <label class="form-label" style="font-size:0.8rem">Description</label>
                            <input type="text" name="feat_desc[]" class="form-control" style="font-size:0.85rem"
                                   value="<?= htmlspecialchars($feat['description'] ?? '') ?>"
                                   placeholder="Short description...">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-top:20px">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $editProd ? 'Update Product' : 'Add Product' ?>
            </button>
        </div>
    </form>
</div>

<!-- ══════════════ PRODUCTS TABLE ══════════════ -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Featured Products (<?= count($products) ?>)</div>
    </div>

    <?php if (empty($products)): ?>
    <div class="empty-state"><i class="fas fa-star"></i><h3>No products yet</h3><p>Add your first featured product above</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Image</th><th>Floral</th><th>Name</th><th>Features</th><th>Link</th><th>Sort</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $i => $p):
                $imgUrl    = $p['image']  ? UPLOAD_URL . 'products/' . $p['image']  : '';
                $floralUrl = $p['floral'] ? UPLOAD_URL . 'products/' . $p['floral'] : '';
                $noImg     = "data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>";
                $feats     = $conn->query("SELECT * FROM featured_product_features WHERE product_id={$p['id']} ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
            ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td>
                        <img src="<?= $imgUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'"
                             style="width:45px;height:58px;object-fit:contain;border-radius:6px;background:var(--cream)">
                    </td>
                    <td>
                        <img src="<?= $floralUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'"
                             style="width:45px;height:45px;object-fit:contain;border-radius:6px;background:var(--cream);opacity:0.7">
                    </td>
                    <td><strong style="font-size:1rem"><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td>
                        <?php foreach ($feats as $f): ?>
                        <div style="font-size:0.75rem;color:var(--text-light);margin-bottom:2px">
                            <i class="fas fa-check" style="color:var(--success);margin-right:4px;font-size:0.65rem"></i>
                            <?= htmlspecialchars($f['title']) ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($feats)): ?><span style="color:var(--text-light);font-size:0.8rem">— no features —</span><?php endif; ?>
                    </td>
                    <td><small style="color:var(--text-light)"><?= htmlspecialchars($p['href']) ?></small></td>
                    <td><?= $p['sort_order'] ?></td>
                    <td>
                        <a href="?action=toggle&id=<?= $p['id'] ?>">
                            <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-icon"
                           onclick="setTimeout(()=>document.getElementById('formCard').scrollIntoView({behavior:'smooth'}),50)">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon"
                           onclick="confirmDelete(this.href,'Delete <?= htmlspecialchars($p['name']) ?>?','&quot;<?= htmlspecialchars($p['name']) ?>&quot; aur uske saare features permanently delete ho jayenge.');return false;">
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
    const input = document.getElementById(inputId);
    input.files = dt.files;
    prevImg(input, prevId);
}
</script>

<?php require_once '../includes/footer.php'; ?>
