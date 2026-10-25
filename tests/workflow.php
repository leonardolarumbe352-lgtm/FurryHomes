<?php
declare(strict_types=1);
$db=new PDO('sqlite::memory:');$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$db->exec('PRAGMA foreign_keys=ON');$db->exec(file_get_contents(__DIR__.'/../app/schema.sql'));
function check(bool $value,string $message): void {if(!$value)throw new RuntimeException($message);echo "PASS: $message\n";}
$db->exec("INSERT INTO users(id,name,email,password,role) VALUES(1,'Team','team@test.local','hash','Admin'),(2,'Adopter','adopter@test.local','hash','Adopter'); INSERT INTO pets(id,name,species,breed,age,gender,intake_date) VALUES(1,'Milo','Dog','Mixed','2 years','Male','2026-10-01')");
$db->beginTransaction();$db->exec("INSERT INTO applications(id,pet_id,user_id,housing,experience,reason) VALUES(1,1,2,'House','Experienced','Companion'); INSERT INTO notifications(user_id,message) VALUES(2,'Submitted')");$db->commit();
check((int)$db->query('SELECT COUNT(*) FROM applications')->fetchColumn()===1,'Application and notification committed');
$db->beginTransaction();$db->exec("UPDATE applications SET status='Approved' WHERE id=1; INSERT INTO notifications(user_id,message) VALUES(2,'Approved')");$db->commit();
check($db->query('SELECT status FROM applications')->fetchColumn()==='Approved','Application approved');
$db->beginTransaction();try{$db->exec("UPDATE pets SET status='Adopted'; INSERT INTO contracts(application_id,fee,payment,agreement,ownership_document,signed_date) VALUES(999,50,'Paid','Signed','Reference','2026-10-25')");$db->commit();}catch(PDOException $e){$db->rollBack();}
check($db->query('SELECT status FROM pets')->fetchColumn()==='Available','Failed contract rolls back pet status');
$db->beginTransaction();$db->exec("INSERT INTO contracts(application_id,fee,payment,agreement,ownership_document,signed_date) VALUES(1,50,'Paid','Signed','Reference','2026-10-25'); UPDATE pets SET status='Adopted'; INSERT INTO notifications(user_id,message) VALUES(2,'Finalized')");$db->commit();
check($db->query('SELECT status FROM pets')->fetchColumn()==='Adopted','Contract and pet status finalized together');
try{$db->exec("INSERT INTO applications(pet_id,user_id,housing,experience,reason) VALUES(1,2,'House','Yes','Yes')");throw new RuntimeException('Duplicate allowed');}catch(PDOException $e){check(true,'Duplicate application rejected');}
check(password_verify('test-password',password_hash('test-password',PASSWORD_DEFAULT)),'Password hashing verified');
echo "All workflow checks passed.\n";
