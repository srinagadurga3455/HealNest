<?php
session_start();
header('Content-Type: application/json');

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
    case 'get_programs':
        getPrograms();
        break;
    case 'get_program_details':
        getProgramDetails();
        break;
    case 'enroll_program':
        enrollProgram();
        break;
    case 'get_enrolled_programs':
        getEnrolledPrograms();
        break;
    case 'get_program_progress':
        getProgramProgress();
        break;
    case 'update_program_progress':
        updateProgramProgress();
        break;
    case 'get_program_tasks':
        getProgramTasks();
        break;
    case 'complete_program_task':
        completeProgramTask();
        break;
    case 'get_recommended_programs':
        getRecommendedPrograms();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getPrograms() {
    global $conn, $user_id;
    
    $filter = $_GET['filter'] ?? 'all';
    
    $sql = "SELECT p.*, 
                   CASE WHEN u.assigned_program_id = p.id THEN 1 ELSE 0 END as is_enrolled,
                   COALESCE(up.progress_percentage, 0) as progress_percentage
            FROM programs p
            LEFT JOIN users u ON u.id = ? AND u.assigned_program_id = p.id
            LEFT JOIN (
                SELECT program_id, 
                       AVG(CASE WHEN utc.id IS NOT NULL THEN 100 ELSE 0 END) as progress_percentage
                FROM program_tasks pt
                LEFT JOIN user_task_completions utc ON pt.task_id = utc.task_id AND utc.user_id = ?
                GROUP BY program_id
            ) up ON up.program_id = p.id
            WHERE p.is_active = 1";
    
    $params = [$user_id, $user_id];
    $types = "ii";
    
    // Apply filters
    if ($filter === 'enrolled') {
        $sql .= " AND u.assigned_program_id = p.id";
    } elseif ($filter === 'recommended') {
        // Get recommended based on assessment
        $assessment_stmt = $conn->prepare("SELECT wellness_score FROM users WHERE id = ?");
        $assessment_stmt->bind_param("i", $user_id);
        $assessment_stmt->execute();
        $assessment_result = $assessment_stmt->get_result();
        $user_data = $assessment_result->fetch_assoc();
        
        if ($user_data && $user_data['wellness_score'] < 60) {
            $sql .= " AND p.difficulty_level IN ('beginner', 'intermediate')";
        } else {
            $sql .= " AND p.difficulty_level IN ('intermediate', 'advanced')";
        }
    } elseif (in_array($filter, ['beginner', 'intermediate', 'advanced'])) {
        $sql .= " AND p.difficulty_level = ?";
        $params[] = $filter;
        $types .= "s";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        // Get task count for this program
        $task_stmt = $conn->prepare("SELECT COUNT(*) as task_count FROM program_tasks WHERE program_id = ?");
        $task_stmt->bind_param("i", $row['id']);
        $task_stmt->execute();
        $task_result = $task_stmt->get_result();
        $task_data = $task_result->fetch_assoc();
        
        // Get participant count (users enrolled in this program)
        $participant_stmt = $conn->prepare("SELECT COUNT(*) as participant_count FROM users WHERE assigned_program_id = ?");
        $participant_stmt->bind_param("i", $row['id']);
        $participant_stmt->execute();
        $participant_result = $participant_stmt->get_result();
        $participant_data = $participant_result->fetch_assoc();
        
        $programs[] = [
            'id' => $row['id'],
            'title' => $row['program_name'],
            'subtitle' => $row['program_goal'] ?? 'Improve your wellness',
            'description' => $row['program_description'],
            'icon' => $row['icon'] ?? '🌟',
            'color' => $row['color'] ?? '#5D87FF',
            'colorLight' => adjustColorBrightness($row['color'] ?? '#5D87FF', 0.2),
            'category' => $row['difficulty_level'],
            'duration' => $row['duration_days'] . ' days',
            'lessons' => $task_data['task_count'] ?? 0,
            'participants' => $participant_data['participant_count'] ?? 0,
            'rating' => 4.5 + (rand(1, 8) / 10), // Simulated rating
            'is_enrolled' => (bool)$row['is_enrolled'],
            'progress' => round($row['progress_percentage'] ?? 0),
            'features' => getProgramFeatures($row['category'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'programs' => $programs
    ]);
}

function getProgramDetails() {
    global $conn, $user_id;
    
    $program_id = $_GET['program_id'] ?? 0;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    // Get program details
    $stmt = $conn->prepare("
        SELECT p.*, 
               CASE WHEN u.assigned_program_id = p.id THEN 1 ELSE 0 END as is_enrolled,
               u.program_start_date
        FROM programs p
        LEFT JOIN users u ON u.id = ? AND u.assigned_program_id = p.id
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->bind_param("ii", $user_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Program not found']);
        return;
    }
    
    $program = $result->fetch_assoc();
    
    // Get program tasks with completion status
    $task_stmt = $conn->prepare("
        SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
               CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ?)
        WHERE pt.program_id = ? AND t.is_active = 1
        ORDER BY pt.order_sequence
    ");
    $task_stmt->bind_param("ii", $user_id, $program_id);
    $task_stmt->execute();
    $task_result = $task_stmt->get_result();
    
    $tasks = [];
    $completed_tasks = 0;
    $total_tasks = 0;
    
    while ($task_row = $task_result->fetch_assoc()) {
        $total_tasks++;
        if ($task_row['completed']) {
            $completed_tasks++;
        }
        
        $status = 'locked';
        if ($program['is_enrolled']) {
            if ($task_row['completed']) {
                $status = 'completed';
            } elseif ($completed_tasks >= $total_tasks - 1) {
                $status = 'current';
            } else {
                $status = 'available';
            }
        }
        
        $tasks[] = [
            'id' => $task_row['id'],
            'title' => $task_row['task_name'],
            'duration' => $task_row['estimated_duration'] . ' min',
            'status' => $status,
            'description' => $task_row['task_description'],
            'type' => $task_row['task_type'],
            'difficulty' => $task_row['difficulty_level'],
            'day_number' => $task_row['day_number'],
            'is_required' => (bool)$task_row['is_required'],
            'completed' => (bool)$task_row['completed']
        ];
    }
    
    // Calculate progress
    $progress = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
    
    // Get participant count
    $participant_stmt = $conn->prepare("SELECT COUNT(*) as participant_count FROM users WHERE assigned_program_id = ?");
    $participant_stmt->bind_param("i", $program_id);
    $participant_stmt->execute();
    $participant_result = $participant_stmt->get_result();
    $participant_data = $participant_result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'program' => [
            'id' => $program['id'],
            'title' => $program['program_name'],
            'subtitle' => $program['program_goal'] ?? 'Improve your wellness',
            'description' => $program['program_description'],
            'icon' => $program['icon'] ?? '🌟',
            'color' => $program['color'] ?? '#5D87FF',
            'colorLight' => adjustColorBrightness($program['color'] ?? '#5D87FF', 0.2),
            'category' => $program['difficulty_level'],
            'duration' => $program['duration_days'] . ' days',
            'lessons' => $total_tasks,
            'participants' => $participant_data['participant_count'] ?? 0,
            'rating' => 4.5 + (rand(1, 8) / 10),
            'is_enrolled' => (bool)$program['is_enrolled'],
            'progress' => $progress,
            'features' => getProgramFeatures($program['category']),
            'tasks' => $tasks,
            'start_date' => $program['program_start_date']
        ]
    ]);
}

function enrollProgram() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $program_id = $data['program_id'] ?? 0;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    // Check if program exists and is active
    $stmt = $conn->prepare("SELECT id, program_name FROM programs WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Program not found or inactive']);
        return;
    }
    
    $program = $result->fetch_assoc();
    
    // Check if user is already enrolled in this program
    $stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if ($user_data['assigned_program_id'] == $program_id) {
        echo json_encode(['success' => false, 'message' => 'Already enrolled in this program']);
        return;
    }
    
    // Enroll user in program
    $today = date('Y-m-d');
    $stmt = $conn->prepare("UPDATE users SET assigned_program_id = ?, program_start_date = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("isi", $program_id, $today, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Successfully enrolled in ' . $program['program_name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to enroll in program']);
    }
}

