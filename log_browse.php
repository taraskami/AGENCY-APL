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

$quiet = true;
include 'includes.php';

/*
 * Basic log control page.
 * Variables passed are:
 * 
 * $action ( 
 *		show, show_client_logs -- All view log actions
 *		forward, back, first, last, browse -- All log index actions )
 * 
 * $LOG_POS ( position in log index ) (for browse) (now session variable)
 * $id ( ID # of log entry ) (for view)
 * $cid ( client ID for show_client_logs )
 */

//-------set session vars--------//

$LOG_POS     = $_SESSION['LOG_POS'];
$LOG_FILTER  = $_SESSION['LOG_FILTER'];
$LAST_ACTION = $_SESSION['LAST_ACTION'];
$LAST_ID     = $_SESSION['LAST_ID'];
$SHOW_LOGS   = $_SESSION['SHOW_LOGS'];
$DISPLAY     = $_SESSION['DISPLAY'];
$CID         = $_SESSION['LOG_CID'] = orr($_REQUEST['cid'],$_SESSION['LOG_CID']);

//------set global requested vars-----//

$pos           = $_REQUEST['pos'];
$pick_logs     = $_REQUEST['pick_logs'];
$client_select = $_REQUEST['client_select'];
$valid_client  = $_REQUEST['valid_client'];
$valid_id      = $_REQUEST['valid_id'];
$staff_select  = $_REQUEST['staff_select'];
$staff_add     = $_REQUEST['staff_add'];
$valid_staff   = $_REQUEST['valid_staff'];
$action        = $_REQUEST['action'];
$id            = $_REQUEST['id'];

$id = trim(orr($id,$LAST_ID));

process_quick_search('N',false); // "N" to Not stop after showing results, false to not auto-forward to client page

//-------add clients--------//
if ( isset($client_select) ) {

	outline( alert_mark() 
		. 'You have requested to add a reference to ' 
		. client_link($client_select) . " to this log (#{$id}).  "
		. hlink($_SERVER['PHP_SELF']."?valid_client=$client_select&valid_id={$id}&action=show&id={$id}",'Click here to confirm'),2);
	$action = 'show';

}

//-------add staff-------//
if ( isset($staff_select) && ($staff_add > 0) ) {

	outline( alert_mark() 
		. 'You have requested to add an alert for ' 
		. staff_link($staff_add) . " to this log (#{$id}).  "
		. hlink($_SERVER['PHP_SELF']."?valid_staff=$staff_add&valid_id={$id}&action=show&id={$id}",'Click here to confirm'),2);
	$action='show';

}

//the way our global searches and such work, these all must be unset here.
$staff_add     = $_REQUEST['staff_add'] = null;
$client_select = $_REQUEST['client_select'] = null;
$staff_select  = $_REQUEST['staff_select'] = null;


if (isset($_REQUEST['logs_index_count'])) {

	$logs_index_count = $_REQUEST['logs_index_count'];

  	if (intval($logs_index_count) < 1) {

		/*
		 * This should help the user avoid messy errors on 
		 * entering an incorrect logs per screen value
		 */

		$out .= oline(red('Invalid value for "Logs per page": ').bold($logs_index_count));
		unset($logs_index_count);

	} else {

		$logs_index_count = intval($logs_index_count);

	}

	$action = 'browse';

}

//---- find and merge user options ----//
$USER_PREFS = $AG_USER_OPTION->get_option('LOG_DISPLAY_OPTIONS'); //get log prefs

$USER_PREFS['pick_logs']   = $pick_logs   = orr($pick_logs,$USER_PREFS['pick_logs'],null);
$USER_PREFS['show_photos'] = $show_photos = orrn($_REQUEST['show_photos'],$USER_PREFS['show_photos'],null);

$logs_per_screen = $USER_PREFS['logs_per_screen'] = $_SESSION['LOGS_PER_SCREEN'] = $LOGS_PER_SCREEN = orr($logs_index_count,
																	    $_SESSION['LOGS_PER_SCREEN'],
																	    $USER_PREFS['logs_per_screen'],
																	    25);
