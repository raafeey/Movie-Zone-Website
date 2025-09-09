<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$user_name = $_SESSION['user_name'] ?? 'Guest';

include 'database.php';
$user_id = $_SESSION['user_id'];
$favorites = $conn->query("SELECT * FROM movies WHERE user_id = $user_id AND is_trending = 1 ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Movie Zone - Home</title>
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
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--dark);
            color: var(--primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .video-section {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }

        .video-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .navbar {
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo img {
            height: 55px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--primary);
            padding: 8px 14px;
            border: 1px solid var(--primary);
            border-radius: 6px;
            transition: 0.3s;
            font-weight: bold;
        }

        .nav-links a:hover {
            background-color: var(--primary);
            color: var(--dark);
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .hamburger span {
            height: 3px;
            width: 25px;
            background: var(--primary);
            margin: 4px 0;
            border-radius: 2px;
            transition: 0.4s;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            background-color: var(--light);
            position: absolute;
            top: 70px;
            right: 20px;
            width: 200px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.4);
            z-index: 99;
        }

        .mobile-menu a {
            padding: 12px;
            text-decoration: none;
            color: var(--primary);
            border-bottom: 1px solid var(--dark);
        }

        .mobile-menu a:hover {
            background: var(--primary);
            color: var(--dark);
        }

        /* Welcome */
        .welcome-banner {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .welcome-banner h1 {
            font-size: 42px;
            background: rgba(255,255,255,0.1);
            padding: 10px 20px;
            border: 2px solid var(--primary);
            border-radius: 10px;
            backdrop-filter: blur(5px);
            color: var(--accent);
        }

        .welcome-banner p {
            margin-top: 15px;
            max-width: 600px;
            line-height: 1.6;
            font-size: 16px;
            color: var(--text);
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--primary);
        }

        .trending-section {
            text-align: center;
            padding: 40px 20px;
        }

        .trending-section h2 {
            font-size: 32px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .trending-section p {
            margin-bottom: 25px;
            color: var(--text);
        }

        .movies-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .movie-box {
            background-color: var(--light);
            padding: 10px;
            border-radius: 10px;
            width: 150px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .movie-box img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
        }

        .movie-box p {
            margin-top: 10px;
            font-weight: bold;
            font-size: 15px;
        }
        .ai-btn {
    display: inline-block;
    margin-top: 20px;
    background-color: transparent;
    color: var(--accent);
    border: 2px solid var(--accent);
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 0 10px var(--accent);
}

.ai-btn:hover {
    background-color: var(--accent);
    color: var(--dark);
    transform: scale(1.05);
    box-shadow: 0 0 20px var(--accent), 0 0 40px var(--accent);
}


        footer {
            background-color: var(--dark);
            text-align: center;
            padding: 15px;
            color: var(--primary);
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .welcome-banner h1 {
                font-size: 28px;
            }

            .welcome-banner p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="video-section">
    <div class="video-container">
        <video autoplay loop muted>
            <source src="assets/TensorPix - WhatsApp Video 2024-12-30 at 2.mp4" type="video/mp4">
        </video>
    </div>
    <div class="video-overlay"></div>

    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">
            <img src="assets/Screenshot_2024-12-30_180101-removebg-preview.png" alt="Logo" />
        </div>

        <nav class="nav-links">
            <a href="home.php">Home</a>
            <a href="movies.php">Movies</a>
            <a href="about.html">About</a>
            <a href="contact.html">Contact</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>

        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="mobile-menu" id="mobile-menu">
            <a href="home.php">Home</a>
            <a href="movies.php">Movies</a>
            <a href="about.html">About</a>
            <a href="contact.html">Contact</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <!-- Welcome Banner -->
    <section class="welcome-banner">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?> to Movie Zone</h1>
        <p>Your personal movie universe — save what you love, review what you feel, and build a collection that’s truly yours.</p>
    </section>
</div>

<!-- Favorite Movies -->
<section class="trending-section">
    <h2>My Favorite Movies</h2>
    <p>These are your favorite movies saved by you.</p>
    <div class="movies-grid">
        <?php while ($movie = $favorites->fetch_assoc()): ?>
            <div class="movie-box">
                <img src="<?php echo $movie['image_path']; ?>" alt="Favorite Poster" />
                <p><?php echo htmlspecialchars($movie['title']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<!-- AI Recommendation Section -->
<section class="trending-section">
    <h2>AI-Based Recommendations</h2>
    <p>Experience the power of AI to get personalized movie suggestions based on your mood and preferences. It's fast, smart, and fun to use!</p>
    <a href="http://127.0.0.1:5000/" class="ai-btn">Try AI Recommender 🚀</a>
</section>
<footer>© 2024 Movie Zone | All Rights Reserved</footer>

<script>
    const hamburger = document.getElementById("hamburger");
    const mobileMenu = document.getElementById("mobile-menu");

    hamburger.addEventListener("click", () => {
        mobileMenu.style.display = mobileMenu.style.display === "flex" ? "none" : "flex";
    });
</script>

</body>
</html>
