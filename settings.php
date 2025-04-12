<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

$full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$user_id = $_SESSION['user_id'];

// Create uploads/dashboard directory if it doesn't exist
if (!file_exists('uploads/dashboard')) {
    mkdir('uploads/dashboard', 0777, true);
}

// Handle dashboard image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'dashboard_image') {
    $error = '';
    $success = '';

    if (!isset($_FILES['dashboard_image'])) {
        $error = 'No file was uploaded';
    } else if ($_FILES['dashboard_image']['error'] !== 0) {
        switch ($_FILES['dashboard_image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error = 'The uploaded file exceeds the upload_max_filesize directive';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            default:
                $error = 'Unknown upload error';
        }
    } else {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['dashboard_image']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($filetype, $allowed)) {
            $error = 'Invalid file type. Allowed types: jpg, jpeg, png, gif';
        } else {
            $newname = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/dashboard/' . $newname;
            
            if (!move_uploaded_file($_FILES['dashboard_image']['tmp_name'], $upload_path)) {
                $error = 'Failed to move uploaded file';
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO dashboard_images (user_id, image_path) VALUES (?, ?)");
                    if ($stmt === false) {
                        throw new Exception('Failed to prepare statement: ' . $conn->error);
                    }
                    
                    $stmt->bind_param("is", $user_id, $newname);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to execute statement: ' . $stmt->error);
                    }
                    
                    $success = 'Image uploaded successfully';
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    // If database insert fails, remove the uploaded file
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            }
        }
    }
    
    // Send response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => empty($error), 'message' => empty($error) ? $success : $error]);
        exit;
    }
}

// Fetch dashboard images
$dashboard_images = [];
$stmt = $conn->prepare("SELECT * FROM dashboard_images WHERE user_id = ? ORDER BY display_order, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $dashboard_images[] = $row;
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['form_type'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    $error = '';
    $success = '';
    
    // Validate required fields
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required fields';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else if (!empty($new_password)) {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();
        }
    }
    
    if (empty($error)) {
        // Update username and email
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully';
            // Update session data
            $_SESSION['username'] = $username;
        } else {
            $error = 'Failed to update profile';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Podcast Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                        <a href="settings.php" class="flex items-center gap-3 px-4 py-2 rounded-lg bg-white/10">
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
            <div class="max-w-2xl mx-auto bg-[#2E2E4E] rounded-lg p-8">
                <h2 class="text-2xl font-bold mb-6">Account Settings</h2>
                
                <?php if (isset($error) && $error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success) && $success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" 
                               class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                               class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                    </div>

                    <div class="border-t border-gray-600 pt-6 mt-6">
                        <h3 class="text-lg font-semibold mb-4">Change Password</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Current Password</label>
                                <input type="password" name="current_password" 
                                       class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">New Password</label>
                                <input type="password" name="new_password" 
                                       class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Confirm New Password</label>
                                <input type="password" name="confirm_password" 
                                       class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-[#D76D77] text-white px-6 py-2 rounded-lg hover:bg-[#C55C66] transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>

                <div class="border-t border-gray-600 pt-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Dashboard Images</h3>
                    <form action="settings.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="form_type" value="dashboard_image">
                        <div>
                            <label class="block text-sm font-medium mb-2">Upload Image</label>
                            <input type="file" name="dashboard_image" accept="image/*" 
                                   class="w-full bg-[#1E1E2E] rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#D76D77]">
                        </div>

                        <button type="submit" class="bg-[#D76D77] text-white px-6 py-2 rounded-lg hover:bg-[#C55C66] transition-colors">
                            Upload Image
                        </button>
                    </form>


                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-[#D76D77] text-white px-6 py-2 rounded-lg hover:bg-[#C55C66] transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
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
</body>
</html>