const assessmentQuestions = [
    {
        id: 1,
        category: 'mood',
        question: 'Over the past two weeks, how often have you felt down, depressed, or hopeless?',
        options: [
            { text: 'Not at all', value: 0 },
            { text: 'Several days', value: 1 },
            { text: 'More than half the days', value: 2 },
            { text: 'Nearly every day', value: 3 }
        ]
    },
    {
        id: 2,
        category: 'anxiety',
        question: 'How often do you feel nervous, anxious, or on edge?',
        options: [
            { text: 'Not at all', value: 0 },
            { text: 'Several days', value: 1 },
            { text: 'More than half the days', value: 2 },
            { text: 'Nearly every day', value: 3 }
        ]
    },
    {
        id: 3,
        category: 'stress',
        question: 'How often do you feel overwhelmed by daily responsibilities?',
        options: [
            { text: 'Never', value: 0 },
            { text: 'Sometimes', value: 1 },
            { text: 'Often', value: 2 },
            { text: 'Always', value: 3 }
        ]
    },
    {
        id: 4,
        category: 'sleep',
        question: 'How would you rate your sleep quality over the past month?',
        options: [
            { text: 'Very good', value: 0 },
            { text: 'Fairly good', value: 1 },
            { text: 'Fairly bad', value: 2 },
            { text: 'Very bad', value: 3 }
        ]
    },
    {
        id: 5,
        category: 'overall',
        question: 'Overall, how would you rate your current mental wellness?',
        options: [
            { text: 'Excellent', value: 0 },
            { text: 'Good', value: 1 },
            { text: 'Fair', value: 2 },
            { text: 'Poor', value: 3 }
        ]
    }
];

let currentQuestionIndex = 0;
let answers = {};

// Initialize assessment
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('totalQuestions').textContent = assessmentQuestions.length;
    loadQuestion(currentQuestionIndex);
});

