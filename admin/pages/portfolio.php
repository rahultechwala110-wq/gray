<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM portfolio_items WHERE id=$id"); showAlert('Deleted!','danger'); redirect('portfolio.php');}
if($action==='toggle'&&$id){$conn->query("UPDATE portfolio_items SET is_active=1-is_active WHERE id=$id"); redirect('portfolio.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $t=sanitize($conn,$_POST['title']??''); $d=sanitize($conn,$_POST['description']??'');
    $img=sanitize($conn,$_POST['image']??''); $cl=sanitize($conn,$_POST['client_name']??'');
    $url=sanitize($conn,$_POST['project_url']??''); $cat=(int)($_POST['category_id']??0); $s=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE portfolio_items SET title='$t',description='$d',image='$img',client_name='$cl',project_url='$url',category_id=$cat,sort_order=$s WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO portfolio_items (title,description,image,client_name,project_url,category_id,sort_order) VALUES ('$t','$d','$img','$cl','$url',$cat,$s)"); showAlert('Added!');}
    redirect('portfolio.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM portfolio_items WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$cats=$conn->query("SELECT * FROM portfolio_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$list=$conn->query("SELECT pi.*, pc.name as cat FROM portfolio_items pi LEFT JOIN portfolio_categories pc ON pi.category_id=pc.id ORDER BY pi.sort_order,pi.id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-briefcase"></i> Portfolio</h2>
    <a href="portfolio_categories.php" class="btn btn-secondary btn-sm"><i class="fas fa-tags"></i> Categories</a>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> Portfolio Item</div>
    <?php if($edit):?><a href="portfolio.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Project Title *</label>
                <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($edit['title']??'')?>" required></div>
            <div class="form-group"><label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="0">-- Select --</option>
                    <?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=($edit['category_id']??'')==$c['id']?'selected':''?>><?=htmlspecialchars($c['name'])?></option><?php endforeach;?>
                </select></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?=htmlspecialchars($edit['description']??'')?></textarea></div>
        <div class="form-group"><label class="form-label">Image URL</label>
            <input type="text" name="image" class="form-control" value="<?=htmlspecialchars($edit['image']??'')?>" placeholder="/uploads/project.jpg"></div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Client Name</label>
                <input type="text" name="client_name" class="form-control" value="<?=htmlspecialchars($edit['client_name']??'')?>"></div>
            <div class="form-group"><label class="form-label">Project URL</label>
                <input type="url" name="project_url" class="form-control" value="<?=htmlspecialchars($edit['project_url']??'')?>"></div>
        </div>
        <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Portfolio Items (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-briefcase"></i><h3>No items yet</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Image</th><th>Title</th><th>Category</th><th>Client</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$p):?>
        <tr><td><?=$i+1?></td>
        <td><img src="<?=htmlspecialchars($p['image'])?>" class="table-img" onerror="this.style.background='var(--cream-dark)'"></td>
        <td><strong><?=htmlspecialchars($p['title'])?></strong></td>
        <td><?=htmlspecialchars($p['cat']?:'—')?></td>
        <td><?=htmlspecialchars($p['client_name']?:'—')?></td>
        <td><a href="?action=toggle&id=<?=$p['id']?>"><span class="badge <?=$p['is_active']?'badge-success':'badge-danger'?>"><?=$p['is_active']?'Active':'Inactive'?></span></a></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$p['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$p['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>