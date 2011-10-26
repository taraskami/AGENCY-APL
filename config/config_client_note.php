<?php
/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2009 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency.sourceforge.net/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with CHASERS.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

$engine['client_note']=array(
					'allow_object_references'=>array('bar','client'),
				     'list_fields'=>array('added_at','added_by','note','flag_entry_codes'),
	 'fields'=>array(
			     'is_front_page'=>array('label'=>'Front Page?',
							    'comment'=>'Select "No" to archive this note'."\n".'(i.e. remove from prominent display)'
							    ),
			     'flag_entry_codes' => array('label'=>'Flag For Entry Notification',
					'data_type'=>'lookup_multi',
					'lookup_format'=>'checkbox_v',
					'lookup'=>array('table' => 'l_entry_location',
						'value_field'=>'entry_location_code',
						'label_field'=>'description'
					),
					'comment'=>'Selecting a location will display note at that location when ' . AG_MAIN_OBJECT . ' enters.'),
				)

	 );
		
?>
