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

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, full_name, email, password_hash, assigned_program_id, assessment_taken FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
    exit;
}

$user = $result->fetch_assoc();

// Verify password

$passwordValid = password_verify($password, $user['password_hash']);

if (!$passwordValid) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
    exit;
}

// Login successful - create session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['logged_in'] = true;

// Generate session token
$session_token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+7 days'));

// Store session in database
$stmt = $conn->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iss", $user['id'], $session_token, $expires_at);
$stmt->execute();

// Update last activity date
$stmt = $conn->prepare("UPDATE users SET last_activity_date = CURDATE() WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'name' => $user['full_name'],
        'email' => $user['email'],
        'has_program' => !empty($user['assigned_program_id']),
        'assessment_taken' => (bool)$user['assessment_taken']
    ],
    'session_token' => $session_token,
    'redirect' => $user['assigned_program_id'] ? 'dashboard.php' : ($user['assessment_taken'] ? 'dashboard.php' : 'assessment.php')
]);

$stmt->close();
$conn->close();
?>
