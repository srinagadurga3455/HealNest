<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/User.php';
require_once '../config/ProgramManager.php';

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
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

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
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function submitAssessment() {
    global $db, $user_id;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    if (empty($data['answers']) || !is_array($data['answers'])) {
        echo json_encode(['success' => false, 'message' => 'Assessment answers are required']);
        return;
    }
    
    $answers = $data['answers'];
    
    // Calculate total score
    $total_score = array_sum($answers);
    
    // Calculate wellness score (0-100, higher is better)
    $max_possible_score = count($answers) * 3; // Assuming max 3 points per question
    $wellness_score = round(((($max_possible_score - $total_score) / $max_possible_score) * 100), 2);
    
    // Generate recommendations based on score and answers
    $recommendations = generateRecommendations($wellness_score, $answers);
    
    // Get recommended program
    $programManager = new ProgramManager($db);
    $recommended_programs = $programManager->getRecommendedPrograms($wellness_score, $answers);
    $recommended_program_id = !empty($recommended_programs) ? $recommended_programs[0]['id'] : null;
    
    // Save assessment to database
    $query = "INSERT INTO assessments (user_id, total_score, wellness_score, answers, recommendations, recommended_program_id) 
              VALUES (:user_id, :total_score, :wellness_score, :answers, :recommendations, :recommended_program_id)";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":total_score", $total_score);
    $stmt->bindParam(":wellness_score", $wellness_score);
    $stmt->bindParam(":answers", json_encode($answers));
    $stmt->bindParam(":recommendations", json_encode($recommendations));
    $stmt->bindParam(":recommended_program_id", $recommended_program_id);
    
    if ($stmt->execute()) {
        // Update user's assessment status
        $user = new User($db);
        $user->id = $user_id;
        $user->completeAssessment($wellness_score);
        
        // Auto-assign recommended program if available
        if ($recommended_program_id) {
            $programManager->assignProgramToUser($user_id, $recommended_program_id);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment completed successfully',
            'results' => [
                'total_score' => $total_score,
                'wellness_score' => $wellness_score,
                'recommendations' => $recommendations,
                'recommended_programs' => $recommended_programs
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save assessment']);
    }
}

function getAssessmentResults() {
    global $db, $user_id;
    
    $query = "SELECT a.*, p.program_name, p.program_description 
              FROM assessments a 
              LEFT JOIN programs p ON a.recommended_program_id = p.id 
              WHERE a.user_id = :user_id 
              ORDER BY a.completed_at DESC 
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $result['answers'] = json_decode($result['answers'], true);
        $result['recommendations'] = json_decode($result['recommendations'], true);
        
        echo json_encode([
            'success' => true,
            'assessment' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No assessment found'
        ]);
    }
}

function getAssessmentHistory() {
    global $db, $user_id;
    
    $query = "SELECT id, total_score, wellness_score, completed_at 
              FROM assessments 
              WHERE user_id = :user_id 
              ORDER BY completed_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'total_assessments' => count($history)
    ]);
}

function generateRecommendations($wellness_score, $answers) {
    $recommendations = [];
    
    // Analyze answers by categories (assuming questions are grouped)
    $categories = [
        'mood' => [0, 1], // Questions about mood
        'anxiety' => [2, 3], // Questions about anxiety
        'sleep' => [4, 5], // Questions about sleep
        'stress' => [6, 7], // Questions about stress
        'social' => [8, 9], // Questions about social connections
        'physical' => [10, 11], // Questions about physical health
        'emotional' => [12, 13], // Questions about emotional health
        'goals' => [14, 15], // Questions about goals and motivation
        'coping' => [16, 17], // Questions about coping skills
        'overall' => [18, 19] // Overall wellness questions
    ];
    
    foreach ($categories as $category => $question_indices) {
        $category_score = 0;
        $category_count = 0;
        
        foreach ($question_indices as $index) {
            if (isset($answers[$index])) {
                $category_score += $answers[$index];
                $category_count++;
            }
        }
        
        if ($category_count > 0) {
            $avg_score = $category_score / $category_count;
            
            if ($avg_score >= 2) {
                $recommendations[] = getCategoryRecommendation($category, 'high');
            } elseif ($avg_score >= 1) {
                $recommendations[] = getCategoryRecommendation($category, 'medium');
            }
        }
    }
    
    // Add general recommendations based on overall wellness score
    if ($wellness_score < 40) {
        $recommendations[] = [
            'category' => 'urgent',
            'title' => 'Immediate Support Recommended',
            'description' => 'Your responses indicate you may benefit from professional mental health support. Consider speaking with a counselor or therapist.',
            'priority' => 'high'
        ];
    } elseif ($wellness_score < 60) {
        $recommendations[] = [
            'category' => 'moderate',
            'title' => 'Focus on Self-Care',
            'description' => 'Implement regular self-care practices and consider joining wellness programs to improve your mental health.',
            'priority' => 'medium'
        ];
    } else {
        $recommendations[] = [
            'category' => 'maintenance',
            'title' => 'Maintain Your Wellness',
            'description' => 'You\'re doing well! Continue your current practices and consider advanced programs for further growth.',
            'priority' => 'low'
        ];
    }
    
    return $recommendations;
}

