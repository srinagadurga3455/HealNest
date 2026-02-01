<?php
// Start output buffering to prevent any accidental output
ob_start();

session_start();

// Clear any previous output and set JSON header
ob_clean();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Disable error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include database connection
require_once '../config/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_dashboard_data':
        getDashboardData();
        break;
    case 'get_user_stats':
        getUserStats();
        break;
    case 'get_todays_tasks':
        getTodaysTasks();
        break;
    case 'complete_task':
        completeTask();
        break;
    case 'get_recent_journal_entries':
        getRecentJournalEntries();
        break;
    case 'get_mood_data':
        getMoodData();
        break;
    case 'save_mood':
        saveMood();
        break;
    case 'update_progress':
        updateProgress();
        break;
    case 'update_streaks':
        updateAllUserStreaks();
        break;
    case 'get_streak_info':
        getStreakInfo();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getDashboardData() {
    global $conn, $user_id;
    
    // Get user info with program details
    $stmt = $conn->prepare("
        SELECT u.*, p.program_name, p.program_description, p.icon, p.color 
        FROM users u 
        LEFT JOIN programs p ON u.assigned_program_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }
    
    // Get user stats
    $stats = getUserStatsData();
    
    // Get today's tasks
    $tasks = getTodaysTasksData();
    
    // Get recent journal entries
    $journal_entries = getRecentJournalEntriesData();
    
    // Get today's mood
    $todays_mood = getTodaysMoodData();
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'wellness_score' => $user['wellness_score'],
            'current_streak' => $user['current_streak'],
            'highest_streak' => $user['highest_streak'],
            'assessment_taken' => (bool)$user['assessment_taken'],
            'assigned_program_id' => $user['assigned_program_id'],
            'program_start_date' => $user['program_start_date'],
            'program' => $user['assigned_program_id'] ? [
                'name' => $user['program_name'],
                'description' => $user['program_description'],
                'icon' => $user['icon'],
                'color' => $user['color']
            ] : null
        ],
        'stats' => $stats,
        'tasks' => $tasks,
        'journal_entries' => $journal_entries,
        'todays_mood' => $todays_mood
    ]);
}

function getUserStats() {
    echo json_encode([
        'success' => true,
        'stats' => getUserStatsData()
    ]);
}

