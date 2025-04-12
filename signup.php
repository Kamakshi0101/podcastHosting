<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $check_user = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $check_user->bind_param('ss', $username, $email);
        $check_user->execute();
        $result = $check_user->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (username, first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $username, $first_name, $last_name, $email, $password_hash);

            if ($stmt->execute()) {
                $success = 'Account created successfully! Please sign in.';
                header('Location: signin.php');
                exit();
            } else {
                $error = 'Error creating account';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Podcast Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1E1E2E] min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-[#2E2E4E] rounded-lg p-8 shadow-lg text-white">
            <h2 class="text-2xl font-semibold mb-6 text-white">Create Your Account</h2>
            <?php if ($error): ?>
                <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-900/50 border border-green-500 text-green-200 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="signup-username">Username</label>
                    <input type="text" name="username" id="signup-username" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="signup-firstname">First Name</label>
                    <input type="text" name="first_name" id="signup-firstname" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="signup-lastname">Last Name</label>
                    <input type="text" name="last_name" id="signup-lastname" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="signup-email">Email</label>
                    <input type="email" name="email" id="signup-email" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="signup-password">Password</label>
                    <input type="password" name="password" id="signup-password" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 text-sm font-bold mb-2" for="confirm-password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm-password" required
                        class="bg-[#1E1E2E] shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 text-white leading-tight focus:outline-none focus:border-[#D76D77] focus:ring-1 focus:ring-[#D76D77]">

                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" name="signup" class="bg-gradient-to-r from-[#4A1E73] to-[#D76D77] text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full hover:opacity-90 transition-opacity">
                        Sign Up
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-gray-400">Already have an account? <a href="signin.php" class="text-[#D76D77] hover:text-[#D76D77]/80">Sign In</a></p>
            <p class="mt-2 text-center"><a href="index.php" class="text-gray-500 hover:text-gray-400">Back to Home</a></p>
        </div>
    </div>
</body>
</html>