import os
import tempfile
import pytest
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.action_chains import ActionChains

from tests.common_flows import (
    get_data_path,
    open_home_and_click_start,
    select_major,
    upload_csv_via_hidden_input,
    wait_validation_result,
    click_view_result,
    wait_result_page,
    result_stats,
    result_categories,
    table_body,
)


def test_landing_page_navigation(driver):
    """
    Test landing page loads and navigation to upload page works.
    """
    driver.get("http://localhost:3000")

    # Check landing page elements
    title = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "title"))
    )
    assert "Kean-Credit-Compass" in title.text

    # Check start button and click
    start_btn = WebDriverWait(driver, 15).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Start Checking')]"))
    )
    start_btn.click()

    # Verify navigation to upload page
    upload_title = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "upload-title"))
    )
    assert "Course Credit Checker" in upload_title.text


def test_major_selection_cs_and_it(driver):
    """
    Test major selection for both Computer Science and IT.
    """
    driver.get("http://localhost:3000/upload")

    # Test CS selection
    select_major(driver, "Computer Science")
    dropdown = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, "select#major-select"))
    )
    assert dropdown.get_attribute("value") == "Computer Science"

    # Test IT selection
    select_major(driver, "IT")
    assert dropdown.get_attribute("value") == "IT"


def test_drag_and_drop_upload(driver):
    """
    Test drag and drop file upload functionality.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Get upload zone
    upload_zone = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "upload-zone"))
    )

    # Create a test file
    test_file_path = get_data_path("valid.csv")

    # Use ActionChains to simulate drag and drop
    actions = ActionChains(driver)
    file_input = driver.find_element(By.CSS_SELECTOR, "input[type='file']")

    # This simulates the drag and drop by directly setting the file input
    # (full drag-and-drop simulation is complex in Selenium)
    file_input.send_keys(test_file_path)

    # Wait for validation result
    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()


def test_api_error_handling(driver):
    """
    Test API error handling when backend is unavailable.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload valid CSV
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    # Wait for validation success
    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    # Click view result (this will trigger API call)
    click_view_result(driver)

    # Note: This test assumes the API is working. In a real scenario,
    # you might mock the API or use a test backend that can simulate errors.
    # For now, we just verify the navigation to result page works.
    try:
        wait_result_page(driver)
        # If we reach here, API call was successful
        stats = result_stats(driver)
        assert stats["total_credits"].text
    except Exception as e:
        # If API fails, should show error message
        error_msg = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "error-message"))
        )
        assert "Failed to fetch data" in error_msg.text or "Error" in error_msg.text


def test_result_page_navigation_and_elements(driver):
    """
    Test result page loads and displays all required elements.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload and navigate to results
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    click_view_result(driver)
    wait_result_page(driver)

    # Verify result page elements
    stats = result_stats(driver)
    assert stats["total_credits"].text
    assert stats["remaining"].text
    assert "%" in stats["progress"].text

    # Check categories are loaded
    cats = result_categories(driver)
    assert len(cats) > 0

    # Check progress title
    progress_title = driver.find_element(By.CLASS_NAME, "progress-title")
    assert "Degree Progress" in progress_title.text


def test_back_navigation_buttons(driver):
    """
    Test back navigation buttons work correctly.
    """
    # Test back button on upload page
    driver.get("http://localhost:3000/upload")

    back_btn = WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.CLASS_NAME, "back-home-btn"))
    )
    back_btn.click()

    # Should navigate back to landing page
    title = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "title"))
    )
    assert "Kean-Credit-Compass" in title.text


def test_form_reset_functionality(driver):
    """
    Test form reset functionality works.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload a file
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    # Wait for validation
    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    # Check data preview is shown
    data_preview = driver.find_element(By.CLASS_NAME, "data-preview")
    assert data_preview.is_displayed()

    # Click reset button
    reset_btn = WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(text(),'Try Another')]"))
    )
    reset_btn.click()

    # Verify form is reset
    dropdown = driver.find_element(By.CSS_SELECTOR, "select#major-select")
    assert dropdown.get_attribute("value") == ""

    # Verify no validation result shown
    validation_results = driver.find_elements(By.CLASS_NAME, "validation-result")
    assert len(validation_results) == 0 or not validation_results[0].is_displayed()


