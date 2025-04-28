<?php
// Database connection using the DATABASE_URL environment variable
$dsn = getenv('DATABASE_URL');
try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
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
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
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
