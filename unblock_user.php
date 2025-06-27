<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid'])) {
    echo "Error: You are not logged in.";
    exit;
}

$userid = (int)$_SESSION['userid'];
$blocked_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;


if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "Error: Invalid CSRF token.";
    exit;
}


if ($blocked_user_id <= 0 || $blocked_user_id === $userid) {
    echo "Error: Invalid user ID.";
    exit;
}


$stmt = $con->prepare("
    SELECT id FROM friends 
    WHERE sender_id = ? AND receiver_id = ? AND status = 'blocked'
");
$stmt->bind_param("ii", $userid, $blocked_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Error: You haven't blocked this user.";
    exit;
}


$deleteStmt = $con->prepare("
    DELETE FROM friends 
    WHERE sender_id = ? AND receiver_id = ? AND status = 'blocked'
");
$deleteStmt->bind_param("ii", $userid, $blocked_user_id);

if ($deleteStmt->execute()) {
    echo "User unblocked successfully.";
} else {
    echo "Error: Failed to unblock user.";
}
?>
