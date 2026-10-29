<?php

function contracts_finalize(): void {
    // Transaction 3: contract, pet ownership status, competing decisions, and notifications.
    transaction(function(){
        $a=query("SELECT a.*,p.name,p.status AS pet_status FROM applications a JOIN pets p ON p.id=a.pet_id WHERE a.id=? AND a.status='Approved'",[required('application_id')])->fetch(PDO::FETCH_ASSOC);
        if(!$a || $a['pet_status']==='Adopted')throw new RuntimeException('Choose an approved application for a pet awaiting adoption.');
        $fee=filter_var(required('fee'),FILTER_VALIDATE_FLOAT);
        if($fee===false || $fee<0)throw new RuntimeException('Enter a valid nonnegative fee.');
        query('INSERT INTO contracts(application_id,fee,payment,agreement,ownership_document,signed_date) VALUES(?,?,?,?,?,?)',[$a['id'],$fee,choice('payment',['Unpaid','Paid','Waived']),required('agreement'),required('ownership_document'),required('signed_date')]);
        query("UPDATE pets SET status='Adopted' WHERE id=?",[$a['pet_id']]);
        query('UPDATE placements SET end_date=? WHERE pet_id=? AND end_date IS NULL',[required('signed_date'),$a['pet_id']]);
        foreach(rows("SELECT * FROM applications WHERE pet_id=? AND id<>? AND status<>'Rejected'",[$a['pet_id'],$a['id']]) as $other){
            query("UPDATE applications SET status='Rejected', updated_at=CURRENT_TIMESTAMP WHERE id=?",[$other['id']]);
            notify((int)$other['user_id'],'Your application for '.$a['name'].' has closed because the pet was adopted.');
        }
        notify((int)$a['user_id'],'Adoption finalized for '.$a['name'].'. Welcome home!');
        audit('Finalized adoption #'.$a['id']);
    });
}
function contracts_update_payment(): void {
    query('UPDATE contracts SET payment=? WHERE id=?',[choice('payment',['Unpaid','Paid','Waived']),required('id')]);
    audit('Updated contract payment #'.required('id'));
}
