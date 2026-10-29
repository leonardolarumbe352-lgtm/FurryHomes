<?php
declare(strict_types=1);

// Exercise production module handlers and views without connecting to hosting.
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec(file_get_contents(APP_PATH . '/database/schema.sqlite.sql'));
$_SESSION = ['csrf'=>'test-csrf'];
$_SERVER['REQUEST_METHOD'] = 'GET';
foreach (['database','auth','http','validation','activity','csrf','uploads','forms','dispatch'] as $component) {
    require APP_PATH . '/shared/' . $component . '.php';
}
set_error_handler(function(int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function verify(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
function run_action(string $action, array $data): void {
    $_POST = $data;
    dispatch_action($action);
}
function fails(callable $work): bool {
    try { $work(); } catch (Throwable $e) { return true; }
    return false;
}
foreach ([1=>'Admin',2=>'Staff',3=>'Adopter',4=>'Adopter'] as $id=>$role) {
    query('INSERT INTO users(id,name,email,password,role) VALUES(?,?,?,?,?)', [$id,$role.' Person',$id.'@example.test',password_hash('test-password', PASSWORD_DEFAULT),$role]);
}
$_SESSION['uid'] = 1;
run_action('pet', ['name'=>'Milo','species'=>'Dog','breed'=>'Mixed','age'=>'2 years','gender'=>'Male','status'=>'Available','description'=>'Friendly companion','intake_date'=>'2026-10-01']);
$petId = (int)$db->lastInsertId();
verify((int)query('SELECT COUNT(*) FROM pets')->fetchColumn()===1, 'Pet module creates a profile');
run_action('foster', ['name'=>'Caregiver','email'=>'foster@example.test','phone'=>'12345','address'=>'Test home']);
$fosterId = (int)$db->lastInsertId();
run_action('placement', ['pet_id'=>$petId,'foster_id'=>$fosterId,'start_date'=>'2026-10-02']);
$placementId = (int)query('SELECT id FROM placements')->fetchColumn();
verify(query('SELECT status FROM pets')->fetchColumn()==='In foster', 'Foster assignment updates pet status');
verify(fails(fn()=>run_action('placement', ['pet_id'=>$petId,'foster_id'=>$fosterId,'start_date'=>'2026-10-03'])), 'Second active placement is rejected');
run_action('end_placement', ['id'=>$placementId,'end_date'=>'2026-10-03']);
verify(query('SELECT status FROM pets')->fetchColumn()==='Available', 'Foster return restores availability');
run_action('medical', ['pet_id'=>$petId,'kind'=>'Vaccination','record_date'=>'2026-10-04','details'=>'Routine vaccine','next_due'=>'2027-10-04']);
verify((int)query('SELECT COUNT(*) FROM medical')->fetchColumn()===1, 'Medical record saved');
$_SESSION['uid'] = 3;
run_action('apply', ['pet_id'=>$petId,'housing'=>'House','experience'=>'Previous pets','reason'=>'Companion']);
$applicationId = (int)query('SELECT id FROM applications WHERE user_id=3')->fetchColumn();
$before = (int)query('SELECT COUNT(*) FROM notifications')->fetchColumn();
verify(fails(fn()=>run_action('apply', ['pet_id'=>$petId,'housing'=>'House','experience'=>'Pets','reason'=>'Companion'])), 'Duplicate application rejected by actual handler');
verify((int)query('SELECT COUNT(*) FROM notifications')->fetchColumn()===$before, 'Failed submission rolls back notifications');
$_SESSION['uid'] = 4;
run_action('apply', ['pet_id'=>$petId,'housing'=>'House','experience'=>'Pets','reason'=>'Companion']);
$_SESSION['uid'] = 2;
run_action('review', ['id'=>$applicationId,'status'=>'Approved','notes'=>'PRIVATE_REVIEW_NOTE']);
verify(query('SELECT status FROM applications WHERE id=?',[$applicationId])->fetchColumn()==='Approved', 'Staff review updates status');
verify(fails(fn()=>run_action('role', ['id'=>3,'role'=>'Admin'])), 'Staff cannot promote users');
run_action('contact', ['user_id'=>3,'note'=>'Called approved adopter']);
$_SESSION['uid'] = 1;
$contract = ['application_id'=>$applicationId,'fee'=>'50.50','payment'=>'Paid','agreement'=>'Signed agreement','ownership_document'=>'Transfer reference','signed_date'=>'2026-10-25'];
verify(fails(fn()=>run_action('contract', array_replace($contract,['fee'=>'-1']))), 'Invalid contract fails');
verify(query('SELECT status FROM pets')->fetchColumn()==='Available', 'Failed contract leaves pet available');
run_action('contract', $contract);
verify(query('SELECT status FROM pets')->fetchColumn()==='Adopted', 'Finalization updates pet status');
verify(query('SELECT status FROM applications WHERE user_id=4')->fetchColumn()==='Rejected', 'Finalization closes competing applications');
verify(fails(fn()=>run_action('review', ['id'=>$applicationId,'status'=>'Rejected'])), 'Finalized application cannot be reviewed again');

$actions = require APP_PATH . '/config/actions.php';
foreach ([null, 1, 2, 3] as $uid) {
    if ($uid) $_SESSION['uid']=$uid; else unset($_SESSION['uid']);
    foreach ($actions as $action=>[$module,$handler,$roles]) {
        if (!role_allowed($roles)) {
            verify(fails(fn()=>dispatch_action($action)), ($uid ? user()['role'] : 'Guest').' denied '.$action);
        }
    }
}
$_SERVER['REQUEST_METHOD']='POST';
$_POST=['csrf'=>'invalid','action'=>'profile'];
verify(str_contains(handle_post('profile') ?? '', 'session expired'), 'Dispatcher rejects invalid CSRF token');
$_SERVER['REQUEST_METHOD']='GET';

$routes = require APP_PATH . '/config/routes.php';
$titles = array_combine(array_keys($routes), array_column($routes,'title'));
foreach ([null, 1, 2, 3] as $uid) {
    if ($uid) $_SESSION['uid']=$uid; else unset($_SESSION['uid']);
    foreach ($routes as $page=>$route) {
        if (!role_allowed($route['roles']) || ($uid && $route['module']==='auth')) continue;
        $_GET = [];
        if ($page==='pets') $_GET['edit']=$petId;
        if ($page==='medical') $_GET['edit']=1;
        if ($page==='applications') $_GET['pet']=$petId;
        $repository = APP_PATH.'/modules/'.$route['module'].'/repository.php';
        if (is_file($repository)) require_once $repository;
        $petOptions = [$petId=>'Milo'];
        $error = null;
        ob_start();
        require APP_PATH . '/layouts/main.php';
        $html = ob_get_clean();
        verify(str_contains($html,'</html>'), ($uid ? user()['role'] : 'Guest').' renders '.$page);
        if ($uid===3 && $page==='applications') {
            verify(!str_contains($html,'PRIVATE_REVIEW_NOTE'), 'Internal review notes stay private');
        }
    }
}
echo "All module integration checks passed.\n";
