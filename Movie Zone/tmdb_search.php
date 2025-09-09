<?php
include 'tmdb_config.php';

$query = $_GET['query'] ?? '';
if (!$query) {
    echo json_encode(['results' => []]);
    exit;
}

$url = "https://api.themoviedb.org/3/search/movie?api_key=$TMDB_API_KEY&query=" . urlencode($query);
$response = file_get_contents($url);
header('Content-Type: application/json');
echo $response;
