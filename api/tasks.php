<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/TaskManager.php';
require_once '../config/User.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$taskManager = new TaskManager($db);
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_daily_tasks':
        getDailyTasks();
        break;
    case 'complete_task':
        completeTask();
        break;
    case 'get_task_history':
        getTaskHistory();
        break;
    case 'get_task_stats':
        getTaskStats();
        break;
    case 'add_custom_task':
        addCustomTask();
        break;
    case 'get_all_tasks':
        getAllTasks();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getDailyTasks() {
    global $taskManager, $user_id;
    
    $date = $_GET['date'] ?? date('Y-m-d');
    $tasks = $taskManager->getDailyTasks($user_id, $date);
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks,
        'date' => $date,
        'total_tasks' => count($tasks),
        'completed_tasks' => count(array_filter($tasks, function($task) { return $task['completed']; }))
    ]);
}

function completeTask() {
    global $taskManager, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['task_id']) || empty($data['program_id'])) {
        echo json_encode(['success' => false, 'message' => 'Task ID and Program ID are required']);
        return;
    }
    
    $task_id = $data['task_id'];
    $program_id = $data['program_id'];
    $notes = $data['notes'] ?? '';
    
    if ($taskManager->completeTask($user_id, $task_id, $program_id, $notes)) {
        // Update user streak
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        $new_streak = $user->calculateStreak($user_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Task completed successfully',
            'new_streak' => $new_streak
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to complete task']);
    }
}

function getTaskHistory() {
    global $taskManager, $user_id;
    
    $days = $_GET['days'] ?? 30;
    $history = $taskManager->getTaskHistory($user_id, $days);
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'days' => $days
    ]);
}

function getTaskStats() {
    global $taskManager, $user_id;
    
    $stats = $taskManager->getTaskStats($user_id);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function addCustomTask() {
    global $taskManager, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['task_name'])) {
        echo json_encode(['success' => false, 'message' => 'Task name is required']);
        return;
    }
    
    $task_name = $data['task_name'];
    $task_description = $data['task_description'] ?? '';
    $task_date = $data['task_date'] ?? date('Y-m-d');
    
    $task_id = $taskManager->addCustomTask($user_id, $task_name, $task_description, $task_date);
    
    if ($task_id) {
        echo json_encode([
            'success' => true, 
            'message' => 'Custom task added successfully',
            'task_id' => $task_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add custom task']);
    }
}

function getAllTasks() {
    global $taskManager;
    
    $tasks = $taskManager->getAllTasks();
    
    // Group tasks by category
    $grouped_tasks = [];
    foreach ($tasks as $task) {
        $category = $task['category'];
        if (!isset($grouped_tasks[$category])) {
            $grouped_tasks[$category] = [];
        }
        $grouped_tasks[$category][] = $task;
    }
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks,
        'grouped_tasks' => $grouped_tasks
    ]);
}
?>