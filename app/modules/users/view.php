<?php ?>
<section class="panel">
<h2>Account permissions</h2>
<p class="muted">New registrations are adopters. Promote trusted team members here.</p><?php foreach(users_listing() as $u):?>
<div class="history">
<h3><?=e($u['name'])?>
</h3>
<p><?=e($u['email'])?>
</p><?php if($u['id']!==user()['id']):formStart('role');?>
<input type="hidden" name="id" value="<?=$u['id']?>"><?php selectField('Role','role',options(['Admin','Staff','Adopter']),$u['role']);button('Update role');else:echo badge($u['role']);endif;?>
</div><?php endforeach;?>
</section>
