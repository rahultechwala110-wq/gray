<?php
require_once '../includes/db.php';
requireLogin();

$conn->query("CREATE TABLE IF NOT EXISTS contact_info (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    address          TEXT DEFAULT '',
    phone            VARCHAR(100) DEFAULT '',
    email            VARCHAR(200) DEFAULT '',
    google_map_embed TEXT DEFAULT '',
    facebook         VARCHAR(300) DEFAULT '',
    twitter          VARCHAR(300) DEFAULT '',
    instagram        VARCHAR(300) DEFAULT '',
    linkedin         VARCHAR(300) DEFAULT '',
    youtube          VARCHAR(300) DEFAULT '',
    whatsapp         VARCHAR(300) DEFAULT '',
    hero_image       VARCHAR(300) DEFAULT '',
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(200) NOT NULL,
    email      VARCHAR(200) NOT NULL,
    subject    VARCHAR(300) DEFAULT '',
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($conn->query("SHOW COLUMNS FROM contact_info LIKE 'hero_image'")->num_rows === 0)
    $conn->query("ALTER TABLE contact_info ADD COLUMN hero_image VARCHAR(300) DEFAULT ''");

if ($conn->query("SHOW COLUMNS FROM contact_info LIKE 'whatsapp'")->num_rows === 0)
    $conn->query("ALTER TABLE contact_info ADD COLUMN whatsapp VARCHAR(300) DEFAULT ''");

$tab    = $_GET['tab']    ?? 'settings';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'read' && $id) {
    $conn->query("UPDATE contact_messages SET is_read=1 WHERE id=$id");
    redirect('contact_info?tab=messages');
}

if ($action === 'delete' && $id) {
    $conn->query("DELETE FROM contact_messages WHERE id=$id");
    showAlert('Message deleted!', 'danger');
    redirect('contact_info?tab=messages');
}

