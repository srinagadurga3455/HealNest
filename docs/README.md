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
- **HTML5**: Semantic markup structure
- **CSS3**: Modern styling with CSS variables, gradients, and animations
- **JavaScript (ES6+)**: Dynamic functionality and interactivity
- **Bootstrap 5**: Responsive grid and components
- **Calm Clarity UI**: Pre-built component library

### Tools & Libraries
- **Tabler Icons (ti)**: Icon library for UI elements
- **LocalStorage API**: Client-side data persistence
- **Responsive Design**: Mobile-first approach

## 📁 Project Structure

```
HealNest/
├── index.php                 # Main entry point
├── landing.html              # Landing/home page
├── login.html                # Login page
├── register.html             # Registration page
├── dashboard.html            # Main dashboard
├── assessment.html           # Mental health assessment
├── program.html              # Wellness programs
├── journal.html              # Digital journal
├── mood.html                 # Mood tracker
├── profile.html              # User profile
├── styles.css                # Custom global styles
├── app.js                    # Main application logic
├── connect.php               # Database connection (if needed)
├── setup.php                 # Initial setup
└── calm_clarity/             # Theme assets
    └── assets/
        ├── css/              # Stylesheets
        ├── js/               # Theme scripts
        └── images/           # Icons and images
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

All user data is stored locally in the browser using `localStorage`:

- **User Authentication**: `healNestUser`
- **Assessment Results**: `healNestAssessment`
- **Mood Data**: `healNestMoodData`
- **Journal Entries**: `healNestJournalEntries`
- **Dark Mode Preference**: `darkMode`

**Note**: Data persists in the browser but will be cleared if browser cache is cleared.

## 🎨 Customization

### Colors & Branding

Edit the CSS variables in [styles.css](styles.css):

```css
:root {
  --primary: #5D87FF;
  --primary-dark: #3B7BFF;
  --secondary: #49BEFF;
  --tertiary: #13DEB9;
  /* ... more colors ... */
}
```

### Pages & Navigation

Main pages and their purposes:

| Page | Path | Purpose |
|------|------|---------|
| Landing | `landing.html` | Welcome & feature showcase |
| Login | `login.html` | User authentication |
| Register | `register.html` | Account creation |
| Dashboard | `dashboard.html` | Main hub after login |
| Assessment | `assessment.html` | Mental health evaluation |
| Programs | `program.html` | Wellness program catalog |
| Journal | `journal.html` | Personal journaling |
| Mood | `mood.html` | Mood tracking |
| Profile | `profile.html` | User settings & info |

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

## 🐛 Troubleshooting

### Issues & Solutions

**Problem**: Data not persisting
- **Solution**: Check if browser allows localStorage, clear cache and try again

**Problem**: Cannot login
- **Solution**: Make sure you registered first, check browser console for errors

**Problem**: Styles not loading
- **Solution**: Clear browser cache (Ctrl+Shift+Del), hard refresh (Ctrl+F5)

**Problem**: Assessment not saving
- **Solution**: Ensure all questions are answered before submitting

## 📞 Support & Contributing

For issues, feature requests, or contributions:
1. Document the issue clearly
2. Include steps to reproduce
3. Suggest potential solutions
4. Submit with examples

## 📄 License

This project is created for educational and mental wellness purposes.

## 🙏 Acknowledgments

- **Bootstrap**: For responsive grid and components
- **Tabler Icons**: For beautiful icon set
- **Calm Clarity Theme**: For UI components
- **Mental Health Organizations**: For assessment methodology

## 🎯 Future Enhancements

Planned features for future releases:
- [ ] Backend database integration
- [ ] User authentication with PHP/MySQL
- [ ] Social features (connect with friends)
- [ ] AI-powered recommendations
- [ ] Video program tutorials
- [ ] Therapist/counselor connection
- [ ] Mobile app version
- [ ] Push notifications
- [ ] Export data functionality
- [ ] Multi-language support

## 📅 Changelog

### Version 1.0 (Current)
- ✅ Complete project structure
- ✅ All core pages implemented
- ✅ Custom CSS styling
- ✅ JavaScript functionality
- ✅ LocalStorage data persistence
- ✅ Responsive design
- ✅ Assessment system
- ✅ Mood tracking
- ✅ Journal entries

---

**HealNest** - Empowering Youth Through Mental Wellness 🌟
