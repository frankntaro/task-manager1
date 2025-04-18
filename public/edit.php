<?php
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Database connection
$host = getenv('MYSQL_HOST') ?: 'localhost';
$user = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';
$database = getenv('MYSQL_DB') ?: 'task_manager';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch task
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        header('Location: index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $filePath = $task['file_path'];
        $deleteFile = isset($_POST['delete_file']);
        
        // Handle file deletion
        if ($deleteFile && $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $filePath = null;
        }
        
        // Handle new file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            // Delete old file if exists
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }
            
            $uploadDir = 'uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $filePath = $targetPath;
            }
        }
        
        // Update task
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, file_path = ? WHERE id = ?");
        $stmt->execute([$title, $description, $filePath, $_GET['id']]);
        
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Task</h1>
        
        <form action="edit.php?id=<?= $task['id'] ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Current File:</label>
                <?php if ($task['file_path']): ?>
                    <?php if (strpos($task['file_path'], '.jpg') !== false || strpos($task['file_path'], '.png') !== false): ?>
                        <img src="<?= $task['file_path'] ?>" alt="Task Image" class="file-preview"><br>
                    <?php else: ?>
                        <a href="<?= $task['file_path'] ?>" class="uploaded-file" download>Download Current File</a><br>
                    <?php endif; ?>
                    <label>
                        <input type="checkbox" name="delete_file"> Delete current file
                    </label>
                <?php else: ?>
                    <span>No file attached</span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="file">New File (optional)</label>
                <input type="file" id="file" name="file">
            </div>
            
            <button type="submit" class="submit-btn">Update Task</button>
            <a href="index.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>