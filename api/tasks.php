<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

session_start();

// Clear all output buffers
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

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
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Not authenticated', 'debug' => 'No user_id in session']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

    // Clear any output before processing
    ob_clean();

    switch ($action) {
        case 'test':
            echo json_encode([
                'success' => true, 
                'message' => 'API is working',
                'user_id' => $user_id,
                'session' => $_SESSION,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
        case 'get_user_tasks':
            getUserTasks($conn, $user_id);
            break;
        case 'get_personalized_tasks':
            getPersonalizedTasks($conn, $user_id);
            break;
        case 'complete_task':
            completeTask($conn, $user_id, $input);
            break;
        case 'get_task_progress':
            getTaskProgress($conn, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action, 'received_action' => $action]);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getPersonalizedTasks($conn, $user_id) {
    try {
        // Get user's latest assessment answers
        $assessment_stmt = $conn->prepare("SELECT answers, wellness_score FROM assessments WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $assessment_stmt->bind_param("i", $user_id);
        $assessment_stmt->execute();
        $assessment_result = $assessment_stmt->get_result();
        
        if ($assessment_result->num_rows === 0) {
            // No assessment found, return default tasks
            echo json_encode([
                'success' => true,
                'tasks' => getDefaultTasks(),
                'message' => 'Using default tasks - no assessment found',
                'user_id' => $user_id
            ]);
            return;
        }
        
        $assessment = $assessment_result->fetch_assoc();
        $answers = json_decode($assessment['answers'], true);
        $wellness_score = $assessment['wellness_score'];
        
        // Analyze answers and assign personalized tasks (3-4 tasks max)
        $personalizedTasks = analyzeAssessmentAndAssignTasks($answers, $wellness_score);
        
        // Get today's completed tasks
        $today = date('Y-m-d');
        $completed_stmt = $conn->prepare("SELECT task_id FROM user_task_completions WHERE user_id = ? AND completion_date = ?");
        $completed_stmt->bind_param("is", $user_id, $today);
        $completed_stmt->execute();
        $completed_result = $completed_stmt->get_result();
        
        $completedTaskIds = [];
        while ($row = $completed_result->fetch_assoc()) {
            $completedTaskIds[] = $row['task_id'];
        }
        
        // Mark tasks as completed
        foreach ($personalizedTasks as &$task) {
            $task['completed_today'] = in_array($task['id'], $completedTaskIds);
            $task['completed'] = $task['completed_today']; // For backward compatibility
        }
        
        echo json_encode([
            'success' => true,
            'tasks' => $personalizedTasks,
            'wellness_score' => $wellness_score,
            'message' => 'Personalized tasks based on assessment',
            'user_id' => $user_id,
            'completed_task_ids' => $completedTaskIds
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

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

function getDefaultTasks() {
    return [
        [
            'id' => 1,
            'title' => 'Morning Meditation',
            'description' => '10 minutes of guided mindfulness meditation',
            'category' => 'Mindfulness',
            'duration' => 10,
            'completed_today' => false
        ],
        [
            'id' => 2,
            'title' => 'Breathing Exercise',
            'description' => '5-minute deep breathing for stress relief',
            'category' => 'Mindfulness',
            'duration' => 5,
            'completed_today' => false
        ],
        [
            'id' => 3,
            'title' => 'Stress Level Check',
            'description' => 'Rate and reflect on your stress level (1-10)',
            'category' => 'Reflection',
            'duration' => 3,
            'completed_today' => false
        ]
    ];
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
        
        // Verify that the task exists in the tasks table
        $check_stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND is_active = 1");
        $check_stmt->bind_param("i", $task_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Task not found or inactive']);
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
                'message' => $completed ? 'Task completed!' : 'Task marked as incomplete',
                'task_id' => $task_id,
                'completed' => $completed,
                'user_id' => $user_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update task status: ' . $stmt->error
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
        // First try to get progress from personalized tasks
        $today = date('Y-m-d');
        
        // Get user's latest assessment to determine personalized tasks
        $assessment_stmt = $conn->prepare("SELECT answers, wellness_score FROM assessments WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $assessment_stmt->bind_param("i", $user_id);
        $assessment_stmt->execute();
        $assessment_result = $assessment_stmt->get_result();
        
        if ($assessment_result->num_rows > 0) {
            // User has assessment, use personalized tasks
            $assessment = $assessment_result->fetch_assoc();
            $answers = json_decode($assessment['answers'], true);
            $wellness_score = $assessment['wellness_score'];
            
            // Get the same personalized tasks that would be assigned
            $personalizedTasks = analyzeAssessmentAndAssignTasks($answers, $wellness_score);
            $total_tasks = count($personalizedTasks);
            
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
                $completed_tasks = $completed_result['completed'];
            } else {
                $completed_tasks = 0;
            }
        } else {
            // No assessment, fall back to program-based tasks
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
        }
        
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