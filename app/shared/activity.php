<?php

function notify(int $uid,string $message): void {
    query('INSERT INTO notifications(user_id,message) VALUES(?,?)',[$uid,$message]);
}
function audit(string $action): void {
    query('INSERT INTO audit(user_id,action) VALUES(?,?)',[user()['id']??null,$action]);
}
