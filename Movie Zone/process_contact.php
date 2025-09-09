<?php
// Enable error reporting (for debugging on hosting)
error_reporting(E_ALL);
ini_set('display_errors', 1);


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movie_zone";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = $conn->real_escape_string($_POST['name'] ?? '');
    $email   = $conn->real_escape_string($_POST['email'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');

    // Simple validation
    if ($name && $email && $message) {
        $sql = "INSERT INTO contacts (name, email, message) VALUES ('$name', '$email', '$message')";
        if ($conn->query($sql) === TRUE) {
            echo "<h2 style='text-align:center; color: #4CAF50;'>Message Sent Successfully!</h2>";
        } else {
            echo "<h2 style='text-align:center; color: red;'>Database Error: " . $conn->error . "</h2>";
        }
    } else {
        echo "<h2 style='text-align:center; color: orange;'>All fields are required.</h2>";
    }

    echo "<div style='text-align: center; margin-top: 20px;'>
            <a href='contact.html' style='padding: 10px 20px; background: #CE851E; color: #441012; text-decoration: none; border-radius: 5px;'>⬅ Go Back</a>
          </div>";
} else {
    echo "<h2 style='text-align:center; color: red;'>Invalid Request Method</h2>";
}

$conn->close();
?>