function getCategoryRecommendation($category, $level) {
    $recommendations = [
        'mood' => [
            'high' => [
                'title' => 'Mood Support',
                'description' => 'Consider mood tracking, professional counseling, and our Emotional Balance program.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Mood Enhancement',
                'description' => 'Practice daily gratitude and engage in activities you enjoy.',
                'priority' => 'medium'
            ]
        ],
        'anxiety' => [
            'high' => [
                'title' => 'Anxiety Management',
                'description' => 'Practice daily mindfulness and breathing exercises. Our Stress Management program can help.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Anxiety Reduction',
                'description' => 'Try relaxation techniques and regular exercise.',
                'priority' => 'medium'
            ]
        ],
        'sleep' => [
            'high' => [
                'title' => 'Sleep Improvement',
                'description' => 'Establish a consistent sleep routine. Consider our Sleep Wellness program.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Sleep Optimization',
                'description' => 'Improve sleep hygiene and create a calming bedtime routine.',
                'priority' => 'medium'
            ]
        ],
        'stress' => [
            'high' => [
                'title' => 'Stress Management',
                'description' => 'Learn stress management techniques and time management skills.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Stress Reduction',
                'description' => 'Practice time management and take regular breaks.',
                'priority' => 'medium'
            ]
        ],
        'social' => [
            'high' => [
                'title' => 'Social Connection',
                'description' => 'Focus on building meaningful connections. Join our Support Groups.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Social Enhancement',
                'description' => 'Maintain existing relationships and be open to new connections.',
                'priority' => 'medium'
            ]
        ],
        'physical' => [
            'high' => [
                'title' => 'Physical Wellness',
                'description' => 'Address physical symptoms with relaxation techniques and exercise.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Physical Health',
                'description' => 'Incorporate regular exercise and relaxation.',
                'priority' => 'medium'
            ]
        ],
        'emotional' => [
            'high' => [
                'title' => 'Emotional Regulation',
                'description' => 'Learn emotional regulation techniques and mindfulness.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Emotional Awareness',
                'description' => 'Practice emotional awareness and expression.',
                'priority' => 'medium'
            ]
        ],
        'goals' => [
            'high' => [
                'title' => 'Goal Setting',
                'description' => 'Work with a counselor or coach on goal-setting and planning.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Goal Clarity',
                'description' => 'Regular goal review and adjustment.',
                'priority' => 'medium'
            ]
        ],
        'coping' => [
            'high' => [
                'title' => 'Coping Skills',
                'description' => 'Learn healthy coping strategies through our wellness programs.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Coping Enhancement',
                'description' => 'Continue developing healthy coping strategies.',
                'priority' => 'medium'
            ]
        ],
        'overall' => [
            'high' => [
                'title' => 'Comprehensive Support',
                'description' => 'Consider comprehensive wellness support and professional guidance.',
                'priority' => 'high'
            ],
            'medium' => [
                'title' => 'Wellness Maintenance',
                'description' => 'Maintain your current wellness practices.',
                'priority' => 'medium'
            ]
        ]
    ];
    
    $recommendation = $recommendations[$category][$level] ?? [
        'title' => 'General Wellness',
        'description' => 'Focus on this area for improved wellness.',
        'priority' => 'medium'
    ];
    
    $recommendation['category'] = $category;
    return $recommendation;
}
?>