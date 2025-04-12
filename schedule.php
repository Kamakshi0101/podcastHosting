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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host_name = mysqli_real_escape_string($conn, $_POST['name']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $query = "INSERT INTO podcast_schedules (host_name, schedule_date, schedule_time, description, user_id) 
              VALUES ('$host_name', '$date', '$time', '$description', {$_SESSION['user_id']})";
    
    mysqli_query($conn, $query);
    header('Location: schedule.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podcast Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                        <a href="schedule.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gradient-to-r from-[#4A1E73] to-[#D76D77]">
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
            <span class="material-icons text-white">menu</span>
        </button>

        <main id="mainContent" class="flex-1 p-10 content-shifted">
            <h2 class="text-2xl font-bold mb-5">Podcast Schedule</h2>
            
            <!-- Schedule Content -->
            <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg mb-6">
                <!-- Form to add a schedule -->
                <form method="POST" action="schedule.php" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="name" placeholder="Your Name" required class="p-2 border rounded bg-[#1E1E2E] text-white border-[#4A1E73]">
                        <input type="date" name="date" required class="p-2 border rounded bg-[#1E1E2E] text-white border-[#4A1E73]">
                        <input type="time" name="time" required class="p-2 border rounded bg-[#1E1E2E] text-white border-[#4A1E73]">
                        <input type="text" name="description" placeholder="Description" required class="p-2 border rounded bg-[#1E1E2E] text-white border-[#4A1E73]">
                    </div>
                    <button type="submit" class="mt-4 bg-[#D76D77] hover:bg-[#FFAF7B] text-white px-4 py-2 rounded transition duration-300">Add Schedule</button>
                </form>
            </div>
            
            <!-- Schedule Table -->
            <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-bold mb-4 text-[#D76D77]">Scheduled Podcasts</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-[#3A1C71] to-[#4A1E73]">
                                <th class="px-4 py-2 text-center">Host</th>
                                <th class="px-4 py-2 text-center">Date</th>
                                <th class="px-4 py-2 text-center">Time</th>
                                <th class="px-4 py-2 text-center">Description</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleTable" class="bg-[#1E1E2E]">
                            <?php
                            $query = "SELECT * FROM podcast_schedules WHERE user_id = {$_SESSION['user_id']} ORDER BY schedule_date, schedule_time";
                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr class='border-t border-[#2E2E4E]'>";
                                    echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($row['host_name']) . "</td>";
                                    echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($row['schedule_date']) . "</td>";
                                    echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($row['schedule_time']) . "</td>";
                                    echo "<td class='px-4 py-2 text-center'>" . htmlspecialchars($row['description']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center p-4'>No schedules available</td></tr>";
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