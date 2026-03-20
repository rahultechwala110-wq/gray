<?php
require_once '../includes/db.php';
requireLogin();

// ── Create Tables ─────────────────────────────────────────────
$conn->query("CREATE TABLE IF NOT EXISTS fragrance_categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    label      VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS fragrances (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(200) NOT NULL,
    type       VARCHAR(200) DEFAULT 'Extrait De Parfum',
    category   VARCHAR(100) NOT NULL,
    image      VARCHAR(300) DEFAULT '',
    href       VARCHAR(300) DEFAULT '/product',
    price      DECIMAL(10,2) DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active  TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default categories if empty
$catCheck = $conn->query("SELECT id FROM fragrance_categories LIMIT 1");
if ($catCheck->num_rows === 0) {
    $conn->query("INSERT INTO fragrance_categories (name, label, sort_order) VALUES
        ('man', 'Man', 1),
        ('woman', 'Woman', 2),
        ('Aroma Pod', 'Aroma Pod', 3)");
}

// Insert default products with correct hrefs if empty
$prodCheck = $conn->query("SELECT id FROM fragrances LIMIT 1");
if ($prodCheck->num_rows === 0) {
    $conn->query("INSERT INTO fragrances (name, type, category, href, price, sort_order) VALUES
        ('Brave',      'Extrait De Parfum', 'man',       '/brave-mens-perfume-55ml',       4200, 1),
        ('Boss',       'Extrait De Parfum', 'man',       '/boss-mens-perfume-55m',          4500, 2),
        ('Gentle',     'Extrait De Parfum', 'man',       '/gentle-mens-perfume-55ml',       4200, 3),
        ('Bold',       'Extrait De Parfum', 'man',       '/gold-men-perfume-55ml',          4800, 4),
        ('Generous',   'Extrait De Parfum', 'man',       '/generous-mens-perfume-55ml',     4200, 5),
        ('Groomed',    'Extrait De Parfum', 'man',       '/groomed-mens-perfume-55m',       4200, 6),
        ('Bliss',      'Extrait De Parfum', 'woman',     '/bliss-womens-perfume-55ml',      3500, 1),
        ('Gorgeous',   'Extrait De Parfum', 'woman',     '/gorgeous-womens-perfume-55ml',   3800, 2),
        ('Braveheart', 'Extrait De Parfum', 'woman',     '/braveheart-womens-perfume-55ml', 4200, 3),
        ('Glorious',   'Extrait De Parfum', 'woman',     '/glorious-womens-perfume-55ml',   4200, 4),
        ('Brilliance', 'Extrait De Parfum', 'woman',     '/brilliance-womens-perfume-55ml', 3800, 5),
        ('Gifted',     'Extrait De Parfum', 'woman',     '/gifted-womens-perfume-55ml',     4200, 6),
        ('B612',       'Aroma Pod',         'Aroma Pod', '/aroma-pod/b612',                 2500, 1),
        ('Bulge',      'Aroma Pod',         'Aroma Pod', '/aroma-pod/bulge',                2500, 2),
        ('Brahe',      'Aroma Pod',         'Aroma Pod', '/aroma-pod/brahe',                2500, 3),
        ('Glese',      'Aroma Pod',         'Aroma Pod', '/aroma-pod/glese',                2500, 4),
        ('Ganymede',   'Aroma Pod',         'Aroma Pod', '/aroma-pod/ganymede',             2500, 5),
        ('Gaspra',     'Aroma Pod',         'Aroma Pod', '/aroma-pod/gaspra',               2500, 6)
    ");
}

// ── Image Upload Helper ───────────────────────────────────────
function uploadFragranceImg($fileKey, $saveName) {
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
$tab    = $_GET['tab']    ?? 'products';
$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ── DELETE ────────────────────────────────────────────────────
if ($action === 'delete' && $id) {
    if ($tab === 'categories') {
        $conn->query("DELETE FROM fragrance_categories WHERE id=$id");
        showAlert('Category deleted!', 'danger');
    } else {
        $conn->query("DELETE FROM fragrances WHERE id=$id");
        showAlert('Fragrance deleted!', 'danger');
    }
    redirect('fragrances?tab=' . $tab);
}

// ── TOGGLE ────────────────────────────────────────────────────
if ($action === 'toggle' && $id) {
    if ($tab === 'categories') {
        $conn->query("UPDATE fragrance_categories SET is_active=1-is_active WHERE id=$id");
    } else {
        $conn->query("UPDATE fragrances SET is_active=1-is_active WHERE id=$id");
    }
    redirect('fragrances?tab=' . $tab);
}

// ── SAVE CATEGORY ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'categories') {
    $name  = sanitize($conn, $_POST['name']  ?? '');
    $label = sanitize($conn, $_POST['label'] ?? '');
    $sort  = (int)($_POST['sort_order'] ?? 0);
    $eid   = (int)($_POST['edit_id'] ?? 0);
    if ($eid) {
        $conn->query("UPDATE fragrance_categories SET name='$name', label='$label', sort_order=$sort WHERE id=$eid");
        showAlert('Category updated! ✅');
    } else {
        $conn->query("INSERT INTO fragrance_categories (name, label, sort_order) VALUES ('$name','$label',$sort)");
        showAlert('Category added! ✅');
    }
    redirect('fragrances?tab=categories');
}

// ── SAVE FRAGRANCE ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'products') {
    $name     = sanitize($conn, $_POST['name']     ?? '');
    $type     = sanitize($conn, $_POST['type']     ?? 'Extrait De Parfum');
    $category = sanitize($conn, $_POST['category'] ?? '');
    $href     = sanitize($conn, $_POST['href']     ?? '/product');
    $price    = (float)($_POST['price'] ?? 0);
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $eid      = (int)($_POST['edit_id'] ?? 0);

    $imgSlug = strtolower(preg_replace('/[^a-z0-9]/i', '-', $name));

    if ($eid) {
        $existing = $conn->query("SELECT image FROM fragrances WHERE id=$eid")->fetch_assoc();
        $image    = $existing['image'] ?? '';
        $newImg   = uploadFragranceImg('image', $imgSlug);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("UPDATE fragrances SET name='$name', type='$type', category='$category', href='$href', price=$price, sort_order=$sort, image='$img' WHERE id=$eid");
        showAlert('Fragrance updated! ✅');
    } else {
        $image  = '';
        $newImg = uploadFragranceImg('image', $imgSlug);
        if ($newImg !== false) $image = $newImg;
        $img = $conn->real_escape_string($image);
        $conn->query("INSERT INTO fragrances (name, type, category, href, price, sort_order, image) VALUES ('$name','$type','$category','$href',$price,$sort,'$img')");
        showAlert('Fragrance added! ✅');
    }
    redirect('fragrances?tab=products');
}

// ── Edit fetch ────────────────────────────────────────────────
$editProd = null; $editCat = null;
if ($action === 'edit' && $id) {
    if ($tab === 'categories') $editCat  = $conn->query("SELECT * FROM fragrance_categories WHERE id=$id")->fetch_assoc();
    else                        $editProd = $conn->query("SELECT * FROM fragrances WHERE id=$id")->fetch_assoc();
}

require_once '../includes/header.php';

$categories = $conn->query("SELECT * FROM fragrance_categories ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$products   = $conn->query("SELECT * FROM fragrances ORDER BY sort_order, name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="fas fa-spray-can"></i> Featured Fragrances</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i>
    Featured Fragrances
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=products" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='products' ? 'var(--gold)' : 'transparent' ?>;color:<?= $tab==='products' ? 'var(--gold)' : 'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-spray-can"></i> Products
    </a>
    <a href="?tab=categories" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='categories' ? 'var(--gold)' : 'transparent' ?>;color:<?= $tab==='categories' ? 'var(--gold)' : 'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-tags"></i> Categories
    </a>
</div>

<?php if ($tab === 'categories'): ?>
<!-- ════════════════════════════════════════════════════════════
     CATEGORIES TAB
════════════════════════════════════════════════════════════ -->

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $editCat ? 'edit' : 'plus' ?>"></i> <?= $editCat ? 'Edit' : 'Add' ?> Category</div>
        <?php if ($editCat): ?><a href="?tab=categories" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST" action="?tab=categories">
        <?php if ($editCat): ?><input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>"><?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">ID / Slug <span style="font-size:0.75rem;color:var(--text-light)">(used in code, no spaces)</span></label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($editCat['name'] ?? '') ?>"
                       placeholder="e.g. man / woman / aroma-pod" required>
            </div>
            <div class="form-group">
                <label class="form-label">Display Label</label>
                <input type="text" name="label" class="form-control"
                       value="<?= htmlspecialchars($editCat['label'] ?? '') ?>"
                       placeholder="e.g. Man / Woman / Aroma Pod" required>
            </div>
            <div class="form-group" style="max-width:150px">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control"
                       value="<?= $editCat['sort_order'] ?? 0 ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= $editCat ? 'Update' : 'Add' ?> Category
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Categories (<?= count($categories) ?>)</div>
    </div>
    <?php if (empty($categories)): ?>
    <div class="empty-state"><i class="fas fa-tags"></i><h3>No categories yet</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Slug</th><th>Label</th><th>Products</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat):
                    $cnt = $conn->query("SELECT COUNT(*) as c FROM fragrances WHERE category='{$cat['name']}'")->fetch_assoc()['c'];
                ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><code style="background:var(--cream-dark);padding:2px 8px;border-radius:4px;font-size:0.8rem"><?= htmlspecialchars($cat['name']) ?></code></td>
                    <td><strong><?= htmlspecialchars($cat['label']) ?></strong></td>
                    <td><span class="badge badge-success"><?= $cnt ?> products</span></td>
                    <td>
                        <a href="?tab=categories&action=toggle&id=<?= $cat['id'] ?>">
                            <span class="badge <?= $cat['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?tab=categories&action=edit&id=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                        <a href="?tab=categories&action=delete&id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- ════════════════════════════════════════════════════════════
     PRODUCTS TAB
════════════════════════════════════════════════════════════ -->

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $editProd ? 'edit' : 'plus' ?>"></i> <?= $editProd ? 'Edit' : 'Add' ?> Fragrance</div>
        <?php if ($editProd): ?><a href="?tab=products" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST" action="?tab=products" enctype="multipart/form-data">
        <?php if ($editProd): ?><input type="hidden" name="edit_id" value="<?= $editProd['id'] ?>"><?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($editProd['name'] ?? '') ?>"
                       placeholder="e.g. Brave" required>
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <input type="text" name="type" class="form-control"
                       value="<?= htmlspecialchars($editProd['type'] ?? 'Extrait De Parfum') ?>"
                       placeholder="Extrait De Parfum">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-control" required>
                    <option value="">— Select Category —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"
                        <?= ($editProd['category'] ?? '') === $cat['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['label']) ?>
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
                <label class="form-label">Product Link</label>
                <input type="text" name="href" class="form-control"
                       value="<?= htmlspecialchars($editProd['href'] ?? '/product') ?>"
                       placeholder="/brave-mens-perfume-55ml">
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
                <span style="font-size:0.75rem;color:var(--text-light)"> — saves to uploads/products/</span>
            </label>
            <?php if (!empty($editProd['image'])): ?>
            <div style="margin-bottom:10px">
                <img src="<?= UPLOAD_URL ?>products/<?= $editProd['image'] ?>?v=<?= time() ?>"
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
            <i class="fas fa-save"></i> <?= $editProd ? 'Update' : 'Add' ?> Fragrance
        </button>
    </form>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Fragrances (<?= count($products) ?>)</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="?tab=products" style="font-size:0.78rem;padding:4px 12px;border-radius:20px;text-decoration:none;background:<?= !isset($_GET['cat']) ? 'var(--gold)' : 'var(--cream-dark)' ?>;color:<?= !isset($_GET['cat']) ? 'white' : 'var(--text-mid)' ?>;font-weight:600">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="?tab=products&cat=<?= urlencode($cat['name']) ?>" style="font-size:0.78rem;padding:4px 12px;border-radius:20px;text-decoration:none;background:<?= ($_GET['cat'] ?? '') === $cat['name'] ? 'var(--gold)' : 'var(--cream-dark)' ?>;color:<?= ($_GET['cat'] ?? '') === $cat['name'] ? 'white' : 'var(--text-mid)' ?>;font-weight:600">
                <?= htmlspecialchars($cat['label']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $filterCat   = isset($_GET['cat']) ? sanitize($conn, $_GET['cat']) : '';
    $whereClause = $filterCat ? "WHERE category='$filterCat'" : '';
    $filtered    = $conn->query("SELECT * FROM fragrances $whereClause ORDER BY sort_order, name")->fetch_all(MYSQLI_ASSOC);
    ?>

    <?php if (empty($filtered)): ?>
    <div class="empty-state"><i class="fas fa-spray-can"></i><h3>No fragrances yet</h3><p>Add your first fragrance above</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Image</th><th>Name</th><th>Type</th><th>Category</th><th>Price</th><th>Link</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($filtered as $i => $f): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td>
                        <?php $imgUrl = $f['image'] ? UPLOAD_URL . 'products/' . $f['image'] : ''; ?>
                        <img src="<?= $imgUrl ?: 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>' ?>"
                             class="table-img" style="width:50px;height:65px;object-fit:cover;border-radius:6px"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>'">
                    </td>
                    <td><strong><?= htmlspecialchars($f['name']) ?></strong></td>
                    <td style="font-size:0.82rem;color:var(--text-light)"><?= htmlspecialchars($f['type']) ?></td>
                    <td>
                        <?php
                        $catLabel = '';
                        foreach ($categories as $c) { if ($c['name'] === $f['category']) { $catLabel = $c['label']; break; } }
                        ?>
                        <span style="background:var(--cream-dark);padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600">
                            <?= htmlspecialchars($catLabel ?: $f['category']) ?>
                        </span>
                    </td>
                    <td><strong>₹<?= number_format($f['price'], 0) ?></strong></td>
                    <td>
                        <small style="color:var(--text-light)"><?= htmlspecialchars($f['href']) ?></small>
                    </td>
                    <td>
                        <a href="?tab=products&action=toggle&id=<?= $f['id'] ?>">
                            <span class="badge <?= $f['is_active'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $f['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </a>
                    </td>
                    <td style="display:flex;gap:6px">
                        <a href="?tab=products&action=edit&id=<?= $f['id'] ?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
                        <a href="?tab=products&action=delete&id=<?= $f['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete <?= htmlspecialchars($f['name']) ?>?')"><i class="fas fa-trash"></i></a>
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
    const input = document.getElementById(inputId);
    input.files = dt.files;
    prevImg(input, prevId);
}
</script>

<?php require_once '../includes/footer.php'; ?>