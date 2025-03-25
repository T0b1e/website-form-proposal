<?php
// File: authcontroller.php
require_once __DIR__ . '/../models/User.php'; // Adjust the path as needed
require_once __DIR__ . '/../../config/db.php'; // Ensure your Database class is accessible

class AuthController {

    private $db;

    public function __construct() {
        // Initialize the database connection
        $database = new Database();
        $this->db = $database->connect();
    }

    // Example signup method (optional)
    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            $user = new User();
            $email = $data['email'];

            // Check if the email already exists
            if ($user->readByEmail($email)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email is already in use'
                ]);
                return;
            }

            // Capture additional fields from the decoded JSON
            $user->username   = trim($data['username']);
            $user->email      = trim($email);
            $user->password   = password_hash(trim($data['password']), PASSWORD_DEFAULT);
            $user->department = trim($data['department']);
            $user->phone      = trim($data['phone']);
            $user->address    = trim($data['address']);
            $user->role       = 'user'; // default role

            // Validate required fields
            if (
                empty($user->username) ||
                empty($user->email)    ||
                empty($user->password) ||
                empty($user->department) ||
                empty($user->phone)    ||
                empty($user->address)
            ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'All fields are required.'
                ]);
                return;
            }

            // Proceed with user creation
            if ($user->create()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Signup successful'
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Signup failed'
                ]);
                exit();
            }
        }
    }

    // Handle user login (using cookies)
    public function login() {
        $sessionLifetime = 7200; // 2 hours

        session_set_cookie_params($sessionLifetime);
        ini_set('session.gc_maxlifetime', $sessionLifetime);
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!isset($data['email']) || !isset($data['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email and password are required'
                ]);
                exit();
            }

            $email    = $data['email'];
            $password = $data['password'];

            $user = new User();
            $user_data = $user->readByEmail($email);

            if (!$user_data) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No account found with that email address'
                ]);
                exit();
            }

            if (password_verify($password, $user_data['password'])) {
                // Set cookies with a 2-hour expiration
                setcookie('user_id', $user_data['user_id'], time() + 7200, "/", "", false, true);
                setcookie('username', $user_data['username'], time() + 7200, "/", "", false, true);
                setcookie('role', $user_data['role'], time() + 7200, "/", "", false, true);

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'user_id'  => $user_data['user_id'],
                    'username' => $user_data['username'],
                    'role'     => $user_data['role']
                ]);
                exit();
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
                exit();
            }
        }
    }

    // Handle forgot password (token-based)
    public function forgot_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get raw input and decode JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!isset($data['email'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'กรุณาระบุอีเมล'
                ]);
                exit();
            }

            $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
            $user = new User();
            $user_data = $user->readByEmail($email);

            // Always respond generically to prevent user enumeration
            if (!$user_data) {
                echo json_encode([
                    'success' => true,
                    'message' => 'หากมีบัญชีที่ตรงกับอีเมลที่ระบุ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของท่าน'
                ]);
                exit();
            }

            // Generate a secure token and set expiration (1 hour from now)
            $token = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

			// Update the user's record with the reset token and expiration
			$userInstance = new User();
			$userInstance->user_id = $user_data['user_id'];
			if (!$userInstance->updateResetToken($token, $expires)) {
				echo json_encode([
					'success' => false,
					'message' => 'ไม่สามารถสร้าง token สำหรับรีเซ็ตรหัสผ่านได้'
				]);
				exit();
			}

            // Construct a reset link (adjust the URL to match your local environment)
            $resetLink = "http://localhost/form/public/reset_password.php?token=" . urlencode($token);

            // Prepare email in Thai (semi-formal)
            $subject = "คำขอรีเซ็ตรหัสผ่าน";
            $message = "เรียน ท่านผู้ใช้งาน,\n\n" .
                       "เราได้รับคำร้องขอให้รีเซ็ตรหัสผ่านสำหรับบัญชีของท่าน กรุณาคลิกที่ลิงก์ด้านล่างนี้เพื่อดำเนินการรีเซ็ตรหัสผ่าน:\n\n" .
                       $resetLink . "\n\n" .
                       "ลิงก์นี้จะมีอายุการใช้งานเพียง 1 ชั่วโมง หากท่านไม่ได้ร้องขอรีเซ็ตรหัสผ่าน กรุณาละเว้นอีเมลฉบับนี้\n\n" .
                       "ขอแสดงความนับถือ,\n" .
                       "Your Company Name";
			$headers = "From: no-reply@narongkorn.com\r\n";
			$headers .= "Reply-To: no-reply@narongkorn.com\r\n";
            // $headers = "From: no-reply@yourdomain.com\r\n";
            // $headers .= "Reply-To: no-reply@yourdomain.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($email, $subject, $message, $headers)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'หากมีบัญชีที่ตรงกับอีเมลที่ระบุ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านไปยังอีเมลของท่าน'
                ]);
            } else {
                error_log("Failed to send password reset email to " . $email);
                echo json_encode([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองใหม่อีกครั้ง'
                ]);
            }
            exit();
        }
    }

    // Handle user logout (clears session)
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /form/public/login.php');
        exit();
    }
}

// Routing: call methods based on the action parameter
if (isset($_GET['action'])) {
    $authController = new AuthController();

    if ($_GET['action'] == 'login') {
        $authController->login();
    } elseif ($_GET['action'] == 'signup') {
        $authController->signup();
    } elseif ($_GET['action'] == 'logout') {
        $authController->logout();
    } elseif ($_GET['action'] == 'forgot_password') {
        $authController->forgot_password();
    }
}
?>
