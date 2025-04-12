<?php
include __DIR__ . '/db.php';

header('Content-Type: application/json');

$sql = "SELECT id, title, start_datetime as start, end_datetime as end FROM schedule";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
