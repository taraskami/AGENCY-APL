CREATE TABLE db_revision_history (
	db_modification_id	VARCHAR(50) PRIMARY KEY,
	cvs_code_version		VARCHAR(50),
	added_at			TIMESTAMP NOT NULL,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	comments			TEXT,
	sys_log			TEXT
);

CREATE UNIQUE INDEX db_revision_history_db_modification_id_key ON db_revision_history ( lower(db_modification_id));
