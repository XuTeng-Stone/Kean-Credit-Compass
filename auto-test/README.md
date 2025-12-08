Automation test folder
# Auto Test for Kean Credit Compass

This repository contains automated UI tests for the **Kean Credit Compass** web application.  
The tests are built using **Python, PyTest, and Selenium WebDriver** to validate correct and incorrect CSV uploads.

---
```
## 📁 Project Structure
auto-test/
│
├── data/
│ ├── valid.csv
│ ├── bad_grade.csv
│ ├── bad_credits.csv
│ └── .gitkeep
│
├── tests/
│ ├── init.py
│ ├── common_flows.py
│ ├── test_valid_upload.py
│ ├── test_invalid_grade.py
│ └── test_invalid_credits.py
│
├── conftest.py
└── README.md
```


## ✅ Test Coverage

This project includes **three automated test cases**:

| Test File | Description | Expected Result |
|----------|-------------|-----------------|
| `test_valid_upload.py` | Upload a correctly formatted CSV file | Upload and analysis succeed |
| `test_invalid_grade.py` | Upload CSV with invalid grade values | System shows validation error |
| `test_invalid_credits.py` | Upload CSV with invalid credit values | System shows validation error |

These tests verify that the system correctly handles both **valid and invalid inputs**.

---

## 🧪 Test Data Files

All CSV files are located in the `data/` directory:

- `valid.csv` → Correct format and values
- `bad_grade.csv` → Contains invalid grade value
- `bad_credits.csv` → Contains invalid credit value

---

## ⚙️ Requirements

- Python **3.9+** (tested on Python 3.13)
- Google Chrome
- ChromeDriver (matching your Chrome version)

---

## 📦 Install Dependencies

Run the following command:

```bash
pip install -r requirements.txt

## If requirements.txt is not present, install manually:
bash：
pip install selenium pytest webdriver-manager

▶️ How to Run the Tests：
From the auto-test root directory, run:
python -m pytest -v

✅ Example Test Result Output:
collected 3 items

tests/test_invalid_credits.py PASSED
tests/test_invalid_grade.py PASSED
tests/test_valid_upload.py PASSED

==================== 3 passed ====================

Design Notes

Selenium WebDriver is used for real browser automation.

PyTest manages test execution and reporting.

A shared test flow is implemented in common_flows.py.

conftest.py provides the WebDriver fixture.

Relative imports inside the tests/ folder are enabled using __init__.py.



👨‍💻 Author：QiuTheodore
