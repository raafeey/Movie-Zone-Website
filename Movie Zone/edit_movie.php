<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = $_GET['id'] ?? 0;

// Get movie data
$result = $conn->query("SELECT * FROM movies WHERE id = $movie_id AND user_id = $user_id");
if ($result->num_rows === 0) {
    echo "Movie not found or access denied.";
    exit;
}
$movie = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Movie - Movie Zone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --dark: #441012;
            --light: #2e0c0e;
            --primary: #CE851E;
            --accent: #FFD700;
            --text: #fff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background-color: var(--dark);
            color: var(--primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: var(--light);
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--accent);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text);
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--primary);
            border-radius: 8px;
            background-color: var(--dark);
            color: var(--text);
            margin-bottom: 20px;
        }

        input::placeholder,
        textarea::placeholder {
            color: #bbb;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--text);
        }

        button {
            width: 100%;
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: var(--accent);
        }

        .current-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .current-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>✏️ Edit Movie</h2>
    <form method="POST" enctype="multipart/form-data" action="update_movie.php">
        <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">

        <label for="title">Movie Title</label>
        <input type="text" id="title" name="title" placeholder="Enter movie title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>

        <label for="review">Your Review</label>
        <textarea id="review" name="review" placeholder="Write your updated review..." required><?php echo htmlspecialchars($movie['review']); ?></textarea>

        <div class="current-image">
            <label>Current Banner:</label><br>
            <?php if (!empty($movie['image_path'])): ?>
                <img src="<?php echo $movie['image_path']; ?>" alt="Current Banner">
            <?php else: ?>
                <p style="color: #ccc;">No banner uploaded.</p>
            <?php endif; ?>
        </div>

        <label for="banner">Upload New Banner (optional)</label>
        <input type="file" id="banner" name="banner">

        <div class="checkbox-row">
            <input type="checkbox" id="favorite" name="trending" <?php if ($movie['is_trending']) echo 'checked'; ?>>
            <label for="favorite">Mark as Favorite</label>
        </div>

        <button type="submit">💾 Update Movie</button>
    </form>
</div>

</body>
</html>
