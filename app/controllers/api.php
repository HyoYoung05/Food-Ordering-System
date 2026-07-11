<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Menu.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../services/EmailService.php';

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function body(): array
{
    return json_decode(file_get_contents('php://input'), true) ?: [];
}

function customerId(): int
{
    if (empty($_SESSION['customer_id'])) respond(['ok' => false, 'message' => 'Please log in first.'], 401);
    return (int)$_SESSION['customer_id'];
}

function publicBaseUrl(): string
{
    $proto=($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https'||(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';$host=$_SERVER['HTTP_X_FORWARDED_HOST']??$_SERVER['HTTP_HOST']??'localhost';$path=str_replace('\\','/',dirname(dirname(dirname($_SERVER['SCRIPT_NAME']??'/'))));return $proto.'://'.$host.($path==='/'?'':$path);
}

try {
    $db = Database::connection();
    $action = $_GET['action'] ?? 'bootstrap';

    switch ($action) {
        case 'bootstrap':
            $customer = $_SESSION['customer'] ?? null;
            respond(['ok' => true, 'user' => $customer, 'menu' => (new Menu($db))->all(),
                'cart' => $customer ? (new Cart($db))->get((int)$customer['id']) : [],
                'orders' => $customer ? (new Order($db))->allForCustomer((int)$customer['id']) : []]);

        case 'login':
            $data = body();
            if (trim($data['identifier'] ?? '') === '' || strlen($data['password'] ?? '') < 6) {
                respond(['ok' => false, 'message' => 'Enter your username/email and password.'], 422);
            }
            $customer = (new Customer($db))->authenticate($data['identifier'], $data['password']);
            session_regenerate_id(true);
            $_SESSION['customer_id'] = $customer['id']; $_SESSION['customer'] = $customer;
            respond(['ok' => true, 'user' => $customer, 'cart' => (new Cart($db))->get($customer['id']), 'orders' => (new Order($db))->allForCustomer($customer['id'])]);

        case 'register':
            $data = body();
            if (trim($data['firstName'] ?? '') === '' || trim($data['surname'] ?? '') === '' || !filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL) || trim($data['phone'] ?? '') === '' || trim($data['phoneCountry'] ?? '') === '' || trim($data['address'] ?? '') === '' || trim($data['country'] ?? '') === '' || trim($data['zipCode'] ?? '') === '' || strlen($data['password'] ?? '') < 6 || ($data['password'] ?? '') !== ($data['passwordConfirmation'] ?? '')) {
                respond(['ok' => false, 'message' => 'Complete every field, use a valid email, and make sure the passwords match.'], 422);
            }
            if (!preg_match('/^\+[0-9]{1,4}$/', $data['phoneCountry']) || strlen(trim($data['country'])) > 80 || strlen(trim($data['zipCode'])) > 20) {
                respond(['ok' => false, 'message' => 'Select a valid country, calling code, and ZIP/postal code.'], 422);
            }
            if (!preg_match('/^[0-9]{6,15}$/', trim($data['phone'])) || !preg_match('/^[0-9]{3,10}$/', trim($data['zipCode']))) {
                respond(['ok' => false, 'message' => 'Phone number and ZIP/postal code must contain numbers only.'], 422);
            }
            $customer = (new Customer($db))->register($data['firstName'], $data['surname'], $data['username'] ?? '', $data['email'], $data['password'], $data['phone'], $data['phoneCountry'], $data['address'], $data['country'], $data['zipCode']);
            $token=$customer['_verificationToken'];unset($customer['_verificationToken']);$url=publicBaseUrl().'/app/controllers/verify-email.php?token='.rawurlencode($token);
            try{(new EmailService())->sendVerification($customer,$url);}catch(Throwable $mailError){$db->prepare('DELETE FROM customers WHERE id=? AND email_verified_at IS NULL')->execute([$customer['id']]);error_log('Verification email failed: '.$mailError->getMessage());throw new RuntimeException('The verification email could not be sent. Check the address and try again.');}
            respond(['ok'=>true,'requiresVerification'=>true,'message'=>'Account created. Check your email and click the verification link before logging in.']);

        case 'resend-verification':
            if($_SERVER['REQUEST_METHOD']!=='POST')respond(['ok'=>false,'message'=>'Method not allowed.'],405);$data=body();$identifier=trim($data['identifier']??'');
            if($identifier==='')respond(['ok'=>false,'message'=>'Enter your username or email first.'],422);
            $customer=(new Customer($db))->prepareVerificationResend($identifier);
            if($customer){$token=$customer['_verificationToken'];unset($customer['_verificationToken']);$url=publicBaseUrl().'/app/controllers/verify-email.php?token='.rawurlencode($token);try{(new EmailService())->sendVerification($customer,$url);}catch(Throwable $mailError){error_log('Resent verification email failed: '.$mailError->getMessage());throw new RuntimeException('The verification email could not be sent right now. Please try again later.');}}
            respond(['ok'=>true,'message'=>'If that account is awaiting verification, a new email has been sent.']);

        case 'forgot-password':
            if($_SERVER['REQUEST_METHOD']!=='POST')respond(['ok'=>false,'message'=>'Method not allowed.'],405);$data=body();$identifier=trim($data['identifier']??'');
            if($identifier==='')respond(['ok'=>false,'message'=>'Enter your username or email first.'],422);
            $customer=(new Customer($db))->preparePasswordReset($identifier);
            if($customer){$token=$customer['_resetToken'];unset($customer['_resetToken']);$url=publicBaseUrl().'/app/controllers/reset-password.php?token='.rawurlencode($token);try{(new EmailService())->sendPasswordReset($customer,$url);}catch(Throwable $mailError){error_log('Password reset email failed: '.$mailError->getMessage());throw new RuntimeException('The password reset email could not be sent right now. Please try again later.');}}
            respond(['ok'=>true,'message'=>'If an account matches that username or email, a password reset link has been sent.']);

        case 'profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'message' => 'Method not allowed.'], 405);
            $data=body();$id=customerId();
            if(trim($data['firstName']??'')===''||trim($data['surname']??'')===''||!filter_var($data['email']??'',FILTER_VALIDATE_EMAIL)||trim($data['address']??'')===''||trim($data['country']??'')===''||!preg_match('/^[0-9]{6,15}$/',trim($data['phone']??''))||!preg_match('/^\+[0-9]{1,4}$/',$data['phoneCountry']??'')||!preg_match('/^[0-9]{3,10}$/',trim($data['zipCode']??''))){
                respond(['ok'=>false,'message'=>'Complete all profile fields with valid information.'],422);
            }
            $customer=(new Customer($db))->updateProfile($id,$data);$_SESSION['customer']=$customer;
            respond(['ok'=>true,'user'=>$customer]);

        case 'logout':
            session_unset(); session_destroy(); respond(['ok' => true]);

        case 'cart':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'message' => 'Method not allowed.'], 405);
            respond(['ok' => true, 'cart' => (new Cart($db))->replace(customerId(), body()['items'] ?? [])]);

        case 'orders':
            respond(['ok'=>true,'orders'=>(new Order($db))->allForCustomer(customerId())]);

        case 'order':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'message' => 'Method not allowed.'], 405);
            $data = body();
            if (trim($data['address'] ?? '') === '' || trim($data['phone'] ?? '') === '' || trim($data['payment'] ?? '') === '') {
                respond(['ok' => false, 'message' => 'Address, phone, and payment method are required.'], 422);
            }
            if (!preg_match('/^[0-9]{6,19}$/', trim($data['phone']))) respond(['ok' => false, 'message' => 'Phone number must contain numbers only.'], 422);
            $order=(new Order($db))->create(customerId(),$data);$emailSent=true;try{(new EmailService())->sendOrderReceipt($order);}catch(Throwable $mailError){$emailSent=false;error_log('Order receipt email failed: '.$mailError->getMessage());}
            respond(['ok'=>true,'order'=>$order,'emailSent'=>$emailSent]);

        case 'cancel-order':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['ok' => false, 'message' => 'Method not allowed.'], 405);
            $id=customerId();$data=body();$orderNumber=trim($data['orderNumber']??'');
            if($orderNumber==='')respond(['ok'=>false,'message'=>'Order number is required.'],422);
            respond(['ok'=>true,'orders'=>(new Order($db))->cancel($id,$orderNumber)]);

        default:
            respond(['ok' => false, 'message' => 'Unknown API action.'], 404);
    }
} catch (RuntimeException $error) {
    respond(['ok' => false, 'message' => $error->getMessage()], 400);
} catch (Throwable $error) {
    error_log($error->getMessage());
    respond(['ok' => false, 'message' => 'Database request failed. Check the XAMPP MySQL service and database configuration.'], 500);
}
