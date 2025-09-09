<?php
include 'database.php';
session_start();

// Reset messages
$_SESSION['signup_error'] = "";
$_SESSION['signup_success'] = "";
$_SESSION['signin_error'] = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ========== SIGNUP ==========
    if (isset($_POST['signup'])) {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $about = $_POST['about'] ?? '';
        $profilePicPath = null;

        // ✅ Email verification check
        $is_verified = $_POST['email_verified'] ?? 'false';
        if ($is_verified !== 'true') {
            $_SESSION['signup_error'] = "Please verify your email before signing up.";
            header('Location: index.php');
            exit;
        }

        // ✅ Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $targetDir = "uploads/pfps/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
                $profilePicPath = $targetFile;
            }
        }

        // ✅ Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['signup_error'] = "This email is already taken!";
        } else {
            $sql = "INSERT INTO users (full_name, email, password, profile_pic, about) 
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $full_name, $email, $password, $profilePicPath, $about);

            if ($stmt->execute()) {
                $_SESSION['signup_success'] = "You have successfully signed up!";
            } else {
                $_SESSION['signup_error'] = "Error: " . $conn->error;
            }
        }
        $stmt->close();

    }

    // ========== SIGNIN ==========
    elseif (isset($_POST['signin'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                header('Location: home.php');
                exit;
            } else {
                $_SESSION['signin_error'] = "Wrong email or password!";
            }
        } else {
            $_SESSION['signin_error'] = "Wrong email or password!";
        }
        $stmt->close();
    }
}

// ✅ Final Redirect
header('Location: index.php');
exit;
