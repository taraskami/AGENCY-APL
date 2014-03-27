CREATE TABLE tbl_work_order (
	work_order_id		SERIAL PRIMARY KEY,
	title			VARCHAR NOT NULL,
	description		TEXT NOT NULL,
	assigned_to		INTEGER REFERENCES tbl_staff (staff_id),-- NOT NULL,
	work_order_status_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_status (work_order_status_code) DEFAULT 'PENDING',
	priority		INTEGER NOT NULL DEFAULT 3 CHECK (priority BETWEEN 1 and 5),
	agency_project_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),
	work_order_category_code	VARCHAR NOT NULL REFERENCES tbl_l_work_order_category (work_order_category_code),
	housing_project_code	VARCHAR REFERENCES tbl_l_housing_project (housing_project_code),
	housing_unit_code	VARCHAR REFERENCES tbl_housing_unit (housing_unit_code),
	target_date		DATE,
	next_action_date	DATE,
	closed_date		DATE,
	hours_estimated		REAL,
	hours_actual		REAL,
		
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

CREATE VIEW work_order AS 
SELECT *
FROM tbl_work_order 
WHERE NOT is_deleted;

COMMENT ON COLUMN tbl_work_order.priority IS '1 = highest priority, 5 = lowest';

CREATE INDEX index_tbl_work_order_assigned_to ON tbl_work_order ( assigned_to );
CREATE INDEX index_tbl_work_order_added_by ON tbl_work_order ( added_by );
CREATE INDEX index_tbl_work_order_added_at ON tbl_work_order ( added_at );
CREATE INDEX index_tbl_work_order_target_date ON tbl_work_order ( target_date );
CREATE INDEX index_tbl_work_order_priority ON tbl_work_order ( priority );
CREATE INDEX index_tbl_work_order_agency_project_code ON tbl_work_order ( agency_project_code );
CREATE INDEX index_tbl_work_order_housing_project_code ON tbl_work_order ( housing_project_code );
CREATE INDEX index_tbl_work_order_housing_unit_code ON tbl_work_order ( housing_unit_code );
CREATE INDEX index_tbl_work_order_work_order_category_code ON tbl_work_order ( work_order_category_code );
CREATE INDEX index_tbl_work_order_next_action_date ON tbl_work_order ( next_action_date );
