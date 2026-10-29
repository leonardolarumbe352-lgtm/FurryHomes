<?php ?>
<section class="panel narrow">
<h2>Contact details</h2><?php formStart('profile');field('Full name','name','text',user()['name']);field('Phone','phone','tel',user()['phone'],false);field('Address','address','text',user()['address'],false);button();?>
</section>
