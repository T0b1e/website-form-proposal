<?php
require_once __DIR__ . '/../models/User.php'; // Adjust the path if needed
require_once __DIR__ . '/../../config/db.php'; // Ensure your Database class is accessible

class AuthController {

    private $db;

    public function __construct() {
        // Initialize the database connection
        $database = new Database();
        $this->db = $database->connect();
    }

    // Handle user signup
	public function signup() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// Get raw input and decode JSON
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
	

    // Handle user login (session-based)
    public function login() {
		session_start();
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// Get raw input and decode JSON
			$input = file_get_contents('php://input');
			$data = json_decode($input, true);
	
			// Check if email and password are set
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
			$user_data = $user->readByEmail($email); // Fetch user by email
	
			if (!$user_data) {
				// If no user is found with the given email
				echo json_encode([
					'success' => false,
					'message' => 'No account found with that email address'
				]);
				exit();
			}
	
			// Verify the provided password against the stored hashed password
			if (password_verify($password, $user_data['password'])) {
				// Store user info in session (optional session-based approach)
				$_SESSION['user_id']  = $user_data['user_id'];
				$_SESSION['username'] = $user_data['username'];
				$_SESSION['role']     = $user_data['role'];
	
				// Return user data in JSON so we can store it in localStorage
				echo json_encode([
					'success' => true,
					'message' => 'Login successful',
					'user_id'  => $user_data['user_id'],
					'username' => $user_data['username'],
					'role'     => $user_data['role']
				]);
				exit();
			} else {
				// Incorrect password
				echo json_encode([
					'success' => false,
					'message' => 'Invalid email or password'
				]);
				exit();
			}
		}
	}

	public function forgot_password() {
		session_start();
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$input = file_get_contents('php://input');
			$data = json_decode($input, true);
	
			if (!isset($data['email']) || !isset($data['password']) || !isset($data['confirm_password'])) {
				echo json_encode([
					'success' => false,
					'message' => 'Email, password and confirmation are required.'
				]);
				exit();
			}
	
			$email = trim($data['email']);
			$password = $data['password'];
			$confirm_password = $data['confirm_password'];
	
			if ($password !== $confirm_password) {
				echo json_encode([
					'success' => false,
					'message' => 'Passwords do not match.'
				]);
				exit();
			}
	
			$user = new User();
			$user_data = $user->readByEmail($email);
			if (!$user_data) {
				echo json_encode([
					'success' => false,
					'message' => 'No account found with that email address.'
				]);
				exit();
			}
	
			// Initialize pending changes array if not exists
			if (!isset($_SESSION['pending_password_changes'])) {
				$_SESSION['pending_password_changes'] = [];
			}
	
			// Append the new pending request with email & timestamp
			$_SESSION['pending_password_changes'][] = [
				'user_id'     => $user_data['user_id'],
				'email'       => $email,
				'new_password'=> password_hash($password, PASSWORD_DEFAULT),
				'timestamp'   => date('Y-m-d H:i:s')
			];
	
			echo json_encode([
				'success' => true,
				'message' => 'Password change request sent for approval.'
			]);
			exit();
		}
	}
	
	public function get_pending_forgot_password() {
		session_start();
		$pending = isset($_SESSION['pending_password_changes']) ? $_SESSION['pending_password_changes'] : [];
		echo json_encode([
			'success' => true,
			'pending' => $pending,
			'count'   => count($pending)
		]);
		exit();
	}
	
	public function approve_forgot_password() {
		session_start();
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$input = file_get_contents('php://input');
			$data = json_decode($input, true);
			if (!isset($data['index'])) {
				echo json_encode([
					'success' => false,
					'message' => 'No request index provided.'
				]);
				exit();
			}
			$index = $data['index'];
			if (!isset($_SESSION['pending_password_changes'][$index])) {
				echo json_encode([
					'success' => false,
					'message' => 'Invalid request index.'
				]);
				exit();
			}
			$pending = $_SESSION['pending_password_changes'][$index];
			$user = new User();
			$user->user_id = $pending['user_id'];
			if ($user->updatePassword($pending['new_password'])) {
				// Remove the approved request and re-index the array
				unset($_SESSION['pending_password_changes'][$index]);
				$_SESSION['pending_password_changes'] = array_values($_SESSION['pending_password_changes']);
				echo json_encode([
					'success' => true,
					'message' => 'Password change approved and updated successfully.'
				]);
				exit();
			} else {
				echo json_encode([
					'success' => false,
					'message' => 'Failed to update password.'
				]);
				exit();
			}
		}
	}
	
	public function decline_forgot_password() {
		session_start();
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$input = file_get_contents('php://input');
			$data = json_decode($input, true);
			if (!isset($data['index'])) {
				echo json_encode([
					'success' => false,
					'message' => 'No request index provided.'
				]);
				exit();
			}
			$index = $data['index'];
			if (isset($_SESSION['pending_password_changes'][$index])) {
				unset($_SESSION['pending_password_changes'][$index]);
				$_SESSION['pending_password_changes'] = array_values($_SESSION['pending_password_changes']);
				echo json_encode([
					'success' => true,
					'message' => 'Password change request declined.'
				]);
				exit();
			} else {
				echo json_encode([
					'success' => false,
					'message' => 'No pending password change request found.'
				]);
				exit();
			}
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
    } elseif ($_GET['action'] == 'approve_forgot_password') {
        $authController->approve_forgot_password();
    } elseif ($_GET['action'] == 'decline_forgot_password') {
        $authController->decline_forgot_password();
    } elseif ($_GET['action'] == 'get_pending_forgot_password') {
        $authController->get_pending_forgot_password();
    }
}
