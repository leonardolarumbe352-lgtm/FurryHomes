<?php $edit=medical_find((int)($_GET['edit'] ?? 0))->fetch(PDO::FETCH_ASSOC)?:[];?>
<details class="panel" <?=$edit?'open':''?>>
<summary><?=$edit?'Update medical record':'+ Add medical record'?>
</summary><?php formStart('medical');?>
<input type="hidden" name="id" value="<?=e($edit['id']??'')?>">
<div class="form-grid"><?php selectField('Pet','pet_id',$petOptions,$edit['pet_id']??'');selectField('Record type','kind',options(['Health check','Vaccination','Spayed / neutered','Treatment']),$edit['kind']??'');field('Record date','record_date','date',$edit['record_date']??date('Y-m-d'));field('Next due date (optional)','next_due','date',$edit['next_due']??'',false);?>
</div><?php area('Clinical details / vaccine / spay-neuter status','details',$edit['details']??'');button();?>
</details>
<section class="panel">
<h2>Care history</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Pet</th>
<th>Type</th>
<th>Date</th>
<th>Details</th>
<th>Next due</th>
<th>
</th>
</tr>
</thead>
<tbody><?php foreach(medical_history() as $r):?>
<tr>
<td><?=e($r['name'])?>
</td>
<td><?=e($r['kind'])?>
</td>
<td><?=e($r['record_date'])?>
</td>
<td><?=e($r['details'])?>
</td>
<td><?=e($r['next_due']??'—')?>
</td>
<td>
<a href="?page=medical&edit=<?=$r['id']?>">Edit</a>
</td>
</tr><?php endforeach;?>
</tbody>
</table>
</div>
</section>
