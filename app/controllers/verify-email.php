<?php
declare(strict_types=1);

require_once __DIR__.'/../../config/database.php';
require_once __DIR__.'/../models/Customer.php';

$success=false;$message='The verification link is invalid or has expired.';
try{$token=trim($_GET['token']??'');if($token==='')throw new RuntimeException($message);(new Customer(Database::connection()))->verifyEmail($token);$success=true;$message='Your email has been verified. You can now log in and place orders.';}catch(Throwable $error){$message=$error->getMessage();}
$proto=($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https'||(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';$host=$_SERVER['HTTP_X_FORWARDED_HOST']??$_SERVER['HTTP_HOST']??'localhost';$path=str_replace('\\','/',dirname(dirname(dirname($_SERVER['SCRIPT_NAME']??'/'))));$base=$proto.'://'.$host.($path==='/'?'':$path);
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Email verification — Savorly</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#fbf8f1;color:#294c3d;font-family:Arial,sans-serif}.card{width:min(480px,100%);padding:38px;border-radius:24px;background:#fff;box-shadow:0 20px 60px rgba(35,62,50,.13);text-align:center}.mark{width:58px;height:58px;margin:auto;border-radius:50%;display:grid;place-items:center;background:<?= $success?'#e9f0e7':'#fde8e5' ?>;font-size:27px}h1{font-family:Georgia,serif}p{color:#65756f;line-height:1.6}a{display:inline-block;margin-top:12px;padding:13px 22px;border-radius:999px;background:#476f5c;color:#fff;text-decoration:none;font-weight:bold}</style></head><body><main class="card"><div class="mark"><?= $success?'✓':'!' ?></div><h1><?= $success?'Email verified':'Verification failed' ?></h1><p><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></p><a href="<?= htmlspecialchars($base,ENT_QUOTES,'UTF-8') ?>/">Return to Savorly</a></main></body></html>
