<?php
include 'db.php';

$category = $_GET['category'] ?? '';

$query = "SELECT * FROM episodes";
if (!empty($category)) {
    $query .= " WHERE category = ?";
}
$query .= " ORDER BY upload_date DESC";

$stmt = $conn->prepare($query);
if (!empty($category)) {
    $stmt->bind_param('s', $category);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">';
        echo '    <div class="flex flex-col space-y-4">';
        if (!empty($row['thumbnail'])) {
            echo '        <img src="uploads/' . $row['thumbnail'] . '" alt="Podcast Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">';
        } else {
            echo '        <img src="https://placehold.co/600x400/2E2E4E/white?text=No+Thumbnail" alt="Default Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">';
        }
        echo '        <h3 class="text-xl font-semibold">' . htmlspecialchars($row['title']) . '</h3>';
        echo '        <p class="text-gray-400">' . htmlspecialchars($row['description']) . '</p>';
        echo '        <audio controls class="w-full">';
        echo '            <source src="uploads/' . $row['file_path'] . '" type="audio/mpeg">';
        echo '            Your browser does not support the audio element.';
        echo '        </audio>';
        echo '        <div class="text-sm text-gray-500">';
        echo '            ' . date('M j, Y', strtotime($row['upload_date']));
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
} else {
    echo '<div class="col-span-full text-center text-gray-400">No podcasts found in this category</div>';
}

$stmt->close();
$conn->close();
?>