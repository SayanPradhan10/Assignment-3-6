<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html");
    exit;
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$userid = (int)$_SESSION['userid'];

$stmt = $con->prepare("
    SELECT u.id, u.name, u.email, u.profile_pic,
           CASE 
               WHEN f.sender_id = ? THEN 'you_blocked' 
               ELSE 'they_blocked' 
           END AS block_direction
    FROM friends f
    JOIN users u ON (u.id = f.receiver_id OR u.id = f.sender_id)
    WHERE (f.sender_id = ? OR f.receiver_id = ?) 
    AND f.status = 'blocked'
    AND u.id != ?
");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("iiii", $userid, $userid, $userid, $userid);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blocked Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f1faee;
            padding: 60px 20px 20px;
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
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #2d6a4f;
        }

        h2 {
            text-align: center;
            color: #2d6a4f;
            margin-bottom: 30px;
        }

        .user-card {
            background-color: #d8f3dc;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .user-left {
            display: flex;
            align-items: center;
        }

        .profile-pic {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #40916c;
        }

        .user-info {
            color: #1b4332;
        }

        .user-info strong {
            font-size: 16px;
        }

        .user-info div {
            font-size: 14px;
            color: #555;
        }

        .unblock-btn {
            background-color: #40916c;
            color: white;
            border: none;
            padding: 8px 14px;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .unblock-btn:hover {
            background-color: #2d6a4f;
        }

        .no-blocked {
            text-align: center;
            color: #777;
            font-size: 16px;
        }
    </style>
</head>
<body>

<a href="home_page.php" class="back-button">🔙 Back</a>

<h2>🚫 Blocked Users</h2>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="user-card">
            <div class="user-left">
                <img src="user_uploads/<?= htmlspecialchars($row['profile_pic']) ?>" class="profile-pic">
                <div class="user-info">
                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                    <div><?= htmlspecialchars($row['email']) ?></div>
                    <div><?= $row['block_direction'] === 'you_blocked' ? 'You blocked this user' : 'This user blocked you' ?></div>
                </div>
            </div>
            <?php if ($row['block_direction'] === 'you_blocked'): ?>
                <button class="unblock-btn" data-id="<?= $row['id'] ?>">Unblock</button>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="no-blocked">No blocked users found.</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    $(".unblock-btn").on("click", function() {
        let $button = $(this);
        $button.prop("disabled", true).text("Unblocking...");
        if (confirm("Are you sure you want to unblock this user?")) {
            let userId = $button.data("id");
            let csrfToken = $("#csrf_token").val();

            if (!userId || !csrfToken) {
                alert("Error: Missing user ID or CSRF token.");
                $button.prop("disabled", false).text("Unblock");
                return;
            }

            $.post("unblock_user.php", {
                user_id: userId,
                csrf_token: csrfToken
            }, function(res) {
                alert(res);
                if (res === "User unblocked successfully.") {
                    $button.closest(".user-card").remove();
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX error: " + textStatus + " - " + errorThrown);
            }).always(function() {
                $button.prop("disabled", false).text("Unblock");
            });
        } else {
            $button.prop("disabled", false).text("Unblock");
        }
    });
});
</script>

</body>
</html>
