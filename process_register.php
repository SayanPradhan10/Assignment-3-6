<?php
include "db_connection.php"; 
session_start();

function validatePassword($password) {
    return preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $password);
}

function showMessage($message, $success = false) {
    $bg = $success ? "#e8f5e9" : "#fff3f3";
    $color = $success ? "#2e7d32" : "#c0392b";
    $border = $success ? "#66bb6a" : "#e74c3c";

    echo <<<HTML
    <div class="message-box">
        <p>$message</p>
        <a href="signup_page.html" class="back-btn">Go Back</a>
    </div>
    <style>
        .message-box {
            font-family: Arial, sans-serif;
            background-color: $bg;
            color: $color;
            border: 1px solid $border;
            padding: 20px;
            margin: 100px auto;
            width: 350px;
            text-align: center;
            border-radius: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #388e3c;
        }
    </style>
    HTML;
    exit;
}

if (isset($_POST['register'])) {
    $name  = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (!$email || !$name || !$phone || !$password || !$confirm) {
        showMessage("All fields are required.");
    }

    if (!validatePassword($password)) {
        showMessage("Password must be at least 8 characters, contain a capital letter and a number.");
    }

    if ($password !== $confirm) {
        showMessage("Passwords do not match.");
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $con->prepare("INSERT INTO users(name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

    if ($stmt->execute()) {
        showMessage("Registration successful!", true);
    } else {
        showMessage("Error: " . $stmt->error);
    }
}
?>
