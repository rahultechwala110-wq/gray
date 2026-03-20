<?php
require_once '../includes/db.php';
requireLogin();

// ── Create Tables ─────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS showcase_settings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    label       VARCHAR(200) DEFAULT 'Shop Products',
    title       VARCHAR(200) DEFAULT 'Our Collections',
    description TEXT,
    btn_text    VARCHAR(100) DEFAULT 'Shop All Collections',
    btn_link    VARCHAR(300) DEFAULT '/all-products',
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS showcase_products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(200) NOT NULL,
    category    VARCHAR(100) DEFAULT 'Man',
    image       VARCHAR(300) DEFAULT '',
    price       DECIMAL(10,2) DEFAULT 0,
    price_range VARCHAR(100) DEFAULT '',
    rating      DECIMAL(2,1) DEFAULT 5.0,
    href        VARCHAR(300) DEFAULT '/product',
    sort_order  INT DEFAULT 0,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure category column exists (for existing installs)
if ($conn->query("SHOW COLUMNS FROM showcase_products LIKE 'category'")->num_rows === 0)
    $conn->query("ALTER TABLE showcase_products ADD COLUMN category VARCHAR(100) DEFAULT 'Man' AFTER name");

// Default settings row
if ($conn->query("SELECT id FROM showcase_settings WHERE id=1")->num_rows === 0) {
    $conn->query("INSERT INTO showcase_settings (id, label, title, description, btn_text, btn_link)
                  VALUES (1,'Shop Products','Our Collections','Experience the art of premium craftsmanship','Shop All Collections','/all-products')");
}

// ── Upload Helper ─────────────────────────────────────────────
function uploadShowcaseImg($fileKey, $saveName) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $file    = $_FILES[$fileKey];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($file['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'products/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $saveName . '-' . time() . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $dir . $filename) ? $filename : false;
}

// ── Actions ───────────────────────────────────────────────────
$tab    = $_GET['tab']    ?? 'settings';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM showcase_products WHERE id=$id");
    showAlert('Product deleted!', 'danger');
    redirect('products_showcase?tab=products');
}

if ($action === 'toggle' && $id) {
    $conn->query("UPDATE showcase_products SET is_active=1-is_active WHERE id=$id");
    redirect('products_showcase?tab=products');
}

// ── SAVE SETTINGS ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'settings') {
    $label       = sanitize($conn, $_POST['label']       ?? '');
    $title       = sanitize($conn, $_POST['title']       ?? '');
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $btn_text    = sanitize($conn, $_POST['btn_text']    ?? '');
    $btn_link    = sanitize($conn, $_POST['btn_link']    ?? '');
    $conn->query("UPDATE showcase_settings SET label='$label', title='$title', description='$description', btn_text='$btn_text', btn_link='$btn_link' WHERE id=1");
    showAlert('Settings updated! ✅');
    redirect('products_showcase?tab=settings');
}

// ── SAVE PRODUCT ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'products') {
    $name        = sanitize($conn, $_POST['name']        ?? '');
    $category    = sanitize($conn, $_POST['category']    ?? 'Man');
    $price       = (float)($_POST['price']               ?? 0);
    $price_range = sanitize($conn, $_POST['price_range'] ?? '');
    $rating      = min(5, max(0, (float)($_POST['rating'] ?? 5)));
    $href        = sanitize($conn, $_POST['href']        ?? '/product');
    $sort        = (int)($_POST['sort_order']            ?? 0);
    $eid         = (int)($_POST['edit_id']               ?? 0);
    $imgSlug     = strtolower(preg_replace('/[^a-z0-9]/i', '-', $name));

    if ($eid) {
        $existing = $conn->query("SELECT image FROM showcase_products WHERE id=$eid")->fetch_assoc();
        $image    = $existing['image'] ?? '';
        $newImg   = uploadShowcaseImg('image', $imgSlug);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("UPDATE showcase_products SET name='$name', category='$category', price=$price, price_range='$price_range', rating=$rating, href='$href', sort_order=$sort, image='$img' WHERE id=$eid");
        showAlert('Product updated! ✅');
    } else {
        $image  = '';
        $newImg = uploadShowcaseImg('image', $imgSlug);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("INSERT INTO showcase_products (name, category, price, price_range, rating, href, sort_order, image) VALUES ('$name','$category',$price,'$price_range',$rating,'$href',$sort,'$img')");
        showAlert('Product added! ✅');
    }
    redirect('products_showcase?tab=products');
}

$editProd = null;
if ($action === 'edit' && $id) {
    $editProd = $conn->query("SELECT * FROM showcase_products WHERE id=$id")->fetch_assoc();
    $tab = 'products';
}

require_once '../includes/header.php';

