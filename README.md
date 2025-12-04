# Kean Credit Compass

Web app for Kean CS/IT students to track degree progress. Upload your completed courses CSV and instantly see what's done and what's left.

---

## Tech Stack

**Frontend:** React 19, React Router  
**Backend:** Remote PHP API (obi.kean.edu)  
**Database:** MySQL (imc.kean.edu)

---

## Features

- CSV course upload with drag-and-drop  
- Real-time validation (columns, grades, credits)  
- Degree progress by category  
- Smart elective detection (Major/Free)  
- Expand/collapse available courses  
- Mobile responsive  
- 120 credit degree tracking

**Categories Tracked:**
- GE Foundation
- GE Humanities
- GE Social Sciences
- GE Science & Mathematics
- Additional Required
- Major Core
- Major Concentration
- Major Electives (CPS 3000+)
- Capstone
- Free Electives (non-CPS)

---

## Quick Start

### Prerequisites

- Node.js 14+
- Modern browser

### Installation

```bash
git clone https://github.com/XuTeng-Stone/Kean-Credit-Compass.git
cd Kean-Credit-Compass

cd kcc-frontend/my-app
npm install
npm start
```

Opens at http://localhost:3000

---

## Project Structure

```
kcc-backend/api/
  test.php        # Main API (GET requirements, POST comparison)
  kcc_table.php   # Database viewer page

kcc-frontend/my-app/
  src/
    Pages/
      LandingPage.jsx     # Home page
      CourseUpload.jsx    # Upload & validate
      DegreeProgress.jsx  # Show results
    config.js             # API settings
    App.js                # Routing
  public/
    sample-courses.csv    # Example file
```

---

## Configuration

### API URL (`src/config.js`)

```javascript
export const API_BASE_URL = 'https://obi.kean.edu/~toranm@kean.edu';
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

### POST `/test.php`
Compare student courses with program requirements.

**Request:**
```json
{
  "program_code": "BS-CPS",
  "courses": [
    {"Course Code": "CPS 1231", "Course Name": "...", "Credits": "4", "Grade": "A", "Semester": "Fall 2024"}
  ]
}
```

**Response:**
```json
{
  "program": {"code": "BS-CPS", "total_credits_req": 120},
  "total_completed_credits": 34,
  "categories": [
    {
      "name": "GE - Foundation",
      "type": "ge_foundation",
      "completed_credits": 12,
      "required_credits": 13,
      "fixed_courses": [...],
      "choice_courses": [...]
    }
  ]
}
```

### GET `/test.php?code=BS-CPS&format=json`
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
2. **Validate:** Frontend checks format
3. **Compare:** Remote API queries DB for program requirements
4. **Match:** Algorithm matches completed courses to requirements
5. **Electives:** Auto-identifies Major (CPS 3000+) and Free (non-CPS) electives
6. **Display:** Shows progress by category with expand/collapse

**Smart Matching:**
- Fixed courses: Direct match
- Choice courses: Match any from group
- Major Electives: CPS 3000+ not already used
- Free Electives: non-CPS not already used
- No double-counting

---

## Deployment

### Frontend (Netlify/Vercel)
```bash
npm run build
# Upload build/ folder or connect GitHub repo
```

### Backend
Upload `test.php` and `kcc_table.php` to PHP-enabled web server.

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
- Check internet connection
- Verify API server is accessible

**CSV validation fails**
- Check all 5 columns present
- Grades must be exact: A, A-, B+, etc.
- Credits must be 0-6

**Wrong progress calculation**
- Verify credits are numeric in CSV
- System requires 120 total credits

---

## License

See LICENSE file.
