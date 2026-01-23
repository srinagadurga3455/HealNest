<?php
require_once 'database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $full_name;
    public $email;
    public $password_hash;
    public $phone;
    public $birth_date;
    public $bio;
    public $avatar_path;
    public $assigned_program_id;
    public $program_start_date;
    public $current_streak;
    public $highest_streak;
    public $last_activity_date;
    public $total_program_days;
    public $assessment_taken;
    public $assessment_date;
    public $wellness_score;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register new user
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET full_name=:full_name, email=:email, password_hash=:password_hash";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = password_hash($this->password_hash, PASSWORD_DEFAULT);

        // Bind values
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Login user
    public function login() {
        $query = "SELECT id, full_name, email, password_hash, assigned_program_id, current_streak, 
                         highest_streak, assessment_taken, wellness_score 
                  FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row && password_verify($this->password_hash, $row['password_hash'])) {
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->assigned_program_id = $row['assigned_program_id'];
            $this->current_streak = $row['current_streak'];
            $this->highest_streak = $row['highest_streak'];
            $this->assessment_taken = $row['assessment_taken'];
            $this->wellness_score = $row['wellness_score'];
            return true;
        }
        return false;
    }

    // Update user profile
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                  SET full_name=:full_name, phone=:phone, birth_date=:birth_date, 
                      bio=:bio, avatar_path=:avatar_path 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":birth_date", $this->birth_date);
        $stmt->bindParam(":bio", $this->bio);
        $stmt->bindParam(":avatar_path", $this->avatar_path);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Assign program to user
    public function assignProgram($program_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET assigned_program_id=:program_id, program_start_date=CURDATE(), 
                      total_program_days=0 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program_id", $program_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Update streak information
    public function updateStreak($new_streak) {
        $query = "UPDATE " . $this->table_name . " 
                  SET current_streak=:current_streak, 
                      highest_streak=GREATEST(highest_streak, :highest_streak),
                      last_activity_date=CURDATE(),
                      total_program_days=total_program_days+1
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":current_streak", $new_streak);
        $stmt->bindParam(":highest_streak", $new_streak);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Mark assessment as completed
    public function completeAssessment($wellness_score) {
        $query = "UPDATE " . $this->table_name . " 
                  SET assessment_taken=1, assessment_date=NOW(), wellness_score=:wellness_score 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":wellness_score", $wellness_score);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Get user by ID
    public function getUserById($user_id) {
        $query = "SELECT u.*, p.program_name, p.program_description, p.program_goal 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN programs p ON u.assigned_program_id = p.id 
                  WHERE u.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get user statistics
    public function getUserStats($user_id) {
        $query = "SELECT 
                    u.current_streak,
                    u.highest_streak,
                    u.total_program_days,
                    u.wellness_score,
                    COUNT(DISTINCT utc.id) as total_tasks_completed,
                    COUNT(DISTINCT je.id) as journal_entries,
                    COUNT(DISTINCT me.id) as mood_entries,
                    COUNT(DISTINCT ua.id) as achievements_earned
                  FROM users u
                  LEFT JOIN user_task_completions utc ON u.id = utc.user_id
                  LEFT JOIN journal_entries je ON u.id = je.user_id
                  LEFT JOIN mood_entries me ON u.id = me.user_id
                  LEFT JOIN user_achievements ua ON u.id = ua.user_id
                  WHERE u.id = :user_id
                  GROUP BY u.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Calculate and update daily streak
    public function calculateStreak($user_id) {
        // Get last activity date
        $query = "SELECT last_activity_date, current_streak FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $last_activity = $user_data['last_activity_date'];
        $current_streak = $user_data['current_streak'];

        // Check if user completed tasks today
        $query = "SELECT COUNT(*) as tasks_today FROM daily_progress 
                  WHERE user_id = :user_id AND progress_date = :today AND tasks_completed > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":today", $today);
        $stmt->execute();
        $today_progress = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($today_progress['tasks_today'] > 0) {
            if ($last_activity == $yesterday) {
                // Continue streak
                $new_streak = $current_streak + 1;
            } elseif ($last_activity == $today) {
                // Already updated today
                $new_streak = $current_streak;
            } else {
                // Start new streak
                $new_streak = 1;
            }

            $this->updateStreak($new_streak);
            return $new_streak;
        }

        return $current_streak;
    }
}
?>