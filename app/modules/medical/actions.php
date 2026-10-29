<?php

function medical_save(): void {
    $values=[required('pet_id'),choice('kind',['Health check','Vaccination','Spayed / neutered','Treatment']),required('record_date'),required('details'),($_POST['next_due']??'')?:null,user()['id']];
    if(!empty($_POST['id'])){
        $values[]=(int)$_POST['id'];
        query('UPDATE medical SET pet_id=?,kind=?,record_date=?,details=?,next_due=?,created_by=? WHERE id=?',$values);
    }
    else query('INSERT INTO medical(pet_id,kind,record_date,details,next_due,created_by) VALUES(?,?,?,?,?,?)',$values);
}
