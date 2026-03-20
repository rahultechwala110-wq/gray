<?php
require_once '../includes/db.php';
requireLogin();

$ensureCols = [
    "image"         => "ALTER TABLE blog_posts ADD COLUMN image VARCHAR(300) DEFAULT '' AFTER excerpt",
    "sort_order"    => "ALTER TABLE blog_posts ADD COLUMN sort_order INT DEFAULT 0",
    "author_name"   => "ALTER TABLE blog_posts ADD COLUMN author_name VARCHAR(200) DEFAULT 'Admin'",
    "author_title"  => "ALTER TABLE blog_posts ADD COLUMN author_title VARCHAR(200) DEFAULT 'Senior Perfumer'",
    "author_photo"  => "ALTER TABLE blog_posts ADD COLUMN author_photo VARCHAR(300) DEFAULT ''",
    "banner_image"  => "ALTER TABLE blog_posts ADD COLUMN banner_image VARCHAR(300) DEFAULT ''",
    "feat_name"     => "ALTER TABLE blog_posts ADD COLUMN feat_name VARCHAR(200) DEFAULT ''",
    "feat_subtitle" => "ALTER TABLE blog_posts ADD COLUMN feat_subtitle VARCHAR(200) DEFAULT ''",
    "feat_image"    => "ALTER TABLE blog_posts ADD COLUMN feat_image VARCHAR(300) DEFAULT ''",
    "feat_link"     => "ALTER TABLE blog_posts ADD COLUMN feat_link VARCHAR(300) DEFAULT '#'",
];
foreach ($ensureCols as $col => $sql) {
    if ($conn->query("SHOW COLUMNS FROM blog_posts LIKE '$col'")->num_rows === 0)
        $conn->query($sql);
}

$conn->query("CREATE TABLE IF NOT EXISTS blog_section_settings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    heading    VARCHAR(300) DEFAULT 'Latest Cosmetic News',
    subheading VARCHAR(500) DEFAULT 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
if ($conn->query("SELECT id FROM blog_section_settings WHERE id=1")->num_rows === 0)
    $conn->query("INSERT INTO blog_section_settings (id) VALUES (1)");

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$tab    = $_GET['tab'] ?? 'posts';

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM blog_posts WHERE id=$id");
    showAlert('Post deleted!', 'danger');
    redirect('blog?tab=posts');
}

if ($action === 'toggle' && $id) {
    $row = $conn->query("SELECT status FROM blog_posts WHERE id=$id")->fetch_assoc();
    $ns  = ($row['status'] === 'published') ? 'draft' : 'published';
    $conn->query("UPDATE blog_posts SET status='$ns' WHERE id=$id");
    redirect('blog?tab=posts');
}

