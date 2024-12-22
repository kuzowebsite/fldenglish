<?php
require_once 'db_connect.php';
session_start();

// Initialize error/success messages
$error = '';
$success = '';

// Function to create directory if it doesn't exist
function createDirectoryIfNotExists($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Create upload directories
createDirectoryIfNotExists('uploads/files');
createDirectoryIfNotExists('uploads/videos');
createDirectoryIfNotExists('uploads/images');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_name = trim($_POST['lesson_name']);
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);
    
    // Basic validation
    if (empty($lesson_name) || empty($description)) {
        $error = "Хичээлийн нэр болон тайлбар заавал шаардлагатай.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Handle file uploads
            $file_path = null;
            $video_path = null;
            $image_path = null;
            
            // File upload handling
            if (!empty($_FILES['file']['name'])) {
                $file_name = time() . '_' . $_FILES['file']['name'];
                $file_path = 'uploads/files/' . $file_name;
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                    throw new Exception("Файл хуулахад алдаа гарлаа: " . error_get_last()['message']);
                }
            }
            
            // Video upload handling
            if (!empty($_FILES['video']['name'])) {
                $video_name = time() . '_' . $_FILES['video']['name'];
                $video_path = 'uploads/videos/' . $video_name;
                if (!move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                    throw new Exception("Видео хуулахад алдаа гарлаа: " . error_get_last()['message']);
                }
            }
            
            // Image upload handling
            if (!empty($_FILES['image']['name'])) {
                $image_name = time() . '_' . $_FILES['image']['name'];
                $image_path = 'uploads/images/' . $image_name;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    throw new Exception("Зураг хуулахад алдаа гарлаа: " . error_get_last()['message']);
                }
            }
            
            // Insert into database
            $sql = "INSERT INTO lessons (name, description, file_path, video_path, image_path, video_link, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$lesson_name, $description, $file_path, $video_path, $image_path, $video_link]);
            
            $pdo->commit();
            $success = "Хичээл амжилттай нэмэгдлээ!";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Алдаа гарлаа: " . $e->getMessage();
            
            // Delete uploaded files if there was an error
            if ($file_path && file_exists($file_path)) unlink($file_path);
            if ($video_path && file_exists($video_path)) unlink($video_path);
            if ($image_path && file_exists($image_path)) unlink($image_path);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хичээл нэмэх</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"], input[type="url"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, input[type="url"]:focus, textarea:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        textarea {
            height: 120px;
            resize: none;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s;
        }

        button:hover {
            background-color: #45a049;
            transform: scale(1.02);
        }

        button:active {
            background-color: #3d8f42;
        }

        .error, .success {
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .error {
            color: #ff0000;
            background: #ffe6e6;
        }

        .success {
            color: #4CAF50;
            background: #e8f5e9;
        }

        .optional {
            font-size: 12px;
            color: #888;
        }

        .back-link {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        color: #4CAF50;
        background-color: #f1f8e9;
        padding: 10px 20px;
        border-radius: 5px;
        border: 1px solid #4CAF50;
        transition: all 0.3s ease;
    }

    .back-link:hover {
        color: white;
        background-color: #4CAF50;
        border-color: #388E3C;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .back-link:active {
        background-color: #388E3C;
        transform: translateY(0);
    }
    </style>
</head>
<body>
    <h1>Хичээл нэмэх</h1>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="lesson_name">Хичээлийн нэр:</label>
            <input type="text" id="lesson_name" name="lesson_name" required>
        </div>

        <div class="form-group">
            <label for="description">Тайлбар:</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="form-group">
            <label for="file">
                Файл оруулах: <span class="optional">(Заавал биш)</span>
            </label>
            <input type="file" id="file" name="file">
        </div>

        <div class="form-group">
            <label for="video">
                Видео оруулах: <span class="optional">(Заавал биш)</span>
            </label>
            <input type="file" id="video" name="video" accept="video/*">
        </div>

        <div class="form-group">
            <label for="video_link">
                Видео линк: <span class="optional">(Заавал биш)</span>
            </label>
            <input type="url" id="video_link" name="video_link" placeholder="https://www.youtube.com/watch?v=...">
        </div>

        <div class="form-group">
            <label for="image">
                Зураг оруулах: <span class="optional">(Заавал биш)</span>
            </label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <button type="submit">Хадгалах</button>
    </form>

    <a href="admin.php" class="back-link">← Буцах</a>
</body>
</html>
