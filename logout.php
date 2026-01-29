<?php
session_start();

// Include database connection
require_once 'config/connect.php';

// Clear session from database if user_id exists
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Delete all sessions for this user
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Clear all session data
session_unset();
session_destroy();

// Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

$conn->close();

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax || $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} else {
    // Redirect to landing page for direct access
    header('Location: pages/landing.html');
    exit;
}
?>