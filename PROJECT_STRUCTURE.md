# HealNest Project Structure

```
HealNest/
│
├── 📄 index.html                           # Main entry point (redirects to landing)
├── 📄 index.php                            # PHP entry point (backup)
├── 🎨 .htaccess                            # Apache routing configuration
├── 📋 STATUS.txt                           # Project status
│
├── 📁 pages/                               # All HTML pages
│   ├── landing.html                        # Landing/home page
│   ├── login.html                          # Login page
│   ├── register.html                       # Registration page
│   ├── dashboard.html                      # Main dashboard
│   ├── assessment.html                     # Mental health assessment
│   ├── program.html                        # Wellness programs
│   ├── journal.html                        # Digital journal
│   ├── mood.html                           # Mood tracker
│   └── profile.html                        # User profile
│
├── 🎨 css/                                 # Stylesheets
│   └── main.css                            # Custom styles (500+ lines)
│
├── 🔧 js/                                  # JavaScript files
│   └── main.js                             # Main app logic (400+ lines)
│
├── 📦 assets/                              # Project assets
│   ├── images/                             # Images (if added)
│   ├── icons/                              # Icons (if added)
│   └── data/                               # Data files (if added)
│
├── 📚 docs/                                # Documentation
│   ├── README.md                           # Full documentation
│   ├── QUICK_START.md                      # Quick start guide
│   ├── COMPLETION_SUMMARY.md               # Project summary
│   └── FEATURES.md                         # Feature list
│
├── ⚙️ config/                              # Configuration files
│   ├── connect.php                         # Database connection (if needed)
│   └── setup.php                           # Setup script (if needed)
│
├── 📁 calm_clarity/                        # Theme/template assets
│   └── assets/
│       ├── css/                            # Framework CSS
│       ├── js/                             # Framework JS
│       ├── images/                         # Theme images
│       └── libs/                           # Dependencies
│
└── 📁 templates/                           # Template files (optional)
    ├── account-template.html
    ├── profile-template.html
    ├── task-template.html
    └── nav.php
```

---

## 📁 Directory Breakdown

### `/pages/` - HTML Pages
All web pages are organized here:
- **landing.html** - Public landing page
- **login.html** - User login
- **register.html** - User registration
- **dashboard.html** - Main user hub
- **assessment.html** - Mental health assessment
- **program.html** - Wellness programs
- **journal.html** - Journaling
- **mood.html** - Mood tracking
- **profile.html** - User profile

**Path References in Pages:**
```html
<!-- CSS -->
<link rel="stylesheet" href="../css/main.css">

<!-- JS -->
<script src="../js/main.js"></script>

<!-- Theme Assets -->
<link rel="stylesheet" href="../calm_clarity/assets/css/styles.min.css">
<script src="../calm_clarity/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
```

---

### `/css/` - Stylesheets
```
css/
└── main.css          (500+ lines) - All custom styles
```

**Usage:**
```html
<link rel="stylesheet" href="css/main.css">
```

---

### `/js/` - JavaScript
```
js/
└── main.js           (400+ lines) - Application logic
```

**Features:**
- Authentication system
- Mood tracking
- Journal management
- Assessment scoring
- Form validation
- LocalStorage management
- UI utilities

**Usage:**
```html
<script src="js/main.js"></script>
```

---

### `/docs/` - Documentation
```
docs/
├── README.md              - Full project documentation
├── QUICK_START.md         - 5-minute quick start
├── COMPLETION_SUMMARY.md  - Project details
└── FEATURES.md            - Feature checklist
```

---

### `/config/` - Configuration
```
config/
├── connect.php   - Database connection (if using DB)
└── setup.php     - Initial setup script
```

---

### `/assets/` - Project Assets
```
assets/
├── images/       - Project images
├── icons/        - Custom icons
└── data/         - JSON data files
```

---

### `/calm_clarity/` - Theme Framework
```
calm_clarity/
└── assets/
    ├── css/
    │   └── styles.min.css
    ├── js/
    │   ├── app.min.js
    │   ├── sidebarmenu.js
    │   └── ...
    ├── images/
    │   ├── logos/
    │   ├── profile/
    │   └── backgrounds/
    └── libs/
        ├── bootstrap/
        ├── jquery/
        ├── tabler-icons/
        └── ...
```

