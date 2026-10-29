<?php $approved=[];foreach(contracts_approved_applications() as $a)$approved[$a['id']]=$a['name'].' → '.$a['adopter'];?>
<details class="panel">
<summary>+ Finalize an adoption</summary>
<p class="notice">Finalizing records the agreement, transfers the pet to Adopted, ends foster care, and closes other applications. Confirm the signed documents before saving.</p><?php formStart('contract');?>
<div class="form-grid"><?php selectField('Approved application','application_id',$approved);field('Adoption fee','fee','number','0');selectField('Payment status','payment',options(['Unpaid','Paid','Waived']));field('Signed date','signed_date','date',date('Y-m-d'));?>
</div><?php area('Signed agreement / contract reference and terms','agreement');area('Transfer-of-ownership documentation / reference','ownership_document');button('Finalize adoption');?>
</details>
<?php foreach(contracts_listing() as $c):?>
<section class="panel">
<div class="row">
<h2><?=e($c['pet'])?> → <?=e($c['adopter'])?>
</h2><?=badge($c['payment'])?>
</div>
<p>Fee: <?=number_format((float)$c['fee'],2)?> · Signed <?=e($c['signed_date'])?>
</p>
<details>
<summary>Agreement & ownership record</summary>
<h3>Agreement</h3>
<p><?=nl2br(e($c['agreement']))?>
</p>
<h3>Ownership documentation</h3>
<p><?=nl2br(e($c['ownership_document']))?>
</p>
</details><?php formStart('payment');?>
<input type="hidden" name="id" value="<?=$c['id']?>"><?php selectField('Payment status','payment',options(['Unpaid','Paid','Waived']),$c['payment']);button('Update payment');?>
</section><?php endforeach;
