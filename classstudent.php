<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
require_once 'db_connect.php';

$message = '';
$messageType = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['success' => false, 'message' => ''];

    if ($_POST['action'] === 'edit_class') {
        $class_id = $_POST['class_id'];
        $new_class_name = $_POST['new_class_name'];
        $sql = "UPDATE classes SET name = :new_class_name WHERE id = :class_id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute(['new_class_name' => $new_class_name, 'class_id' => $class_id]);
        
        if ($result) {
            $response = ['success' => true, 'message' => 'Анги амжилттай шинэчлэгдлээ'];
        } else {
            $response = ['success' => false, 'message' => 'Алдаа: Анги шинэчлэхэд алдаа гарлаа'];
        }
    } elseif ($_POST['action'] === 'delete_class') {
        $class_id = $_POST['class_id'];
        
        try {
            $pdo->beginTransaction();

            // First, delete associated students
            $sql = "DELETE FROM students WHERE class_id = :class_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['class_id' => $class_id]);
            
            // Then, delete the class
            $sql = "DELETE FROM classes WHERE id = :class_id";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute(['class_id' => $class_id]);
            
            if ($result) {
                $pdo->commit();
                $response = ['success' => true, 'message' => 'Анги амжилттай устгагдлаа'];
            } else {
                throw new Exception('Анги устгахад алдаа гарлаа');
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $response = ['success' => false, 'message' => 'Алдаа: ' . $e->getMessage()];
        }
    }

    echo json_encode($response);
    exit;
}

// Анги нэмэх
if (isset($_POST['add_class'])) {
    $class_name = $_POST['class_name'];
    $sql = "INSERT INTO classes (name) VALUES (:class_name)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['class_name' => $class_name]);
    if ($stmt->rowCount() > 0) {
        $message = "Анги амжилттай нэмэгдлээ";
        $messageType = "success";
    } else {
        $message = "Алдаа: Анги нэмэхэд алдаа гарлаа";
        $messageType = "error";
    }
}

// Оюутан нэмэх
if (isset($_POST['add_student'])) {
    $student_name = $_POST['student_name'];
    $class_id = $_POST['class_id'];
    $sql = "INSERT INTO students (name, class_id) VALUES (:student_name, :class_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['student_name' => $student_name, 'class_id' => $class_id]);
    if ($stmt->rowCount() > 0) {
        $message = "Оюутан амжилттай нэмэгдлээ";
        $messageType = "success";
    } else {
        $message = "Алдаа: Оюутан нэмэхэд алдаа гарлаа";
        $messageType = "error";
    }
}

// Ангиудыг авах
$sql = "SELECT * FROM classes";
$stmt = $pdo->query($sql);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анги болон Оюутан Удирдах</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f9fafb;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .form-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-container h3 {
            margin-bottom: 15px;
            color: #333;
        }
        input[type="text"], select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions button {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .actions .edit {
            background-color: #ffc107;
            color: white;
        }
        .actions .delete {
            background-color: #dc3545;
            color: white;
        }
        .actions button:hover {
            opacity: 0.8;
        }
        .message {
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
        .back-link {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 15px;
        font-size: 16px;
        font-weight: bold;
        color: #ffffff;
        background-color: #45a049;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .back-link:hover {
        background-color: #45a049;
        transform: scale(1.05);
    }
    .back-link:active {
        background-color: #003d82;
        transform: scale(0.98);
    }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 20px;">Анги болон Оюутан Удирдах</h2>

        <!-- Зурвас харуулах хэсэг -->
        <div id="message" class="message" style="display: none;"></div>

        <!-- Анги нэмэх форм -->
        <div class="form-container">
            <h3>Анги Нэмэх</h3>
            <form method="post">
                <input type="text" name="class_name" placeholder="Ангийн нэр" required>
                <input type="submit" name="add_class" value="Анги нэмэх">
            </form>
        </div>

        <!-- Оюутан нэмэх форм -->
        <div class="form-container">
            <h3>Оюутан Нэмэх</h3>
            <form method="post">
                <input type="text" name="student_name" placeholder="Оюутны нэр" required>
                <select name="class_id" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="add_student" value="Оюутан нэмэх">
            </form>
        </div>

        <!-- Ангиудын хүснэгт -->
        <div class="form-container">
            <h3>Ангиуд</h3>
            <table>
                <thead>
                    <tr>
                        <th>Ангийн нэр</th>
                        <th>Үйлдэл</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($class['name']); ?></td>
                        <td class="actions">
                            <button class="edit" onclick="editClass(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['name']); ?>')">Засах</button>
                            <button class="delete" onclick="deleteClass(<?php echo $class['id']; ?>)">Устгах</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Буцах холбоос -->
        <a href="admin.php" class="back-link">Буцах</a>
    </div>

    <script>
        function sendRequest(action, data) {
            const formData = new FormData();
            formData.append('action', action);
            for (const key in data) {
                formData.append(key, data[key]);
            }

            return fetch('classstudent.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Алдаа гарлаа. Дахин оролдоно уу.', 'error');
            });
        }

        function showMessage(message, type) {
            const messageElement = document.getElementById('message');
            messageElement.textContent = message;
            messageElement.className = `message ${type}`;
            messageElement.style.display = 'block';
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 3000);
        }

        function editClass(classId, className) {
            const newName = prompt("Ангийн шинэ нэр:", className);
            if (newName !== null && newName !== "") {
                sendRequest('edit_class', { class_id: classId, new_class_name: newName });
            }
        }

        function deleteClass(classId) {
            if (confirm("Та энэ ангийг устгахдаа итгэлтэй байна уу?")) {
                sendRequest('delete_class', { class_id: classId });
            }
        }
    </script>
</body>
</html>
