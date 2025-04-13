<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['name']) || !isset($data['latitude']) || !isset($data['longitude'])) {
    http_response_code(400);
    exit('Missing required fields');
}

// Prepare and execute the insert statement
$stmt = $conn->prepare("INSERT INTO ngo_markers (user_id, name, description, latitude, longitude, category, contact_info, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issddsss", 
    $_SESSION['user_id'],
    $data['name'],
    $data['description'],
    $data['latitude'],
    $data['longitude'],
    $data['category'],
    $data['contact_info'],
    $data['website']
);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'NGO marker saved successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save NGO marker']);
}

$stmt->close();
$conn->close();