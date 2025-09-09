<?php
session_start();
include 'database.php';

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    echo "User not found.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit;
}

// Movies & Stats
$movies = $conn->query("SELECT * FROM movies WHERE user_id = $user_id ORDER BY created_at DESC");
$review_count = $movies->num_rows;
$viewer = $_SESSION['user_id'] ?? null;

$count_followers = $conn->query("SELECT COUNT(*) AS total FROM followers WHERE following_id = $user_id")->fetch_assoc()['total'];
$count_following = $conn->query("SELECT COUNT(*) AS total FROM followers WHERE follower_id = $user_id")->fetch_assoc()['total'];

$is_following = false;
if ($viewer && $viewer != $user_id) {
    $stmt = $conn->prepare("SELECT * FROM followers WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $viewer, $user_id);
    $stmt->execute();
    $is_following = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($user['full_name']); ?> - Profile</title>
  <style>
    :root {
      --dark: #441012;
      --light: #2e0c0e;
      --primary: #CE851E;
      --accent: #FFD700;
      --text: #fff;
    }

    body {
      background: var(--dark);
      color: var(--primary);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
    }

    .profile-box {
      max-width: 800px;
      margin: auto;
      background: var(--light);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.6);
    }

    .profile-box img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--primary);
      display: block;
      margin: auto;
    }

    .profile-box h2, .profile-box p {
      text-align: center;
    }

    .follow-counts {
      text-align: center;
      margin: 10px 0;
      font-size: 16px;
      color: var(--text);
    }

    .follow-button {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .follow-button a {
      padding: 10px 20px;
      background: var(--accent);
      color: var(--dark);
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }

    .back-button {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .back-button a {
      padding: 10px 20px;
      background: var(--primary);
      color: var(--dark);
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }

    .movies-list {
      max-width: 800px;
      margin: 40px auto;
    }

    .movie-card {
      display: flex;
      background: var(--light);
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      align-items: center;
      gap: 15px;
      color: var(--text);
      box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    }

    .movie-card img {
      width: 90px;
      height: 120px;
      object-fit: cover;
      border-radius: 6px;
      border: 2px solid var(--primary);
    }

    .movie-content h4 {
      margin: 0 0 6px 0;
      font-size: 18px;
      color: var(--accent);
    }

    .movie-content p {
      margin: 5px 0;
      font-size: 14px;
      line-height: 1.4;
    }

    .movie-rating {
      font-weight: bold;
      color: var(--accent);
      font-size: 14px;
      margin-top: 5px;
    }

    @media (max-width: 600px) {
      .movie-card {
        flex-direction: column;
        align-items: flex-start;
      }

      .movie-card img {
        width: 100%;
        height: auto;
      }

      .movie-content {
        margin-top: 10px;
      }

      .movie-content h4 {
        font-size: 16px;
      }
    }
  </style>
</head>
<body>

<!-- Profile Section -->
<div class="profile-box">
  <img src="<?php echo $user['profile_pic'] ?: 'assets/default.png'; ?>" alt="User Profile Picture">
  <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
  <p><?php echo nl2br(htmlspecialchars($user['about'])); ?></p>

  <div class="follow-counts">
    👥 <strong><?php echo $count_followers; ?></strong> Followers |
    <strong><?php echo $count_following; ?></strong> Following
  </div>

  <?php if ($viewer && $viewer != $user_id): ?>
    <div class="follow-button">
      <a href="follow_user.php?id=<?php echo $user_id; ?>">
        <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
      </a>
    </div>
  <?php endif; ?>

  <p style="text-align:center;">🎬 <strong><?php echo $review_count; ?></strong> Movie Reviews</p>

  <div class="back-button">
    <a href="profile.php">⬅ Back to My Profile</a>
  </div>
</div>

<!-- Movie Reviews -->
<div class="movies-list">
  <?php while ($movie = $movies->fetch_assoc()): ?>
    <div class="movie-card">
      <img src="<?php echo $movie['image_path'] ?: 'assets/default-movie.jpg'; ?>" alt="Movie Image">
      <div class="movie-content">
        <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
        <p><?php echo nl2br(htmlspecialchars($movie['review'])); ?></p>
        <?php if (!empty($movie['rating'])): ?>
          <p class="movie-rating">🌟 Rating: <?php echo htmlspecialchars($movie['rating']); ?>/10</p>
        <?php endif; ?>
      </div>
    </div>
  <?php endwhile; ?>
</div>

</body>
</html>
