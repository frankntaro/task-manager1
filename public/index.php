<?php
// Database connection using the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');  // Get connection string
if ($databaseUrl) {
    // Parse the database URL
    $dbParts = parse_url($databaseUrl);

    // Extract database connection details
    $dbHost = $dbParts['host'];
    $dbPort = $dbParts['port'] ?? 5432; // Default to 5432 if no port is provided
    $dbName = ltrim($dbParts['path'], '/');
    $dbUser = $dbParts['user'];
    $dbPassword = $dbParts['pass'];

    try {
        // Establish the PDO connection
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch all tasks
        $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
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
            <?php if (isset($tasks) && !empty($tasks)): ?>
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
            <?php else: ?>
                <p>No tasks found.</p>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
