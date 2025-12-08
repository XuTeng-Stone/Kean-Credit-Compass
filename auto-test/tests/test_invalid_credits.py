from tests.common_flows import (
    get_data_path,
    open_home_and_click_start,
    upload_csv_via_hidden_input,
)


def test_invalid_credits_csv(driver):
    """
    Invalid credits should be rejected.
    """
    open_home_and_click_start(driver)

    csv_path = get_data_path("bad_credits.csv")
    upload_csv_via_hidden_input(driver, csv_path)
