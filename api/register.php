<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../config/connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Fallback to regular POST
    $data = $_POST;
}

// Handle both field name formats
$fullName = isset($data['full_name']) ? trim($data['full_name']) : 
           (isset($data['fullName']) ? trim($data['fullName']) : '');
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';
$confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : $password; // Default to password if not provided

// Validation
if (empty($fullName) || empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all fields'
    ]);
    exit;
}

// Only check password confirmation if it was provided
if (isset($data['confirmPassword']) && $password !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match'
    ]);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'An account with this email already exists'
    ]);
    exit;
}

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, current_streak, highest_streak, assessment_taken, wellness_score, created_at, updated_at) VALUES (?, ?, ?, 0, 0, 0, 0, NOW(), NOW())");
$stmt->bind_param("sss", $fullName, $email, $passwordHash);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    
    // Create session for the new user
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;
    $_SESSION['logged_in'] = true;
    
    // Generate session token
    $session_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    // Store session in database
    $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $session_token, $expires_at);
    $stmt->execute();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user' => [
            'id' => $userId,
            'name' => $fullName,
            'email' => $email,
            'has_program' => false,
            'assessment_taken' => false
        ],
        'session_token' => $session_token,
        'redirect' => 'assessment.php'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
?>