function getUserStatsData() {
    global $conn, $user_id;
    
    // Get basic user stats
    $stmt = $conn->prepare("SELECT current_streak, highest_streak, wellness_score, program_start_date, assigned_program_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    // Calculate program days completed and remaining
    $program_days_completed = 0;
    $program_days_remaining = 0;
    $program_duration = 30; // Default duration
    
    if ($user_data['program_start_date'] && $user_data['assigned_program_id']) {
        // Get program duration
        $stmt = $conn->prepare("SELECT duration_days FROM programs WHERE id = ?");
        $stmt->bind_param("i", $user_data['assigned_program_id']);
        $stmt->execute();
        $program_result = $stmt->get_result();
        if ($program_result->num_rows > 0) {
            $program_duration = $program_result->fetch_assoc()['duration_days'] ?? 30;
        }
        
        // Calculate days since program start
        $start_date = new DateTime($user_data['program_start_date']);
        $today = new DateTime();
        $program_days_completed = max(0, $start_date->diff($today)->days + 1);
        $program_days_remaining = max(0, $program_duration - $program_days_completed);
        
        // Don't let completed days exceed program duration
        if ($program_days_completed > $program_duration) {
            $program_days_completed = $program_duration;
            $program_days_remaining = 0;
        }
    }
    
    // Get journal entries count
    $stmt = $conn->prepare("SELECT COUNT(*) as journal_count FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $journal_data = $result->fetch_assoc();
    
    // Get completed tasks today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) as tasks_completed FROM user_task_completions WHERE user_id = ? AND completion_date = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $task_data = $result->fetch_assoc();
    
    // Calculate real current streak based on consecutive days with task completions
    $current_streak = calculateRealStreak($user_id);
    
    // Update user's current streak in database if it's different
    if ($current_streak != $user_data['current_streak']) {
        $highest_streak = max($current_streak, $user_data['highest_streak']);
        $stmt = $conn->prepare("UPDATE users SET current_streak = ?, highest_streak = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iii", $current_streak, $highest_streak, $user_id);
        $stmt->execute();
        $user_data['current_streak'] = $current_streak;
        $user_data['highest_streak'] = $highest_streak;
    }
    
    return [
        'current_streak' => $user_data['current_streak'] ?? 0,
        'highest_streak' => $user_data['highest_streak'] ?? 0,
        'wellness_score' => $user_data['wellness_score'] ?? 0,
        'program_days_completed' => $program_days_completed,
        'program_days_remaining' => $program_days_remaining,
        'program_duration' => $program_duration,
        'journal_count' => $journal_data['journal_count'] ?? 0,
        'tasks_completed_today' => $task_data['tasks_completed'] ?? 0
    ];
}

function calculateRealStreak($user_id) {
    global $conn;
    
    $streak = 0;
    $current_date = new DateTime();
    
    // Check each day backwards from today to find consecutive days with activity
    for ($i = 0; $i < 365; $i++) {
        $date_string = $current_date->format('Y-m-d');
        
        // Check if user had any activity on this date (tasks completed OR mood logged)
        $stmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM user_task_completions WHERE user_id = ? AND completion_date = ?) +
                (SELECT COUNT(*) FROM mood_entries WHERE user_id = ? AND entry_date = ?) as total_activity
        ");
        $stmt->bind_param("isis", $user_id, $date_string, $user_id, $date_string);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $total_activity = $data['total_activity'] ?? 0;
        
        // Consider day active if user completed at least 1 task OR logged mood
        if ($total_activity >= 1) {
            $streak++;
        } else {
            // If it's today and no activity yet, don't break the streak
            if ($i == 0 && $date_string == date('Y-m-d')) {
                // Today - check if it's still early in the day, don't break streak yet
                $current_hour = (int)date('H');
                if ($current_hour < 12) {
                    // It's still morning, don't break streak yet
                    $streak++;
                } else {
                    // It's afternoon/evening with no activity, break streak
                    break;
                }
            } else {
                // Past day with no activity, break streak
                break;
            }
        }
        
        $current_date->modify('-1 day');
    }
    
    return $streak;
}

function updateUserStreaks() {
    global $conn;
    
    // Get all users with programs
    $stmt = $conn->query("SELECT id FROM users WHERE assigned_program_id IS NOT NULL");
    
    while ($row = $stmt->fetch_assoc()) {
        $user_id = $row['id'];
        
        // Calculate current streak
        $current_streak = calculateRealStreak($user_id);
        
        // Get current highest streak
        $stmt2 = $conn->prepare("SELECT highest_streak FROM users WHERE id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $user_data = $result->fetch_assoc();
        $highest_streak = max($current_streak, $user_data['highest_streak'] ?? 0);
        
        // Update user streaks
        $stmt3 = $conn->prepare("UPDATE users SET current_streak = ?, highest_streak = ?, last_activity_date = CURDATE(), updated_at = NOW() WHERE id = ?");
        $stmt3->bind_param("iii", $current_streak, $highest_streak, $user_id);
        $stmt3->execute();
    }
}

function getTodaysTasks() {
    echo json_encode([
        'success' => true,
        'tasks' => getTodaysTasksData()
    ]);
}

function getTodaysTasksData() {
    global $conn, $user_id;
    
    // Get user's assigned program
    $stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if (!$user_data['assigned_program_id']) {
        return [];
    }
    
    $today = date('Y-m-d');
    
    // Get program tasks with completion details
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
    $stmt->bind_param("isi", $user_id, $today, $user_data['assigned_program_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
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
        
        $tasks[] = $task_data;
    }
    
    return $tasks;
}

function completeTask() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $task_id = $data['task_id'] ?? 0;
    $completed = $data['completed'] ?? true;
    $notes = $data['notes'] ?? '';
    
    if (!$task_id) {
        echo json_encode(['success' => false, 'message' => 'Task ID is required']);
        return;
    }
    
    $today = date('Y-m-d');
    
    try {
        $conn->begin_transaction();
        
        if ($completed) {
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
                $stmt->bind_param("iiiss", $user_id, $task_id, $program_id, $today, $notes);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to record task completion");
                }
            }
        } else {
            // Remove completion record
            $stmt = $conn->prepare("DELETE FROM user_task_completions WHERE user_id = ? AND task_id = ? AND completion_date = ?");
            $stmt->bind_param("iis", $user_id, $task_id, $today);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to remove task completion");
            }
        }
        
        // Update daily progress and streaks
        updateDailyProgress();
        
        // Update user streaks after task completion
        $current_streak = calculateRealStreak($user_id);
        $stmt = $conn->prepare("SELECT highest_streak FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $highest_streak = max($current_streak, $user_data['highest_streak'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE users SET current_streak = ?, highest_streak = ?, last_activity_date = CURDATE(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iii", $current_streak, $highest_streak, $user_id);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update task: ' . $e->getMessage()]);
    }
}

