<?php
require_once 'db_connect.php';
$error = '';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    $class = isset($_POST['class']) ? $_POST['class'] : null;

    if ($user_type == 'student' && $class === null) {
        $error = "Анги сонгоно уу";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? AND user_type = ?";
        $params = [$username, $user_type];

        if ($user_type == 'student') {
            $sql .= " AND class = ?";
            $params[] = $class;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['class'] = $user['class'];

            error_log("Login successful. User type: " . $user['user_type'] . ", User ID: " . $user['id'] . ", Class: " . $user['class']);

            if ($user['user_type'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: student.php");
            }
            exit();
        } else {
            $error = "Хэрэглэгчийн нэр, нууц үг эсвэл хэрэглэгчийн төрөл буруу байна";
            error_log("Login failed. Username: " . $username . ", User type: " . $user_type . ", Class: " . $class);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Нэвтрэх</title>
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Arial', sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: #333;
        padding: 20px;
    }

    .container {
        background: #fff;
        padding: 30px 40px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    .logo {
    text-align: center;
    margin-bottom: 20px;
}

.logo img {
    max-width: 150px;
    height: auto;
    border-radius: 50%;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}


    h1 {
        margin-bottom: 20px;
        color: #444;
        font-size: 24px;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    label {
        text-align: left;
        margin-bottom: 5px;
        font-weight: bold;
        font-size: 14px;
    }

    input, select {
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        width: 100%;
    }

    input[type="submit"] {
        background: #6a11cb;
        color: #fff;
        border: none;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s;
    }

    input[type="submit"]:hover {
        background: #2575fc;
    }

    p {
        color: #e74c3c;
        margin-bottom: 15px;
        font-size: 14px;
    }

    a {
        color: #2575fc;
        text-decoration: none;
        margin-top: 10px;
        display: inline-block;
    }

    a:hover {
        text-decoration: underline;
    }

    #class-select {
        display: none;
    }

    /* Media query for smaller screens */
    @media (max-width: 768px) {
        .container {
            padding: 20px 20px;
        }

        h1 {
            font-size: 20px;
        }

        label {
            font-size: 13px;
        }

        input, select {
            padding: 8px;
            font-size: 13px;
        }

        input[type="submit"] {
            padding: 10px;
            font-size: 14px;
        }

        a {
            font-size: 13px;
        }
    }
</style>
</head>
<body>
    <div class="container">

    <div class="logo">
    <img src="logo.jpeg" alt="FLD Logo">
</div>
        <h1>Нэвтрэх</h1>
        <?php if ($error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="username">Хэрэглэгчийн нэр:</label>
            <input type="text" id="username" name="username" placeholder="Нэвтрэх нэрээ оруулна уу" required>

            <label for="password">Нууц үг:</label>
            <input type="password" id="password" name="password" placeholder="Нууц үгээ оруулна уу" required>

            <label for="user_type">Хэрэглэгчийн төрөл:</label>
            <select id="user_type" name="user_type" required>
                <option value="admin">Админ</option>
                <option value="student">Сурагч</option>
            </select>

            <div id="class-select">
                <label for="class">Анги:</label>
                <select id="class" name="class">
                    <option value="">Анги сонгох</option>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM classes ORDER BY name");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <input type="submit" value="Нэвтрэх">
        </form>
        <a href="register.php">Бүртгүүлэх</a>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var userTypeSelect = document.getElementById('user_type');
    var classSelect = document.getElementById('class-select');
    var classSelectInput = document.getElementById('class');

    function toggleClassSelect() {
        if (userTypeSelect.value === 'student') {
            classSelect.style.display = 'block';
            classSelectInput.required = true;
        } else {
            classSelect.style.display = 'none';
            classSelectInput.required = false;
            classSelectInput.value = ''; // Reset the class selection
        }
    }

    userTypeSelect.addEventListener('change', toggleClassSelect);
    toggleClassSelect(); // Initial call to set the correct state
});
</script>
</body>
</html>
