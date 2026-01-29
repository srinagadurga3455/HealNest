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

// Handle JSON input
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['action'])) {
    $action = $input['action'];
}

switch ($action) {
    case 'submit_assessment':
        submitAssessment();
        break;
    case 'get_assessment_results':
        getAssessmentResults();
        break;
    case 'get_assessment_history':
        getAssessmentHistory();
        break;
    case 'reset_assessment':
        resetAssessment();
        break;
    default:
        // If no action provided, return available actions
        if (empty($action)) {
            echo json_encode([
                'success' => false, 
                'message' => 'No action specified',
                'available_actions' => ['submit_assessment', 'get_assessment_results', 'get_assessment_history', 'reset_assessment'],
                'usage' => 'Send POST request with action parameter in JSON body or as query parameter'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid action: ' . $action,
                'available_actions' => ['submit_assessment', 'get_assessment_results', 'get_assessment_history', 'reset_assessment']
            ]);
        }
}

function submitAssessment() {
    global $conn, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['answers']) || !is_array($data['answers'])) {
        echo json_encode(['success' => false, 'message' => 'Assessment answers are required']);
        return;
    }
    
    $answers = $data['answers'];
    
    // Calculate wellness score (0-100, higher is better)
    $total_score = array_sum($answers);
    $answer_count = count($answers);
    $max_possible_score = $answer_count * 3;
    $wellness_score = round(((($max_possible_score - $total_score) / $max_possible_score) * 100), 0);
    
    // Generate recommendations
    $recommendations = generateRecommendations($wellness_score, $answers);
    
    // Get recommended program
    $recommended_program_id = getRecommendedProgram($answers);
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Save assessment
        $stmt = $conn->prepare("INSERT INTO assessments (user_id, wellness_score, answers, recommendations, recommended_program_id, completed_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iissi", $user_id, $wellness_score, json_encode($answers), json_encode($recommendations), $recommended_program_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save assessment");
        }
        
        // Update user with assessment results and program assignment
        $stmt = $conn->prepare("UPDATE users SET assessment_taken = 1, wellness_score = ?, assessment_date = NOW(), assigned_program_id = ?, program_start_date = CURDATE(), updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iii", $wellness_score, $recommended_program_id, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user");
        }
        
        // Initialize daily progress for the user
        $stmt = $conn->prepare("INSERT INTO daily_progress (user_id, progress_date, total_tasks_assigned, tasks_completed, completion_percentage, streak_day) VALUES (?, CURDATE(), 0, 0, 0, 0) ON DUPLICATE KEY UPDATE progress_date = CURDATE()");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Get program details for response
        $program_details = getProgramDetails($recommended_program_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment completed successfully',
            'results' => [
                'wellness_score' => $wellness_score,
                'recommendations' => $recommendations,
                'recommended_program' => $program_details
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to save assessment: ' . $e->getMessage()]);
    }
}

function getAssessmentResults() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("SELECT a.*, p.program_name, p.program_description FROM assessments a LEFT JOIN programs p ON a.recommended_program_id = p.id WHERE a.user_id = ? ORDER BY a.completed_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assessment = $result->fetch_assoc();
        $assessment['answers'] = json_decode($assessment['answers'], true);
        $assessment['recommendations'] = json_decode($assessment['recommendations'], true);
        
        echo json_encode([
            'success' => true,
            'assessment' => $assessment
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No assessment found'
        ]);
    }
}

function getAssessmentHistory() {
    global $conn, $user_id;
    
    $stmt = $conn->prepare("SELECT id, wellness_score, completed_at FROM assessments WHERE user_id = ? ORDER BY completed_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'total_assessments' => count($history)
    ]);
}

function generateRecommendations($wellness_score, $answers) {
    $recommendations = [];
    
    if ($wellness_score < 40) {
        $recommendations[] = [
            'category' => 'urgent',
            'title' => 'Immediate Support Recommended',
            'description' => 'Your responses indicate you may benefit from professional mental health support.',
            'priority' => 'high'
        ];
    } elseif ($wellness_score < 60) {
        $recommendations[] = [
            'category' => 'moderate',
            'title' => 'Focus on Self-Care',
            'description' => 'Implement regular self-care practices and wellness programs.',
            'priority' => 'medium'
        ];
    } else {
        $recommendations[] = [
            'category' => 'maintenance',
            'title' => 'Maintain Your Wellness',
            'description' => 'Continue your current practices and consider advanced programs.',
            'priority' => 'low'
        ];
    }
    
    return $recommendations;
}

function getRecommendedProgram($answers) {
    // Default to program 1 (Mindfulness & Stress Relief)
    $program_id = 1;
    
    // Simple logic based on answers - can be enhanced
    if (isset($answers[2]) && $answers[2] >= 2) $program_id = 1; // High anxiety -> Mindfulness
    if (isset($answers[4]) && $answers[4] >= 2) $program_id = 1; // Poor sleep -> Mindfulness  
    if (isset($answers[1]) && $answers[1] >= 2) $program_id = 1; // Depression/mood -> Mindfulness
    
    return $program_id;
}

function getProgramDetails($program_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

function resetAssessment() {
    global $conn, $user_id;
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Reset user's assessment status and program assignment
        $stmt = $conn->prepare("UPDATE users SET assessment_taken = 0, assigned_program_id = NULL, program_start_date = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to reset user assessment status");
        }
        
        // Optionally, you might want to keep assessment history for analytics
        // but mark it as superseded or create a new entry when they retake
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment reset successfully. You can now retake the assessment.'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset assessment: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>