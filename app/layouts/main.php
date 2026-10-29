<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>FurryHomes · <?=e($titles[$page]??'Welcome')?>
</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<?php if(!user()): ?>
<?php require APP_PATH . '/modules/auth/view.php'; ?>
<?php else: ?>
<aside class="sidebar">
<a class="brand" href="?page=dashboard">♥ FurryHomes</a>
<span class="eyebrow">SHELTER WORKSPACE</span>
<nav aria-label="Main navigation"><?php $nav=['dashboard'=>['◫','Overview'],'pets'=>['♧','Pet profiles'],'applications'=>['▤',staff()?'Applications':'My applications']];if(staff())$nav+=['medical'=>['♡','Medical records'],'adopters'=>['♙','Adopter directory'],'contracts'=>['▧','Agreements & fees'],'fosters'=>['⌂','Foster homes']];if(user()['role']==='Admin')$nav+=['analytics'=>['▥','Shelter insights'],'users'=>['⚙','Team & access']];foreach($nav as $key=>[$icon,$label]):?>
<a class="<?=$page===$key?'active':''?>" href="?page=<?=$key?>">
<span><?=$icon?>
</span><?=e($label)?>
</a><?php endforeach;?>
</nav>
<div class="sidebar-bottom">
<a href="?page=profile"><?=e(user()['name'])?>
<small><?=e(user()['role'])?> account</small>
</a><?php formStart('logout');button('Sign out');?>
</div>
</aside>
<div class="workspace">
<header>
<span>Rescue. Rehabilitate. Rehome.</span>
<span class="account"><?=e(user()['role'])?> <span class="avatar"><?=e(mb_substr(user()['name'],0,1))?>
</span>
</span>
</header>
<main>
<div class="page-heading">
<div>
<span class="eyebrow">FURRYHOMES / <?=e(strtoupper($nav[$page][1]??'PROFILE'))?>
</span>
<h1><?=e($titles[$page])?>
</h1>
<p class="muted"><?= $page==='pets'?'Meet the little lives at the heart of everything we do.':'A simpler way to care for every step of the adoption journey.'?>
</p>
</div>
<span class="date"><?=date('D, d M Y')?>
</span>
</div>
<?php if(!empty($error)):?>
<div role="alert" class="alert error"><?=e($error)?>
</div><?php endif;if(isset($_SESSION['flash'])):?>
<div role="status" class="alert"><?=e($_SESSION['flash'])?>
</div><?php unset($_SESSION['flash']);endif;require APP_PATH . '/modules/' . $route['module'] . '/view.php';?>
</main>
<footer>FurryHomes <span>Made for second chances.</span>
</footer>
</div>
<script src="public/js/app.js">
</script>
<?php endif;?>
</body>
</html>
