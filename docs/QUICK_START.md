# 🚀 HealNest - Quick Start Guide

## ✨ Welcome to HealNest!

Your complete youth mental wellness platform is ready to use. This guide will help you get started in 5 minutes.

---

## 🎯 What You Have

✅ **Complete Website** with 9 fully functional pages
✅ **Custom Styling** with modern responsive design  
✅ **Full JavaScript** with authentication & features
✅ **Data Persistence** with LocalStorage
✅ **Beautiful UI** with animations & smooth transitions

---

## 📝 Quick Links

**🌐 Access the Website:**
```
http://localhost:8000
```

**📖 Full Documentation:**
- See [README.md](README.md) for complete guide
- See [COMPLETION_SUMMARY.md](COMPLETION_SUMMARY.md) for details

---

## 🚀 Getting Started (5 Steps)

### 1️⃣ Visit the Landing Page
```
http://localhost:8000
```
You'll see a beautiful hero section with features and CTA buttons.

### 2️⃣ Create Your Account
- Click **"Sign Up"** button
- Fill in:
  - Full Name
  - Email Address
  - Age Group
  - Password (see strength indicator)
- Click **"Create Account"**

### 3️⃣ Log In
- Use your email and password
- You'll be redirected to Dashboard

### 4️⃣ Complete Assessment
- Go to **Assessment** from sidebar
- Answer 3 questions about your wellness
- Get your personalized wellness score
- See recommendations

### 5️⃣ Explore Features
- **Mood Tracker**: Log your daily mood
- **Journal**: Write private journal entries
- **Programs**: Browse wellness programs
- **Profile**: Manage your information

---

## 📱 Main Pages

| Page | URL | Purpose |
|------|-----|---------|
| Landing | `/landing.html` | Homepage with features |
| Login | `/login.html` | User login |
| Register | `/register.html` | Create account |
| Dashboard | `/dashboard.html` | Main hub |
| Assessment | `/assessment.html` | Mental health test |
| Programs | `/program.html` | Wellness programs |
| Journal | `/journal.html` | Journaling |
| Mood | `/mood.html` | Mood tracking |
| Profile | `/profile.html` | User profile |

---

## ✨ Cool Features to Try

### 🔐 Authentication
```javascript
// Password strength is shown as you type
// Strong passwords need:
// - 8+ characters
// - Upper and lower case
// - Numbers
// - Special characters
```

### 📊 Assessment Results
```
Answer questions → Get score (0-100) → See recommendations
Example: 
- Score 80+ = Excellent wellness
- Score 50-79 = Moderate wellness  
- Score <50 = Areas to improve
```

### 😊 Mood Tracking
```
1. Select mood (Excellent, Good, Neutral, etc)
2. Optional: Add notes
3. Save
4. View your mood history and trends
```

### 📝 Journal Entries
```
1. Click "New Entry"
2. Add title and content
3. Use #hashtags for organization
4. Save and search later
```

---

## 💾 Your Data

All data is saved locally in your browser:
- User account info
- Assessment results
- Mood entries (by date)
- Journal entries
- Preferences

**Note**: Data persists until you clear browser cache.

---

## 🎨 Customization

### Change Colors
Edit `styles.css`:
```css
:root {
  --primary: #5D87FF;      /* Change this */
  --secondary: #49BEFF;    /* And this */
}
```

### Add Programs
Edit `program.html` and add to the programs list.

### Add Assessment Questions
Edit `assessment.html` and add new question sections.

---

## 🔒 Test Accounts

Since it's local, you can create any test accounts you want:

```
Email: john@example.com
Password: Test@12345
```

Just register and the account will be created locally.

---

## 📊 Sample Workflow

### Day 1: Setup
```
1. Visit http://localhost:8000
2. Register account
3. Complete assessment
4. View recommendations
```

### Day 2: Daily Use
```
1. Log in to dashboard
2. Log today's mood
3. Write journal entry
4. View progress
```

### Day 7: Check Progress
```
1. View mood statistics
2. Search journal entries
3. Check assessment score
4. Explore programs
```

---

## 🎯 Feature Deep Dive

### Assessment Scoring
```
Each answer: 0-3 points
Total questions: 3
Total possible: 9 points
Your score: (points / 9) * 100
```

### Mood Tracking
```
Moods tracked:
- Excellent
- Good
- Neutral
- Bad
- Terrible

Data stored by date
```

### Journal Organization
```
Features:
- Rich text support
- Automatic hashtags
- Full-text search
- Delete entries
- View history
```

---

## 🐛 Troubleshooting

