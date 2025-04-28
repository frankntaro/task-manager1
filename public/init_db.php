<?php
// Get the database URL from the environment
$dsn = getenv('DATABASE_URL'); // This will use the DATABASE_URL provided by Render
$user = '';  // Username will be part of the DATABASE_URL
$password = '';  // Password will be part of the DATABASE_URL
$database = '';  // Database will be part of the DATABASE_URL

try {
    $pdo = new PDO($dsn);
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
?>
