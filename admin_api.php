<?php
header('Content-Type: application/json');
require_once 'config/connect.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_users':
        getUsers();
        break;
    case 'get_programs':
        getPrograms();
        break;
    case 'get_activity':
        getActivity();
        break;
    case 'reset_demo_user':
        resetDemoUser();
        break;
    case 'clear_sessions':
        clearSessions();
        break;
    case 'reset_database':
        resetDatabase();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getUsers() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT u.*, p.program_name 
        FROM users u 
        LEFT JOIN programs p ON u.assigned_program_id = p.id 
        ORDER BY u.id
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
}

function getPrograms() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT p.*, COUNT(pt.id) as task_count 
        FROM programs p 
        LEFT JOIN program_tasks pt ON p.id = pt.program_id 
        GROUP BY p.id 
        ORDER BY p.id
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    echo json_encode(['success' => true, 'programs' => $programs]);
}

function getActivity() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT 
            utc.completion_date as date,
            u.full_name as user_name,
            'Task Completion' as activity_type,
            CONCAT(t.task_name, ' completed') as details
        FROM user_task_completions utc
        JOIN users u ON utc.user_id = u.id
        JOIN tasks t ON utc.task_id = t.id
        
        UNION ALL
        
        SELECT 
            me.entry_date as date,
            u.full_name as user_name,
            'Mood Entry' as activity_type,
            CONCAT('Mood: ', me.mood, ' (', me.mood_score, '/5)') as details
        FROM mood_entries me
        JOIN users u ON me.user_id = u.id
        
        ORDER BY date DESC
        LIMIT 20
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activity = [];
    while ($row = $result->fetch_assoc()) {
        $activity[] = $row;
    }
    
    echo json_encode(['success' => true, 'activity' => $activity]);
}

function resetDemoUser() {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        $user_id = 1;
        
        // Reset user data
        $stmt = $conn->prepare("UPDATE users SET current_streak = 0, highest_streak = 0, last_activity_date = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Clear task completions
        $stmt = $conn->prepare("DELETE FROM user_task_completions WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Clear mood entries
        $stmt = $conn->prepare("DELETE FROM mood_entries WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Clear journal entries
        $stmt = $conn->prepare("DELETE FROM journal_entries WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Demo user reset successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error resetting demo user: ' . $e->getMessage()]);
    }
}

function clearSessions() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM user_sessions");
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'All sessions cleared']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error clearing sessions: ' . $e->getMessage()]);
    }
}

function resetDatabase() {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        // Clear all user data
        $conn->query("DELETE FROM user_task_completions");
        $conn->query("DELETE FROM mood_entries");
        $conn->query("DELETE FROM journal_entries");
        $conn->query("DELETE FROM user_sessions");
        $conn->query("DELETE FROM daily_progress");
        $conn->query("DELETE FROM user_achievements");
        $conn->query("DELETE FROM assessments");
        
        // Reset users
        $conn->query("UPDATE users SET current_streak = 0, highest_streak = 0, last_activity_date = NULL, assessment_taken = 0, assigned_program_id = NULL, program_start_date = NULL");
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Database reset successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error resetting database: ' . $e->getMessage()]);
    }
}

$conn->close();
?>