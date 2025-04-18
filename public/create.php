<?php
// Add cache control headers at the VERY TOP
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Fetch recent tasks for the side panel
$recentTasks = [];
try {
    $host = getenv('MYSQL_HOST') ?: 'db'; // updated
    $user = getenv('MYSQL_USER') ?: 'root';
    $password = getenv('MYSQL_PASSWORD') ?: '';
    $database = getenv('MYSQL_DB') ?: 'task_manager';

    $dsn = "mysql:host=$host;port=3306;dbname=$database"; // force TCP
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT * FROM tasks ORDER BY created_at DESC LIMIT 5");
    $recentTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently fail, recent tasks panel just won't show
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $host = getenv('MYSQL_HOST') ?: 'db'; // updated
    $user = getenv('MYSQL_USER') ?: 'root';
    $password = getenv('MYSQL_PASSWORD') ?: '';
    $database = getenv('MYSQL_DB') ?: 'task_manager';

    try {
        $dsn = "mysql:host=$host;port=3306;dbname=$database"; // force TCP
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Sanitize inputs
        $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $filePath = null;

        // Handle file upload
        if (isset($_FILES['file'])) {
            $uploadDir = 'uploads/';

            // Validate file upload
            if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
                $targetPath = $uploadDir . $fileName;

                // Create directory if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    $filePath = $targetPath;
                }
            }
        }

        // Insert task
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$title, $description, $filePath]);

        // Clear POST data
        $_POST = array();

        // Redirect to prevent form resubmission
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
</head>
<body>
    <div class="container">
        <h1>WELCOME TO THE TASK MANAGEMENT SYSTEM</h1>
        <h2>Create New Task</h2>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="create.php" method="post" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label data-aos="fade-left" for="title">Title</label>
                <input data-aos="fade-right" type="text" id="title" name="title" 
                       value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label data-aos="fade-left" for="description">Description</label>
                <textarea data-aos="fade-up" id="description" name="description" required class="box"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label data-aos="fade-left" for="file">Attach File (optional)</label>
                <input data-aos="fade-up" type="file" id="file" name="file">
            </div>

            <button type="submit" class="submit-btn">Create Task</button>
            <a href="index.php" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>

    <!-- Recent Tasks Panel -->
    <div class="view-existing-tasks" data-aos="fade-left">
        <a href="index.php" class="view-all-btn">View All previous created Tasks</a>
    </div>

    <div class="credit"> &copy; copyright @ <?= date('Y') ?> by <span>Mr.FRANK software developer. All rights reserved.</span></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            delay: 300
        }); 
    </script>
</body>
</html>
