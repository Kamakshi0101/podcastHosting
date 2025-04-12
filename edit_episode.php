<?php
include 'db.php';

if (!isset($_GET['id'])) {
    header('Location: myepisode.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM episodes WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$episode = mysqli_fetch_assoc($result);

if (!$episode) {
    header('Location: myepisode.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = isset($_POST['status']) ? 1 : 0;

    $update_query = "UPDATE episodes SET 
                    title = '$title',
                    description = '$description',
                    category = '$category',
                    status = '$status'
                    WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        header('Location: myepisode.php');
        exit();
    } else {
        $error = "Error updating episode: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Episode</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .custom-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
</head>
<body class="bg-[#1E1E2E] text-white min-h-screen p-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="myepisode.php" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons">arrow_back</span>
            </a>
            <h1 class="text-2xl font-bold">Edit Episode</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-500 text-white p-4 rounded mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Title</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($episode['title']); ?>" 
                               class="w-full p-2 rounded bg-[#1E1E2E] border border-[#4A1E73] focus:ring-2 focus:ring-[#4A1E73] focus:border-transparent transition-all" 
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Category</label>
                        <select name="category" 
                                class="w-full p-2 rounded bg-[#1E1E2E] border border-[#4A1E73] focus:ring-2 focus:ring-[#4A1E73] focus:border-transparent custom-select transition-all">
                            <option value="education" <?php echo ($episode['category'] == 'education') ? 'selected' : ''; ?>>Education</option>
                            <option value="entertainment" <?php echo ($episode['category'] == 'entertainment') ? 'selected' : ''; ?>>Entertainment</option>
                            <option value="news" <?php echo ($episode['category'] == 'news') ? 'selected' : ''; ?>>News</option>
                            <option value="technology" <?php echo ($episode['category'] == 'technology') ? 'selected' : ''; ?>>Technology</option>
                            <option value="business" <?php echo ($episode['category'] == 'business') ? 'selected' : ''; ?>>Business</option>
                            <option value="other" <?php echo ($episode['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4" 
                              class="w-full p-2 rounded bg-[#1E1E2E] border border-[#4A1E73] focus:ring-2 focus:ring-[#4A1E73] focus:border-transparent transition-all"><?php echo htmlspecialchars($episode['description']); ?></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="status" id="status" 
                           <?php echo $episode['status'] ? 'checked' : ''; ?> 
                           class="rounded bg-[#1E1E2E] border border-[#4A1E73] text-[#4A1E73] focus:ring-[#4A1E73] transition-all">
                    <label for="status" class="text-sm font-medium">Published</label>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" 
                            class="bg-[#4A1E73] px-6 py-2 rounded hover:bg-[#3A1C71] transition-all flex items-center gap-2">
                        <span class="material-icons text-sm">save</span>
                        Save Changes
                    </button>
                    <a href="myepisode.php" 
                       class="bg-gray-600 px-6 py-2 rounded hover:bg-gray-700 transition-all flex items-center gap-2">
                        <span class="material-icons text-sm">close</span>
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-6 bg-[#2E2E4E] p-6 rounded-lg shadow-lg">
            <h2 class="text-lg font-semibold mb-4 text-[#FFAF7B]">Media Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-300">
                <div>
                    <p class="text-sm">File Name:</p>
                    <p class="font-medium"><?php echo htmlspecialchars($episode['file_path']); ?></p>
                </div>
                <div>
                    <p class="text-sm">Upload Date:</p>
                    <p class="font-medium"><?php echo date('F j, Y', strtotime($episode['upload_date'])); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>