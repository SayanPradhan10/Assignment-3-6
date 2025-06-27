<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html");
    exit;
}

include "db_connection.php";
$userid = $_SESSION['userid'];

$stmt = $con->prepare("SELECT name, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f3f6f4;
        }

        .sidebar {
            width: 240px;
            background-color: #1b4332; 
            height: 100vh;
            color: white;
            padding-top: 30px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }

        .sidebar img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-top: 10px;
            object-fit: cover;
            border: 3px solid #95d5b2;
        }

        .sidebar h3 {
            margin: 15px 0 25px;
            font-size: 20px;
            color: #d8f3dc;
        }

        .sidebar a {
            display: block;
            color: #d8f3dc;
            padding: 12px 20px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #2d6a4f;
        }

        .content {
            flex-grow: 1;
            padding: 40px;
            background-color: #f1faee;
        }

        .content h1 {
            color: #1b4332;
            font-size: 32px;
        }

        hr {
            border: 0;
            height: 1px;
            background-color: #40916c;
            margin: 20px 0;
            width: 80%;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <center>
        <a href="profile_page.php">
            <img src="user_uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture">
            <h3><?= htmlspecialchars($user['name']) ?></h3>
        </a>
    </center>
    <hr>
    <a href="messages_view.php">💬 Chat</a>
    <a href="friends_list.php">👥 Friends</a>
    <a href="all_posts_feed.php">📝 Posts</a>
    <a href="alerts.php">🔔 Notifications</a>
    <a href="profile_page.php">👤 Profile</a>
    <a href="signout.php">🚪 Logout</a>
</div>

<div class="content">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
</div>

</body>
</html>
