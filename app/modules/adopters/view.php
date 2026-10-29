<?php $search=trim($_GET['q']??'');?>
<form class="filters">
<input type="hidden" name="page" value="adopters">
<input aria-label="Search approved adopters" name="q" placeholder="Search name, email, or phone…" value="<?=e($search)?>">
<button>Search</button>
</form>
<?php $adopters=adopters_search($search);if(!$adopters)echo '<div class="empty">Approved adopters will appear here after application review.</div>';foreach($adopters as $a):?>
<section class="panel">
<h2><?=e($a['name'])?>
</h2>
<p><?=e($a['email'])?> · <?=e($a['phone'])?>
</p>
<p class="muted"><?=e($a['address'])?>
</p>
<details>
<summary>Contact history</summary><?php foreach(adopters_contacts($a) as $c):?>
<p><?=nl2br(e($c['note']))?>
<small><?=e($c['author'])?> · <?=e($c['created_at'])?>
</small>
</p><?php endforeach;formStart('contact');?>
<input type="hidden" name="user_id" value="<?=$a['id']?>"><?php area('Add contact note','note');button('Record contact');?>
</details>
</section><?php endforeach;
