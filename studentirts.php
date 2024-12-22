<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Fetch all classes
$sql = "SELECT * FROM classes";
$stmt = $pdo->query($sql);
$classes = $stmt->fetchAll();

// Fetch attendance statuses
$sql = "SELECT * FROM attendance_status";
$stmt = $pdo->query($sql);
$statuses = $stmt->fetchAll();

// Handle attendance submission
if (isset($_POST['submit_attendance'])) {
    try {
        $pdo->beginTransaction();
        
        $student_id = $_POST['student_id'];
        $status_id = $_POST['status_id'];
        $date = $_POST['date'];
        
        // Check if attendance record exists
        $check_sql = "SELECT id FROM attendance WHERE student_id = :student_id AND date = :date";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute(['student_id' => $student_id, 'date' => $date]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing record
            $sql = "UPDATE attendance SET status_id = :status_id 
                    WHERE student_id = :student_id AND date = :date";
        } else {
            // Insert new record
            $sql = "INSERT INTO attendance (student_id, status_id, date) 
                    VALUES (:student_id, :status_id, :date)";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'student_id' => $student_id,
            'status_id' => $status_id,
            'date' => $date
        ]);
        
        $pdo->commit();
        
        // Redirect back with success message
        header("Location: studentirts.php?class_id={$_POST['class_id']}&date={$date}&success=1");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Алдаа гарлаа: " . $e->getMessage();
    }
}

// Fetch students and their attendance if a class is selected
$students = [];
if (isset($_GET['class_id']) && isset($_GET['date'])) {
    $class_id = $_GET['class_id'];
    $date = $_GET['date'];
    
    // Modified query to ensure one record per student
    $sql = "SELECT DISTINCT s.id, s.name, 
            COALESCE(a.status_id, 
            (SELECT MIN(id) FROM attendance_status)) as status_id, 
            COALESCE(ast.name, 'Тэмдэглээгүй') as status_name
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date = :date
            LEFT JOIN attendance_status ast ON a.status_id = ast.id
            WHERE s.class_id = :class_id
            ORDER BY s.id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['class_id' => $class_id, 'date' => $date]);
    $students = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оюутны Ирц</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4a4a4a;
        }
        form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        select, input[type="date"], input[type="submit"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            font-weight: bold;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f1f9ff;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        select {
            padding: 8px;
            font-size: 14px;
        }
        .actions input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .actions input[type="submit"]:hover {
            background-color: #218838;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
        }
        a.back-link:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Оюутны Ирц</h2>

        <form method="get">
            <select name="class_id" required>
                <option value="">Анги сонгох</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date" required value="<?= isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') ?>">
            <input type="submit" value="Харах">
        </form>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">Ирц амжилттай хадгалагдлаа!</div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Нэр</th>
                        <th>Ирц</th>
                        <th>Үйлдэл</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= $student['id'] ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['status_name']) ?></td>
                            <td class="actions">
                                <form method="post">
                                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                    <input type="hidden" name="date" value="<?= $_GET['date'] ?>">
                                    <input type="hidden" name="class_id" value="<?= $_GET['class_id'] ?>">
                                    <select name="status_id" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= $status['id'] ?>" <?= ($student['status_id'] == $status['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($status['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="submit" name="submit_attendance" value="Хадгалах">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['class_id'])): ?>
            <p>Энэ ангид оюутан байхгүй байна.</p>
        <?php endif; ?>

        <a href="admin.php" class="back-link">← Буцах</a>
    </div>
</body>
</html>
