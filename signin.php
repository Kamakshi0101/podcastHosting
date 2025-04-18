<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare('SELECT id, first_name, last_name, password_hash FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            header('Location: dashboard_index.php');
            exit();
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Podcast Platform</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1E1E2E] min-h-screen">
    <nav>
        <div class="logo">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <circle cx="20" cy="20" r="18" stroke="#FF4081" stroke-width="4"/>
                <rect x="15" y="12" width="4" height="16" rx="2" fill="#FF4081"/>
                <rect x="21" y="8" width="4" height="24" rx="2" fill="#FF4081"/>
            </svg>
            <span>PodcastPro</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="index.php#about">About Us</a>
            <a href="index.php#features">Features</a>
            <a href="index.php#how-it-works">How It Works</a>
            <a href="index.php#contact">Contact Us</a>
        </div>
        <div class="auth-buttons">
            <button class="login p-2 w-24" onclick="window.location.href='signin.php'">Log in</button>
            <button class="signup p-2 w-24" onclick="window.location.href='signup.php'">Sign up</button>
        </div>
    </nav>
    
    <div class="flex items-center justify-center flex-grow pt-20">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-[#2E2E4E] rounded-lg p-8 shadow-lg text-white">
            <h2 class="text-2xl font-semibold mb-6 text-white">Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-[#D76D77] text-sm font-bold mb-2" for="signin-username">Username</label>
                    <input type="text" name="username" id="signin-username" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">
                </div>
                <div class="mb-6">
                    <label class="block text-[#D76D77] text-sm font-bold mb-2" for="signin-password">Password</label>
                    <input type="password" name="password" id="signin-password" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" name="signin" class="bg-gradient-to-r from-[#4A1E73] to-[#D76D77] text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full hover:opacity-90 transition-opacity">
                        Sign In
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-gray-400">Don't have an account? <a href="signup.php" class="text-[#D76D77] hover:text-[#D76D77]/80">Sign Up</a></p>
            <p class="mt-2 text-center"><a href="index.php" class="text-gray-500 hover:text-gray-400">Back to Home</a></p>
        </div>
    </div>
</body>
</html>