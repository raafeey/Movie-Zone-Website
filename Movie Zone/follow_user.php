<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$current_user = $_SESSION['user_id'];
$target_user = (int)$_GET['id'];

// Check if already following
$check = $conn->prepare("SELECT * FROM followers WHERE follower_id = ? AND following_id = ?");
$check->bind_param("ii", $current_user, $target_user);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Unfollow
    $delete = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
    $delete->bind_param("ii", $current_user, $target_user);
    $delete->execute();
} else {
    // Follow
    $insert = $conn->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
    $insert->bind_param("ii", $current_user, $target_user);
    $insert->execute();
}

header("Location: view_profile.php?id=$target_user");
exit;
?>
