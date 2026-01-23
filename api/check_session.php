<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once '../config/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => 'Not logged in'
    ]);
    exit;
}

// Get user data
$stmt = $conn->prepare("SELECT id, full_name, email, assigned_program_id, assessment_taken, current_streak, wellness_score FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => 'User not found'
    ]);
    exit;
}

$user = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'user' => [
        'id' => $user['id'],
        'name' => $user['full_name'],
        'email' => $user['email'],
        'has_program' => !empty($user['assigned_program_id']),
        'assessment_taken' => (bool)$user['assessment_taken'],
        'current_streak' => $user['current_streak'],
        'wellness_score' => $user['wellness_score']
    ]
]);

$stmt->close();
$conn->close();
?>
