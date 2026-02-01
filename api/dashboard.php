<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Include database connection
    require_once '../config/connect.php';

    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'get_dashboard_data':
            getDashboardData($conn, $user_id);
            break;
        case 'get_user_stats':
            getUserStats($conn, $user_id);
            break;
        case 'get_recent_activities':
            getRecentActivities($conn, $user_id);
            break;
        case 'update_progress':
            updateProgress($conn, $user_id, $input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getDashboardData($conn, $user_id) {
    try {
        // Get user data with program info
        $user_stmt = $conn->prepare("
            SELECT u.full_name, u.email, u.current_streak, u.highest_streak, 
                   u.wellness_score, u.assigned_program_id, u.program_start_date,
                   p.program_name, p.program_description, p.icon, p.color
            FROM users u 
            LEFT JOIN programs p ON u.assigned_program_id = p.id 
            WHERE u.id = ?
        ");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        $user = $user_result->fetch_assoc();
        
        // Get today's task progress - use personalized tasks like the tasks page
        $today = date('Y-m-d');
        
        // Get user's latest assessment to determine personalized tasks (same as tasks.php)
        $assessment_stmt = $conn->prepare("SELECT answers, wellness_score FROM assessments WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $assessment_stmt->bind_param("i", $user_id);
        $assessment_stmt->execute();
        $assessment_result = $assessment_stmt->get_result();
        
        $task_progress = ['total_tasks' => 0, 'completed_tasks' => 0];
        
        if ($assessment_result->num_rows > 0) {
            // User has assessment, use personalized tasks (same logic as tasks.php)
            $assessment = $assessment_result->fetch_assoc();
            $answers = json_decode($assessment['answers'], true);
            $wellness_score = $assessment['wellness_score'];
            
            // Get the same personalized tasks that would be assigned
            $personalizedTasks = analyzeAssessmentAndAssignTasks($answers, $wellness_score);
            $task_progress['total_tasks'] = count($personalizedTasks);
            
            // Get completed personalized tasks for today
            $task_ids = array_column($personalizedTasks, 'id');
            if (!empty($task_ids)) {
                $placeholders = str_repeat('?,', count($task_ids) - 1) . '?';
                $completed_stmt = $conn->prepare("
                    SELECT COUNT(*) as completed
                    FROM user_task_completions 
                    WHERE user_id = ? AND task_id IN ($placeholders) AND completion_date = ?
                ");
                
                $types = 'i' . str_repeat('i', count($task_ids)) . 's';
                $params = array_merge([$user_id], $task_ids, [$today]);
                $completed_stmt->bind_param($types, ...$params);
                $completed_stmt->execute();
                $completed_result = $completed_stmt->get_result()->fetch_assoc();
                $task_progress['completed_tasks'] = $completed_result['completed'];
            }
        } else {
            // No assessment, fall back to program-based tasks
            $task_progress_stmt = $conn->prepare("
                SELECT 
                    COUNT(pt.task_id) as total_tasks,
                    COUNT(utc.id) as completed_tasks
                FROM program_tasks pt
                JOIN tasks t ON pt.task_id = t.id
                LEFT JOIN user_task_completions utc ON (utc.task_id = pt.task_id AND utc.user_id = ? AND utc.completion_date = ?)
                WHERE pt.program_id = ? AND t.is_active = 1
            ");
            $task_progress_stmt->bind_param("isi", $user_id, $today, $user['assigned_program_id']);
            $task_progress_stmt->execute();
            $task_progress = $task_progress_stmt->get_result()->fetch_assoc();
        }
        
        // Calculate completion percentage (cap at 100%)
        $completion_percentage = 0;
        if ($task_progress['total_tasks'] > 0) {
            $completion_percentage = min(100, round(($task_progress['completed_tasks'] / $task_progress['total_tasks']) * 100));
        }
        
        // Get recent mood entry
        $mood_stmt = $conn->prepare("
            SELECT mood, mood_score, note, entry_date 
            FROM mood_entries 
            WHERE user_id = ? 
            ORDER BY entry_date DESC, created_at DESC 
            LIMIT 1
        ");
        $mood_stmt->bind_param("i", $user_id);
        $mood_stmt->execute();
        $mood_result = $mood_stmt->get_result();
        $recent_mood = $mood_result->num_rows > 0 ? $mood_result->fetch_assoc() : null;
        
        // Get recent journal entries (last 3)
        $journal_stmt = $conn->prepare("
            SELECT id, title, content, mood, created_at 
            FROM journal_entries 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 3
        ");
        $journal_stmt->bind_param("i", $user_id);
        $journal_stmt->execute();
        $journal_result = $journal_stmt->get_result();
        $journal_entries = [];
        while ($entry = $journal_result->fetch_assoc()) {
            $journal_entries[] = $entry;
        }
        
        // Get journal entries count (total)
        $journal_count_stmt = $conn->prepare("
            SELECT COUNT(*) as journal_count 
            FROM journal_entries 
            WHERE user_id = ?
        ");
        $journal_count_stmt->bind_param("i", $user_id);
        $journal_count_stmt->execute();
        $journal_count = $journal_count_stmt->get_result()->fetch_assoc()['journal_count'];
        
        // Get today's tasks
        $tasks_stmt = $conn->prepare("
            SELECT t.id, t.task_name as title, t.task_description as description,
                   CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed_today
            FROM program_tasks pt
            JOIN tasks t ON pt.task_id = t.id
            LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ? AND utc.completion_date = ?)
            WHERE pt.program_id = ? AND t.is_active = 1
            ORDER BY pt.order_sequence
        ");
        $tasks_stmt->bind_param("isi", $user_id, $today, $user['assigned_program_id']);
        $tasks_stmt->execute();
        $tasks_result = $tasks_stmt->get_result();
        $tasks = [];
        while ($task = $tasks_result->fetch_assoc()) {
            $tasks[] = $task;
        }
        
        // Calculate program days completed
        $program_days_stmt = $conn->prepare("
            SELECT COUNT(DISTINCT completion_date) as days_completed
            FROM user_task_completions
            WHERE user_id = ? AND program_id = ?
        ");
        $program_days_stmt->bind_param("ii", $user_id, $user['assigned_program_id']);
        $program_days_stmt->execute();
        $program_days = $program_days_stmt->get_result()->fetch_assoc()['days_completed'];
        
        // Calculate current streak properly
        $current_streak = calculateCurrentStreak($conn, $user_id);
        
        // Update the user's streak in the database
        $update_streak_stmt = $conn->prepare("UPDATE users SET current_streak = ? WHERE id = ?");
        $update_streak_stmt->bind_param("ii", $current_streak, $user_id);
        $update_streak_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $user_id,
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'wellness_score' => $user['wellness_score'],
                'current_streak' => $user['current_streak'],
                'highest_streak' => $user['highest_streak'],
                'program' => [
                    'name' => $user['program_name'] ?: 'Mindfulness & Stress Relief',
                    'description' => $user['program_description'] ?: 'Learn evidence-based techniques to manage stress and build mindfulness.',
                    'icon' => $user['icon'] ?: '🧘',
                    'color' => $user['color'] ?: '#8b7355'
                ]
            ],
            'stats' => [
                'current_streak' => $current_streak, // Use calculated streak instead of database value
                'highest_streak' => $user['highest_streak'],
                'wellness_score' => $user['wellness_score'],
                'program_days_completed' => $program_days,
                'program_duration' => 30, // Default program duration
                'journal_count' => $journal_count,
                'tasks_completed_today' => $task_progress['completed_tasks'],
                'total_tasks_today' => $task_progress['total_tasks'],
                'completion_percentage' => $completion_percentage
            ],
            'todays_mood' => $recent_mood,
            'journal_entries' => $journal_entries,
            'tasks' => $tasks
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading dashboard data: ' . $e->getMessage()]);
    }
}

function getUserStats($conn, $user_id) {
    try {
        // Get user statistics
        $stats_stmt = $conn->prepare("
            SELECT 
                u.current_streak,
                u.highest_streak,
                u.wellness_score,
                COUNT(DISTINCT utc.completion_date) as total_active_days,
                COUNT(utc.id) as total_completed_tasks
            FROM users u
            LEFT JOIN user_task_completions utc ON u.id = utc.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stats_stmt->bind_param("i", $user_id);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading user stats: ' . $e->getMessage()]);
    }
}

function getRecentActivities($conn, $user_id) {
    try {
        // Get recent activities (tasks completed, mood entries, journal entries)
        $activities = [];
        
        // Recent task completions
        $tasks_stmt = $conn->prepare("
            SELECT 'task' as type, t.task_name as title, utc.completed_at as timestamp
            FROM user_task_completions utc
            JOIN tasks t ON utc.task_id = t.id
            WHERE utc.user_id = ?
            ORDER BY utc.completed_at DESC
            LIMIT 5
        ");
        $tasks_stmt->bind_param("i", $user_id);
        $tasks_stmt->execute();
        $tasks_result = $tasks_stmt->get_result();
        while ($task = $tasks_result->fetch_assoc()) {
            $activities[] = $task;
        }
        
        // Recent mood entries
        $mood_stmt = $conn->prepare("
            SELECT 'mood' as type, CONCAT('Mood: ', mood) as title, created_at as timestamp
            FROM mood_entries
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 3
        ");
        $mood_stmt->bind_param("i", $user_id);
        $mood_stmt->execute();
        $mood_result = $mood_stmt->get_result();
        while ($mood = $mood_result->fetch_assoc()) {
            $activities[] = $mood;
        }
        
        // Recent journal entries
        $journal_stmt = $conn->prepare("
            SELECT 'journal' as type, title, created_at as timestamp
            FROM journal_entries
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 3
        ");
        $journal_stmt->bind_param("i", $user_id);
        $journal_stmt->execute();
        $journal_result = $journal_stmt->get_result();
        while ($journal = $journal_result->fetch_assoc()) {
            $activities[] = $journal;
        }
        
        // Sort all activities by timestamp
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        echo json_encode([
            'success' => true,
            'activities' => array_slice($activities, 0, 10)
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading recent activities: ' . $e->getMessage()]);
    }
}

function updateProgress($conn, $user_id, $data) {
    try {
        // Update user's daily progress
        $today = date('Y-m-d');
        
        // Get current task completion count
        $task_count_stmt = $conn->prepare("
            SELECT COUNT(*) as completed_tasks
            FROM user_task_completions
            WHERE user_id = ? AND completion_date = ?
        ");
        $task_count_stmt->bind_param("is", $user_id, $today);
        $task_count_stmt->execute();
        $completed_tasks = $task_count_stmt->get_result()->fetch_assoc()['completed_tasks'];
        
        // Update or insert daily progress
        $progress_stmt = $conn->prepare("
            INSERT INTO daily_progress (user_id, progress_date, tasks_completed, completion_percentage)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            tasks_completed = VALUES(tasks_completed),
            completion_percentage = VALUES(completion_percentage)
        ");
        
        $completion_percentage = min(100, ($completed_tasks / 3) * 100); // Assuming 3 tasks per day
        $progress_stmt->bind_param("isid", $user_id, $today, $completed_tasks, $completion_percentage);
        
        if ($progress_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Progress updated',
                'completed_tasks' => $completed_tasks,
                'completion_percentage' => $completion_percentage
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating progress: ' . $e->getMessage()]);
    }
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Add the same personalized task analysis functions from tasks.php
function analyzeAssessmentAndAssignTasks($answers, $wellness_score) {
    // Use the actual tasks from the database instead of hardcoded IDs
    $taskLibrary = [
        'emotional_wellbeing' => [
            [
                'id' => 1, // Morning Meditation - exists in DB
                'title' => 'Morning Meditation',
                'description' => '10 minutes of guided mindfulness meditation',
                'category' => 'Mindfulness',
                'duration' => 10
            ],
            [
                'id' => 2, // Breathing Exercise - exists in DB
                'title' => 'Breathing Exercise',
                'description' => '5-minute deep breathing for stress relief',
                'category' => 'Mindfulness',
                'duration' => 5
            ]
        ],
        'anxiety_stress' => [
            [
                'id' => 2, // Breathing Exercise - exists in DB
                'title' => 'Deep Breathing Exercise',
                'description' => '4-7-8 breathing technique: Inhale 4, hold 7, exhale 8',
                'category' => 'Anxiety & Stress',
                'duration' => 5
            ],
            [
                'id' => 3, // Stress Level Check - exists in DB
                'title' => 'Stress Level Check',
                'description' => 'Rate and reflect on your stress level (1-10)',
                'category' => 'Reflection',
                'duration' => 3
            ]
        ],
        'sleep_rest' => [
            [
                'id' => 1, // Morning Meditation can help with sleep
                'title' => 'Evening Meditation',
                'description' => '10 minutes of calming meditation before bed',
                'category' => 'Sleep & Rest',
                'duration' => 10
            ],
            [
                'id' => 2, // Breathing Exercise
                'title' => 'Bedtime Breathing',
                'description' => '5-minute deep breathing for better sleep',
                'category' => 'Sleep & Rest',
                'duration' => 5
            ]
        ],
        'social_connection' => [
            [
                'id' => 3, // Stress Level Check can include social reflection
                'title' => 'Social Connection Check',
                'description' => 'Reflect on your social connections and reach out to someone',
                'category' => 'Social Connection',
                'duration' => 10
            ],
            [
                'id' => 1, // Meditation can help with social anxiety
                'title' => 'Social Confidence Meditation',
                'description' => '10 minutes of meditation focused on social confidence',
                'category' => 'Social Connection',
                'duration' => 10
            ]
        ],
        'self_care' => [
            [
                'id' => 1, // Morning Meditation
                'title' => 'Self-Care Meditation',
                'description' => '10 minutes of self-compassion meditation',
                'category' => 'Self-Care',
                'duration' => 10
            ],
            [
                'id' => 2, // Breathing Exercise
                'title' => 'Mindful Breathing',
                'description' => '5 minutes of mindful breathing for self-care',
                'category' => 'Self-Care',
                'duration' => 5
            ]
        ],
        'mindfulness' => [
            [
                'id' => 1,
                'title' => 'Morning Meditation',
                'description' => '10 minutes of guided mindfulness meditation',
                'category' => 'Mindfulness',
                'duration' => 10
            ],
            [
                'id' => 2,
                'title' => 'Breathing Exercise',
                'description' => '5-minute deep breathing for stress relief',
                'category' => 'Mindfulness',
                'duration' => 5
            ],
            [
                'id' => 3,
                'title' => 'Stress Level Check',
                'description' => 'Rate and reflect on your stress level (1-10)',
                'category' => 'Reflection',
                'duration' => 3
            ]
        ]
    ];
    
    // Analyze assessment answers by category
    $categoryScores = analyzeAssessmentByCategory($answers);
    
    // Select 3-4 tasks based on highest need areas
    $selectedTasks = [];
    $usedTaskIds = [];
    
    // Sort categories by score (highest score = most need)
    arsort($categoryScores);
    
    $taskCount = 0;
    $maxTasks = 4;
    
    foreach ($categoryScores as $category => $score) {
        if ($taskCount >= $maxTasks) break;
        
        // Select 1-2 tasks from high-need categories
        if (isset($taskLibrary[$category])) {
            $categoryTasks = $taskLibrary[$category];
            
            // For high-need categories (score > 15), try to add 2 tasks
            // For moderate-need categories, add 1 task
            $tasksToAdd = ($score > 15) ? 2 : 1;
            
            for ($i = 0; $i < $tasksToAdd && $taskCount < $maxTasks && $i < count($categoryTasks); $i++) {
                $task = $categoryTasks[$i];
                
                // Avoid duplicate task IDs
                if (!in_array($task['id'], $usedTaskIds)) {
                    $selectedTasks[] = $task;
                    $usedTaskIds[] = $task['id'];
                    $taskCount++;
                }
            }
        }
    }
    
    // If we don't have enough tasks, add default mindfulness tasks
    while ($taskCount < 3 && isset($taskLibrary['mindfulness'])) {
        foreach ($taskLibrary['mindfulness'] as $task) {
            if ($taskCount >= 3) break;
            
            // Check if task is already selected
            if (!in_array($task['id'], $usedTaskIds)) {
                $selectedTasks[] = $task;
                $usedTaskIds[] = $task['id'];
                $taskCount++;
            }
        }
        break;
    }
    
    return array_slice($selectedTasks, 0, $maxTasks); // Ensure max 4 tasks
}

function analyzeAssessmentByCategory($answers) {
    // Map assessment questions to categories
    // Questions 0-2: Emotional Wellbeing
    // Questions 3-5: Anxiety & Stress  
    // Questions 6-8: Sleep & Rest
    // Questions 9-11: Social Connection
    // Questions 12-13: Self-Care & Mindfulness
    
    $categoryScores = [
        'emotional_wellbeing' => 0,
        'anxiety_stress' => 0,
        'sleep_rest' => 0,
        'social_connection' => 0,
        'self_care' => 0,
        'mindfulness' => 0
    ];
    
    if (!is_array($answers) || empty($answers)) {
        // Default to mindfulness if no answers
        $categoryScores['mindfulness'] = 10;
        return $categoryScores;
    }
    
    // Emotional Wellbeing (questions 0-2)
    for ($i = 0; $i <= 2 && $i < count($answers); $i++) {
        $categoryScores['emotional_wellbeing'] += $answers[$i];
    }
    
    // Anxiety & Stress (questions 3-5)
    for ($i = 3; $i <= 5 && $i < count($answers); $i++) {
        $categoryScores['anxiety_stress'] += $answers[$i];
    }
    
    // Sleep & Rest (questions 6-8)
    for ($i = 6; $i <= 8 && $i < count($answers); $i++) {
        $categoryScores['sleep_rest'] += $answers[$i];
    }
    
    // Social Connection (questions 9-11)
    for ($i = 9; $i <= 11 && $i < count($answers); $i++) {
        $categoryScores['social_connection'] += $answers[$i];
    }
    
    // Self-Care (questions 12-13)
    for ($i = 12; $i <= 13 && $i < count($answers); $i++) {
        $categoryScores['self_care'] += $answers[$i];
    }
    
    // Add base mindfulness score
    $categoryScores['mindfulness'] = 8;
    
    return $categoryScores;
}

// Function to calculate current streak based on consecutive days of activity
function calculateCurrentStreak($conn, $user_id) {
    $current_streak = 0;
    $today = date('Y-m-d');
    
    // Get all completion dates for this user, ordered by date descending
    $stmt = $conn->prepare("
        SELECT DISTINCT completion_date 
        FROM user_task_completions 
        WHERE user_id = ? 
        ORDER BY completion_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $completion_dates = [];
    while ($row = $result->fetch_assoc()) {
        $completion_dates[] = $row['completion_date'];
    }
    
    if (empty($completion_dates)) {
        return 0; // No activity yet
    }
    
    // Check if user has activity today or yesterday (to account for different time zones)
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $most_recent_date = $completion_dates[0];
    
    // If the most recent activity is not today or yesterday, streak is 0
    if ($most_recent_date !== $today && $most_recent_date !== $yesterday) {
        return 0;
    }
    
    // Start counting streak from the most recent date
    $current_date = $most_recent_date;
    $current_streak = 1; // Count the most recent day
    
    // Count consecutive days backwards
    for ($i = 1; $i < count($completion_dates); $i++) {
        $previous_date = $completion_dates[$i];
        $expected_previous_date = date('Y-m-d', strtotime($current_date . ' -1 day'));
        
        if ($previous_date === $expected_previous_date) {
            $current_streak++;
            $current_date = $previous_date;
        } else {
            // Gap found, streak ends
            break;
        }
    }
    
    return $current_streak;
}
?>