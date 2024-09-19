<?php
// Initialize an empty array for storing errors
$errors = [];
$success = '';

// Include database configuration
require 'config.php'; // Ensure this path is correct

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validation
    if (empty($email)) {
        $errors[] = 'Email is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // If there are no errors, proceed with password reset
    if (empty($errors)) {
        // Generate a unique token and expiration timestamp
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour

        // Insert into password_resets table
        try {
            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) 
                                    SELECT id, :token, :expires_at FROM users WHERE email = :email");
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at
            ]);

            // Send password reset email (example URL)
            $resetLink = "http://localhost/reset_password_form.php?token=$token";
            $message = "Click the following link to reset your password: $resetLink";
            mail($email, 'Password Reset', $message);

            $success = 'Password reset link has been sent to your email.';
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
    <title>News Today - Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .btn:hover {
            background-color: #218838;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <!-- Display errors from server-side -->
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php echo implode('<br>', $errors); ?>
        </div>
    <?php endif; ?>

    <!-- Display success message -->
    <?php if ($success): ?>
        <div class="success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
        </div>

        <button type="submit" class="btn">Send Reset Link</button>
    </form>
</div>

</body>
</html>
