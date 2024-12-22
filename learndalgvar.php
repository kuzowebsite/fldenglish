<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Handle deletion
if (isset($_POST['delete_assignment'])) {
    try {
        $assignment_id = $_POST['assignment_id'];
        
        // First get the assignment details to delete associated files
        $stmt = $pdo->prepare("SELECT file_path, video_path FROM assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);
        $assignment = $stmt->fetch();
        
        // Delete physical files if they exist
        if ($assignment['file_path'] && file_exists($assignment['file_path'])) {
            unlink($assignment['file_path']);
        }
        if ($assignment['video_path'] && file_exists($assignment['video_path'])) {
            unlink($assignment['video_path']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->execute([$assignment_id]);
        
        $success_message = "Даалгавар амжилттай устгагдлаа!";
    } catch (Exception $e) {
        $error_message = "Алдаа гарлаа: " . $e->getMessage();
    }
}

// Handle edit
if (isset($_POST['edit_assignment'])) {
    try {
        $assignment_id = $_POST['assignment_id'];
        $subject_name = $_POST['subject_name'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = $_POST['description'];
        
        // Initialize file and video paths
        $file_path = $_POST['current_file_path'];
        $video_path = $_POST['current_video_path'];
        
        // Handle new file upload
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            // Delete old file if exists
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            
            $file_tmp = $_FILES['assignment_file']['tmp_name'];
            $file_name = $_FILES['assignment_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_files = array('pdf', 'doc', 'docx', 'txt');
            
            if (in_array($file_ext, $allowed_files)) {
                $upload_dir = 'uploads/assignments/files/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                move_uploaded_file($file_tmp, $file_path);
            }
        }
        
        // Handle new video upload
        if (isset($_FILES['assignment_video']) && $_FILES['assignment_video']['error'] === UPLOAD_ERR_OK) {
            // Delete old video if exists
            if ($video_path && file_exists($video_path)) {
                unlink($video_path);
            }
            
            $video_tmp = $_FILES['assignment_video']['tmp_name'];
            $video_name = $_FILES['assignment_video']['name'];
            $video_ext = strtolower(pathinfo($video_name, PATHINFO_EXTENSION));
            $allowed_videos = array('mp4', 'webm', 'mov');
            
            if (in_array($video_ext, $allowed_videos)) {
                $upload_dir = 'uploads/assignments/videos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_video_name = uniqid() . '.' . $video_ext;
                $video_path = $upload_dir . $new_video_name;
                
                move_uploaded_file($video_tmp, $video_path);
            }
        }
        
        // Update database
        $sql = "UPDATE assignments SET 
                subject_name = :subject_name,
                start_date = :start_date,
                end_date = :end_date,
                description = :description,
                file_path = :file_path,
                video_path = :video_path
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'subject_name' => $subject_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description,
            'file_path' => $file_path,
            'video_path' => $video_path,
            'id' => $assignment_id
        ]);
        
        $success_message = "Даалгавар амжилттай шинэчлэгдлээ!";
        
    } catch (Exception $e) {
        $error_message = "Алдаа гарлаа: " . $e->getMessage();
    }
}

// Handle form submission for new assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
    try {
        $class_id = $_POST['class_id'];
        
        // Fetch the class name based on the selected class_id
        $stmt = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch();
        if (!$class) {
            throw new Exception("Invalid class selected");
        }
        $class_name = $class['name'];
        
        $subject_name = $_POST['subject_name'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $description = $_POST['description'];
        
        // Initialize file and video paths as null
        $file_path = null;
        $video_path = null;
        
        // Handle file upload
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['assignment_file']['tmp_name'];
            $file_name = $_FILES['assignment_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_files = array('pdf', 'doc', 'docx', 'txt');
            
            if (in_array($file_ext, $allowed_files)) {
                $upload_dir = 'uploads/assignments/files/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                move_uploaded_file($file_tmp, $file_path);
            }
        }
        
        // Handle video upload
        if (isset($_FILES['assignment_video']) && $_FILES['assignment_video']['error'] === UPLOAD_ERR_OK) {
            $video_tmp = $_FILES['assignment_video']['tmp_name'];
            $video_name = $_FILES['assignment_video']['name'];
            $video_ext = strtolower(pathinfo($video_name, PATHINFO_EXTENSION));
            $allowed_videos = array('mp4', 'webm', 'mov');
            
            if (in_array($video_ext, $allowed_videos)) {
                $upload_dir = 'uploads/assignments/videos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_video_name = uniqid() . '.' . $video_ext;
                $video_path = $upload_dir . $new_video_name;
                
                move_uploaded_file($video_tmp, $video_path);
            }
        }
        
        // Insert into database
        $sql = "INSERT INTO assignments (classname, subject_name, start_date, end_date, description, file_path, video_path) 
                VALUES (:classname, :subject_name, :start_date, :end_date, :description, :file_path, :video_path)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'classname' => $class_name,
            'subject_name' => $subject_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'description' => $description,
            'file_path' => $file_path,
            'video_path' => $video_path
        ]);
        
        $success_message = "Даалгавар амжилттай нэмэгдлээ!";
        
    } catch (Exception $e) {
        $error_message = "Алдаа гарлаа: " . $e->getMessage();
    }
}

