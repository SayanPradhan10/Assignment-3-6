<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html");
    exit;
}

$userid = $_SESSION['userid'];
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

$stmt = $con->prepare("
    SELECT u.id, u.name 
    FROM users u 
    WHERE u.id IN (
        SELECT CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END
        FROM friends 
        WHERE (sender_id = ? OR receiver_id = ?) AND status = 'accepted'
    )
");
$stmt->bind_param("iii", $userid, $userid, $userid);
$stmt->execute();
$friends = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Green Chat</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1faee;
            padding: 40px;
            color: #1b4332;
        }

        .chat-container {
            max-width: 700px;
            margin: auto;
            background-color: #d8f3dc;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h3 {
            margin-bottom: 20px;
            color: #1b4332;
        }

        .back-button {
            padding: 10px 20px;
            background-color: #52b788;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
        }

        select, input[type="text"] {
            font-family: 'Poppins', sans-serif;
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 2px solid #95d5b2;
            border-radius: 8px;
            font-size: 16px;
            background-color: #ffffff;
        }

        #chat-box {
            border: 2px solid #95d5b2;
            border-radius: 8px;
            height: 300px;
            overflow-y: auto;
            padding: 15px;
            background-color: #ffffff;
            margin-bottom: 15px;
        }

        #loading {
            text-align: center;
            color: #2d6a4f;
            display: none;
            font-size: 14px;
        }

        #error {
            color: red;
            display: none;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #40916c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2d6a4f;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

<div class="chat-container">
    <h3>💬 Select a Friend to Chat</h3>

    <button class="back-button" onclick="window.history.back()">⬅ Back</button>

    <select id="friend_id">
        <option value="">-- Select Friend --</option>
        <?php while ($f = $friends->fetch_assoc()): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endwhile; ?>
    </select>

    <div id="chat-box"></div>
    <div id="loading">Loading messages...</div>
    <div id="error"></div>

    <input type="hidden" id="csrf_token" value="<?= $csrf_token ?>">
    <input type="text" id="message" placeholder="Type a message..." maxlength="1000">
    <button id="send">Send</button>
</div>

<script>
let interval;

function showError(message) {
    $("#error").text(message).show();
    setTimeout(() => $("#error").hide(), 5000);
}

function loadMessages(friendId) {
    $("#loading").show();
    $.post("fetch_messages.php", { friend_id: friendId, csrf_token: $("#csrf_token").val() })
        .done(function(data) {
            const chatBox = $("#chat-box")[0];
            const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 100;

            $("#chat-box").html(data);

            if (isAtBottom) {
                $("#chat-box").scrollTop(chatBox.scrollHeight);
            }
        })
        .fail(() => showError("Failed to load messages. Please try again."))
        .always(() => $("#loading").hide());
}

$("#friend_id").change(function() {
    clearInterval(interval);
    let friendId = $(this).val();
    if (friendId) {
        loadMessages(friendId);
        interval = setInterval(() => loadMessages(friendId), 3000);
    } else {
        $("#chat-box").html("");
        $("#loading").hide();
    }
});

$("#send").click(function() {
    let friendId = $("#friend_id").val();
    let msg = $("#message").val().trim();
    let csrfToken = $("#csrf_token").val();
    let $sendBtn = $(this);

    if (!friendId) return showError("Please select a friend.");
    if (!msg) return showError("Please enter a message.");
    if ($sendBtn.prop('disabled')) return;

    $sendBtn.prop('disabled', true);
    $.post("message_submit.php", { friend_id: friendId, message: msg, csrf_token: csrfToken }, null, "json")
        .done(function(response) {
            if (response.success) {
                $("#message").val("");
                loadMessages(friendId);
            } else {
                showError(response.error || "Failed to send message.");
            }
        })
        .fail(() => showError("Failed to send message. Please try again."))
        .always(() => $sendBtn.prop('disabled', false));
});

$("#message").keypress(function(e) {
    if (e.which === 13) {
        $("#send").click();
    }
});

$(window).on('unload', function() {
    clearInterval(interval);
});
</script>

</body>
</html>
