<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html");
    exit;
}

$user_id = $_SESSION['userid'];


$stmt = $con->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f1faee;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #40916c;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #2d6a4f;
        }

        .profile-container {
            background-color: #d8f3dc;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
            width: 90%;
            max-width: 500px;
        }

        .profile-container h2 {
            color: #2d6a4f;
            margin-bottom: 20px;
        }

        img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 4px solid #40916c;
            margin-bottom: 15px;
        }

        .profile-info {
            font-size: 20px;
            font-weight: 600;
            color: #1b4332;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
            color: #2d6a4f;
        }

        input[type="file"] {
            margin-bottom: 10px;
            border: none;
        }

        button {
            background-color: #40916c;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2d6a4f;
        }

        #uploadResult {
            margin-top: 15px;
            font-weight: 500;
        }

        form {
            margin-top: 20px;
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


<a href="home_page.php" class="back-button">🔙 Back</a>

<div class="profile-container">
    <h2>👤 My Profile</h2>
    <img src="user_uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" id="profilePic" alt="Profile Picture">
    
    <div class="profile-info">
        <?php echo htmlspecialchars($user['name']); ?>
    </div>

    <form id="profilePicForm" enctype="multipart/form-data">
        <label for="profileInput">Change Profile Picture:</label>
        <input type="file" name="profile_pic" id="profileInput" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <div id="uploadResult"></div>

    <hr>

   
</div>

<script>
document.getElementById("profilePicForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = document.getElementById("profilePicForm");
    const formData = new FormData(form);

    fetch("profile_upload.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const resultDiv = document.getElementById("uploadResult");

        if (data.status === "success") {
            const img = document.getElementById("profilePic");
            img.src = "user_uploads/" + data.file + "?t=" + new Date().getTime();

            resultDiv.style.color = "#2d6a4f";
            resultDiv.textContent = "✅ Profile picture updated successfully!";
        } else {
            resultDiv.style.color = "red";
            resultDiv.textContent = data.message || "❌ Upload failed.";
        }
    })
    .catch(() => {
        const resultDiv = document.getElementById("uploadResult");
        resultDiv.style.color = "red";
        resultDiv.textContent = "❌ Upload failed.";
    });
});
</script>

</body>
</html>
