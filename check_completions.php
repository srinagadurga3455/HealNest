<?php
require 'config/connect.php';

echo "All task completions:\n\n";

$result = $conn->query('SELECT * FROM user_task_completions ORDER BY completed_at DESC LIMIT 10')->fetch_all(MYSQLI_ASSOC);
print_r($result);

echo "\n\nAll completions for user 1:\n\n";
$stmt = $conn->prepare('SELECT * FROM user_task_completions WHERE user_id = 1 ORDER BY completed_at DESC');
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
print_r($result);
?>