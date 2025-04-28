<?php
// Get the database URL from the environment
$databaseUrl = getenv('DATABASE_URL'); // This will use the DATABASE_URL provided by Render

// Parse the database URL into components
if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);

    // Extract the necessary parts from the URL
    $dbHost = $dbParts['host'];
    $dbPort = $dbParts['port'] ?? 5432; // Default to 5432 if no port is provided
    $dbName = ltrim($dbParts['path'], '/'); // Remove the leading slash
    $dbUser = $dbParts['user'];
    $dbPassword = $dbParts['pass'];

    try {
        // Create the PDO connection string
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            file_path VARCHAR(255)
        )");

        echo "Database setup completed successfully.";
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    die("Database URL not found.");
}
?>
