<?php
session_start();

// Set demo user session for testing
$_SESSION['user_id'] = 1;

// Include database connection
require_once 'config/connect.php';

echo "<h2>Testing Tasks Data</h2>";

$user_id = 1;
$today = date('Y-m-d');

echo "<h3>Current Date: $today</h3>";

// Test user data
echo "<h3>User Data:</h3>";
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
echo "<pre>" . print_r($user, true) . "</pre>";

echo "<hr>";

// Test task completions for today
echo "<h3>Task Completions for Today ($today):</h3>";
$stmt = $conn->prepare("SELECT * FROM user_task_completions WHERE user_id = ? AND completion_date = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$completions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "<pre>" . print_r($completions, true) . "</pre>";

echo "<hr>";

// Test program tasks with completion status
echo "<h3>Program Tasks with Completion Status:</h3>";
$stmt = $conn->prepare("
    SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
           CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed_today,
           utc.completed_at as completion_time,
           utc.notes as completion_notes
    FROM program_tasks pt
    JOIN tasks t ON pt.task_id = t.id
    LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ? AND utc.completion_date = ?)
    WHERE pt.program_id = ? AND t.is_active = 1
    ORDER BY pt.order_sequence
");
$stmt->bind_param("isi", $user_id, $today, $user['assigned_program_id']);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "<pre>" . print_r($tasks, true) . "</pre>";

echo "<hr>";

// Test the getTodaysTasksData function logic
echo "<h3>Formatted Tasks Data:</h3>";
$formatted_tasks = [];
foreach ($tasks as $row) {
    $task_data = [
        'id' => $row['id'],
        'title' => $row['task_name'],
        'description' => $row['task_description'],
        'type' => $row['task_type'],
        'category' => $row['category'],
        'estimated_duration' => $row['estimated_duration'],
        'difficulty_level' => $row['difficulty_level'],
        'is_required' => (bool)$row['is_required'],
        'completed_today' => (bool)$row['completed_today']
    ];
    
    // Add completion details if task was completed today
    if ($row['completed_today']) {
        $task_data['completion_details'] = [
            'completed_at' => $row['completion_time'],
            'completion_date' => $today,
            'notes' => $row['completion_notes']
        ];
    }
    
    $formatted_tasks[] = $task_data;
}

echo "<pre>" . print_r($formatted_tasks, true) . "</pre>";
?>