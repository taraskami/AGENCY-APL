/*
 * This script will create the database user and the database
 *
 * It must be run by the Postgres superuser (usually "postgres")
 *
 * An install script might bypass this file
 *
 * It might be better to just do this part by hand,
 * So I'm commenting it out.  If you want to run it,
 * uncomment and know what you're doing.
 *
 */

/*
--Create user
CREATE
	USER my_user -- No quotes
	PASSWORD 'my_pass' -- Single quotes
	NOCREATEDB NOCREATEUSER;

--Create database
CREATE
	DATABASE my_db -- No quotes
	OWNER my_user;  -- No quotes

-- Connect to database
  \c fff
*/
