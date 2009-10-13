CREATE VIEW calendar AS SELECT
	calendar_id,
	staff_id,
	inanimate_item_code,
	calendar_type_code,
	calendar_date,
	calendar_date_end,
	calendar_permission_add,
	calendar_permission_delete,
	calendar_permission_edit,
	calendar_permission_list,
	calendar_permission_view,
	schedule_ahead_interval,
	schedule_ahead_permission,
	(CURRENT_DATE + schedule_ahead_interval)::date AS maximum_appoinment_date,
	COALESCE((SELECT description FROM l_inanimate_item WHERE inanimate_item_code = tbl_calendar.inanimate_item_code),staff_name(staff_id)) AS calendar_title,
	standard_lunch_hour_start,
	standard_lunch_hour_end,
	day_0_start,
	day_0_end,
	day_1_start,
	day_1_end,
	day_2_start,
	day_2_end,
	day_3_start,
	day_3_end,
	day_4_start,
	day_4_end,
	day_5_start,
	day_5_end,
	day_6_start,
	day_6_end,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_at,
	deleted_by,
	deleted_comment,
	sys_log
FROM tbl_calendar WHERE NOT is_deleted;

CREATE VIEW calendar_current AS SELECT * FROM calendar
	WHERE calendar_date <= CURRENT_DATE AND (calendar_date_end > CURRENT_DATE OR calendar_date_end IS NULL);