function getEnrolledPrograms() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("
        SELECT p.*, u.program_start_date,
               COALESCE(up.progress_percentage, 0) as progress_percentage
        FROM users u
        JOIN programs p ON u.assigned_program_id = p.id
        LEFT JOIN (
            SELECT program_id, 
                   AVG(CASE WHEN utc.id IS NOT NULL THEN 100 ELSE 0 END) as progress_percentage
            FROM program_tasks pt
            LEFT JOIN user_task_completions utc ON pt.task_id = utc.task_id AND utc.user_id = ?
            GROUP BY program_id
        ) up ON up.program_id = p.id
        WHERE u.id = ? AND p.is_active = 1
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = [
            'id' => $row['id'],
            'title' => $row['program_name'],
            'description' => $row['program_description'],
            'icon' => $row['icon'] ?? '🌟',
            'color' => $row['color'] ?? '#5D87FF',
            'progress' => round($row['progress_percentage'] ?? 0),
            'start_date' => $row['program_start_date'],
            'duration' => $row['duration_days'] . ' days'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'programs' => $programs
    ]);
}

function getProgramProgress() {
    global $conn, $user_id;
    
    $program_id = $_GET['program_id'] ?? 0;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    // Get program tasks with completion status
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_tasks,
               SUM(CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END) as completed_tasks
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ?)
        WHERE pt.program_id = ? AND t.is_active = 1
    ");
    $stmt->bind_param("ii", $user_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $progress_data = $result->fetch_assoc();
    
    $total_tasks = $progress_data['total_tasks'] ?? 0;
    $completed_tasks = $progress_data['completed_tasks'] ?? 0;
    $progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
    
    echo json_encode([
        'success' => true,
        'progress' => [
            'program_id' => $program_id,
            'total_tasks' => $total_tasks,
            'completed_tasks' => $completed_tasks,
            'progress_percentage' => $progress_percentage
        ]
    ]);
}

