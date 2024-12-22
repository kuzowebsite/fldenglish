<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Хэрэглэгчийн мэдээллийг авах
$stmt = $pdo->prepare("SELECT username AS name, email, profile_image FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Хичээлүүдийг сангаас авах
$stmt = $pdo->query("SELECT id, name, description, file_path, video_path, image_path FROM lessons");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
$profile_image = !empty($student['profile_image']) ? $student['profile_image'] : 'default_profile.jpg';
?>

<!DOCTYPE html>
<html lang="mn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Хичээлүүд</title>
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
        h2 {
            color: #333;
            padding: 20px;
        }

        .backgroundimage {
    background-image: url("backimage.webp"); /* Зургийн зөв замыг оруулна */
    background-size: cover; /* Зургийг дэлгэцэнд тааруулах */
    background-position: center; /* Төвд байрлуулах */
    background-repeat: no-repeat; /* Зургаа дахин давтахгүй */
    height: 520px; /* Өндрийг тохируулна */
    width: 1312px;
    display: flex; /* Хүүхэд элементүүдийг төвд оруулахын тулд ашиглана */
    align-items: center;
    justify-content: center;
    color: white; /* Хэрэв текст харагдахыг хүсвэл тохирно */
    border-radius: 15px;
}

        .header {
            display: flex;
            align-items: center;
            gap: 15px; /* Зураг болон текстийн хоорондын зай */
            margin-bottom: 20px;
        }

        .header img {
            width: 80px; /* Логоны хэмжээ */
            height: auto;
        }

        .header p {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .desktop-onlys {
    display: flex;
    gap: 10px; /* Товчлууруудын хоорондох зай */
    align-items: center;
    margin-left: auto; /* Зүүн булангаас баруун тийш шилжүүлэх */
}

        .admin-button {
            margin-top: 21px;
    margin-bottom: 10px;
    display: flex;
    justify-content: center;
    gap: 30px;
}

.admin-button a {
    display: inline-block;
    padding: 8px 25px;
    background-color:rgb(255, 255, 255);
    color: black;
    text-decoration: none;
    border-radius: 5px;
    font-size: 12px;
    box-sizing: border-box;
    transition: outline 0.3s ease;
}

.admin-button a:hover {
    color:black;
    outline: 2px solid black;
}

.admin {
    display: none;
}


/* For small screens (Mobile) */
@media (max-width: 932px) {
    .admin {
            display: flex; /* Товчнуудыг уян хатан байдлаар байрлуулах */
            justify-content: center; /* Төвд байрлуулах */
            gap: 10px; /* Товчнуудын хоорондын зай */
            bottom: 700px; /* Доод талд байрлуулах */
            left: 0;
            right: 0;
            background-color: white; /* Цагаан дэвсгэр өнгө */
            padding: 10px 0; /* Дотор зайн тохиргоо */
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Сүүдэр нэмэх */
        }
        .admin-button {
            display: none;
        }

        .admin a {
            flex-grow: 1; /* Товчнуудыг адил хэмжээтэй болгох */
            text-align: center; /* Текстийг төвд байрлуулах */
            padding: 10px 0; /* Товчны дотор зай */
            font-size: 12px; /* Фонтын хэмжээ */
            color: black; /* Текстийн өнгө */
            text-decoration: none; /* Доогуур зураасыг арилгах */
            border-radius: 15px; /* Булангуудыг дугуйруулах */
            background-color:rgba(211, 208, 208, 0.58); /* Цайвар саарал дэвсгэр өнгө */
            border: 1px solid #ddd; /* Товчны хүрээ */
        }
        .admin-button a {
            display: none;
        }

        .admin a:hover {
            color:black;
    outline: 2px solid black;
        }
        .admin-button a:hover {
            display: none;
        }


        .backgroundimage {
    background-image: url("backimage.webp"); /* Зургийн зөв замыг оруулна */
    background-size: cover; /* Зургийг дэлгэцэнд тааруулах */
    background-position: center; /* Төвд байрлуулах */
    background-repeat: no-repeat; /* Зургаа дахин давтахгүй */
    height: 620px; /* Өндрийг тохируулна */
    width: auto;
    display: flex; /* төвд оруулахын тулд ашиглана */
    align-items: center;
    justify-content: center;
    color: white; /* Хэрэв текст харагдахыг хүсвэл тохирно */
    border-radius: 15px;
}

        .garh {
    position: fixed; /* Тогтмол байрлалд оруулах */
    bottom: 12px; /* Доод талаас 20 пикселийн зай */
    right: 20px; /* Баруун талаас 20 пикселийн зай */
    width: 38px; /* Зургийн өргөн */
    height: 38px; /* Зургийн өндөр */
    background-image: url("garh.png"); /* Зургийг холбоно */
    background-size: cover; /* Зургийг бүрэн тохируулах */
    background-repeat: no-repeat; /* Зургаа дахин давтахгүй */
    border-radius: 50%; /* Дугуй хэлбэртэй болгох */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Сүүдэр нэмэх */
    transition: transform 0.2s ease, box-shadow 0.2s ease; /* Хулганаар хүрэхэд шилжилт хийх */
}

.garh:hover {
    transform: scale(1.1); /* Хулганаар хүрэхэд томруулах */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); /* Хулганаар хүрэхэд сүүдэрийг тодруулах */
}

/*fergthgrfefrgth */

.header {
            display: flex;
            align-items: center;
            gap: 15px; /* Зураг болон текстийн хоорондын зай */
            margin-bottom: 20px;
        }

        .header img {
            width: 50px; /* Логоны хэмжээ */
            height: auto;
        }

        .header p {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

         /*adwwdawdawdawdadwawd*/

.profile img {
    width: 20px; /* Зурагны хэмжээ */
    height: 20px;
    border-radius: 20%; /* Дугуй хэлбэртэй болгох */
    object-fit: cover;
    right: 15px;
    position: absolute;
    top: 20px;
}

div p:first-child {
    font-size: 12px; /* Нэрийн текст */
    font-weight: bold;
    position: absolute;
    right: 10px; 
    top: 55px;
}

div p:last-child {
    display: none;
}

}

/* Hide the menu toggle button on large screens */
@media (min-width: 769px) {
    .menu-toggle {
        display: none;
    }

}

        .lessons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

.lesson {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    text-align: center;
    border: 2px solid #666;
}

.lesson:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    border: 2px solid black;
}

.lesson img {
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.lesson-info {
    padding: 15px;
}

.lesson-info h3 {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    margin: 0 0 10px;
}

.lesson-info p {
    font-size: 14px;
    color: #666;
    margin: 0 0 15px;
}

.view-btn {
    padding: 16px 8px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 10px;
    font-size: 14px;
}

.view-btn:hover {
    background-color: #0056b3;
}

.profile {
    display: flex;
    align-items: center;
    gap: 10px; /* Зураг болон текстийн хооронд зай */
    text-decoration: none;
    cursor: pointer;
    margin-top: 1px;
    margin-left: 110px;
}

.profile:hover {
    opacity: 0.8; /* Хулганаараа хүрхэд бүдгэрүүлэх */
}

.profile img {
    width: 30px; /* Зурагны хэмжээ */
    height: 30px;
    border-radius: 50%; /* Дугуй хэлбэртэй болгох */
    object-fit: cover;
}

.profile p {
    margin: 0;
    font-size: 10px;
    color: #333;
}

.garh {
    position: fixed; /* Тогтмол байрлалд оруулах */
    bottom: 12px; /* Доод талаас 20 пикселийн зай */
    right: 20px; /* Баруун талаас 20 пикселийн зай */
    width: 38px; /* Зургийн өргөн */
    height: 38px; /* Зургийн өндөр */
    background-image: url("garh.png"); /* Зургийг холбоно */
    background-size: cover; /* Зургийг бүрэн тохируулах */
    background-repeat: no-repeat; /* Зургаа дахин давтахгүй */
    border-radius: 50%; /* Дугуй хэлбэртэй болгох */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); /* Сүүдэр нэмэх */
    transition: transform 0.2s ease, box-shadow 0.2s ease; /* Хулганаар хүрэхэд шилжилт хийх */
}

.garh:hover {
    transform: scale(1.1); /* Хулганаар хүрэхэд томруулах */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); /* Хулганаар хүрэхэд сүүдэрийг тодруулах */
}
    </style>
