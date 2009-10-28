/*
Simple Staff Report
*/

INSERT INTO tbl_report (report_title, report_category_code, report_header, report_footer, report_comment, client_page, allow_output_screen, allow_output_spreadsheet, override_sql_security, rows_per_page, output, report_permission, sql, variables, added_by, added_at, changed_by, changed_at, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Simple staff report', 'GENERAL', 'Simple staff report', NULL, NULL, NULL, true, true, false, NULL, NULL, NULL, 'SELECT staff_id,staff_program(staff_id),staff_project(staff_id) FROM staff', NULL, 1, current_timestamp, 1, current_timestamp, false, NULL, NULL, NULL, NULL);

