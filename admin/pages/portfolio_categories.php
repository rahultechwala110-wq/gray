<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM portfolio_categories WHERE id=$id"); showAlert('Deleted!','danger'); redirect('portfolio_categories.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $n=sanitize($conn,$_POST['name']??''); $sl=sanitize($conn,strtolower(preg_replace('/[^a-z0-9]+/','-',strtolower($n)))); $s=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE portfolio_categories SET name='$n',slug='$sl',sort_order=$s WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO portfolio_categories (name,slug,sort_order) VALUES ('$n','$sl',$s)"); showAlert('Added!');}
    redirect('portfolio_categories.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM portfolio_categories WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT pc.*, COUNT(pi.id) as item_count FROM portfolio_categories pc LEFT JOIN portfolio_items pi ON pc.id=pi.category_id GROUP BY pc.id ORDER BY pc.sort_order")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-tags"></i> Portfolio Categories</h2>
    <a href="portfolio.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div class="form-row" style="align-items:start">
    <div class="card" style="margin:0">
        <div class="card-header"><div class="card-title"><?=$edit?'Edit':'Add'?> Category</div>
        <?php if($edit):?><a href="portfolio_categories.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
        <form method="POST">
            <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
            <div class="form-group"><label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($edit['name']??'')?>" required></div>
            <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
        </form>
    </div>
    <div class="card" style="margin:0">
        <div class="card-header"><div class="card-title">All Categories (<?=count($list)?>)</div></div>
        <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-tags"></i><h3>No categories</h3></div>
        <?php else:?><div class="table-responsive"><table>
        <thead><tr><th>Name</th><th>Slug</th><th>Items</th><th>Actions</th></tr></thead>
        <tbody><?php foreach($list as $c):?>
            <tr><td><strong><?=htmlspecialchars($c['name'])?></strong></td>
            <td><code><?=htmlspecialchars($c['slug'])?></code></td>
            <td><span class="badge badge-info"><?=$c['item_count']?></span></td>
            <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$c['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
            <a href="?action=delete&id=<?=$c['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endforeach;?></tbody></table></div><?php endif;?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>