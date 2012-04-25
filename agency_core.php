<?php
/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2012 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency-software.org/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AGENCY.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

/*
 * This file is for core AGENCY functions.
 * 
 * There are probably things in io.php and elsewhere that should be moved here.
 */

function is_enabled( $feature ) {
	switch ($feature) {
		// Features can be disabled here
		// FIXME:  This should be moved to a config file

		case 'some_disabled_feature' :
		case 'bar' :
		case 'residence_own' :
		case 'entry_visitor' :
		case 'charge_and_payment' :
			return false;
		default :
			return true;
	}
}

?>
