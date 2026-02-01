# HealNest - Youth Empowerment & Mental Wellness Platform

## 🌟 Project Overview

HealNest is a comprehensive mental health and youth empowerment platform designed to support young people in their journey toward mental wellness, personal growth, and positive community impact. Built with modern web technologies, it provides an intuitive interface for mental health assessments, wellness programs, mood tracking, journaling, and personalized support.

## 📋 Features

### 🔐 Authentication
- **Sign Up/Register**: Create new user accounts with email validation
- **Sign In/Login**: Secure login with session management
- **Password Strength Indicator**: Real-time feedback on password security
- **Session Management**: Automatic logout and session timeout

### 📊 Mental Health Assessment
- Comprehensive questionnaire to evaluate mental wellness
- Evidence-based assessment criteria
- Personalized recommendations based on results
- Score tracking and progress monitoring

### 🎯 Wellness Programs
- Curated wellness programs tailored for youth
- Programs include:
  - Stress Management
  - Emotional Intelligence
  - Sleep Improvement
  - Mindfulness & Meditation
  - Leadership Development
  - Creative Expression
  - Support Groups
- Program enrollment tracking

### 😊 Mood Tracker
- Daily mood logging with optional notes
- Visual mood calendar display
- Mood statistics and trends
- Historical mood data analysis
- Emoji-based mood selection

### 📔 Digital Journal
- Personal journaling with rich text support
- Automatic tag extraction from entries
- Entry organization and search functionality
- Mood association with journal entries
- Private and secure note-taking

### 👤 User Profile
- Complete profile management
- Personal information storage
- Avatar/profile picture upload
- Progress tracking dashboard
- Achievement badges

### 📈 Dashboard
- Personal wellness overview
- Quick statistics and metrics
- Recent activity timeline
- Recommended next steps
- Progress visualization

## 🛠️ Technology Stack

### Frontend
- **PHP**: Hypertext Preprocessor
- **HTML5**: Semantic markup structure
- **CSS3**: Modern styling with CSS variables, gradients, and animations
- **JavaScript (ES6+)**: Dynamic functionality and interactivity
- **Bootstrap 5**: Responsive grid and components
- **Calm Clarity UI**: Pre-built component library

## 📁 Project Structure

```
# File Tree: HealNest

**Root Path:** `/Applications/XAMPP/xamppfiles/htdocs/HealNest`

```
├── 📁 api
│   ├── ⚙️ .htaccess
│   ├── 🐘 assessment.php
│   ├── 🐘 check_session.php
│   ├── 🐘 dashboard.php
│   ├── 🐘 journal.php
│   ├── 🐘 login.php
│   ├── 🐘 logout.php
│   ├── 🐘 mood.php
│   ├── 🐘 profile.php
│   ├── 🐘 program.php
│   └── 🐘 register.php

├── 📁 config
│   └── 🐘 connect.php
├── 📁 css
│   ├── 🎨 assessment.css
│   ├── 🎨 dashboard.css
│   ├── 🎨 journal.css
│   ├── 🎨 login.css
│   ├── 🎨 mood.css
│   ├── 🎨 profile.css
│   ├── 🎨 program.css
│   ├── 🎨 register.css
│   └── 🎨 tasks.css
├── 📁 db
│   └── 📄 healnest_db.sql
├── 📁 docs
│   └── 📝 README.md
├── 📁 js
│   ├── 📄 assessment.js
│   ├── 📄 auth.js
│   ├── 📄 dashboard.js
│   ├── 📄 journal-utils.js
│   ├── 📄 journal.js
│   ├── 📄 login.js
│   ├── 📄 mood.js
│   ├── 📄 profile.js
│   ├── 📄 program.js
│   ├── 📄 register.js
│   └── 📄 tasks.js
├── 📁 pages
│   ├── 🐘 assessment.php
│   ├── 🐘 auth-choice.php
│   ├── 🐘 dashboard.php
│   ├── 🐘 journal.php
│   ├── 🐘 login.php
│   ├── 🐘 logout.php
│   ├── 🐘 mood.php
│   ├── 🐘 profile.php
│   ├── 🐘 program.php
│   ├── 🐘 register.php
│   └── 🐘 tasks.php
├── 📁 whatsapp
├── 🐘 admin_api.php
├── 🐘 admin_panel.php
├── 🌐 index.html
└── 🐘 logout.php
```

## 🚀 Getting Started

### Prerequisites
- PHP 7.0 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Clone/Extract Project**
   ```bash
   # Extract the project files to your web directory
   cd /path/to/HealNest
   ```

2. **Start PHP Development Server**
   ```bash
   php -S localhost:8000
   ```

3. **Access the Application**
   - Open browser and navigate to `http://localhost:8000`
   - You'll be redirected to the landing page

### First Time Setup

1. Visit the **Landing Page** to understand the platform
2. Click **"Sign Up"** to create a new account
3. Fill in your details and create a password
4. Log in with your credentials
5. Complete the **Mental Health Assessment**
6. Explore available **Wellness Programs**
7. Start tracking your **Mood** and keeping your **Journal**

## 💾 Data Storage

All sensitive user data (authentication, assessments, moods, journals, profiles) is securely stored on the server in a database via PHP APIs.

Some non-sensitive preferences may be stored locally in the browser using `localStorage`, such as:

- **Dark Mode Preference**: `darkMode`

**Note**: Only preferences like dark mode are stored in the browser. All main user data is kept secure on the server and is not affected by browser cache.

## 🎨 Customization

### Colors & Branding

Edit the CSS variables in [styles.css](styles.css):

```css
:root {
   --primary: #2c2c2c;         /* Main brand color (dark gray) */
   --accent: #8b7355;          /* Accent color (brownish) */
   --text-primary: #2c2c2c;    /* Main text color */
   --text-secondary: #666;     /* Secondary text */
   --text-muted: #999;         /* Muted text */
   --bg-primary: #ffffff;      /* Primary background */
   --bg-secondary: #fafafa;    /* Secondary background */
   --border: #f0f0f0;          /* Border color */
   --border-dark: #e0e0e0;     /* Darker border */
}
```

## 📱 Responsive Design

The platform is fully responsive and works on:
- ✅ Desktop (1920px and above)
- ✅ Laptop (1024px - 1920px)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (320px - 768px)

## 🔒 Security Features

- Client-side validation for all forms
- Password strength requirements
- Session-based authentication
- No sensitive data stored in plain text
- CORS-ready structure

## 📖 Usage Guide

### Creating an Account
1. Go to Sign Up page
2. Enter full name, email, age group
3. Create a strong password (8+ chars recommended)
4. Agree to Terms & Conditions
5. Click "Create Account"

### Taking Assessment
1. Navigate to Assessment from dashboard
2. Answer all questions honestly
3. Submit to get personalized recommendations
4. View your wellness score
5. Explore recommended programs

### Tracking Mood
1. Go to Mood Tracker
2. Select your mood for the day
3. Optional: Add notes about your feelings
4. View mood history and trends

### Journaling
1. Open Journal section
2. Click "New Entry"
3. Write your thoughts and feelings
4. Use #hashtags for organization
5. Save and organize entries

### Exploring Programs
1. Browse available programs by category
2. Click to view program details
3. Check duration, level, and requirements
4. Enroll to start the program
5. Track your progress


## 📄 License

This project is created for educational and mental wellness purposes.

**HealNest** - Empowering Youth Through Mental Wellness 🌟
