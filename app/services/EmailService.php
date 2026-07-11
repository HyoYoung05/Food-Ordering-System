<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/ReceiptPdf.php';

final class EmailService
{
    private function mailer(): PHPMailer
    {
        $username=Env::get('SMTP_USERNAME','');$password=str_replace(' ','',Env::get('SMTP_APP_PASSWORD',''));
        if($username===''||$password==='')throw new RuntimeException('Gmail SMTP is not configured.');
        $mail=new PHPMailer(true);$mail->isSMTP();$mail->Host=Env::get('SMTP_HOST','smtp.gmail.com');$mail->SMTPAuth=true;$mail->Username=$username;$mail->Password=$password;$mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;$mail->Port=(int)Env::get('SMTP_PORT','587');$mail->CharSet='UTF-8';$mail->setFrom($username,Env::get('SMTP_FROM_NAME','Savorly Kitchen'));return $mail;
    }

    public function sendVerification(array $customer,string $verificationUrl): void
    {
        $mail=$this->mailer();$mail->addAddress($customer['email'],$customer['name']);$mail->isHTML(true);$mail->Subject='Verify your Savorly email address';$name=htmlspecialchars($customer['firstName']?:$customer['name'],ENT_QUOTES,'UTF-8');$url=htmlspecialchars($verificationUrl,ENT_QUOTES,'UTF-8');$mail->Body="<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#294c3d'><h1>Welcome to Savorly</h1><p>Hi {$name}, verify your email address to activate your customer account.</p><p style='margin:28px 0'><a href='{$url}' style='padding:13px 20px;border-radius:24px;background:#476f5c;color:#fff;text-decoration:none;font-weight:bold'>Verify email address</a></p><p style='color:#65756f;font-size:13px'>If the button does not open, copy and paste this link into your browser:</p><p style='padding:10px;border-radius:8px;background:#f1f4ef;color:#294c3d;font-size:12px;word-break:break-all'>{$url}</p><p style='color:#65756f;font-size:13px'>This link expires in one hour. If you did not create this account, ignore this email.</p></div>";$mail->AltBody="Verify your Savorly account by opening this link:\n{$verificationUrl}\n\nThis link expires in one hour.";$mail->send();
    }

    public function sendOrderReceipt(array $order): void
    {
        $mail=$this->mailer();$mail->addAddress($order['email'],$order['customer']);$mail->isHTML(true);$mail->Subject="Savorly order confirmation #{$order['id']}";$name=htmlspecialchars($order['customer'],ENT_QUOTES,'UTF-8');$number=htmlspecialchars($order['id'],ENT_QUOTES,'UTF-8');$total=number_format((float)$order['total'],2);$mail->Body="<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#294c3d'><h1>Order confirmed</h1><p>Hi {$name}, we received order <strong>#{$number}</strong>.</p><p>Total: <strong>PHP {$total}</strong><br>Payment: Cash on Delivery</p><p>Your PDF receipt is attached. Track the order from My Orders in Savorly.</p></div>";$mail->AltBody="Your Savorly order #{$order['id']} is confirmed. Total: PHP {$total}. Your receipt is attached.";$mail->addStringAttachment(ReceiptPdf::make($order),"Savorly-Receipt-{$order['id']}.pdf",'base64','application/pdf');$mail->send();
    }

    public function sendOrderDelivered(array $order): void
    {
        $mail=$this->mailer();$mail->addAddress($order['email'],$order['customer']);$mail->isHTML(true);$mail->Subject="Order Delivered — Savorly #{$order['number']}";$name=htmlspecialchars($order['customer'],ENT_QUOTES,'UTF-8');$number=htmlspecialchars($order['number'],ENT_QUOTES,'UTF-8');$total=number_format((float)$order['total'],2);$mail->Body="<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#294c3d'><div style='width:58px;height:58px;line-height:58px;border-radius:50%;background:#e9f0e7;text-align:center;font-size:28px'>✓</div><h1 style='font-family:Georgia,serif'>Order Delivered</h1><p>Hi {$name}, your Savorly order <strong>#{$number}</strong> has been delivered.</p><p style='padding:14px;border-radius:12px;background:#f6f4ee'>Order total: <strong>PHP {$total}</strong></p><p>Thank you for ordering with Savorly. We hope you enjoy your meal!</p></div>";$mail->AltBody="Order Delivered\n\nHi {$order['customer']}, your Savorly order #{$order['number']} has been delivered. Total: PHP {$total}. Thank you for ordering with Savorly!";$mail->send();
    }

    public function sendPasswordReset(array $customer,string $resetUrl): void
    {
        $mail=$this->mailer();$mail->addAddress($customer['email'],$customer['name']);$mail->isHTML(true);$mail->Subject='Reset your Savorly password';$name=htmlspecialchars($customer['firstName']?:$customer['name'],ENT_QUOTES,'UTF-8');$url=htmlspecialchars($resetUrl,ENT_QUOTES,'UTF-8');$mail->Body="<div style='font-family:Arial,sans-serif;max-width:560px;margin:auto;color:#294c3d'><h1 style='font-family:Georgia,serif'>Reset your password</h1><p>Hi {$name}, we received a request to reset your Savorly password.</p><p style='margin:28px 0'><a href='{$url}' style='padding:13px 20px;border-radius:24px;background:#476f5c;color:#fff;text-decoration:none;font-weight:bold'>Choose a new password</a></p><p style='color:#65756f;font-size:13px'>If the button does not open, copy this link:</p><p style='padding:10px;border-radius:8px;background:#f1f4ef;color:#294c3d;font-size:12px;word-break:break-all'>{$url}</p><p style='color:#65756f;font-size:13px'>The link expires in one hour. If you did not request this change, ignore this email.</p></div>";$mail->AltBody="Reset your Savorly password:\n{$resetUrl}\n\nThis link expires in one hour.";$mail->send();
    }
}
