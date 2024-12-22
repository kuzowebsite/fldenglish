<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Хичээлийн ID-г URL-аас авах
if (!isset($_GET['id'])) {
    die("Хичээлийн ID байхгүй байна!");
}

$lesson_id = intval($_GET['id']);

// Хичээлийн дэлгэрэнгүй мэдээллийг авах
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("Ийм хичээл байхгүй байна!");
}

// YouTube богино холбоосыг Embed формат руу хөрвүүлэх
if (strpos($lesson['video_link'], 'youtu.be') !== false) {
    // Богино холбоосыг хөрвүүлэх
    $lesson['video_link'] = str_replace("youtu.be/", "www.youtube.com/embed/", $lesson['video_link']);
} elseif (strpos($lesson['video_link'], 'youtube.com/watch') !== false) {
    // Уламжлалт холбоосыг хөрвүүлэх
    $lesson['video_link'] = str_replace("watch?v=", "embed/", $lesson['video_link']);
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            font-size: 24px;
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            text-align: center;
        }

        .content img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .content p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: justify;
        }

        .content video {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .btn-container a {
            display: inline-block;
            padding: 10px 15px;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            background-color: #007bff;
            border-radius: 5px;
            transition: background-color 0.2s;
        }

        .btn-container a:hover {
            background-color: #0056b3;
        }

        .btn-container a.download-btn {
            background-color: #28a745;
        }

        .btn-container a.download-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($lesson['name']); ?></h2>
        <p><?php echo htmlspecialchars($lesson['description']); ?></p>
        <div class="content">
            <?php if ($lesson['image_path']): ?>
                <img src="<?php echo htmlspecialchars($lesson['image_path']); ?>" alt="Хичээлийн зураг">
            <?php endif; ?>


            <?php if ($lesson['video_path']): ?>
                <video controls>
                    <source src="<?php echo htmlspecialchars($lesson['video_path']); ?>" type="video/mp4">
                    Таны төхөөрөмж видео тоглуулахыг дэмжихгүй байна.
                </video>
            <?php endif; ?>

            <?php if ($lesson['video_link']): ?>
    <iframe width="100%" height="315" 
            src="<?php echo htmlspecialchars($lesson['video_link']); ?>" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
    </iframe>
<?php endif; ?>

</div>

        <div class="btn-container">
            <a href="student.php">Буцах</a>
            <?php if ($lesson['file_path']): ?>
                <a href="<?php echo htmlspecialchars($lesson['file_path']); ?>" download class="download-btn">Файл татах</a>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>

