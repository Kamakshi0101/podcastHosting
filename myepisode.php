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

// Fetch episodes from the database
$sql = "SELECT *, 
        CASE 
            WHEN status = 1 THEN 'Published'
            ELSE 'Draft'
        END as status_text 
        FROM episodes 
        WHERE user_id = {$_SESSION['user_id']}
        ORDER BY upload_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Episodes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
            background: linear-gradient(180deg, #1a1625 0%, #2d1f3d 50%, #2d2442 100%);
        }
        .sidebar-hidden { transform: translateX(-100%); }
        .sidebar-visible { transform: translateX(0); }
        .sidebar nav ul li a {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .sidebar nav ul li a:hover {
            background: linear-gradient(90deg, rgba(74, 30, 115, 0.8) 0%, rgba(215, 109, 119, 0.8) 100%);
        }
        .sidebar nav ul li a.active {
            background: linear-gradient(90deg, rgba(74, 30, 115, 0.8) 0%, rgba(215, 109, 119, 0.8) 100%);
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
    </style>
    <script>
        function getMediaDuration(url, id) {
            const media = document.createElement(url.toLowerCase().endsWith('.mp4') ? 'video' : 'audio');
            media.src = 'uploads/' + url;
            media.addEventListener('loadedmetadata', function() {
                const minutes = Math.floor(media.duration / 60);
                const seconds = Math.floor(media.duration % 60);
                document.getElementById('duration-' + id).textContent = 
                    `${minutes}:${seconds.toString().padStart(2, '0')}`;
            });
            media.addEventListener('error', function() {
                document.getElementById('duration-' + id).textContent = 'N/A';
            });
        }
    </script>
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
                        <a href="myepisode.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gradient-to-r from-[#4A1E73] to-[#D76D77]">
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
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <button id="sidebarToggle" class="fixed left-0 top-4 bg-transparent p-2 z-30 toggle-moved">
            <span class="material-icons text-white">menu</span>
        </button>

        <main id="mainContent" class="flex-1 p-10 content-shifted md:content-shifted">
            <div class="bg-gradient-to-r from-[#4A1E73] to-[#D76D77] p-8 rounded-lg mb-8 shadow-lg">
                <h2 class="text-4xl font-bold">My Episodes</h2>
            </div>
            
            <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse bg-[#1E1E2E] rounded-lg overflow-hidden">
                    
                        <thead>
                            <tr class="bg-gradient-to-r from-[#3A1C71] to-[#4A1E73] text-[#FFAF7B]">
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-center">STATUS</th>
                                <th class="px-4 py-3 text-center">TYPE</th>
                                <th class="px-4 py-3 text-center">LENGTH</th>
                                <th class="px-4 py-3 text-center">PLAYS</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1E1E2E]">
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $format = pathinfo($row['file_path'], PATHINFO_EXTENSION);
                                    $format = strtoupper($format);
                                    $isVideo = in_array(strtolower($format), ['mp4', 'webm', 'ogg']);
                                    ?>
                                    <tr class="border-t border-[#2E2E4E] hover:bg-[#2E2E4E]">
                                        <td class="px-4 py-3">
                                            <div>
                                                <h3 class="font-bold text-[#FFAF7B]"><?php echo htmlspecialchars($row['title']); ?></h3>
                                                <p class="text-sm text-gray-400">Uploaded: <?php echo date('M j, Y', strtotime($row['upload_date'])); ?></p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center">
                                                <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                                <?php echo htmlspecialchars($row['status_text']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <span class="material-icons text-sm">
                                                    <?php echo $isVideo ? 'videocam' : 'audiotrack'; ?>
                                                </span>
                                                <?php echo $format; ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span id="duration-<?php echo $row['id']; ?>">Loading...</span>
                                            <script>
                                                getMediaDuration('<?php echo $row['file_path']; ?>', <?php echo $row['id']; ?>);
                                            </script>
                                        </td>
                                        <td class="px-4 py-3 text-center"><?php echo isset($row['plays']) ? $row['plays'] : '0'; ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="edit_episode.php?id=<?php echo $row['id']; ?>" 
                                                   class="bg-[#4A1E73] p-2 rounded hover:bg-[#3A1C71]">
                                                    <span class="material-icons text-sm">edit</span>
                                                </a>
                                                <a href="episode_delete.php?id=<?php echo $row['id']; ?>" 
                                                   class="bg-red-600 p-2 rounded hover:bg-red-700" 
                                                   onclick="return confirm('Are you sure you want to delete this episode?');">
                                                    <span class="material-icons text-sm">delete</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <span class="material-icons text-6xl text-gray-500 mb-4">podcasts</span>
                                        <p class="text-gray-400">No episodes uploaded yet.</p>
                                        <a href="new_podcast.php" class="inline-block mt-4 bg-[#4A1E73] px-6 py-2 rounded hover:bg-[#3A1C71]">
                                            Upload Your First Episode
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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
                this.setInitialState();
                window.addEventListener('resize', () => this.setInitialState());
            }

            initializeSidebar() {
                this.sidebarToggle.addEventListener('click', () => {
                    this.toggleSidebar();
                });
            }

            setInitialState() {
                if (window.innerWidth < 768) {
                    this.sidebar.classList.add('sidebar-hidden');
                    this.sidebar.classList.remove('sidebar-visible');
                    this.sidebarToggle.classList.add('toggle-default');
                    this.sidebarToggle.classList.remove('toggle-moved');
                    this.mainContent.classList.add('content-full');
                    this.mainContent.classList.remove('content-shifted');
                } else {
                    this.sidebar.classList.remove('sidebar-hidden');
                    this.sidebar.classList.add('sidebar-visible');
                    this.sidebarToggle.classList.remove('toggle-default');
                    this.sidebarToggle.classList.add('toggle-moved');
                    this.mainContent.classList.remove('content-full');
                    this.mainContent.classList.add('content-shifted');
                }
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
</body>
</html>