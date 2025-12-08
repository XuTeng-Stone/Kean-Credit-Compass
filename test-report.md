# Selenium E2E Test Report

## Summary
- Command: `BASE_URL=http://localhost:3000 python3 -m pytest -v`
- Runtime: Python 3.9.6, pytest 8.4.2, Chrome (webdriver-manager)
- Result: 10 passed, 0 failed, 5 deselected
- Warning: urllib3 NotOpenSSLWarning (LibreSSL 2.8.3) — informational only

## Covered Cases
- Upload valid.csv: success message, table rows, result page stats/categories.
- Invalid grade (bad_grade.csv): error, no success.
- Invalid credits (bad_credits.csv): error, no success.
- Missing major: prompt to select major.
- Non-CSV file: rejected as invalid type.
- Missing column (no Semester): shows missing column message.
- Empty file: shows missing column message.
- Header only: “0 courses loaded”.
- Large file (>5MB): “file too large”.
- Repeat upload: valid.csv success then bad_grade.csv error, error list shows Invalid grade.