// Fetch all classes
$sql = "SELECT * FROM classes";
$stmt = $pdo->query($sql);
$classes = $stmt->fetchAll();

// Fetch existing assignments
$assignments = [];
if (isset($_GET['class_id'])) {
    // First, get the class name
    $stmt = $pdo->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt->execute([$_GET['class_id']]);
    $class = $stmt->fetch();
    
    if ($class) {
        $sql = "SELECT * FROM assignments 
                WHERE classname = :classname 
                ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['classname' => $class['name']]);
        $assignments = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Даалгавар нэмэх</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #4CAF30;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f7f7f7;
            font-size: 14px;
            color: #333;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input:focus, textarea:focus, select:focus {
            border-color: #4CAF30;
        }

        textarea {
            height: 120px;
            resize: none;
        }

        button {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #4CAF30;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #4CAF50;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .assignments-list {
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #4CAF30;
            color: #fff;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #4CAF30;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            background-color: #4CAF30;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-link:hover {
            background-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .back-link:active {
            background-color: #4CAF50;
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        select {
            padding: 10px;
            background-color: white;
            color: black;
            border: 1px solid #FF9800;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            appearance: none;
            cursor: pointer;
        }

        select:focus {
            border-color: #FFB74D;
            background-color:rgb(255, 255, 255);
        }

        option {
            background-color: #FF9800;
            color: #fff;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .edit-btn {
            background-color: #ffc107;
            color: black;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .edit-btn:hover {
            background-color: #ffb300;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            width: 90%;
            max-width: 600px;
            padding: 20px;
            border-radius: 8px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .modal-buttons button {
            width: 45%;
            padding: 8px;
        }
        
        .cancel-btn {
            background-color: #6c757d;
        }
        
        .confirm-btn {
            background-color: #28a745;
        }
        
        .current-files {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        /* Previous styles remain the same */
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Даалгавар нэмэх</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_assignment" value="1">
            <div class="form-group">
                <label for="class_id">Анги:</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Анги сонгох</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subject_name">Хичээлийн нэр:</label>
                <input type="text" name="subject_name" id="subject_name" required>
            </div>

            <div class="form-group">
                <label for="start_date">Эхлэх огноо:</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>

            <div class="form-group">
                <label for="end_date">Дуусах огноо:</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>

            <div class="form-group">
                <label for="description">Тайлбар:</label>
                <textarea name="description" id="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="assignment_file">Файл (заавал биш):</label>
                <input type="file" name="assignment_file" id="assignment_file">
            </div>

            <div class="form-group">
                <label for="assignment_video">Видео (заавал биш):</label>
                <input type="file" name="assignment_video" id="assignment_video">
            </div>

            <button type="submit">Даалгавар нэмэх</button>
        </form>

        <div class="assignments-list">
            <h3>Оруулсан даалгаврууд</h3>
            <form method="get">
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">Анги сонгох</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if (!empty($assignments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Анги</th>
                            <th>Хичээл</th>
                            <th>Эхлэх огноо</th>
                            <th>Дуусах огноо</th>
                            <th>Тайлбар</th>
                            <th>Файл</th>
                            <th>Видео</th>
                            <th>Үйлдэл</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= htmlspecialchars($assignment['classname']) ?></td>
                                <td><?= htmlspecialchars($assignment['subject_name']) ?></td>
                                <td><?= $assignment['start_date'] ?></td>
                                <td><?= $assignment['end_date'] ?></td>
                                <td><?= htmlspecialchars($assignment['description']) ?></td>
                                <td>
                                    <?php if ($assignment['file_path']): ?>
                                        <a href="<?= htmlspecialchars($assignment['file_path']) ?>" target="_blank">Файл татах</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($assignment['video_path']): ?>
                                        <a href="<?= htmlspecialchars($assignment['video_path']) ?>" target="_blank">Видео үзэх</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            class="edit-btn" 
                                            onclick="showEditForm(<?= htmlspecialchars(json_encode($assignment)) ?>)"
                                        >
                                            Засах
                                        </button>
                                        <button 
                                            class="delete-btn" 
                                            onclick="showDeleteConfirmation(<?= $assignment['id'] ?>)"
                                        >
                                            Устгах
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif (isset($_GET['class_id'])): ?>
                <p>Энэ ангид даалгавар байхгүй байна.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Устгах уу?</h3>
            <p>Та энэ даалгаврыг устгахдаа итгэлтэй байна уу?</p>
            <div class="modal-buttons">
                <button onclick="hideDeleteConfirmation()" class="cancel-btn">Үгүй</button>
                <form method="post" style="display: inline; width: 45%;">
                    <input type="hidden" name="assignment_id" id="deleteAssignmentId">
                    <button type="submit" name="delete_assignment" class="confirm-btn">Тийм</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Form Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Даалгавар засах</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" id="editAssignmentId">
                <input type="hidden" name="current_file_path" id="currentFilePath">
                <input type="hidden" name="current_video_path" id="currentVideoPath">
                
                <div class="form-group">
                    <label for="edit_subject_name">Хичээлийн нэр:</label>
                    <input type="text" name="subject_name" id="edit_subject_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_start_date">Эхлэх огноо:</label>
                    <input type="date" name="start_date" id="edit_start_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_end_date">Дуусах огноо:</label>
                    <input type="date" name="end_date" id="edit_end_date" required>
                </div>

                <div class="form-group">
                    <label for="edit_description">Тайлбар:</label>
                    <textarea name="description" id="edit_description" required></textarea>
                </div>

                <div class="current-files">
                    <div id="currentFileInfo"></div>
                    <div id="currentVideoInfo"></div>
                </div>

                <div class="form-group">
                    <label for="edit_assignment_file">Шинэ файл (заавал биш):</label>
                    <input type="file" name="assignment_file" id="edit_assignment_file">
                </div>

                <div class="form-group">
                    <label for="edit_assignment_video">Шинэ видео (заавал биш):</label>
                    <input type="file" name="assignment_video" id="edit_assignment_video">
                </div>

                <div class="modal-buttons">
                    <button type="button" onclick="hideEditForm()" class="cancel-btn">Цуцлах</button>
                    <button type="submit" name="edit_assignment" class="confirm-btn">Хадгалах</button>
                </div>
            </form>
        </div>
    </div>

    <a href="admin.php" class="back-link">← Буцах</a>

    <script>
    function showDeleteConfirmation(assignmentId) {
        document.getElementById('deleteModal').style.display = 'block';
        document.getElementById('deleteAssignmentId').value = assignmentId;
    }

    function hideDeleteConfirmation() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    function showEditForm(assignment) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('editAssignmentId').value = assignment.id;
        document.getElementById('edit_subject_name').value = assignment.subject_name;
        document.getElementById('edit_start_date').value = assignment.start_date;
        document.getElementById('edit_end_date').value = assignment.end_date;
        document.getElementById('edit_description').value = assignment.description;
        document.getElementById('currentFilePath').value = assignment.file_path || '';
        document.getElementById('currentVideoPath').value = assignment.video_path || '';
        
        // Show current file and video information
        let fileInfo = document.getElementById('currentFileInfo');
        let videoInfo = document.getElementById('currentVideoInfo');
        
        fileInfo.innerHTML = assignment.file_path ? 
            'Одоогийн файл: <a href="' + assignment.file_path + '" target="_blank">Үзэх</a>' : 
            'Файл байхгүй байна';
        
        videoInfo.innerHTML = assignment.video_path ? 
            'Одоогийн видео: <a href="' + assignment.video_path + '" target="_blank">Үзэх</a>' : 
            'Видео байхгүй байна';
    }

    function hideEditForm() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }
    </script>
</body>
</html>

