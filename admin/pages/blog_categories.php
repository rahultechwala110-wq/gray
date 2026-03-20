<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM blog_categories WHERE id=$id"); showAlert('Deleted!','danger'); redirect('blog_categories.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $n=sanitize($conn,$_POST['name']??''); $sl=sanitize($conn,strtolower(preg_replace('/[^a-z0-9]+/','-',strtolower($n)))); $d=sanitize($conn,$_POST['description']??''); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE blog_categories SET name='$n',slug='$sl',description='$d' WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO blog_categories (name,slug,description) VALUES ('$n','$sl','$d')"); showAlert('Category Added!');}
    redirect('blog_categories.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM blog_categories WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT bc.*, COUNT(bp.id) as post_count FROM blog_categories bc LEFT JOIN blog_posts bp ON bc.id=bp.category_id GROUP BY bc.id ORDER BY bc.name")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-tags"></i> Blog Categories</h2>
    <a href="blog.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Blog</a>
</div>
<div class="form-row" style="align-items:start">
    <div class="card" style="margin:0">
        <div class="card-header"><div class="card-title"><?=$edit?'Edit':'Add'?> Category</div>
        <?php if($edit):?><a href="blog_categories.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
        <form method="POST">
            <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
            <div class="form-group"><label class="form-label">Category Name *</label>
                <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($edit['name']??'')?>" required></div>
            <div class="form-group"><label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="<?=htmlspecialchars($edit['description']??'')?>"></div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
        </form>
    </div>
    <div class="card" style="margin:0">
        <div class="card-header"><div class="card-title">All Categories (<?=count($list)?>)</div></div>
        <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-tags"></i><h3>No categories</h3></div>
        <?php else:?><div class="table-responsive"><table>
        <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead>
        <tbody><?php foreach($list as $c):?>
            <tr><td><strong><?=htmlspecialchars($c['name'])?></strong></td>
            <td><code><?=htmlspecialchars($c['slug'])?></code></td>
            <td><span class="badge badge-info"><?=$c['post_count']?></span></td>
            <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$c['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
            <a href="?action=delete&id=<?=$c['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
        <?php endforeach;?></tbody></table></div><?php endif;?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>