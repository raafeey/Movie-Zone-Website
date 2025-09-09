<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Count followers and following
$followers = $conn->query("SELECT COUNT(*) AS total FROM followers WHERE following_id = $user_id")->fetch_assoc()['total'];
$following = $conn->query("SELECT COUNT(*) AS total FROM followers WHERE follower_id = $user_id")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile - Movie Zone</title>
  <style>
    :root {
      --dark: #441012;
      --light: #2e0c0e;
      --primary: #CE851E;
      --accent: #FFD700;
      --text: #fff;
    }

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: var(--dark);
      color: var(--primary);
    }

    /* Responsive Navbar */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      background: rgba(0, 0, 0, 0.7);
    }

    .logo {
      font-size: 20px;
      font-weight: bold;
    }

    .nav-links {
      display: flex;
      gap: 15px;
    }

    .nav-links a {
      color: var(--primary);
      text-decoration: none;
      border: 1px solid var(--primary);
      padding: 6px 12px;
      border-radius: 5px;
    }

    .nav-links a:hover {
      background: var(--primary);
      color: var(--dark);
    }

    .hamburger {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
    }

    .hamburger div {
      width: 25px;
      height: 3px;
      background: var(--primary);
    }

    @media (max-width: 768px) {
      .nav-links {
        display: none;
        flex-direction: column;
        background: var(--light);
        position: absolute;
        top: 60px;
        right: 0;
        padding: 10px;
        border-radius: 0 0 0 10px;
      }

      .nav-links.active {
        display: flex;
      }

      .hamburger {
        display: flex;
      }
    }

    .profile-container {
      max-width: 700px;
      margin: 40px auto;
      background: var(--light);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }

    .profile-header {
      text-align: center;
    }

    .profile-header img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--primary);
      box-shadow: 0 0 10px rgba(0,0,0,0.4);
    }

    .profile-header h2 {
      margin-top: 15px;
      font-size: 24px;
    }

    .follow-counts {
      margin-top: 5px;
      font-size: 16px;
      color: var(--text);
    }

    .profile-info {
      margin-top: 30px;
    }

    .profile-info label {
      font-weight: bold;
      display: block;
      margin-bottom: 5px;
    }

    .profile-info input,
    .profile-info textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid var(--primary);
      border-radius: 6px;
      background: var(--dark);
      color: white;
    }

    button {
      background-color: var(--primary);
      color: var(--dark);
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    button:hover {
      background-color: var(--accent);
    }

    .user-card {
      display: flex;
      align-items: center;
      background: #2e0c0e;
      margin: 15px 0;
      padding: 15px;
      border-radius: 10px;
    }

    .user-card img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin-right: 15px;
      object-fit: cover;
      border: 2px solid var(--primary);
    }

    .user-card h4 {
      margin: 0;
    }

    .user-card p {
      margin: 5px 0;
      font-size: 14px;
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div class="logo">Movie Zone</div>
  <div class="hamburger" onclick="toggleMenu()">
    <div></div><div></div><div></div>
  </div>
  <div class="nav-links" id="navLinks">
    <a href="home.php">Home</a>
    <a href="movies.php">Movies</a>
    <a href="about.html">About</a>
    <a href="contact.html">Contact</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<!-- Profile Container -->
<div class="profile-container">
  <div class="profile-header">
    <img src="<?php echo $user['profile_pic'] ?: 'assets/default.png'; ?>" alt="Profile Picture">
    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
    <div class="follow-counts">
      👥 <?php echo $followers; ?> Followers | <?php echo $following; ?> Following
    </div>
  </div>

  <!-- Edit Form -->
  <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="profile-info">
    <label for="full_name">Full Name</label>
    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

    <label for="profile_pic">Change Profile Picture</label>
    <input type="file" name="profile_pic" accept="image/*">

    <label for="about">About Me</label>
    <textarea name="about" rows="4"><?php echo htmlspecialchars($user['about']); ?></textarea>

    <button type="submit">Update Profile</button>
  </form>

  <!-- Search and Other Users -->
  <hr style="margin: 40px 0; border-color: #CE851E;">
  <h3 style="text-align: center; color: #FFD700;">Explore Other Profiles</h3>

  <form method="GET" style="text-align: center; margin: 20px 0;">
    <input type="text" name="search" placeholder="Search users..." style="padding: 10px; width: 250px; border-radius: 8px; border: 1px solid #CE851E;">
    <button type="submit" style="padding: 10px; background: #CE851E; color: #441012; border: none; border-radius: 5px;">Search</button>
  </form>

  <?php
  $search = $_GET['search'] ?? '';
  $search_sql = "SELECT id, full_name, profile_pic, about FROM users WHERE id != ?";
  if ($search) {
      $search_sql .= " AND full_name LIKE ?";
      $stmt = $conn->prepare($search_sql);
      $like = "%$search%";
      $stmt->bind_param("is", $user_id, $like);
  } else {
      $stmt = $conn->prepare($search_sql);
      $stmt->bind_param("i", $user_id);
  }

  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()):
  ?>
    <div class="user-card">
      <img src="<?php echo $row['profile_pic'] ?: 'assets/default.png'; ?>" alt="User PFP">
      <div style="flex: 1;">
        <h4><?php echo htmlspecialchars($row['full_name']); ?></h4>
        <p><?php echo htmlspecialchars(substr($row['about'], 0, 100)); ?>...</p>
      </div>
      <a href="view_profile.php?id=<?php echo $row['id']; ?>" style="padding: 8px 15px; background: #FFD700; color: #441012; border-radius: 6px; text-decoration: none; font-weight: bold;">View Profile</a>
    </div>
  <?php endwhile; ?>
</div>

<script>
  function toggleMenu() {
    document.getElementById("navLinks").classList.toggle("active");
  }
</script>

</body>
</html>
