<?php
session_start();
header('Content-Type: application/json');

$user_code = $_POST['code'] ?? '';
$stored_code = $_SESSION['email_verification_code'] ?? null;
$generated_time = $_SESSION['otp_generated_at'] ?? null;

if (!$stored_code || !$generated_time) {
    echo json_encode(['status' => 'error', 'message' => 'OTP not sent or session expired.']);
    exit;
}

// Check expiry (5 minutes = 300 seconds)
if (time() - $generated_time > 300) {
    unset($_SESSION['email_verification_code']);
    unset($_SESSION['otp_generated_at']);
    echo json_encode(['status' => 'expired', 'message' => 'OTP has expired. Please request a new one.']);
    exit;
}

// Match code
if ($user_code == $stored_code) {
    $_SESSION['email_verified'] = true;
    echo json_encode(['status' => 'success', 'message' => 'Email verified successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect verification code.']);
}
