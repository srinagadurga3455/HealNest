<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
include '../config/connect.php';

// Check if user already has a program assigned
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT assigned_program_id, assessment_taken, program_start_date FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$has_program = !empty($user['assigned_program_id']);
$assessment_taken = $user['assessment_taken'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Health Assessment - HealNest</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }

        /* Assessment Page */
        .assessment-page {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 2rem;
        }

        .assessment-container {
            max-width: 800px;
            margin: 0 auto;
            padding-top: 2rem;
        }

        .assessment-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            color: white;
            border-radius: 15px;
        }

        .progress-container {
            margin-bottom: 2rem;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .progress-bar-custom {
            height: 8px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .question-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }

        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2A3547;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .answer-option {
            padding: 1rem 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .answer-option:hover {
            border-color: #5D87FF;
            background: rgba(93, 135, 255, 0.05);
        }

        .answer-option.selected {
            border-color: #5D87FF;
            background: rgba(93, 135, 255, 0.1);
            color: #5D87FF;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .btn-nav {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-prev {
            background: #6c757d;
            color: white;
        }

        .btn-next {
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            color: white;
        }

        .btn-submit {
            background: linear-gradient(135deg, #13DEB9 0%, #20c997 100%);
            color: white;
        }

        .btn-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-nav:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .hidden {
            display: none;
        }

        .results-section {
            text-align: center;
        }

        .program-assignment {
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(93, 135, 255, 0.4);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .assessment-page {
                padding: 1rem;
            }
            
            .assessment-container {
                padding-top: 1rem;
            }
            
            .question-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="assessment-page">
        <div class="assessment-container">
            <?php if ($has_program && $assessment_taken): ?>
                <!-- User already has a program assigned -->
                <div class="assessment-header">
                    <h1>Assessment Already Completed</h1>
                    <p>You have already completed your wellness assessment and have been assigned a program.</p>
                </div>
                
                <div class="question-card results-section">
                    <div class="program-assignment">
                        <h3>✅ Your Current Program</h3>
                        <p>You are currently enrolled in your personalized wellness program.</p>
                        <p><strong>Program started:</strong> <?php echo date('F j, Y', strtotime($user['program_start_date'])); ?></p>
                    </div>
                    
                    <div style="margin: 2rem 0;">
                        <h4>What would you like to do?</h4>
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
                            <button class="btn-primary" onclick="window.location.href='./dashboard.php'">
                                📊 Go to Dashboard
                            </button>
                            <button class="btn-primary" onclick="window.location.href='./program.php'">
                                🎯 View My Program
                            </button>
                            <button class="btn-primary" onclick="window.location.href='./tasks.php'">
                                ✅ Today's Tasks
                            </button>
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
                            <p style="color: #666; font-size: 0.9rem;">
                                Want to retake the assessment? This will reassign your program based on your current needs.
                            </p>
                            <button class="btn-nav" onclick="retakeAssessment()" style="background: #6c757d; color: white; margin-top: 1rem;">
                                🔄 Retake Assessment
                            </button>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- User needs to take assessment -->
                <div class="assessment-header">
                    <h1>Mental Health Assessment</h1>
                    <p>This assessment will help us understand your current mental wellness and provide personalized recommendations.</p>
                </div>

                <div class="progress-container">
                    <div class="progress-info">
                        <span>Progress</span>
                        <span><span id="currentQuestion">1</span> of <span id="totalQuestions">5</span></span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" id="progressFill" style="width: 20%"></div>
                    </div>
                </div>

                <div id="assessmentQuestions">
                    <div class="question-card">
                        <div class="question-text" id="questionText">Loading question...</div>
                        <div class="answer-options" id="answerOptions">
                            <!-- Options will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="navigation-buttons">
                    <button class="btn-nav btn-prev" id="prevBtn" onclick="previousQuestion()" style="display: none;">Previous</button>
                    <button class="btn-nav btn-next" id="nextBtn" onclick="nextQuestion()">Next</button>
                    <button class="btn-nav btn-submit" id="submitBtn" onclick="submitAssessment()" style="display: none;">
                        <span id="submitText">Complete Assessment</span>
                        <span id="submitLoading" class="loading hidden"></span>
                    </button>
                </div>

                <div id="resultsSection" class="hidden">
                    <div class="question-card results-section">
                        <h2>Assessment Complete!</h2>
                        <div id="programAssignment"></div>
                        <button class="btn-primary" onclick="goToDashboard()">Continue to Dashboard</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentQuestionIndex = 0;
        let assessmentAnswers = {};
        let isSubmitting = false;

        // Assessment Questions
        const questions = [
            {
                text: "Over the past two weeks, how often have you felt down, depressed, or hopeless?",
                options: [
                    { text: "Not at all", value: 0 },
                    { text: "Several days", value: 1 },
                    { text: "More than half the days", value: 2 },
                    { text: "Nearly every day", value: 3 }
                ]
            },
            {
                text: "How often do you feel nervous, anxious, or on edge?",
                options: [
                    { text: "Not at all", value: 0 },
                    { text: "Several days", value: 1 },
                    { text: "More than half the days", value: 2 },
                    { text: "Nearly every day", value: 3 }
                ]
            },
            {
                text: "How often do you feel overwhelmed by daily responsibilities?",
                options: [
                    { text: "Never", value: 0 },
                    { text: "Sometimes", value: 1 },
                    { text: "Often", value: 2 },
                    { text: "Always", value: 3 }
                ]
            },
            {
                text: "How would you rate your sleep quality over the past month?",
                options: [
                    { text: "Very good", value: 0 },
                    { text: "Fairly good", value: 1 },
                    { text: "Fairly bad", value: 2 },
                    { text: "Very bad", value: 3 }
                ]
            },
            {
                text: "Overall, how would you rate your current mental wellness?",
                options: [
                    { text: "Excellent", value: 0 },
                    { text: "Good", value: 1 },
                    { text: "Fair", value: 2 },
                    { text: "Poor", value: 3 }
                ]
            }
        ];

        // Initialize assessment
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Assessment page loaded');
            document.getElementById('totalQuestions').textContent = questions.length;
            loadQuestion(0);
            
            // Add error handling for any uncaught errors
            window.addEventListener('error', function(e) {
                console.error('JavaScript error:', e.error);
            });
            
            // Add error handling for unhandled promise rejections
            window.addEventListener('unhandledrejection', function(e) {
                console.error('Unhandled promise rejection:', e.reason);
            });
        });

        function loadQuestion(index) {
            const question = questions[index];
            document.getElementById('questionText').textContent = question.text;
            document.getElementById('currentQuestion').textContent = index + 1;
            
            const progress = ((index + 1) / questions.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            
            const optionsContainer = document.getElementById('answerOptions');
            optionsContainer.innerHTML = '';
            
            question.options.forEach(option => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'answer-option';
                optionDiv.dataset.value = option.value;
                optionDiv.textContent = option.text;
                optionDiv.onclick = () => selectAnswer(optionDiv);
                
                // Restore previous selection if exists
                if (assessmentAnswers[index] === option.value) {
                    optionDiv.classList.add('selected');
                }
                
                optionsContainer.appendChild(optionDiv);
            });

            // Update navigation buttons
            document.getElementById('prevBtn').style.display = index > 0 ? 'block' : 'none';
            
            if (index === questions.length - 1) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'block';
            } else {
                document.getElementById('nextBtn').style.display = 'block';
                document.getElementById('submitBtn').style.display = 'none';
            }
        }

        function selectAnswer(element) {
            document.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            assessmentAnswers[currentQuestionIndex] = parseInt(element.dataset.value);
        }

        function nextQuestion() {
            if (assessmentAnswers[currentQuestionIndex] === undefined) {
                alert('Please select an answer before continuing.');
                return;
            }

            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                loadQuestion(currentQuestionIndex);
            }
        }

        function previousQuestion() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                loadQuestion(currentQuestionIndex);
            }
        }

        async function submitAssessment() {
            if (assessmentAnswers[currentQuestionIndex] === undefined) {
                alert('Please select an answer before submitting.');
                return;
            }

            if (isSubmitting) return;
            
            isSubmitting = true;
            setSubmitLoading(true);

            try {
                // Convert answers object to array for API
                const answersArray = Object.values(assessmentAnswers);
                console.log('Submitting answers:', answersArray);
                
                const response = await fetch('../api/assessment.php', {
                    method: 'POST',
                    credentials: 'same-origin', // Include session cookies
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'submit_assessment',
                        answers: answersArray
                    })
                });

                const data = await response.json();
                console.log('Assessment API response:', data);

                if (data.success) {
                    // Show results
                    document.getElementById('assessmentQuestions').style.display = 'none';
                    document.querySelector('.navigation-buttons').style.display = 'none';
                    document.querySelector('.progress-container').style.display = 'none';
                    
                    // Use the correct field names from API response
                    const program = data.results.recommended_program;
                    if (program) {
                        document.getElementById('programAssignment').innerHTML = `
                            <div class="program-assignment">
                                <h3>🎯 ${program.program_name}</h3>
                                <p>${program.program_description}</p>
                                <small style="opacity: 0.9;"><strong>Why this program:</strong> Based on your assessment results</small>
                            </div>
                        `;
                    } else {
                        document.getElementById('programAssignment').innerHTML = `
                            <div class="program-assignment">
                                <h3>🎯 Assessment Complete!</h3>
                                <p>Your wellness program has been assigned based on your responses.</p>
                                <small style="opacity: 0.9;"><strong>Wellness Score:</strong> ${data.results.wellness_score}%</small>
                            </div>
                        `;
                    }
                    
                    document.getElementById('resultsSection').classList.remove('hidden');
                } else {
                    console.error('Assessment submission failed:', data);
                    alert(data.message || 'Failed to submit assessment. Please try again.');
                }
            } catch (error) {
                console.error('Assessment submission error:', error);
                alert('Network error. Please check your connection and try again.');
            } finally {
                isSubmitting = false;
                setSubmitLoading(false);
            }
        }

        function setSubmitLoading(loading) {
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            const submitBtn = document.getElementById('submitBtn');
            
            if (loading) {
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                submitLoading.classList.remove('hidden');
            } else {
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                submitLoading.classList.add('hidden');
            }
        }

        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }
        
        function goToTasks() {
            window.location.href = 'tasks.php';
        }
        
        // Function to retake assessment
        function retakeAssessment() {
            if (confirm('Are you sure you want to retake the assessment? This will reassign your program based on your current needs.')) {
                // Reset assessment status and reload page
                fetch('../api/assessment.php?action=reset_assessment', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to show assessment form
                        window.location.reload();
                    } else {
                        alert('Error resetting assessment. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error resetting assessment. Please try again.');
                });
            }
        }
    </script>
    
    <!-- Assessment JavaScript is included inline above -->
</body>
</html>