function uploadContactImg($fileKey) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== 0) return false;
    $f   = $_FILES[$fileKey];
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','avif'])) return false;
    if ($f['size'] > 10 * 1024 * 1024) return false;
    $dir = ADMIN_UPLOAD_PATH . 'contact/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = 'hero-' . time() . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], $dir . $filename) ? $filename : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'hero') {
    $existing = $conn->query("SELECT hero_image FROM contact_info WHERE id=1")->fetch_assoc();
    $hero     = $existing['hero_image'] ?? '';
    $newHero  = uploadContactImg('hero_image');
    if ($newHero !== false) $hero = $newHero;
    $hero = $conn->real_escape_string($hero);
    $conn->query("INSERT INTO contact_info (id, hero_image) VALUES (1, '$hero')
                  ON DUPLICATE KEY UPDATE hero_image='$hero'");
    showAlert('Hero image updated! ✅');
    redirect('contact_info?tab=hero');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'settings') {
    $addr = sanitize($conn, $_POST['address']          ?? '');
    $ph   = sanitize($conn, $_POST['phone']            ?? '');
    $em   = sanitize($conn, $_POST['email']            ?? '');
    $map  = $conn->real_escape_string($_POST['google_map_embed'] ?? '');
    $fb   = sanitize($conn, $_POST['facebook']         ?? '');
    $tw   = sanitize($conn, $_POST['twitter']          ?? '');
    $ig   = sanitize($conn, $_POST['instagram']        ?? '');
    $li   = sanitize($conn, $_POST['linkedin']         ?? '');
    $yt   = sanitize($conn, $_POST['youtube']          ?? '');
    $wa   = sanitize($conn, $_POST['whatsapp']         ?? '');

    $conn->query("INSERT INTO contact_info
        (id,address,phone,email,google_map_embed,facebook,twitter,instagram,linkedin,youtube,whatsapp)
        VALUES (1,'$addr','$ph','$em','$map','$fb','$tw','$ig','$li','$yt','$wa')
        ON DUPLICATE KEY UPDATE
        address='$addr', phone='$ph', email='$em',
        google_map_embed='$map', facebook='$fb', twitter='$tw',
        instagram='$ig', linkedin='$li', youtube='$yt', whatsapp='$wa'");

    showAlert('Contact info updated! ✅');
    redirect('contact_info?tab=settings');
}

require_once '../includes/header.php';

$c        = $conn->query("SELECT * FROM contact_info WHERE id=1")->fetch_assoc() ?? [];
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread   = $conn->query("SELECT COUNT(*) as c FROM contact_messages WHERE is_read=0")->fetch_assoc()['c'];
$heroUrl  = ($c['hero_image'] ?? '') ? UPLOAD_URL . 'contact/' . $c['hero_image'] : '';

function cv($c, $k) { return htmlspecialchars($c[$k] ?? ''); }
?>

<div class="page-header">
    <h2><i class="fas fa-map-marker-alt"></i> Contact Information</h2>
    <a href="<?= ADMIN_URL ?>/pages/dashboard" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>
<div class="breadcrumb" style="margin-bottom:20px">
    <a href="<?= ADMIN_URL ?>/pages/dashboard">Dashboard</a>
    <i class="fas fa-chevron-right" style="font-size:0.7rem;margin:0 6px"></i> Contact
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid var(--cream-deep)">
    <a href="?tab=hero" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='hero'?'var(--gold)':'transparent' ?>;color:<?= $tab==='hero'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-image"></i> Hero Image
    </a>
    <a href="?tab=settings" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='settings'?'var(--gold)':'transparent' ?>;color:<?= $tab==='settings'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-cog"></i> Settings
    </a>
    <a href="?tab=messages" style="padding:10px 28px;font-size:0.9rem;font-weight:600;text-decoration:none;border-bottom:3px solid <?= $tab==='messages'?'var(--gold)':'transparent' ?>;color:<?= $tab==='messages'?'var(--gold)':'var(--text-light)' ?>;margin-bottom:-2px;transition:all 0.2s">
        <i class="fas fa-inbox"></i> Messages (<?= count($messages) ?>)
        <?php if ($unread > 0): ?>
        <span style="background:var(--danger,#e74c3c);color:white;font-size:0.65rem;padding:1px 6px;border-radius:10px;margin-left:4px"><?= $unread ?></span>
        <?php endif; ?>
    </a>
</div>

<?php if ($tab === 'settings'): ?>
<!-- ═══════ SETTINGS ═══════ -->
<form method="POST" action="?tab=settings">

    <div class="card" style="margin-bottom:20px">
        <div class="card-header"><div class="card-title"><i class="fas fa-address-card"></i> Contact Details</div></div>
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control"><?= cv($c,'address') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= cv($c,'phone') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= cv($c,'email') ?>">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Google Map Embed URL
                <span style="font-size:0.75rem;color:var(--text-light)">(Google Maps → Share → Embed a map → src URL only)</span>
            </label>
            <textarea name="google_map_embed" class="form-control" rows="3"
                      placeholder="https://www.google.com/maps/embed?pb=..."><?= cv($c,'google_map_embed') ?></textarea>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px">
        <div class="card-header"><div class="card-title"><i class="fas fa-share-alt"></i> Social Media Links</div></div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook</label>
                <input type="url" name="facebook" class="form-control" value="<?= cv($c,'facebook') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-twitter" style="color:#1da1f2"></i> Twitter</label>
                <input type="url" name="twitter" class="form-control" value="<?= cv($c,'twitter') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-instagram" style="color:#e1306c"></i> Instagram</label>
                <input type="url" name="instagram" class="form-control" value="<?= cv($c,'instagram') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn</label>
                <input type="url" name="linkedin" class="form-control" value="<?= cv($c,'linkedin') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-whatsapp" style="color:#25d366"></i> WhatsApp</label>
                <input type="url" name="whatsapp" class="form-control" value="<?= cv($c,'whatsapp') ?>" placeholder="https://wa.me/911234567890">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fab fa-youtube" style="color:#ff0000"></i> YouTube</label>
                <input type="url" name="youtube" class="form-control" value="<?= cv($c,'youtube') ?>">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Contact Info</button>
</form>

<?php elseif ($tab === 'hero'): ?>
<!-- ═══════ HERO IMAGE ═══════ -->
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-image"></i> Hero Image</div></div>
    <form method="POST" action="?tab=hero" enctype="multipart/form-data">
        <?php if ($heroUrl): ?>
        <div style="margin-bottom:16px">
            <img src="<?= $heroUrl ?>?v=<?= time() ?>"
                 style="max-width:400px;width:100%;height:150px;border-radius:8px;border:2px solid var(--cream-deep);object-fit:cover;display:block">
            <span style="font-size:0.75rem;color:var(--success);margin-top:6px;display:block;font-weight:600">
                <i class="fas fa-check-circle"></i> Current: <?= htmlspecialchars($c['hero_image']) ?>
            </span>
        </div>
        <?php endif; ?>
        <div onclick="document.getElementById('heroInput').click()"
             ondragover="event.preventDefault();this.style.borderColor='var(--gold)'"
             ondragleave="this.style.borderColor='var(--cream-deep)'"
             style="border:2px dashed var(--cream-deep);border-radius:10px;padding:28px;text-align:center;cursor:pointer;background:var(--cream);transition:all 0.2s">
            <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--gold);display:block;margin-bottom:8px"></i>
            <p style="font-size:0.85rem;font-weight:600;color:var(--text-mid)"><?= $heroUrl ? 'Replace image' : 'Click or drag & drop' ?></p>
            <p style="font-size:0.72rem;color:var(--text-light);margin-top:4px">JPG, PNG, WebP • Max 10MB</p>
            <input type="file" name="hero_image" id="heroInput" accept="image/*"
                   style="display:none" onchange="prevHero(this)">
        </div>
        <div id="heroPrev" style="display:none;margin-top:12px">
            <img id="heroPrevImg" style="max-width:400px;width:100%;height:150px;border-radius:8px;border:2px solid var(--success);object-fit:cover;display:block">
            <span id="heroPrevName" style="font-size:0.75rem;color:var(--success);margin-top:6px;display:block;font-weight:600"></span>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:16px">
            <i class="fas fa-save"></i> Save Hero Image
        </button>
    </form>
