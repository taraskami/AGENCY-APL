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

function last_entry_f($clientid, $java=false) {

 	$def = get_def('entry');
	$res = get_generic(client_filter($clientid),'entered_at DESC','1',$def);
	if (!($entry=array_shift($res))) {
		return smaller('(no Entries)');
	}
	$entered = value_generic($entry['entered_at'],$def,'entered_at','view');
	$link = $java
		? seclink('entry',$entered,
			    'onclick="javascript:showHideElement(\'entryChildList\')"')
		: $entered;
	return 'Last Entry ('.value_generic($entry['entry_location_code'],$def,'entry_location_code','list').'): '
		. $link;
}

function show_pick_entry($locations,$prefs)
{
	$entry_locations = array();
	foreach ($locations as $key => $label) {
		$default = !be_null($prefs['entry_location'][$key]);
		$entry_locations[] = span(formcheck('entry_location['.$key.']',$default) . ' ' . $label
						  ,'class="radioButtonSet"');
	}
	$output =
		oline(bold('Select Entry Location(s)'))
		. formto($_SERVER['PHP_SELF'])
		. implode(oline(),$entry_locations)
		. hiddenvar('action','change_locations')
		. oline()
		. button('View','SUBMIT') 
		. formend();
	return $output;
}

?>
