# 🎬 Movie Zone

Movie Zone is a full-stack web platform for movie lovers and critics.\
It allows users to sign up, log in, post reviews, browse others'
reviews, and manage their profiles.\
Additionally, it includes an **AI Movie Recommendation System**
(form-based + chatbot) powered by Python.

------------------------------------------------------------------------

## 🚀 Features

-   ✅ User Authentication (Sign Up with email verification, Sign In,
    Logout)\
-   ✅ Profile Page (view & edit user info)\
-   ✅ Movies Page (add reviews, rate movies, mark favorites,
    like/unlike reviews)\
-   ✅ View Other Users' Profiles\
-   ✅ AI Recommendation System
    -   Form-based movie recommendations (choose genre, mood, release
        type, etc.)\
    -   Chatbot mode (ask AI directly for suggestions)

------------------------------------------------------------------------

## 🛠️ Requirements

Make sure you have these installed: -
[XAMPP](https://www.apachefriends.org/) (Apache + MySQL) - [Python
(latest)](https://www.python.org/downloads/) - [VS
Code](https://code.visualstudio.com/)

------------------------------------------------------------------------

## ⚡ Setup Instructions

### 1. Database Setup

1.  Start **XAMPP** → turn on **Apache** & **MySQL**\
2.  Go to `http://localhost/phpmyadmin/`\
3.  Import the file `movie_zone.sql` (database schema)\
    → This will create the `movie_zone` database.

------------------------------------------------------------------------

### 2. Project Files

1.  Download the **Movie Zone** project folder.\

2.  Paste it inside:

        C:\xampp\htdocs\

3.  Open the folder in **VS Code**.

------------------------------------------------------------------------

### 3. Email Verification Setup

1.  In the project folder, open `send_otp.php`.\

2.  Find these lines:

    ``` php
    $mail->Username   = '';   // ✅ Your Gmail address
    $mail->Password   = '';   // ✅ Gmail App Password
    ```

3.  Add your Gmail & App Password here.

📌 To generate a Gmail App Password: - Go to [Google
Account](https://myaccount.google.com/) → Security\
- Enable **2-Step Verification**\
- Open **App Passwords** → Generate new password\
- Copy the 16-digit password → Paste it in `send_otp.php`.

------------------------------------------------------------------------

### 4. AI Recommendation System Setup

1.  Open **VS Code Terminal**\

2.  Navigate to the AI folder:

    ``` bash
    cd "C:\xampp\htdocs\Movie Zone\ai_movie_recommendation"
    ```

3.  Run Flask app:

    ``` bash
    python app.py
    ```

4.  This will start the AI server at:

        http://127.0.0.1:5000

------------------------------------------------------------------------

### 5. Run the Website

1.  Open browser →

        http://localhost/Movie%20Zone/index.php

2.  Sign up with your email → check **Inbox/Spam** for OTP code\

3.  After signup → log in and explore the site 🎉

------------------------------------------------------------------------

## 🎯 Project Links

-   Main Website: [Movie Zone](https://moviezone.wuaze.com)\
-   AI Recommendation Demo: [PythonAnywhere Hosted
    AI](https://raafeey.pythonanywhere.com)

------------------------------------------------------------------------

## 📌 Note

-   Use the README step-by-step to run the project.\
-   If any issue occurs with Gmail App Password → check Google's help
    docs.\
-   AI model used: `mistralai/Mixtral-8x7B-Instruct-v0.1` (via Together
    API).

------------------------------------------------------------------------

💡 This project is open source --- feel free to explore, contribute, and
improve! 🚀
