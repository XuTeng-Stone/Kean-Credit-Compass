import os
import tempfile
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from tests.common_flows import (
    get_data_path,
    open_upload,
    select_major,
    upload_csv_via_hidden_input,
    wait_validation_result,
    error_items,
)


def test_invalid_grade_csv(driver):
    """
    Invalid grade should be rejected.
    """
    open_upload(driver)
    select_major(driver, "Computer Science")

    csv_path = get_data_path("bad_grade.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "error" in result.text.lower()
    errors = error_items(driver)
    assert any("Invalid grade" in e.text for e in errors)
    assert "courses loaded" not in result.text.lower()


def test_requires_major_before_upload(driver):
    open_upload(driver)
    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)
    result = wait_validation_result(driver)
    assert "select a major" in result.text.lower()
    hint = WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, ".error-message"))
    )
    assert "select a major" in hint.text.lower()


def test_rejects_non_csv(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".txt")
    try:
        tmp.write(b"not csv")
        tmp.close()
        upload_csv_via_hidden_input(driver, tmp.name)
        result = wait_validation_result(driver)
        assert "invalid file type" in result.text.lower()
    finally:
        os.unlink(tmp.name)


def test_missing_column(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".csv")
    try:
        tmp.write(b"Course Code,Course Name,Credits,Grade\n")  # missing Semester
        tmp.write(b"CPS 1231,Programming I,4,A\n")
        tmp.close()
        upload_csv_via_hidden_input(driver, tmp.name)
        result = wait_validation_result(driver)
        assert "missing column" in result.text.lower()
    finally:
        os.unlink(tmp.name)


def test_empty_file(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".csv")
    try:
        tmp.write(b"")  # empty
        tmp.close()
        upload_csv_via_hidden_input(driver, tmp.name)
        result = wait_validation_result(driver)
        assert "missing column" in result.text.lower()
    finally:
        os.unlink(tmp.name)


def test_header_only(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".csv")
    try:
        tmp.write(b"Course Code,Course Name,Credits,Grade,Semester\n")
        tmp.close()
        upload_csv_via_hidden_input(driver, tmp.name)
        result = wait_validation_result(driver)
        assert "courses loaded" in result.text.lower()
        assert "0 courses loaded" in result.text.lower()
    finally:
        os.unlink(tmp.name)


def test_large_file(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=".csv")
    try:
        tmp.write(b"Course Code,Course Name,Credits,Grade,Semester\n")
        line = b"CPS 9999,Big Course,6,A,Fall 2024\n"
        repeats = (5 * 1024 * 1024) // len(line) + 10
        tmp.write(line * repeats)
        tmp.close()
        upload_csv_via_hidden_input(driver, tmp.name)
        result = wait_validation_result(driver)
        assert "file too large" in result.text.lower()
    finally:
        os.unlink(tmp.name)


def test_repeat_upload_overwrites(driver):
    open_upload(driver)
    select_major(driver, "Computer Science")
    first = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, first)
    res1 = wait_validation_result(driver)
    assert "courses loaded" in res1.text.lower()
    second = get_data_path("bad_grade.csv")
    upload_csv_via_hidden_input(driver, second)
    res2 = wait_validation_result(driver)
    assert "error" in res2.text.lower()
    errs = error_items(driver)
    assert any("Invalid grade" in e.text for e in errs)
