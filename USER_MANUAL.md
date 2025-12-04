# User Manual - Kean Credit Compass

## 1. Installation & Deployment

### Technical Specifications

| Component | Requirement |
|-----------|-------------|
| Node.js | v14.0 or higher |
| npm | v6.0 or higher |
| Browser | Chrome, Firefox, Edge, or Safari (latest) |
| Internet | Required for API connection |
| OS | Windows, macOS, or Linux |

### Backend (Remote API)

The backend API is hosted at:
```
https://obi.kean.edu/~toranm@kean.edu/test.php
```

**Server Requirements (if self-hosting):**
- PHP 7.4+
- PDO MySQL extension
- Apache or Nginx web server

**Database:**
- MySQL server: imc.kean.edu
- Schema: 2025F_CPS4301_01

### Frontend Installation Steps

```bash
# Step 1: Clone the repository
git clone https://github.com/XuTeng-Stone/Kean-Credit-Compass.git

# Step 2: Navigate to frontend directory
cd Kean-Credit-Compass/kcc-frontend/my-app

# Step 3: Install dependencies
npm install

# Step 4: Start the application
npm start
```

The application will open at `http://localhost:3000`

### Production Deployment

```bash
# Build for production
npm run build

# Deploy the 'build' folder to any static hosting service:
# - Netlify
# - Vercel
# - GitHub Pages
```

---

## 2. Main Features

### Feature Overview

**Kean Credit Compass** is a web application designed for Kean University Computer Science and IT students to track their degree progress.

### Key Features

1. **CSV Course Upload**
   - Drag-and-drop file upload
   - Supports standard CSV format
   - Instant file validation

2. **Real-time Validation**
   - Checks required columns (Course Code, Course Name, Credits, Grade, Semester)
   - Validates grade format (A, A-, B+, B, B-, C+, C, C-, D, F)
   - Validates credit range (0-6)

3. **Degree Progress Tracking**
   - Tracks 120 total credits required for graduation
   - Shows completion percentage
   - Visual progress bars for each category

4. **Category Breakdown**
   - GE Foundation, Humanities, Social Sciences, Science & Math
   - Additional Required courses
   - Major Core and Concentration
   - Major Electives (CPS 3000+ level)
   - Capstone
   - Free Electives

5. **Smart Course Matching**
   - Automatically matches courses to requirements
   - Detects Major Electives (CPS courses at 3000+ level)
   - Detects Free Electives (non-CPS courses)
   - Prevents double-counting

6. **Expand/Collapse Views**
   - View available courses for each category
   - See which courses are completed vs remaining

---

## 3. Main Scenario Walkthrough

### Scenario: Checking Degree Progress for CS Major

**Goal:** Upload completed courses and view degree progress

#### Step 1: Open the Application
Navigate to `http://localhost:3000` in your browser.

![Landing Page]
- You will see the landing page with "Start Checking" button

#### Step 2: Click "Start Checking"
Click the button to proceed to the upload page.

#### Step 3: Select Your Major
![Select Major]
- Click "Computer Science" or "IT" button
- The selected major will be highlighted

#### Step 4: Upload Your CSV File
![Upload Area]
- Drag your CSV file onto the upload zone, OR
- Click the upload zone to browse files

**CSV Format Required:**
```csv
Course Code,Course Name,Credits,Grade,Semester
CPS 1231,Fundamentals of Computer Science,4,A,Fall 2022
HIST 1062,Worlds of History,3,B+,Fall 2022
```

#### Step 5: Review Validation Results
![Validation Success]
- Success message shows number of courses loaded
- Preview table displays all uploaded courses
- If errors exist, they will be listed for correction

#### Step 6: Click "View Result"
Click the button to see your degree progress.

#### Step 7: View Progress Results
![Progress Page]
- **Top Section:** Total credits completed, remaining credits, completion percentage
- **Middle Section:** Category breakdown with progress bars
- **Bottom Section:** Full course list with circular chart

#### Step 8: Explore Categories
- Click "View Available" to expand any category
- See completed courses (with checkmarks) and remaining requirements
- Click again to collapse

---

## 4. Additional Scenarios

### Scenario A: Handling Invalid CSV File

**Goal:** Understand how the system handles validation errors

#### Step 1: Upload Invalid File
Upload a CSV with errors (missing columns, invalid grades, etc.)

#### Step 2: View Error Messages
![Validation Errors]
- System displays specific error messages:
  - "Missing column: Credits"
  - "Row 3: Invalid grade 'X'"
  - "Row 5: Credits must be 0-6"

#### Step 3: Fix and Re-upload
- Click "Try Again"
- Correct the errors in your CSV file
- Upload the corrected file

---

### Scenario B: Checking IT Major Progress

**Goal:** Track progress for IT major instead of CS

#### Step 1: Select IT Major
- On the upload page, click "IT" instead of "Computer Science"

#### Step 2: Upload Courses
- Upload same CSV file with your completed courses

#### Step 3: View IT-Specific Requirements
![IT Progress]
- Categories will reflect BS-IT program requirements
- Different Major Core and Concentration courses
- Same GE requirements as CS

#### Step 4: Compare Requirements
- IT major has different concentration courses
- Major Electives may include IT-specific courses
- Total credits remain 120

---

### Scenario C: Using Sample CSV

**Goal:** Test the system with sample data

#### Step 1: Download Sample
- Click "Download Sample" link on upload page

#### Step 2: Review Sample Format
```csv
Course Code,Course Name,Credits,Grade,Semester
CPS 1231,Fundamentals of Computer Science,4,A,Fall 2022
HIST 1062,Worlds of History,3,B+,Fall 2022
ENG 1030,Composition,3,A,Spring 2023
MATH 2415,Calculus I,4,B,Spring 2023
```

#### Step 3: Upload and View Results
- Upload the sample file
- View how courses are categorized
- Use as template for your own course list
