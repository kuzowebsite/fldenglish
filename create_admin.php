<?php
require_once 'db_connect.php';

$username = 'admin';
$password = 'admin123'; // Энэ нууц үгийг өөрчилнө үү
$email = 'admin@example.com';
$user_type = 'admin';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, ?)");

try {
    $stmt->execute([$username, $hashed_password, $email, $user_type]);
    echo "Админ хэрэглэгч амжилттай үүслээ.";
} catch (PDOException $e) {
    echo "Алдаа гарлаа: " . $e->getMessage();
}
?>