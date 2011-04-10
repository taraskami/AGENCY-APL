CREATE OR REPLACE VIEW bar AS
SELECT
	*,
	(bar_date_end - bar_date) + 1 as days_barred,
	non_client_name_last || ', ' || non_client_name_first AS non_client_name_full,
	CASE WHEN bar_date_end IS NOT NULL AND brc_client_attended_date IS NULL
		THEN bar_date_end-bar_date + 1 || (CASE 
											WHEN bar_date_end-bar_date > 0 THEN ' days'
											ELSE ' day'
											END)
		ELSE 'OPEN'
	END AS bar_type


/*
	bar_id,
	client_id,
	non_client_name_last,
	non_client_name_first,
	non_client_description,
	bar_date,
	bar_date_end,
	barred_by,
	bar_incident_location_code,
	bar_resolution_location_code,
	description,
	staff_involved,
	gate_mail_date,
	brc_elig_date,
	brc_client_attended_date,
	brc_resolution_code,
	appeal_elig_date,	
	reinstate_condition,
	brc_recommendation,
	comments,
	police_incident_number,
	added_by,
	added_at,
	changed_by,
	changed_at,
	is_deleted,
	deleted_by,
	deleted_at,
	deleted_comment,
	sys_log,
*/
FROM tbl_bar
WHERE NOT is_deleted;

CREATE VIEW  bar_current AS
SELECT * from bar
WHERE bar_date <= current_date
AND COALESCE(bar_date_end,current_date)>= current_date;

