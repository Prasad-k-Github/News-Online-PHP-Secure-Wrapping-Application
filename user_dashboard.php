<?php
// Start the session
session_start();

// Include database configuration
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch news articles from the database
try {
    $stmt = $pdo->query("SELECT title, content, created_at FROM news ORDER BY created_at DESC");
    $newsArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - News Today</title>
    <a href="logout.php" class="btn">Logout</a>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .container {
            padding: 20px;
            max-width: 800px;
            margin: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .news-article {
            margin-bottom: 20px;
        }

        .news-article h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #333;
        }

        .news-article p {
            font-size: 16px;
            color: #666;
        }

        .news-article .date {
            font-size: 14px;
            color: #999;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #007bff;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>News Today - User Dashboard</h1>
</div>

<div class="container">
    <?php if (!empty($newsArticles)): ?>
        <?php foreach ($newsArticles as $article): ?>
            <div class="news-article">
                <h2><?php echo htmlspecialchars($article['title']); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>
                <div class="date">Published on: <?php echo htmlspecialchars($article['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No news articles available.</p>
    <?php endif; ?>
</div>

<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> News Today. All rights reserved.</p>
</div>

</body>
</html>
