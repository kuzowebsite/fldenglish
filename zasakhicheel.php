<?php
require_once 'db_connect.php';
session_start();

$error = $success = '';

if (!isset($_GET['id'])) {
    header("Location: nemelthicheelharah.php");
    exit();
}

$lesson_id = $_GET['id'];

// Fetch the lesson
try {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
    $stmt->execute([$lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        header("Location: nemelthicheelharah.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Хичээлийн мэдээллийг авахад алдаа гарлаа: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lesson_name = trim($_POST['lesson_name']);
    $description = trim($_POST['description']);
    $video_link = trim($_POST['video_link']);
    
    if (empty($lesson_name) || empty($description)) {
        $error = "Хичээлийн нэр болон тайлбар заавал шаардлагатай.";
    } else {
        try {
            $pdo->beginTransaction();
            
            $file_path = $lesson['file_path'];
            $video_path = $lesson['video_path'];
            $image_path = $lesson['image_path'];
            
            // File upload handling
            if (!empty($_FILES['file']['name'])) {
                $file_name = time() . '_' . $_FILES['file']['name'];
                $new_file_path = 'uploads/files/' . $file_name;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $new_file_path)) {
                    if ($file_path && file_exists($file_path)) unlink($file_path);
                    $file_path = $new_file_path;
                }
            }
            
            // Video upload handling
            if (!empty($_FILES['video']['name'])) {
                $video_name = time() . '_' . $_FILES['video']['name'];
                $new_video_path = 'uploads/videos/' . $video_name;
                if (move_uploaded_file($_FILES['video']['tmp_name'], $new_video_path)) {
                    if ($video_path && file_exists($video_path)) unlink($video_path);
                    $video_path = $new_video_path;
                }
            }
            
            // Image upload handling
            if (!empty($_FILES['image']['name'])) {
                $image_name = time() . '_' . $_FILES['image']['name'];
                $new_image_path = 'uploads/images/' . $image_name;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path)) {
                    if ($image_path && file_exists($image_path)) unlink($image_path);
                    $image_path = $new_image_path;
                }
            }
            
            // Update database
            $sql = "UPDATE lessons SET name = ?, description = ?, file_path = ?, video_path = ?, image_path = ?, video_link = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$lesson_name, $description, $file_path, $video_path, $image_path, $video_link, $lesson_id]);
            
            $pdo->commit();
            $success = "Хичээл амжилттай шинэчлэгдлээ!";
            
            // Refresh lesson data
            $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
            $stmt->execute([$lesson_id]);
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Алдаа гарлаа: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хичээл засах</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea, input[type="url"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn:hover {
            background: #444;
        }
        .error, .success {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Хичээл засах</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="lesson_name">Хичээлийн нэр:</label>
                <input type="text" id="lesson_name" name="lesson_name" value="<?php echo htmlspecialchars($lesson['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Тайлбар:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($lesson['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="file">Файл: <?php echo $lesson['file_path'] ? '(Одоогийн файл: ' . basename($lesson['file_path']) . ')' : ''; ?></label>
                <input type="file" id="file" name="file">
            </div>

            <div class="form-group">
                <label for="video">Видео: <?php echo $lesson['video_path'] ? '(Одоогийн видео: ' . basename($lesson['video_path']) . ')' : ''; ?></label>
                <input type="file" id="video" name="video" accept="video/*">
            </div>

            <div class="form-group">
                <label for="video_link">Видео линк:</label>
                <input type="url" id="video_link" name="video_link" value="<?php echo htmlspecialchars($lesson['video_link']); ?>" placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div class="form-group">
                <label for="image">Зураг: <?php echo $lesson['image_path'] ? '(Одоогийн зураг: ' . basename($lesson['image_path']) . ')' : ''; ?></label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn">Хадгалах</button>
        </form>

        <a href="nemelthicheelharah.php" class="btn" style="margin-top: 20px;">Буцах</a>
    </div>

    <script>
        // Add client-side file size validation
        document.querySelector('form').onsubmit = function(e) {
            const maxSize = 50 * 1024 * 1024; // 50MB max size
            const files = ['file', 'video', 'image'];
            
            for (let file of files) {
                const input = document.getElementById(file);
                if (input.files.length > 0 && input.files[0].size > maxSize) {
                    alert('Файлын хэмжээ хэт их байна. 50MB-с бага оруулна уу.');
                    e.preventDefault();
                    return false;
                }
            }

            // Basic URL validation for video link
            const videoLink = document.getElementById('video_link').value.trim();
            if (videoLink !== '' && !videoLink.match(/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/)) {
                alert('Буруу видео линк байна. Зөвхөн YouTube линк оруулна уу.');
                e.preventDefault();
                return false;
            }

            return true;
        };
    </script>
</body>
</html>