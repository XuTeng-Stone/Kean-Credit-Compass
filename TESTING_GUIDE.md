# Testing Guide

This guide focuses on the default flow: run the frontend dev server locally and call the remote PHP API `https://obi.kean.edu/~toranm@kean.edu/test.php`. No local backend or database is required.

---

## Prerequisites
- Node.js 14+ and npm
- Modern browser
- Internet access to reach the remote API
- Code location: `kcc-frontend/my-app`

Start the frontend:
```bash
cd kcc-frontend/my-app
npm install
npm start
# Open http://localhost:3000
```

---

## Entry Points and Flow
Routes: `/` → `/upload` → `/result`

1) Landing: click “Start Checking” to go to upload page  
2) Upload page:
- Pick major: Computer Science → program code `BS-CPS`; IT → `BS-IT`
- Upload CSV; frontend validates it
- On success, click “View Result” to go to result page
3) Result page:
- Shows total credits, remaining credits, completion rate
- Categories can expand/collapse
- Rule-based categories (Major/Free Electives) show requirements and what is met

---

## CSV Requirements
- Columns: `Course Code, Course Name, Credits, Grade, Semester`
- Grades: `A, A-, B+, B, B-, C+, C, C-, D, F`
- Credits: 0–6
- File size: ≤5MB
- Sample: `public/sample-courses.csv`
- Negative test: `public/sample-invalid.csv` (to verify validation failure)

---

## Test Cases Checklist
- Happy path: choose major, upload `sample-courses.csv`, expect validation success and category progress on result page
- No major selected then upload: expect prompt to select a major
- CSV validation failure: upload `sample-invalid.csv`, expect error list
- API failure: disconnect network or set `API_BASE_URL` to an invalid host, expect error message
- Result page interactions: expand/collapse categories; rule-based categories show requirements and completed items
- CS vs IT switch: pick different majors; results should reflect BS-CPS vs BS-IT

---

## Troubleshooting
- Cannot load result / “Failed to fetch data”
  - Check network connectivity
  - Verify remote API via curl: `curl "https://obi.kean.edu/~toranm@kean.edu/test.php?code=BS-CPS&format=json"`
- 400 missing fields: request body must include `program_code` and `courses`
- 404 Program not found: confirm program code (CS→BS-CPS, IT→BS-IT)
- 500 Server error: retry later or contact backend/ops

---

## Appendix: Optional Remote Access (self-hosted backend)
Not needed by default. If you self-host the backend and need external access, set up port mapping or a tunnel tool (e.g., ngrok), then update `src/config.js` to your backend URL.
