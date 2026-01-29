<?php
session_start();

// Set demo user session for testing
$_SESSION['user_id'] = 1;

// Include database connection
require_once 'config/connect.php';

$user_id = 1;
$task_id = 1; // Morning Meditation
$today = date('Y-m-d');

echo "<h2>Testing Task Completion</h2>";
echo "<h3>Completing Task ID: $task_id for User ID: $user_id on $today</h3>";

try {
    $conn->begin_transaction();
    
    // Check if already completed today
    $stmt = $conn->prepare("SELECT id FROM user_task_completions WHERE user_id = ? AND task_id = ? AND completion_date = ?");
    $stmt->bind_param("iis", $user_id, $task_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Get user's program ID for the completion record
        $stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $program_id = $user_data['assigned_program_id'] ?? null;
        
        // Insert completion record
        $stmt = $conn->prepare("INSERT INTO user_task_completions (user_id, task_id, program_id, completion_date, completed_at, notes) VALUES (?, ?, ?, ?, NOW(), ?)");
        $notes = "Test completion";
        $stmt->bind_param("iiiis", $user_id, $task_id, $program_id, $today, $notes);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Task completed successfully!</p>";
        } else {
            throw new Exception("Failed to record task completion");
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Task already completed today</p>";
    }
    
    $conn->commit();
    
    // Now check the updated status
    echo "<h3>Updated Task Status:</h3>";
    $stmt = $conn->prepare("
        SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
               CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed_today,
               utc.completed_at as completion_time,
               utc.notes as completion_notes
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ? AND utc.completion_date = ?)
        WHERE pt.program_id = 1 AND t.is_active = 1 AND t.id = ?
        ORDER BY pt.order_sequence
    ");
    $stmt->bind_param("isi", $user_id, $today, $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    echo "<pre>" . print_r($task, true) . "</pre>";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color: red;'>❌ Failed to complete task: " . $e->getMessage() . "</p>";
}
?>