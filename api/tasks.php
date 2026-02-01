<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();

// Clear all output buffers
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include database connection
    require_once '../config/connect.php';

    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

    // Clear any output before processing
    ob_clean();

    switch ($action) {
        case 'get_user_tasks':
            getUserTasks($conn, $user_id);
            break;
        case 'complete_task':
            completeTask($conn, $user_id, $input);
            break;
        case 'get_task_progress':
            getTaskProgress($conn, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getUserTasks($conn, $user_id) {
    try {
        // Get user's assigned program
        $user_stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result()->fetch_assoc();
        
        if (!$user_result || !$user_result['assigned_program_id']) {
            echo json_encode([
                'success' => true,
                'tasks' => [],
                'message' => 'No program assigned yet. Please complete your assessment first.'
            ]);
            return;
        }
        
        $program_id = $user_result['assigned_program_id'];
        
        // Get tasks assigned to this program
        $tasks_stmt = $conn->prepare("
            SELECT t.id, t.task_name, t.task_description, t.category, t.estimated_duration, t.difficulty_level,
                   pt.is_required, pt.order_sequence,
                   CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed
            FROM program_tasks pt
            JOIN tasks t ON pt.task_id = t.id
            LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ? AND utc.completion_date = CURDATE())
            WHERE pt.program_id = ? AND t.is_active = 1
            ORDER BY pt.order_sequence ASC
        ");
        
        $tasks_stmt->bind_param("ii", $user_id, $program_id);
        $tasks_stmt->execute();
        $tasks_result = $tasks_stmt->get_result();
        
        $tasks = [];
        while ($task = $tasks_result->fetch_assoc()) {
            $tasks[] = [
                'id' => $task['id'],
                'title' => $task['task_name'],
                'description' => $task['task_description'],
                'category' => $task['category'],
                'duration' => $task['estimated_duration'],
                'difficulty' => $task['difficulty_level'],
                'required' => $task['is_required'],
                'completed' => (bool)$task['completed']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks,
            'program_id' => $program_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error loading tasks: ' . $e->getMessage()
        ]);
    }
}

function completeTask($conn, $user_id, $data) {
    try {
        $task_id = $data['task_id'] ?? null;
        $completed = $data['completed'] ?? false;
        
        if (!$task_id) {
            echo json_encode(['success' => false, 'message' => 'Task ID required']);
            return;
        }
        
        if ($completed) {
            // Mark task as completed
            $stmt = $conn->prepare("
                INSERT INTO user_task_completions (user_id, task_id, program_id, completion_date, completed_at) 
                SELECT ?, ?, u.assigned_program_id, CURDATE(), NOW() 
                FROM users u WHERE u.id = ?
                ON DUPLICATE KEY UPDATE completed_at = NOW()
            ");
            $stmt->bind_param("iii", $user_id, $task_id, $user_id);
        } else {
            // Mark task as incomplete (remove completion)
            $stmt = $conn->prepare("
                DELETE FROM user_task_completions 
                WHERE user_id = ? AND task_id = ? AND completion_date = CURDATE()
            ");
            $stmt->bind_param("ii", $user_id, $task_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => $completed ? 'Task completed!' : 'Task marked as incomplete'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update task status'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating task: ' . $e->getMessage()
        ]);
    }
}

function getTaskProgress($conn, $user_id) {
    try {
        // Get user's assigned program
        $user_stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result()->fetch_assoc();
        
        if (!$user_result || !$user_result['assigned_program_id']) {
            echo json_encode([
                'success' => true,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'progress_percentage' => 0
            ]);
            return;
        }
        
        $program_id = $user_result['assigned_program_id'];
        
        // Get total tasks for today
        $total_stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM program_tasks pt
            JOIN tasks t ON pt.task_id = t.id
            WHERE pt.program_id = ? AND t.is_active = 1
        ");
        $total_stmt->bind_param("i", $program_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result()->fetch_assoc();
        $total_tasks = $total_result['total'];
        
        // Get completed tasks for today
        $completed_stmt = $conn->prepare("
            SELECT COUNT(*) as completed
            FROM user_task_completions utc
            JOIN program_tasks pt ON utc.task_id = pt.task_id
            WHERE utc.user_id = ? AND pt.program_id = ? AND utc.completion_date = CURDATE()
        ");
        $completed_stmt->bind_param("ii", $user_id, $program_id);
        $completed_stmt->execute();
        $completed_result = $completed_stmt->get_result()->fetch_assoc();
        $completed_tasks = $completed_result['completed'];
        
        $progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
        
        echo json_encode([
            'success' => true,
            'total_tasks' => $total_tasks,
            'completed_tasks' => $completed_tasks,
            'progress_percentage' => $progress_percentage
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error getting progress: ' . $e->getMessage()
        ]);
    }
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>