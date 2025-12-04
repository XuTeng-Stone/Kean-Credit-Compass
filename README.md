# Kean Credit Compass

Web app for Kean CS/IT students to track degree progress. Upload your completed courses CSV and instantly see what's done and what's left.

---

## Tech Stack

**Frontend:** React 19, React Router  
**Backend:** PHP + PDO  
**Database:** MySQL (imc.kean.edu)  
**Server:** Apache (XAMPP)

---

## Features

✓ CSV course upload with drag-and-drop  
✓ Real-time validation (columns, grades, credits)  
✓ Degree progress by category  
✓ Smart elective detection (Major/Free)  
✓ Expand/collapse available courses  
✓ Mobile responsive  
✓ 120 credit degree tracking

**Categories Tracked:**
- General Education
- Additional Required
- Major Core
- Concentration
- Major Electives (CPS 3000+)
- Capstone
- Free Electives (non-CPS 3000+)

---

## Quick Start

### Prerequisites

- Node.js 14+
- XAMPP (Apache + MySQL)
- Modern browser

### Installation

```bash
# Clone
git clone https://github.com/YOUR_USERNAME/Kean-Credit-Compass.git
cd Kean-Credit-Compass

# Backend setup
# 1. Copy kcc-backend/ to C:\xampp\htdocs\
# 2. Start Apache in XAMPP Control Panel
# Database already configured for imc.kean.edu

# Frontend setup
cd kcc-frontend/my-app
npm install
npm start
```

Opens at http://localhost:3000

---

## Project Structure

```
kcc-backend/api/
  config.php                    # Database setup
  get_program_requirements.php  # Get requirements
  compare_courses.php           # Match courses

kcc-frontend/my-app/
  src/
    Pages/
      LandingPage.jsx           # Home page
      CourseUpload.jsx          # Upload & validate
      DegreeProgress.jsx        # Show results
    config.js                   # API settings
    App.js                      # Routing
  public/
    sample-courses.csv          # Example file
```

---

## Configuration

### API URL (`src/config.js`)

Auto-detects localhost vs network IP:

```javascript
const getApiBaseUrl = () => {
  if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    return 'http://localhost/kcc-backend/api';
  }
  return `http://${window.location.hostname}/kcc-backend/api`;
};
```

### Database (`kcc-backend/api/config.php`)

```php
$dbHost = 'imc.kean.edu';
$dbName = '2025F_CPS4301_01';
$dbUser = '2025F_CPS4301_01';
$dbPass = '2025F_CPS4301_01';
```

---

## CSV Format

**Required columns:**
```
Course Code,Course Name,Credits,Grade,Semester
```

**Rules:**
- Credits: 0-6 (numeric)
- Grades: A, A-, B+, B, B-, C+, C, C-, D, F
- Max file: 5MB
- Encoding: UTF-8

**Example:**
```csv
Course Code,Course Name,Credits,Grade,Semester
CPS 1231,Fundamentals of Computer Science,4,A,Fall 2022
HIST 1062,Worlds of History,3,B+,Fall 2022
MATH 2415,Calculus I,4,B,Spring 2023
```

Download `sample-courses.csv` from upload page.

---

## API Endpoints

### POST `/compare_courses.php`
Compare student courses with program requirements.

**Request:**
```json
{
  "program_code": "BS-CPS",
  "courses": [
    {"Course Code": "CPS 1231", "Course Name": "...", "Credits": "4", ...}
  ]
}
```

**Response:**
```json
{
  "program": {"code": "BS-CPS", "total_credits_req": 120},
  "categories": [
    {
      "name": "General Education",
      "type": "general_education",
      "completed_credits": 15,
      "required_credits": 39,
      "fixed_courses": [...],
      "choice_courses": [...]
    }
  ]
}
```

### GET `/get_program_requirements.php?code=BS-CPS`
Fetch all requirements for a program.

---

## Commands

```bash
npm start       # Dev server (port 3000)
npm run build   # Production build
npm test        # Run tests
```

---

## How It Works

1. **Upload:** Student selects major (CS/IT) and uploads CSV
2. **Validate:** Frontend checks format, backend validates data
3. **Compare:** PHP queries DB for program requirements
4. **Match:** Algorithm matches completed courses to requirements
5. **Electives:** Auto-identifies Major (CPS 3000+) and Free (non-CPS 3000+) electives
6. **Display:** Shows progress by category with expand/collapse

**Smart Matching:**
- Fixed courses: Direct match
- Choice courses: Match any from group
- Major Electives: CPS 3000+ not already used
- Free Electives: non-CPS 3000+ not already used
- No double-counting

---

## Testing

**Local:**
- http://localhost:3000

**Network (other devices):**
1. Find your IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
2. Update `src/config.js` if needed
3. Access from other device: `http://YOUR_IP:3000`

---

## Deployment

### Frontend (Netlify/Vercel)
```bash
npm run build
# Upload build/ folder or connect GitHub repo
```

### Backend (Web Server)
1. Copy `kcc-backend/` to server
2. Update `config.php` with production DB credentials
3. Ensure PHP 7.4+ with PDO MySQL extension

---

## Team

| Name | Role |
|------|------|
| Mitch | Backend |
| Stone | Frontend |
| Xianyang | Frontend |

---

## Troubleshooting

**"Failed to fetch data"**
- Check Apache is running
- Verify `http://localhost/kcc-backend/api/compare_courses.php` accessible

**CSV validation fails**
- Check all 5 columns present
- Grades must be exact: A, A-, B+, etc.
- Credits must be 0-6

**Wrong progress calculation**
- Verify credits are numeric in CSV
- System requires 120 total credits

**CORS errors (network testing)**
- Add your IP to `compare_courses.php` allowed origins

---

## License

See LICENSE file.