$settings = $conn->query("SELECT * FROM showcase_settings WHERE id=1")->fetch_assoc();
$products = $conn->query("SELECT * FROM showcase_products ORDER BY sort_order, id")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="fas fa-box-open"></i> Products Showcase</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i> Products Showcase
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=settings" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='settings'?'var(--gold)':'transparent' ?>;color:<?= $tab==='settings'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-cog"></i> Section Settings
    </a>
    <a href="?tab=products" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='products'?'var(--gold)':'transparent' ?>;color:<?= $tab==='products'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-boxes"></i> Products (<?= count($products) ?>)
    </a>
</div>

<?php if ($tab === 'settings'): ?>
<!-- ═══════════════════ SETTINGS TAB ═══════════════════ -->
<div class="card" style="margin-bottom:20px">
    <div class="card-header"><div class="card-title"><i class="fas fa-edit"></i> Section Heading</div></div>
    <form method="POST" action="?tab=settings">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Label <span style="font-size:0.75rem;color:var(--text-light)">(small text above heading)</span></label>
                <input type="text" name="label" class="form-control" value="<?= htmlspecialchars($settings['label'] ?? '') ?>" placeholder="Shop Products">
            </div>
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($settings['title'] ?? '') ?>" placeholder="Our Collections">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($settings['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Bottom Button Text</label>
                <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($settings['btn_text'] ?? '') ?>" placeholder="Shop All Collections">
            </div>
            <div class="form-group">
                <label class="form-label">Bottom Button Link</label>
                <input type="text" name="btn_link" class="form-control" value="<?= htmlspecialchars($settings['btn_link'] ?? '') ?>" placeholder="/all-products">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>

<?php else: ?>
<!-- ═══════════════════ PRODUCTS TAB ═══════════════════ -->
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
                       value="<?= htmlspecialchars($editProd['name'] ?? '') ?>" placeholder="e.g. Gentle" required>
            </div>
            <div class="form-group" style="max-width:180px">
                <label class="form-label">Category</label>
                <select name="category" class="form-control">
                    <?php foreach (['Man', 'Woman', 'Aroma Pod'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($editProd['category'] ?? 'Man') === $cat ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Price (₹)</label>
                <input type="number" name="price" class="form-control"
                       value="<?= $editProd['price'] ?? '' ?>" placeholder="4200" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label">Price Range</label>
                <input type="text" name="price_range" class="form-control"
                       value="<?= htmlspecialchars($editProd['price_range'] ?? '') ?>" placeholder="$35 - $70">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="max-width:150px">
                <label class="form-label">Rating <span style="font-size:0.75rem;color:var(--text-light)">(0–5)</span></label>
                <input type="number" name="rating" class="form-control"
                       value="<?= $editProd['rating'] ?? 5 ?>" min="0" max="5" step="0.1">
            </div>
            <div class="form-group">
                <label class="form-label">Product Link</label>
                <input type="text" name="href" class="form-control"
                       value="<?= htmlspecialchars($editProd['href'] ?? '/product') ?>" placeholder="/product">
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
                <span style="font-size:0.75rem;color:var(--text-light)"> — saves to /public/products/</span>
            </label>
            <?php if (!empty($editProd['image'])): ?>
            <div style="margin-bottom:10px">
                <img src="<?= UPLOAD_URL ?>products/<?= $editProd['image'] ?>?v=<?= time() ?>"
                     style="height:90px;border-radius:8px;border:2px solid var(--cream-deep)">
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
                <img id="imgPrevImg" style="height:90px;border-radius:8px;border:2px solid var(--success)">
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
    <div class="empty-state"><i class="fas fa-box-open"></i><h3>No products yet</h3><p>Add your first product above</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Rating</th><th>Link</th><th>Sort</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($products as $i => $p):
                $imgUrl = $p['image'] ? UPLOAD_URL . 'products/' . $p['image'] : '';
                $noImg  = "data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>";
            ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><img src="<?= $imgUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'"
                             style="width:45px;height:55px;object-fit:contain;border-radius:6px;background:var(--cream)"></td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                    <td><span style="background:var(--cream-dark);padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600"><?= htmlspecialchars($p['category'] ?? '—') ?></span></td>
                    <td><strong>₹<?= number_format($p['price'], 0) ?></strong></td>
                    <td>
                        <span style="display:flex;align-items:center;gap:4px;font-weight:600;font-size:0.85rem">
                            <?= number_format($p['rating'], 1) ?>
                            <i class="fas fa-star" style="color:#f4b942;font-size:0.7rem"></i>
                        </span>
                    </td>
                    <td><small style="color:var(--text-light)"><?= htmlspecialchars($p['href']) ?></small></td>
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
function handleDrop(e, inputId, prevId) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById(inputId).files = dt.files;
    prevImg(document.getElementById(inputId), prevId);
}
</script>

<?php require_once '../includes/footer.php'; ?>