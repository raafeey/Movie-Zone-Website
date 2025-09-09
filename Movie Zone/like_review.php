<?php
session_start();
header('Content-Type: application/json'); // Important for AJAX responses
include 'database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = intval($_POST['movie_id'] ?? 0);

if (!$movie_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Movie ID missing']);
    exit;
}

// Check if user already liked this movie
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND movie_id = ?");
$stmt->bind_param("ii", $user_id, $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$already_liked = $result->num_rows > 0;
$stmt->close();

if ($already_liked) {
    // Unlike (delete)
    $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND movie_id = ?");
    $del->bind_param("ii", $user_id, $movie_id);
    $del->execute();
    $del->close();
    $liked = false;
} else {
    // Like (insert)
    $add = $conn->prepare("INSERT INTO likes (user_id, movie_id) VALUES (?, ?)");
    $add->bind_param("ii", $user_id, $movie_id);
    $add->execute();
    $add->close();
    $liked = true;
}

// Get updated like count
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM likes WHERE movie_id = ?");
$count_stmt->bind_param("i", $movie_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'likes' => $count_result['total']
]);
exit;
?>
