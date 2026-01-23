<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/User.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        register();
        break;
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'check_session':
        checkSession();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function register() {
    global $user;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['full_name']) || empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    if (strlen($data['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        return;
    }
    
    $user->full_name = $data['full_name'];
    $user->email = $data['email'];
    $user->password_hash = $data['password'];
    
    if ($user->register()) {
        // Create session
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->full_name;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'assessment_taken' => false
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Email may already exist.']);
    }
}

function login() {
    global $user;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }
    
    $user->email = $data['email'];
    $user->password_hash = $data['password'];
    
    if ($user->login()) {
        // Create session
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->full_name;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'assigned_program_id' => $user->assigned_program_id,
                'current_streak' => $user->current_streak,
                'highest_streak' => $user->highest_streak,
                'assessment_taken' => $user->assessment_taken,
                'wellness_score' => $user->wellness_score
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
}

function logout() {
    session_start();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

function checkSession() {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        global $user;
        $userData = $user->getUserById($_SESSION['user_id']);
        
        if ($userData) {
            echo json_encode([
                'success' => true, 
                'logged_in' => true,
                'user' => [
                    'id' => $userData['id'],
                    'full_name' => $userData['full_name'],
                    'email' => $userData['email'],
                    'assigned_program_id' => $userData['assigned_program_id'],
                    'current_streak' => $userData['current_streak'],
                    'highest_streak' => $userData['highest_streak'],
                    'assessment_taken' => $userData['assessment_taken'],
                    'wellness_score' => $userData['wellness_score'],
                    'program_name' => $userData['program_name']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'logged_in' => false]);
        }
    } else {
        echo json_encode(['success' => false, 'logged_in' => false]);
    }
}
?>