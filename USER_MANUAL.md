# User Manual - Kean Credit Compass

Track your degree progress for Computer Science or IT majors.

---

## Getting Started

### What You Need
- Your completed courses in CSV format
- A web browser (Chrome, Firefox, Edge, Safari)

### Steps
1. Open app: http://localhost:3000
2. Click "Start Checking"
3. Select major (Computer Science or IT)
4. Upload CSV file
5. View progress

---

## CSV File Requirements

### Required Columns (exact names)
```
Course Code, Course Name, Credits, Grade, Semester
```

### Rules
- **Credits:** Number from 0 to 6
- **Grades:** A, A-, B+, B, B-, C+, C, C-, D, F (case-insensitive)
- **File size:** Max 5MB
- **Format:** CSV only (not Excel .xlsx)

### Example File
```csv
Course Code,Course Name,Credits,Grade,Semester
CPS 1231,Fundamentals of Computer Science,4,A,Fall 2022
HIST 1062,Worlds of History,3,B+,Fall 2022
ENG 1030,Composition,3,A,Spring 2023
MATH 2415,Calculus I,4,B,Spring 2023
CPS 2231,Computer Programming,4,A,Fall 2023
MATH 2110,Discrete Structures,3,A-,Fall 2023
CPS 2232,Data Structures,4,A,Spring 2024
ENG 3091,Technical Writing,3,B+,Spring 2024
CPS 4301,Special Topics in Computer Science,3,A-,Fall 2024
MATH 3700,Mathematical Modeling,3,B+,Fall 2024
```

**Download sample:** Click "Download Sample" on upload page.

---

## Using the App

### Page 1: Landing
Simple start screen.
- Click "Start Checking" to begin

### Page 2: Upload

**Select Major** (required first):
- Computer Science → Checks BS-CPS requirements
- IT → Checks BS-IT requirements

**Upload CSV**:
- Drag file onto upload zone, OR
- Click to browse files
- Wait for validation (instant)

**Validation Results**:
- ✓ Success: Shows course count, displays table preview
- ✗ Error: Lists all problems found

**Preview Table**:
- See all courses before proceeding
- Check data looks correct

**Buttons**:
- "View Result" → See progress (only if valid)
- "Try Again" → Upload different file

### Page 3: Progress

**Top Section - Overall Stats**:
- Total credits completed (e.g., 34)
- Credits remaining (e.g., 86 out of 120)
- Completion rate with bar (e.g., 28%)

**Middle Section - Categories**:

Each category shows:
- Name (e.g., "General Education")
- Progress: X / Y credits
- Progress bar
- Completed courses with checkmarks

**Category Types**:

1. **Fixed Requirements** (GE, Core, Capstone)
   - Shows completed courses
   - Click "View Available" to see what's left
   - Some have choice groups (pick 1 of 3)

2. **Major Electives**
   - System auto-detects CPS 3000+ courses
   - Only counts courses not used elsewhere
   - Shows rule: "CPS courses at 3000+ level"
   - Displays your completed electives
   - Click "View Guide" for help

3. **Free Electives**
   - System auto-detects non-CPS 3000+ courses
   - Only counts courses not used elsewhere
   - Shows rule: "Any dept, 3000+ level, X-Y credits"
   - Displays your completed electives
   - Click "View Guide" for help

**Expand/Collapse**:
- Click button to show available courses
- Click again to hide

**Bottom Section - Course List**:
- Circular chart showing completion %
- Full list of uploaded courses
- Shows: Code, Name, Credits, Semester

**Buttons**:
- "Upload Again" → Upload updated CSV
- "Home" → Return to start

---

## Understanding Results

### Credit Tracking
App tracks 120 total credits required for graduation.

### Categories
Your courses are matched to these categories:

1. **General Education** (39 cr)
   - Foundation courses (ENG, MATH, HIST, etc.)

2. **Additional Required** (varies)
   - Extra required courses (e.g., ENG 3091)

3. **Major Core** (varies)
   - Required major courses (CPS 1231, 2231, 2232, etc.)

