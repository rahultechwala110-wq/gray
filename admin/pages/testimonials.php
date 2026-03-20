<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if ($action==='delete'&&$id){$conn->query("DELETE FROM testimonials WHERE id=$id"); showAlert('Deleted!','danger'); redirect('testimonials');}
if ($action==='toggle'&&$id){$conn->query("UPDATE testimonials SET is_active=1-is_active WHERE id=$id"); redirect('testimonials');}
if ($_SERVER['REQUEST_METHOD']==='POST'){
    $n=sanitize($conn,$_POST['client_name']??''); $c=sanitize($conn,$_POST['company']??'');
    $r=sanitize($conn,$_POST['review']??''); $rat=(int)($_POST['rating']??5);
    $feat=isset($_POST['is_featured'])?1:0; $sort=(int)($_POST['sort_order']??0);
    $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE testimonials SET client_name='$n',company='$c',review='$r',rating=$rat,is_featured=$feat,sort_order=$sort WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO testimonials (client_name,company,review,rating,is_featured,sort_order) VALUES ('$n','$c','$r',$rat,$feat,$sort)"); showAlert('Added!');}
    redirect('testimonials');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM testimonials WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT * FROM testimonials ORDER BY sort_order,id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-quote-left"></i> Testimonials</h2></div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> Testimonial</div>
    <?php if($edit):?><a href="<?= ADMIN_URL ?>/pages/testimonials" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Client Name *</label>
                <input type="text" name="client_name" class="form-control" value="<?=htmlspecialchars($edit['client_name']??'')?>" required></div>
            <div class="form-group"><label class="form-label">Company</label>
                <input type="text" name="company" class="form-control" value="<?=htmlspecialchars($edit['company']??'')?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Review *</label>
            <textarea name="review" class="form-control" required><?=htmlspecialchars($edit['review']??'')?></textarea></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Rating (1-5)</label>
                <select name="rating" class="form-control">
                <?php for($r=5;$r>=1;$r--):?><option value="<?=$r?>" <?=($edit['rating']??5)==$r?'selected':''?>><?=$r?> ⭐</option><?php endfor;?>
                </select></div>
            <div class="form-group"><label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:10px;">
            <label class="toggle-switch"><input type="checkbox" name="is_featured" <?=($edit['is_featured']??0)?'checked':''?>>
            <span class="toggle-slider"></span></label>
            <label class="form-label" style="margin:0">Featured?</label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Testimonials (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-quote-left"></i><h3>No testimonials</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Client</th><th>Company</th><th>Review</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$t):?>
        <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars($t['client_name'])?></strong></td>
        <td><?=htmlspecialchars($t['company']?:'—')?></td>
        <td><?=substr(htmlspecialchars($t['review']),0,60)?>...</td>
        <td class="stars"><?=str_repeat('★',$t['rating'])?></td>
        <td><a href="?action=toggle&id=<?=$t['id']?>"><span class="badge <?=$t['is_active']?'badge-success':'badge-danger'?>"><?=$t['is_active']?'Active':'Inactive'?></span></a></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$t['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$t['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="confirmDelete(this.href,'Delete Item?','This item will be permanently deleted. This cannot be undone.');return false;"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div>
    <?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>