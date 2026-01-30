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
    <title>Wellness Assessment - HealNest</title>
    <base href="/HealNest/">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            line-height: 1.8;
            color: #2c2c2c;
            background: #fafafa;
        }

        /* Assessment Page */
        .assessment-page {
            min-height: 100vh;
            background: #ffffff;
        }

        .assessment-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .assessment-header {
            text-align: center;
            margin-bottom: 4rem;
            padding: 3rem 2rem;
            background: #fafafa;
            border-top: 3px solid #2c2c2c;
            border-bottom: 1px solid #f0f0f0;
        }

        .assessment-header h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3rem;
            font-weight: 300;
            color: #2c2c2c;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .assessment-header p {
            color: #666;
            font-size: 1.05rem;
            font-weight: 300;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* Progress bar - minimal and elegant */
        .progress-container {
            margin-bottom: 4rem;
            padding: 0 1rem;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-weight: 300;
            color: #666;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .progress-bar-custom {
            height: 2px;
            background: #f0f0f0;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: #2c2c2c;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Question card - spacious and calming */
        .question-card {
            background: #ffffff;
            padding: 4rem 3rem;
            margin-bottom: 3rem;
            border: 1px solid #f0f0f0;
            transition: all 0.6s ease;
        }

        .question-number {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            color: #999;
            margin-bottom: 1.5rem;
            letter-spacing: 1px;
            font-weight: 300;
        }

        .question-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            font-weight: 400;
            color: #2c2c2c;
            margin-bottom: 3rem;
            line-height: 1.5;
            letter-spacing: -0.3px;
        }

        /* Answer options - clean radio button style */
        .answer-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .answer-option {
            padding: 1.25rem 1.5rem;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #ffffff;
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .answer-option::before {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .answer-option:hover {
            border-color: #2c2c2c;
            background: #fafafa;
        }

        .answer-option:hover::before {
            border-color: #2c2c2c;
        }

        .answer-option.selected {
            border-color: #2c2c2c;
            background: #fafafa;
        }

        .answer-option.selected::before {
            border-color: #2c2c2c;
            background: #2c2c2c;
            box-shadow: inset 0 0 0 4px #fafafa;
        }

        .answer-text {
            flex: 1;
            font-size: 0.95rem;
            color: #2c2c2c;
            font-weight: 300;
        }

        /* Navigation - minimal buttons */
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3rem;
            padding: 0 1rem;
        }

        .btn-nav {
            padding: 1rem 2.5rem;
            border: none;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.4s ease;
            background: transparent;
            color: #2c2c2c;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .btn-prev {
            border: 1px solid #e0e0e0;
        }

        .btn-prev:hover {
            border-color: #2c2c2c;
            background: #fafafa;
        }

        .btn-next, .btn-submit {
            background: #2c2c2c;
            color: #ffffff;
        }

        .btn-next:hover, .btn-submit:hover {
            background: #8b7355;
        }

        .btn-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .btn-nav:disabled:hover {
            background: #2c2c2c;
            border-color: #e0e0e0;
        }

        /* Skip button for optional questions */
        .btn-skip {
            background: transparent;
            border: none;
            color: #999;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }

        .btn-skip:hover {
            color: #666;
        }

        .hidden {
            display: none;
        }

        /* Results section */
        .results-section {
            text-align: center;
            padding: 3rem 2rem;
        }

        .results-section h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.5rem;
            font-weight: 300;
            color: #2c2c2c;
            margin-bottom: 2rem;
        }

        .program-assignment {
            background: #fafafa;
            padding: 3rem;
            margin: 2rem 0;
            border-top: 3px solid #2c2c2c;
        }

        .program-assignment h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 400;
            color: #2c2c2c;
            margin-bottom: 1.5rem;
        }

        .program-assignment p {
            color: #666;
            font-weight: 300;
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto 1rem;
        }

        .program-assignment small {
            color: #999;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #2c2c2c;
            color: #ffffff;
            border: none;
            padding: 1rem 2.5rem;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.4s ease;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            margin: 1rem 0.5rem;
        }

        .btn-primary:hover {
            background: #8b7355;
        }

        .loading {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Breathing animation for question transitions */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Motivational quote between sections */
        .motivation-card {
            text-align: center;
            padding: 3rem 2rem;
            margin: 3rem 0;
            background: #fafafa;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .motivation-card blockquote {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 300;
            color: #2c2c2c;
            font-style: italic;
            margin-bottom: 1rem;
        }

        .motivation-card cite {
            font-size: 0.9rem;
            color: #999;
            font-style: normal;
            letter-spacing: 1px;
        }

        /* Already completed section */
        .completed-section {
            margin-top: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .divider-section {
            margin: 2rem 0;
            padding-top: 2rem;
            border-top: 1px solid #f0f0f0;
        }

        .divider-section p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .assessment-container {
                padding: 2rem 1.5rem;
            }

            .assessment-header {
                padding: 2rem 1.5rem;
                margin-bottom: 3rem;
            }

            .assessment-header h1 {
                font-size: 2.2rem;
            }

            .question-card {
                padding: 2.5rem 2rem;
            }

            .question-text {
                font-size: 1.4rem;
            }

            .navigation-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-nav {
                width: 100%;
            }

            .btn-prev {
                order: 2;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-primary {
                width: 100%;
            }
        }

        /* Section divider for long assessments */
        .section-divider {
            text-align: center;
            margin: 4rem 0;
            padding: 2rem 0;
            border-top: 1px solid #f0f0f0;
        }

        .section-divider h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.5rem;
            font-weight: 400;
            color: #2c2c2c;
            margin-bottom: 0.5rem;
        }

        .section-divider p {
            color: #999;
            font-size: 0.9rem;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <div class="assessment-page">
        <div class="assessment-container">
            <?php if ($has_program && $assessment_taken): ?>
                <!-- User already has a program assigned -->
                <div class="assessment-header">
                    <h1>Assessment Complete</h1>
                    <p>You have completed your wellness assessment and have been assigned a personalized program.</p>
                </div>
                
                <div class="question-card results-section">
                    <div class="program-assignment">
                        <h3>Your Current Program</h3>
                        <p>You are currently enrolled in your personalized wellness journey.</p>
                        <p><small>Program started: <?php echo date('F j, Y', strtotime($user['program_start_date'])); ?></small></p>
                    </div>
                    
                    <div class="completed-section">
                        <h4 style="font-family: 'Cormorant Garamond', serif; font-weight: 400; margin-bottom: 1rem;">Continue Your Journey</h4>
                        <div class="action-buttons">
                            <button class="btn-primary" onclick="window.location.href='./dashboard.php'">
                                View Dashboard
                            </button>
                            <button class="btn-primary" onclick="window.location.href='./program.php'">
                                My Program
                            </button>
                            <button class="btn-primary" onclick="window.location.href='./tasks.php'">
                                Today's Tasks
                            </button>
                        </div>
                        
                        <div class="divider-section">
                            <p>Want to retake the assessment? This will reassign your program based on your current needs.</p>
                            <button class="btn-nav btn-prev" onclick="retakeAssessment()" style="margin-top: 1rem;">
                                Retake Assessment
                            </button>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- User needs to take assessment -->
                <div class="assessment-header">
                    <h1>Wellness Assessment</h1>
                    <p>Take a moment to reflect on your current state. This thoughtful assessment will help us create a personalized path for your wellness journey.</p>
                </div>

                <div class="progress-container">
                    <div class="progress-info">
                        <span>Question <span id="currentQuestion">1</span> of <span id="totalQuestions">15</span></span>
                        <span id="progressPercent">7%</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" id="progressFill" style="width: 7%"></div>
                    </div>
                </div>

                <div id="assessmentQuestions">
                    <div class="question-card">
                        <div class="question-number" id="questionCategory">Emotional Wellbeing</div>
                        <div class="question-text" id="questionText">Loading question...</div>
                        <div class="answer-options" id="answerOptions">
                            <!-- Options will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="navigation-buttons">
                    <button class="btn-nav btn-prev" id="prevBtn" onclick="previousQuestion()" style="display: none;">Previous</button>
                    <div style="display: flex; gap: 1rem;">
                        <button class="btn-nav btn-next" id="nextBtn" onclick="nextQuestion()">Continue</button>
                        <button class="btn-nav btn-submit" id="submitBtn" onclick="submitAssessment()" style="display: none;">
                            <span id="submitText">Complete Assessment</span>
                            <span id="submitLoading" class="loading hidden"></span>
                        </button>
                    </div>
                </div>

                <div id="resultsSection" class="hidden">
                    <div class="question-card results-section">
                        <h2>Assessment Complete</h2>
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

        // Expanded Assessment Questions - organized by category
        const questions = [
            // Emotional Wellbeing (5 questions)
            {
                category: "Emotional Wellbeing",
                text: "Over the past two weeks, how often have you felt down, depressed, or hopeless?",
                options: [
                    { text: "Not at all", value: 0 },
                    { text: "Several days", value: 1 },
                    { text: "More than half the days", value: 2 },
                    { text: "Nearly every day", value: 3 }
                ]
            },
            {
                category: "Emotional Wellbeing",
                text: "How often do you feel joy or contentment in your daily life?",
                options: [
                    { text: "Very often", value: 0 },
                    { text: "Sometimes", value: 1 },
                    { text: "Rarely", value: 2 },
                    { text: "Almost never", value: 3 }
                ]
            },
            {
                category: "Emotional Wellbeing",
                text: "How comfortable are you expressing your emotions to others?",
                options: [
                    { text: "Very comfortable", value: 0 },
                    { text: "Somewhat comfortable", value: 1 },
                    { text: "Somewhat uncomfortable", value: 2 },
                    { text: "Very uncomfortable", value: 3 }
                ]
            },
            
            // Anxiety & Stress (4 questions)
            {
                category: "Anxiety & Stress",
                text: "How often do you feel nervous, anxious, or on edge?",
                options: [
                    { text: "Not at all", value: 0 },
                    { text: "Several days", value: 1 },
                    { text: "More than half the days", value: 2 },
                    { text: "Nearly every day", value: 3 }
                ]
            },
            {
                category: "Anxiety & Stress",
                text: "How often do you feel overwhelmed by daily responsibilities?",
                options: [
                    { text: "Never", value: 0 },
                    { text: "Sometimes", value: 1 },
                    { text: "Often", value: 2 },
                    { text: "Always", value: 3 }
                ]
            },
            {
                category: "Anxiety & Stress",
                text: "How well do you handle unexpected changes or challenges?",
                options: [
                    { text: "Very well", value: 0 },
                    { text: "Fairly well", value: 1 },
                    { text: "Not very well", value: 2 },
                    { text: "Poorly", value: 3 }
                ]
            },
            
            // Sleep & Rest (3 questions)
            {
                category: "Sleep & Rest",
                text: "How would you rate your sleep quality over the past month?",
                options: [
                    { text: "Very good", value: 0 },
                    { text: "Fairly good", value: 1 },
                    { text: "Fairly poor", value: 2 },
                    { text: "Very poor", value: 3 }
                ]
            },
            {
                category: "Sleep & Rest",
                text: "How often do you wake up feeling rested and refreshed?",
                options: [
                    { text: "Always", value: 0 },
                    { text: "Often", value: 1 },
                    { text: "Sometimes", value: 2 },
                    { text: "Rarely or never", value: 3 }
                ]
            },
            {
                category: "Sleep & Rest",
                text: "How often do you have trouble falling or staying asleep?",
                options: [
                    { text: "Never", value: 0 },
                    { text: "Occasionally", value: 1 },
                    { text: "Frequently", value: 2 },
                    { text: "Almost always", value: 3 }
                ]
            },
            
            // Social Connection (3 questions)
            {
                category: "Social Connection",
                text: "How satisfied are you with your social relationships?",
                options: [
                    { text: "Very satisfied", value: 0 },
                    { text: "Somewhat satisfied", value: 1 },
                    { text: "Somewhat dissatisfied", value: 2 },
                    { text: "Very dissatisfied", value: 3 }
                ]
            },
            {
                category: "Social Connection",
                text: "How often do you feel lonely or isolated?",
                options: [
                    { text: "Never", value: 0 },
                    { text: "Sometimes", value: 1 },
                    { text: "Often", value: 2 },
                    { text: "Always", value: 3 }
                ]
            },
            {
                category: "Social Connection",
                text: "Do you have someone you can talk to when you need support?",
                options: [
                    { text: "Yes, several people", value: 0 },
                    { text: "Yes, at least one person", value: 1 },
                    { text: "Maybe, but I'm not sure", value: 2 },
                    { text: "No, not really", value: 3 }
                ]
            },
            
            // Self-Care & Mindfulness (2 questions)
            {
                category: "Self-Care & Mindfulness",
                text: "How often do you engage in activities that bring you peace or joy?",
                options: [
                    { text: "Daily", value: 0 },
                    { text: "Several times a week", value: 1 },
                    { text: "Occasionally", value: 2 },
                    { text: "Rarely or never", value: 3 }
                ]
            },
            {
                category: "Self-Care & Mindfulness",
                text: "How much time do you dedicate to self-care and personal wellness?",
                options: [
                    { text: "Plenty of time", value: 0 },
                    { text: "Some time", value: 1 },
                    { text: "Very little time", value: 2 },
                    { text: "No time at all", value: 3 }
                ]
            },
            
            // Overall Wellness (2 questions)
            {
                category: "Overall Wellness",
                text: "Overall, how would you rate your current mental wellness?",
                options: [
                    { text: "Excellent", value: 0 },
                    { text: "Good", value: 1 },
                    { text: "Fair", value: 2 },
                    { text: "Poor", value: 3 }
                ]
            },
            {
                category: "Overall Wellness",
                text: "How motivated do you feel to improve your mental wellness?",
                options: [
                    { text: "Very motivated", value: 0 },
                    { text: "Somewhat motivated", value: 1 },
                    { text: "A little motivated", value: 2 },
                    { text: "Not motivated", value: 3 }
                ]
            }
        ];

        // Motivational quotes to show at intervals
        const motivationalQuotes = [
            { quote: "Peace comes from within. Do not seek it without.", author: "BUDDHA" },
            { quote: "The present moment is filled with joy and happiness. If you are attentive, you will see it.", author: "THÍCH NHẤT HẠNH" },
            { quote: "Self-care is not selfish. You cannot serve from an empty vessel.", author: "ELEANOR BROWN" },
            { quote: "You are not a drop in the ocean. You are the entire ocean in a drop.", author: "RUMI" }
        ];

        // Initialize assessment
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Assessment page loaded');
            document.getElementById('totalQuestions').textContent = questions.length;
            loadQuestion(0);
            
            window.addEventListener('error', function(e) {
                console.error('JavaScript error:', e.error);
            });
            
            window.addEventListener('unhandledrejection', function(e) {
                console.error('Unhandled promise rejection:', e.reason);
            });
        });

        function loadQuestion(index) {
            const question = questions[index];
            
            // Show motivational quote every 5 questions (except first)
            if (index > 0 && index % 5 === 0) {
                showMotivationalBreak(index);
                return;
            }
            
            document.getElementById('questionCategory').textContent = question.category;
            document.getElementById('questionText').textContent = question.text;
            document.getElementById('currentQuestion').textContent = index + 1;
            
            const progress = ((index + 1) / questions.length) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('progressPercent').textContent = Math.round(progress) + '%';
            
            const optionsContainer = document.getElementById('answerOptions');
            optionsContainer.innerHTML = '';
            
            question.options.forEach(option => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'answer-option';
                optionDiv.dataset.value = option.value;
                
                const textSpan = document.createElement('span');
                textSpan.className = 'answer-text';
                textSpan.textContent = option.text;
                optionDiv.appendChild(textSpan);
                
                optionDiv.onclick = () => selectAnswer(optionDiv);
                
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

        function showMotivationalBreak(index) {
            const quoteIndex = Math.floor(index / 5) - 1;
            const quote = motivationalQuotes[quoteIndex % motivationalQuotes.length];
            
            document.getElementById('questionCategory').textContent = 'Take a Breath';
            document.getElementById('questionText').innerHTML = `
                <div class="motivation-card" style="padding: 2rem 0;">
                    <blockquote>"${quote.quote}"</blockquote>
                    <cite>— ${quote.author}</cite>
                </div>
                <p style="color: #666; font-size: 1rem; margin-top: 2rem;">You're doing great. Let's continue.</p>
            `;
            
            const optionsContainer = document.getElementById('answerOptions');
            optionsContainer.innerHTML = '';
            
            // Check if this is the last question
            if (index === questions.length - 1) {
                document.getElementById('nextBtn').style.display = 'none';
                document.getElementById('submitBtn').style.display = 'block';
            } else {
                document.getElementById('nextBtn').style.display = 'block';
                document.getElementById('submitBtn').style.display = 'none';
            }
            document.getElementById('prevBtn').style.display = 'block';
        }

        function selectAnswer(element) {
            document.querySelectorAll('.answer-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            assessmentAnswers[currentQuestionIndex] = parseInt(element.dataset.value);
        }

        function nextQuestion() {
            // Check if current question is a motivational break - skip validation
            const isMotivationalBreak = currentQuestionIndex > 0 && currentQuestionIndex % 5 === 0;
            
            if (!isMotivationalBreak && assessmentAnswers[currentQuestionIndex] === undefined) {
                alert('Please select an answer to continue.');
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
                // Skip motivational breaks when going back
                if (currentQuestionIndex > 0 && currentQuestionIndex % 5 === 0) {
                    currentQuestionIndex--;
                }
                loadQuestion(currentQuestionIndex);
            }
        }

        async function submitAssessment() {
            // Check if current question is a motivational break - skip validation
            const isMotivationalBreak = currentQuestionIndex > 0 && currentQuestionIndex % 5 === 0;
            
            if (!isMotivationalBreak && assessmentAnswers[currentQuestionIndex] === undefined) {
                alert('Please select an answer before submitting.');
                return;
            }

            if (isSubmitting) return;
            
            isSubmitting = true;
            setSubmitLoading(true);

            try {
                const answersArray = Object.values(assessmentAnswers);
                console.log('Submitting answers:', answersArray);
                
                const response = await fetch('api/assessment.php', {
                    method: 'POST',
                    credentials: 'same-origin',
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
                    document.getElementById('assessmentQuestions').style.display = 'none';
                    document.querySelector('.navigation-buttons').style.display = 'none';
                    document.querySelector('.progress-container').style.display = 'none';
                    
                    const program = data.results.recommended_program;
                    if (program) {
                        document.getElementById('programAssignment').innerHTML = `
                            <div class="program-assignment">
                                <h3>${program.program_name}</h3>
                                <p>${program.program_description}</p>
                                <small>Personalized based on your responses</small>
                            </div>
                        `;
                    } else {
                        document.getElementById('programAssignment').innerHTML = `
                            <div class="program-assignment">
                                <h3>Your Journey Begins</h3>
                                <p>Your personalized wellness program has been created based on your responses.</p>
                                <small>Wellness Score: ${data.results.wellness_score}%</small>
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
        
        function retakeAssessment() {
            if (confirm('Are you sure you want to retake the assessment? This will reassign your program based on your current needs.')) {
                fetch('api/assessment.php?action=reset_assessment', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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
</body>
</html>