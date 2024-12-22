<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Fetch all classes
$stmt = $pdo->query("SELECT DISTINCT class_name FROM submissions ORDER BY class_name");
$classes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables
$selected_class = $_GET['class'] ?? '';
$selected_date = $_GET['date'] ?? '';

// Fetch submissions based on filters
$submissions = [];
if ($selected_class && $selected_date) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.profile_image 
        FROM submissions s
        LEFT JOIN users u ON s.student_id = u.id
        WHERE s.class_name = ? AND DATE(s.submission_date) = ?
        ORDER BY s.submission_date DESC
    ");
    $stmt->execute([$selected_class, $selected_date]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Даалгавар шалгах</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #e8f5e9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #66bb6a;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #388e3c;
            text-align: center;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group {
            flex: 1;
            min-width: 240px;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            color: #388e3c;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #66bb6a;
            border-radius: 4px;
            font-size: 16px;
            background: #e8f5e9;
            color: #388e3c;
        }

        select:focus, input[type="date"]:focus {
            outline: none;
            border-color: #ffb74d;
            background: #fff3e0;
            color: #f57c00;
        }

        button {
            background: #388e3c;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-left: 30px;
            margin-top: 26px;
        }

        button:hover {
            background: #2e7d32;
        }

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }

        .submissions-table th,
        .submissions-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .submissions-table th {
            background: #a5d6a7;
            color: #1b5e20;
            font-weight: bold;
        }

        .submissions-table tbody tr:nth-child(even) {
            background: #f1f8e9;
        }

        .submissions-table tbody tr:hover {
            background: #e8f5e9;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #388e3c;
        }

        .file-link {
            color: #f57c00;
            text-decoration: none;
            font-weight: bold;
        }

        .file-link:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #888;
            background: #fff3e0;
            border: 1px solid #ffcc80;
            border-radius: 8px;
            margin-top: 20px;
        }

        .garh {
            display: block;
            text-align: center;
            margin: 20px auto 0;
            text-decoration: none;
            font-weight: bold;
            color: #fff;
            background: #388e3c;
            padding: 10px 20px;
            border-radius: 4px;
            transition: background 0.3s ease;
            max-width: 200px;
        }

        .garh:hover {
            background: #2e7d32;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            select, input[type="date"] {
        width: 100%;
        font-size: 16px;
    }

            h2 {
                font-size: 20px;
            }

            .filters {
                flex-direction: column;
            }

            .filters .filter-group {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

select, input[type="date"], button {
    width: 100%; /* Бүхэлдээ 100% болгоно */
    box-sizing: border-box; /* Padding нэмэгдсэн ч хэмжээнээс хэтрэхгүй */
}

button {
            background: #388e3c;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-left: -2px;
            margin-top: 26px;
        }

        button:hover {
            background: #2e7d32;
        }

            .submissions-table th,
            .submissions-table td {
                padding: 8px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Даалгавар шалгах</h2>

        <form method="get" class="filters">
            <div class="filter-group">
                <label>Анги сонгох:</label>
                <select name="class">
                    <option value="">Сонгох...</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo htmlspecialchars($class); ?>" 
                                <?php echo $selected_class === $class ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Огноо сонгох:</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            </div>

            <div class="filter-group">
                <button type="submit">Хайх</button>
            </div>
        </form>

        <?php if (!empty($submissions)): ?>
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>Оюутан</th>
                        <th>Хичээл</th>
                        <th>Илгээсэн огноо</th>
                        <th>Файл</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td>
                                <div class="student-info">
                                    <img src="<?php echo htmlspecialchars($submission['profile_image'] ?? 'default_profile.jpg'); ?>" 
                                         alt="Profile" class="student-image">
                                    <span><?php echo htmlspecialchars($submission['student_name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($submission['subject_name']); ?></td>
                            <td><?php echo htmlspecialchars($submission['submission_date']); ?></td>
                            <td>
                                <?php if ($submission['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" 
                                       class="file-link" target="_blank">Файл үзэх</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($selected_class && $selected_date): ?>
            <p class="no-results">Сонгосон өдөр, ангид даалгавар илгээгээгүй байна.</p>
        <?php endif; ?>
    </div>
    <a class="garh" href="admin.php">Гарах</a>
</body>
</html>
