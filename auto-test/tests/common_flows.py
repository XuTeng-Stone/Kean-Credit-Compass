import os
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC

BASE_URL = os.getenv("BASE_URL", "http://localhost:3000")


def get_data_path(filename: str) -> str:
    """
    Get absolute path of CSV data file.
    """
    base_dir = os.path.dirname(os.path.dirname(__file__))
    return os.path.join(base_dir, "data", filename)


def open_home_and_click_start(driver):
    """
    Open homepage and click 'Start Checking' button.
    """
    driver.get(BASE_URL)

    start_btn = WebDriverWait(driver, 15).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Start Checking')]"))
    )
    start_btn.click()


def open_upload(driver):
    driver.get(f"{BASE_URL}/upload")


def select_major_cs(driver):
    """
    Select Computer Science as major.
    """
    dropdown = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, "select#major-select, select"))
    )
    Select(dropdown).select_by_visible_text("Computer Science")


def select_major(driver, label: str):
    dropdown = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, "select#major-select, select"))
    )
    Select(dropdown).select_by_visible_text(label)


def upload_csv_via_hidden_input(driver, csv_path):
    """
    Upload CSV file using hidden input[type=file].
    """
    file_input = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, "input[type='file']"))
    )
    file_input.send_keys(csv_path)


def click_analyze_or_view(driver):
    """
    Click Analyze or View Result button.
    """
    analyze_btn = WebDriverWait(driver, 15).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'View') or contains(text(),'Analyze')]"))
    )
    analyze_btn.click()


def wait_validation_result(driver):
    return WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, ".validation-result"))
    )


def error_items(driver):
    return WebDriverWait(driver, 5).until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".error-details li"))
    )


def preview_rows(driver):
    return WebDriverWait(driver, 5).until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".data-table tbody tr"))
    )


def table_body(driver):
    return WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, ".data-table tbody"))
    )


def click_view_result(driver):
    btn = WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'View Result')]"))
    )
    btn.click()


def wait_result_page(driver):
    return WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.XPATH, "//*[contains(text(),'Degree Progress')]"))
    )


def result_stats(driver):
    return {
        "total_credits": WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".stat-card:nth-of-type(1) .stat-value"))
        ),
        "remaining": WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".stat-card:nth-of-type(2) .stat-value"))
        ),
        "progress": WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".stat-card:nth-of-type(3) .stat-value"))
        ),
    }


def result_categories(driver):
    return WebDriverWait(driver, 10).until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".categories-grid .category-card"))
    )