function updateProgramProgress() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $program_id = $data['program_id'] ?? 0;
    $progress_increment = $data['progress_increment'] ?? 20;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    // Simulate progress by completing next available task
    $stmt = $conn->prepare("
        SELECT t.id as task_id
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ?)
        WHERE pt.program_id = ? AND t.is_active = 1 AND utc.id IS NULL
        ORDER BY pt.order_sequence
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $task_data = $result->fetch_assoc();
        $task_id = $task_data['task_id'];
        
        // Complete the task
        $today = date('Y-m-d');
        $stmt = $conn->prepare("INSERT INTO user_task_completions (user_id, task_id, program_id, completion_date, completed_at, notes) VALUES (?, ?, ?, ?, NOW(), 'Auto-completed via progress update')");
        $stmt->bind_param("iiis", $user_id, $task_id, $program_id, $today);
        $stmt->execute();
        
        // Get updated progress
        $progress_stmt = $conn->prepare("
            SELECT COUNT(*) as total_tasks,
                   SUM(CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END) as completed_tasks
            FROM program_tasks pt
            JOIN tasks t ON pt.task_id = t.id
            LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ?)
            WHERE pt.program_id = ? AND t.is_active = 1
        ");
        $progress_stmt->bind_param("ii", $user_id, $program_id);
        $progress_stmt->execute();
        $progress_result = $progress_stmt->get_result();
        $progress_data = $progress_result->fetch_assoc();
        
        $total_tasks = $progress_data['total_tasks'] ?? 0;
        $completed_tasks = $progress_data['completed_tasks'] ?? 0;
        $progress_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
        
        echo json_encode([
            'success' => true,
            'message' => 'Progress updated successfully',
            'progress' => $progress_percentage,
            'completed' => $progress_percentage >= 100
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No more tasks to complete or program already finished'
        ]);
    }
}

function getProgramTasks() {
    global $conn, $user_id;
    
    $program_id = $_GET['program_id'] ?? 0;
    
    if (!$program_id) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
               CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed,
               utc.completion_date, utc.notes
        FROM program_tasks pt
        JOIN tasks t ON pt.task_id = t.id
        LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = ?)
        WHERE pt.program_id = ? AND t.is_active = 1
        ORDER BY pt.order_sequence
    ");
    $stmt->bind_param("ii", $user_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => $row['id'],
            'title' => $row['task_name'],
            'description' => $row['task_description'],
            'type' => $row['task_type'],
            'category' => $row['category'],
            'estimated_duration' => $row['estimated_duration'],
            'difficulty_level' => $row['difficulty_level'],
            'day_number' => $row['day_number'],
            'is_required' => (bool)$row['is_required'],
            'order_sequence' => $row['order_sequence'],
            'completed' => (bool)$row['completed'],
            'completion_date' => $row['completion_date'],
            'notes' => $row['notes']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
}

