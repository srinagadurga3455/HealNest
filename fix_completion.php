<?php
require 'config/connect.php';

$today = date('Y-m-d');
echo "Fixing completion date for today: $today\n\n";

// Update the existing record with the correct date
$stmt = $conn->prepare("UPDATE user_task_completions SET completion_date = ? WHERE user_id = 1 AND task_id = 1 AND completion_date = '0000-00-00'");
$stmt->bind_param("s", $today);

if ($stmt->execute()) {
    echo "✅ Updated completion date successfully\n";
    echo "Affected rows: " . $stmt->affected_rows . "\n\n";
} else {
    echo "❌ Failed to update completion date\n\n";
}

// Check the result
echo "Updated record:\n";
$stmt = $conn->prepare('SELECT * FROM user_task_completions WHERE user_id = 1 AND task_id = 1 ORDER BY completed_at DESC LIMIT 1');
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
print_r($result);

// Test the query that the API uses
echo "\n\nTesting API query:\n";
$stmt = $conn->prepare("
    SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
           CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed_today,
           utc.completed_at as completion_time,
           utc.notes as completion_notes
    FROM program_tasks pt
    JOIN tasks t ON pt.task_id = t.id
    LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = 1 AND utc.completion_date = ?)
    WHERE pt.program_id = 1 AND t.is_active = 1 AND t.id = 1
    ORDER BY pt.order_sequence
");
$stmt->bind_param("s", $today);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
print_r($task);
?>