if (isset($pick_logs)) {

	// set session variable
	$_SESSION['SHOW_LOGS'] = $SHOW_LOGS = $pick_logs;

	unset( $LOG_FILTER); // start with clean copy

	// and update filter for record selection
	foreach($GLOBALS['log_types'] as $key=>$dummy) {

		$key   = "in_$key";
		$value = $SHOW_LOGS[$key];

		if ($value) {

			$LOG_FILTER['in_dummy'][$key]=sql_true();

		} else {

			unset($LOG_FILTER['in_dummy'][$key]);

		}

	}

	/*
	 * $SHOW_LOGS includes "flagged_only", for the was_ fields
	 * This needs to get interpreted as a filter selection
	 */
	if ($SHOW_LOGS['flagged_only']) {

		$LOG_FILTER['was_dummy'] = array('was_assault_staff'  => sql_true(),
							   'was_threat_staff'   => sql_true(),
							   'was_assault_client' => sql_true(),
							   'was_police'            => sql_true(),
							   'was_medics'         => sql_true(),
							   'was_bar'            => sql_true(),
							   'was_drugs'          => sql_true());

	} else {

		unset ($LOG_FILTER['was_dummy']);

	}
	// Note--no break here, as it passes through to browse.

	// -- photo display -- //
	if (isset($_REQUEST['pick_logs'])) { //this means the form was submitted

		$LOG_POS=-1;  // this will force reset to end of log
		$show_photos = $USER_PREFS['show_photos'] = $_REQUEST['show_photos'] ? 'Y' : '';

	} else {

		$show_photos = $USER_PREFS['show_photos'];

	}

	$DISPLAY['photos']      = $show_photos;
	$_SESSION['DISPLAY']    = $DISPLAY;
	$_SESSION['LOG_FILTER'] = $LOG_FILTER;

}

/*
 * set log prefs - if changed
 */
$AG_USER_OPTION->set_option('LOG_DISPLAY_OPTIONS',$USER_PREFS);


$log_count = log_count();
if (isset($_REQUEST['log_jump_date'])) {

	$tmp_filter = $LOG_FILTER;
	if ($tmp_jump = dateof($_REQUEST['log_jump_date'],'SQL')) {

		// this should help users avoid messy errors on incorrect date entry
		$tmp_filter['<:added_at']=$tmp_jump;

	} else {

		$out .= oline(red('Incorrect date value: ').bold($_REQUEST['log_jump_date']));

	}

	$pos = max($log_count - count_rows($log_table,$tmp_filter) - $logs_per_screen,0);
	unset($tmp_filter);

}

if ( isset($valid_client) ) {

	post_client1($valid_id,$UID,$valid_client);
	$assigns = array_unique(get_staff_clients(array($valid_client),true));

	foreach($assigns as $assign) {

		if ($assign) {

			post_staff1($valid_id,$UID,$assign);

		}

	}

}

if ( isset($valid_staff) ) {

	post_staff1($valid_id,$UID,$valid_staff);

}

$action = orr($action,$LAST_ACTION,'browse');

$show_page_logs_link=hlink($_SERVER['PHP_SELF'].'?action=show_page_logs','show these logs on 1 page');

