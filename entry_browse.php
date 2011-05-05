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

// FIXME: if search term is entered, should go to QuickSearch
//        currently is just silently ignored

$quiet='Y';
include 'includes.php';

$def=get_def('entry');

if (isset($_REQUEST['enterClientUndo'])) {
	// Delete most recent post
	$d_c=$_REQUEST['enterClientUndo'];
	$d_filter=$_SESSION['ENTRY_BROWSE_UNDO_RECORD'];
	if ($d_c==$d_filter[$_SESSION['ENTRY_BROWSE_UNDO_FIELD']]) {
		if (delete_generic($d_filter,$def,'Auto-deleted by entry_browse undo')) {
			$delete_result = 'Deleted entry record for ' . client_link($d_c) . '@' . datetimeof($d_filter['entered_at']);
		} else {
			$delete_result = oline('Failed to delete entry record for ' . client_link($d_c) . '@' . datetimeof($d_filter['entered_at']));
		}
	}
	unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
	unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
}

if (isset($_REQUEST['enterClient'])) {
	// Client has been entered and sent from previous page
	$e_c=$_REQUEST['enterClient'];
	$id_field=$_REQUEST['enterClientField'];
	if (is_numeric($e_c) and $e_c==intval($e_c) and preg_match('/^([a-z_0-9])+$/i',$id_field)
		// don't allow same client to be entered twice, consecutively
		and($_SESSION['ENTRY_BROWSE_LAST_RECORD'][$id_field] != $e_c)) {
		$rec=array();
		$cont=array('action'=>'add','object'=>'entry','id'=>'dummy');
		blank_generic($def, $rec,$cont);
		$rec[$id_field]=$e_c;
		$rec['entered_at']=datetimeof('now','SQL');
		$rec['entry_location_code']='SHELTER';  // FIXME
		$rec['added_by']=$rec['changed_by']=$UID;
		$rec['sys_log']='auto-added by entry_browse';
		if (post_generic($rec,$def,$message)) {
			$_SESSION['ENTRY_BROWSE_UNDO_RECORD']=$_SESSION['ENTRY_BROWSE_LAST_RECORD']=$rec;
			$_SESSION['ENTRY_BROWSE_UNDO_FIELD']=$id_field;
			$undo_link=hlink($_SERVER['PHP_SELF'].'?enterClientUndo='.$e_c,smaller(' (Undo)'));
			$post_result='Posted ' . $def['singular'] .' for ' . client_link($e_c) . ' ' . $undo_link;
		} else {
			unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
			unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
			$post_result='Failed to post ' . $def['singular'] . 'for ' . client_link($e_c);
		}		
	} else {
		unset($_SESSION['ENTRY_BROWSE_UNDO_RECORD']);
		unset($_SESSION['ENTRY_BROWSE_UNDO_FIELD']);
	}
}
	
$c_req=$_REQUEST['control'];
if (!is_array($c_req)) {
    $c_req = unserialize_control($c_req);
}
if ($c_req['list'] and (!is_array($c_req['list']))) {
    $c_req['list']=unserialize_control($c_req['list']);
}
foreach ($engine['control_pass_elements'] as $key) {
        $tmp_control[$key] = $control[$key];
}
$_SESSION['LAST_CALLED_CONTROL_VARIABLE'] = $tmp_control;

/* find current entry locations */
$e_locations=get_generic(array('is_current'=>sql_true()),NULL,NULL,get_def('l_entry_location'));
while ($loc=sql_fetch_assoc($e_locations)) {
	$AG_ENTRY_LOCATIONS[$loc['entry_location_code']]=$loc['description'];
}

//---- find and merge user options ----//
foreach (array_keys($AG_ENTRY_LOCATIONS) as $tmp_key) {
	$tmp_entry_defaults[$tmp_key] = true;
}

$USER_PREFS = $AG_USER_OPTION->get_option('ENTRY_DISPLAY_OPTIONS'); //get entry prefs
$USER_PREFS['entry_location'] = orr($_REQUEST['entry_location'],$USER_PREFS['entry_location'],$tmp_entry_defaults);
$AG_USER_OPTION->set_option('ENTRY_DISPLAY_OPTIONS',$USER_PREFS); //set entry prefs - if changed

$control=array(
	'action'=>'list',
	'object'=>'entry',
	'id'=>'list',
	'page'=>$_SERVER['PHP_SELF'],
	'list'=>array('filter'=>array('IN:entry_location_code'=>array_keys($USER_PREFS['entry_location'])),
	'fields'=>array('entered_at','entry_location_code','client_id'),
	'display_add_link'=>false,
	'no_controls'=>false));

foreach ($c_req['list'] as $key=>$value) {
	$control['list'][$key]=$value;
}

$ef_step=orr($ef_step,'continue');  // don't think this does anything?
$ef_rec=orr($ef_rec,array('client_id'=>NULL));
$ef_control=array('action'=>'add','object'=>'client','id'=>'dummy');
engine_process_quicksearch($ef_step,$ef_rec,$ef_control);
$ef_def=get_def('client');
$ef_def['fields']['client_id']['display_add']='edit';
$entry_form='Record an ' .$def['singular'] .': '.formvartext('enterClient','','class="enterClient"').hiddenvar('enterClientField','client_id');
$entry_form=form($entry_form,'','','class="enterClient"');
$entries=call_engine($control,'',true,false,$DUMMY,&$PERMISSION);
$page_title = 'Viewing ' . ucfirst($def['plural']) . ' for ' . implode(', ',array_keys($USER_PREFS['entry_location']));
html_header($page_title,$refresh_rate);
$commands=array(cell(show_pick_entry($AG_ENTRY_LOCATIONS,$USER_PREFS),'class="pick"'));
agency_top_header($commands);	
$out .= html_heading_1($page_title)
	. ($post_result ? div($post_result,'','id="postClientResult"') : '')
	. ($delete_result ? div($delete_result,'','id="deleteClientResult"') : '')
	. div($entry_form,'','id="enterClientForm"')
	. oline($entries);
out($out);
page_close();

?>

