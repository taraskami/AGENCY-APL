CREATE TABLE tbl_work_order_comment (
	work_order_comment_id		SERIAL PRIMARY KEY,
	work_order_id			INTEGER NOT NULL REFERENCES tbl_work_order (work_order_id),
	comment				TEXT NOT NULL,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
	
);

CREATE VIEW work_order_comment AS 
SELECT *
FROM tbl_work_order_comment 
WHERE NOT is_deleted;

