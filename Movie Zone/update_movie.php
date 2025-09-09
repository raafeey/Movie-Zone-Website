<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = $_POST['id'];

$title = $conn->real_escape_string($_POST['title']);
$review = $conn->real_escape_string($_POST['review']);
$trending = isset($_POST['trending']) ? 1 : 0;

$imagePathSQL = "";
if (isset($_FILES["banner"]) && $_FILES["banner"]["error"] == 0) {
    $imageName = time() . "_" . basename($_FILES["banner"]["name"]);
    $targetPath = "uploads/" . $imageName;
    if (move_uploaded_file($_FILES["banner"]["tmp_name"], $targetPath)) {
        $imagePathSQL = ", image_path = '$targetPath'";
    }
}

$sql = "UPDATE movies SET title = '$title', review = '$review', is_trending = $trending $imagePathSQL 
        WHERE id = $movie_id AND user_id = $user_id";

if ($conn->query($sql)) {
    header("Location: movies.php");
    exit;
} else {
    echo "Update failed: " . $conn->error;
}
?>
