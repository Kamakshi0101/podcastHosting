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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Podcasts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .custom-audio-player {
            --progress-color: #D76D77;
            --bg-color: #2E2E4E;
            padding: 1rem;
            background: var(--bg-color);
            border-radius: 0.75rem;
        }
        .progress-bar {
            background: rgba(255,255,255,0.1);
            cursor: pointer;
            transition: height 0.2s;
        }
        .progress-fill {
            transition: width 0.1s linear;
        }
        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 12px;
            height: 12px;
            background: #D76D77;
            border-radius: 50%;
            cursor: pointer;
        }
        .play-pause-btn {
            transition: transform 0.2s, background-color 0.2s;
        }
        .play-pause-btn:active {
            transform: scale(0.95);
        }
        .sidebar {
            transition: transform 0.3s ease-in-out;
            background: linear-gradient(180deg, #1a1625 0%, #2d1f3d 50%, #2d2442 100%);
        }
        .sidebar a.bg-white\/10 {
            background: linear-gradient(90deg, #4A1E73 0%, #D76D77 100%);
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
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-white/10">
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
            <div class="flex justify-between items-center mb-5">
    <h2 class="text-2xl font-bold">Explore Podcasts</h2>
    <div class="relative">
        <select id="searchInput" class="bg-[#2E2E4E] text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]" onchange="filterPodcasts()">
    
    <?php
    $categories = ['Technology', 'Education', 'Entertainment', 'Business', 'Science'];
    echo '<option value="" disabled selected hidden>Select a category</option>';
    echo '<option value="">All Categories</option>';
    foreach($categories as $cat) {
        echo '<option value="' . $cat . '">' . $cat . '</option>';
    }
    ?>
</select>
<script>
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
        body: `episode_id=${encodeURIComponent(episodeId)}`,
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

function filterPodcasts() {
    const category = document.getElementById('searchInput').value;
    fetch(`filter_podcasts.php?category=${category}`)
        .then(response => response.text())
        .then(data => {
            document.querySelector('.grid').innerHTML = data;
        });
}
</script>
        <span class="material-icons absolute right-3 top-2.5 text-gray-400">search</span>
    </div>
</div>
            
            <?php
$result = mysqli_query($conn, "SELECT episodes.*, users.username FROM episodes JOIN users ON episodes.user_id = users.id ORDER BY upload_date DESC");
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php if (mysqli_num_rows($result) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg cursor-pointer" onclick="openMediaModal('<?php echo $row['file_path']; ?>', '<?php echo pathinfo($row['file_path'], PATHINFO_EXTENSION) === 'mp4' ? 'video' : 'audio'; ?>', '<?php echo htmlspecialchars($row['title']); ?>', <?php echo $row['id']; ?>)">
        <div class="flex flex-col space-y-4">
            <?php if (!empty($row['thumbnail'])): ?>
                <img src="uploads/<?php echo $row['thumbnail']; ?>" alt="Podcast Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">
            <?php else: ?>
                <img src="https://placehold.co/600x400/2E2E4E/white?text=No+Thumbnail" alt="Default Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">
            <?php endif; ?>
            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($row['title']); ?></h3>
            <p class="text-gray-400"><?php echo htmlspecialchars($row['description']); ?></p>
            <p class="text-sm text-gray-300 mt-2">Posted by: <?php echo htmlspecialchars($row['username']); ?></p>
            <audio class="w-full mt-4" controls>
    <source src="uploads/<?php echo $row['file_path']; ?>" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>
            <div class="text-sm text-gray-500">
                <?php echo date('M j, Y', strtotime($row['upload_date'])); ?>
            </div>
            <?php
            $episode_id = $row['id'];
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $is_liked = false;
            if ($user_id) {
                try {
                    $liked_sql = "SELECT * FROM likes WHERE user_id = $user_id AND episode_id = $episode_id";
                    $liked_result = mysqli_query($conn, $liked_sql);
                    if ($liked_result) {
                        $is_liked = mysqli_num_rows($liked_result) > 0;
                    }
                } catch (Exception $e) {
                    // Silently handle error, default to not liked
                    $is_liked = false;
                }
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
    <?php endwhile; ?>
<?php else: ?>
    <div class="col-span-full text-center text-gray-400">
        No podcasts available
    </div>
<?php endif; ?>
</div>
        </main>
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
    </script>

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
</script>

</body>
</html>