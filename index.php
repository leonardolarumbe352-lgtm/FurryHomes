<?php
require __DIR__.'/app/bootstrap.php';
$page=$_GET['page']??(user()?'dashboard':'login');
$allowed=['login','register','dashboard','pets','applications','medical','adopters','contracts','fosters','analytics','users','profile'];
if(!in_array($page,$allowed,true))$page='dashboard';
require __DIR__.'/app/actions.php';
if(!user() && !in_array($page,['login','register'],true))redirect('login');
if(user() && in_array($page,['login','register'],true))redirect('dashboard');
$restricted=['medical','adopters','contracts','fosters'];
if((in_array($page,$restricted,true) && !staff()) || (in_array($page,['analytics','users'],true) && user()['role']!=='Admin')){http_response_code(403);exit('Access denied.');}
if(isset($_GET['poll']) && user()){
 header('Content-Type: application/json');echo json_encode(['notifications'=>rows('SELECT id,message,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 10',[user()['id']]),'applications'=>rows('SELECT id,status FROM applications WHERE user_id=?',[user()['id']])]);exit;
}
if($page==='analytics' && ($_GET['export']??'')==='csv'){
 header('Content-Type: text/csv');header('Content-Disposition: attachment; filename="furryhomes-report.csv"');$out=fopen('php://output','w');fputcsv($out,['Month','Intakes','Adoptions']);foreach(rows("SELECT month,SUM(intakes) AS intakes,SUM(adoptions) AS adoptions FROM (SELECT substr(intake_date,1,7) month,COUNT(*) intakes,0 adoptions FROM pets GROUP BY month UNION ALL SELECT substr(signed_date,1,7) month,0 intakes,COUNT(*) adoptions FROM contracts GROUP BY month) GROUP BY month ORDER BY month DESC") as $r)fputcsv($out,$r);fclose($out);exit;
}
function field(string $label,string $name,string $type='text',$value='',bool $mandatory=true): void {echo '<label>'.e($label).'<input name="'.e($name).'" type="'.e($type).'" value="'.e($value).'" '.($mandatory?'required':'').' '.($type==='number'?'min="0" step="0.01"':'').($type==='file'?' accept="image/jpeg,image/png,image/webp"':'').'></label>';}
function area(string $label,string $name,$value=''): void {echo '<label>'.e($label).'<textarea name="'.e($name).'" required rows="3">'.e($value).'</textarea></label>';}
function selectField(string $label,string $name,array $options,$selected=''): void {echo '<label>'.e($label).'<select name="'.e($name).'" required><option value="">Choose…</option>';foreach($options as $k=>$v)echo '<option value="'.e($k).'" '.((string)$k===(string)$selected?'selected':'').'>'.e($v).'</option>';echo '</select></label>';}
function options(array $values): array {return array_combine($values,$values);}
function formStart(string $action): void {echo '<form method="post" enctype="multipart/form-data">'.csrf().'<input type="hidden" name="action" value="'.e($action).'">';}
function button(string $label='Save changes'): void {echo '<button type="submit">'.e($label).'</button></form>';}
function badge(string $s): string {return '<span class="badge '.strtolower(str_replace(' ','-',$s)).'">'.e($s).'</span>';}
$titles=['dashboard'=>'A little care. A new beginning.','pets'=>'Find their next chapter','applications'=>'Adoption applications','medical'=>'Health & wellbeing','adopters'=>'Adopter directory','contracts'=>'Adoption agreements','fosters'=>'Foster homes','analytics'=>'Shelter insights','users'=>'Team & access','profile'=>'Your profile'];
$petOptions=[];if(user())foreach(rows('SELECT id,name FROM pets ORDER BY name') as $p)$petOptions[$p['id']]=$p['name'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>FurryHomes · <?=e($titles[$page]??'Welcome')?></title><link rel="stylesheet" href="public/style.css"></head><body>
<?php if(!user()): ?>
<main class="auth"><section class="auth-story"><a class="brand" href="index.php">♥ FurryHomes</a><div><span class="eyebrow">SMALL PAWS. BIG POSSIBILITIES.</span><h1>Every pet deserves<br>a place to belong.</h1><p>Bringing rescue pets and caring people together, one happy home at a time.</p><div class="pet-art" aria-hidden="true">🐕 🐈</div></div><small>Care starts with connection.</small></section><section class="auth-form"><div><span class="eyebrow">WELCOME TO FURRYHOMES</span><h2><?=$page==='register'?'Make room for a new beginning.':'Good to see you again.'?></h2><p class="muted"><?=$page==='register'?'Create your account to get started.':'Sign in to your shelter community.'?></p>
<?php if(!empty($error)):?><div role="alert" class="alert error"><?=e($error)?></div><?php endif;?>
<?php formStart($page); if($page==='register'){field('Full name','name');}field('Email address','email','email');field('Password'.($page==='register'?' (at least 10 characters)':''),'password','password');if($page==='register'){field('Phone number','phone','tel','',false);field('Address','address','text','',false);if(!query('SELECT COUNT(*) FROM users')->fetchColumn())echo '<p class="notice">First-time setup: this first account will be the shelter administrator.</p>';}button($page==='register'?'Create account':'Sign in'); ?>
<p class="muted"><?=$page==='register'?'Already registered?':'New here?'?> <a href="?page=<?=$page==='register'?'login':'register'?>"><?=$page==='register'?'Sign in':'Create an account'?></a></p></div></section></main>
<?php else: ?>
<aside class="sidebar"><a class="brand" href="?page=dashboard">♥ FurryHomes</a><span class="eyebrow">SHELTER WORKSPACE</span><nav aria-label="Main navigation"><?php $nav=['dashboard'=>['◫','Overview'],'pets'=>['♧','Pet profiles'],'applications'=>['▤',staff()?'Applications':'My applications']];if(staff())$nav+=['medical'=>['♡','Medical records'],'adopters'=>['♙','Adopter directory'],'contracts'=>['▧','Agreements & fees'],'fosters'=>['⌂','Foster homes']];if(user()['role']==='Admin')$nav+=['analytics'=>['▥','Shelter insights'],'users'=>['⚙','Team & access']];foreach($nav as $key=>[$icon,$label]):?><a class="<?=$page===$key?'active':''?>" href="?page=<?=$key?>"><span><?=$icon?></span><?=e($label)?></a><?php endforeach;?></nav><div class="sidebar-bottom"><a href="?page=profile"><?=e(user()['name'])?><small><?=e(user()['role'])?> account</small></a><?php formStart('logout');button('Sign out');?></div></aside>
<div class="workspace"><header><span>Rescue. Rehabilitate. Rehome.</span><span class="account"><?=e(user()['role'])?> <span class="avatar"><?=e(mb_substr(user()['name'],0,1))?></span></span></header><main><div class="page-heading"><div><span class="eyebrow">FURRYHOMES / <?=e(strtoupper($nav[$page][1]??'PROFILE'))?></span><h1><?=e($titles[$page])?></h1><p class="muted"><?= $page==='pets'?'Meet the little lives at the heart of everything we do.':'A simpler way to care for every step of the adoption journey.'?></p></div><span class="date"><?=date('D, d M Y')?></span></div>
<?php if(!empty($error)):?><div role="alert" class="alert error"><?=e($error)?></div><?php endif;if(isset($_SESSION['flash'])):?><div role="status" class="alert"><?=e($_SESSION['flash'])?></div><?php unset($_SESSION['flash']);endif;require __DIR__.'/app/views.php';?>
</main><footer>FurryHomes <span>Made for second chances.</span></footer></div><script src="public/app.js"></script>
<?php endif;?></body></html>