</head>
<body>
    <div class="container">
     <!-- Header Section -->
     <div class="header">
    <img src="logo.jpeg" alt="FLD Logo">
    <p>FLD STUDY CENTER</p>
    <div class="admin-button desktop-onlys">
        <a href="student.php">НҮҮР</a>
        <a href="studentdlgvrharh.php">ДААЛГАВАР</a>
        <a href="dalgvarilgeeh.php">ДААЛГАВАР ИЛГЭЭХ</a>
    </div>
    <a href="profileedit.php" class="profile">
    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
    <div>
        <p><strong><?php echo htmlspecialchars($student['name']); ?></strong></p>
        <p><?php echo htmlspecialchars($student['email']); ?></p>
    </div>
</a>
</div>
<div class="admin">
        <a href="student.php">НҮҮР</a>
        <a href="studentdlgvrharh.php">ДААЛГАВАР</a> 
        <a href="dalgvarilgeeh.php">ДААЛГАВАР ИЛГЭЭХ</a>
    </div>
        <!-- Mobile button -->
        <div class="backgroundimage">
        <h2>Тавтай морил, <?php echo htmlspecialchars($student['name']); ?>!</h2>
        </div>

        <h2>Хичээлүүдийн жагсаалт</h2>
        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson">
                <img src="<?php echo htmlspecialchars($lesson['image_path'] ?: 'logo.jpeg'); ?>" alt="Хичээлийн зураг">
                    <div class="lesson-info">
                        <h3><?php echo htmlspecialchars($lesson['name']); ?></h3>
                        <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                    </div>
                    <a href="lesson_detail.php?id=<?php echo $lesson['id']; ?>" class="view-btn">Дэлгэрэнгүй</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <a class="garh" href="logout.php"></a>
</body>
</html>