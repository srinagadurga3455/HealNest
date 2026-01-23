<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../config/connect.php';

// Get session token from request
$data = json_decode(file_get_contents('php://input'), true);
$session_token = isset($data['session_token']) ? $data['session_token'] : '';

if (!empty($session_token)) {
    // Delete session from database
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
    $stmt->bind_param("s", $session_token);
    $stmt->execute();
    $stmt->close();
}

// Destroy PHP session
session_unset();
session_destroy();

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>
