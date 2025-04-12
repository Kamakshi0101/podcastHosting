<?php
header('Content-Type: application/json');
session_start();
include 'db.php';

// Initialize response array
$response = ['status' => 'error', 'message' => ''];

// Check if episode_id is provided
if (!isset($_POST['episode_id']) || empty($_POST['episode_id'])) {
    $response['message'] = 'Episode ID is required';
    echo json_encode($response);
    exit;
}

$episode_id = intval($_POST['episode_id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // 0 for anonymous users

// Validate episode exists
$check_episode = $conn->prepare("SELECT id FROM episodes WHERE id = ?");
$check_episode->bind_param('i', $episode_id);
$check_episode->execute();
$episode_result = $check_episode->get_result();

if ($episode_result->num_rows === 0) {
    $response['message'] = 'Episode not found';
    echo json_encode($response);
    exit;
}

try {
    // Insert play record
    $stmt = $conn->prepare("INSERT INTO plays (episode_id, user_id, play_timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param('ii', $episode_id, $user_id);
    
    if ($stmt->execute()) {
        // Update plays count in episodes table if it exists
        $update_plays = $conn->prepare("UPDATE episodes SET plays = IFNULL(plays, 0) + 1 WHERE id = ?");
        $update_plays->bind_param('i', $episode_id);
        $update_plays->execute();
        
        $response = [
            'status' => 'success',
            'message' => 'Play recorded successfully'
        ];
    } else {
        $response['message'] = 'Failed to record play: ' . $stmt->error;
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);