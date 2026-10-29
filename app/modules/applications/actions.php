<?php

function applications_submit(): void {
    if(user()['role']!=='Adopter')throw new RuntimeException('Adopter accounts can submit applications.');
    // Transaction 1: application, notification, and audit must succeed together.
    transaction(function(){
        $pet=query("SELECT * FROM pets WHERE id=? AND status IN ('Available','In foster')",[required('pet_id')])->fetch(PDO::FETCH_ASSOC);
        if(!$pet)throw new RuntimeException('This pet is not accepting applications.');
        query('INSERT INTO applications(pet_id,user_id,housing,experience,reason) VALUES(?,?,?,?,?)',[$pet['id'],user()['id'],required('housing'),required('experience'),required('reason')]);
        notify((int)user()['id'],'Your application for '.$pet['name'].' was submitted.');
        audit('Submitted application for pet #'.$pet['id']);
    });
}
function applications_review(): void {
    // Transaction 2: review decision, applicant notification, and audit are atomic.
    transaction(function(){
        $a=query('SELECT a.*,p.name FROM applications a JOIN pets p ON p.id=a.pet_id WHERE a.id=?',[required('id')])->fetch(PDO::FETCH_ASSOC);
        if(!$a)throw new RuntimeException('Application not found.');
        if(query('SELECT COUNT(*) FROM contracts WHERE application_id=?',[$a['id']])->fetchColumn())throw new RuntimeException('A finalized adoption cannot be reviewed again.');
        $status=choice('status',['Pending','Approved','Rejected']);
        if($status==='Approved' && query("SELECT status FROM pets WHERE id=?",[$a['pet_id']])->fetchColumn()==='Adopted')throw new RuntimeException('This pet is already adopted.');
        query('UPDATE applications SET status=?,notes=?,updated_at=CURRENT_TIMESTAMP WHERE id=?',[$status,trim($_POST['notes']??''),$a['id']]);
        if($status!==$a['status'])notify((int)$a['user_id'],'Application for '.$a['name'].': '.$status.'.');
        audit('Reviewed application #'.$a['id'].' as '.$status);
    });
}
