<?php
$conn = mysqli_connect("localhost", "root", "", "Podcast_hosting");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>