def test_category_expansion_on_result_page(driver):
    """
    Test category expansion/collapse functionality on result page.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload and navigate to results
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    click_view_result(driver)
    wait_result_page(driver)

    # Find expand buttons
    expand_buttons = WebDriverWait(driver, 10).until(
        EC.presence_of_all_elements_located((By.CLASS_NAME, "expand-button"))
    )

    if len(expand_buttons) > 0:
        # Click first expand button
        expand_buttons[0].click()

        # Wait a moment for expansion
        WebDriverWait(driver, 5).until(
            lambda d: "Hide" in expand_buttons[0].text or len(d.find_elements(By.CLASS_NAME, "available-courses")) > 0
        )

        # Click again to collapse
        expand_buttons[0].click()

        # Verify collapsed state
        assert "View Available" in expand_buttons[0].text or "View Guide" in expand_buttons[0].text


def test_download_sample_csv_link(driver):
    """
    Test sample CSV download link is present and accessible.
    """
    driver.get("http://localhost:3000/upload")

    # Check download sample link exists
    download_link = WebDriverWait(driver, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "download-sample"))
    )
    assert download_link.is_displayed()
    assert "Download Sample" in download_link.text

    # Verify href attribute points to sample file
    href = download_link.get_attribute("href")
    assert "sample-courses.csv" in href


def test_upload_progress_indicators(driver):
    """
    Test upload progress indicators and visual feedback.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload valid CSV
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    # Wait for validation result
    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    # Check validation result styling (should be success)
    assert "success" in result.get_attribute("class")

    # Check data preview table is shown
    preview_table = WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.CLASS_NAME, "data-table"))
    )
    assert preview_table.is_displayed()


def test_boundary_case_empty_csv_with_headers(driver):
    """
    Test handling of CSV with headers but no data rows.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Create temporary CSV with headers only
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".csv", mode='w')
    try:
        tmp.write("Course Code,Course Name,Credits,Grade,Semester\n")
        tmp.close()

        upload_csv_via_hidden_input(driver, tmp.name)

        result = wait_validation_result(driver)
        # Should show success but with 0 courses
        assert "courses loaded" in result.text.lower()
        assert "0 courses loaded" in result.text.lower()

    finally:
        os.unlink(tmp.name)


def test_validation_error_display_formatting(driver):
    """
    Test that validation errors are displayed in proper format.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload invalid CSV
    csv_path = get_data_path("bad_credits.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "error" in result.text.lower()

    # Check error details are shown
    error_details = WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.CLASS_NAME, "error-details"))
    )
    assert error_details.is_displayed()

    # Check error list items exist
    error_items = driver.find_elements(By.CSS_SELECTOR, ".error-details li")
    assert len(error_items) > 0


def test_responsive_ui_elements(driver):
    """
    Test that key UI elements are present and properly sized.
    """
    driver.get("http://localhost:3000/upload")

    # Check main container
    container = driver.find_element(By.CLASS_NAME, "upload-container")
    assert container.is_displayed()

    # Check upload zone
    upload_zone = driver.find_element(By.CLASS_NAME, "upload-zone")
    assert upload_zone.is_displayed()

    # Check major selection
    major_section = driver.find_element(By.CLASS_NAME, "major-selection")
    assert major_section.is_displayed()

    # Check CSV info card
    info_card = driver.find_element(By.CLASS_NAME, "csv-info-card")
    assert info_card.is_displayed()


def test_result_page_action_buttons(driver):
    """
    Test action buttons on result page work correctly.
    """
    driver.get("http://localhost:3000/upload")
    select_major(driver, "Computer Science")

    # Upload and navigate to results
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()

    click_view_result(driver)
    wait_result_page(driver)

    # Check action buttons are present
    action_buttons = driver.find_elements(By.CLASS_NAME, "btn")
    assert len(action_buttons) >= 2  # Should have at least Upload Again and Home buttons

    # Test Upload Again button
    upload_again_btn = None
    for btn in action_buttons:
        if "Upload Again" in btn.text:
            upload_again_btn = btn
            break

    if upload_again_btn:
        upload_again_btn.click()
        # Should navigate back to upload page
        upload_title = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "upload-title"))
        )
        assert "Course Credit Checker" in upload_title.text
