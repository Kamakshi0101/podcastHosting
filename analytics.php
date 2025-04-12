<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Fetch dashboard images
$dashboard_images = [];
$stmt = $conn->prepare("SELECT * FROM dashboard_images WHERE user_id = ? ORDER BY display_order, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $dashboard_images[] = $row;
}

// Get total number of likes for user's episodes
$total_likes_query = "SELECT COUNT(*) as total_likes FROM likes l 
                      JOIN episodes e ON l.episode_id = e.id 
                      WHERE e.user_id = ?";
$stmt = $conn->prepare($total_likes_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$total_likes_result = $stmt->get_result()->fetch_assoc();
$total_likes = $total_likes_result['total_likes'];

// Get average likes per episode
$avg_likes_query = "SELECT AVG(like_count) as avg_likes FROM 
                    (SELECT e.id, COUNT(l.id) as like_count 
                     FROM episodes e 
                     LEFT JOIN likes l ON e.id = l.episode_id 
                     WHERE e.user_id = ? 
                     GROUP BY e.id) as episode_likes";
$stmt = $conn->prepare($avg_likes_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$avg_likes_result = $stmt->get_result()->fetch_assoc();
$avg_likes = round($avg_likes_result['avg_likes'], 2);

// Get episodes ranked by likes
$ranked_episodes_query = "SELECT e.description, COUNT(l.id) as like_count 
                         FROM episodes e 
                         LEFT JOIN likes l ON e.id = l.episode_id 
                         WHERE e.user_id = ? 
                         GROUP BY e.id 
                         ORDER BY like_count DESC";
$stmt = $conn->prepare($ranked_episodes_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$ranked_episodes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
            background: linear-gradient(180deg, #1a1625 0%, #2d1f3d 50%, #2d2442 100%);
        }
        .sidebar-hidden { transform: translateX(-100%); }
        .sidebar-visible { transform: translateX(0); }
        .toggle-moved {
            transform: translateX(16rem) translateY(-50%);
            transition: transform 0.3s ease-in-out;
        }
        .toggle-default {
            transform: translateX(0) translateY(-50%);
            transition: transform 0.3s ease-in-out;
        }
        .content-shifted {
            margin-left: 16rem;
            transition: margin-left 0.3s ease-in-out;
        }
        .content-full {
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
        }
        #sidebarToggle {
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-[#1E1E2E] text-white overflow-x-hidden">
    <div class="flex h-screen relative">
        <aside id="sidebar" class="sidebar w-64 text-white p-8 fixed h-full z-20 sidebar-visible">
            <div class="flex items-center gap-3 mb-8">
                <?php if (!empty($dashboard_images)): ?>
                    <img src="uploads/dashboard/<?php echo htmlspecialchars($dashboard_images[0]['image_path']); ?>" 
                         alt="Profile Image" 
                         class="w-12 h-12 rounded-full object-cover">
                <?php else: ?>
                    <span class="material-icons text-4xl">account_circle</span>
                <?php endif; ?>
                <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($full_name); ?></h1>
            </div>
            <nav>
                <ul class="space-y-6">
                    <li>
                        <a href="dashboard_index.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">home</span>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gradient-to-r from-[#4A1E73] to-[#D76D77]">
                            <span class="material-icons">analytics</span>
                            <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="schedule.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">calendar_today</span>
                            <span>Schedule</span>
                        </a>
                    </li>
                    <li>
                        <a href="myepisode.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">mic</span>
                            <span>My Episodes</span>
                        </a>
                    </li>
                    <li>
                        <a href="new_podcast.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">add_circle</span>
                            <span>New Podcast</span>
                        </a>
                    </li>
                    <li>
                        <a href="explore.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">explore</span>
                            <span>Explore</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">settings</span>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="signin.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors mt-auto">
                            <span class="material-icons">logout</span>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <button id="sidebarToggle" class="fixed left-0 top-4 bg-transparent p-2 z-30 toggle-moved">
            <span class="material-icons text-white text-3xl">menu</span>
        </button>

        <div class="flex-1 p-4 content-shifted">
        <div class="bg-gradient-to-r from-[#4A1E73]/30 to-[#D76D77]/30 p-2 rounded-xl mb-6 shadow-lg transform hover:scale-[1.01] transition-all duration-300">
            <h1 class="text-lg font-bold">Podcast Analytics for <?php echo htmlspecialchars($full_name); ?></h1>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="analytics-card rounded-xl p-6 shadow-lg">
                <h2 class="text-lg font-semibold mb-3 text-[#FFAF7B]">Total Likes</h2>
                <div class="analytics-number"><?php echo $total_likes; ?></div>
                <p class="text-sm text-gray-400 mt-2">Total engagement from your audience</p>
            </div>
            
            <div class="analytics-card rounded-xl p-6 shadow-lg">
                <h2 class="text-lg font-semibold mb-3 text-[#FFAF7B]">Average Likes per Episode</h2>
                <div class="analytics-number"><?php echo $avg_likes; ?></div>
                <p class="text-sm text-gray-400 mt-2">Average engagement per episode</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="table-container p-6 shadow-lg">
                <h2 class="text-2xl font-semibold mb-6 text-[#FFAF7B]">Episodes Ranked by Likes</h2>
                <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="table-header">
                            <th class="px-6 py-4 text-left font-semibold text-[#FFAF7B]">Rank</th>
                            <th class="px-6 py-4 text-left font-semibold text-[#FFAF7B]">Episode</th>
                            <th class="px-6 py-4 text-left font-semibold text-[#FFAF7B]">Likes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while ($episode = $ranked_episodes->fetch_assoc()): 
                        ?>
                        <tr class="table-row border-t border-[#2E2E4E]">
                            <td class="px-6 py-4 text-[#FFAF7B]"><?php echo $rank++; ?></td>
                            <td class="px-6 py-4 text-[#FFAF7B]"><?php echo htmlspecialchars($episode['description']); ?></td>
                            <td class="px-6 py-4 text-[#FFAF7B]"><?php echo $episode['like_count']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <div class="map-container p-6 shadow-lg rounded-xl" style="background: rgba(46, 46, 78, 0.6); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.08);">
                <h2 class="text-2xl font-semibold mb-6 text-[#FFAF7B]">Podcast Listening Journey</h2>
                <div id="map" class="h-[400px] rounded-lg overflow-hidden"></div>
            </div>
            <?php
            // Check if plays table exists and create it if it doesn't
            $table_exists = false;
            $check_table_query = "SHOW TABLES LIKE 'plays'";
            $result = $conn->query($check_table_query);
            if ($result && $result->num_rows > 0) {
                $table_exists = true;
            } else {
                // Create plays table if it doesn't exist
                $create_plays_table = "CREATE TABLE plays (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    episode_id INT NOT NULL,
                    play_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE,
                    INDEX idx_episode_plays (episode_id),
                    INDEX idx_user_plays (user_id)
                )";
                try {
                    $conn->query($create_plays_table);
                    $table_exists = true;
                } catch (Exception $e) {
                    error_log('Error creating plays table: ' . $e->getMessage());
                }
            }

            // Fetch total listening sessions
            if ($table_exists) {
                try {
                    $total_listening_sessions_query = "SELECT COUNT(*) as total_sessions FROM plays WHERE user_id = ?";
                    $stmt = $conn->prepare($total_listening_sessions_query);
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $total_sessions_result = $stmt->get_result()->fetch_assoc();
                    $total_listening_sessions = isset($total_sessions_result['total_sessions']) ? $total_sessions_result['total_sessions'] : 0;
                    
                    // Get total plays for user's episodes - count from plays table instead of episodes table
                    $total_plays_query = "SELECT COUNT(*) as total_plays FROM plays p JOIN episodes e ON p.episode_id = e.id WHERE e.user_id = ?";
                    $stmt = $conn->prepare($total_plays_query);
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $total_plays_result = $stmt->get_result()->fetch_assoc();
                    $total_plays = $total_plays_result['total_plays'] ? $total_plays_result['total_plays'] : 0;
                } catch (mysqli_sql_exception $e) {
                    // Handle the error gracefully
                    $total_listening_sessions = 0;
                    $total_plays = 0;
                    error_log('Error in plays query: ' . $e->getMessage());
                }
            } else {
                // Table doesn't exist, set default values
                $total_listening_sessions = 0;
                $total_plays = 0;
            }
            ?>
            <div class="listening-activity-container p-6 shadow-lg rounded-xl mt-6 col-span-2" style="background: rgba(46, 46, 78, 0.6); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.08);">
                <h2 class="text-2xl font-semibold mb-6 text-[#FFAF7B]">Listening Activity</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="listeningActivityChart"></canvas>
                    </div>
                    <div class="stats-container p-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div class="activity-card rounded-xl p-4 shadow-md">
                                <h3 class="text-lg font-semibold mb-2 text-[#FFAF7B]">Total Listening Sessions</h3>
                                <div class="activity-number"><?php echo $total_listening_sessions; ?></div>
                                <p class="text-sm text-gray-400 mt-1">Total sessions from your audience</p>
                            </div>
                            <div class="activity-card rounded-xl p-4 shadow-md">
                                <h3 class="text-lg font-semibold mb-2 text-[#FFAF7B]">Total Podcast Plays</h3>
                                <div class="activity-number"><?php echo $total_plays; ?></div>
                                <p class="text-sm text-gray-400 mt-1">Times your podcasts were opened to play</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                    .activity-card {
                        background: linear-gradient(135deg, rgba(74, 30, 115, 0.08), rgba(215, 109, 119, 0.08));
                        backdrop-filter: blur(8px);
                        border: 1px solid rgba(255, 255, 255, 0.08);
                        transition: all 0.3s ease;
                    }
                    .activity-card:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
                        background: linear-gradient(135deg, rgba(74, 30, 115, 0.12), rgba(215, 109, 119, 0.12));
                    }
                    .activity-number {
                        background: linear-gradient(135deg, #4A1E73, #D76D77);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        font-size: 2rem;
                        font-weight: bold;
                        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
                    }
                </style>
            </div>
        </div>
    </div>
    <style>
        .analytics-card {
            background: linear-gradient(135deg, rgba(74, 30, 115, 0.08), rgba(215, 109, 119, 0.08));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .analytics-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, rgba(74, 30, 115, 0.12), rgba(215, 109, 119, 0.12));
        }
        .analytics-number {
            background: linear-gradient(135deg, #4A1E73, #D76D77);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.25rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        .table-container {
            background: rgba(46, 46, 78, 0.6);
            backdrop-filter: blur(8px);
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .table-header {
            background: linear-gradient(135deg, rgba(58, 28, 113, 0.8), rgba(74, 30, 115, 0.8));
        }
        .table-row {
            transition: background-color 0.2s ease;
        }
        .table-row:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }
    </style>
    <script>
        class Sidebar {
            constructor() {
                this.sidebar = document.getElementById('sidebar');
                this.sidebarToggle = document.getElementById('sidebarToggle');
                this.content = document.querySelector('.content-shifted');
                this.initializeSidebar();
            }

            initializeSidebar() {
                this.sidebarToggle.addEventListener('click', () => {
                    this.toggleSidebar();
                });
            }

            toggleSidebar() {
                this.sidebar.classList.toggle('sidebar-hidden');
                this.sidebar.classList.toggle('sidebar-visible');
                this.content.classList.toggle('content-shifted');
                this.content.classList.toggle('content-full');
                this.sidebarToggle.classList.toggle('toggle-moved');
                this.sidebarToggle.classList.toggle('toggle-default');
            }
        }

        new Sidebar();

        // Fetch podcast locations from PHP
        <?php
        try {
            $locations_query = "SELECT e.description, e.latitude, e.longitude, u.first_name, u.last_name 
                               FROM episodes e 
                               JOIN users u ON e.user_id = u.id 
                               WHERE e.latitude IS NOT NULL AND e.longitude IS NOT NULL";
            $locations_result = $conn->query($locations_query);
            $podcast_locations = [];
            while ($location = $locations_result->fetch_assoc()) {
                $podcast_locations[] = [
                    'lat' => (float)$location['latitude'],
                    'lng' => (float)$location['longitude'],
                    'title' => htmlspecialchars($location['description']),
                    'uploader' => htmlspecialchars($location['first_name'] . ' ' . $location['last_name'])
                ];
            }
        } catch (Exception $e) {
            $podcast_locations = [];
            // Log error for debugging
            error_log('Error in podcast locations query: ' . $e->getMessage());
        }
        ?>

        // Initialize the map
        const map = L.map('map').setView([1.3521, 103.8198], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(map);

        // Initialize the listening activity pie chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('listeningActivityChart').getContext('2d');
            
            // Data for the pie chart
            const data = {
                labels: [
                    'Listening Sessions (<?php echo $total_listening_sessions; ?>)',
                    'Podcast Plays (<?php echo $total_plays; ?>)',
                    'Other Interactions (<?php echo round(($total_listening_sessions + $total_plays) * 0.3); ?>)'
                ],
                datasets: [{
                    data: [
                        <?php echo $total_listening_sessions; ?>,
                        <?php echo $total_plays; ?>,
                        <?php echo round(($total_listening_sessions + $total_plays) * 0.3); ?>
                    ],
                    backgroundColor: [
                        '#4A1E73',
                        '#D76D77',
                        '#FFAF7B'
                    ],
                    borderColor: 'rgba(255, 255, 255, 0.08)',
                    borderWidth: 1,
                    hoverOffset: 15
                }]
            };
            
            // Configuration options
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#FFAF7B',
                            font: {
                                size: 12
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Listening Activity Distribution',
                        color: '#FFAF7B',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${percentage}%`;
                            }
                        }
                    }
                }
            };
            
            // Create the pie chart
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        });

        // Add markers for all podcast locations
        const podcastLocations = <?php echo json_encode($podcast_locations); ?>;
        podcastLocations.forEach(location => {
            if (location.lat && location.lng) {
                L.marker([location.lat, location.lng])
                    .bindPopup(`<b>${location.title}</b><br>Uploaded by: ${location.uploader}`)
                    .addTo(map);
            }
        });

        // Adjust map view to fit all markers if there are any locations
        if (podcastLocations.length > 0) {
            const bounds = L.latLngBounds(podcastLocations.map(loc => [loc.lat, loc.lng]));
            map.fitBounds(bounds);
        }
    </script>
</body>
</html>