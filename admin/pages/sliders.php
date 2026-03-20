<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM sliders WHERE id=$id"); showAlert('Deleted!','danger'); redirect('sliders.php');}
if($action==='toggle'&&$id){$conn->query("UPDATE sliders SET is_active=1-is_active WHERE id=$id"); redirect('sliders.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $t=sanitize($conn,$_POST['title']??''); $sub=sanitize($conn,$_POST['subtitle']??'');
    $img=sanitize($conn,$_POST['image']??''); $bt=sanitize($conn,$_POST['button_text']??'');
    $bl=sanitize($conn,$_POST['button_link']??''); $s=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE sliders SET title='$t',subtitle='$sub',image='$img',button_text='$bt',button_link='$bl',sort_order=$s WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO sliders (title,subtitle,image,button_text,button_link,sort_order) VALUES ('$t','$sub','$img','$bt','$bl',$s)"); showAlert('Slider Added!');}
    redirect('sliders.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM sliders WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT * FROM sliders ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-sliders-h"></i> Sliders / Banners</h2></div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> Slider</div>
    <?php if($edit):?><a href="sliders.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-group"><label class="form-label">Image URL / Path *</label>
            <input type="text" name="image" class="form-control" value="<?=htmlspecialchars($edit['image']??'')?>" placeholder="/uploads/slider1.jpg" required></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($edit['title']??'')?>"></div>
            <div class="form-group"><label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" value="<?=htmlspecialchars($edit['subtitle']??'')?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Button Text</label>
                <input type="text" name="button_text" class="form-control" value="<?=htmlspecialchars($edit['button_text']??'')?>"></div>
            <div class="form-group"><label class="form-label">Button Link</label>
                <input type="text" name="button_link" class="form-control" value="<?=htmlspecialchars($edit['button_link']??'')?>"></div>
        </div>
        <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?> Slider</button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Sliders (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-images"></i><h3>No sliders yet</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Image</th><th>Title</th><th>Button</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$s):?>
        <tr><td><?=$i+1?></td>
        <td><img src="<?=htmlspecialchars($s['image'])?>" class="table-img" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23ede0c8%22 width=%2250%22 height=%2250%22/><text x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23a07850%22>IMG</text></svg>'"></td>
        <td><strong><?=htmlspecialchars($s['title']?:'—')?></strong><br><small><?=htmlspecialchars(substr($s['subtitle'],0,40))?></small></td>
        <td><?=htmlspecialchars($s['button_text']?:'—')?></td>
        <td><a href="?action=toggle&id=<?=$s['id']?>"><span class="badge <?=$s['is_active']?'badge-success':'badge-danger'?>"><?=$s['is_active']?'Active':'Inactive'?></span></a></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$s['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$s['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>