### Question: Can't Log In?
**Answer**: Make sure you registered first. Data is in localStorage.

### Question: Where's my data?
**Answer**: Check browser's LocalStorage (DevTools > Application > LocalStorage)

### Question: Styles look weird?
**Answer**: Clear cache (Ctrl+Shift+Del) and hard refresh (Ctrl+F5)

### Question: Can I export my data?
**Answer**: Currently saves to browser. For export, check browser DevTools console.

---

## 🔧 Developer Info

### Key Files
```
app.js       - Main application logic (400+ lines)
styles.css   - Custom styling (500+ lines)
*.html       - Individual pages (9 files)
```

### Available Functions
```javascript
Auth.login(email, pwd)
Auth.register(name, email, pwd)
Auth.logout()
Auth.isLoggedIn()

MoodTracker.saveMood(mood, note)
MoodTracker.getTodayMood()
MoodTracker.getMoodStats()

Journal.saveEntry(title, content)
Journal.getEntries()
Journal.search(query)

Assessment.saveResults(score, answers)
Assessment.getLastAssessment()

Notification.success(msg)
Notification.error(msg)
```

### API Endpoints (if using PHP backend)
```
POST /api/auth/register
POST /api/auth/login
GET  /api/auth/logout

POST /api/mood/save
GET  /api/mood/history

POST /api/journal/save
GET  /api/journal/entries

POST /api/assessment/save
GET  /api/assessment/last
```

---

## 📚 Resources Included

### CSS Framework
- Bootstrap 5
- Custom utility classes
- Responsive grid system
- Pre-built components

### JavaScript Features
- ES6+ support
- LocalStorage management
- Form validation
- Event handling
- Animation helpers

### UI Components
- Cards and containers
- Forms and inputs
- Buttons and controls
- Navigation and menus
- Modals and alerts

---

## 🎓 Learning Outcomes

Using this platform, you'll learn:
- ✅ HTML5 semantic markup
- ✅ Modern CSS3 techniques
- ✅ JavaScript ES6+ features
- ✅ LocalStorage API
- ✅ Responsive design
- ✅ UI/UX principles
- ✅ Form validation
- ✅ Data persistence

---

## 🌟 Best Practices

### Using the Platform
1. **Create a unique account** - Use your real email
2. **Complete assessment first** - Get personalized recommendations
3. **Log mood daily** - Consistency helps track patterns
4. **Journal regularly** - Write freely and often
5. **Track progress** - Check statistics weekly

### Development
1. **Always include** `app.js` in scripts
2. **Import** `styles.css` after Bootstrap
3. **Use LocalStorage** carefully (size limited)
4. **Test** in multiple browsers
5. **Validate** all user input

---

## 🎉 Next Steps

### Immediate (Today)
1. ✅ Visit the website
2. ✅ Create an account
3. ✅ Take the assessment

### Short Term (This Week)
1. ✅ Log mood daily
2. ✅ Write journal entries
3. ✅ Explore programs
4. ✅ Check your profile

### Medium Term (This Month)
1. ✅ Set wellness goals
2. ✅ Join programs
3. ✅ Track progress
4. ✅ Update profile

### Long Term (Ongoing)
1. ✅ Maintain wellness practices
2. ✅ Regular assessments
3. ✅ Track achievements
4. ✅ Share with friends

---

## 📞 Support & Questions

### Getting Help
1. Check [README.md](README.md) for detailed docs
2. Review [COMPLETION_SUMMARY.md](COMPLETION_SUMMARY.md) for features
3. Check browser console for errors
4. Try incognito mode to test

### Common Issues
| Issue | Solution |
|-------|----------|
| Can't login | Register first |
| Styles broken | Clear cache |
| Data missing | Check localStorage |
| Assessment not saving | Answer all questions |

---

## 📈 What's Working

✅ User registration and authentication
✅ Mental health assessment with scoring
✅ Daily mood tracking with history
✅ Digital journaling with search
✅ Wellness programs browsing
✅ User profile management
✅ Dashboard with statistics
✅ Responsive mobile design
✅ Form validation
✅ LocalStorage persistence

---

## 🎯 Summary

You now have a **fully functional** mental wellness platform with:
- Modern responsive design
- Complete feature set
- Data persistence
- Beautiful animations
- Professional UX

**Start using it now!** 🌟

```
Go to: http://localhost:8000
Click: Sign Up
Create: Your account
Enjoy: HealNest!
```

---

**Happy exploring!** 🎉

*Empowering Youth Through Mental Wellness & Personal Growth*
