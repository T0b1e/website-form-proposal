<?php
// File: reset-password.php

// Include your database connection (adjust the file path as needed)
require_once 'db.php'; // db.php should initialize a PDO instance ($pdo)

if (!isset($_GET['token'])) {
    die("Invalid request. No token provided.");
}

$token = $_GET['token'];

// Retrieve the reset request record
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token");
$stmt->execute(['token' => $token]);
$resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetRequest) {
    die("Invalid or expired token.");
}

if (strtotime($resetRequest['expires']) < time()) {
    // Optionally remove expired token
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
    $stmt->execute(['token' => $token]);
    die("This reset link has expired.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirmPassword)) {
        $error = "กรุณากรอกรหัสผ่านทั้งสองช่อง";
    } elseif ($password !== $confirmPassword) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {
        // Hash the new password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password using the user_id from the reset request
        $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
        $updateSuccess = $updateStmt->execute([
            'password' => $hashedPassword,
            'user_id'  => $resetRequest['user_id']
        ]);

        if ($updateSuccess) {
            // Delete the used reset token
            $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
            $deleteStmt->execute(['token' => $token]);

            echo "รีเซ็ตรหัสผ่านสำเร็จแล้ว กรุณา <a href='login.php'>เข้าสู่ระบบ</a>";
            exit;
        } else {
            $error = "เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>รีเซ็ตรหัสผ่าน</title>
  <link rel="stylesheet" href="./css/auth.css" />
  <link href="https://fonts.googleapis.com/css2?family=TH+Sarabun+New:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-container">
    <h2>รีเซ็ตรหัสผ่าน</h2>
    <?php if ($error): ?>
      <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="password" name="password" placeholder="รหัสผ่านใหม่" required>
      <input type="password" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่" required>
      <button type="submit">รีเซ็ตรหัสผ่าน</button>
    </form>
  </div>
</body>
</html>