function getRecentJournalEntries() {
    echo json_encode([
        'success' => true,
        'entries' => getRecentJournalEntriesData()
    ]);
}

function getRecentJournalEntriesData() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'mood' => $row['mood'],
            'tags' => json_decode($row['tags'], true),
            'created_at' => $row['created_at']
        ];
    }
    
    return $entries;
}

function getMoodData() {
    echo json_encode([
        'success' => true,
        'mood_data' => getMoodDataArray()
    ]);
}

function getMoodDataArray() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? ORDER BY entry_date DESC LIMIT 30");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mood_data = [];
    while ($row = $result->fetch_assoc()) {
        $mood_data[$row['entry_date']] = [
            'mood' => $row['mood'],
            'mood_score' => $row['mood_score'],
            'note' => $row['note']
        ];
    }
    
    return $mood_data;
}

function getTodaysMoodData() {
    global $conn, $user_id;
    
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM mood_entries WHERE user_id = ? AND entry_date = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

function saveMood() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $mood = $data['mood'] ?? '';
    $mood_score = $data['mood_score'] ?? 3;
    $note = $data['note'] ?? '';
    
    if (!$mood) {
        echo json_encode(['success' => false, 'message' => 'Mood is required']);
        return;
    }
    
    $today = date('Y-m-d');
    
    try {
        $conn->begin_transaction();
        
        // Check if mood already exists for today
        $stmt = $conn->prepare("SELECT id FROM mood_entries WHERE user_id = ? AND entry_date = ?");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing mood
            $stmt = $conn->prepare("UPDATE mood_entries SET mood = ?, mood_score = ?, note = ?, created_at = NOW() WHERE user_id = ? AND entry_date = ?");
            $stmt->bind_param("sisis", $mood, $mood_score, $note, $user_id, $today);
        } else {
            // Insert new mood
            $stmt = $conn->prepare("INSERT INTO mood_entries (user_id, mood, mood_score, note, entry_date, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isiss", $user_id, $mood, $mood_score, $note, $today);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save mood");
        }
        
        // Update user's last activity and streaks
        $current_streak = calculateRealStreak($user_id);
        $stmt = $conn->prepare("SELECT highest_streak FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $highest_streak = max($current_streak, $user_data['highest_streak'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE users SET current_streak = ?, highest_streak = ?, last_activity_date = CURDATE(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iii", $current_streak, $highest_streak, $user_id);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Mood saved successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to save mood: ' . $e->getMessage()]);
    }
}

function updateProgress() {
    updateDailyProgress();
    echo json_encode(['success' => true, 'message' => 'Progress updated']);
}