---

## 🔗 File References

### From Root (index.html)
```html
<!-- Redirect to pages/landing.html -->
<script>
    window.location.href = 'pages/landing.html';
</script>
```

### From Pages (pages/*.html)
```html
<!-- CSS -->
<link rel="stylesheet" href="../css/main.css">

<!-- JS -->
<script src="../js/main.js"></script>

<!-- Theme Assets -->
<link rel="stylesheet" href="../calm_clarity/assets/css/styles.min.css">
<script src="../calm_clarity/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<!-- Navigation Between Pages -->
<a href="./dashboard.html">Dashboard</a>        <!-- Same folder -->
<a href="../landing.html">Home</a>             <!-- Parent folder -->
```

---

## 📝 Key Files Explained

### index.html
```html
<!-- Root redirect file -->
<!-- Purpose: Entry point that redirects to landing page -->
<!-- Auto-redirects to: pages/landing.html -->
```

### pages/*.html
```html
<!-- All application pages -->
<!-- Located in: /pages/ -->
<!-- Examples: landing.html, dashboard.html, etc -->
```

### css/main.css
```css
/* Main stylesheet - 500+ lines */
/* Contains:
   - Color palette & variables
   - Typography
   - Buttons & forms
   - Cards & components
   - Animations
   - Responsive design
   - Utility classes
*/
```

### js/main.js
```javascript
// Main application - 400+ lines
// Contains:
// - Authentication (Auth)
// - Mood Tracking (MoodTracker)
// - Journal (Journal)
// - Assessment (Assessment)
// - Notifications (Notification)
// - Utilities (UI, Validation)
```

---

## 🚀 Navigation Map

```
http://localhost:8000/
    ↓
index.html (redirect)
    ↓
pages/landing.html
    ↓
├─ pages/login.html
├─ pages/register.html
├─ pages/dashboard.html
│   ├─ pages/assessment.html
│   ├─ pages/program.html
│   ├─ pages/journal.html
│   ├─ pages/mood.html
│   └─ pages/profile.html
```

---

## 📂 File Organization Best Practices

### When Adding New Files:
1. **HTML Pages** → `/pages/` folder
2. **CSS Styles** → `/css/` folder
3. **JavaScript** → `/js/` folder
4. **Images** → `/assets/images/` folder
5. **Documentation** → `/docs/` folder
6. **Config Files** → `/config/` folder

### Path Reference Guide:
| From | To | Path |
|------|-----|------|
| Root | CSS | `css/main.css` |
| Root | JS | `js/main.js` |
| pages/ | CSS | `../css/main.css` |
| pages/ | JS | `../js/main.js` |
| pages/ | Other page | `./other-page.html` |

---

## 🔄 Build/Deploy Structure

### Development
```
localhost:8000/index.html
└── Serves all files locally
```

### Production
```
domain.com/
├── index.html           (redirects to pages/landing.html)
├── pages/               (all HTML files)
├── css/                 (stylesheets)
├── js/                  (scripts)
├── assets/              (images, icons)
├── docs/                (documentation)
└── calm_clarity/        (framework)
```

---

## ✅ Organization Checklist

- ✅ Pages organized in `/pages/` folder
- ✅ Styles in `/css/main.css`
- ✅ Scripts in `/js/main.js`
- ✅ Documentation in `/docs/`
- ✅ Config files in `/config/`
- ✅ All paths updated correctly
- ✅ Relative paths working
- ✅ Ready for deployment

---

## 📞 Quick Reference

**Start Server:**
```bash
php -S localhost:8000
```

**Access Application:**
```
http://localhost:8000
```

**View Documentation:**
```
/docs/README.md
/docs/QUICK_START.md
/docs/FEATURES.md
```

**Main Files:**
```
- css/main.css        (500+ lines of styling)
- js/main.js          (400+ lines of functionality)
- pages/              (9 HTML pages)
```

---

**Your project is now professionally organized!** 📁✨