function completeProgramTask() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $task_id = $data['task_id'] ?? 0;
    $program_id = $data['program_id'] ?? 0;
    $notes = $data['notes'] ?? '';
    
    if (!$task_id || !$program_id) {
        echo json_encode(['success' => false, 'message' => 'Task ID and Program ID are required']);
        return;
    }
    
    $today = date('Y-m-d');
    
    // Check if already completed
    $stmt = $conn->prepare("SELECT id FROM user_task_completions WHERE user_id = ? AND task_id = ? AND program_id = ?");
    $stmt->bind_param("iii", $user_id, $task_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Task already completed']);
        return;
    }
    
    // Complete the task
    $stmt = $conn->prepare("INSERT INTO user_task_completions (user_id, task_id, program_id, completion_date, completed_at, notes) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iiiss", $user_id, $task_id, $program_id, $today, $notes);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task completed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to complete task']);
    }
}

function getRecommendedPrograms() {
    global $conn, $user_id;
    
    // Get user's assessment data
    $stmt = $conn->prepare("SELECT wellness_score FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    $wellness_score = $user_data['wellness_score'] ?? 50;
    
    // Recommend programs based on wellness score
    $difficulty_filter = '';
    if ($wellness_score < 40) {
        $difficulty_filter = "AND p.difficulty_level = 'beginner'";
    } elseif ($wellness_score < 70) {
        $difficulty_filter = "AND p.difficulty_level IN ('beginner', 'intermediate')";
    } else {
        $difficulty_filter = "AND p.difficulty_level IN ('intermediate', 'advanced')";
    }
    
    $stmt = $conn->prepare("
        SELECT p.*, 
               CASE WHEN u.assigned_program_id = p.id THEN 1 ELSE 0 END as is_enrolled
        FROM programs p
        LEFT JOIN users u ON u.id = ? AND u.assigned_program_id = p.id
        WHERE p.is_active = 1 $difficulty_filter
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = [
            'id' => $row['id'],
            'title' => $row['program_name'],
            'subtitle' => $row['program_goal'] ?? 'Improve your wellness',
            'description' => $row['program_description'],
            'icon' => $row['icon'] ?? '🌟',
            'color' => $row['color'] ?? '#5D87FF',
            'category' => $row['difficulty_level'],
            'duration' => $row['duration_days'] . ' days',
            'is_enrolled' => (bool)$row['is_enrolled']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'programs' => $programs,
        'recommendation_reason' => getRecommendationReason($wellness_score)
    ]);
}

// Helper functions
function adjustColorBrightness($hex, $percent) {
    // Remove # if present
    $hex = str_replace('#', '', $hex);
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = min(255, max(0, $r + ($r * $percent)));
    $g = min(255, max(0, $g + ($g * $percent)));
    $b = min(255, max(0, $b + ($b * $percent)));
    
    // Convert back to hex
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
                 str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
                 str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

function getProgramFeatures($category) {
    $features = [
        'Mental' => [
            'Stress reduction techniques',
            'Mindfulness exercises',
            'Emotional regulation',
            'Mental clarity improvement',
            'Anxiety management'
        ],
        'Physical' => [
            'Exercise routines',
            'Nutrition guidance',
            'Sleep optimization',
            'Energy boosting',
            'Health tracking'
        ],
        'Social' => [
            'Communication skills',
            'Relationship building',
            'Community support',
            'Social confidence',
            'Empathy development'
        ]
    ];
    
    return $features[$category] ?? [
        'Personal development',
        'Goal achievement',
        'Progress tracking',
        'Expert guidance',
        'Community support'
    ];
}

function getRecommendationReason($wellness_score) {
    if ($wellness_score < 40) {
        return "Based on your wellness assessment, we recommend starting with beginner-friendly programs to build a strong foundation.";
    } elseif ($wellness_score < 70) {
        return "Your wellness score suggests you're ready for intermediate challenges while still benefiting from foundational programs.";
    } else {
        return "Great wellness score! You're ready for advanced programs that will challenge and further develop your skills.";
    }
}

$conn->close();
?>