from tests.common_flows import (
    get_data_path,
    open_upload,
    select_major,
    upload_csv_via_hidden_input,
    preview_rows,
    wait_validation_result,
    click_view_result,
    wait_result_page,
    result_stats,
    result_categories,
    table_body,
)


def test_valid_csv_upload(driver):
    """
    Valid CSV should navigate to result page successfully.
    """
    open_upload(driver)
    select_major(driver, "Computer Science")

    csv_path = get_data_path("valid.csv")
    upload_csv_via_hidden_input(driver, csv_path)

    result = wait_validation_result(driver)
    assert "courses loaded" in result.text.lower()
    rows = preview_rows(driver)
    assert len(rows) == 4
    assert "CPS 1231" in rows[0].text
    body = table_body(driver)
    assert "Data Structures" in body.text
    click_view_result(driver)
    wait_result_page(driver)
    stats = result_stats(driver)
    assert stats["total_credits"].text
    assert stats["remaining"].text
    assert "%" in stats["progress"].text
    cats = result_categories(driver)
    assert len(cats) > 0
