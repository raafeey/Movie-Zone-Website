<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? '';
$about = $_POST['about'] ?? '';
$profilePicPath = null;

// Handle image upload
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $targetDir = "uploads/pfps/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFile = $targetDir . $fileName;
    move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile);
    $profilePicPath = $targetFile;

    // Update with profile_pic, name and about
    $sql = "UPDATE users SET full_name = ?, profile_pic = ?, about = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $full_name, $profilePicPath, $about, $user_id);
} else {
    // Update only name and about
    $sql = "UPDATE users SET full_name = ?, about = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $full_name, $about, $user_id);
}

$stmt->execute();
$stmt->close();

// Update session name
$_SESSION['user_name'] = $full_name;

header("Location: profile.php");
exit;
?>
