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

$engine['guest'] = array(
	'child_records' => array(
		'guest_identification','guest_authorization','guest_visit'
	),
	'list_fields'=>array('name_full','photo'),
	'fields' => array(
		'guest_photo' => array(
			'data_type' => 'attachment',
			'display_view'=>'hide',
			'display_list'=>'hide'
		),
		'photo' => array(
			'value' => 'guest_photo($rec["guest_id"])',
			'value_list' => 'guest_photo($rec["guest_id"],50,50)',
			'is_html'=>true,
			'display_add' => 'hide',
			'display_edit' => 'hide'
		),
		'client_id' => array(
			'display'=>'hide'
		),
		'identification_status' => array(
			'value' => '$x ? elink("guest_identification",$x,"on file") : "not on file" . add_link("guest_identification","Add ID now",NULL,array("guest_id"=>$rec["guest_id"]))',
			'is_html'=>true
		)
	)
);


?>
