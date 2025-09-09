<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title    = $conn->real_escape_string($_POST['title']);
    $poster   = $conn->real_escape_string($_POST['poster']); // Image URL from TMDb
    $review   = $conn->real_escape_string($_POST['review']);
    $favorite = isset($_POST['favorite']) ? 1 : 0;

    // ✅ Only store USER rating
    $user_rating = isset($_POST['user_rating']) && $_POST['user_rating'] !== '' ? (float)$_POST['user_rating'] : null;

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO movies (user_id, title, image_path, review, is_trending, rating) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssii", $user_id, $title, $poster, $review, $favorite, $user_rating);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: movies.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
