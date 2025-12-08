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

## Selenium E2E (auto-test)
- Purpose: end-to-end validation of upload flow and messages using `auto-test/data/{valid.csv,bad_credits.csv,bad_grade.csv}`
- Requirements: Python 3.9+, Chrome with matching ChromeDriver, `pip install selenium pytest webdriver-manager`
- Layout: `auto-test/data` (CSV), `auto-test/tests` (cases and shared flows), `auto-test/conftest.py` (driver fixture)
- Run:
  ```bash
  cd auto-test
  BASE_URL=http://localhost:3000 python -m pytest -v
  ```
- Cases:
  - valid.csv uploads successfully, preview renders, then navigates to result page
  - bad_credits.csv shows invalid credits error; no success state
  - bad_grade.csv shows invalid grade error; no success state
  - Upload without selecting major shows required-major message
  - Non-CSV upload is rejected
  - Missing column (no Semester) shows missing column message
  - Empty file shows missing column message
  - Header-only CSV shows “0 courses loaded”
  - >5MB CSV shows “file too large”
  - Repeat upload: valid.csv success then bad_grade.csv error with invalid grade listed

Latest run (see `auto-test/test-report.md` for details):
- Command: `BASE_URL=http://localhost:3000 python3 -m pytest -v`
- Result: 10 passed, 0 failed, 5 deselected
- Warning: urllib3 NotOpenSSLWarning (LibreSSL 2.8.3), informational only

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
