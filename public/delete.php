<?php
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

// Database connection using the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL'); // Get database URL from the environment
if ($databaseUrl) {
    // Parse the database URL into components
    $dbParts = parse_url($databaseUrl);

    // Extract necessary parts from the URL
    $dbHost = $dbParts['host'];
    $dbPort = $dbParts['port'] ?? 5432; // Default to 5432 if no port is provided
    $dbName = ltrim($dbParts['path'], '/'); // Remove leading slash
    $dbUser = $dbParts['user'];
    $dbPassword = $dbParts['pass'];

    try {
        // Create the PDO connection string
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch task to get file path
        $stmt = $pdo->prepare("SELECT file_path FROM tasks WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        // Delete file if it exists
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
}
?>
