<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Get student information
$stmt = $pdo->prepare("SELECT username AS name, class, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $subject_name = $_POST['subject_name'];
        $submission_date = date('Y-m-d'); // Current date
        $file_path = null;
        
        // Handle file upload
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['assignment_file']['tmp_name'];
            $file_name = $_FILES['assignment_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_files = array('pdf', 'doc', 'docx', 'txt');
            
            if (in_array($file_ext, $allowed_files)) {
                $upload_dir = 'uploads/submissions/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                move_uploaded_file($file_tmp, $file_path);
            }
        }
        
        // Insert into database
        $sql = "INSERT INTO submissions (student_id, student_name, class_name, subject_name, submission_date, file_path) 
                VALUES (:student_id, :student_name, :class_name, :subject_name, :submission_date, :file_path)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'student_id' => $_SESSION['user_id'],
            'student_name' => $student['name'],
            'class_name' => $student['class'],
            'subject_name' => $subject_name,
            'submission_date' => $submission_date,
            'file_path' => $file_path
        ]);
        
        $success_message = "Даалгавар амжилттай илгээгдлээ!";
        
    } catch (Exception $e) {
        $error_message = "Алдаа гарлаа: " . $e->getMessage();
    }
}

$profile_image = !empty($student['profile_image']) ? $student['profile_image'] : 'default_profile.jpg';
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Даалгавар илгээх</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[readonly] {
            background-color: #f5f5f5;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        /* Header styles from previous pages */
        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .header p {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .garh {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .garh:hover {
            background-color: #45a049;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p>Даалгавар илгээх</p>
        </div>

        <div class="form-container">
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Оюутны нэр:</label>
                    <input type="text" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Анги:</label>
                    <input type="text" value="<?php echo htmlspecialchars($student['class']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Огноо:</label>
                    <input type="text" value="<?php echo date('Y-m-d'); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Хичээлийн нэр:</label>
                    <input type="text" name="subject_name" required>
                </div>

                <div class="form-group">
                    <label>Файл (заавал биш):</label>
                    <input type="file" name="assignment_file">
                </div>

                <button type="submit">Илгээх</button>
            </form>
        </div>
    </div>
    <a class="garh" href="student.php">Гарах</a>
</body>
</html>

