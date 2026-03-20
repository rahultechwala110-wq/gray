<?php
require_once '../includes/db.php';
requireLogin();
$action=$_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($action==='delete'&&$id){$conn->query("DELETE FROM stats_section WHERE id=$id"); showAlert('Deleted!','danger'); redirect('stats.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $n=sanitize($conn,$_POST['number_value']??''); $l=sanitize($conn,$_POST['label']??'');
    $i=sanitize($conn,$_POST['icon']??''); $s=(int)($_POST['sort_order']??0); $eid=(int)($_POST['edit_id']??0);
    if($eid){$conn->query("UPDATE stats_section SET number_value='$n',label='$l',icon='$i',sort_order=$s WHERE id=$eid"); showAlert('Updated!');}
    else{$conn->query("INSERT INTO stats_section (number_value,label,icon,sort_order) VALUES ('$n','$l','$i',$s)"); showAlert('Added!');}
    redirect('stats.php');
}
$edit=null; if($action==='edit'&&$id){$edit=$conn->query("SELECT * FROM stats_section WHERE id=$id")->fetch_assoc();}
require_once '../includes/header.php';
$list=$conn->query("SELECT * FROM stats_section ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
?>
<div class="page-header"><h2><i class="fas fa-chart-bar"></i> Stats / Counters</h2></div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-<?=$edit?'edit':'plus'?>"></i> <?=$edit?'Edit':'Add'?> Stat</div>
    <?php if($edit):?><a href="stats.php" class="btn btn-secondary btn-sm">Cancel</a><?php endif;?></div>
    <form method="POST">
        <?php if($edit):?><input type="hidden" name="edit_id" value="<?=$edit['id']?>"><?php endif;?>
        <div class="form-row-3">
            <div class="form-group"><label class="form-label">Number / Value *</label>
                <input type="text" name="number_value" class="form-control" value="<?=htmlspecialchars($edit['number_value']??'')?>" placeholder="500+" required></div>
            <div class="form-group"><label class="form-label">Label *</label>
                <input type="text" name="label" class="form-control" value="<?=htmlspecialchars($edit['label']??'')?>" placeholder="Happy Clients" required></div>
            <div class="form-group"><label class="form-label">Icon (Font Awesome)</label>
                <input type="text" name="icon" class="form-control" value="<?=htmlspecialchars($edit['icon']??'')?>" placeholder="fas fa-smile"></div>
        </div>
        <div class="form-group" style="max-width:200px"><label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?=$edit['sort_order']??0?>"></div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?=$edit?'Update':'Add'?></button>
    </form>
</div>
<div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-list"></i> All Stats (<?=count($list)?>)</div></div>
    <?php if(empty($list)):?><div class="empty-state"><i class="fas fa-chart-bar"></i><h3>No stats yet</h3></div>
    <?php else:?><div class="table-responsive"><table>
    <thead><tr><th>#</th><th>Icon</th><th>Number</th><th>Label</th><th>Actions</th></tr></thead>
    <tbody><?php foreach($list as $i=>$s):?>
        <tr><td><?=$i+1?></td>
        <td><i class="<?=htmlspecialchars($s['icon'])?>" style="font-size:1.3rem;color:var(--gold)"></i></td>
        <td><strong style="font-size:1.2rem;color:var(--brown-dark)"><?=htmlspecialchars($s['number_value'])?></strong></td>
        <td><?=htmlspecialchars($s['label'])?></td>
        <td style="display:flex;gap:6px;"><a href="?action=edit&id=<?=$s['id']?>" class="btn btn-secondary btn-sm btn-icon"><i class="fas fa-edit"></i></a>
        <a href="?action=delete&id=<?=$s['id']?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td></tr>
    <?php endforeach;?></tbody></table></div><?php endif;?>
</div>
<?php require_once '../includes/footer.php'; ?>