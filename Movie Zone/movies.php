<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user's movies
$my_movies = $conn->query("SELECT * FROM movies WHERE user_id = $user_id ORDER BY created_at DESC");

// Fetch all movies with user info, sorted by total likes DESC
$all_movies = $conn->query("
    SELECT m.*, u.full_name, u.id AS user_id,
           (SELECT COUNT(*) FROM likes WHERE movie_id = m.id) AS total_likes
    FROM movies m
    JOIN users u ON m.user_id = u.id
    ORDER BY total_likes DESC, m.created_at DESC
");


// Fetch like counts
$likes_map = [];
$likes_result = $conn->query("SELECT movie_id, COUNT(*) AS total_likes FROM likes GROUP BY movie_id");
while ($row = $likes_result->fetch_assoc()) {
    $likes_map[$row['movie_id']] = $row['total_likes'];
}
// Get which movies the current user has liked
$user_likes = [];
$user_like_result = $conn->query("SELECT movie_id FROM likes WHERE user_id = $user_id");
while ($row = $user_like_result->fetch_assoc()) {
    $user_likes[] = $row['movie_id'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Movies - Movie Zone</title>
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

    .logo {
        font-weight: bold;
        font-size: 18px;
        color: var(--accent);
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

    .container {
        max-width: 900px;
        margin: auto;
        padding: 20px;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    #search-input {
        width: 100%;
        padding: 10px;
        margin-bottom: 30px;
        border: 1px solid var(--primary);
        background: var(--dark);
        color: white;
        border-radius: 6px;
    }

    #toggle-form-btn {
        padding: 10px 20px;
        background: var(--primary);
        color: var(--dark);
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: block;
        margin: 0 auto 20px;
        font-weight: bold;
    }

    #movie-form-container {
        display: none;
        background: var(--light);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    form input, form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 12px;
        border-radius: 6px;
        border: 1px solid var(--primary);
        background: var(--dark);
        color: white;
    }

    form input::placeholder, textarea::placeholder {
        color: #ccc;
    }

    form button[type="submit"] {
        background: var(--primary);
        color: var(--dark);
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }

    form button:hover {
        background: var(--accent);
    }

    .movie-card {
        background: var(--light);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        gap: 15px;
        align-items: flex-start;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        flex-wrap: wrap;
    }

    .movie-card img {
        width: 120px;
        height: auto;
        border-radius: 6px;
    }

    .movie-card h3 {
        margin: 0 0 10px;
        color: var(--accent);
    }

    .movie-card p {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
        max-width: 100%;
    }

    .movie-actions a {
        display: inline-block;
        margin-right: 10px;
        color: var(--accent);
        font-size: 14px;
        text-decoration: none;
    }

    .movie-actions a:hover {
        color: red;
    }

    .review-tabs {
        text-align: center;
        margin-top: 40px;
        margin-bottom: 20px;
    }

    .review-tabs button {
        background-color: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
        padding: 10px 15px;
        border-radius: 6px;
        margin: 0 10px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .review-tabs button.active-tab {
        background-color: var(--primary);
        color: var(--dark);
    }
    button[disabled] {
    opacity: 0.6;
    cursor: not-allowed;
}
button {
  font-size: 15px;
}

button:focus {
  outline: none;
}
.like-btn {
    color: var(--primary);
    font-weight: bold;
    font-size: 15px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.like-btn:hover {
    color: var(--accent);
}

    @media (max-width: 768px) {
        .nav-links { display: none; }
        .hamburger { display: flex; }
    }
  </style>
</head>
<body>

<div class="navbar">
    <div class="logo">🎬 Movie Zone</div>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="movies.php">Movies</a>
        <a href="about.html">About</a>
        <a href="contact.html">Contact</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
    </div>
    <div class="mobile-menu" id="mobile-menu">
        <a href="home.php">Home</a>
        <a href="movies.php">Movies</a>
        <a href="about.html">About</a>
        <a href="contact.html">Contact</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>🎬 Search Your Movies</h2>
    <input type="text" id="search-input" placeholder="Type movie title to filter...">

    <button id="toggle-form-btn">➕ Add New Movie</button>

    <div id="movie-form-container">
        
<form action="add_movie.php" method="POST">
    <input type="text" id="movie-search" name="search_title" placeholder="Search movie title..." required autocomplete="off">

    <div id="search-results" style="background: #2e0c0e; border: 1px solid #CE851E; max-height: 200px; overflow-y: auto; margin-bottom: 10px;"></div>

    <!-- Filled by JS from TMDb -->
    <input type="hidden" name="title" id="selected-title">
    <input type="hidden" name="poster" id="selected-poster">

    <textarea name="review" placeholder="Write your review..." required></textarea>

    <!-- ✅ User Rating Field -->
    <input type="number" name="user_rating" min="1" max="10" step="0.1" placeholder="Your rating (1-10)" required>

    <label><input type="checkbox" name="favorite"> Mark as Favorite</label>
    <br><br>
    <button type="submit">Add Movie</button>
</form>


    </div>

    <!-- 🔁 Tab Buttons -->
    <div class="review-tabs">
        <button id="all-reviews-btn" class="active-tab">🌍 All Reviews</button>
<button id="my-reviews-btn">🎥 My Reviews</button>
    </div>

    <!-- 🎥 My Reviews Section -->
    <div id="my-reviews-container">
        <?php while ($movie = $my_movies->fetch_assoc()): ?>
            <div class="movie-card">
                <?php if (!empty($movie['image_path'])): ?>
                    <img src="<?php echo $movie['image_path']; ?>" alt="Movie Poster">
                <?php endif; ?>
                <div>
                    <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($movie['review'])); ?></p>
                    <?php if ($movie['is_trending']): ?>
                        <strong>⭐ Favorite</strong><br>
                    <?php endif; ?>
                    <?php if (!empty($movie['rating'])): ?>
                        <span style="color: #FFD700; font-weight: bold;">🌟 Rating: <?php echo htmlspecialchars($movie['rating']); ?>/10</span><br>
                    <?php endif; ?>
                    <div class="movie-actions">
                        <a href="edit_movie.php?id=<?php echo $movie['id']; ?>">✏️ Edit</a>
                        <a href="delete_movie.php?id=<?php echo $movie['id']; ?>" onclick="return confirm('Are you sure?');">❌ Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- 🌍 All Reviews Section (empty for now) -->
<div id="all-reviews-container" style="display: none;">
<?php if ($all_movies->num_rows === 0): ?>
    <p style="text-align:center; color:white;">😕 No public reviews yet!</p>
<?php else: ?>
    <?php while ($review = $all_movies->fetch_assoc()): ?>

        <div class="movie-card">
            <?php if (!empty($review['image_path'])): ?>
                <img src="<?php echo $review['image_path']; ?>" alt="Movie Poster">
            <?php endif; ?>
            <div>
                <!-- 🔗 User Mini ID + Link to Profile -->
                <small>
                    👤 
                    <a href="view_profile.php?id=<?php echo $review['user_id']; ?>" style="color: #FFD700; text-decoration: underline;">
                        <?php echo htmlspecialchars($review['full_name']); ?>
                    </a>
                </small><br>

                <h3><?php echo htmlspecialchars($review['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>

                <?php if ($review['is_trending']): ?>
                    <strong>⭐ Favorite</strong><br>
                <?php endif; ?>

                <?php if (!empty($review['rating'])): ?>
                    <span style="color: #FFD700; font-weight: bold;">🌟 Rating: <?php echo htmlspecialchars($review['rating']); ?>/10</span><br>
                <?php endif; ?>

                <!-- ❤️ Like Button -->
                <div class="like-container" data-movie-id="<?php echo $review['id']; ?>" style="margin-top: 8px;">
    <button class="like-btn" data-liked="<?php echo in_array($review['id'], $user_likes) ? '1' : '0'; ?>" style="background: none; border: none; cursor: pointer;">
        <?php echo in_array($review['id'], $user_likes) ? '❤️‍🔥 Unlike' : '🤍 Like'; ?>
    </button>
    <span class="like-count" style="color: #FFD700;">
        <?php echo $likes_map[$review['id']] ?? 0; ?> Likes
    </span>
</div>



            </div>
        </div>
        
    <?php endwhile; ?>
<?php endif; ?>
</div>

</div>

<script>
// 🔍 Movie Filter Search (on-page)
document.getElementById('search-input').addEventListener('input', function () {
    const filter = this.value.toLowerCase();
    const cards = document.querySelectorAll('.movie-card');
    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        card.style.display = title.includes(filter) ? 'flex' : 'none';
    });
});

// ➕ Toggle Add Movie Form
const toggleBtn = document.getElementById('toggle-form-btn');
const formContainer = document.getElementById('movie-form-container');
toggleBtn.addEventListener('click', () => {
    const isVisible = formContainer.style.display === 'block';
    formContainer.style.display = isVisible ? 'none' : 'block';
    toggleBtn.textContent = isVisible ? "➕ Add New Movie" : "➖ Hide Movie Form";
});

// 🍔 Hamburger Menu
document.getElementById('hamburger').addEventListener('click', () => {
    const menu = document.getElementById('mobile-menu');
    menu.style.display = menu.style.display === "flex" ? "none" : "flex";
});

// 🔁 Tab Toggle — All Reviews Default
const myBtn = document.getElementById('my-reviews-btn');
const allBtn = document.getElementById('all-reviews-btn');
const mySection = document.getElementById('my-reviews-container');
const allSection = document.getElementById('all-reviews-container');

mySection.style.display = 'none';
allSection.style.display = 'block';
myBtn.classList.remove('active-tab');
allBtn.classList.add('active-tab');

myBtn.addEventListener('click', () => {
    mySection.style.display = 'block';
    allSection.style.display = 'none';
    myBtn.classList.add('active-tab');
    allBtn.classList.remove('active-tab');
});

allBtn.addEventListener('click', () => {
    mySection.style.display = 'none';
    allSection.style.display = 'block';
    allBtn.classList.add('active-tab');
    myBtn.classList.remove('active-tab');
});

// ❤️ Like/Unlike AJAX (no reload)
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const container = this.closest('.like-container');
        const movieId = container.getAttribute('data-movie-id');
        const likeCountSpan = container.querySelector('.like-count');

        fetch('like_review.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `movie_id=${movieId}`
        })
        .then(res => res.json())
        .then(data => {
            this.textContent = data.liked ? '❤️‍🔥 Unlike' : '🤍 Like';
            this.setAttribute('data-liked', data.liked ? '1' : '0');
            likeCountSpan.textContent = `${data.likes} Likes`;
        })
        .catch(err => {
            alert("Failed to like/unlike. Please try again.");
            console.error(err);
        });
    });
});

// 🔎 TMDb Movie Search with Auto-fill (no rating fill)
document.getElementById('movie-search').addEventListener('input', function () {
    const query = this.value.trim();
    const resultBox = document.getElementById('search-results');
    resultBox.innerHTML = '';

    if (query.length < 2) return;

    fetch(`tmdb_search.php?query=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.results) return;
            data.results.slice(0, 5).forEach(movie => {
                const div = document.createElement('div');
                div.style.padding = "10px";
                div.style.borderBottom = "1px solid #CE851E";
                div.style.cursor = "pointer";
                div.style.color = "#FFD700";
                div.textContent = `${movie.title} (${movie.release_date?.slice(0, 4) || 'N/A'})`;
                
                div.addEventListener('click', () => {
                    document.getElementById('movie-search').value = `${movie.title} (${movie.release_date?.slice(0, 4) || ''})`;
                    document.getElementById('selected-title').value = movie.title;
                    document.getElementById('selected-poster').value = movie.poster_path 
                        ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` 
                        : '';
                    // No TMDb rating set — user will enter their own rating
                    resultBox.innerHTML = '';
                });

                resultBox.appendChild(div);
            });
        })
        .catch(err => {
            console.error("TMDb fetch error:", err);
        });
});
</script>


</body>
</html>
