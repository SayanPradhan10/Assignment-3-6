<?php
session_start();
include "db_connection.php"; 

if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html"); 
    exit;
}

$user_id = $_SESSION['userid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Feed</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 60px 20px 20px;
            background-color: #f1faee;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #40916c;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #2d6a4f;
        }

        .container {
            max-width: 700px;
            margin: auto;
        }

        h2 {
            color: #1b4332;
            text-align: center;
            margin-top: 0;
        }

        form {
            background: #d8f3dc;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #95d5b2;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            resize: vertical;
        }

        button {
            background-color: #40916c;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2d6a4f;
        }

        .filter-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .filter-buttons button {
            margin: 0 10px;
        }

        #postMessage {
            margin-top: 10px;
            font-weight: 500;
            color: #1b4332;
            text-align: center;
        }

        .post-card {
            background-color: #ffffff;
            border-left: 6px solid #40916c;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .post-card strong {
            font-size: 15px;
            color: #1b4332;
        }

        .post-card small {
            display: block;
            color: #777;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<a href="home_page.php" class="back-button">🔙 Back</a>

<div class="container">

    <h2>📝 Post Something</h2>
    <form id="postForm">
        <textarea name="content" placeholder="What's on your mind?" required rows="4"></textarea><br>
        <button type="submit">Post</button>
    </form>

    <div id="postMessage"></div>

    <hr>

    <h2>📢 Post Feed</h2>
    <div class="filter-buttons">
        <button onclick="loadPosts('all')">🌍 All Posts</button>
        <button onclick="loadPosts('friends')">👥 Friends' Posts</button>
    </div>

    <div id="postFeed"></div>

</div>

<script>
document.getElementById("postForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("post_submit.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("postMessage").innerText = data;
        document.getElementById("postForm").reset();
        loadPosts('all'); 
    });
});

function loadPosts(type) {
    fetch("fetch_posts.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "type=" + type
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("postFeed").innerHTML = data;
    });
}


loadPosts('all');
</script>

</body>
</html>
