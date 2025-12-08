from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from tests.common_flows import (
    get_data_path,
    open_home_and_click_start,
    select_major_cs,
    upload_csv_via_hidden_input,
    click_analyze_or_view,
)


def test_valid_csv_upload(driver):
    """
    Valid CSV should navigate to result page successfully.
    """
    open_home_and_click_start(driver)
    select_major_cs(driver)

    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    click_analyze_or_view(driver)

    WebDriverWait(driver, 15).until(
        EC.presence_of_element_located((By.XPATH, "//*[contains(text(),'Degree Progress')]"))
    )
