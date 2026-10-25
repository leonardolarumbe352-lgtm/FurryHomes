<?php
if($_SERVER['REQUEST_METHOD']==='POST') {
 try {
  if(!hash_equals($_SESSION['csrf'],$_POST['csrf']??''))throw new RuntimeException('Your session expired. Refresh and try again.');
  $action=required('action');
  if($action==='register' || $action==='login') {
   $email=strtolower(required('email'));if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Enter a valid email address.');
   $password=required('password');
   if($action==='register') {
    if(strlen($password)<10)throw new RuntimeException('Use a password of at least 10 characters.');
    transaction(function()use($email,$password){
     $first=(int)query('SELECT COUNT(*) FROM users')->fetchColumn()===0;
     query('INSERT INTO users(name,email,password,role,phone,address) VALUES(?,?,?,?,?,?)',[required('name'),$email,password_hash($password,PASSWORD_DEFAULT),$first?'Admin':'Adopter',trim($_POST['phone']??''),trim($_POST['address']??'')]);
     global $db;$_SESSION['uid']=(int)$db->lastInsertId();
    });
   } else {
    $ip=$_SERVER['REMOTE_ADDR']??'local';
    if(query("SELECT COUNT(*) FROM login_attempts WHERE (email=? OR ip=?) AND created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)",[$email,$ip])->fetchColumn()>=10)throw new RuntimeException('Too many attempts. Try again in 15 minutes.');
    $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch(PDO::FETCH_ASSOC);
    if(!$u || !password_verify($password,$u['password'])){query('INSERT INTO login_attempts(email,ip) VALUES(?,?)',[$email,$ip]);throw new RuntimeException('Email or password is incorrect.');}
    $_SESSION['uid']=$u['id'];query('DELETE FROM login_attempts WHERE email=?',[$email]);
   }
   session_regenerate_id(true);redirect('dashboard');
  }
  if(!user())throw new RuntimeException('Please sign in first.');
  if($action==='logout'){$_SESSION=[];session_destroy();redirect('login');}
  if($action==='profile'){
   query('UPDATE users SET name=?,phone=?,address=? WHERE id=?',[required('name'),trim($_POST['phone']??''),trim($_POST['address']??''),user()['id']]);
  } elseif($action==='apply') {
   if(user()['role']!=='Adopter')throw new RuntimeException('Adopter accounts can submit applications.');
   // Transaction 1: application, notification, and audit must succeed together.
   transaction(function(){
    $pet=query("SELECT * FROM pets WHERE id=? AND status IN ('Available','In foster')",[required('pet_id')])->fetch(PDO::FETCH_ASSOC);if(!$pet)throw new RuntimeException('This pet is not accepting applications.');
    query('INSERT INTO applications(pet_id,user_id,housing,experience,reason) VALUES(?,?,?,?,?)',[$pet['id'],user()['id'],required('housing'),required('experience'),required('reason')]);
    notify((int)user()['id'],'Your application for '.$pet['name'].' was submitted.');audit('Submitted application for pet #'.$pet['id']);
   });
  } else {
   if(!staff())throw new RuntimeException('Staff access is required.');
   if($action==='pet'){
    $id=(int)($_POST['id']??0);$p=$id?query('SELECT * FROM pets WHERE id=?',[$id])->fetch(PDO::FETCH_ASSOC):null;
    $status=choice('status',['Available','In foster','Adopted','On hold']);
    if($status==='Adopted' && (!$p || $p['status']!=='Adopted'))throw new RuntimeException('Finalize an adoption contract to mark a pet adopted.');
    if($p && $p['status']==='Adopted' && $status!=='Adopted')throw new RuntimeException('An adopted pet has a finalized contract.');
    $active=$id?query('SELECT COUNT(*) FROM placements WHERE pet_id=? AND end_date IS NULL',[$id])->fetchColumn():0;
    if(($status==='In foster') !== ((int)$active>0))throw new RuntimeException('Manage foster status in the Foster homes module.');
    $values=[required('name'),choice('species',['Dog','Cat','Other']),required('breed'),required('age'),choice('gender',['Female','Male','Unknown']),$status,required('description'),photo()??($p['photo']??null),required('intake_date')];
    if($id){$values[]=$id;query('UPDATE pets SET name=?,species=?,breed=?,age=?,gender=?,status=?,description=?,photo=?,intake_date=? WHERE id=?',$values);}else query('INSERT INTO pets(name,species,breed,age,gender,status,description,photo,intake_date) VALUES(?,?,?,?,?,?,?,?,?)',$values);
   } elseif($action==='review'){
    // Transaction 2: review decision, applicant notification, and audit are atomic.
    transaction(function(){
     $a=query('SELECT a.*,p.name FROM applications a JOIN pets p ON p.id=a.pet_id WHERE a.id=?',[required('id')])->fetch(PDO::FETCH_ASSOC);if(!$a)throw new RuntimeException('Application not found.');
     if(query('SELECT COUNT(*) FROM contracts WHERE application_id=?',[$a['id']])->fetchColumn())throw new RuntimeException('A finalized adoption cannot be reviewed again.');
     $status=choice('status',['Pending','Approved','Rejected']);
     if($status==='Approved' && query("SELECT status FROM pets WHERE id=?",[$a['pet_id']])->fetchColumn()==='Adopted')throw new RuntimeException('This pet is already adopted.');
     query('UPDATE applications SET status=?,notes=?,updated_at=CURRENT_TIMESTAMP WHERE id=?',[$status,trim($_POST['notes']??''),$a['id']]);
     if($status!==$a['status'])notify((int)$a['user_id'],'Application for '.$a['name'].': '.$status.'.');audit('Reviewed application #'.$a['id'].' as '.$status);
    });
   } elseif($action==='medical'){
    $values=[required('pet_id'),choice('kind',['Health check','Vaccination','Spayed / neutered','Treatment']),required('record_date'),required('details'),($_POST['next_due']??'')?:null,user()['id']];
    if(!empty($_POST['id'])){$values[]=(int)$_POST['id'];query('UPDATE medical SET pet_id=?,kind=?,record_date=?,details=?,next_due=?,created_by=? WHERE id=?',$values);}else query('INSERT INTO medical(pet_id,kind,record_date,details,next_due,created_by) VALUES(?,?,?,?,?,?)',$values);
   } elseif($action==='contact'){
    if(!query("SELECT COUNT(*) FROM applications WHERE user_id=? AND status='Approved'",[required('user_id')])->fetchColumn())throw new RuntimeException('Choose an approved adopter.');
    query('INSERT INTO contacts(user_id,note,created_by) VALUES(?,?,?)',[required('user_id'),required('note'),user()['id']]);
   } elseif($action==='contract'){
    // Transaction 3: contract, pet ownership status, competing decisions, and notifications.
    transaction(function(){
     $a=query("SELECT a.*,p.name,p.status AS pet_status FROM applications a JOIN pets p ON p.id=a.pet_id WHERE a.id=? AND a.status='Approved'",[required('application_id')])->fetch(PDO::FETCH_ASSOC);
     if(!$a || $a['pet_status']==='Adopted')throw new RuntimeException('Choose an approved application for a pet awaiting adoption.');
     $fee=filter_var(required('fee'),FILTER_VALIDATE_FLOAT);if($fee===false || $fee<0)throw new RuntimeException('Enter a valid nonnegative fee.');
     query('INSERT INTO contracts(application_id,fee,payment,agreement,ownership_document,signed_date) VALUES(?,?,?,?,?,?)',[$a['id'],$fee,choice('payment',['Unpaid','Paid','Waived']),required('agreement'),required('ownership_document'),required('signed_date')]);
     query("UPDATE pets SET status='Adopted' WHERE id=?",[$a['pet_id']]);
     query('UPDATE placements SET end_date=? WHERE pet_id=? AND end_date IS NULL',[required('signed_date'),$a['pet_id']]);
     foreach(rows("SELECT * FROM applications WHERE pet_id=? AND id<>? AND status<>'Rejected'",[$a['pet_id'],$a['id']]) as $other){query("UPDATE applications SET status='Rejected', updated_at=CURRENT_TIMESTAMP WHERE id=?",[$other['id']]);notify((int)$other['user_id'],'Your application for '.$a['name'].' has closed because the pet was adopted.');}
     notify((int)$a['user_id'],'Adoption finalized for '.$a['name'].'. Welcome home!');audit('Finalized adoption #'.$a['id']);
    });
   } elseif($action==='payment'){
    query('UPDATE contracts SET payment=? WHERE id=?',[choice('payment',['Unpaid','Paid','Waived']),required('id')]);audit('Updated contract payment #'.required('id'));
   } elseif($action==='foster'){
    query('INSERT INTO fosters(name,email,phone,address) VALUES(?,?,?,?)',[required('name'),required('email'),required('phone'),required('address')]);
   } elseif($action==='placement'){
    // Transaction 4: housing assignment and pet availability change together.
    transaction(function(){
     $id=required('pet_id');$status=query('SELECT status FROM pets WHERE id=?',[$id])->fetchColumn();if(!in_array($status,['Available','On hold'],true))throw new RuntimeException('Choose an available pet without an active placement.');
     query('INSERT INTO placements(pet_id,foster_id,start_date) VALUES(?,?,?)',[$id,required('foster_id'),required('start_date')]);query("UPDATE pets SET status='In foster' WHERE id=?",[$id]);audit('Placed pet #'.$id.' in foster care');
    });
   } elseif($action==='end_placement'){
    transaction(function(){
     $p=query('SELECT * FROM placements WHERE id=? AND end_date IS NULL',[required('id')])->fetch(PDO::FETCH_ASSOC);if(!$p)throw new RuntimeException('Placement already closed.');
     $end=required('end_date');if($end<$p['start_date'])throw new RuntimeException('End date must follow the start date.');
     query('UPDATE placements SET end_date=? WHERE id=?',[$end,$p['id']]);query("UPDATE pets SET status='Available' WHERE id=? AND status='In foster'",[$p['pet_id']]);
    });
   } elseif($action==='role'){
    if(user()['role']!=='Admin')throw new RuntimeException('Admin access is required.');
    if((int)required('id')===(int)user()['id'])throw new RuntimeException('You cannot change your own role.');
    query('UPDATE users SET role=? WHERE id=?',[choice('role',['Admin','Staff','Adopter']),required('id')]);audit('Changed user role #'.required('id'));
   }else throw new RuntimeException('Unknown action.');
  }
  flash('Saved successfully.');redirect($page);
 }catch(Throwable $err){
  $error=$err instanceof PDOException ? (str_contains($err->getMessage(),'UNIQUE')?'That record already exists. You may have already submitted this application.':'The record could not be saved. Check your selections and try again.') : $err->getMessage();
 }
}
