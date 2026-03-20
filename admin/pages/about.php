<?php
require_once '../includes/db.php';
requireLogin();

// Ensure columns exist
$aboutCols = [
    'heading'      => "ALTER TABLE about_section ADD COLUMN heading VARCHAR(300) DEFAULT ''",
    'subheading'   => "ALTER TABLE about_section ADD COLUMN subheading VARCHAR(500) DEFAULT ''",
    'content'      => "ALTER TABLE about_section ADD COLUMN content TEXT",
    'quote'        => "ALTER TABLE about_section ADD COLUMN quote TEXT",
    'story_heading'=> "ALTER TABLE about_section ADD COLUMN story_heading VARCHAR(300) DEFAULT 'Our Story'",
    'story_content'=> "ALTER TABLE about_section ADD COLUMN story_content TEXT",
    'image1'       => "ALTER TABLE about_section ADD COLUMN image1 VARCHAR(300) DEFAULT ''",
    'image2'       => "ALTER TABLE about_section ADD COLUMN image2 VARCHAR(300) DEFAULT ''",
];
foreach ($aboutCols as $col => $sql) {
    if ($conn->query("SHOW COLUMNS FROM about_section LIKE '$col'")->num_rows === 0)
        $conn->query($sql);
}

// Upload helper
function uploadAboutImg(string $key, string $name): string|false {
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== 0) return false;
    $f   = $_FILES[$key];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($f['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'about/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = $name . '-' . time() . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $filename) ? $filename : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $h  = $conn->real_escape_string($_POST['heading']       ?? '');
    $sh = $conn->real_escape_string($_POST['subheading']    ?? '');
    $c  = $conn->real_escape_string($_POST['content']       ?? '');
    $q  = $conn->real_escape_string($_POST['quote']         ?? '');
    $sth= $conn->real_escape_string($_POST['story_heading'] ?? 'Our Story');
    $stc= $conn->real_escape_string($_POST['story_content'] ?? '');

    // Fetch existing images
    $ex = $conn->query("SELECT image1, image2 FROM about_section WHERE id=1")->fetch_assoc();
    $img1 = $ex['image1'] ?? '';
    $img2 = $ex['image2'] ?? '';
    if (($n = uploadAboutImg('image1', 'about-hero'))  !== false) $img1 = $n;
    if (($n = uploadAboutImg('image2', 'about-side'))  !== false) $img2 = $n;
    $i1 = $conn->real_escape_string($img1);
    $i2 = $conn->real_escape_string($img2);

    $conn->query("INSERT INTO about_section (id, heading, subheading, content, quote, story_heading, story_content, image1, image2)
        VALUES (1,'$h','$sh','$c','$q','$sth','$stc','$i1','$i2')
        ON DUPLICATE KEY UPDATE
            heading='$h', subheading='$sh', content='$c', quote='$q',
            story_heading='$sth', story_content='$stc',
            image1='$i1', image2='$i2'");

    showAlert('About section updated! ✅');
    redirect('about');
}

require_once '../includes/header.php';
$a = $conn->query("SELECT * FROM about_section WHERE id=1")->fetch_assoc() ?? [];
?>

<div class="page-header">
    <h2><i class="fas fa-info-circle"></i> About Page</h2>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i> About Page
</div>

<form method="POST" enctype="multipart/form-data">

    <!-- ── Section 1: Hero Text ── -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-heading"></i> Hero Text </div>
        </div>
        <div class="form-group">
            <label class="form-label">Main Heading <span style="font-size:0.75rem;color:var(--text-light)">(e.g. "Our Story")</span></label>
            <input type="text" name="heading" class="form-control"
                   value="<?= htmlspecialchars($a['heading'] ?? 'Our Story') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subheading / Description <span style="font-size:0.75rem;color:var(--text-light)">(paragraph below heading)</span></label>
            <textarea name="subheading" class="form-control" style="min-height:100px"><?= htmlspecialchars($a['subheading'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- ── Section 2: Hero Image ── -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-image"></i> Hero Full-Width Image <span style="font-size:0.75rem;color:var(--text-light);font-weight:400">(wide banner below heading)</span></div>
        </div>
        <?php if (!empty($a['image1'])): ?>
        <div style="margin-bottom:12px">
            <img src="<?= UPLOAD_URL ?>about/<?= $a['image1'] ?>?v=<?= time() ?>"
                 style="height:110px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover">
            <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:4px">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($a['image1']) ?>
            </span>
        </div>
        <?php endif; ?>
        <div onclick="document.getElementById('img1Input').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
             ondragleave="this.style.borderColor='var(--cream-deep)'"
             ondrop="handleDrop(event,'img1Input')"
             style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
            <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
            <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
            <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
            <input type="file" name="image1" id="img1Input" accept="image/*" style="display:none" onchange="prevImg('img1Input')">
        </div>
        <div id="img1Input-wrap" style="display:none;margin-top:8px">
            <img id="img1Input-prev" style="height:80px;border-radius:8px;border:2px solid var(--success);object-fit:cover">
            <span id="img1Input-name" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
        </div>
    </div>

    <!-- ── Section 3: Center Quote ── -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-quote-left"></i> Center Quote <span style="font-size:0.75rem;color:var(--text-light);font-weight:400">(centered italic text below hero image)</span></div>
        </div>
        <div class="form-group">
            <label class="form-label">Quote Text</label>
            <textarea name="quote" class="form-control" style="min-height:90px"><?= htmlspecialchars($a['quote'] ?? '') ?></textarea>
        </div>
    </div>

    <!-- ── Section 4: Our Story (side-by-side) ── -->
    <div class="card" style="margin-bottom:20px">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-align-left"></i> Our Story Section <span style="font-size:0.75rem;color:var(--text-light);font-weight:400">(text left + image right)</span></div>
        </div>
        <div class="form-group">
            <label class="form-label">Section Heading <span style="font-size:0.75rem;color:var(--text-light)">(e.g. "Our Story")</span></label>
            <input type="text" name="story_heading" class="form-control"
                   value="<?= htmlspecialchars($a['story_heading'] ?? 'Our Story') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Section Content</label>
            <textarea name="story_content" class="form-control" style="min-height:130px"><?= htmlspecialchars($a['story_content'] ?? '') ?></textarea>
        </div>

        <!-- Side Image -->
        <label class="form-label"><i class="fas fa-image"></i> Side Image <span style="font-size:0.75rem;color:var(--text-light)">(right side image)</span></label>
        <?php if (!empty($a['image2'])): ?>
        <div style="margin-bottom:12px">
            <img src="<?= UPLOAD_URL ?>about/<?= $a['image2'] ?>?v=<?= time() ?>"
                 style="height:110px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover">
            <span style="font-size:0.75rem;color:var(--success);display:block;margin-top:4px">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($a['image2']) ?>
            </span>
        </div>
        <?php endif; ?>
        <div onclick="document.getElementById('img2Input').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
             ondragleave="this.style.borderColor='var(--cream-deep)'"
             ondrop="handleDrop(event,'img2Input')"
             style="border:2px dashed var(--cream-deep);border-radius:10px;padding:18px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
            <i class="fas fa-cloud-upload-alt" style="font-size:1.6rem;color:var(--gold);display:block;margin-bottom:6px"></i>
            <p style="font-size:0.82rem;font-weight:600;color:var(--text-mid)">Click or drag & drop</p>
            <p style="font-size:0.72rem;color:var(--text-light);margin-top:3px">JPG, PNG, WebP • Max 10MB</p>
            <input type="file" name="image2" id="img2Input" accept="image/*" style="display:none" onchange="prevImg('img2Input')">
        </div>
        <div id="img2Input-wrap" style="display:none;margin-top:8px">
            <img id="img2Input-prev" style="height:80px;border-radius:8px;border:2px solid var(--success);object-fit:cover">
            <span id="img2Input-name" style="font-size:0.75rem;color:var(--success);margin-left:8px;font-weight:600"></span>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
</form>

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