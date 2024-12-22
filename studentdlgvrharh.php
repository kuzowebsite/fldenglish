<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Get student information including their class
$stmt = $pdo->prepare("SELECT u.username AS name, u.email, u.profile_image, u.class FROM users u WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch assignments for the student's class only
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE classname = ? ORDER BY created_at DESC");
$stmt->execute([$student['class']]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$profile_image = !empty($student['profile_image']) ? $student['profile_image'] : 'default_profile.jpg';
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Даалгаврууд</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9; /* Зөөлөн цагаан дэвсгэр */
        margin: 0;
        color: #333;
    }

    h2 {
        color: #4CAF50; /* Ногоон өнгө */
        text-align: center;
        margin: 20px 0;
    }

    .assignments-table {
        width: 90%;
        margin: 20px auto;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .assignments-table th {
        background-color: #FF9800; /* Улбар шар өнгө */
        color: white;
        padding: 15px;
        font-weight: bold;
    }

    .assignments-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .assignments-table tr:nth-child(even) {
        background-color: #f4f4f4; /* Зөөлөн саарал судалтай */
    }

    .assignments-table tr:hover {
        background-color: #FFE0B2; /* Зөөлөн улбар шар */
    }

    .assignments-table a {
        color: #4CAF50; /* Ногоон өнгө */
        text-decoration: none;
        font-weight: bold;
    }

    .assignments-table a:hover {
        text-decoration: underline;
    }

    .garh {
    position: fixed; /* Товчийг дэлгэц дээр тогтмол байршуулах */
    bottom: 20px; /* Дэлгэцийн доод талаас зай */
    left: 20px; /* Дэлгэцийн зүүн талаас зай */
    width: 150px;
    text-align: center;
    padding: 12px 0;
    background-color: #FF9800; /* Улбар шар */
    color: white;
    text-decoration: none;
    font-size: 16px;
    border-radius: 5px;
    transition: background-color 0.3s ease, transform 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.garh:hover {
    background-color: #E68900; /* Харанхуй улбар шар */
    transform: scale(1.05);
}

    /* Гар утасны загвар */
    @media (max-width: 768px) {
        .assignments-table {
            width: 100%;
            font-size: 14px;
        }

        .assignments-table th,
        .assignments-table td {
            padding: 10px;
        }

        .garh {
            font-size: 14px;
        }
    }
</style>

</head>
<body>
        <h2><?php echo htmlspecialchars($student['class']); ?> ангийн даалгаврууд</h2>

        <?php if (!empty($assignments)): ?>
            <table class="assignments-table">
                <thead>
                    <tr>
                        <th>Хичээл</th>
                        <th>Эхлэх огноо</th>
                        <th>Дуусах огноо</th>
                        <th>Тайлбар</th>
                        <th>Файл</th>
                        <th>Видео</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                            <td>
                                <?php if ($assignment['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" target="_blank">Файл татах</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($assignment['video_path']): ?>
                                    <a href="<?php echo htmlspecialchars($assignment['video_path']); ?>" target="_blank">Видео үзэх</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Одоогоор даалгавар байхгүй байна.</p>
        <?php endif; ?>
    </div>

    <a class="garh" href="student.php">Гарах</a>
</body>
</html>

