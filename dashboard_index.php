<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch dashboard images
$dashboard_images = [];
$stmt = $conn->prepare("SELECT * FROM dashboard_images WHERE user_id = ? ORDER BY display_order, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $dashboard_images[] = $row;
}

$full_name = $_SESSION['first_name'] . (isset($_SESSION['last_name']) ? ' ' . $_SESSION['last_name'] : '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
            background: linear-gradient(180deg, #1a1625 0%, #2d1f3d 50%, #2d2442 100%);
        }
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        .sidebar-visible {
            transform: translateX(0);
        }
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
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gradient-to-r from-[#4A1E73] to-[#D76D77]">
                            <span class="material-icons">home</span>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="analytics.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
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
                        <a href="social_impact.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">volunteer_activism</span>
                            <span>Social Impact</span>
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
            <span class="material-icons text-white">menu</span>
        </button>

        <main id="mainContent" class="flex-1 p-10 content-shifted">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                <a href="analytics.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">analytics</span>
                        <h3 class="text-xl font-semibold">Analytics</h3>
                    </div>
                    <p class="text-gray-300">Track your podcast performance with detailed analytics and insights.</p>
                </a>
                <a href="schedule.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">calendar_today</span>
                        <h3 class="text-xl font-semibold">Schedule</h3>
                    </div>
                    <p class="text-gray-300">Plan and manage your podcast episodes with our scheduling tools.</p>
                </a>
                <a href="myepisode.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">mic</span>
                        <h3 class="text-xl font-semibold">My Episodes</h3>
                    </div>
                    <p class="text-gray-300">Access and manage all your podcast episodes in one place.</p>
                </a>
                <a href="new_podcast.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">add_circle</span>
                        <h3 class="text-xl font-semibold">New Podcast</h3>
                    </div>
                    <p class="text-gray-300">Create and upload new podcast episodes with ease.</p>
                </a>
                <a href="explore.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">explore</span>
                        <h3 class="text-xl font-semibold">Explore</h3>
                    </div>
                    <p class="text-gray-300">Discover new podcasts and connect with other creators.</p>
                </a>
                <a href="social_impact.php" class="bg-gradient-to-br from-[#4A1E73]/20 to-[#D76D77]/20 p-6 rounded-xl hover:from-[#4A1E73]/30 hover:to-[#D76D77]/30 transition-all duration-300 group">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-icons text-3xl text-[#FFAF7B] group-hover:scale-110 transition-transform">volunteer_activism</span>
                        <h3 class="text-xl font-semibold">Social Impact</h3>
                    </div>
                    <p class="text-gray-300">Make a difference with your podcast through NGO collaborations.</p>
                </a>
            </div>

            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl font-bold">Your Latest Episode</h2>
            </div>
            
            <?php
            // Fetch the latest episode for the current user
            $latest_episode_query = "SELECT episodes.*, users.first_name, users.last_name 
                                    FROM episodes 
                                    JOIN users ON episodes.user_id = users.id 
                                    WHERE episodes.user_id = ? 
                                    ORDER BY upload_date DESC 
                                    LIMIT 1";
            $stmt = $conn->prepare($latest_episode_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            ?>

            <?php
            // Fetch analytics data for the latest episode
            $analytics_query = "SELECT 
                (SELECT COUNT(*) FROM plays WHERE episode_id = e.id) as total_plays,
                (SELECT COUNT(*) FROM likes WHERE episode_id = e.id) as total_likes,
                (SELECT COUNT(DISTINCT user_id) FROM plays WHERE episode_id = e.id) as unique_listeners
            FROM episodes e 
            WHERE e.id = ?"; 
            
            if ($result->num_rows > 0):
                $row = $result->fetch_assoc();
                $episode_id = $row['id'];
                
                $analytics_stmt = $conn->prepare($analytics_query);
                $analytics_stmt->bind_param('i', $episode_id);
                $analytics_stmt->execute();
                $analytics = $analytics_stmt->get_result()->fetch_assoc();
            endif;
            ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl mx-auto">
            <?php if ($result->num_rows > 0): ?>
                <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg cursor-pointer" onclick="openMediaModal('<?php echo $row['file_path']; ?>', '<?php echo pathinfo($row['file_path'], PATHINFO_EXTENSION) === 'mp4' ? 'video' : 'audio'; ?>', '<?php echo htmlspecialchars($row['title']); ?>', <?php echo $row['id']; ?>)">
                    <div class="flex flex-col space-y-4">
                        <?php if (!empty($row['thumbnail'])): ?>
                            <img src="uploads/<?php echo $row['thumbnail']; ?>" alt="Podcast Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/2E2E4E/white?text=No+Thumbnail" alt="Default Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">
                        <?php endif; ?>
                        <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="text-gray-400"><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="text-sm text-gray-300 mt-2">Posted by: <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                        <audio class="w-full mt-4" controls>
                            <source src="uploads/<?php echo $row['file_path']; ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                        <div class="text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($row['upload_date'])); ?>
                        </div>
                        <?php
                        $episode_id = $row['id'];
                        $is_liked = false;
                        try {
                            $liked_sql = "SELECT * FROM likes WHERE user_id = $user_id AND episode_id = $episode_id";
                            $liked_result = mysqli_query($conn, $liked_sql);
                            if ($liked_result) {
                                $is_liked = mysqli_num_rows($liked_result) > 0;
                            }
                        } catch (Exception $e) {
                            $is_liked = false;
                        }
                        ?>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="material-icons cursor-pointer like-btn <?php echo $is_liked ? 'text-red-500' : 'text-gray-400'; ?>" 
                                data-episode-id="<?php echo $episode_id; ?>"
                                onclick="handleLike(<?php echo $episode_id; ?>, event)">
                                <?php echo $is_liked ? 'favorite' : 'favorite_border'; ?>
                            </span>
                            <span class="text-gray-400 like-count">
                                <?php 
                                try {
                                    $count_sql = "SELECT COUNT(*) AS count FROM likes WHERE episode_id = $episode_id";
                                    $count_result = mysqli_query($conn, $count_sql);
                                    if ($count_result) {
                                        $count_row = mysqli_fetch_assoc($count_result);
                                        echo $count_row['count'];
                                    } else {
                                        echo "0";
                                    }
                                } catch (Exception $e) {
                                    echo "0";
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (isset($analytics)): ?>
                <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold mb-4">Episode Analytics</h3>
                    <div class="space-y-4">
                        <div class="h-64">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="text-center">
                                <span class="text-gray-400 block">Total Plays</span>
                                <span class="text-2xl font-bold"><?php echo number_format($analytics['total_plays']); ?></span>
                            </div>
                            <div class="text-center">
                                <span class="text-gray-400 block">Total Likes</span>
                                <span class="text-2xl font-bold text-red-500"><?php echo number_format($analytics['total_likes']); ?></span>
                            </div>
                            <div class="text-center">
                                <span class="text-gray-400 block">Unique Listeners</span>
                                <span class="text-2xl font-bold text-blue-500"><?php echo number_format($analytics['unique_listeners']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="col-span-full text-center text-gray-400">
                    No episodes available. <a href="new_podcast.php" class="text-[#D76D77] hover:underline">Create your first episode!</a>
                </div>
            <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="mediaModal" class="hidden fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4">
        <div class="bg-[#2E2E4E] rounded-lg w-full max-w-3xl p-6 relative">
            <button onclick="closeMediaModal()" class="absolute -top-4 -right-4 bg-red-500 p-2 rounded-full hover:bg-red-600">
                <span class="material-icons text-white">close</span>
            </button>
            <h3 class="text-xl font-bold mb-4" id="modalTitle"></h3>
            <div id="mediaContainer"></div>
        </div>
    </div>

    <script>
        class Sidebar {
            constructor() {
                this.sidebar = document.getElementById('sidebar');
                this.sidebarToggle = document.getElementById('sidebarToggle');
                this.mainContent = document.getElementById('mainContent');
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
                this.mainContent.classList.toggle('content-shifted');
                this.mainContent.classList.toggle('content-full');
                this.sidebarToggle.classList.toggle('toggle-moved');
                this.sidebarToggle.classList.toggle('toggle-default');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            new Sidebar();
        });

        function handleLike(episodeId, event) {
            event.stopImmediatePropagation();
            event.preventDefault();
            
            const likeBtn = document.querySelector(`.like-btn[data-episode-id="${episodeId}"]`);
            const likeCount = likeBtn.closest('.flex').querySelector('.like-count');
            
            fetch('like_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `episode_id=${encodeURIComponent(episodeId)}&user_id=${encodeURIComponent(<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0'; ?>)}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    likeBtn.textContent = data.is_liked ? 'favorite' : 'favorite_border';
                    likeBtn.classList.toggle('text-red-500', data.is_liked);
                    likeCount.textContent = data.like_count;
                    if (!data.is_liked) likeBtn.classList.remove('text-red-500');
                } else if (data.error) {
                    alert(data.error);
                    likeBtn.classList.remove('text-red-500');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update like. Please try again.');
                likeBtn.classList.remove('text-red-500');
            });
        }

        function openMediaModal(filePath, mediaType, title, episodeId) {
            const container = document.getElementById('mediaContainer');
            container.innerHTML = mediaType === 'video' 
                ? `<video class="w-full rounded-lg" controls autoplay>
                     <source src="uploads/${filePath}" type="video/mp4">
                   </video>`
                : `<audio class="w-full" controls autoplay>
                     <source src="uploads/${filePath}" type="audio/mpeg">
                   </audio>`;
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('mediaModal').classList.remove('hidden');
            
            // Track play when media is opened
            trackPlay(episodeId);
        }

        function trackPlay(episodeId) {
            fetch('track_play.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `episode_id=${encodeURIComponent(episodeId)}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Error tracking play:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function closeMediaModal() {
            document.getElementById('mediaModal').classList.add('hidden');
            const mediaElement = document.querySelector('#mediaContainer video, #mediaContainer audio');
            if(mediaElement) mediaElement.pause();
        }

        window.addEventListener('click', (e) => {
            if(e.target.id === 'mediaModal') closeMediaModal();
        });
        // Initialize analytics chart
        <?php if (isset($analytics)): ?>
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Last 7 Days', '6 Days Ago', '5 Days Ago', '4 Days Ago', '3 Days Ago', '2 Days Ago', 'Today'],
                datasets: [{
                    label: 'Plays',
                    data: [0, <?php echo $analytics['total_plays']; ?>],
                    borderColor: '#60A5FA',
                    tension: 0.4,
                    fill: false
                }, {
                    label: 'Likes',
                    data: [0, <?php echo $analytics['total_likes']; ?>],
                    borderColor: '#EF4444',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#9CA3AF'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    },
                    x: {
                        grid: {
                            color: '#374151'
                        },
                        ticks: {
                            color: '#9CA3AF'
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>