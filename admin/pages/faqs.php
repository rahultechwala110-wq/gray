<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM faqs WHERE id=$id"); showAlert('Deleted!','danger'); redirect('faqs.php');}
if($action==='toggle'&&$id){$conn->query("UPDATE faqs SET is_active=1-is_active WHERE id=$id"); redirect('faqs.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $q=sanitize($conn,$_POST['question']??''); $a=sanitize($conn,$_POST['answer']??''); $sort=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE faqs SET question='$q',answer='$a',sort_order=$sort WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO faqs (question,answer,sort_order) VALUES ('$q','$a',$sort)"); showAlert('FAQ Added!');}
    redirect('faqs.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM faqs WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT * FROM faqs ORDER BY sort_order,id")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-question-circle"></i> FAQs</h2></div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> FAQ</div>
    <?php if($edit):?><a href="faqs.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-group"><label class="form-label">Question *</label>
            <input type="text" name="question" class="form-control" value="<?=htmlspecialchars($edit['question']??'')?>" required></div>
        <div class="form-group"><label class="form-label">Answer *</label>
            <textarea name="answer" class="form-control" required><?=htmlspecialchars($edit['answer']??'')?></textarea></div>
        <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?> FAQ</button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All FAQs (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-question-circle"></i><h3>No FAQs yet</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Question</th><th>Answer Preview</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$f):?>
        <tr><td><?=$i+1?></td><td><strong><?=htmlspecialchars(substr($f['question'],0,60))?></strong></td>
        <td><?=htmlspecialchars(substr($f['answer'],0,80))?>...</td>
        <td><a href="?action=toggle&id=<?=$f['id']?>"><span class="badge <?=$f['is_active']?'badge-success':'badge-danger'?>"><?=$f['is_active']?'Active':'Inactive'?></span></a></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$f['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$f['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>