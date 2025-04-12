       <?php
// Database Initialization Script
// This script will automatically create all the tables needed for the podcast application

// Display all errors for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db.php';

// Add some basic styling for better readability
echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Initialization</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        h2 { color: #555; }
        .success { color: green; font-weight: bold; }
        .error { color: red; }
        .warning { color: orange; font-weight: bold; }
        .table-name { font-weight: bold; }
    </style>
</head>
<body>";

echo "<h1>Database Initialization</h1>";
echo "<p>Starting table creation process...</p>";

// Function to execute SQL and handle errors
function executeSQL($conn, $sql, $tableName) {
    echo "<p>Creating table: <span class='table-name'>$tableName</span>... ";
    
    if ($conn->query($sql) === TRUE) {
        echo "<span class='success'>SUCCESS</span></p>";
        return true;
    } else {
        echo "<span class='error'>ERROR: " . $conn->error . "</span></p>";
        return false;
    }
}

// Array of SQL statements for table creation
$sqlStatements = [
    // Users table
    [
        'name' => 'users',
        'sql' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            profile_picture VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ],
    
    // Podcasts table
    [
        'name' => 'podcasts',
        'sql' => "CREATE TABLE IF NOT EXISTS podcasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            cover_image VARCHAR(255),
            category VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ],
    
    // Episodes table
    [
        'name' => 'episodes',
        'sql' => "CREATE TABLE IF NOT EXISTS episodes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            audio_file VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            category VARCHAR(50),
            duration INT,
            status TINYINT DEFAULT 0,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            thumbnail VARCHAR(255),
            latitude DECIMAL(10,8),
            longitude DECIMAL(11,8)
        )"
    ],
    
    // Schedule table
    [
        'name' => 'podcast_schedules',
        'sql' => "CREATE TABLE IF NOT EXISTS podcast_schedules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            host_name VARCHAR(100) NOT NULL,
            schedule_date DATE NOT NULL,
            schedule_time TIME NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ],
    
    // Likes table
    [
        'name' => 'likes',
        'sql' => "CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            episode_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_like (user_id, episode_id)
        )"
    ],
    
    // Comments table
    [
        'name' => 'comments',
        'sql' => "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            episode_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE
        )"
    ],
    
    // Analytics table
    [
        'name' => 'analytics',
        'sql' => "CREATE TABLE IF NOT EXISTS analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            episode_id INT NOT NULL,
            plays INT DEFAULT 0,
            unique_listeners INT DEFAULT 0,
            average_duration INT DEFAULT 0,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE
        )"
    ]
];

// Execute each SQL statement
$successCount = 0;
$totalTables = count($sqlStatements);

foreach ($sqlStatements as $statement) {
    if (executeSQL($conn, $statement['sql'], $statement['name'])) {
        $successCount++;
    }
}

// Summary
echo "<h2>Initialization Summary</h2>";
echo "<p>Successfully created $successCount out of $totalTables tables.</p>";

if ($successCount === $totalTables) {
    echo "<p class='success'>Database initialization completed successfully!</p>";
} else {
    echo "<p class='warning'>Database initialization completed with some errors. Please check the messages above.</p>";
}

echo "</body>
</html>";

// Close connection
$conn->close();
?>