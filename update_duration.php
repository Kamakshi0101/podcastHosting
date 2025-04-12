<?php
include 'db.php';

if (isset($_POST['id']) && isset($_POST['duration'])) {
    $id = (int)$_POST['id'];
    $duration = (int)$_POST['duration'];
    
    $sql = "UPDATE episodes SET duration = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $duration, $id);
    $stmt->execute();
}