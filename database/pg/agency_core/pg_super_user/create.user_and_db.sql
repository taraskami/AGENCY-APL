/*
 * This script will create the database user and the database
 *
 * It must be run by the Postgres superuser (usually "postgres")
 *
 * An install script might bypass this file
 */

CREATE
	USER agency
	PASSWORD 'PASSWORD'
	NOCREATEDB NOCREATEUSER;

CREATE
	DATABASE agency
	OWNER agency;

  \c agency

