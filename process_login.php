<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 3) {
    echo <<<HTML
    <div class="message-box">
        <p>Too many failed attempts. Try again later.</p>
        <a href="user_login_form.html" class="back-btn">Go Back</a>
    </div>
    <style>
        .message-box {
            font-family: Arial, sans-serif;
            background-color: #fff3f3;
            color: #c0392b;
            border: 1px solid #e74c3c;
            padding: 20px;
            margin: 100px auto;
            width: 300px;
            text-align: center;
            border-radius: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color: #388e3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color: #388e3c;
        }
    </style>
    HTML;
    exit;
}

if (isset($_POST['login'])) {
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];
    $login_id = htmlspecialchars($login_id);

    $stmt = $con->prepare("SELECT * FROM users WHERE email=? OR phone=?");
    $stmt->bind_param("ss", $login_id, $login_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['login_attempts'] = 0;

            header("Location: home_page.php");
            exit;
        }
    }

    $_SESSION['login_attempts'] += 1;

    echo <<<HTML
    <div class="message-box">
        <p>Invalid credentials. Attempt: {$_SESSION['login_attempts']}</p>
        <a href="user_login_form.html" class="back-btn">Go Back</a>
    </div>
    <style>
        .message-box {
            font-family: Arial, sans-serif;
            background-color: #fff3f3;
            color: #c0392b;
            border: 1px solid #e74c3c;
            padding: 20px;
            margin: 100px auto;
            width: 300px;
            text-align: center;
            border-radius: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 16px;
            background-color:  #388e3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background-color:  #388e3c;
        }
    </style>
    HTML;
}
?>
