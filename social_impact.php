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

// Fetch NGO markers
$markers = [];
$stmt = $conn->prepare("SELECT * FROM ngo_markers");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $markers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Impact - Podcast Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
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
        .impact-card {
            background: linear-gradient(135deg, rgba(74, 30, 115, 0.08), rgba(215, 109, 119, 0.08));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        .impact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, rgba(74, 30, 115, 0.12), rgba(215, 109, 119, 0.12));
        }
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.75rem;
        }
        .leaflet-popup-content-wrapper {
            background: #2E2E4E;
            color: white;
        }
        .leaflet-popup-tip {
            background: #2E2E4E;
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
                        <a href="explore.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white/10 transition-colors">
                            <span class="material-icons">explore</span>
                            <span>Explore</span>
                        </a>
                    </li>
                    <li>
                        <a href="social_impact.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-gradient-to-r from-[#4A1E73] to-[#D76D77]">
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
            <span class="material-icons text-white text-3xl">menu</span>
        </button>

        <div class="flex-1 p-4 content-shifted">
            <div class="bg-gradient-to-r from-[#4A1E73]/30 to-[#D76D77]/30 p-6 rounded-xl mb-6 shadow-lg">
                <h1 class="text-2xl font-bold mb-4">NGO Impact Map</h1>
                <p class="text-gray-300 mb-4">Discover and mark NGO locations making a difference in your community. Click on the map to add a new NGO marker.</p>
                <div id="map" class="shadow-xl"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="impact-card rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold mb-4 text-[#FFAF7B]">Add New NGO Location</h2>
                    <form id="ngoForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">NGO Name</label>
                            <input type="text" id="ngoName" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Description</label>
                            <textarea id="ngoDescription" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none" rows="3" placeholder="Describe the NGO's mission and activities"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Category</label>
                            <select id="ngoCategory" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                                <option value="education">Education</option>
                                <option value="healthcare">Healthcare</option>
                                <option value="environment">Environment</option>
                                <option value="social">Social Services</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Website</label>
                            <input type="url" id="ngoWebsite" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Contact Info</label>
                            <input type="text" id="ngoContact" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Impact Story</label>
                            <textarea id="ngoImpactStory" class="w-full bg-[#1E1E2E] p-2 rounded border border-[#4A1E73] focus:border-[#D76D77] focus:outline-none" rows="3" placeholder="Share how this NGO is using podcasts to create social impact"></textarea>
                        </div>
                        <div id="locationWarning" class="hidden text-red-500 text-sm mb-2">
                            Please click on the map to select a location first
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-gradient-to-r from-[#4A1E73] to-[#D76D77] rounded hover:opacity-90 transition-opacity">
                            Add NGO Location
                        </button>
                    </form>
                </div>

                <div class="impact-card rounded-xl p-6 shadow-lg">
                    <h2 class="text-xl font-semibold mb-4 text-[#FFAF7B]">Recent NGO Additions</h2>
                    <div id="recentNGOs" class="space-y-4">
                        <?php
                        $recent_markers_query = $conn->prepare("SELECT * FROM ngo_markers ORDER BY id DESC LIMIT 5");
                        $recent_markers_query->execute();
                        $recent_markers = $recent_markers_query->get_result();
                        
                        while ($ngo = $recent_markers->fetch_assoc()) {
                            echo "<div class='bg-[#1E1E2E] p-4 rounded-lg border border-[#4A1E73]/30'>";
                            echo "<h3 class='text-lg font-semibold text-[#FFAF7B]'>" . htmlspecialchars($ngo['name']) . "</h3>";
                            echo "<p class='text-gray-300 mt-2'>" . htmlspecialchars($ngo['description']) . "</p>";
                            echo "<div class='mt-2 flex items-center gap-2'>";
                            echo "<span class='px-2 py-1 bg-[#4A1E73]/30 rounded text-sm'>" . htmlspecialchars($ngo['category']) . "</span>";
                            if ($ngo['website']) {
                                echo "<a href='" . htmlspecialchars($ngo['website']) . "' target='_blank' class='text-[#FFAF7B] text-sm hover:underline'>Visit Website</a>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('map');
        
        // Try to get user's location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    map.setView([userLat, userLng], 13);
                    
                    // Add a special marker for user's location
                    L.marker([userLat, userLng], {
                        icon: L.divIcon({
                            className: 'user-location-marker',
                            html: '<div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-lg pulse-animation"></div>'
                        })
                    }).addTo(map).bindPopup('Your Location');
                },
                function(error) {
                    console.log('Error getting location:', error.message);
                    map.setView([0, 0], 2); // Default view if location access is denied
                }
            );
        } else {
            console.log('Geolocation is not supported');
            map.setView([0, 0], 2); // Default view if geolocation is not supported
        }
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add custom styles for the user location marker
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .user-location-marker {
                background: transparent;
                border: none;
            }
            .pulse-animation {
                animation: pulse 1.5s infinite;
            }
            @keyframes pulse {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.3); opacity: 0.7; }
                100% { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(styleElement);

        // Load existing markers
        const markers = <?php echo json_encode($markers); ?>;
        markers.forEach(marker => {
            const popupContent = `
                <h3 class="font-bold">${marker.name}</h3>
                <p>${marker.description}</p>
                <p class="mt-2"><strong>Category:</strong> ${marker.category}</p>
                <p><strong>Contact:</strong> ${marker.contact_info}</p>
                <div class="mt-2">
                    <a href="${marker.website}" target="_blank" class="text-[#FFAF7B] block">Visit Website</a>
                    ${marker.podcast_channel_url ? `<a href="${marker.podcast_channel_url}" target="_blank" class="text-[#FFAF7B] block mt-1">Listen to Podcast</a>` : ''}
                </div>
                ${marker.impact_story ? `
                <div class="mt-2 p-2 bg-[#2E2E4E] rounded">
                    <strong>Impact Story:</strong>
                    <p class="text-sm mt-1">${marker.impact_story}</p>
                </div>` : ''}
                ${marker.featured_episode_id ? `
                <div class="mt-2">
                    <strong>Featured Episode:</strong>
                    <a href="episode.php?id=${marker.featured_episode_id}" class="text-[#FFAF7B] block mt-1">Listen Now</a>
                </div>` : ''}
            `;
            L.marker([marker.latitude, marker.longitude])
                .bindPopup(popupContent)
                .addTo(map);
        });

        // Handle map clicks
        let selectedLocation = null;
        map.on('click', function(e) {
            if (selectedLocation) map.removeLayer(selectedLocation);
            selectedLocation = L.marker(e.latlng).addTo(map);
            document.getElementById('ngoForm').dataset.lat = e.latlng.lat;
            document.getElementById('ngoForm').dataset.lng = e.latlng.lng;
        });

        // Handle form submission
        document.getElementById('ngoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const locationWarning = document.getElementById('locationWarning');
            
            if (!this.dataset.lat || !this.dataset.lng) {
                locationWarning.classList.remove('hidden');
                return;
            }
            locationWarning.classList.add('hidden');
            
            const formData = {
                name: document.getElementById('ngoName').value,
                description: document.getElementById('ngoDescription').value,
                category: document.getElementById('ngoCategory').value,
                contact_info: document.getElementById('ngoContact').value,
                website: document.getElementById('ngoWebsite').value,
                impact_story: document.getElementById('ngoImpactStory').value,
                latitude: this.dataset.lat,
                longitude: this.dataset.lng
            };

            try {
                const response = await fetch('save_ngo_marker.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Failed to save NGO marker');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to save NGO location. Please try again.');
            }
        });

        // Sidebar functionality
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
                map.invalidateSize();
            }
        }

        new Sidebar();
    </script>
</body>
</html>