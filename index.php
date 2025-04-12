<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Podcast Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1E1E2E] min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h1 class="text-5xl font-bold mb-6">Your Podcast Journey Starts Here</h1>
            <p class="text-xl mb-12">Create, host, and share your podcasts with the world</p>

            <div class="flex justify-center gap-6 mb-8">
                <a href="signup.php" class="bg-gradient-to-r from-[#4A1E73] to-[#D76D77] text-white px-8 py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">Sign Up</a>
                <a href="signin.php" class="bg-[#2E2E4E] text-white px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition duration-300 shadow-lg">Sign In</a>
            </div>

            <!-- Features Section -->
            <div class="grid md:grid-cols-3 gap-8 mt-16">
                <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                    <h3 class="text-2xl font-semibold mb-4">Easy Hosting</h3>
                    <p class="text-gray-300">Upload and manage your podcast episodes with just a few clicks</p>
                </div>
                <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                    <h3 class="text-2xl font-semibold mb-4">Analytics</h3>
                    <p class="text-gray-300">Track your audience engagement and growth with detailed analytics</p>
                </div>
                <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
                    <h3 class="text-2xl font-semibold mb-4">Distribution</h3>
                    <p class="text-gray-300">Reach listeners across all major podcast platforms</p>
                </div>
            </div>
        </div>
    </div>


</body>
</html>