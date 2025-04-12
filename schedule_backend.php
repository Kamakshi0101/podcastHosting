<?php
include __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];

    if ($action == "add") {
        $title = $_POST["title"];
        $start = $_POST["start"];
        $end = $_POST["end"];

        if (!empty($title) && !empty($start) && !empty($end)) {
            $stmt = $conn->prepare("INSERT INTO schedule (title, start_datetime, end_datetime) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $start, $end);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "id" => $stmt->insert_id]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to insert event."]);
            }
        }
    }

    if ($action == "update") {
        $id = $_POST["id"];
        $start = $_POST["start"];
        $end = $_POST["end"];

        if (!empty($id) && !empty($start) && !empty($end)) {
            $stmt = $conn->prepare("UPDATE schedule SET start_datetime = ?, end_datetime = ? WHERE id = ?");
            $stmt->bind_param("ssi", $start, $end, $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            }
        }
    }

    if ($action == "delete") {
        $id = $_POST["id"];

        if (!empty($id)) {
            $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            }
        }
    }
}

$conn->close();
?>
