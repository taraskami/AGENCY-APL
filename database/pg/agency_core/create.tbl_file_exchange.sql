DROP VIEW IF EXISTS file_exchange;
DROP TABLE IF EXISTS tbl_file_exchange;

CREATE TABLE tbl_file_exchange (
	title					VARCHAR,
	file_exchange_id		SERIAL PRIMARY KEY,
	file_attachment			INTEGER NOT NULL,
	recipient_list			INTEGER[],
	comment					TEXT,

	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id)
							CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment			TEXT,
	sys_log					TEXT

--	CONSTRAINT housing_unit_or_client CHECK ( xor(housing_unit_code IS NOT NULL,client_id IS NOT NULL))
);

CREATE VIEW file_exchange AS SELECT 
*
FROM tbl_file_exchange 
WHERE NOT is_deleted;

--CREATE INDEX index_tbl_file_exchange_client_id ON tbl_file_exchange ( client_id );
