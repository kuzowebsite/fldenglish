<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$message = '';

// Хэрэглэгчийн мэдээллийг авах
$stmt = $pdo->prepare("SELECT username, email, password, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']); // Хэрэглэгчийн нэр
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($name) || empty($email)) {
        $message = "Нэр болон и-мэйл хаяг оруулна уу.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Зөв и-мэйл хаяг оруулна уу.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $message = "Шинэ нууц үг таарахгүй байна.";
    } else {
        // Хэрэглэгчийн мэдээллийг шинэчлэх
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $params = [$name, $email, $_SESSION['user_id']];

        if (!empty($new_password)) {
            $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $params = [$name, $email, $hashed_password, $_SESSION['user_id']];
        }

        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $message = "Профайл амжилттай шинэчлэгдлээ.";
            $_SESSION['name'] = $name;
        } else {
            $message = "Алдаа гарлаа. Дахин оролдоно уу.";
        }
    }

    // Зураг оруулах
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (!in_array(strtolower($filetype), $allowed)) {
            $message = "Зөвхөн JPG, JPEG, PNG, GIF файл оруулах боломжтой.";
        } else {
            $new_filename = uniqid() . "." . $filetype;
            $upload_path = "uploads/" . $new_filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Профайл зургийг шинэчлэх
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$upload_path, $_SESSION['user_id']]);
                $_SESSION['profile_image'] = $upload_path; // Сессийн өгөгдлийг шинэчлэх
                $message .= " Профайл зураг амжилттай шинэчлэгдлээ.";
            } else {
                $message .= " Профайл зураг оруулахад алдаа гарлаа.";
            }
        }
    }
}

// Шинэчлэгдсэн хэрэглэгчийн мэдээллийг дахин авах
$stmt = $pdo->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профайл засах</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color:rgb(255, 255, 255);
            color: black;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            outline: 2px solid black;
        }
        input[type="submit"]:hover {
            color:white;
            background-color: black;
            outline: 2px solid black;
        }

        .garh {
            background-color:rgb(255, 255, 255);
            color: black;
            padding: 5px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            outline: 2px solid black;
        }

        .garh:hover {
            color:white;
            background-color: black;
            outline: 2px solid black;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Профайл засах</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Алдаа') !== false ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <!-- Хэрэглэгчийн профайл зургийг харах -->
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image">

                <label for="profile_image">Профайл зураг:</label>
                <input type="file" id="profile_image" name="profile_image">
            </div>

            <div class="form-group">
                <label for="name">Нэр:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">И-мэйл:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Хэрэглэгчийн нэр:</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>

            <div class="form-group">
                <label for="new_password">Шинэ нууц үг (хоосон орхивол хэвээр үлдэнэ):</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Шинэ нууц үг баталгаажуулах:</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <input type="submit" value="Хадгалах">
        </form>
        
        <p><a href="student.php" class="garh">Буцах</a></p>
    </div>
</body>
</html>
