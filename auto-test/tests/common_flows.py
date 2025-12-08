import os
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


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
    driver.get("http://localhost:3000")

    start_btn = WebDriverWait(driver, 15).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Start Checking')]"))
    )
    start_btn.click()


def select_major_cs(driver):
    """
    Select Computer Science as major.
    """
    dropdown = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.TAG_NAME, "select"))
    )
    dropdown.send_keys("Computer Science")


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
