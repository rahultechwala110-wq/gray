<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM features WHERE id=$id"); showAlert('Deleted!','danger'); redirect('features.php');}
if($action==='toggle'&&$id){$conn->query("UPDATE features SET is_active=1-is_active WHERE id=$id"); redirect('features.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $t=sanitize($conn,$_POST['title']??''); $d=sanitize($conn,$_POST['description']??'');
    $i=sanitize($conn,$_POST['icon']??''); $s=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE features SET title='$t',description='$d',icon='$i',sort_order=$s WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO features (title,description,icon,sort_order) VALUES ('$t','$d','$i',$s)"); showAlert('Added!');}
    redirect('features.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM features WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT * FROM features ORDER BY sort_order,id")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-star"></i> Features Section</h2></div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> Feature</div>
    <?php if($edit):?><a href="features.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($edit['title']??'')?>" required></div>
            <div class="form-group"><label class="form-label">Icon (Font Awesome)</label>
                <input type="text" name="icon" class="form-control" value="<?=htmlspecialchars($edit['icon']??'')?>" placeholder="fas fa-rocket"></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?=htmlspecialchars($edit['description']??'')?></textarea></div>
        <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Features (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-star"></i><h3>No features yet</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Icon</th><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$f):?>
        <tr><td><?=$i+1?></td>
        <td><i class="<?=htmlspecialchars($f['icon'])?>" style="font-size:1.3rem;color:var(--gold)"></i></td>
        <td><strong><?=htmlspecialchars($f['title'])?></strong></td>
        <td><?=htmlspecialchars(substr($f['description'],0,60))?>...</td>
        <td><a href="?action=toggle&id=<?=$f['id']?>"><span class="badge <?=$f['is_active']?'badge-success':'badge-danger'?>"><?=$f['is_active']?'Active':'Inactive'?></span></a></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$f['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$f['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>