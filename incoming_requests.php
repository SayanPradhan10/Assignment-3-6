<?php
session_start();
include "db_connection.php";
$userid = $_SESSION['userid'];

$stmt = $con->prepare("
    SELECT u.id, u.name, u.email
    FROM friends f
    JOIN users u ON u.id = f.sender_id
    WHERE f.receiver_id = ? AND f.status = 'pending'
");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Friend Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1faee;
            margin: 0;
            padding: 60px 20px 20px;
            color: #1b4332;
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
            margin-bottom: 30px;
            color: #2d6a4f;
        }

        .request-card {
            background-color: #d8f3dc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .request-card strong {
            display: block;
            font-size: 18px;
            color: #1b4332;
        }

        .request-card small {
            display: block;
            margin-bottom: 15px;
            color: #555;
        }

        .request-card button {
            background-color: #40916c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            margin-right: 10px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .request-card button:hover {
            background-color: #2d6a4f;
        }

        .no-requests {
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>

<a href="home_page.php" class="back-button">🔙 Back</a>

<h2>👥 Friend Requests</h2>

<?php if (count($requests) > 0): ?>
    <?php foreach ($requests as $row): ?>
        <div class="request-card">
            <strong><?= htmlspecialchars($row['name']) ?></strong>
            <small><?= htmlspecialchars($row['email']) ?></small>
            <button class="accept" data-id="<?= $row['id'] ?>">✅ Accept</button>
            <button class="block" data-id="<?= $row['id'] ?>">🚫 Block</button>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="no-requests">No pending friend requests.</p>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".accept, .block").forEach(button => {
        button.addEventListener("click", function () {
            const from_id = this.dataset.id;
            const action = this.classList.contains("accept") ? "accept" : "block";

            fetch("request_actions.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `from_id=${from_id}&action=${action}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                this.closest(".request-card").innerHTML = `<strong>✅ ${data}</strong>`;
            })
            .catch(error => {
                alert("An error occurred.");
                console.error(error);
            });
        });
    });
});
</script>

</body>
</html>