switch( $action ) {

	/* 
	 * view-mode actions
	 */
 case 'merge' :

	 include 'openoffice.php';
	 include 'zipclass.php';
	 
	 $a = get_log( $id );
	 
	 office_mime_header('writer');
	 $x=oowriter_merge($a,AG_TEMPLATE_DIRECTORY.'/log_print.sxw');
	 out($x->data());
	 page_close($silent=true); //no footer on oo files
	 exit;
	 
 case 'show' : 
	 
	 $a    = get_log( $id );
	 $mode ='view';
	 
	 break;
	 
 case 'show_client_logs' :
	 
	 $a          = get_logs_client( $CID );
	 $page_title = 'Displaying all ' . sql_num_rows( $a ) . ' logs for '
		 . client_link($CID) . "(ID # $CID)";

	 $multi = true;
	 $mode  = 'view';

	 break;

case 'show_page_logs' :

	$show_page_logs_link = hlink($_SERVER['PHP_SELF'].'?action=browse','return to log index');

	$a = get_logs( $LOG_FILTER,'log_id DESC ' . sql_limit($logs_per_screen,$LOG_POS));

	$multi = true;
 	$mode  = 'browse';

	break;

	/*
	 *browse-mode actions
	 */
 case 'browse' :  // this is index mode

	 if (isset($pos) && $pos >=0 && $pos <= max($log_count-$logs_per_screen,0) ) { 

		// if passed a _valid_ pos variable, set session var $LOG_POS to it.
		$LOG_POS = $pos;

	}

	if ( ! (isset($LOG_POS) && $LOG_POS >=0 && $LOG_POS <= $log_count-$logs_per_screen ) ) {

		// if invalid LOG_POS, set to end of log.
 		$LOG_POS = 0;
	}

	$multi = true;
	$mode  = 'browse';

	break;

case 'setoflogs' : // this is for a search set

	$page_title = 'Log Search Results';

	$mode  = 'browse';
	$multi = true;

	break;

}


switch ($mode) {

 case 'view' :
	 if ( !$a || (sql_num_rows($a)<1)) {

		 $commands   = array(bottomcell(log_view_navbar( $id,'bogus','bogus' )));
		 $page_title = "Requested Log ($id) Does Not Exist";
		 $out        = oline($page_title);

	} else {

		$page_title = oline(bigger(bold("Log $id")));

		$commands = $multi ? array() : array(log_view_navbar($id));
		$out .= log_show( $a, $DISPLAY['photos'] );

		$client_select = formto()
			. hiddenvar('id',$id)
			. client_selector('N','Search to add '.AG_MAIN_OBJECT.':','N')
			. formend();
				
		$staff_select = formto()
			. hiddenvar('id',$id)
			. staff_selector('N','N')
			. formend();

		array_push($commands,bottomcell($client_select));
		array_push($commands,bottomcell($staff_select,'class="staff" style="text-align: center;"'));

	 }

	break;

case 'browse' :

	if ( !isset($a) && isset($LOG_FILTER) ) {

       	$a = get_logs_bypos( $LOG_FILTER, $LOG_POS, $logs_per_screen );

	}

	$page_title= $LOG_FILTER ? 
		'Viewing log entries ' . ( $log_count - ($LOG_POS+sql_num_rows($a) - 1) ) .'-->' .($log_count - $LOG_POS)
		: 'Select Logs to View';

	$our_title = 'Of the ' . red($log_count) . " total combined logs, $page_title";

	$commands = $LOG_FILTER
		? log_top_navcell()
		: cell(alert_mark('Use Checkboxes to Select Log(s) to View'),'align="center" bgcolor="'.$colors['nav'].'"');

	$commands =array($commands,bottomcell(show_pick_logs(),'class="pick" align="center"'));

	if (isset($LOG_FILTER) && sql_num_rows($a)==0) {

		outline(alert_mark('Your selection contains no log entries'));

	} elseif ($LOG_FILTER) { // ??? (OK, if no LOG_FILTER, user needs to select logs first)

		$out .= oline(center(bold(bigger($our_title)) //. " ($log_count logs total)"
					   . smaller(oline() . '(' . $show_page_logs_link . ')')),2)
			. oline(center( 
					$action == 'show_page_logs' 
					? log_show( $a,$DISPLAY['photos'] , true)
					: show_log_heads( $a,$DISPLAY['photos'],true) ))
			. tablestart('','bgcolor="#1DF733" class="" cellpadding="10" cellspacing="0" align="center"')
			. log_top_navcell()
			. tableend();

	}

}

/*
 * Done with browse or view, create output
 */
$title = strip_tags($page_title);
agency_top_header($commands);
out( $out );

$LAST_ACTION = $action;
$LAST_ID     = $id;

page_close();

//--------setting session vars here--------//
/*
 * A just-in-case, better safe than sorry, saving of session variables
 */
$_SESSION['LOG_POS'] = $LOG_POS;
$_SESSION['LAST_ACTION'] = $LAST_ACTION;
$_SESSION['LAST_ID'] = $LAST_ID;

?>

