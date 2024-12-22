<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $class = $_POST['class'];

    // Check if username already exists
    $check_stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
    $check_stmt->execute([$username]);
    
    if ($check_stmt->rowCount() > 0) {
        $error = "Username already exists. Please choose another username.";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, class, user_type) VALUES (?, ?, ?, ?, 'student')");
        if ($stmt->execute([$username, $password, $email, $class])) {
            $success = "Registration successful. You can now login.";
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

// Fetch available classes
$classes_stmt = $pdo->query("SELECT * FROM classes");
$classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сурагчийн бүртгэл</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            padding: 20px;
        }

        .logo {
    text-align: center;
    margin-bottom: 20px;
}

.logo img {
    max-width: 150px;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}


        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 400px;
            width: 100%;
        }

        .logo {
    text-align: center;
    margin-bottom: 20px;
}

.logo img {
    max-width: 150px;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}


        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            font-size: 14px;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }

        input[type="submit"] {
            background: #6a11cb;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background: #2575fc;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .success {
            color: #2ecc71;
            font-size: 14px;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        a {
            color: #2575fc;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Responsive design for smaller devices */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }

            label {
                font-size: 13px;
            }

            input, select {
                font-size: 13px;
            }

            input[type="submit"] {
                padding: 10px;
                font-size: 14px;
            }

            p {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">

    <div class="logo">
    <img src="logo.jpeg" alt="FLD Logo">
</div>

        <h2>Сурагчийн бүртгэл</h2>

        <?php
        if (isset($error)) echo "<p class='error'>$error</p>";
        if (isset($success)) echo "<p class='success'>$success</p>";
        ?>

        <form method="post">
            <div class="form-group">
                <label for="username">Хэрэглэгчийн нэр:</label>
                <input type="text" id="username" name="username" placeholder="Хэрэглэгчийн нэрээ оруулна уу" required>
            </div>

            <div class="form-group">
                <label for="password">Нууц үг:</label>
                <input type="password" id="password" name="password" placeholder="Нууц үгээ оруулна уу" required>
            </div>

            <div class="form-group">
                <label for="email">И-мэйл:</label>
                <input type="email" id="email" name="email" placeholder="И-мэйл хаягаа оруулна уу" required>
            </div>

            <div class="form-group">
                <label for="class">Анги:</label>
                <select id="class" name="class" required>
                    <option value="">Анги сонгох</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class['name']); ?>">
                            <?php echo htmlspecialchars($class['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <input type="submit" value="Бүртгүүлэх">
            </div>
        </form>

        <p>Хэрэв данс байгаа бол? <a href="login.php">Нэвтрэх</a></p>
    </div>
</body>
</html>
