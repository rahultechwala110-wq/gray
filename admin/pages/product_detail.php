<?php
require_once '../includes/db.php';
requireLogin();

$conn->query("CREATE TABLE IF NOT EXISTS product_details (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    slug             VARCHAR(300) NOT NULL UNIQUE,
    name             VARCHAR(200) NOT NULL,
    subtitle         VARCHAR(200) DEFAULT 'Extrait de Parfume',
    price            DECIMAL(10,2) DEFAULT 0,
    description      VARCHAR(500) DEFAULT '',
    full_description TEXT,
    volume           VARCHAR(100) DEFAULT '55 ml',
    key_notes        VARCHAR(500) DEFAULT '',
    ingredients      TEXT,
    caution          TEXT,
    best_before      VARCHAR(100) DEFAULT '36 Months from Mfg. Date',
    image1           VARCHAR(300) DEFAULT '',
    image2           VARCHAR(300) DEFAULT '',
    image3           VARCHAR(300) DEFAULT '',
    video1           VARCHAR(300) DEFAULT '',
    video2           VARCHAR(300) DEFAULT '',
    whisper1_image   VARCHAR(300) DEFAULT '',
    whisper1_heading VARCHAR(200) DEFAULT 'First Whisper',
    whisper1_content TEXT,
    whisper2_heading VARCHAR(200) DEFAULT 'Second Whisper',
    whisper2_content TEXT,
    made_with_love   VARCHAR(200) DEFAULT 'Made with love',
    made_subtitle    VARCHAR(300) DEFAULT '',
    sort_order       INT DEFAULT 0,
    is_active        TINYINT(1) DEFAULT 1,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure whisper1_image column exists for existing installs
if ($conn->query("SHOW COLUMNS FROM product_details LIKE 'whisper1_image'")->num_rows === 0)
    $conn->query("ALTER TABLE product_details ADD COLUMN whisper1_image VARCHAR(300) DEFAULT '' AFTER video2");

function uploadProductImg($fileKey, $prefix) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $f   = $_FILES[$fileKey];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($f['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'product-details/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $prefix . '-' . time() . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $filename) ? $filename : false;
}

function uploadProductVideo($fileKey, $prefix) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $f   = $_FILES[$fileKey];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['mp4','webm','mov'])) return false;
    if ($f['size'] > 100 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'product-details/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $prefix . '-' . time() . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $filename) ? $filename : false;
}

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM product_details WHERE id=$id");
    showAlert('Product deleted!', 'danger');
    redirect('product_detail');
}