function loadQuestion(index) {
    const question = assessmentQuestions[index];
    const container = document.getElementById('assessmentQuestions');

    container.innerHTML = `
                <div class="question-card">
                    <div class="question-number">Question ${index + 1} of ${assessmentQuestions.length}</div>
                    <div class="question-text">${question.question}</div>
                    <div class="answer-options">
                        ${question.options.map((option, optionIndex) => `
                            <label class="answer-option" for="option_${optionIndex}">
                                <input type="radio" name="question_${question.id}" value="${option.value}" id="option_${optionIndex}">
                                ${option.text}
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;

    // Add click handlers for options
    const options = container.querySelectorAll('.answer-option');
    options.forEach(option => {
        option.addEventListener('click', function () {
            // Remove selected class from all options
            options.forEach(opt => opt.classList.remove('selected'));
            // Add selected class to clicked option
            this.classList.add('selected');
            // Check the radio button
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Restore previous answer if exists
    if (answers[question.id] !== undefined) {
        const savedOption = container.querySelector(`input[value="${answers[question.id]}"]`);
        if (savedOption) {
            savedOption.checked = true;
            savedOption.closest('.answer-option').classList.add('selected');
        }
    }

    updateProgress();
    updateNavigationButtons();
}

function updateProgress() {
    const progress = ((currentQuestionIndex + 1) / assessmentQuestions.length) * 100;
    document.getElementById('progressFill').style.width = progress + '%';
    document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    prevBtn.style.display = currentQuestionIndex > 0 ? 'block' : 'none';

    if (currentQuestionIndex === assessmentQuestions.length - 1) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
    } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
    }
}

function nextQuestion() {
    const currentQuestion = assessmentQuestions[currentQuestionIndex];
    const selectedOption = document.querySelector(`input[name="question_${currentQuestion.id}"]:checked`);

    if (!selectedOption) {
        alert('Please select an answer before continuing.');
        return;
    }

    // Save answer
    answers[currentQuestion.id] = parseInt(selectedOption.value);

    if (currentQuestionIndex < assessmentQuestions.length - 1) {
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

function submitAssessment() {
    const currentQuestion = assessmentQuestions[currentQuestionIndex];
    const selectedOption = document.querySelector(`input[name="question_${currentQuestion.id}"]:checked`);

    if (!selectedOption) {
        alert('Please select an answer before submitting.');
        return;
    }

    // Save final answer
    answers[currentQuestion.id] = parseInt(selectedOption.value);

    // Calculate results
    const results = calculateResults();
    displayResults(results);
}

function calculateResults() {
    const totalScore = Object.values(answers).reduce((sum, value) => sum + value, 0);
    const maxScore = assessmentQuestions.length * 3;
    const wellnessScore = Math.round(((maxScore - totalScore) / maxScore) * 100);

    // Categorize scores by area
    const categoryScores = {};
    assessmentQuestions.forEach(question => {
        if (!categoryScores[question.category]) {
            categoryScores[question.category] = [];
        }
        categoryScores[question.category].push(answers[question.id]);
    });

    // Generate recommendations
    const recommendations = generateRecommendations(wellnessScore, categoryScores);

    return {
        wellnessScore,
        totalScore,
        maxScore,
        categoryScores,
        recommendations
    };
}

function generateRecommendations(score, categoryScores) {
    const recommendations = [];

    // Analyze each category
    Object.entries(categoryScores).forEach(([category, scores]) => {
        const avgScore = scores.reduce((sum, score) => sum + score, 0) / scores.length;

        if (avgScore >= 2) {
            recommendations.push({
                category: category,
                level: 'high',
                title: getCategoryTitle(category),
                description: getHighPriorityRecommendation(category)
            });
        } else if (avgScore >= 1) {
            recommendations.push({
                category: category,
                level: 'medium',
                title: getCategoryTitle(category),
                description: getMediumPriorityRecommendation(category)
            });
        }
    });

    // Add general recommendations based on overall score
    if (score < 60) {
        recommendations.unshift({
            category: 'general',
            level: 'high',
            title: 'Immediate Support Recommended',
            description: 'Consider speaking with a mental health professional. Your responses indicate you may benefit from additional support.'
        });
    } else if (score < 80) {
        recommendations.unshift({
            category: 'general',
            level: 'medium',
            title: 'Focus on Self-Care',
            description: 'Implement regular self-care practices and consider joining our wellness programs to improve your mental health.'
        });
    }

    return recommendations;
}

function getCategoryTitle(category) {
    const titles = {
        mood: 'Mood Management',
        anxiety: 'Anxiety Support',
        sleep: 'Sleep Improvement',
        stress: 'Stress Management',
        social: 'Social Connection',
        energy: 'Energy & Vitality',
        concentration: 'Focus & Concentration',
        appetite: 'Healthy Habits',
        self_esteem: 'Self-Esteem Building',
        coping: 'Coping Skills',
        motivation: 'Motivation Enhancement',
        physical: 'Physical Wellness',
        future: 'Goal Setting',
        support: 'Support Network',
        habits: 'Lifestyle Changes',
        work_life: 'Work-Life Balance',
        emotional: 'Emotional Regulation',
        goals: 'Personal Development',
        resilience: 'Resilience Building',
        overall: 'Overall Wellness'
    };
    return titles[category] || 'Wellness Support';
}

function getHighPriorityRecommendation(category) {
    const recommendations = {
        mood: 'Consider mood tracking and professional counseling. Join our Emotional Balance program.',
        anxiety: 'Practice daily mindfulness and breathing exercises. Our Stress Management program can help.',
        sleep: 'Establish a consistent sleep routine. Consider our Sleep Wellness program.',
        stress: 'Learn stress management techniques and time management skills.',
        social: 'Focus on building meaningful connections. Join our Support Groups.',
        energy: 'Evaluate your nutrition, exercise, and sleep patterns.',
        concentration: 'Practice mindfulness and reduce distractions in your environment.',
        appetite: 'Consider speaking with a healthcare provider about eating patterns.',
        self_esteem: 'Work on self-compassion and positive self-talk exercises.',
        coping: 'Learn healthy coping strategies through our wellness programs.',
        motivation: 'Set small, achievable goals and celebrate progress.',
        physical: 'Address physical symptoms with relaxation techniques and exercise.',
        future: 'Work with a counselor or coach on goal-setting and planning.',
        support: 'Build your support network through community connections.',
        habits: 'Focus on one healthy habit change at a time.',
        work_life: 'Set boundaries and prioritize self-care activities.',
        emotional: 'Learn emotional regulation techniques and mindfulness.',
        goals: 'Clarify your values and create actionable plans.',
        resilience: 'Build resilience through stress management and support systems.',
        overall: 'Consider comprehensive wellness support and professional guidance.'
    };
    return recommendations[category] || 'Focus on this area for improved wellness.';
}

function getMediumPriorityRecommendation(category) {
    const recommendations = {
        mood: 'Practice daily gratitude and engage in activities you enjoy.',
        anxiety: 'Try relaxation techniques and regular exercise.',
        sleep: 'Improve sleep hygiene and create a calming bedtime routine.',
        stress: 'Practice time management and regular breaks.',
        social: 'Maintain existing relationships and be open to new connections.',
        energy: 'Ensure adequate rest and nutrition.',
        concentration: 'Take regular breaks and practice focus exercises.',
        appetite: 'Maintain regular meal times and mindful eating.',
        self_esteem: 'Practice positive affirmations and self-care.',
        coping: 'Continue developing healthy coping strategies.',
        motivation: 'Set realistic goals and track your progress.',
        physical: 'Incorporate regular exercise and relaxation.',
        future: 'Regularly review and adjust your goals.',
        support: 'Maintain your current support systems.',
        habits: 'Continue building positive lifestyle habits.',
        work_life: 'Maintain healthy boundaries and regular self-care.',
        emotional: 'Practice emotional awareness and expression.',
        goals: 'Regular goal review and adjustment.',
        resilience: 'Continue building your coping skills.',
        overall: 'Maintain your current wellness practices.'
    };
    return recommendations[category] || 'Continue focusing on this area.';
}

function displayResults(results) {
    // Hide assessment questions
    document.getElementById('assessmentQuestions').style.display = 'none';
    document.querySelector('.navigation-buttons').style.display = 'none';
    document.querySelector('.progress-container').style.display = 'none';

    // Assign program based on assessment
    const assignedProgram = assignProgramBasedOnAssessment(results);

    // Show results
    const resultsContainer = document.getElementById('resultsContainer');
    document.getElementById('scoreNumber').textContent = results.wellnessScore;

    // Display program assignment and recommendations
    const recommendationsList = document.getElementById('recommendationsList');
    recommendationsList.innerHTML = `
                <div class="program-assignment mb-4 p-3" style="background: linear-gradient(135deg, #5D87FF 0%, #49BEFF 100%); color: white; border-radius: 15px;">
                    <h4 class="mb-2">🎯 Your Assigned Program</h4>
                    <h5 class="mb-2">${assignedProgram.name}</h5>
                    <p class="mb-2" style="opacity: 0.9;">${assignedProgram.description}</p>
                    <small style="opacity: 0.8;"><strong>Why this program:</strong> ${assignedProgram.reason}</small>
                </div>
                ${results.recommendations.slice(0, 2).map(rec => `
                    <div class="recommendation-item category-${rec.level}">
                        <h5>${rec.title}</h5>
                        <p class="mb-0">${rec.description}</p>
                    </div>
                `).join('')}
            `;

    resultsContainer.style.display = 'block';

    // Save results to API
    fetch('../api/assessment.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'submit_assessment',
            answers: answers
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('healNestAssessmentCompleted', 'true');
            console.log('Assessment saved successfully:', data);
        } else {
            console.error('Failed to save assessment:', data.message);
        }
    })
    .catch(error => {
        console.error('Error saving assessment:', error);
    });
}

function assignProgramBasedOnAssessment(results) {
    const programs = {
        1: {
            id: 1,
            name: 'Mindfulness & Stress Relief',
            description: 'Daily meditation and stress management techniques to help you find inner peace.',
            reason: 'Your assessment shows elevated stress levels. This program will help you develop coping strategies.'
        },
        2: {
            id: 2,
            name: 'Emotional Balance & Resilience',
            description: 'Build emotional intelligence and resilience through targeted exercises and journaling.',
            reason: 'Your responses indicate mood fluctuations. This program focuses on emotional stability.'
        },
        3: {
            id: 3,
            name: 'Sleep Optimization & Recovery',
            description: 'Improve sleep quality and establish healthy sleep habits for better energy.',
            reason: 'Your assessment shows sleep-related concerns. Better sleep will improve your overall wellness.'
        },
        4: {
            id: 4,
            name: 'Anxiety Management & Coping',
            description: 'Learn evidence-based techniques to manage anxiety and build confidence.',
            reason: 'Your responses suggest anxiety symptoms. This program provides practical coping tools.'
        },
        5: {
            id: 5,
            name: 'Self-Confidence & Personal Growth',
            description: 'Build self-esteem and develop leadership skills through daily challenges.',
            reason: 'Your assessment indicates potential for growth. This program will boost your confidence.'
        }
    };

    // Assign program based on dominant issues from assessment
    const totalScore = Object.values(answers).reduce((sum, value) => sum + value, 0);

    if (answers[2] >= 2) return programs[4]; // High anxiety -> Anxiety Management
    if (answers[4] >= 2) return programs[3]; // Poor sleep -> Sleep Optimization  
    if (answers[1] >= 2) return programs[2]; // Depression/mood -> Emotional Balance
    if (answers[3] >= 2) return programs[1]; // High stress -> Mindfulness
    if (totalScore <= 5) return programs[5]; // Low total score -> Personal Growth

    return programs[1]; // Default to Mindfulness
}

function goToDashboard() {
    window.location.href = './dashboard.php';
}

// Prevent accidental page refresh
window.addEventListener('beforeunload', function (e) {
    if (Object.keys(answers).length > 0 && !localStorage.getItem('healNestAssessmentCompleted')) {
        e.preventDefault();
        e.returnValue = '';
    }
});