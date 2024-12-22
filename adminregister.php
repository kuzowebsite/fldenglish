<?php
session_start();
require_once 'db_connect.php';
require_once 'admin_key_functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, 'admin')");
    if ($stmt->execute([$username, $password, $email])) {
        $success = "Админ амжилттай бүртгэгдлээ.";
    } else {
        $error = "Бүртгэл амжилтгүй боллоо. Дахин оролдоно уу.";
    }
}

$currentKey = getCurrentAdminKey();

// Fetch the current admin key details
$keyDetails = null;
if ($currentKey) {
    $stmt = $pdo->query("SELECT id, key_hash, created_at, expires_at FROM admin_keys WHERE expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
$keyDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ Бүртгэл</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            background-color: #f4f4f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Админ Бүртгэл</h2>
    <?php 
    if (isset($error)) echo "<p class='message error'>$error</p>";
    if (isset($success)) echo "<p class='message success'>$success</p>";
    ?>
    <form method="post">
        <input type="text" name="username" placeholder="Хэрэглэгчийн нэр" required>
        <input type="password" name="password" placeholder="Нууц үг" required>
        <input type="email" name="email" placeholder="И-мэйл" required>
        <input type="submit" value="Бүртгүүлэх">
    </form>

    <a class="back-link" href="admin.php">Буцах</a>
</body>
</html>
