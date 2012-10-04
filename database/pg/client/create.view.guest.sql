CREATE VIEW guest AS SELECT 
	*,
	CASE WHEN client_id IS NOT NULL THEN
		client_name(client_id)
	ELSE
	name_first
	|| COALESCE(' ' || name_middle,'')
	|| ' '
	|| name_last
	END AS name_full,
	(SELECT guest_identification_id FROM guest_identification_current WHERE client_id=tbl_guest.client_id LIMIT 1)
	AS identification_status
FROM tbl_guest WHERE NOT is_deleted;
