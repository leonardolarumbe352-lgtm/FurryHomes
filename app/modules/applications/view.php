<?php if(!staff() && isset($_GET['pet'])):$pet=applications_available_pet((int)($_GET['pet'] ?? 0))->fetch(PDO::FETCH_ASSOC);if($pet):?>
<section class="panel">
<h2>Apply to adopt <?=e($pet['name'])?>
</h2>
<p class="muted">Tell us about the home you can offer. Our shelter team will review your application.</p><?php formStart('apply');?>
<input type="hidden" name="pet_id" value="<?=$pet['id']?>"><?php area('Your living situation & permission to keep pets','housing');area('Your experience caring for animals','experience');area('Why is this pet a good match for you?','reason');button('Submit application');?>
</section>
<?php endif;endif;
 $applications=applications_listing();
 if(!$applications)echo '<div class="empty">
<h2>No applications yet</h2>
<a href="?page=pets">Meet pets looking for a home →</a>
</div>';
 foreach($applications as $a):?>
<section class="panel">
<div class="row">
<h2><?=e($a['pet'])?> <small>· #<?=$a['id']?>
</small>
</h2>
<span data-application="<?=$a['id']?>"><?=badge($a['status'])?>
</span>
</div>
<p class="muted">Submitted <?=e($a['created_at'])?><?=staff()?' · '.e($a['applicant']).' · '.e($a['email']).' · '.e($a['phone']):''?>
</p>
<details>
<summary>Application details</summary>
<dl><?php foreach(['housing'=>'Living situation','experience'=>'Animal care experience','reason'=>'Adoption motivation'] as $k=>$label):?>
<dt><?=e($label)?>
</dt>
<dd><?=nl2br(e($a[$k]))?>
</dd><?php endforeach;?>
</dl>
</details><?php if(staff()):formStart('review');?>
<input type="hidden" name="id" value="<?=$a['id']?>">
<div class="form-grid"><?php selectField('Decision','status',options(['Pending','Approved','Rejected']),$a['status']);?>
<label>Internal review notes<textarea name="notes" rows="3"><?=e($a['notes'])?>
</textarea>
</label>
</div><?php button('Save review');endif;?>
</section><?php endforeach;
