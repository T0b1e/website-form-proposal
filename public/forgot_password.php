<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ลืมรหัสผ่าน</title>
  <link rel="stylesheet" href="./css/auth.css" />
  <link href="https://fonts.googleapis.com/css2?family=TH+Sarabun+New:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-container">
    <h2>ลืมรหัสผ่าน</h2>
    <form id="forgot-password-form">
      <input type="email" name="email" id="email" placeholder="กรอกอีเมลที่ลงทะเบียน" required>
      <button type="submit">ส่งคำร้อง</button>
    </form>
    <p id="message" style="display: none;"></p>
    <p><a href="login.php">กลับไปที่หน้าเข้าสู่ระบบ</a></p>
  </div>

  <script>
    document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const email = document.getElementById('email').value.trim();
      const message = document.getElementById('message');
      message.style.display = 'none';

      fetch('../src/controllers/AuthController.php?action=forgot_password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email })
      })
      .then(response => response.json())
      .then(data => {
        message.innerText = data.message;
        message.style.display = 'block';
      })
      .catch(error => {
        console.error('Error:', error);
        message.innerText = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
        message.style.display = 'block';
      });
    });
  </script>
</body>
</html>
