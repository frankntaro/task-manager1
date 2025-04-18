<?php
// Database connection
$host = getenv('MYSQL_HOST') ?: 'db'; // Updated fallback from 'localhost' to 'db'
$user = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';
$database = getenv('MYSQL_DB') ?: 'task_manager';

try {
    // Added port to enforce TCP/IP connection
    $dsn = "mysql:host=$host;port=3306;dbname=$database";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all tasks
    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Task Manager</h1>
        
        <a href="create.php" class="add-btn">Add New Task</a>

        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
            <li class="task-item">
                <div>
                    <span class="task-title"><?= htmlspecialchars($task['title']) ?></span>
                    <p><?= htmlspecialchars($task['description']) ?></p>
                    <?php if ($task['file_path']): ?>
                        <?php if (strpos($task['file_path'], '.jpg') !== false || strpos($task['file_path'], '.png') !== false): ?>
                            <img src="uploads/<?= basename($task['file_path']) ?>" alt="Task Image" class="file-preview">
                        <?php else: ?>
                            <a href="uploads/<?= basename($task['file_path']) ?>" class="uploaded-file" download>Download File</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="task-actions">
                    <a href="edit.php?id=<?= $task['id'] ?>" class="edit-btn">Edit</a>
                    <a href="delete.php?id=<?= $task['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
