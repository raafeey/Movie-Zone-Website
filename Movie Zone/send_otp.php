<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// ✅ Prevent resend spam: only allow 1 request every 60 seconds
if (isset($_SESSION['otp_last_sent']) && time() - $_SESSION['otp_last_sent'] < 60) {
    echo json_encode(['status' => 'wait', 'message' => 'Please wait 1 minute before requesting a new code.']);
    exit;
}

// ✅ Generate random 6-digit OTP
$otp = rand(100000, 999999);
$_SESSION['email_verification_code'] = $otp;
$_SESSION['email_verification_target'] = $email;
$_SESSION['otp_generated_at'] = time();
$_SESSION['otp_last_sent'] = time();

// ✅ Send OTP using PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '';   // ✅ Your Gmail address
    $mail->Password   = '';          // ✅ Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('abdurrafaytahir2@gmail.com', 'MovieZone');
    $mail->addAddress($email);

    $mail->Subject = '🎬 Verify Your Email - MovieZone';

$mail->Body = "
Hi there,

Thank you for signing up at **MovieZone** — your go-to place for movie reviews and film discussions!

🔐 Your verification code is: **$otp**

This code is valid for the next **5 minutes**.  
Please do not share it with anyone.

If you didn't request this, you can safely ignore this message.

Best regards,  
🎥 **The MovieZone Team**
";


    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Verification code sent to your Gmail.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email: ' . $mail->ErrorInfo]);
}
