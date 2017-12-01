BEGIN;

/*
 * db_mod.TEMPLATE
 */
 
/*
NOTE:  I believe it is not possible to know the
git SHA ID before actually making a commit.

It is possible to know a git tag, and include
that in the commit.
*/

INSERT INTO tbl_db_revision_history 
	(
	db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by
	)

	 VALUES (
		'REPORT_DYNAMIC_PIVOT', /*UNIQUE_DB_MOD_NAME */
		'Add support for dynamic crosstab/pibot/chart as report_block_type',
		'AGENCY_CORE', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.76', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );
INSERT INTO tbl_l_report_block_type VALUES ('PIVOT','Dynamic Pivot/Crosstab/Chart',sys_user(),current_timestamp,sys_user(),current_timestamp);

COMMIT;

