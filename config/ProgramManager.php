<?php
require_once 'database.php';

class ProgramManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all available programs
    public function getAllPrograms() {
        $query = "SELECT * FROM programs WHERE is_active = 1 ORDER BY difficulty_level, program_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get program by ID
    public function getProgramById($program_id) {
        $query = "SELECT * FROM programs WHERE id = :program_id AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get recommended programs based on assessment
    public function getRecommendedPrograms($wellness_score, $assessment_answers = null) {
        $recommended_programs = [];

        // Basic recommendation logic based on wellness score
        if ($wellness_score < 40) {
            // Low wellness score - recommend anxiety and stress management
            $query = "SELECT * FROM programs 
                      WHERE category IN ('Mental Health', 'Mental Wellness') 
                      AND difficulty_level = 'beginner' 
                      AND is_active = 1 
                      ORDER BY RAND() LIMIT 2";
        } elseif ($wellness_score < 70) {
            // Medium wellness score - recommend emotional balance and mindfulness
            $query = "SELECT * FROM programs 
                      WHERE category IN ('Emotional Health', 'Mental Wellness') 
                      AND difficulty_level IN ('beginner', 'intermediate') 
                      AND is_active = 1 
                      ORDER BY RAND() LIMIT 2";
        } else {
            // High wellness score - recommend personal development
            $query = "SELECT * FROM programs 
                      WHERE category IN ('Personal Development', 'Physical Wellness') 
                      AND is_active = 1 
                      ORDER BY RAND() LIMIT 2";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Assign program to user
    public function assignProgramToUser($user_id, $program_id) {
        try {
            $this->conn->beginTransaction();

            // Update user's assigned program
            $query = "UPDATE users 
                      SET assigned_program_id = :program_id, 
                          program_start_date = CURDATE(),
                          total_program_days = 0 
                      WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":program_id", $program_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            // Create program tasks assignments
            $this->createProgramTaskAssignments($program_id);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    // Create task assignments for a program
    private function createProgramTaskAssignments($program_id) {
        // Get program details
        $program = $this->getProgramById($program_id);
        if (!$program) return false;

        // Define task assignments based on program category
        $task_assignments = $this->getTaskAssignmentsByCategory($program['category'], $program['duration_days']);

        // Insert program task assignments
        foreach ($task_assignments as $assignment) {
            $query = "INSERT IGNORE INTO program_tasks (program_id, task_id, day_number, is_required, order_sequence) 
                      VALUES (:program_id, :task_id, :day_number, :is_required, :order_sequence)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":program_id", $program_id);
            $stmt->bindParam(":task_id", $assignment['task_id']);
            $stmt->bindParam(":day_number", $assignment['day_number']);
            $stmt->bindParam(":is_required", $assignment['is_required']);
            $stmt->bindParam(":order_sequence", $assignment['order_sequence']);
            $stmt->execute();
        }

        return true;
    }

    // Get task assignments based on program category
    private function getTaskAssignmentsByCategory($category, $duration_days) {
        $assignments = [];

        // Get tasks by category
        $query = "SELECT id, task_name, category FROM tasks WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Define task assignment patterns based on category
        switch ($category) {
            case 'Mental Wellness':
                $core_tasks = ['Morning Meditation', 'Breathing Exercise', 'Gratitude Journal', 'Emotion Check-in'];
                $weekly_tasks = ['Weekly Planning', 'Progress Review'];
                break;
            
            case 'Emotional Health':
                $core_tasks = ['Gratitude Journal', 'Emotion Check-in', 'Positive Affirmations', 'Self-Reflection'];
                $weekly_tasks = ['Social Connection', 'Progress Review'];
                break;
            
            case 'Physical Wellness':
                $core_tasks = ['Sleep Schedule Check', 'Water Intake', 'Relaxation Routine'];
                $weekly_tasks = ['Weekly Planning', 'Progress Review'];
                break;
            
            case 'Mental Health':
                $core_tasks = ['Anxiety Breathing', 'Grounding Exercise', 'Worry Time', 'Emotion Check-in'];
                $weekly_tasks = ['Progress Review', 'Social Connection'];
                break;
            
            case 'Personal Development':
                $core_tasks = ['Goal Review', 'Self-Reflection', 'Skill Practice', 'Confidence Building'];
                $weekly_tasks = ['Weekly Planning', 'Progress Review', 'Social Connection'];
                break;
            
            default:
                $core_tasks = ['Morning Meditation', 'Gratitude Journal', 'Emotion Check-in'];
                $weekly_tasks = ['Weekly Planning', 'Progress Review'];
        }

        // Create daily assignments
        for ($day = 1; $day <= $duration_days; $day++) {
            $order = 1;
            
            // Assign core daily tasks
            foreach ($core_tasks as $task_name) {
                $task = $this->findTaskByName($all_tasks, $task_name);
                if ($task) {
                    $assignments[] = [
                        'task_id' => $task['id'],
                        'day_number' => $day,
                        'is_required' => 1,
                        'order_sequence' => $order++
                    ];
                }
            }

            // Assign weekly tasks (every 7 days)
            if ($day % 7 == 0) {
                foreach ($weekly_tasks as $task_name) {
                    $task = $this->findTaskByName($all_tasks, $task_name);
                    if ($task) {
                        $assignments[] = [
                            'task_id' => $task['id'],
                            'day_number' => $day,
                            'is_required' => 0,
                            'order_sequence' => $order++
                        ];
                    }
                }
            }

            // Add progressive tasks based on program progress
            if ($day > 7) {
                // Add intermediate tasks after first week
                $intermediate_tasks = $this->getIntermediateTasksByCategory($category);
                foreach ($intermediate_tasks as $task_name) {
                    if ($day % 3 == 0) { // Every 3 days
                        $task = $this->findTaskByName($all_tasks, $task_name);
                        if ($task) {
                            $assignments[] = [
                                'task_id' => $task['id'],
                                'day_number' => $day,
                                'is_required' => 0,
                                'order_sequence' => $order++
                            ];
                        }
                    }
                }
            }
        }

        return $assignments;
    }

    // Find task by name in task array
    private function findTaskByName($tasks, $task_name) {
        foreach ($tasks as $task) {
            if ($task['task_name'] === $task_name) {
                return $task;
            }
        }
        return null;
    }

    // Get intermediate tasks by category
    private function getIntermediateTasksByCategory($category) {
        switch ($category) {
            case 'Mental Wellness':
                return ['Body Scan', 'Mindful Walking'];
            case 'Emotional Health':
                return ['Stress Level Assessment', 'Progressive Muscle Relaxation'];
            case 'Physical Wellness':
                return ['Screen Time Limit'];
            case 'Mental Health':
                return ['Progressive Muscle Relaxation', 'Worry Time'];
            case 'Personal Development':
                return ['Skill Practice', 'Confidence Building'];
            default:
                return ['Body Scan', 'Stress Level Assessment'];
        }
    }

    // Get user's program progress
    public function getUserProgramProgress($user_id) {
        $query = "SELECT 
                    u.assigned_program_id,
                    u.program_start_date,
                    u.total_program_days,
                    p.program_name,
                    p.program_description,
                    p.duration_days,
                    p.icon,
                    p.color,
                    DATEDIFF(CURDATE(), u.program_start_date) + 1 as days_in_program,
                    ROUND((DATEDIFF(CURDATE(), u.program_start_date) + 1) / p.duration_days * 100, 2) as progress_percentage
                  FROM users u
                  LEFT JOIN programs p ON u.assigned_program_id = p.id
                  WHERE u.id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if user completed program
    public function checkProgramCompletion($user_id) {
        $progress = $this->getUserProgramProgress($user_id);
        
        if ($progress && $progress['days_in_program'] >= $progress['duration_days']) {
            // Mark program as completed
            $query = "UPDATE users SET assigned_program_id = NULL WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            // Award completion achievement
            $this->awardCompletionAchievement($user_id);
            
            return true;
        }

        return false;
    }

    // Award program completion achievement
    private function awardCompletionAchievement($user_id) {
        // Get "Wellness Champion" achievement
        $query = "SELECT id FROM achievements WHERE achievement_name = 'Wellness Champion'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $achievement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($achievement) {
            $query = "INSERT IGNORE INTO user_achievements (user_id, achievement_id) 
                      VALUES (:user_id, :achievement_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":achievement_id", $achievement['id']);
            $stmt->execute();
        }
    }

    // Get program statistics
    public function getProgramStats() {
        $query = "SELECT 
                    COUNT(*) as total_programs,
                    COUNT(CASE WHEN difficulty_level = 'beginner' THEN 1 END) as beginner_programs,
                    COUNT(CASE WHEN difficulty_level = 'intermediate' THEN 1 END) as intermediate_programs,
                    COUNT(CASE WHEN difficulty_level = 'advanced' THEN 1 END) as advanced_programs,
                    AVG(duration_days) as avg_duration
                  FROM programs WHERE is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>