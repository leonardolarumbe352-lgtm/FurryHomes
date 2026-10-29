<?php

function users_update_role(): void {
    if(user()['role']!=='Admin')throw new RuntimeException('Admin access is required.');
    if((int)required('id')===(int)user()['id'])throw new RuntimeException('You cannot change your own role.');
    query('UPDATE users SET role=? WHERE id=?',[choice('role',['Admin','Staff','Adopter']),required('id')]);
    audit('Changed user role #'.required('id'));
}
