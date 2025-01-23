<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=TH+Sarabun+New:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Create Account</h2>
        <form id="signup-form">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="text" name="department" id="department" placeholder="Department" required>
            <input type="tel" name="phone" id="phone" placeholder="Phone Number" required>
            <input type="text" name="address" id="address" placeholder="Address" required>
            <button type="submit">Sign Up</button>
        </form>
        <p id="error-message" style="color: red; display: none;"></p> <!-- Error message section -->
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    <script>
    document.getElementById('signup-form').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting the traditional way

        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const department = document.getElementById('department').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;

        // AJAX request to send signup data
        fetch('../src/controllers/AuthController.php?action=signup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                username: username,
                email: email,
                password: password,
                department: department,
                phone: phone,
                address: address
            })
        })
        .then(response => response.json())
        .then(data => {
            const errorMessage = document.getElementById('error-message');
            if (data.success) {
                // Redirect to login page on success
                window.location.href = 'login.php'; 
            } else {
                // Show error message in red if signup fails
                errorMessage.innerText = data.message || 'An error occurred during signup.';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMessage = document.getElementById('error-message');
            errorMessage.innerText = 'An error occurred. Please try again later.';
            errorMessage.style.display = 'block';
        });
    });
</script>

</body>
</html>
