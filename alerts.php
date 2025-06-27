<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid'])) {
    exit("Unauthorized access.");
}

$userid = $_SESSION['userid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
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

        h3 {
            color: #1b4332;
            border-bottom: 2px solid #95d5b2;
            padding-bottom: 6px;
            margin-top: 30px;
        }

        .notification-card {
            background-color: #d8f3dc;
            border-left: 6px solid #40916c;
            margin: 15px 0;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .post-notification-card {
            background-color: #fff3cd;
            border-left: 6px solid #ffb703;
            margin: 15px 0;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .notification-card strong,
        .post-notification-card strong {
            color: #081c15;
        }

        .notification-card small,
        .post-notification-card small {
            color: #495057;
        }

        .no-alerts {
            color: #6c757d;
            font-size: 16px;
            text-align: center;
            margin-top: 20px;
        }

        hr {
            margin: 40px 0 10px;
            border: none;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

<a href="home_page.php" class="back-button">🔙 Back</a>

<?php

echo "<h3>🔔 General Notifications</h3>";

$stmt = $con->prepare("SELECT message, timestamp FROM notifications WHERE user_id = ? AND seen = FALSE ORDER BY timestamp DESC");
if (!$stmt) {
    die("Prepare failed for general notifications: " . $con->error);
}
$stmt->bind_param("i", $userid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "<div class='notification-card'>
                <strong>" . htmlspecialchars($row['message']) . "</strong><br>
                <small>" . htmlspecialchars($row['timestamp']) . "</small>
              </div>";
    }
} else {
    echo "<div class='no-alerts'>No general notifications.</div>";
}
$stmt->close();


$stmtSeen = $con->prepare("UPDATE notifications SET seen = TRUE WHERE user_id = ?");
$stmtSeen->bind_param("i", $userid);
$stmtSeen->execute();
$stmtSeen->close();


echo "<hr><h3>📝 Post Notifications from Friends</h3>";

$stmt2 = $con->prepare("
    SELECT u.name, p.content, p.timestamp
    FROM post_notifications pn
    JOIN posts p ON pn.post_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE pn.user_id = ? AND pn.seen = FALSE
    ORDER BY p.timestamp DESC
");
if (!$stmt2) {
    die("Prepare failed for post notifications: " . $con->error);
}
$stmt2->bind_param("i", $userid);
$stmt2->execute();
$res2 = $stmt2->get_result();

if ($res2->num_rows > 0) {
    while ($row = $res2->fetch_assoc()) {
        echo "<div class='post-notification-card'>
                <strong>" . htmlspecialchars($row['name']) . " posted:</strong><br>
                <em>" . htmlspecialchars($row['content']) . "</em><br>
                <small>" . htmlspecialchars($row['timestamp']) . "</small>
              </div>";
    }
} else {
    echo "<div class='no-alerts'>No new post notifications.</div>";
}
$stmt2->close();


$stmtSeen2 = $con->prepare("UPDATE post_notifications SET seen = TRUE WHERE user_id = ?");
$stmtSeen2->bind_param("i", $userid);
$stmtSeen2->execute();
$stmtSeen2->close();
?>

</body>
</html>
