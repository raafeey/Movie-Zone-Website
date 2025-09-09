<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = $_GET['id'] ?? 0;

$conn->query("DELETE FROM movies WHERE id = $movie_id AND user_id = $user_id");

header("Location: movies.php");
exit;
?>
