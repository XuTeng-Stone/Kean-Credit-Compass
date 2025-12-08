from tests.common_flows import (
    get_data_path,
    open_upload,
    select_major,
    upload_csv_via_hidden_input,
    wait_validation_result,
    error_items,
)


def test_invalid_credits_csv(driver):
    """
    Invalid credits should be rejected.
    """
    open_upload(driver)
    select_major(driver, "Computer Science")

    csv_path = get_data_path("bad_credits.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "error" in result.text.lower()
    errors = error_items(driver)
    assert any("Invalid credits" in e.text for e in errors)
    assert "courses loaded" not in result.text.lower()