</div>

<?php else: ?>
<!-- ═══════ MESSAGES ═══════ -->
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-inbox"></i> Messages (<?= count($messages) ?>)</div></div>
    <?php if (empty($messages)): ?>
    <div style="padding:40px;text-align:center;color:var(--text-light)">
        <i class="fas fa-inbox" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:0.3"></i>
        <p>No messages yet.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($messages as $i => $m): ?>
            <tr style="<?= !$m['is_read'] ? 'background:var(--cream)' : '' ?>">
                <td><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color:var(--gold)"><?= htmlspecialchars($m['email']) ?></a></td>
                <td><small><?= htmlspecialchars($m['subject'] ?: '—') ?></small></td>
                <td style="max-width:220px"><small style="color:var(--text-light)"><?= htmlspecialchars(substr($m['message'], 0, 80)) ?>...</small></td>
                <td><small style="color:var(--text-light)"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></small></td>
                <td>
                    <?php if (!$m['is_read']): ?>
                    <span class="badge badge-success">New</span>
                    <?php else: ?>
                    <span class="badge" style="background:var(--cream-deep);color:var(--text-light)">Read</span>
                    <?php endif; ?>
                </td>
                <td style="display:flex;gap:6px">
                    <?php if (!$m['is_read']): ?>
                    <a href="?tab=messages&action=read&id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </a>
                    <?php endif; ?>
                    <a href="?tab=messages&action=delete&id=<?= $m['id'] ?>"
                       class="btn btn-danger btn-sm btn-icon"
                       onclick="return confirm('Delete this message?')">
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
function prevHero(input) {
    const file = input.files[0];
    if (!file) return;
    document.getElementById('heroPrevImg').src = URL.createObjectURL(file);
    document.getElementById('heroPrevName').textContent = file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    document.getElementById('heroPrev').style.display = 'block';
}
</script>

<?php require_once '../includes/footer.php'; ?>