<?php
require_once 'db_connect.php';
session_start();

$success = $error = '';

// Delete lesson
if (isset($_POST['delete'])) {
    $lesson_id = $_POST['delete'];
    try {
        $stmt = $pdo->prepare("SELECT file_path, video_path, image_path FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);

        // Delete associated files
        if ($lesson['file_path'] && file_exists($lesson['file_path'])) unlink($lesson['file_path']);
        if ($lesson['video_path'] && file_exists($lesson['video_path'])) unlink($lesson['video_path']);
        if ($lesson['image_path'] && file_exists($lesson['image_path'])) unlink($lesson['image_path']);

        $success = "Хичээл амжилттай устгагдлаа.";
    } catch (PDOException $e) {
        $error = "Хичээл устгахад алдаа гарлаа: " . $e->getMessage();
    }
}

// Fetch all lessons
try {
    $stmt = $pdo->query("SELECT * FROM lessons ORDER BY created_at DESC");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Хичээлүүдийг авахад алдаа гарлаа: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хичээлүүд</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .btn-add {
            background-color: #4CAF50;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }
        .btn-edit {
            background-color: #0288D1;
        }
        .btn-delete {
            background-color: #F44336;
        }
        .success, .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: white;
        }
        .success {
            background-color: #4CAF50;
        }
        .error {
            background-color: #F44336;
        }
        .lesson {
            display: flex;
            flex-direction: column;
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .lesson h2 {
            margin: 0 0 10px;
            color: #444;
            font-size: 20px;
        }
        .lesson p {
            margin: 5px 0;
            color: #555;
        }
        .lesson img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .actions {
            margin-top: 15px;
        }
        .actions a,
        .actions button {
            margin-right: 10px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .actions button {
            padding: 10px 15px;
            color: white;
            border-radius: 5px;
            font-size: 14px;
        }
        footer {
            text-align: center;
            margin-top: 20px;
        }
        .back-link {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    color: #fff;
    background-color: #4CAF50;
    padding: 10px 20px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: bold;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: fixed; /* Байрлал тогтмол */
    bottom: 20px; /* Дэлгэцийн доороос 20px */
    left: 20px; /* Дэлгэцийн зүүнээс 20px */
    z-index: 1000; /* Бусад элементээс дээгүүр харагдах */
}
.back-link:hover {
    opacity: 0.9;
}
.back-link span {
    margin-left: 8px;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Хичээлүүд</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <a href="nemelthicheel.php" class="btn btn-add">+ Шинэ хичээл нэмэх</a>

        <?php foreach ($lessons as $lesson): ?>
            <div class="lesson">
                <h2><?php echo htmlspecialchars($lesson['name']); ?></h2>
                <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                <p><strong>Үүсгэсэн огноо:</strong> <?php echo htmlspecialchars($lesson['created_at']); ?></p>
                
                <?php if ($lesson['file_path']): ?>
                    <p><strong>Файл:</strong> <a href="<?php echo htmlspecialchars($lesson['file_path']); ?>" target="_blank">Татах</a></p>
                <?php endif; ?>

                <?php if ($lesson['video_path']): ?>
                    <p><strong>Видео:</strong> <a href="<?php echo htmlspecialchars($lesson['video_path']); ?>" target="_blank">Үзэх</a></p>
                <?php endif; ?>

                <?php if ($lesson['video_link']): ?>
                    <p><strong>Видео линк:</strong> <a href="<?php echo htmlspecialchars($lesson['video_link']); ?>" target="_blank">Үзэх</a></p>
                <?php endif; ?>

                <?php if ($lesson['image_path']): ?>
                    <p><strong>Зураг:</strong></p>
                    <img src="<?php echo htmlspecialchars($lesson['image_path']); ?>" alt="Lesson Image">
                <?php endif; ?>

                <div class="actions">
                    <a href="zasakhicheel.php?id=<?php echo $lesson['id']; ?>" class="btn btn-edit">Засах</a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Энэ хичээлийг устгахдаа итгэлтэй байна уу?');">
                        <button type="submit" name="delete" value="<?php echo $lesson['id']; ?>" class="btn btn-delete">Устгах</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <footer>
    <a href="admin.php" class="back-link">
        <span>Буцах</span>
    </a>
    </footer>
</body>
</html>
