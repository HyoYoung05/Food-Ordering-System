<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../models/AdminUser.php';
require_once __DIR__.'/../models/StaffUser.php';
require_once __DIR__.'/../models/AdminOrder.php';

function staffRespond(array $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
function staffBody():array{return json_decode(file_get_contents('php://input'),true)?:[];}
function requireStaff():array{if(empty($_SESSION['staff_user']))staffRespond(['ok'=>false,'message'=>'Staff authentication required.'],401);return $_SESSION['staff_user'];}
function portalDashboard(PDO $db,array $user):array{
    $data=(new AdminOrder($db))->dashboard();
    if(($user['role']??'staff')!=='admin')unset($data['stats']['todayRevenue']);
    return $data;
}

try{
    $db=Database::connection();$action=$_GET['action']??'session';
    switch($action){
        case 'session': staffRespond(['ok'=>true,'user'=>$_SESSION['staff_user']??null]);
        case 'login':
            $data=staffBody();$email=$data['email']??'';$password=$data['password']??'';
            $user=(new AdminUser($db))->authenticate($email,$password) ?? (new StaffUser($db))->authenticate($email,$password);
            if(!$user)throw new RuntimeException('Invalid admin/staff email or password.');
            session_regenerate_id(true);$_SESSION['staff_user']=$user;staffRespond(['ok'=>true,'user'=>$user]);
        case 'logout': unset($_SESSION['staff_user']);staffRespond(['ok'=>true]);
        case 'dashboard': $user=requireStaff();staffRespond(['ok'=>true]+portalDashboard($db,$user));
        case 'status':
            $user=requireStaff();$data=staffBody();(new AdminOrder($db))->updateStatus((int)($data['orderId']??0),$data['status']??'');
            staffRespond(['ok'=>true]+portalDashboard($db,$user));
        default: staffRespond(['ok'=>false,'message'=>'Unknown staff API action.'],404);
    }
}catch(RuntimeException $error){staffRespond(['ok'=>false,'message'=>$error->getMessage()],400);}
catch(Throwable $error){error_log($error->getMessage());staffRespond(['ok'=>false,'message'=>'Database request failed. Check XAMPP MySQL.'],500);}
