<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // First get the file path
    $query = "SELECT file_path FROM episodes WHERE id = '$id'";
    $result = mysqli_query($conn, $query);
    $episode = mysqli_fetch_assoc($result);
    
    if ($episode) {
        // Delete the file if it exists
        $file_path = 'uploads/' . $episode['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $delete_query = "DELETE FROM episodes WHERE id = '$id'";
        if (mysqli_query($conn, $delete_query)) {
            header('Location: myepisode.php');
            exit();
        } else {
            die("Error deleting episode: " . mysqli_error($conn));
        }
    }
}

header('Location: myepisode.php');
exit();
?>