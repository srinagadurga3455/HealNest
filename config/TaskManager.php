<?php
require_once 'database.php';

class TaskManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get daily tasks for a user based on their program
    public function getDailyTasks($user_id, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Get user's assigned program and program start date
        $query = "SELECT assigned_program_id, program_start_date FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data['assigned_program_id']) {
            return [];
        }

        // Calculate which day of the program this is
        $program_start = new DateTime($user_data['program_start_date']);
        $current_date = new DateTime($date);
        $day_number = $current_date->diff($program_start)->days + 1;

        // Get tasks for this day
        $query = "SELECT t.*, pt.day_number, pt.is_required, pt.order_sequence,
                         CASE WHEN utc.id IS NOT NULL THEN 1 ELSE 0 END as completed
                  FROM program_tasks pt
                  JOIN tasks t ON pt.task_id = t.id
                  LEFT JOIN user_task_completions utc ON (utc.task_id = t.id AND utc.user_id = :user_id AND utc.completion_date = :date)
                  WHERE pt.program_id = :program_id AND pt.day_number = :day_number
                  ORDER BY pt.order_sequence";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":program_id", $user_data['assigned_program_id']);
        $stmt->bindParam(":day_number", $day_number);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Complete a task
    public function completeTask($user_id, $task_id, $program_id, $notes = '') {
        $completion_date = date('Y-m-d');

        // Insert task completion
        $query = "INSERT INTO user_task_completions (user_id, task_id, program_id, completion_date, notes) 
                  VALUES (:user_id, :task_id, :program_id, :completion_date, :notes)
                  ON DUPLICATE KEY UPDATE completed_at = NOW(), notes = :notes";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":task_id", $task_id);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->bindParam(":completion_date", $completion_date);
        $stmt->bindParam(":notes", $notes);

        if ($stmt->execute()) {
            // Update daily progress
            $this->updateDailyProgress($user_id, $completion_date);
            return true;
        }
        return false;
    }

    // Update daily progress summary
    public function updateDailyProgress($user_id, $date) {
        // Get total tasks assigned for today
        $tasks_today = $this->getDailyTasks($user_id, $date);
        $total_assigned = count($tasks_today);

        // Count completed tasks
        $query = "SELECT COUNT(*) as completed FROM user_task_completions 
                  WHERE user_id = :user_id AND completion_date = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $completed_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $tasks_completed = $completed_data['completed'];

        // Calculate completion percentage
        $completion_percentage = $total_assigned > 0 ? ($tasks_completed / $total_assigned) * 100 : 0;

        // Get current streak
        $user = new User($this->conn);
        $current_streak = $user->calculateStreak($user_id);

        // Insert or update daily progress
        $query = "INSERT INTO daily_progress (user_id, progress_date, total_tasks_assigned, tasks_completed, completion_percentage, streak_day) 
                  VALUES (:user_id, :date, :total_assigned, :tasks_completed, :completion_percentage, :streak_day)
                  ON DUPLICATE KEY UPDATE 
                  total_tasks_assigned = :total_assigned,
                  tasks_completed = :tasks_completed,
                  completion_percentage = :completion_percentage,
                  streak_day = :streak_day";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":total_assigned", $total_assigned);
        $stmt->bindParam(":tasks_completed", $tasks_completed);
        $stmt->bindParam(":completion_percentage", $completion_percentage);
        $stmt->bindParam(":streak_day", $current_streak);

        return $stmt->execute();
    }

    // Get user's task completion history
    public function getTaskHistory($user_id, $days = 30) {
        $start_date = date('Y-m-d', strtotime("-$days days"));
        
        $query = "SELECT dp.*, 
                         DATE_FORMAT(dp.progress_date, '%Y-%m-%d') as formatted_date,
                         DATE_FORMAT(dp.progress_date, '%M %d') as display_date
                  FROM daily_progress dp
                  WHERE dp.user_id = :user_id AND dp.progress_date >= :start_date
                  ORDER BY dp.progress_date DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get task completion stats
    public function getTaskStats($user_id) {
        $query = "SELECT 
                    COUNT(*) as total_completed,
                    COUNT(CASE WHEN completion_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as week_completed,
                    COUNT(CASE WHEN completion_date = CURDATE() THEN 1 END) as today_completed,
                    AVG(CASE WHEN dp.completion_percentage IS NOT NULL THEN dp.completion_percentage ELSE 0 END) as avg_completion_rate
                  FROM user_task_completions utc
                  LEFT JOIN daily_progress dp ON (utc.user_id = dp.user_id AND utc.completion_date = dp.progress_date)
                  WHERE utc.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Assign program tasks to user (called when user is assigned a program)
    public function assignProgramTasks($user_id, $program_id) {
        // Get program duration
        $query = "SELECT duration_days FROM programs WHERE id = :program_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->execute();
        $program = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$program) return false;

        // Get all tasks for this program
        $query = "SELECT task_id FROM program_tasks WHERE program_id = :program_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->execute();
        $program_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create daily task assignments for the program duration
        $success = true;
        for ($day = 1; $day <= $program['duration_days']; $day++) {
            $task_date = date('Y-m-d', strtotime("+$day days"));
            
            // Assign tasks for this day (you can customize logic here)
            foreach ($program_tasks as $task) {
                // For now, assign all tasks to all days (you can modify this logic)
                $query = "INSERT IGNORE INTO program_tasks (program_id, task_id, day_number) 
                          VALUES (:program_id, :task_id, :day_number)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":program_id", $program_id);
                $stmt->bindParam(":task_id", $task['task_id']);
                $stmt->bindParam(":day_number", $day);
                
                if (!$stmt->execute()) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    // Get all available tasks
    public function getAllTasks() {
        $query = "SELECT * FROM tasks WHERE is_active = 1 ORDER BY category, task_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add custom task for user
    public function addCustomTask($user_id, $task_name, $task_description, $task_date = null) {
        if (!$task_date) {
            $task_date = date('Y-m-d');
        }

        // First, create the task in tasks table
        $query = "INSERT INTO tasks (task_name, task_description, task_type, category) 
                  VALUES (:task_name, :task_description, 'daily', 'Custom')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":task_name", $task_name);
        $stmt->bindParam(":task_description", $task_description);
        
        if ($stmt->execute()) {
            $task_id = $this->conn->lastInsertId();
            
            // Get user's program
            $query = "SELECT assigned_program_id FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data['assigned_program_id']) {
                // Calculate day number
                $query = "SELECT program_start_date FROM users WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                $start_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $start_date = new DateTime($start_data['program_start_date']);
                $current_date = new DateTime($task_date);
                $day_number = $current_date->diff($start_date)->days + 1;
                
                // Add to program tasks
                $query = "INSERT INTO program_tasks (program_id, task_id, day_number, is_required) 
                          VALUES (:program_id, :task_id, :day_number, 0)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":program_id", $user_data['assigned_program_id']);
                $stmt->bindParam(":task_id", $task_id);
                $stmt->bindParam(":day_number", $day_number);
                $stmt->execute();
            }
            
            return $task_id;
        }
        
        return false;
    }
}
?>