function uploadBlogImg(string $fileKey, string $prefix): string|false {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $f   = $_FILES[$fileKey];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($f['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'blog/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $prefix . '-' . time() . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $filename) ? $filename : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $h = $conn->real_escape_string($_POST['heading']    ?? '');
    $s = $conn->real_escape_string($_POST['subheading'] ?? '');
    $conn->query("UPDATE blog_section_settings SET heading='$h', subheading='$s' WHERE id=1");
    showAlert('Section settings saved! ✅');
    redirect('blog?tab=settings');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['save_settings'])) {
    $eid    = (int)($_POST['edit_id'] ?? 0);
    $title  = sanitize($conn, $_POST['title']   ?? '');
    $exc    = sanitize($conn, $_POST['excerpt']  ?? '');
    $cont   = $conn->real_escape_string($_POST['content'] ?? '');
    $cat    = (int)($_POST['category_id'] ?? 0);
    $catVal = $cat ? $cat : 'NULL';
    $tags   = sanitize($conn, $_POST['tags']     ?? '');
    $status = sanitize($conn, $_POST['status']   ?? 'draft');
    $sort   = (int)($_POST['sort_order'] ?? 0);
    $auth   = $_SESSION['admin_id'];
    $aName  = $conn->real_escape_string($_POST['author_name']   ?? 'Admin');
    $aTitle = $conn->real_escape_string($_POST['author_title']  ?? 'Senior Perfumer');
    $fName  = $conn->real_escape_string($_POST['feat_name']     ?? '');
    $fSub   = $conn->real_escape_string($_POST['feat_subtitle'] ?? '');
    $fLink  = $conn->real_escape_string($_POST['feat_link']     ?? '#');

    $baseSlug = trim(strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($_POST['title'] ?? ''))), '-');
    $slug     = $baseSlug;
    $counter  = 1;
    while ($conn->query("SELECT id FROM blog_posts WHERE slug='$slug'" . ($eid ? " AND id != $eid" : ""))->num_rows > 0) {
        $slug = $baseSlug . '-' . $counter++;
    }
    $slug = $conn->real_escape_string($slug);

    if ($eid) {
        $ex     = $conn->query("SELECT image, banner_image, author_photo, feat_image FROM blog_posts WHERE id=$eid")->fetch_assoc();
        $image  = $ex['image']        ?? '';
        $banner = $ex['banner_image'] ?? '';
        $aPhoto = $ex['author_photo'] ?? '';
        $fImage = $ex['feat_image']   ?? '';

        if (($n = uploadBlogImg('image',        $slug ?: 'blog'))   !== false) $image  = $n;
        if (($n = uploadBlogImg('banner_image', $slug . '-banner')) !== false) $banner = $n;
        if (($n = uploadBlogImg('author_photo', $slug . '-author')) !== false) $aPhoto = $n;
        if (($n = uploadBlogImg('feat_image',   $slug . '-feat'))   !== false) $fImage = $n;

        $img = $conn->real_escape_string($image);
        $ban = $conn->real_escape_string($banner);
        $aph = $conn->real_escape_string($aPhoto);
        $fim = $conn->real_escape_string($fImage);

        $conn->query("UPDATE blog_posts SET
            title='$title', slug='$slug', excerpt='$exc', content='$cont',
            category_id=$catVal, tags='$tags', status='$status', sort_order=$sort,
            image='$img', banner_image='$ban',
            author_name='$aName', author_title='$aTitle', author_photo='$aph',
            feat_name='$fName', feat_subtitle='$fSub', feat_image='$fim', feat_link='$fLink'
            WHERE id=$eid");
        showAlert('Post updated! ✅');
    } else {
        $image  = (($n = uploadBlogImg('image',        $slug ?: 'blog'))   !== false) ? $n : '';
        $banner = (($n = uploadBlogImg('banner_image', $slug . '-banner')) !== false) ? $n : '';
        $aPhoto = (($n = uploadBlogImg('author_photo', $slug . '-author')) !== false) ? $n : '';
        $fImage = (($n = uploadBlogImg('feat_image',   $slug . '-feat'))   !== false) ? $n : '';

        $img = $conn->real_escape_string($image);
        $ban = $conn->real_escape_string($banner);
        $aph = $conn->real_escape_string($aPhoto);
        $fim = $conn->real_escape_string($fImage);

        $conn->query("INSERT INTO blog_posts
            (title, slug, excerpt, content, category_id, tags, status, sort_order, author_id,
             image, banner_image, author_name, author_title, author_photo,
             feat_name, feat_subtitle, feat_image, feat_link)
            VALUES
            ('$title','$slug','$exc','$cont',$catVal,'$tags','$status',$sort,$auth,
             '$img','$ban','$aName','$aTitle','$aph',
             '$fName','$fSub','$fim','$fLink')");
        showAlert('Post added! ✅');
    }
    redirect('blog?tab=posts');
}

$edit = null;
if ($action === 'edit' && $id) {
    $edit = $conn->query("SELECT * FROM blog_posts WHERE id=$id")->fetch_assoc();
    $tab  = 'posts';
}

require_once '../includes/header.php';

$postsQ   = $conn->query("SELECT bp.*, bc.name as cat_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bp.category_id=bc.id ORDER BY bp.sort_order ASC, bp.created_at DESC");
$posts    = $postsQ ? $postsQ->fetch_all(MYSQLI_ASSOC) : [];
$cats     = $conn->query("SELECT * FROM blog_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$settings = $conn->query("SELECT * FROM blog_section_settings WHERE id=1")->fetch_assoc();

function imgRow(string $label, string $fieldName, string $existingFile, string $inputId): void { ?>
<div class="form-group">
    <label class="form-label">
        <i class="fas fa-image"></i> <?= $label ?>
        <span style="font-size:0.75rem;color:var(--text-light)"> — /public/blog/</span>
    </label>
    <?php if (!empty($existingFile)): ?>
    <div style="margin-bottom:10px">
        <img src="<?= UPLOAD_URL ?>blog/<?= $existingFile ?>?v=<?= time() ?>"
             style="height:90px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover">
        <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:3px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($existingFile) ?>
        </span>
    </div>
    <?php endif; ?>
    <div onclick="document.getElementById('<?= $inputId ?>').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
         ondragleave="this.style.borderColor='var(--cream-deep)'"
         ondrop="handleDrop(event,'<?= $inputId ?>')"
         style="border:2px dashed var(--cream-deep);border-radius:10px;padding:16px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
        <i class="fas fa-cloud-upload-alt" style="font-size:1.5rem;color:var(--gold);display:block;margin-bottom:6px"></i>
        <p style="font-size:0.8rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
        <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
        <input type="file" name="<?= $fieldName ?>" id="<?= $inputId ?>" accept="image/*"
               style="display:none" onchange="prevImg('<?= $inputId ?>')">
    </div>
    <div id="<?= $inputId ?>-wrap" style="display:none;margin-top:8px">
        <img id="<?= $inputId ?>-prev" style="height:80px;border-radius:8px;border:2px solid var(--success);object-fit:cover">
        <span id="<?= $inputId ?>-name" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
    </div>
</div>
<?php } ?>

<div class="page-header">
    <h2><i class="fas fa-blog"></i> Blog / News</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i> Blog / News
</div>

<!-- Tabs — Product Showcase style -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=settings" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='settings'?'var(--gold)':'transparent' ?>;color:<?= $tab==='settings'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-cog"></i> Section Settings
    </a>
    <a href="?tab=posts" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='posts'?'var(--gold)':'transparent' ?>;color:<?= $tab==='posts'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-file-alt"></i> Posts (<?= count($posts) ?>)
    </a>
</div>

<?php if ($tab === 'settings'): ?>
<!-- ═══════════════════ SETTINGS TAB ═══════════════════ -->
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-heading"></i> Section Heading</div></div>
    <form method="POST">
        <input type="hidden" name="save_settings" value="1">
        <div class="form-group">
            <label class="form-label">Main Heading</label>
            <input type="text" name="heading" class="form-control" value="<?= htmlspecialchars($settings['heading'] ?? 'Latest Cosmetic News') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subheading / Description</label>
            <input type="text" name="subheading" class="form-control" value="<?= htmlspecialchars($settings['subheading'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
    </form>
</div>

<?php else: ?>
<!-- ═══════════════════ POSTS TAB ═══════════════════ -->
<div class="card" style="margin-bottom:20px" id="formCard">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-<?= $edit ? 'edit' : 'plus' ?>"></i>
            <?= $edit ? 'Edit: ' . htmlspecialchars($edit['title']) : 'Add New Post' ?>
        </div>
        <?php if ($edit): ?>
        <a href="<?= ADMIN_URL ?>/pages/blog?tab=posts" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <?php if ($edit): ?><input type="hidden" name="edit_id" value="<?= $edit['id'] ?>"><?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit['title'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="max-width:200px">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="0">— No Category —</option>
                    <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($edit['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="max-width:150px">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="published" <?= ($edit['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft"     <?= ($edit['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                </select>
            </div>
            <div class="form-group" style="max-width:120px">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?= $edit['sort_order'] ?? 0 ?>">
            </div>
        </div>

        <!-- Images -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:14px">
                <i class="fas fa-images" style="margin-right:6px;color:var(--gold)"></i> Images
            </p>
            <div class="form-row" style="align-items:flex-start">
                <div style="flex:1"><?php imgRow('Thumbnail (Blog List)', 'image', $edit['image'] ?? '', 'blogThumb'); ?></div>
                <div style="flex:1"><?php imgRow('Hero / Banner (Detail Page)', 'banner_image', $edit['banner_image'] ?? '', 'blogBanner'); ?></div>
            </div>
        </div>

        <!-- Content -->
        <div class="form-group">
            <label class="form-label">Excerpt <span style="font-size:0.75rem;color:var(--text-light)">(Short description)</span></label>
            <textarea name="excerpt" class="form-control" style="min-height:70px"><?= htmlspecialchars($edit['excerpt'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Full Content</label>
            <textarea name="content" class="form-control" style="min-height:200px"><?= htmlspecialchars($edit['content'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Tags <span style="font-size:0.75rem;color:var(--text-light)">(comma separated)</span></label>
            <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($edit['tags'] ?? '') ?>" placeholder="Fragrance, Skincare, Ingredients">
        </div>

        <!-- Author -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:14px">
                <i class="fas fa-user" style="margin-right:6px;color:var(--gold)"></i> Author (Sidebar)
            </p>
            <div class="form-row" style="align-items:flex-start">
                <div class="form-group" style="flex:1">
                    <label class="form-label">Author Name</label>
                    <input type="text" name="author_name" class="form-control" value="<?= htmlspecialchars($edit['author_name'] ?? 'Admin') ?>">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Author Title / Role</label>
                    <input type="text" name="author_title" class="form-control" value="<?= htmlspecialchars($edit['author_title'] ?? 'Senior Perfumer') ?>">
                </div>
                <div style="flex:1"><?php imgRow('Author Photo', 'author_photo', $edit['author_photo'] ?? '', 'authorPhoto'); ?></div>
            </div>
        </div>

        <!-- Featured Product -->
        <div style="border-top:1px solid var(--cream-deep);margin:16px 0;padding-top:14px">
            <p style="font-size:0.8rem;font-weight:700;color:var(--text-mid);letter-spacing:0.08em;text-transform:uppercase;margin-bottom:14px">
                <i class="fas fa-star" style="margin-right:6px;color:var(--gold)"></i> Featured Product (Sidebar)
            </p>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="feat_name" class="form-control" value="<?= htmlspecialchars($edit['feat_name'] ?? '') ?>" placeholder="e.g. Brave">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="feat_subtitle" class="form-control" value="<?= htmlspecialchars($edit['feat_subtitle'] ?? '') ?>" placeholder="e.g. 100ml EDP">
                </div>
                <div class="form-group" style="flex:1">
                    <label class="form-label">Shop Link</label>
                    <input type="text" name="feat_link" class="form-control" value="<?= htmlspecialchars($edit['feat_link'] ?? '#') ?>" placeholder="/products/brave">
                </div>
            </div>
            <?php imgRow('Featured Product Image', 'feat_image', $edit['feat_image'] ?? '', 'featImg'); ?>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:8px">
            <i class="fas fa-save"></i> <?= $edit ? 'Update Post' : 'Add Post' ?>
        </button>
    </form>
</div>

<!-- Posts Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list"></i> All Posts (<?= count($posts) ?>)</div>
    </div>
    <?php if (empty($posts)): ?>
    <div class="empty-state"><i class="fas fa-blog"></i><h3>No posts yet</h3><p>Add your first blog post above</p></div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Thumb</th><th>Banner</th><th>Title</th><th>Category</th><th>Author</th><th>Tags</th><th>Sort</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php
            $noImg = "data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><rect fill=%22%23ede0c8%22 width=%2260%22 height=%2260%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22 font-size=%228%22>IMG</text></svg>";
            foreach ($posts as $i => $p):
                $imgUrl    = $p['image']        ? UPLOAD_URL . 'blog/' . $p['image']        : '';
                $bannerUrl = $p['banner_image'] ? UPLOAD_URL . 'blog/' . $p['banner_image'] : '';
            ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><img src="<?= $imgUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'" style="width:55px;height:50px;object-fit:cover;border-radius:6px" title="Thumbnail"></td>
                    <td><img src="<?= $bannerUrl ?: $noImg ?>" onerror="this.src='<?= $noImg ?>'" style="width:55px;height:50px;object-fit:cover;border-radius:6px" title="Banner"></td>
                    <td>
                        <strong><?= htmlspecialchars($p['title']) ?></strong><br>
                        <small style="color:var(--text-light)">/blog-details/<?= htmlspecialchars($p['slug']) ?></small>
                    </td>
                    <td><span style="font-size:0.8rem;color:var(--text-light)"><?= htmlspecialchars($p['cat_name'] ?? '—') ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px">
                            <?php if (!empty($p['author_photo'])): ?>
                            <img src="<?= UPLOAD_URL ?>blog/<?= $p['author_photo'] ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                            <?php endif; ?>
                            <span style="font-size:0.8rem"><?= htmlspecialchars($p['author_name'] ?? 'Admin') ?></span>
                        </div>
                    </td>
                    <td style="max-width:140px">
                        <?php foreach (array_slice(array_map('trim', explode(',', $p['tags'] ?? '')), 0, 2) as $tag): ?>
                        <?php if ($tag): ?>
                        <span style="font-size:0.7rem;background:var(--cream-deep);padding:2px 7px;border-radius:20px;margin-right:3px;display:inline-block"><?= htmlspecialchars($tag) ?></span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                    <td><?= $p['sort_order'] ?></td>
                    <td>
                        <a href="?tab=posts&action=toggle&id=<?= $p['id'] ?>">
                            <span class="badge <?= $p['status']==='published' ? 'badge-success' : 'badge-danger' ?>"><?= ucfirst($p['status']) ?></span>
                        </a>
                    </td>
                    <td><small style="color:var(--text-light)"><?= date('M j, Y', strtotime($p['created_at'])) ?></small></td>
                    <td style="display:flex;gap:6px">
                        <a href="?tab=posts&action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-icon"
                           onclick="setTimeout(()=>document.getElementById('formCard').scrollIntoView({behavior:'smooth'}),50)">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?tab=posts&action=delete&id=<?= $p['id'] ?>"
                           class="btn btn-danger btn-sm btn-icon"
                           onclick="confirmDelete(this.href,'Delete Post?','&quot;<?= htmlspecialchars(addslashes($p['title'])) ?>&quot; permanently delete ho jayega.');return false;">
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
function prevImg(inputId) {
    const input = document.getElementById(inputId);
    const file  = input.files[0];
    if (!file) return;
    document.getElementById(inputId + '-prev').src = URL.createObjectURL(file);
    document.getElementById(inputId + '-name').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById(inputId + '-wrap').style.display = 'block';
}
function handleDrop(e, inputId) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById(inputId).files = dt.files;
    prevImg(inputId);
}
</script>

<?php require_once '../includes/footer.php'; ?>