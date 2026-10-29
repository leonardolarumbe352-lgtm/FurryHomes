<?php $search=trim($_GET['q']??'');$filter=$_GET['status']??'';
 $pets=pets_search($filter, $search);?>
<form class="filters" method="get">
<input type="hidden" name="page" value="pets">
<label class="sr-only" for="search">Search pets</label>
<input id="search" name="q" placeholder="Search by name or breed…" value="<?=e($search)?>">
<select name="status" aria-label="Filter by status">
<option value="">All statuses</option><?php foreach(['Available','In foster','On hold','Adopted'] as $s):?>
<option <?=$filter===$s?'selected':''?>><?=e($s)?>
</option><?php endforeach;?>
</select>
<button>Search</button>
</form>
<?php if(staff()): $edit=pets_find((int)($_GET['edit'] ?? 0))->fetch(PDO::FETCH_ASSOC)?:[];?>
<details class="panel" <?= $edit?'open':''?>>
<summary><?=$edit?'Edit '.e($edit['name']):'+ Add a rescue pet'?>
</summary><?php formStart('pet');?>
<input type="hidden" name="id" value="<?=e($edit['id']??'')?>">
<div class="form-grid"><?php field('Pet name','name','text',$edit['name']??'');selectField('Species','species',options(['Dog','Cat','Other']),$edit['species']??'');field('Breed','breed','text',$edit['breed']??'');field('Age (e.g. 2 years)','age','text',$edit['age']??'');selectField('Gender','gender',options(['Female','Male','Unknown']),$edit['gender']??'');selectField('Status','status',options(['Available','In foster','On hold','Adopted']),$edit['status']??'Available');field('Intake date','intake_date','date',$edit['intake_date']??date('Y-m-d'));field('Photo · JPG, PNG, WebP · max 4 MB','photo','file','',false);?>
</div><?php area('About this pet','description',$edit['description']??'');button($edit?'Save pet':'Add pet');?>
</details>
<?php endif;?>
<div class="pet-grid"><?php foreach($pets as $p):?>
<article class="pet-card">
<div class="pet-photo <?=strtolower(e($p['species']))?>"><?php if($p['photo']):?>
<img src="<?=e($p['photo'])?>" alt="<?=e($p['name'])?>"><?php else:?>
<span aria-hidden="true"><?=$p['species']==='Cat'?'🐈':($p['species']==='Dog'?'🐕':'🐾')?>
</span><?php endif;?><?=badge($p['status'])?>
</div>
<div class="pet-body">
<div class="row">
<h2><?=e($p['name'])?>
</h2>
<span><?=e($p['gender'])?>
</span>
</div>
<p class="muted"><?=e($p['breed'])?> · <?=e($p['age'])?>
</p>
<p><?=e($p['description'])?>
</p><?php if(staff()):?>
<a class="text-link" href="?page=pets&edit=<?=$p['id']?>">Manage profile →</a><?php elseif(in_array($p['status'],['Available','In foster'])):?>
<a class="button" href="?page=applications&pet=<?=$p['id']?>">Apply to adopt →</a><?php endif;?>
</div>
</article><?php endforeach;?>
</div><?php if(!$pets):?>
<div class="empty">🐾<h2>No pets found</h2>
<p>Try another search<?=staff()?' or add your first rescue pet':''?>.</p>
</div><?php endif;?>