if ($action === 'toggle' && $id) {
    $conn->query("UPDATE product_details SET is_active=1-is_active WHERE id=$id");
    redirect('product_detail');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid  = (int)($_POST['edit_id'] ?? 0);
    $name = sanitize($conn, $_POST['name'] ?? '');
    $slug = sanitize($conn, $_POST['slug'] ?? '');
    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($slug)));
    $sub  = sanitize($conn, $_POST['subtitle']         ?? 'Extrait de Parfume');
    $pr   = (float)($_POST['price']                    ?? 0);
    $desc = $conn->real_escape_string($_POST['description'] ?? '');

    // ✅ FIX: Normalize \r\n → \n so paragraphs split correctly on frontend
    $fdesc = $conn->real_escape_string(
        str_replace(["\r\n", "\r"], "\n", $_POST['full_description'] ?? '')
    );
    $w1c  = $conn->real_escape_string(
        str_replace(["\r\n", "\r"], "\n", $_POST['whisper1_content'] ?? '')
    );
    $w2c  = $conn->real_escape_string(
        str_replace(["\r\n", "\r"], "\n", $_POST['whisper2_content'] ?? '')
    );

    $vol  = sanitize($conn, $_POST['volume']           ?? '55 ml');
    $kn   = sanitize($conn, $_POST['key_notes']        ?? '');
    $ing  = $conn->real_escape_string($_POST['ingredients'] ?? '');
    $cau  = $conn->real_escape_string($_POST['caution']     ?? '');
    $bb   = sanitize($conn, $_POST['best_before']      ?? '36 Months from Mfg. Date');
    $w1h  = sanitize($conn, $_POST['whisper1_heading'] ?? 'First Whisper');
    $w2h  = sanitize($conn, $_POST['whisper2_heading'] ?? 'Second Whisper');
    $mwl  = sanitize($conn, $_POST['made_with_love']   ?? 'Made with love');
    $msub = sanitize($conn, $_POST['made_subtitle']    ?? '');
    $sort = (int)($_POST['sort_order']                 ?? 0);

    if ($eid) {
        $ex  = $conn->query("SELECT image1,image2,image3,video1,video2,whisper1_image FROM product_details WHERE id=$eid")->fetch_assoc();
        $i1  = $ex['image1'];  $i2 = $ex['image2']; $i3 = $ex['image3'];
        $v1  = $ex['video1'];  $v2 = $ex['video2'];
        $wi1 = $ex['whisper1_image'];
    } else {
        $i1 = $i2 = $i3 = $v1 = $v2 = $wi1 = '';
    }

    $slugPrefix = $slug ?: strtolower(preg_replace('/[^a-z0-9]/i', '-', $name));
    if (($n = uploadProductImg('image1',        $slugPrefix . '-img1'))    !== false) $i1  = $n;
    if (($n = uploadProductImg('image2',        $slugPrefix . '-img2'))    !== false) $i2  = $n;
    if (($n = uploadProductImg('image3',        $slugPrefix . '-img3'))    !== false) $i3  = $n;
    if (($n = uploadProductVideo('video1',      $slugPrefix . '-vid1'))    !== false) $v1  = $n;
    if (($n = uploadProductVideo('video2',      $slugPrefix . '-vid2'))    !== false) $v2  = $n;
    if (($n = uploadProductImg('whisper1_image',$slugPrefix . '-whisper1'))!== false) $wi1 = $n;

    $i1  = $conn->real_escape_string($i1);  $i2  = $conn->real_escape_string($i2);
    $i3  = $conn->real_escape_string($i3);  $v1  = $conn->real_escape_string($v1);
    $v2  = $conn->real_escape_string($v2);  $wi1 = $conn->real_escape_string($wi1);

    if ($eid) {
        $conn->query("UPDATE product_details SET
            slug='$slug', name='$name', subtitle='$sub', price=$pr,
            description='$desc', full_description='$fdesc', volume='$vol',
            key_notes='$kn', ingredients='$ing', caution='$cau', best_before='$bb',
            image1='$i1', image2='$i2', image3='$i3', video1='$v1', video2='$v2',
            whisper1_image='$wi1',
            whisper1_heading='$w1h', whisper1_content='$w1c',
            whisper2_heading='$w2h', whisper2_content='$w2c',
            made_with_love='$mwl', made_subtitle='$msub', sort_order=$sort
            WHERE id=$eid");
        showAlert('Product updated! ✅');
    } else {
        $conn->query("INSERT INTO product_details
            (slug,name,subtitle,price,description,full_description,volume,key_notes,ingredients,caution,best_before,
             image1,image2,image3,video1,video2,whisper1_image,
             whisper1_heading,whisper1_content,whisper2_heading,whisper2_content,
             made_with_love,made_subtitle,sort_order)
            VALUES
            ('$slug','$name','$sub',$pr,'$desc','$fdesc','$vol','$kn','$ing','$cau','$bb',
             '$i1','$i2','$i3','$v1','$v2','$wi1',
             '$w1h','$w1c','$w2h','$w2c','$mwl','$msub',$sort)");
        showAlert('Product added! ✅');
    }
    redirect('product_detail');
}

$edit = null;
if ($action === 'edit' && $id)
    $edit = $conn->query("SELECT * FROM product_details WHERE id=$id")->fetch_assoc();

require_once '../includes/header.php';
$products = $conn->query("SELECT * FROM product_details ORDER BY sort_order, id")->fetch_all(MYSQLI_ASSOC);

function imgPrev($label, $field, $inputId, $existing = '') { ?>
<div class="form-group">
    <label class="form-label"><i class="fas fa-image"></i> <?= $label ?></label>
    <?php if ($existing): ?>
    <div style="margin-bottom:8px">
        <img src="<?= UPLOAD_URL ?>product-details/<?= $existing ?>?v=<?= time() ?>"
             style="height:80px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:contain;background:var(--cream)">
        <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($existing) ?>
        </span>
    </div>
    <?php endif; ?>
    <div onclick="document.getElementById('<?= $inputId ?>').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
         ondragleave="this.style.borderColor='var(--cream-deep)'"
         style="border:2px dashed var(--cream-deep);border-radius:10px;padding:14px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
        <i class="fas fa-cloud-upload-alt" style="font-size:1.4rem;color:var(--gold);display:block;margin-bottom:5px"></i>
        <p style="font-size:0.8rem;font-weight:600;color:var(--text-mid)">Click or drag</p>
        <p style="font-size:0.7rem;color:var(--text-light);margin-top:2px">JPG, PNG, WebP • Max 10MB</p>
        <input type="file" name="<?= $field ?>" id="<?= $inputId ?>" accept="image/*" style="display:none" onchange="prevF('<?= $inputId ?>')">
    </div>
    <div id="<?= $inputId ?>-wrap" style="display:none;margin-top:6px">
        <img id="<?= $inputId ?>-prev" style="height:70px;border-radius:8px;border:2px solid var(--success)">
        <span id="<?= $inputId ?>-name" style="font-size:0.72rem;color:var(--success);margin-left:6px;font-weight:600"></span>
    </div>
</div>
<?php }

function vidPrev($label, $field, $inputId, $existing = '') { ?>
<div class="form-group">
    <label class="form-label"><i class="fas fa-video"></i> <?= $label ?></label>
    <?php if ($existing): ?>
    <div style="margin-bottom:8px;padding:8px;background:var(--cream);border-radius:8px;border:1px solid var(--cream-deep)">
        <video src="<?= UPLOAD_URL ?>product-details/<?= $existing ?>" controls style="max-width:200px;border-radius:6px;display:block"></video>
        <span style="font-size:0.72rem;color:var(--success);margin-top:4px;display:block;font-weight:600">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($existing) ?>
        </span>
    </div>
    <?php endif; ?>
    <div onclick="document.getElementById('<?= $inputId ?>').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
         ondragleave="this.style.borderColor='var(--cream-deep)'"
         style="border:2px dashed var(--cream-deep);border-radius:10px;padding:14px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
        <i class="fas fa-film" style="font-size:1.4rem;color:var(--gold);display:block;margin-bottom:5px"></i>
        <p style="font-size:0.8rem;font-weight:600;color:var(--text-mid)">Click or drag</p>
        <p style="font-size:0.7rem;color:var(--text-light);margin-top:2px">MP4, WebM • Max 100MB</p>
        <input type="file" name="<?= $field ?>" id="<?= $inputId ?>" accept="video/*" style="display:none" onchange="prevVid('<?= $inputId ?>')">
    </div>
    <div id="<?= $inputId ?>-wrap" style="display:none;margin-top:6px">
        <video id="<?= $inputId ?>-prev" controls style="max-width:200px;border-radius:8px;border:2px solid var(--success);display:block"></video>
        <span id="<?= $inputId ?>-name" style="font-size:0.72rem;color:var(--success);margin-top:4px;display:block;font-weight:600"></span>
    </div>
</div>
<?php }
?>

<div class="page-header">
    <h2><i class="fas fa-flask"></i> Product Detail Pages</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i> Product Detail Pages
</div>

<div class="card" style="margin-bottom:20px" id="formCard">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-<?= $edit ? 'edit' : 'plus' ?>"></i> <?= $edit ? 'Edit: '.htmlspecialchars($edit['name']) : 'Add New Product Page' ?></div>
        <?php if ($edit): ?><a href="<?= ADMIN_URL ?>/pages/product_detail" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a><?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required placeholder="e.g. BRAVE">
            </div>
            <div class="form-group">
                <label class="form-label">URL Slug * <span style="font-size:0.72rem;color:var(--text-light)">(page URL: /slug/)</span></label>
                <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($edit['slug'] ?? '') ?>" required placeholder="e.g. brave-mens-perfume-55ml">
            </div>
            <div class="form-group" style="max-width:200px">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($edit['subtitle'] ?? 'Extrait de Parfume') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="max-width:150px">
                <label class="form-label">Price (₹)</label>
                <input type="number" name="price" class="form-control" value="<?= $edit['price'] ?? '' ?>" step="0.01">
            </div>
            <div class="form-group" style="max-width:150px">
                <label class="form-label">Volume</label>
                <input type="text" name="volume" class="form-control" value="<?= htmlspecialchars($edit['volume'] ?? '55 ml') ?>">
            </div>
            <div class="form-group" style="max-width:120px">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= $edit['sort_order'] ?? 0 ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Short Description</label>
            <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">
                Full Description
                <span style="font-size:0.72rem;color:var(--text-light)">(paragraph আলাদা করতে একটা blank line দাও)</span>
            </label>
            <!-- ✅ FIX: nl2br নয়, raw value দাও — textarea নিজেই newline রাখে -->
            <textarea name="full_description" class="form-control" rows="10" style="font-family:monospace;font-size:0.85rem;line-height:1.6"><?= htmlspecialchars($edit['full_description'] ?? '') ?></textarea>
            <p style="font-size:0.7rem;color:var(--text-light);margin-top:4px">
                <i class="fas fa-info-circle"></i> প্রতিটি paragraph এর মাঝে একটি <strong>খালি লাইন</strong> রাখুন (Enter দুইবার)।
            </p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Key Notes <span style="font-size:0.72rem;color:var(--text-light)">(comma separated)</span></label>
                <input type="text" name="key_notes" class="form-control" value="<?= htmlspecialchars($edit['key_notes'] ?? '') ?>" placeholder="Woody, Amber, Floral, Honey, Vanilla, Nutty">
            </div>
            <div class="form-group" style="max-width:200px">
                <label class="form-label">Best Before</label>
                <input type="text" name="best_before" class="form-control" value="<?= htmlspecialchars($edit['best_before'] ?? '36 Months from Mfg. Date') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Ingredients</label>
            <textarea name="ingredients" class="form-control" rows="2"><?= htmlspecialchars($edit['ingredients'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Caution</label>
            <textarea name="caution" class="form-control" rows="3"><?= htmlspecialchars($edit['caution'] ?? '') ?></textarea>
        </div>

        <!-- Images -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px">
                <i class="fas fa-images" style="color:var(--gold);margin-right:6px"></i> Product Images (3)
            </p>
            <div class="form-row">
                <div style="flex:1"><?php imgPrev('Image 1 (Main)', 'image1', 'img1', $edit['image1'] ?? ''); ?></div>
                <div style="flex:1"><?php imgPrev('Image 2', 'image2', 'img2', $edit['image2'] ?? ''); ?></div>
                <div style="flex:1"><?php imgPrev('Image 3', 'image3', 'img3', $edit['image3'] ?? ''); ?></div>
            </div>
        </div>

        <!-- Videos -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px">
                <i class="fas fa-film" style="color:var(--gold);margin-right:6px"></i> Videos
            </p>
            <div class="form-row">
                <div style="flex:1"><?php vidPrev('"Made with Love" Video', 'video1', 'vid1', $edit['video1'] ?? ''); ?></div>
                <div style="flex:1"><?php vidPrev('Second Whisper Video', 'video2', 'vid2', $edit['video2'] ?? ''); ?></div>
            </div>
        </div>

        <!-- Whisper Sections -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px">
                <i class="fas fa-feather-alt" style="color:var(--gold);margin-right:6px"></i> Whisper Sections
            </p>
            <div class="form-row" style="align-items:flex-start">
                <div class="form-group" style="flex:1">
                    <label class="form-label">First Whisper Heading</label>
                    <input type="text" name="whisper1_heading" class="form-control" value="<?= htmlspecialchars($edit['whisper1_heading'] ?? 'First Whisper') ?>">
                    <label class="form-label" style="margin-top:10px">First Whisper Content</label>
                    <textarea name="whisper1_content" class="form-control" rows="4"><?= htmlspecialchars($edit['whisper1_content'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Second Whisper Heading</label>
                    <input type="text" name="whisper2_heading" class="form-control" value="<?= htmlspecialchars($edit['whisper2_heading'] ?? 'Second Whisper') ?>">
                    <label class="form-label" style="margin-top:10px">Second Whisper Content</label>
                    <textarea name="whisper2_content" class="form-control" rows="4"><?= htmlspecialchars($edit['whisper2_content'] ?? '') ?></textarea>
                </div>
            </div>
            <!-- Whisper 1 Image -->
            <div style="margin-top:14px">
                <?php imgPrev('First Whisper Image', 'whisper1_image', 'whisper1Img', $edit['whisper1_image'] ?? ''); ?>
            </div>
        </div>

        <!-- Made with Love -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px">
                <i class="fas fa-heart" style="color:var(--gold);margin-right:6px"></i> Made with Love Section
            </p>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Heading</label>
                    <input type="text" name="made_with_love" class="form-control" value="<?= htmlspecialchars($edit['made_with_love'] ?? 'Made with love') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="made_subtitle" class="form-control" value="<?= htmlspecialchars($edit['made_subtitle'] ?? '') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit ? 'Update' : 'Add' ?> Product</button>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Products (<?= count($products) ?>)</div></div>
    <?php if (empty($products)): ?>
    <div class="empty-state"><i class="fas fa-flask"></i><h3>No products yet</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Slug</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $i => $p):
                $imgUrl = $p['image1'] ? UPLOAD_URL . 'product-details/' . $p['image1'] : '';
                $noImg  = "data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>";
            ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><img src="<?= $imgUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'" style="width:45px;height:55px;object-fit:contain;border-radius:6px;background:var(--cream)"></td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small style="color:var(--text-light)"><?= htmlspecialchars($p['subtitle']) ?></small></td>
                <td><code style="background:var(--cream-dark);padding:2px 8px;border-radius:4px;font-size:0.75rem">/<?= htmlspecialchars($p['slug']) ?>/</code></td>
                <td><strong>₹<?= number_format($p['price'], 0) ?></strong></td>
                <td>
                    <a href="?action=toggle&id=<?= $p['id'] ?>">
                        <span class="badge <?= $p['is_active'] ? 'badge-success' : 'badge-danger' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </a>
                </td>
                <td style="display:flex;gap:6px">
                    <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-icon"
                       onclick="setTimeout(()=>document.getElementById('formCard').scrollIntoView({behavior:'smooth'}),50)"><i class="fas fa-edit"></i></a>
                    <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function prevF(id) {
    const f = document.getElementById(id).files[0];
    if (!f) return;
    document.getElementById(id+'-prev').src = URL.createObjectURL(f);
    document.getElementById(id+'-name').textContent = f.name;
    document.getElementById(id+'-wrap').style.display = 'block';
}
function prevVid(id) {
    const f = document.getElementById(id).files[0];
    if (!f) return;
    document.getElementById(id+'-prev').src = URL.createObjectURL(f);
    document.getElementById(id+'-name').textContent = f.name;
    document.getElementById(id+'-wrap').style.display = 'block';
}
</script>
<?php require_once '../includes/footer.php'; ?>