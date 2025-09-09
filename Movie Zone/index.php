<?php
session_start();
$signup_error = $_SESSION['signup_error'] ?? '';
$signup_success = $_SESSION['signup_success'] ?? '';
$signin_error = $_SESSION['signin_error'] ?? '';
session_unset();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Movie Zone - Login / Signup</title>
  <style>
    :root {
      --bg-dark: #441012;
      --bg-light: #2e0c0e;
      --primary: #CE851E;
      --highlight: #FFD700;
      --text-light: #fff;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      background-color: var(--bg-dark);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--primary);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background-color: var(--bg-light);
      padding: 30px 25px;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 400px;
    }

    h1 {
      text-align: center;
      color: var(--highlight);
      margin-bottom: 25px;
    }

    .form-toggle {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .form-toggle button {
      background: none;
      border: none;
      color: var(--primary);
      font-size: 16px;
      margin: 0 15px;
      padding: 8px 12px;
      cursor: pointer;
      transition: 0.3s;
      border-bottom: 2px solid transparent;
    }

    .form-toggle button.active {
      color: var(--highlight);
      border-bottom: 2px solid var(--highlight);
    }

    .form {
      display: none;
      flex-direction: column;
      gap: 15px;
      animation: fadeIn 0.3s ease;
    }

    .form.active { display: flex; }

    input, textarea {
      padding: 10px;
      border-radius: 6px;
      border: 1px solid var(--primary);
      background-color: var(--bg-dark);
      color: var(--text-light);
      font-size: 15px;
    }

    input::placeholder, textarea::placeholder {
      color: #aaa;
    }

    textarea {
      resize: vertical;
    }

    .checkbox-row {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--text-light);
    }

    button[type="submit"], button[type="button"] {
      background-color: var(--primary);
      color: var(--bg-dark);
      border: none;
      padding: 10px;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background-color: var(--highlight);
    }

    .error {
      color: red;
      font-size: 14px;
    }

    .success {
      color: lightgreen;
      font-size: 14px;
    }

    .section-label {
      font-size: 14px;
      color: var(--text-light);
      margin-top: -5px;
      margin-bottom: -10px;
    }

    #verify-status {
      font-size: 14px;
      font-weight: bold;
    }

    #otp-timer {
      font-size: 13px;
      color: #bbb;
    }

    button[disabled] {
      background-color: #999 !important;
      cursor: not-allowed !important;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @media (max-width: 480px) {
      .container { padding: 20px; }
      h1 { font-size: 22px; }
      .form-toggle button {
        font-size: 14px;
        margin: 0 8px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <h1>🎬 Movie Zone</h1>

  <div class="form-toggle">
    <button id="login-btn" class="active">Sign In</button>
    <button id="signup-btn">Sign Up</button>
  </div>

  <!-- Sign In Form -->
  <form id="login-form" class="form active" method="POST" action="process_signpin.php">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" id="login-password" required>
    <div class="checkbox-row">
      <input type="checkbox" id="show-login-password">
      <label for="show-login-password">Show Password</label>
    </div>
    <button type="submit" name="signin">Sign In</button>
    <p class="error"><?php echo $signin_error; ?></p>
  </form>
  <!-- Sign Up Form -->
  <form id="signup-form" class="form" method="POST" action="process_signpin.php" enctype="multipart/form-data" onsubmit="return checkVerification();">
    <input type="text" name="full_name" placeholder="Full Name" required>

    <!-- Email + Send OTP -->
    <input type="email" name="email" id="email" placeholder="Enter your Gmail" required>
    <button type="button" onclick="sendOTP()" id="send-otp-btn">Send Verification Code</button>
    <p id="otp-timer"></p>

    <!-- OTP Input -->
    <div id="otp-section" style="display: none;">
      <input type="text" id="otp-input" placeholder="Enter Verification Code">
      <button type="button" onclick="verifyOTP()">Verify Code</button>
      <p id="verify-status"></p>
    </div>

    <input type="hidden" name="email_verified" id="email_verified" value="false">

    <input type="password" name="password" placeholder="Password" id="signup-password" required>
    <div class="checkbox-row">
      <input type="checkbox" id="show-signup-password">
      <label for="show-signup-password">Show Password</label>
    </div>

    <label class="section-label">Upload Profile Picture (optional)</label>
    <input type="file" name="profile_pic" accept="image/*">

    <label class="section-label">About You (optional)</label>
    <textarea name="about" placeholder="Tell us something about yourself..." rows="3"></textarea>

    <button type="submit" name="signup">Sign Up</button>
    <p class="error"><?php echo $signup_error; ?></p>
    <p class="success"><?php echo $signup_success; ?></p>
  </form>
</div>

<script>
  const loginBtn = document.getElementById('login-btn');
  const signupBtn = document.getElementById('signup-btn');
  const loginForm = document.getElementById('login-form');
  const signupForm = document.getElementById('signup-form');

  loginBtn.addEventListener('click', () => {
    loginForm.classList.add('active');
    signupForm.classList.remove('active');
    loginBtn.classList.add('active');
    signupBtn.classList.remove('active');
  });

  signupBtn.addEventListener('click', () => {
    signupForm.classList.add('active');
    loginForm.classList.remove('active');
    signupBtn.classList.add('active');
    loginBtn.classList.remove('active');
  });

  document.getElementById('show-login-password').addEventListener('change', function () {
    document.getElementById('login-password').type = this.checked ? 'text' : 'password';
  });

  document.getElementById('show-signup-password').addEventListener('change', function () {
    document.getElementById('signup-password').type = this.checked ? 'text' : 'password';
  });

  let resendTimer;
  function sendOTP() {
    const email = document.getElementById('email').value;
    const btn = document.getElementById('send-otp-btn');
    const timer = document.getElementById('otp-timer');

    if (!email) {
      alert("Please enter your Gmail.");
      return;
    }

    btn.disabled = true;
    fetch('send_otp.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      if (data.status === 'success') {
        document.getElementById('otp-section').style.display = 'block';
        startOTPTimer(60); // 60 seconds timer
      } else {
        btn.disabled = false;
      }
    })
    .catch(() => {
      alert("Something went wrong");
      btn.disabled = false;
    });

    function startOTPTimer(seconds) {
      let time = seconds;
      timer.innerText = `⏳ Please wait ${time} seconds to resend`;
      resendTimer = setInterval(() => {
        time--;
        timer.innerText = `⏳ Please wait ${time} seconds to resend`;
        if (time <= 0) {
          clearInterval(resendTimer);
          btn.disabled = false;
          timer.innerText = '';
        }
      }, 1000);
    }
  }

  function verifyOTP() {
    const code = document.getElementById('otp-input').value;
    if (!code) {
      alert("Please enter the verification code");
      return;
    }

    fetch('verify_otp.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'code=' + encodeURIComponent(code)
    })
    .then(res => res.json())
    .then(data => {
      const status = document.getElementById('verify-status');
      if (data.status === 'success') {
        status.style.color = 'lightgreen';
        status.innerText = "✅ Email verified!";
        document.getElementById('email_verified').value = "true";
      } else {
        status.style.color = 'red';
        status.innerText = "❌ Incorrect code";
        document.getElementById('email_verified').value = "false";
      }
    });
  }

  function checkVerification() {
    if (document.getElementById('email_verified').value !== "true") {
      alert("Please verify your email before signing up.");
      return false;
    }
    return true;
  }
</script>

</body>
</html>
