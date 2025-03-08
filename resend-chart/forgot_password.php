<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
  <link rel="stylesheet" href="./css/auth.css" />
  <link href="https://fonts.googleapis.com/css2?family=TH+Sarabun+New:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="auth-container">
    <h2>Forgot Password</h2>
    <form id="forgot-password-form">
      <input type="email" name="email" id="email" placeholder="Enter your registered email" required>
      <input type="password" name="password" id="password" placeholder="New Password" required>
      <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
      <button type="submit">Submit</button>
    </form>
    <p id="message" style="display: none;"></p>
    <p><a href="login.php">Login here</a></p>
  </div>

  <script>
    document.getElementById('forgot-password-form').addEventListener('submit', function(e){
      e.preventDefault();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const message = document.getElementById('message');
      message.style.display = 'none';
      
      if(password !== confirmPassword) {
        message.innerText = "Passwords do not match.";
        message.style.display = 'block';
        return;
      }
      
      fetch('../src/controllers/AuthController.php?action=forgot_password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, password: password, confirm_password: confirmPassword })
      })
      .then(response => response.json())
      .then(data => {
        message.innerText = data.message;
        message.style.display = 'block';
        if (data.success) {
          // Optionally clear the form or redirect
          document.getElementById('forgot-password-form').reset();
        }
      })
      .catch(error => {
        console.error('Error:', error);
        message.innerText = 'Error occurred. Try again later.';
        message.style.display = 'block';
      });
    });
  </script>
</body>
</html>
