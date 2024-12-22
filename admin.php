<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ хуудас</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }

        .logo img {
            max-width: 80px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .admin-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }

        .admin-buttons a {
            text-decoration: none;
        }

        .admin-buttons button {
            background: linear-gradient(135deg,rgb(57, 230, 23),rgb(201, 114, 16));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            width: 100%;
        }

        .admin-buttons button:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, rgb(201, 114, 16), rgb(57, 230, 23));
        }

        .logout-button {
            background: #e74c3c;
        }

        .logout-button:hover {
            background: #c0392b;
        }

        @media (max-width: 768px) {
            h2 {
                font-size: 20px;
            }

            .admin-buttons button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Лого -->
        <div class="logo">
            <img src="logo.jpeg" alt="Лого">
        </div>

        <h2>Тавтай морил, Админ!</h2>

        <!-- Товчлуурууд -->
        <div class="admin-buttons">
            <a href="nemelthicheel.php"><button type="button">Нэмэлт хичээл нэмэх</button></a>
            <a href="nemelthicheelharah.php"><button type="button">Нэмэлт хичээл харах</button></a>
            <a href="learndalgvar.php"><button type="button">Даалгавар нэмэх</button></a>
            <a href="dalgvarshalgah.php"><button type="button">Даалгавар шалгах</button></a>
            <a href="classstudent.php"><button type="button">Анги | Оюутан нэмэх</button></a>
            <a href="studentirts.php"><button type="button">Ирц</button></a>
            <a href="adminregister.php"><button type="button">Админ бүртгэл</button></a>
            <a href="logout.php"><button type="button" class="logout-button">Гарах</button></a>
        </div>
    </div>
</body>
</html>
