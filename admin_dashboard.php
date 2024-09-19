<?php
// Start session and include database configuration
session_start();
require 'config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize variables
$news = [];
$users = [];
$success = '';
$errors = [];

// Fetch news
try {
    $stmt = $pdo->query("SELECT * FROM news");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Error fetching news: ' . $e->getMessage();
}

// Fetch users
try {
    $stmt = $pdo->query("SELECT id, name, email, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Error fetching users: ' . $e->getMessage();
}

// Handle add news
if (isset($_POST['add_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $errors[] = 'Title and content are required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO news (title, content, created_at) VALUES (:title, :content, NOW())");
            $stmt->execute([
                ':title' => $title,
                ':content' => $content
            ]);
            $success = 'News added successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle update news
if (isset($_POST['update_news'])) {
    $newsId = $_POST['news_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $errors[] = 'Title and content are required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE news SET title = :title, content = :content WHERE id = :id");
            $stmt->execute([
                ':title' => $title,
                ':content' => $content,
                ':id' => $newsId
            ]);
            $success = 'News updated successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle delete news
if (isset($_GET['action']) && $_GET['action'] === 'delete_news' && isset($_GET['id'])) {
    $newsId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
        $stmt->execute([':id' => $newsId]);
        $success = 'News deleted successfully!';
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }

    header('Location: admin_dashboard.php');
    exit();
}

// Handle delete user
if (isset($_GET['action']) && $_GET['action'] === 'delete_user' && isset($_GET['id'])) {
    $userId = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $success = 'User deleted successfully!';
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }

    header('Location: admin_dashboard.php');
    exit();
}

// Handle change user role
if (isset($_POST['change_role'])) {
    $userId = $_POST['user_id'];
    $newRole = trim($_POST['role']);

    if (empty($newRole) || !in_array($newRole, ['user', 'admin'])) {
        $errors[] = 'Invalid role.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
            $stmt->execute([
                ':role' => $newRole,
                ':id' => $userId
            ]);
            $success = 'User role updated successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* Include internal CSS for styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            margin-right: 5px;
        }
        .btn-add {
            background-color: #007bff;
        }
        .btn-add:hover {
            background-color: #0056b3;
        }
        .btn-edit {
            background-color: #ffc107;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn-submit {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .error {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Admin Dashboard</h1>
    <a href="logout.php" class="btn">Logout</a>

    <?php if (!empty($errors)): ?>
        <div class="error"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Form for adding news -->
    <h2>Add News</h2>
    <form method="POST">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="5" required></textarea>
        </div>
        <button type="submit" name="add_news" class="btn-submit">Add News</button>
    </form>

    <!-- Display news -->
    <h2>Manage News</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Content</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($news as $newsItem): ?>
                <tr>
                    <td><?php echo htmlspecialchars($newsItem['id']); ?></td>
                    <td><?php echo htmlspecialchars($newsItem['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($newsItem['content'], 0, 50)) . '...'; ?></td>
                    <td><?php echo htmlspecialchars($newsItem['created_at']); ?></td>
                    <td>
                        <a href="admin_dashboard.php?action=edit_news&id=<?php echo htmlspecialchars($newsItem['id']); ?>" class="btn btn-edit">Edit</a>
                        <a href="admin_dashboard.php?action=delete_news&id=<?php echo htmlspecialchars($newsItem['id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this news item?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Display users -->
    <h2>Manage Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <select name="role">
                                <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" name="change_role" class="btn btn-edit">Change Role</button>
                        </form>
                        <a href="admin_dashboard.php?action=delete_user&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