4. **Major Concentration** (varies)
   - Focus area courses

5. **Major Electives** (varies)
   - Any CPS course 3000+ not already required
   - System finds these automatically from your CSV
   - Example: CPS 4301 if not required elsewhere

6. **Capstone** (3-6 cr)
   - Senior project/thesis

7. **Free Electives** (varies)
   - Any non-CPS course 3000+ not already used
   - System finds these automatically from your CSV
   - Example: MATH 3700 if not required elsewhere

### No Double-Counting
Each course only counts once. If CPS 4301 is required in Core, it won't count as Major Elective.

### Status Badges
- **"Met"** = Category complete
- **"X needed"** = Still need X credits

---

## Common Issues

### Upload Problems

**"Select a major first"**
→ Choose CS or IT before uploading

**"Invalid file type. Use CSV"**
→ Save Excel file as CSV format
→ Right-click file → should end with .csv

**"File too large (max 5MB)"**
→ Remove extra columns
→ Only include required 5 columns

### Validation Errors

**"Missing column: X"**
→ CSV must have exact headers:
→ `Course Code,Course Name,Credits,Grade,Semester`

**"Row X: Missing course code"**
→ Every row needs all 5 fields filled

**"Row X: Invalid credits (must be 0-6)"**
→ Credits must be number 0-6
→ Fix decimal issues (use 3 not 3.0)

**"Row X: Invalid grade"**
→ Use: A, A-, B+, B, B-, C+, C, C-, D, F
→ Check for typos (not "B +" with space)

**"Row X: Missing semester"**
→ Fill in semester column
→ Any format ok (Fall 2023, F23, etc.)

### Results Issues

**Progress shows 0%**
→ Check credits are numbers not text
→ Verify CSV has data rows (not just header)

**Course not matching**
→ Check course code exact (CPS 1231 not CPS1231)
→ Space between subject and number

**Wrong category assignment**
→ System follows DB program requirements
→ Course may be in different category than expected

---

## Tips

- Keep CSV updated each semester
- Use sample file as template
- Check all data before "View Result"
- Expand categories to explore options
- Major electives = advanced CPS courses
- Free electives = explore other departments
- Consult advisor for course planning
- 3000+ means junior/senior level (3000, 4000, etc.)

---

## Getting Help

**CSV Issues**
1. Download sample file
2. Copy format exactly
3. Use Excel/Google Sheets → Save As CSV

**Progress Questions**
1. Check course codes match DB
2. Verify major selection correct
3. Review category expansions

**Technical Problems**
Report on GitHub with:
- What happened
- Your CSV (remove personal info)
- Screenshot
- Browser used

Example: "Upload shows 0 courses. Chrome. CSV attached."

---

## Sample Workflow

**Emily's Example:**

1. Opens app, clicks "Start Checking"
2. Selects "Computer Science"
3. Downloads sample CSV
4. Opens in Excel, adds her 10 completed courses
5. Saves as CSV (File → Save As → CSV)
6. Drags CSV to upload zone
7. Sees: "10 courses loaded" ✓
8. Reviews table preview - looks good
9. Clicks "View Result"
10. Sees: 34 credits done, 86 to go, 28% complete
11. Expands "Major Core" - 3 more courses needed
12. Notes CPS 4301 counted as Major Elective
13. Notes MATH 3700 counted as Free Elective
14. Plans next semester courses

---

## FAQ

**Q: Can I use Excel file?**
A: No, must convert to CSV first.

**Q: How many courses can I upload?**
A: No limit. Average student: 40-50 courses.

**Q: Do I need internet?**
A: Yes, for DB connection.

**Q: Is my data saved?**
A: No, not stored. Upload fresh each time.

**Q: What if my course isn't recognized?**
A: Check spelling and code format.

**Q: Can I edit after upload?**
A: No, edit CSV and re-upload.

**Q: Why 120 credits?**
A: Standard bachelor's degree requirement.

**Q: What's the difference between Major and Free electives?**
A: Major = CPS dept, Free = any other dept.
