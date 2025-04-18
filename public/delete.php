<?php
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Database connection
$host = getenv('MYSQL_HOST') ?: 'db'; // Docker service name
$user = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';
$database = getenv('MYSQL_DB') ?: 'task_manager';

try {
    $dsn = "mysql:host=$host;port=3306;dbname=$database";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch task to get file path
    $stmt = $pdo->prepare("SELECT file_path FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete file if exists
    if ($task && $task['file_path'] && file_exists($task['file_path'])) {
        unlink($task['file_path']);
    }

    // Delete task
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
