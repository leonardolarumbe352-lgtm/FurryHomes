<?php

function photo(): ?string {
    if(empty($_FILES['photo']['name']))return null;
    $f=$_FILES['photo'];
    if($f['error']!==UPLOAD_ERR_OK || $f['size']>4*1024*1024)throw new RuntimeException('Choose a photo smaller than 4 MB.');
    $type=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$type]??null;
    if(!$ext || !getimagesize($f['tmp_name']))throw new RuntimeException('Use a JPG, PNG, or WebP image.');
    $name=bin2hex(random_bytes(16)).'.'.$ext;
    if(!is_dir(BASE_PATH.'/public/uploads'))mkdir(BASE_PATH.'/public/uploads',0755,true);
    if(!move_uploaded_file($f['tmp_name'],BASE_PATH.'/public/uploads/'.$name))throw new RuntimeException('Photo could not be saved.');
    return 'public/uploads/'.$name;
}
