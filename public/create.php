<?php
// Database connection using the DATABASE_URL environment variable
$dsn = getenv('DATABASE_URL'); // Get the connection string from the environment
if ($dsn) {
    // Parse the database URL into components
    $dbParts = parse_url($dsn);
    
    // Extract necessary parts from the URL
    $dbHost = $dbParts['host'];
    $dbPort = $dbParts['port'] ?? 5432; // Default to 5432 if no port is provided
    $dbName = ltrim($dbParts['path'], '/'); // Remove leading slash
    $dbUser = $dbParts['user'];
    $dbPassword = $dbParts['pass'];

    try {
        // Create the PDO connection string for PostgreSQL
        $pdo = new PDO("pgsql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize inputs
            $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $filePath = null;

            // Handle file upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';

                // Validate file upload (e.g., check file type, size)
                $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $filePath = $targetPath;
                } else {
                    $error = "File upload failed.";
                }
            }

            // Insert task into the database
            $stmt = $pdo->prepare("INSERT INTO tasks (title, description, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $filePath]);

            // Clear POST data to prevent resubmission
            $_POST = array();

            // Redirect to index to avoid resubmitting the form
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        $error = "Database error: " . htmlspecialchars($e->getMessage());
    } catch (Exception $e) {
        $error = htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Create New Task</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form action="create.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="file">Attach File (optional)</label>
                <input type="file" id="file" name="file">
            </div>

            <button type="submit" class="submit-btn">Create Task</button>
            <a href="index.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
</body>
</html>
