<?php
require_once 'db_connect.php';

function generateAdminKey($length = 16) {
    return bin2hex(random_bytes($length));
}

function saveAdminKey($key, $expiresInDays = 30) {
    global $pdo;
    $keyHash = password_hash($key, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime("+$expiresInDays days"));
    
    $stmt = $pdo->prepare("INSERT INTO admin_keys (key_hash, expires_at) VALUES (?, ?)");
    return $stmt->execute([$keyHash, $expiresAt]);
}

function verifyAdminKey($key) {
    global $pdo;
    $stmt = $pdo->query("SELECT key_hash FROM admin_keys WHERE expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && password_verify($key, $result['key_hash'])) {
        return true;
    }
    return false;
}

function getCurrentAdminKey() {
    global $pdo;
    $stmt = $pdo->query("SELECT id FROM admin_keys WHERE expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        $newKey = generateAdminKey();
        if (saveAdminKey($newKey)) {
            return $newKey;
        }
    }
    return false;
}
?>