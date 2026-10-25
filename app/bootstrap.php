<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Singapore');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
session_start();
$config = require __DIR__.'/../storage/database.php';
$db = new PDO($config['dsn'], $config['user'], $config['password']);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
function query(string $sql, array $params=[]): PDOStatement { global $db; $s=$db->prepare($sql); $s->execute($params); return $s; }
function rows(string $sql,array $params=[]): array {return query($sql,$params)->fetchAll(PDO::FETCH_ASSOC);}
function e($v): string {return htmlspecialchars((string)($v??''), ENT_QUOTES,'UTF-8');}
function user(): ?array {return isset($_SESSION['uid']) ? (query('SELECT * FROM users WHERE id=?',[$_SESSION['uid']])->fetch(PDO::FETCH_ASSOC) ?: null) : null;}
function staff(): bool {return in_array(user()['role']??'', ['Admin','Staff'],true);}
function redirect(string $page): never {header('Location: index.php?page='.$page);exit;}
function flash(string $message): void {$_SESSION['flash']=$message;}
function required(string $key): string {
 $v=trim($_POST[$key]??'');
 if($v==='')throw new RuntimeException('Please complete all required fields.');
 if(strlen($v)>20000)throw new RuntimeException('Please shorten your entry to 20,000 characters.');
 if(in_array($key,['intake_date','record_date','signed_date','start_date','end_date'],true)){
  $date=DateTimeImmutable::createFromFormat('!Y-m-d',$v);
  if(!$date || $date->format('Y-m-d')!==$v)throw new RuntimeException('Enter a valid date.');
 }
 return $v;
}
function choice(string $key,array $options): string {$v=required($key);if(!in_array($v,$options,true))throw new RuntimeException('Invalid selection.');return $v;}
function transaction(callable $work): void {global $db;$db->beginTransaction();try{$work();$db->commit();}catch(Throwable $e){$db->rollBack();throw $e;}}
function notify(int $uid,string $message): void {query('INSERT INTO notifications(user_id,message) VALUES(?,?)',[$uid,$message]);}
function audit(string $action): void {query('INSERT INTO audit(user_id,action) VALUES(?,?)',[user()['id']??null,$action]);}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function csrf(): string {return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function photo(): ?string {
 if(empty($_FILES['photo']['name']))return null;
 $f=$_FILES['photo']; if($f['error']!==UPLOAD_ERR_OK || $f['size']>4*1024*1024)throw new RuntimeException('Choose a photo smaller than 4 MB.');
 $type=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$type]??null;
 if(!$ext || !getimagesize($f['tmp_name']))throw new RuntimeException('Use a JPG, PNG, or WebP image.');
 $name=bin2hex(random_bytes(16)).'.'.$ext;if(!is_dir(__DIR__.'/../public/uploads'))mkdir(__DIR__.'/../public/uploads',0755,true);
 if(!move_uploaded_file($f['tmp_name'],__DIR__.'/../public/uploads/'.$name))throw new RuntimeException('Photo could not be saved.');return 'public/uploads/'.$name;
}
