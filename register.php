<?php

// Start the session
session_start(); 

// Include database configuration
require 'config.php'; // Ensure this path is correct

// Redirect to homepage if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to the homepage or another page
    exit();
}

// Initialize an empty array for storing errors
$errors = [];
$success = '';

// Include database configuration
require 'config.php'; // Ensure this path is correct

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'All fields are required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Strong password validation
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    if (!preg_match($passwordRegex, $password)) {
        $errors[] = 'Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        // Secure password handling
        $salt = bin2hex(random_bytes(16)); // Generate random salt
        $pepper = PEPPER; // Retrieve pepper from config file

        if (empty($pepper)) {
            $errors[] = 'Pepper value is missing in the config file.';
        } else {
            $hashed_password = hash('sha256', $salt . $password . $pepper); // SHA-256 hashing with salt and pepper

            // Insert into the database (using prepared statements to prevent SQL injection)
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, salt, role) VALUES (:name, :email, :password, :salt, 'user')");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':salt' => $salt,
                ]);
                $success = "Registration successful!";
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Today - Register</title>
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

        input[type="text"], input[type="email"], input[type="password"] {
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
    <script>
        function validateForm() {
            let name = document.getElementById("name").value.trim();
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();
            let confirmPassword = document.getElementById("confirm_password").value.trim();
            let errors = [];

            if (name === "" || email === "" || password === "" || confirmPassword === "") {
                errors.push("All fields are required.");
            }

            if (!/\S+@\S+\.\S+/.test(email)) {
                errors.push("Invalid email format.");
            }

            let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!passwordRegex.test(password)) {
                errors.push("Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
            }

            if (password !== confirmPassword) {
                errors.push("Passwords do not match.");
            }

            if (errors.length > 0) {
                document.getElementById("error-messages").innerHTML = errors.join("<br>");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Register</h2>

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

    <form method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit" class="btn">Register</button>
        <a href="login.php" class="btn btn-login">Login</a>
    </form>
</div>

</body>
</html>
