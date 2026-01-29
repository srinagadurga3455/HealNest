<?php
session_start();

echo "<h2>Authentication Test</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Session ID:</h3>";
echo session_id();

echo "<h3>Session Status:</h3>";
echo "Session Status: " . session_status() . "<br>";
echo "User logged in: " . (isset($_SESSION['user_id']) ? 'YES' : 'NO') . "<br>";

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
    echo "User Email: " . ($_SESSION['user_email'] ?? 'Not set') . "<br>";
}

echo "<h3>Test Actions:</h3>";
echo '<a href="pages/login.php" style="margin: 10px; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>';
echo '<a href="pages/dashboard.php" style="margin: 10px; padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go to Dashboard</a>';
echo '<a href="logout.php" style="margin: 10px; padding: 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px;">Logout</a>';

echo "<h3>Database Connection Test:</h3>";
try {
    require_once 'config/connect.php';
    echo "Database connection: SUCCESS<br>";
    
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "User found in database: " . $user['full_name'] . " (" . $user['email'] . ")<br>";
        } else {
            echo "User NOT found in database<br>";
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "Database connection: ERROR - " . $e->getMessage() . "<br>";
}
?>