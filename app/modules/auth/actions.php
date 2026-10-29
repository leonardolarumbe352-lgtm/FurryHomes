<?php

function auth_authenticate(string $action): void {
    $email=strtolower(required('email'));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter a valid email address.');
    $password=required('password');
    if($action==='register') {
        if(strlen($password)<10)throw new RuntimeException('Use a password of at least 10 characters.');
        transaction(function()use($email,$password){
            $first=(int)query('SELECT COUNT(*) FROM users')->fetchColumn()===0;
            query('INSERT INTO users(name,email,password,role,phone,address) VALUES(?,?,?,?,?,?)',[required('name'),$email,password_hash($password,PASSWORD_DEFAULT),$first?'Admin':'Adopter',trim($_POST['phone']??''),trim($_POST['address']??'')]);
            global $db;
            $_SESSION['uid']=(int)$db->lastInsertId();
        }
        );
    }
    else {
        $ip=$_SERVER['REMOTE_ADDR']??'local';
        if(query("SELECT COUNT(*) FROM login_attempts WHERE (email=? OR ip=?) AND created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)",[$email,$ip])->fetchColumn()>=10)throw new RuntimeException('Too many attempts. Try again in 15 minutes.');
        $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch(PDO::FETCH_ASSOC);
        if(!$u || !password_verify($password,$u['password'])){
            query('INSERT INTO login_attempts(email,ip) VALUES(?,?)',[$email,$ip]);
            throw new RuntimeException('Email or password is incorrect.');
        }
        $_SESSION['uid']=$u['id'];
        query('DELETE FROM login_attempts WHERE email=?',[$email]);
    }
    session_regenerate_id(true);
    redirect('dashboard');
}
function auth_logout(): void {
    $_SESSION = [];
    session_destroy();
    redirect('login');
}
