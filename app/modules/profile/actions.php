<?php

function profile_update(): void {
    query('UPDATE users SET name=?,phone=?,address=? WHERE id=?',[required('name'),trim($_POST['phone']??''),trim($_POST['address']??''),user()['id']]);
}
