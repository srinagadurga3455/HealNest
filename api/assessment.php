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
        case 'submit_assessment':
            submitAssessment($conn, $user_id, $input);
            break;
        case 'reset_assessment':
            resetAssessment($conn, $user_id);
            break;
        case 'get_latest_assessment':
            getLatestAssessment($conn, $user_id);
            break;
        case 'check_live_database':
            checkLiveDatabase($conn, $user_id);
            break;
        case 'check_user_session':
            checkUserSession($conn, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getLatestAssessment($conn, $user_id) {
    try {
        $stmt = $conn->prepare("SELECT id, wellness_score, completed_at, answers FROM assessments WHERE user_id = ? ORDER BY completed_at DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assessment = $result->fetch_assoc();
        
        if ($assessment) {
            // Count answers
            $answers = json_decode($assessment['answers'], true);
            $answer_count = is_array($answers) ? count($answers) : 0;
            
            echo json_encode([
                'success' => true,
                'assessment' => [
                    'id' => $assessment['id'],
                    'wellness_score' => $assessment['wellness_score'],
                    'completed_at' => $assessment['completed_at'],
                    'answer_count' => $answer_count,
                    'formatted_date' => date('F j, Y \a\t g:i A', strtotime($assessment['completed_at']))
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No assessments found'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving assessment: ' . $e->getMessage()
        ]);
    }
}

function submitAssessment($conn, $user_id, $data) {
    try {
        error_log("=== SIMPLE ASSESSMENT SUBMISSION START ===");
        error_log("User ID: " . $user_id);
        error_log("Session data: " . print_r($_SESSION, true));
        
        if (empty($data['answers']) || !is_array($data['answers'])) {
            echo json_encode(['success' => false, 'message' => 'Assessment answers are required']);
            return;
        }

        $answers = $data['answers'];
        error_log("Number of answers: " . count($answers));
        error_log("Answers array: " . print_r($answers, true));
        
        // Calculate wellness score
        $total_score = array_sum($answers);
        $answer_count = count($answers);
        $max_possible_score = $answer_count * 3;
        $wellness_score = round(((($max_possible_score - $total_score) / $max_possible_score) * 100), 0);
        
        error_log("Wellness score: " . $wellness_score);
        
        // Analyze answers and assign personalized tasks
        $personalized_tasks = [];
        try {
            $personalized_tasks = analyzeAnswersAndAssignTasks($conn, $answers, $wellness_score);
            error_log("Personalized tasks assigned: " . count($personalized_tasks));
        } catch (Exception $task_error) {
            error_log("Task assignment error: " . $task_error->getMessage());
            // Continue without personalized tasks
        }
        
        // Simple recommendations based on analysis
        $recommendations = [];
        try {
            $recommendations = generateRecommendations($answers, $wellness_score);
        } catch (Exception $rec_error) {
            error_log("Recommendation error: " . $rec_error->getMessage());
            $recommendations = [['category' => 'general', 'title' => 'Wellness Journey', 'description' => 'Your personalized program has been created.']];
        }
        
        $recommended_program_id = 1;
        
        // Try direct insert without transaction first
        error_log("Attempting direct insert...");
        
        $answers_json = json_encode($answers);
        $recommendations_json = json_encode($recommendations);
        
        // Assign personalized tasks to the user
        if (!empty($personalized_tasks)) {
            assignTasksToUser($conn, $user_id, $personalized_tasks);
        }
        
        // Use prepared statement for safety
        $stmt = $conn->prepare("INSERT INTO assessments (user_id, wellness_score, answers, recommendations, recommended_program_id, completed_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iissi", $user_id, $wellness_score, $answers_json, $recommendations_json, $recommended_program_id);
        
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Database execute failed: " . $stmt->error);
        }
        
        $assessment_id = $conn->insert_id;
        error_log("Assessment inserted successfully! ID: " . $assessment_id);
        
        // Verify the insert worked
        $verify_stmt = $conn->prepare("SELECT id, wellness_score, completed_at FROM assessments WHERE id = ?");
        $verify_stmt->bind_param("i", $assessment_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            throw new Exception("Assessment was not saved - verification failed");
        }
        
        $saved_assessment = $verify_result->fetch_assoc();
        error_log("Verification successful: " . print_r($saved_assessment, true));
        
        
        // Update user with prepared statement
        $user_stmt = $conn->prepare("UPDATE users SET assessment_taken = 1, wellness_score = ?, assigned_program_id = ? WHERE id = ?");
        $user_stmt->bind_param("iii", $wellness_score, $recommended_program_id, $user_id);
        
        if (!$user_stmt->execute()) {
            error_log("User update failed: " . $user_stmt->error);
        } else {
            error_log("User updated successfully");
        }
        
        // Get program details
        $program_result = $conn->query("SELECT * FROM programs WHERE id = $recommended_program_id");
        $program_details = $program_result ? $program_result->fetch_assoc() : null;
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment completed successfully',
            'results' => [
                'wellness_score' => $wellness_score,
                'recommendations' => $recommendations,
                'recommended_program' => $program_details,
                'assessment_id' => $assessment_id,
                'saved_at' => date('F j, Y \a\t g:i A'),
                'answer_count' => count($answers),
                'user_id' => $user_id,
                'database_confirmed' => true,
                'personalized_tasks_count' => is_array($personalized_tasks) ? count($personalized_tasks) : 0,
                'task_categories' => is_array($personalized_tasks) ? getTaskCategories($personalized_tasks) : []
            ]
        ]);
        
        error_log("=== ASSESSMENT SUBMISSION SUCCESS - ID: $assessment_id ===");
        
    } catch (Exception $e) {
        error_log("=== ASSESSMENT SUBMISSION ERROR ===");
        error_log("Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save assessment: ' . $e->getMessage()]);
    }
}

function analyzeAnswersAndAssignTasks($conn, $answers, $wellness_score) {
    try {
        // Ensure we have enough answers
        if (count($answers) < 13) {
            error_log("Not enough answers for analysis: " . count($answers));
            return [];
        }
        
        // Map answers to assessment categories (based on question order)
        $emotional_wellbeing = array_slice($answers, 0, 3); // Questions 1-3
        $anxiety_stress = array_slice($answers, 3, 3); // Questions 4-6  
        $sleep_rest = array_slice($answers, 6, 3); // Questions 7-9
        $social_connection = array_slice($answers, 9, 3); // Questions 10-12
        $self_care = count($answers) > 12 ? array_slice($answers, 12, 2) : [0, 0]; // Questions 13-14
        
        // Calculate category scores (higher score = more issues)
        $emotional_score = array_sum($emotional_wellbeing);
        $anxiety_score = array_sum($anxiety_stress);
        $sleep_score = array_sum($sleep_rest);
        $social_score = array_sum($social_connection);
        $selfcare_score = array_sum($self_care);
        
        error_log("Category scores - Emotional: $emotional_score, Anxiety: $anxiety_score, Sleep: $sleep_score, Social: $social_score, Self-care: $selfcare_score");
        
        // Create personalized tasks based on user's specific needs
        $personalized_tasks = [];
        
        // Emotional Wellbeing Tasks (if emotional_score >= 4)
        if ($emotional_score >= 4) {
            $task_id = createPersonalizedTask($conn, 'Mood Journaling', 'Write about your feelings and emotions for 10 minutes', 'daily', 'Emotional Health', 10, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
            
            $task_id = createPersonalizedTask($conn, 'Gratitude Practice', 'List 3 things you are grateful for today', 'daily', 'Emotional Health', 5, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
        }
        
        // Anxiety & Stress Tasks (if anxiety_score >= 4)
        if ($anxiety_score >= 4) {
            $task_id = createPersonalizedTask($conn, 'Deep Breathing Exercise', '4-7-8 breathing technique for anxiety relief', 'daily', 'Stress Management', 5, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
            
            $task_id = createPersonalizedTask($conn, 'Progressive Muscle Relaxation', 'Tense and relax muscle groups to reduce stress', 'daily', 'Stress Management', 15, 'medium');
            if ($task_id) $personalized_tasks[] = $task_id;
        }
        
        // Sleep & Rest Tasks (if sleep_score >= 4)
        if ($sleep_score >= 4) {
            $task_id = createPersonalizedTask($conn, 'Sleep Hygiene Check', 'Review and improve your bedtime routine', 'daily', 'Sleep Health', 5, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
            
            $task_id = createPersonalizedTask($conn, 'Evening Wind-Down', '30 minutes of relaxing activities before bed', 'daily', 'Sleep Health', 30, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
        }
        
        // Social Connection Tasks (if social_score >= 4)
        if ($social_score >= 4) {
            $task_id = createPersonalizedTask($conn, 'Social Connection', 'Reach out to a friend or family member', 'daily', 'Social Health', 15, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
        }
        
        // Self-Care Tasks (if selfcare_score >= 3)
        if ($selfcare_score >= 3) {
            $task_id = createPersonalizedTask($conn, 'Self-Care Activity', 'Do something that brings you joy or peace', 'daily', 'Self-Care', 20, 'easy');
            if ($task_id) $personalized_tasks[] = $task_id;
        }
        
        // Always include basic wellness task
        $task_id = createPersonalizedTask($conn, 'Daily Check-In', 'Rate your overall mood and energy (1-10)', 'daily', 'Wellness Tracking', 3, 'easy');
        if ($task_id) $personalized_tasks[] = $task_id;
        
        error_log("Created " . count($personalized_tasks) . " personalized tasks");
        return $personalized_tasks;
        
    } catch (Exception $e) {
        error_log("Error in task analysis: " . $e->getMessage());
        return [];
    }
}

function createPersonalizedTask($conn, $name, $description, $type, $category, $duration, $difficulty) {
    try {
        // Check if task already exists to avoid duplicates
        $check_stmt = $conn->prepare("SELECT id FROM tasks WHERE task_name = ? LIMIT 1");
        if (!$check_stmt) {
            error_log("Prepare failed for task check: " . $conn->error);
            return null;
        }
        
        $check_stmt->bind_param("s", $name);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            error_log("Task already exists: $name (ID: " . $existing['id'] . ")");
            return $existing['id'];
        }
        
        // Create new personalized task
        $stmt = $conn->prepare("INSERT INTO tasks (task_name, task_description, task_type, category, estimated_duration, difficulty_level, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        if (!$stmt) {
            error_log("Prepare failed for task insert: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("ssssis", $name, $description, $type, $category, $duration, $difficulty);
        
        if ($stmt->execute()) {
            $task_id = $conn->insert_id;
            error_log("Created personalized task: $name (ID: $task_id)");
            return $task_id;
        } else {
            error_log("Failed to execute task insert: " . $stmt->error);
            return null;
        }
        
    } catch (Exception $e) {
        error_log("Error creating personalized task: " . $e->getMessage());
        return null;
    }
}

function generateRecommendations($answers, $wellness_score) {
    $recommendations = [];
    
    // Ensure we have enough answers
    if (count($answers) < 9) {
        $recommendations[] = ['category' => 'general', 'title' => 'Personalized Wellness Plan', 'description' => 'Your wellness program has been customized based on your responses.'];
        return $recommendations;
    }
    
    // Analyze specific areas needing attention
    $emotional_wellbeing = array_slice($answers, 0, 3);
    $anxiety_stress = array_slice($answers, 3, 3);
    $sleep_rest = array_slice($answers, 6, 3);
    $social_connection = count($answers) > 9 ? array_slice($answers, 9, 3) : [0, 0, 0];
    
    $emotional_score = array_sum($emotional_wellbeing);
    $anxiety_score = array_sum($anxiety_stress);
    $sleep_score = array_sum($sleep_rest);
    $social_score = array_sum($social_connection);
    
    // Generate specific recommendations
    if ($emotional_score >= 4) {
        $recommendations[] = ['category' => 'emotional', 'title' => 'Focus on Emotional Well-being', 'description' => 'Your responses suggest you could benefit from emotional support activities like journaling and gratitude practice.'];
    }
    
    if ($anxiety_score >= 4) {
        $recommendations[] = ['category' => 'stress', 'title' => 'Stress Management Priority', 'description' => 'Consider incorporating daily breathing exercises and relaxation techniques into your routine.'];
    }
    
    if ($sleep_score >= 4) {
        $recommendations[] = ['category' => 'sleep', 'title' => 'Improve Sleep Quality', 'description' => 'Focus on sleep hygiene and establishing a consistent bedtime routine.'];
    }
    
    if ($social_score >= 4) {
        $recommendations[] = ['category' => 'social', 'title' => 'Strengthen Social Connections', 'description' => 'Building and maintaining supportive relationships can significantly improve your well-being.'];
    }
    
    // Overall wellness recommendation
    if ($wellness_score < 50) {
        $recommendations[] = ['category' => 'overall', 'title' => 'Comprehensive Wellness Approach', 'description' => 'Your personalized program includes tasks across multiple wellness areas to support your overall mental health.'];
    } else if ($wellness_score < 75) {
        $recommendations[] = ['category' => 'maintenance', 'title' => 'Maintain and Improve', 'description' => 'You\'re doing well! Focus on the specific areas identified to continue improving your wellness.'];
    } else {
        $recommendations[] = ['category' => 'maintenance', 'title' => 'Wellness Maintenance', 'description' => 'Great job! Continue with regular self-care practices to maintain your positive mental health.'];
    }
    
    return $recommendations;
}

function getTaskCategories($task_ids) {
    if (empty($task_ids)) {
        return [];
    }
    
    $categories = [];
    foreach ($task_ids as $task_id) {
        if ($task_id) {
            // This is a simplified version - in a real implementation, 
            // you'd query the database to get the actual categories
            $categories[] = 'Personalized Task';
        }
    }
    
    return array_unique($categories);
}

function assignTasksToUser($conn, $user_id, $task_ids) {
    try {
        // Clear existing task assignments for this user (for reassessment)
        $clear_stmt = $conn->prepare("DELETE FROM program_tasks WHERE program_id = (SELECT assigned_program_id FROM users WHERE id = ?)");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        
        // Get user's assigned program
        $program_stmt = $conn->prepare("SELECT assigned_program_id FROM users WHERE id = ?");
        $program_stmt->bind_param("i", $user_id);
        $program_stmt->execute();
        $program_result = $program_stmt->get_result()->fetch_assoc();
        $program_id = $program_result['assigned_program_id'] ?? 1;
        
        // Assign new personalized tasks
        $order = 1;
        foreach ($task_ids as $task_id) {
            if ($task_id) {
                $assign_stmt = $conn->prepare("INSERT INTO program_tasks (program_id, task_id, day_number, is_required, order_sequence) VALUES (?, ?, 1, 1, ?) ON DUPLICATE KEY UPDATE order_sequence = ?");
                $assign_stmt->bind_param("iiii", $program_id, $task_id, $order, $order);
                $assign_stmt->execute();
                $order++;
            }
        }
        
        error_log("Assigned " . count($task_ids) . " tasks to user $user_id in program $program_id");
        
    } catch (Exception $e) {
        error_log("Error assigning tasks to user: " . $e->getMessage());
    }
}

function resetAssessment($conn, $user_id) {
    try {
        $conn->begin_transaction();
        
        $stmt = $conn->prepare("UPDATE users SET assessment_taken = 0, assigned_program_id = NULL, program_start_date = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to reset user assessment status");
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Assessment reset successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset assessment: ' . $e->getMessage()
        ]);
    }
}

function checkUserSession($conn, $user_id) {
    try {
        // Get current user info
        $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_info = $user_result->fetch_assoc();
        
        // Get session info
        $session_info = [
            'session_id' => session_id(),
            'user_id_from_session' => $_SESSION['user_id'] ?? 'not set',
            'all_session_data' => $_SESSION
        ];
        
        echo json_encode([
            'success' => true,
            'session_check' => [
                'current_user_id' => $user_id,
                'user_info' => $user_info,
                'session_info' => $session_info,
                'php_session_active' => session_status() === PHP_SESSION_ACTIVE
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Session check failed: ' . $e->getMessage()
        ]);
    }
}

function checkLiveDatabase($conn, $user_id) {
    try {
        // Get all assessments for this user
        $stmt = $conn->prepare("SELECT id, wellness_score, completed_at, answers FROM assessments WHERE user_id = ? ORDER BY completed_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $assessments = [];
        while ($row = $result->fetch_assoc()) {
            $answers = json_decode($row['answers'], true);
            $assessments[] = [
                'id' => $row['id'],
                'wellness_score' => $row['wellness_score'],
                'completed_at' => $row['completed_at'],
                'answer_count' => is_array($answers) ? count($answers) : 0,
                'formatted_date' => date('F j, Y \a\t g:i A', strtotime($row['completed_at']))
            ];
        }
        
        // Get total count of all assessments in database
        $total_result = $conn->query("SELECT COUNT(*) as total FROM assessments");
        $total_count = $total_result->fetch_assoc()['total'];
        
        // Get database name
        $db_result = $conn->query("SELECT DATABASE() as db_name");
        $db_name = $db_result->fetch_assoc()['db_name'];
        
        echo json_encode([
            'success' => true,
            'database_info' => [
                'database_name' => $db_name,
                'total_assessments_in_db' => $total_count,
                'user_assessments' => $assessments,
                'user_assessment_count' => count($assessments),
                'user_id' => $user_id,
                'note' => 'This shows LIVE database data, not the static SQL file'
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database check failed: ' . $e->getMessage()
        ]);
    }
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>