<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../models/AdminUser.php';
require_once __DIR__.'/../models/StaffUser.php';
require_once __DIR__.'/../models/AdminOrder.php';
require_once __DIR__.'/../models/AdminProduct.php';
require_once __DIR__.'/../services/CloudinaryUploader.php';
require_once __DIR__.'/../services/EmailService.php';

function staffRespond(array $data,int $status=200):never{http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
function staffBody():array{return json_decode(file_get_contents('php://input'),true)?:[];}
function requireStaff():array{if(empty($_SESSION['staff_user']))staffRespond(['ok'=>false,'message'=>'Staff authentication required.'],401);return $_SESSION['staff_user'];}
function requireAdmin():array{$user=requireStaff();if(($user['role']??'')!=='admin')staffRespond(['ok'=>false,'message'=>'Administrator access required.'],403);return $user;}
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
        case 'revenue': requireAdmin();staffRespond(['ok'=>true,'revenue'=>(new AdminOrder($db))->revenue($_GET['period']??'daily')]);
        case 'order-details': requireStaff();staffRespond(['ok'=>true,'details'=>(new AdminOrder($db))->details((int)($_GET['orderId']??0))]);
        case 'products': requireStaff();staffRespond(['ok'=>true,'products'=>(new AdminProduct($db))->all()]);
        case 'save-product':
            $user=requireStaff();$data=$_POST?:staffBody();
            if(isset($_FILES['image'])&&$_FILES['image']['error']!==UPLOAD_ERR_NO_FILE){
                if($_FILES['image']['error']!==UPLOAD_ERR_OK)throw new RuntimeException('The food image could not be uploaded.');
                if($_FILES['image']['size']>5*1024*1024)throw new RuntimeException('Food images must be 5 MB or smaller.');
                $mime=(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if(!isset($extensions[$mime]))throw new RuntimeException('Use a JPG, PNG, or WebP food image.');
                $data['imagePath']=(new CloudinaryUploader())->upload($_FILES['image']['tmp_name']);
            }
            $data['isAvailable']=filter_var($data['isAvailable']??false,FILTER_VALIDATE_BOOLEAN);staffRespond(['ok'=>true,'product'=>(new AdminProduct($db))->save($data,$user)]);
        case 'product-status': $user=requireStaff();$data=staffBody();staffRespond(['ok'=>true,'product'=>(new AdminProduct($db))->setAvailability((int)($data['id']??0),(bool)($data['isAvailable']??false),$user)]);
        case 'status':
            $user=requireStaff();$data=staffBody();$orderId=(int)($data['orderId']??0);$status=$data['status']??'';$orders=new AdminOrder($db);$orders->updateStatus($orderId,$status,$user);$deliveryEmailSent=null;
            if($status==='Delivered'&&($recipient=$orders->claimDeliveryNotification($orderId))){try{(new EmailService())->sendOrderDelivered($recipient);$deliveryEmailSent=true;}catch(Throwable $mailError){$orders->releaseDeliveryNotification($orderId);$deliveryEmailSent=false;error_log('Delivered email failed: '.$mailError->getMessage());}}
            staffRespond(['ok'=>true,'deliveryEmailSent'=>$deliveryEmailSent]+portalDashboard($db,$user));
        default: staffRespond(['ok'=>false,'message'=>'Unknown staff API action.'],404);
    }
}catch(RuntimeException $error){staffRespond(['ok'=>false,'message'=>$error->getMessage()],400);}
catch(Throwable $error){error_log($error->getMessage());staffRespond(['ok'=>false,'message'=>'Database request failed. Check XAMPP MySQL.'],500);}
