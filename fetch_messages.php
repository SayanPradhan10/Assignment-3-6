<?php
session_start();
include "db_connection.php";

if (!isset($_SESSION['userid']) || !isset($_POST['friend_id']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo "Unauthorized request";
    exit;
}

$user_id = $_SESSION['userid'];
$friend_id = intval($_POST['friend_id']);

$stmt = $con->prepare("
    SELECT 1 FROM friends 
    WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
    AND status = 'accepted'
");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    http_response_code(403);
    echo "You are not friends with this user.";
    exit;
}

$stmt = $con->prepare("
    SELECT sender_id, message, timestamp 
    FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY timestamp ASC
");
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $isMine = $row['sender_id'] == $user_id;
    echo '<div style="text-align:' . ($isMine ? 'right' : 'left') . ';margin:5px 0">';
    echo '<span style="background:' . ($isMine ? '#dcf8c6' : '#eee') . ';padding:5px 10px;border-radius:10px;">';
    echo htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
    echo ' <small style="font-size:10px;color:#888;">' . $row['timestamp'] . '</small>';
    echo '</span></div>';
}

$stmt->close();
$con->close();
?>