function updateAllUserStreaks() {
    updateUserStreaks();
    echo json_encode(['success' => true, 'message' => 'All user streaks updated']);
}

function getStreakInfo() {
    global $conn, $user_id;
    
    // Get current streak calculation details
    $current_streak = calculateRealStreak($user_id);
    
    // Get streak history (last 30 days)
    $streak_history = [];
    $current_date = new DateTime();
    
    for ($i = 0; $i < 30; $i++) {
        $date_string = $current_date->format('Y-m-d');
        
        // Check activity for this date
        $stmt = $conn->prepare("
            SELECT 
                (SELECT COUNT(*) FROM user_task_completions WHERE user_id = ? AND completion_date = ?) as tasks,
                (SELECT COUNT(*) FROM mood_entries WHERE user_id = ? AND entry_date = ?) as moods
        ");
        $stmt->bind_param("isis", $user_id, $date_string, $user_id, $date_string);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        $streak_history[] = [
            'date' => $date_string,
            'tasks' => $data['tasks'] ?? 0,
            'moods' => $data['moods'] ?? 0,
            'active' => ($data['tasks'] + $data['moods']) > 0
        ];
        
        $current_date->modify('-1 day');
    }
    
    echo json_encode([
        'success' => true,
        'current_streak' => $current_streak,
        'streak_history' => array_reverse($streak_history)
    ]);
}

function updateDailyProgress() {
    global $conn, $user_id;
    
    $today = date('Y-m-d');
    
    // Get total tasks assigned for today (from user's program)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_tasks
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        JOIN users u ON u.assigned_program_id = pt.program_id
        WHERE u.id = ? AND t.is_active = 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_data = $result->fetch_assoc();
    $total_tasks = $total_data['total_tasks'] ?? 0;
    
    // Get completed tasks for today
    $stmt = $conn->prepare("SELECT COUNT(*) as completed_tasks FROM user_task_completions WHERE user_id = ? AND completion_date = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed_data = $result->fetch_assoc();
    $completed_tasks = $completed_data['completed_tasks'] ?? 0;
    
    // Calculate completion percentage
    $completion_percentage = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
    
    // Calculate streak
    $streak = calculateStreak();
    
    // Update or insert daily progress
    $stmt = $conn->prepare("
        INSERT INTO daily_progress (user_id, progress_date, total_tasks_assigned, tasks_completed, completion_percentage, streak_day)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        total_tasks_assigned = VALUES(total_tasks_assigned),
        tasks_completed = VALUES(tasks_completed),
        completion_percentage = VALUES(completion_percentage),
        streak_day = VALUES(streak_day)
    ");
    $stmt->bind_param("isiidd", $user_id, $today, $total_tasks, $completed_tasks, $completion_percentage, $streak);
    $stmt->execute();
    
    // Update user's current streak
    $stmt = $conn->prepare("UPDATE users SET current_streak = ?, highest_streak = GREATEST(highest_streak, ?), last_activity_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("iisi", $streak, $streak, $today, $user_id);
    $stmt->execute();
}

function calculateStreak() {
    global $conn, $user_id;
    
    $streak = 0;
    $current_date = new DateTime();
    
    // Check each day backwards from today
    for ($i = 0; $i < 365; $i++) {
        $date_string = $current_date->format('Y-m-d');
        
        // Check completed tasks for this date
        $stmt = $conn->prepare("SELECT COUNT(*) as completed FROM user_task_completions WHERE user_id = ? AND completion_date = ?");
        $stmt->bind_param("is", $user_id, $date_string);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $completed_tasks = $data['completed'] ?? 0;
        
        // Consider day complete if at least 2 tasks are done (more realistic threshold)
        if ($completed_tasks >= 2) {
            $streak++;
        } else {
            break;
        }
        
        $current_date->modify('-1 day');
    }
    
    return $streak;
}

// Close database connection
if (isset($conn)) {
    $conn->close();
}

// End output buffering and send clean JSON
ob_end_flush();
?>