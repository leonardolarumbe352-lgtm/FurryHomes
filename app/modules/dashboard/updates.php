<?php

header('Content-Type: application/json');
echo json_encode([ 'notifications' => rows('SELECT id,message,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 10', [user()['id']]), 'applications' => rows('SELECT id,status FROM applications WHERE user_id=?', [user()['id']]), ]);
