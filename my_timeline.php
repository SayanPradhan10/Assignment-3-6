<?php
session_start();
include "db_connection.php"; 

if (!isset($_SESSION['userid'])) {
    header("Location: user_login_form.html"); 
    exit;
}

$user_id = $_SESSION['userid'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_id'])) {
    $delete_id = intval($_POST['delete_post_id']);

   
    $delete_stmt = $con->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $delete_id, $user_id);
    $delete_stmt->execute();
}


$stmt = $con->prepare("
    SELECT posts.*, users.name 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id = ? 
    ORDER BY posts.timestamp DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Posts</title>
</head>
<body>

    <h2>My Posts</h2>
    <a href="home_page.php"><button>⬅ Go Back</button></a> 

    <?php
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>
            <strong>{$row['name']}</strong> <em>{$row['timestamp']}</em><br>
            {$row['content']}
            <form method='POST' onsubmit='return confirm(\"Are you sure you want to delete this post?\");'>
                <input type='hidden' name='delete_post_id' value='{$row['id']}'>
                <button type='submit' style='margin-top: 10px; color: white; background-color: red; border: none; padding: 5px 10px;'>🗑 Delete</button>
            </form>
        </div>";
    }
    ?>

</body>
</html>
