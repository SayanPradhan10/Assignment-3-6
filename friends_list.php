<?php
session_start();
include "db_connection.php"; 
$userid = $_SESSION['userid'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Friends</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1faee;
            padding: 40px;
            color: #1b4332;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background-color: #d8f3dc;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #1b4332;
        }

        .btn {
            display: inline-block;
            margin: 5px 8px 15px 0;
            background-color: #40916c;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #2d6a4f;
        }

        .btn.red {
            background-color: #d00000;
        }

        .btn.blue {
            background-color: #007BFF;
        }

        .btn.back {
            background-color: #52b788;
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #95d5b2;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        #result div,
        #my-friends div {
            padding: 10px;
            background-color: #ffffff;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid #b7e4c7;
        }

        hr {
            border: none;
            height: 1px;
            background-color: #95d5b2;
            margin: 30px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <button onclick="window.history.back()" class="btn back">⬅ Back</button>

        <h2>🔍 Search Users</h2>

      
        <a href="incoming_requests.php" class="btn blue">View Friend Requests</a>
        <a href="blocked_users.php" class="btn red">View Blocked Users</a>

        <input type="text" id="search" placeholder="Search by name or email">
        <div id="result"></div>

        <hr>

        <h2>👥 My Friends</h2>
        <div id="my-friends">
            <?php
            $stmt = $con->prepare("
                SELECT u.id, u.name, u.email
                FROM friends f
                JOIN users u ON (
                    (u.id = f.sender_id AND f.receiver_id = ?)
                    OR
                    (u.id = f.receiver_id AND f.sender_id = ?)
                )
                WHERE f.status = 'accepted'
                AND u.id != ?
            ");
            $stmt->bind_param("iii", $userid, $userid, $userid);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    echo "<div>
                        <strong>" . htmlspecialchars($row['name']) . "</strong> (" . htmlspecialchars($row['email']) . ")
                    </div>";
                }
            } else {
                echo "<p>You have no friends yet.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#search').on("keyup", function() {
                let query = $(this).val();
                if (query.length > 0) {
                    $.post("find_users.php", {
                        query: query
                    }, function(data) {
                        $("#result").html(data);
                    });
                } else {
                    $("#result").html("");
                }
            });

            $(document).on("click", ".send-request", function() {
                let to_id = $(this).data("id");
                $.post("friend_request_send.php", {
                    to_id: to_id
                }, function(response) {
                    alert(response);
                });
            });
        });
    </script>
</body>

</html>
