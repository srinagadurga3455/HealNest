<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/ProgramManager.php';
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
$programManager = new ProgramManager($db);
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_all_programs':
        getAllPrograms();
        break;
    case 'get_program':
        getProgram();
        break;
    case 'get_recommended_programs':
        getRecommendedPrograms();
        break;
    case 'assign_program':
        assignProgram();
        break;
    case 'get_user_program_progress':
        getUserProgramProgress();
        break;
    case 'check_program_completion':
        checkProgramCompletion();
        break;
    case 'get_program_stats':
        getProgramStats();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getAllPrograms() {
    global $programManager;
    
    $programs = $programManager->getAllPrograms();
    
    // Group programs by difficulty level
    $grouped_programs = [
        'beginner' => [],
        'intermediate' => [],
        'advanced' => []
    ];
    
    foreach ($programs as $program) {
        $grouped_programs[$program['difficulty_level']][] = $program;
    }
    
    echo json_encode([
        'success' => true,
        'programs' => $programs,
        'grouped_programs' => $grouped_programs,
        'total_programs' => count($programs)
    ]);
}

function getProgram() {
    global $programManager;
    
    $program_id = $_GET['program_id'] ?? '';
    
    if (empty($program_id)) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    $program = $programManager->getProgramById($program_id);
    
    if ($program) {
        echo json_encode([
            'success' => true,
            'program' => $program
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Program not found']);
    }
}

function getRecommendedPrograms() {
    global $programManager, $user_id;
    
    // Get user's wellness score
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    $userData = $user->getUserById($user_id);
    
    $wellness_score = $userData['wellness_score'] ?? 50;
    
    $recommended = $programManager->getRecommendedPrograms($wellness_score);
    
    echo json_encode([
        'success' => true,
        'recommended_programs' => $recommended,
        'wellness_score' => $wellness_score,
        'total_recommended' => count($recommended)
    ]);
}

function assignProgram() {
    global $programManager, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['program_id'])) {
        echo json_encode(['success' => false, 'message' => 'Program ID is required']);
        return;
    }
    
    $program_id = $data['program_id'];
    
    if ($programManager->assignProgramToUser($user_id, $program_id)) {
        $program = $programManager->getProgramById($program_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Program assigned successfully',
            'program' => $program
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign program']);
    }
}

function getUserProgramProgress() {
    global $programManager, $user_id;
    
    $progress = $programManager->getUserProgramProgress($user_id);
    
    if ($progress) {
        echo json_encode([
            'success' => true,
            'progress' => $progress
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'progress' => null,
            'message' => 'No program assigned'
        ]);
    }
}

function checkProgramCompletion() {
    global $programManager, $user_id;
    
    $completed = $programManager->checkProgramCompletion($user_id);
    
    echo json_encode([
        'success' => true,
        'program_completed' => $completed,
        'message' => $completed ? 'Congratulations! Program completed!' : 'Program still in progress'
    ]);
}

function getProgramStats() {
    global $programManager;
    
    $stats = $programManager->getProgramStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}
?>