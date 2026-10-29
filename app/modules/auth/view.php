<main class="auth">
<section class="auth-story">
<a class="brand" href="index.php">♥ FurryHomes</a>
<div>
<span class="eyebrow">SMALL PAWS. BIG POSSIBILITIES.</span>
<h1>Every pet deserves<br>a place to belong.</h1>
<p>Bringing rescue pets and caring people together, one happy home at a time.</p>
<div class="pet-art" aria-hidden="true">🐕 🐈</div>
</div>
<small>Care starts with connection.</small>
</section>
<section class="auth-form">
<div>
<span class="eyebrow">WELCOME TO FURRYHOMES</span>
<h2><?=$page==='register'?'Make room for a new beginning.':'Good to see you again.'?>
</h2>
<p class="muted"><?=$page==='register'?'Create your account to get started.':'Sign in to your shelter community.'?>
</p>
<?php if(!empty($error)):?>
<div role="alert" class="alert error"><?=e($error)?>
</div><?php endif;?>
<?php formStart($page); if($page==='register'){field('Full name','name');}field('Email address','email','email');field('Password'.($page==='register'?' (at least 10 characters)':''),'password','password');if($page==='register'){field('Phone number','phone','tel','',false);field('Address','address','text','',false);if(!auth_account_count()->fetchColumn())echo '<p class="notice">First-time setup: this first account will be the shelter administrator.</p>';}button($page==='register'?'Create account':'Sign in'); ?>
<p class="muted"><?=$page==='register'?'Already registered?':'New here?'?> <a href="?page=<?=$page==='register'?'login':'register'?>"><?=$page==='register'?'Sign in':'Create an account'?>
</a>
</p>
</div>
</section>
</main>
