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
    <title>New Podcast - Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="js/location.js"></script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
            background: linear-gradient(180deg, #4A1E73 0%, #D76D77 100%);
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
        #sidebarToggle { transition: transform 0.3s ease-in-out; }
        .file-drop-area {
            border: 2px dashed #D76D77;
            transition: all 0.3s ease;
        }
        .file-drop-area.dragover {
            border-color: #FFAF7B;
            background: rgba(255, 175, 123, 0.1);
        }
        .content-tile {
            height: calc(100vh - 160px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #D76D77 #1E1E2E;
        }
        .content-tile::-webkit-scrollbar {
            width: 8px;
        }
        .content-tile::-webkit-scrollbar-track {
            background: #1E1E2E;
        }
        .content-tile::-webkit-scrollbar-thumb {
            background-color: #D76D77;
            border-radius: 4px;
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
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-white/10">
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
                        <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
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
            <h2 class="text-2xl font-bold mb-5">Upload New Podcast</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="content-tile">
                    <form action="process_upload.php" method="POST" enctype="multipart/form-data">
                        <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                            <div class="mb-4">
                                <label for="title" class="block text-[#FFAF7B] mb-2">Podcast Title</label>
                                <input type="text" id="title" name="title" required
                                    class="w-full bg-[#1E1E2E] text-white p-3 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                            </div>

                            <div class="mb-4">
                                <label for="description" class="block text-[#FFAF7B] mb-2">Description</label>
                                <textarea id="description" name="description" rows="4" required
                                    class="w-full bg-[#1E1E2E] text-white p-3 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none"></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="category" class="block text-[#FFAF7B] mb-2">Category</label>
                                <select id="category" name="category" required
                                    class="w-full bg-[#1E1E2E] text-white p-3 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                                    <option value="">Select a category</option>
                                    <option value="technology">Technology</option>
                                    <option value="business">Business</option>
                                    <option value="education">Education</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="lifestyle">Lifestyle</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-[#FFAF7B] mb-2">Media Type</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="media_type" value="audio" checked
                                            class="mr-2 text-[#D76D77]">
                                        Audio
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="media_type" value="video"
                                            class="mr-2 text-[#D76D77]">
                                        Video
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-[#FFAF7B] mb-2">Upload File</label>
                                <div id="dropArea" class="file-drop-area p-8 rounded-lg text-center cursor-pointer">
                                    <span class="material-icons text-[#D76D77] text-5xl mb-2">cloud_upload</span>
                                    <p>Drag and drop your file here or click to browse</p>
                                    <p class="text-sm text-gray-400 mt-2">Supported formats: MP3, MP4, WAV (Max 500MB)</p>
                                    <input type="file" id="fileInput" name="podcast_file" accept=".mp3,.mp4,.wav" 
                                        class="hidden" required>
                                </div>
                                <div id="fileInfo" class="mt-2 text-sm text-gray-400"></div>
                            </div>

                            <div class="mb-4">
                                <label for="thumbnail" class="block text-[#FFAF7B] mb-2">Thumbnail Image (Optional)</label>
                                <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                                    class="w-full bg-[#1E1E2E] text-white p-3 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                            </div>

                            <!-- Hidden fields for geolocation data -->
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                            <div class="flex justify-end gap-4">
                                <button type="button" onclick="window.location.href='dashboard_index.php'" 
                                    class="px-6 py-2 rounded bg-[#1E1E2E] hover:bg-[#2E2E4E] transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    class="px-6 py-2 rounded bg-gradient-to-r from-[#3A1C71] via-[#D76D77] to-[#FFAF7B] hover:opacity-90 transition-opacity">
                                    Upload Podcast
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="content-tile">
                    <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg h-full">
                        <h3 class="text-xl font-bold mb-4 text-[#FFAF7B]">Additional Information</h3>
                        <div class="space-y-4">
                            <div class="bg-[#1E1E2E] p-4 rounded">
                                <h4 class="text-lg font-semibold mb-2">Tips for Great Podcasting</h4>
                                <ul class="list-disc list-inside space-y-2 text-gray-300">
                                    <li>Ensure good audio quality with proper equipment</li>
                                    <li>Plan your content structure beforehand</li>
                                    <li>Keep your target audience in mind</li>
                                    <li>Edit your content for clarity and flow</li>
                                </ul>
                            </div>
                            
                            <div class="bg-[#1E1E2E] p-4 rounded">
                                <h4 class="text-lg font-semibold mb-2">Upload Guidelines</h4>
                                <ul class="list-disc list-inside space-y-2 text-gray-300">
                                    <li>Maximum file size: 500MB</li>
                                    <li>Supported formats: MP3, MP4, WAV</li>
                                    <li>Recommended thumbnail size: 1400x1400px</li>
                                    <li>Clear episode titles and descriptions</li>
                                </ul>
                            </div>
                            
                            <div class="bg-[#1E1E2E] p-4 rounded">
                                <h4 class="text-lg font-semibold mb-2">Best Practices</h4>
                                <ul class="list-disc list-inside space-y-2 text-gray-300">
                                    <li>Use descriptive episode titles</li>
                                    <li>Include relevant keywords in descriptions</li>
                                    <li>Add appropriate tags for better discoverability</li>
                                    <li>Maintain consistent upload schedule</li>
                                </ul>
                            </div>
                            
                            <div class="bg-[#1E1E2E] p-4 rounded">
                                <h4 class="text-lg font-semibold mb-2">Need Help?</h4>
                                <p class="text-gray-300">
                                    If you need assistance with your podcast upload or have any questions, 
                                    please contact our support team or visit our help center for detailed guides.
                                </p>
                            </div>
                        </div>
                    </div>
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
                // Remove the typo 'this.s'
                this.sidebarToggle.classList.toggle('toggle-default');
            }
        }

        // Initialize sidebar
        document.addEventListener('DOMContentLoaded', () => {
            new Sidebar();

            // File upload functionality
            const dropArea = document.getElementById('dropArea');
            const fileInput = document.getElementById('fileInput');
            const fileInfo = document.getElementById('fileInfo');

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop zone when dragging over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });

            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);
            dropArea.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', handleFileSelect);

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight(e) {
                dropArea.classList.add('dragover');
            }

            function unhighlight(e) {
                dropArea.classList.remove('dragover');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            function handleFileSelect(e) {
                const files = e.target.files;
                handleFiles(files);
            }

            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    // Check file size (500MB limit)
                    if (file.size > 500 * 1024 * 1024) {
                        fileInfo.textContent = 'Error: File size exceeds 500MB limit';
                        fileInfo.style.color = '#ff6b6b';
                        fileInput.value = '';
                        return;
                    }
                    // Display file info
                    fileInfo.textContent = `Selected: ${file.name} (${(file.size / (1024 * 1024)).toFixed(2)}MB)`;
                    fileInfo.style.color = '#FFAF7B';
                }
            }
        });
    </script>
</body>
</html>