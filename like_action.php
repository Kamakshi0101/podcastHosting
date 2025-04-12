<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

function handleDBError($conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$episode_id = $_POST['episode_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if already liked
    $check_sql = "SELECT * FROM likes WHERE user_id = $user_id AND episode_id = $episode_id";
    $result = mysqli_query($conn, $check_sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $sql = "DELETE FROM likes WHERE user_id = $user_id AND episode_id = $episode_id";
        $action = 'unliked';
    } else {
        $sql = "INSERT INTO likes (user_id, episode_id) VALUES ($user_id, $episode_id)";
        $action = 'liked';
    }
    if (!mysqli_query($conn, $sql)) {
        handleDBError($conn);
    }

    $count_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE episode_id = $episode_id";
    $count_result = mysqli_query($conn, $count_sql);
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $like_count = $count_row['like_count'];
    } else {
        $like_count = 0;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'action' => $action,
    'like_count' => $like_count,
    'is_liked' => $action === 'liked'
]);
?>