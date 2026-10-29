<?php ?>
<div class="two-cols">
<details class="panel">
<summary>+ Register a foster caregiver</summary><?php formStart('foster');field('Full name','name');field('Email','email','email');field('Phone','phone','tel');field('Housing address','address');button('Register caregiver');?>
</details>
<details class="panel">
<summary>+ Assign a pet</summary><?php $available=[];foreach(fosters_available_pets() as $p)$available[$p['id']]=$p['name'];$fosters=[];foreach(fosters_caregivers() as $f)$fosters[$f['id']]=$f['name'].' · '.$f['address'];formStart('placement');selectField('Pet','pet_id',$available);selectField('Caregiver & location','foster_id',$fosters);field('Placement start','start_date','date',date('Y-m-d'));button('Assign foster home');?>
</details>
</div>
<section class="panel">
<h2>Housing history</h2><?php foreach(fosters_placements() as $pl):?>
<article class="history">
<div class="row">
<h3><?=e($pl['pet'])?> · <?=e($pl['caregiver'])?>
</h3><?=badge($pl['end_date']?'Completed':'Active')?>
</div>
<p><?=e($pl['address'])?> · <?=e($pl['phone'])?>
</p>
<p class="muted"><?=e($pl['start_date'])?> → <?=e($pl['end_date']??'Ongoing')?>
</p><?php if(!$pl['end_date']):formStart('end_placement');?>
<input type="hidden" name="id" value="<?=$pl['id']?>"><?php field('Return date','end_date','date',date('Y-m-d'));button('End placement');endif;?>
</article><?php endforeach;?>
</section>
<section class="panel">
<h2>Registered caregivers</h2><?php foreach(fosters_directory() as $f):?>
<p>
<strong><?=e($f['name'])?>
</strong> · <?=e($f['email'])?> · <?=e($f['phone'])?>
<small><?=e($f['address'])?>
</small>
</p><?php endforeach;?>
</section>
