// HealNest Programs Database

const PROGRAMS = [
    {
        id: 1,
        name: 'Intensive Support',
        description: 'A 14-day focused program to help you manage stress and build healthy coping mechanisms.',
        duration: 14,
        difficulty: 'Beginner',
        tasks: [
            {
                day: 1,
                title: 'Start Your Journal',
                description: 'Begin journaling about your feelings and what brought you here today. Write freely for 10 minutes.'
            },
            {
                day: 2,
                title: 'Breathing Exercise',
                description: 'Practice the 4-7-8 breathing technique: Inhale for 4, hold for 7, exhale for 8. Repeat 5 times.'
            },
            {
                day: 3,
                title: 'Identify Stressors',
                description: 'Write down 3 things that stress you most. For each, write one small action you can take.'
            },
            {
                day: 4,
                title: 'Mindful Walking',
                description: 'Take a 10-minute walk focusing on your surroundings. Notice 5 things you see, 4 you hear, 3 you feel.'
            },
            {
                day: 5,
                title: 'Evening Reflection',
                description: 'Spend 5 minutes journaling about one positive moment from your day.'
            },
            {
                day: 6,
                title: 'Progressive Relaxation',
                description: 'Tense and release each muscle group for 2 seconds. Start from toes, move to head. 10 minutes total.'
            },
            {
                day: 7,
                title: 'Weekly Review',
                description: 'Reflect on your week. What helped? What was difficult? Write your insights.'
            },
            {
                day: 8,
                title: 'Gratitude Practice',
                description: 'Write 5 things you\'re grateful for, no matter how small. Feel the gratitude.'
            },
            {
                day: 9,
                title: 'Body Scan Meditation',
                description: 'Close your eyes and mentally scan your body from top to bottom. Notice tension. Breathe into it.'
            },
            {
                day: 10,
                title: 'Emotional Check-in',
                description: 'Rate your stress level daily. Track patterns. What made today better or worse?'
            },
            {
                day: 11,
                title: 'Self-Compassion',
                description: 'Write a compassionate letter to yourself. Be kind. You\'re doing your best.'
            },
            {
                day: 12,
                title: 'Creative Release',
                description: 'Draw, paint, or write poetry for 15 minutes. No judgment. Let emotions flow.'
            },
            {
                day: 13,
                title: 'Connect & Share',
                description: 'Reach out to someone you trust. Share how you\'ve been feeling. Connection heals.'
            },
            {
                day: 14,
                title: 'Celebrate Progress',
                description: 'Review your journal entries from day 1. Celebrate how far you\'ve come. You did it!'
            }
        ]
    },
    {
        id: 2,
        name: 'Stress Management',
        description: 'A 21-day program to build sustainable stress management habits and improve daily resilience.',
        duration: 21,
        difficulty: 'Intermediate',
        tasks: [
            {
                day: 1,
                title: 'Foundation: Morning Meditation',
                description: 'Start your day with a 5-minute guided meditation. Set your intention for the day.'
            },
            {
                day: 2,
                title: 'Identify Your Triggers',
                description: 'List 5 stress triggers. For each, brainstorm 2 coping strategies you can use.'
            },
            {
                day: 3,
                title: 'Healthy Boundaries',
                description: 'Practice saying "no" to one non-essential request. Your time and energy matter.'
            },
            {
                day: 4,
                title: 'Movement Break',
                description: 'Do 10 minutes of light exercise (walk, stretch, dance). Notice how your body feels.'
            },
            {
                day: 5,
                title: 'Mindful Eating',
                description: 'Eat one meal slowly, without distractions. Notice textures, flavors, sensations.'
            },
            {
                day: 6,
                title: 'Sleep Hygiene',
                description: 'Create a bedtime routine. No screens 30 min before sleep. Journal any worries.'
            },
            {
                day: 7,
                title: 'Weekly Assessment',
                description: 'Rate your stress (1-10) each day. Identify your best day and why it was better.'
            },
            {
                day: 8,
                title: 'Social Connection',
                description: 'Spend time with someone who uplifts you. Quality conversation reduces stress.'
            },
            {
                day: 9,
                title: 'Cognitive Reframing',
                description: 'Take one worry. Reframe it as a challenge you can face. What\'s one positive outcome?'
            },
            {
                day: 10,
                title: 'Nature Time',
                description: 'Spend 15 minutes in nature. Observe plants, animals, sky. Feel the calm.'
            },
            {
                day: 11,
                title: 'Self-Care Priority',
                description: 'Do one activity purely for joy (music, art, hobby). Guilt-free self-care matters.'
            },
            {
                day: 12,
                title: 'Breathing Mastery',
                description: 'Learn box breathing: 4 counts in, 4 hold, 4 out, 4 hold. Practice 3 times.'
            },
            {
                day: 13,
                title: 'Limit Stressors',
                description: 'Reduce time with draining people or activities. Protect your mental space.'
            },
            {
                day: 14,
                title: 'Midpoint Reflection',
                description: 'You\'re halfway! Reflect on growth. What habits are sticking? What needs adjustment?'
            },
            {
                day: 15,
                title: 'Journaling Deep Dive',
                description: 'Write about your biggest win this week. How did you handle stress differently?'
            },
            {
                day: 16,
                title: 'Tech Detox Hour',
                description: 'No screens for 1 hour. Read, create, rest. Notice the difference in your stress.'
            },
            {
                day: 17,
                title: 'Positive Affirmations',
                description: 'Create 3 personal affirmations. Repeat them daily. "I am capable of handling stress."'
            },
            {
                day: 18,
                title: 'Progressive Challenge',
                description: 'Face one small stressor head-on. You\'re stronger than you think.'
            },
            {
                day: 19,
                title: 'Creative Expression',
                description: 'Create art, music, or writing about your stress journey. Let it out.'
            },
            {
                day: 20,
                title: 'Community & Support',
                description: 'Share your stress management success with someone. You might inspire them.'
            },
            {
                day: 21,
                title: 'Celebration & Commitment',
                description: 'You completed 21 days! Commit to one stress-management habit you\'ll keep forever.'
            }
        ]
    },
    {
        id: 3,
        name: 'Balance & Focus',
        description: 'A 30-day program to improve focus, productivity, and emotional balance in your daily life.',
        duration: 30,
        difficulty: 'Intermediate',
        tasks: [
            {
                day: 1,
                title: 'Set Your Goals',
                description: 'Define 3 goals for this program. Make them specific and achievable.'
            },
            {
                day: 2,
                title: 'Pomodoro Technique',
                description: 'Work for 25 minutes, rest for 5. Repeat 4 times, then take a long break.'
            },
            {
                day: 3,
                title: 'Distraction Audit',
                description: 'List your biggest focus killers. Plan to eliminate or reduce them.'
            },
            {
                day: 4,
                title: 'Morning Ritual',
                description: 'Create a 10-minute morning routine before starting work. Set the tone.'
            },
            {
                day: 5,
                title: 'Focus Space Setup',
                description: 'Organize your workspace. Clean, minimal, inspiring. Your space affects focus.'
            },
            {
                day: 6,
                title: 'Energy Management',
                description: 'Schedule important tasks during your peak energy hours. Work smarter.'
            },
            {
                day: 7,
                title: 'Week 1 Review',
                description: 'What worked? What didn\'t? Adjust your strategies for week 2.'
            },
            {
                day: 8,
                title: 'Digital Boundaries',
                description: 'Disable notifications during focus time. Be intentional with technology.'
            },
            {
                day: 9,
                title: 'Break Ritual',
                description: 'During breaks: stretch, hydrate, look away from screen. Physical reset.'
            },
            {
                day: 10,
                title: 'Emotional Balance Check',
                description: 'How are you feeling? Connect emotions to productivity. Treat yourself well.'
            },
            {
                day: 11,
                title: 'Single-Tasking',
                description: 'Do ONE thing at a time. Multitasking is a myth. Quality over quantity.'
            },
            {
                day: 12,
                title: 'Mindful Transition',
                description: '2-minute meditation between tasks. Clear your mind. Fresh start each time.'
            },
            {
                day: 13,
                title: 'Celebrate Small Wins',
                description: 'Acknowledge every completed task. Progress compounds.'
            },
            {
                day: 14,
                title: 'Midpoint Momentum',
                description: 'Halfway through! Track productivity improvements. You\'re building momentum.'
            },
            {
                day: 15,
                title: 'Learn From Setbacks',
                description: 'Had a distracted day? That\'s ok. What triggered it? Plan to prevent it.'
            },
            {
                day: 16,
                title: 'Accountability Partner',
                description: 'Share your focus goals with someone. Check in daily. Community drives success.'
            },
            {
                day: 17,
                title: 'Advanced Breathing',
                description: 'Try resonance breathing: 5 sec in, 5 sec out. Enhances focus and clarity.'
            },
            {
                day: 18,
                title: 'Energy Peak Hour',
                description: 'Track when you\'re most focused. Protect that time fiercely.'
            },
            {
                day: 19,
                title: 'Deep Work Session',
                description: '90-minute deep work block with no interruptions. You can do this.'
            },
            {
                day: 20,
                title: 'Reflection & Adjust',
                description: 'Reflect on day 20 focus. What new habits are solidifying?'
            },
            {
                day: 21,
                title: 'Week 3 Completion',
                description: 'You\'ve built 3 weeks of focus habits. That\'s incredible progress.'
            },
            {
                day: 22,
                title: 'Emotional Resilience',
                description: 'Difficult day? Practice bouncing back. Resilience is a skill you\'re building.'
            },
            {
                day: 23,
                title: 'Creative Focus',
                description: 'One focused hour on something creative. Let your mind soar.'
            },
            {
                day: 24,
                title: 'Balance Check',
                description: 'Are you balancing focus with rest? Sustainable success requires both.'
            },
            {
                day: 25,
                title: 'Advanced Technique',
                description: 'Try timeboxing: assign time limits to tasks. Control and completion.'
            },
            {
                day: 26,
                title: 'Gratitude for Focus',
                description: 'Grateful for your improving focus. How has it changed your life?'
            },
            {
                day: 27,
                title: 'Social Battery',
                description: 'Connection is energy. Positive social interaction enhances overall balance.'
            },
            {
                day: 28,
                title: 'Peak Performance Day',
                description: 'Use everything you\'ve learned. This is your day to shine.'
            },
            {
                day: 29,
                title: 'Plan for Success',
                description: 'How will you maintain these habits? Create your long-term strategy.'
            },
            {
                day: 30,
                title: 'You Did It!',
                description: 'Completed 30 days of building balance & focus. This is your new normal.'
            }
        ]
    },
    {
        id: 4,
        name: 'Optimization',
        description: 'A 30-day advanced program for those already on their wellness journey seeking continuous improvement.',
        duration: 30,
        difficulty: 'Advanced',
        tasks: [
            {
                day: 1,
                title: 'Advanced Goal Setting',
                description: 'Set SMART goals for personal optimization. Specific, measurable, time-bound.'
            },
            {
                day: 2,
                title: 'Habit Stacking',
                description: 'Link new positive habits to existing ones. "After coffee, I meditate."'
            },
            {
                day: 3,
                title: 'Energy Optimization',
                description: 'Deep analysis of your energy patterns. When do you thrive? Protect those times.'
            },
            {
                day: 4,
                title: 'Mindfulness Upgrade',
                description: 'Advance to 15-minute meditation. Deeper states of calm and clarity.'
            },
            {
                day: 5,
                title: 'Nutrition & Wellness',
                description: 'One day of optimized nutrition. Notice the mental clarity boost.'
            },
            {
                day: 6,
                title: 'Sleep Optimization',
                description: 'Advanced sleep protocol. Track sleep quality. Aim for 7-9 hours consistently.'
            },
            {
                day: 7,
                title: 'Week 1 Data Review',
                description: 'Analyze your data. Identify patterns. What\'s working? Optimize further.'
            },
            {
                day: 8,
                title: 'Physical Practice',
                description: 'Yoga or tai chi for 20 minutes. Body-mind integration at its finest.'
            },
            {
                day: 9,
                title: 'Cognitive Training',
                description: 'Challenge your mind. Learn something new. Neuroplasticity in action.'
            },
            {
                day: 10,
                title: 'Emotional Mastery',
                description: 'Advanced emotional awareness. Name emotions without judgment. Observe patterns.'
            },
            {
                day: 11,
                title: 'Social Optimization',
                description: 'Evaluate relationships. Spend time with people who elevate you.'
            },
            {
                day: 12,
                title: 'Digital Wellness',
                description: 'Audit technology use. Delete apps that don\'t serve you. Intentional only.'
            },
            {
                day: 13,
                title: 'Creative Flow',
                description: 'Aim for deep flow state. 90 minutes on something meaningful to you.'
            },
            {
                day: 14,
                title: 'Midpoint Elevation',
                description: 'Halfway! You\'re operating at a higher level now. Acknowledge the shift.'
            },
            {
                day: 15,
                title: 'Stress Immunity',
                description: 'Advanced stress management. Build mental resilience to handle challenges.'
            },
            {
                day: 16,
                title: 'Purpose Alignment',
                description: 'Are your daily actions aligned with your purpose? Adjust if needed.'
            },
            {
                day: 17,
                title: 'Advanced Breathing',
                description: 'Wim Hof breathing technique. Powerful mind-body connection.'
            },
            {
                day: 18,
                title: 'Peak Performance',
                description: 'Operating at your peak. What does it feel like? How can you sustain it?'
            },
            {
                day: 19,
                title: 'Teach Others',
                description: 'Share your wellness journey with someone. Teaching reinforces mastery.'
            },
            {
                day: 20,
                title: 'Advanced Reflection',
                description: 'Deep journaling. How has your consciousness shifted? What have you learned?'
            },
            {
                day: 21,
                title: 'Week 3 Integration',
                description: 'These practices are becoming second nature. You\'re transformed.'
            },
            {
                day: 22,
                title: 'Intuition Development',
                description: 'Trust your gut. Make one decision intuitively. Feel the difference.'
            },
            {
                day: 23,
                title: 'Advanced Gratitude',
                description: 'Deep gratitude practice. Feel genuine appreciation for everything.'
            },
            {
                day: 24,
                title: 'Continuous Learning',
                description: 'Read or listen to content on personal development. Never stop growing.'
            },
            {
                day: 25,
                title: 'Relationship Optimization',
                description: 'Deepen one important relationship. Vulnerability and authenticity.'
            },
            {
                day: 26,
                title: 'Legacy Building',
                description: 'How do your optimized practices serve others? What impact are you having?'
            },
            {
                day: 27,
                title: 'Advanced Creativity',
                description: 'Create something from your heart. Art, music, writing, or movement.'
            },
            {
                day: 28,
                title: 'Peak Day Planning',
                description: 'Design your ultimate optimal day. Live it today.'
            },
            {
                day: 29,
                title: 'Future Vision',
                description: 'Visualize yourself in 6 months after sustaining these practices.'
            },
            {
                day: 30,
                title: 'Mastery Achieved',
                description: 'You\'ve reached a new level. This isn\'t the end—it\'s the beginning.'
            }
        ]
    }
];
