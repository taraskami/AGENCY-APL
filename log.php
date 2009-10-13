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

function remove_id( $id, &$selected )
{
	// pass id of 0 means erase array, for some reason
	$new=array();
    if ($id>0)
	{
		foreach($selected as $ids)
		{
			if (!($id==$ids))
			{
				add_id($ids,$new);
			}
		}
	}
	$selected=$new;
}

function log_query($label = "Log Search")
{
        return tablestart('','class="log"') . row( cell ( formto('search.php')
							  . formvartext('QuickSearch',$_REQUEST['QuickSearch'])
							  . hiddenvar('QSType','log')							  
							  . button($label)
							  . formend()
							  )) . tableend();
}

function next_log($id,$op=">")
{
	// although called next_log, you can have this return the previous
	// log by feeding in a "<" as argument 2.
	// This function relies on $LOG_FILTER

	// I am overhauling this function, since all it is doing 
	// is getting either the next or the previous id -jh
	global $LOG_FILTER;
	$tmp = sql_fetch_assoc(get_generic(array('log_id'=>$id),'','','log'));
	$tmp_filter=$LOG_FILTER;
	if ($tmp['added_at']) {
		$tmp_filter[$op.':added_at']=$tmp['added_at'];
		$a = get_generic($tmp_filter,'added_at'.($op=='<' ? ' DESC' : ''),'1','log');
		if (sql_num_rows($a) == 1) {
			$aa = sql_fetch_assoc($a);
			return $aa['log_id'];
		}
	}
	return false;
}

function tmpClientPageLogs($logs)
{
      //TEMPORARY FUNCTION UNTIL ENGINE HANDLES LOGS
        global $colors, $UID;
        if (sql_num_rows($logs)==0 )
        {
          $result .= oline("No Logs to Display");
          return $result;
        }
        $result .= tablestart("","border=5")
          . row(
          cell(bold("Time")) .
          cell(bold("Log #")) .
          cell(bold(ucwords(AG_MAIN_OBJECT)."s")) .
          cell(bold("subject")) );

        while($log=sql_fetch_assoc($logs))
        {
              $st=sql_to_php_array($log['_staff_alert_ids']);
              $cl_links=client_link(sql_to_php_array($log['_client_links']));
			  $cm=sql_to_php_array($log['_case_mgr_id']);
              // if alert to this staff, use $alert_color:
              $color = (in_array($UID,$st)) ? " bgcolor=\"${colors['alert']}\"" : "";
              $Snippet = orr($log["subject"],$log["snippet"]);
              $in_logs=which_logs($log,"log_");
              $authorized = (!$in_logs) // client_only entry
              || has_perm($in_logs,"R") // log_specific permission
              || in_array($UID,$st)  // Flagged to user's attention
              || in_array($UID,$cm); //case manager
              $result .= row(
                     cell( dateof($log["added_at"]) . "<br>" .
                           smaller(timeof($log["added_at"])))
                     . cell( $log["log_id"] . smaller("<br>" . oline(staff_link($log["added_by"],$log['_added_by']))
                                                      . which_logs_f($log),3) )
                     . cell( orr(implode('<BR>',$cl_links),'(no '.AG_MAIN_OBJECT.'s referenced)'),'class="client"')
                     . cell( $authorized
                             ? log_link($log["log_id"],webify($Snippet))
                             : "(Not authorized to view)",$color ));
        }
        $result .= tableend();
        return $result;
}

function show_log_heads( $logs, $photos="N", $reverse=false)
{
	global $colors, $UID;
// needs added_at, added_by, log_id, Snippet, subject
	if (sql_num_rows($logs)==0 )
	{
		$result .= oline("No Logs to Display");
		return $result;
	}
	$result .= tablestart("","border=5")
		. row( 
		cell(bold(oline('When/') . 'Log #')) .
		cell(bold(oline('By Whom/') . 'Log(s)')) .
		cell(bold(ucwords(AG_MAIN_OBJECT)."s")) .
		cell(bold("subject")) ); 
 
	$rows = '';
	while($log=sql_fetch_assoc($logs)) {
		$st=get_alerts_for_log( $log["log_id"],"NF" );
		$cl=get_clients_for_log( $log["log_id"],"NF");
		// if alert to this staff, use $alert_color:
		$color_ref = (in_array($UID,$st)) ? " bgcolor=${colors["alert"]}" : "";
		// if log by this staff, use $alert_color:
		$color_by = ($UID==$log['added_by']) ? " bgcolor=${colors["alert"]}" : "";
		$Snippet = orr($log["subject"],$log["snippet"]);
		$in_logs=which_logs($log,"log_");
		$authorized = (!$in_logs) // client_only entry
			|| has_perm($in_logs,"R") // log_specific permission
			|| in_array($UID,$st)  // Flagged to user's attention
			|| in_array($UID,get_staff_clients($cl)); //staff assigned to client
		$tmp_row = row( 
				   cell( smaller(oline(dateof($log["added_at"])) . 
					   oline(timeof($log["added_at"]))
						. bold(italic("#" . $log["log_id"])),2))
				   . cell( smaller(oline(staff_link($log["added_by"]))
										. which_logs_f($log),1), $color_by ) 
				   . cell( orr( get_clients_for_log( $log["log_id"],"<br>",$photos), 
						    "(no ".AG_MAIN_OBJECT."s referenced)" ),'class="client"')
				   . cell( $authorized
					     ? log_link($log["log_id"],webify($Snippet))
					     : "(Not authorized to view)" ,$color_ref));
		if ($reverse) { 
			$rows = $tmp_row . $rows; 
		} else {
			$rows .= $tmp_row;
		}
    }
	$result .= $rows . tableend();
	return $result;
}

function log_view_navbar( $id , $sep=", ")
{
	global $colors;
	$after= smaller(
	formto()
	. hiddenvar("action","show")
	. "Log # "  
	. formvartext("id", $id,"size=7")
	. button("Go") 
	. formend()
	.  oline(" " 
	. link_next_log($id) 
	. $sep . link_prev_log($id))
	. link_index_log()
	. $sep . link_add_log() 
		);
	return bottomcell($after,'align="center" bgcolor="'.$colors["nav"].'"');
}

function log_browse_navbar( $sep=" | " )
{
	return link_first_log()
	. $sep . link_last_log()
	. $sep . link_back_log()
	. $sep . link_forw_log()
	. $sep . link_add_log();
}

function log_top_navcell($options="")
{
	global $logs_per_screen,$colors;
	return bottomcell( 
	smaller(right(log_jump_index_button()
	. log_browse_navbar()))
	,
	'cellpadding="4" align="center" bgcolor="'.$colors["nav"].'" style="white-space: nowrap;"' );
}

function link_back_log($label="Back")	// reminder--back/forward = browse,
{				// next/prev = view
	global $log_page, $LOG_POS,$logs_per_screen;
// 	$back = max($LOG_POS-$logs_per_screen,0);
	$logs=log_count();
	$back = min($LOG_POS+$logs_per_screen,$logs-$logs_per_screen);
// 	return ($LOG_POS < 1)
	return ($LOG_POS >= ($logs-$logs_per_screen))
	? gray($label)
	: hlink( "$log_page?action=browse&pos=$back", $label);
}

function link_forw_log( $label="Forward")
{
	global $log_page, $LOG_POS, $logs_per_screen;
	$logs=log_count();
// 	$forward = min($LOG_POS+$logs_per_screen,$logs-$logs_per_screen);
	$forward = max($LOG_POS-$logs_per_screen,0);
// 	return ($LOG_POS >= ($logs-$logs_per_screen ) )
	return ($LOG_POS < 1)
	? gray($label)
	: hlink( "$log_page?action=browse&pos=$forward", $label);
} 
       
function link_first_log($label="First")
{
	global $log_page, $logs_per_screen,$LOG_POS;
	return  ( ($LOG_POS+$logs_per_screen) >= log_count() )
		? gray($label)
		: hlink( "$log_page?action=browse&pos=" . (log_count()-$logs_per_screen), $label);
} 

function link_last_log($label="Last")
{
	global $log_page,$LOG_POS;
	return ( $LOG_POS==0 ) 
		? gray($label)
		: hlink( "$log_page?action=browse&pos=0", $label);
} 

function link_client_logs( $cid )
{
	global $log_page;
	return hlink( $log_page . "?action=show_client_logs&cid=$cid","Show full text of these logs");
}

function link_index_log($label="Index",$type="link")
{
	global $log_page;
	return ($type == "Button") 
		?	button_link( "$log_page" . "?action=browse",$label )
		:	hlink( "$log_page" . "?action=browse",$label );
}

function link_next_log( $id , $label = "Next" )
{
	global $log_page;
	return ( $a = next_log($id) ) 	// if at end, return inactive (non-link)
	? hlink( "$log_page" . "?action=show&id=$a",$label )
	: gray($label);
}

function link_prev_log( $id , $label="Previous" )
{
	global $log_page;
	return ( $a = next_log($id,"<") ) // if at beginning, return inactive (non-link)
	? hlink( "$log_page" . "?action=show&id=$a",$label )
	: gray($label);
}

function link_add_log($label="New")
{
	global $log_add_page;
	return db_read_only_mode() ? dead_link($label) : hlink( "$log_add_page?action=new", $label );
}

function log_jump_index_button($before="",$after="")
{
// changed this so it jumps to a date
	global $log_page;
	return  smaller(formto( $log_page )
		. $before
 		. "Date: " . hiddenvar("action","browse")
                . formvartext("log_jump_date","","size=7")
                . button( "Jump" )
		. $after
        . formend());
}

function log_count($filter="")
{
	global $log_table, $LOG_FILTER;
	static $log_count,$last_filter;
	$filter=orr($filter,$LOG_FILTER);
	$log_count = ($log_count && ($last_filter==$filter)) 
			? $log_count 
			: count_rows($log_table,$filter);
	$last_filter=$filter;
	return $log_count;
}

function get_logs_bypos($filter,$start,$count)
{
	global $log_table;
	return get_logs($filter,"$log_table.added_at DESC" . sql_limit($count,$start));
}

function get_logs_client($idnum)
{
		global $log_table,$log_select_sql;

		$def = get_def('client_ref');

		$ref_filter['client_id']=$idnum;
		$ref_filter['ref_table']='LOG';
		$ref_filter=read_filter($ref_filter);
		$log_filter["FIELDIN:$log_table.log_id"]="(SELECT ref_id FROM ".$def['table']." WHERE $ref_filter)";
		return agency_query($log_select_sql,$log_filter,"$log_table.added_at");
}

function log_link( $idnum, $label="lookup" )
{        // Doesn't do Validate or Match
        global $log_page,$log_table;
		if ($label=="lookup")
		{
			$label =sql_assign("SELECT SUBSTRING(COALESCE(subject,log_text) FROM 0 FOR 50) FROM $log_table",array($log_table . "_id" => $idnum));
		}
        return (hlink($log_page . "?action=show&id=".  $idnum,$label));
}

function get_log( $id )
{
	global $log_table, $log_table_id,$log_select_sql;
	return agency_query( $log_select_sql, array("$log_table_id"=>$id) );
}

function get_logs( $filter, $order="" )
{
	global $log_table, $log_table_id,$log_select_sql;
	return agency_query( $log_select_sql, $filter, $order );
}

function which_logs_f( $log, $sep=", ")
{
// take a log record, and return a formatted string of which logs it's in
	global $log_types;
	$in_logs=which_logs( $log );
	foreach ($in_logs as $l) {
		$in_logs_f[] = $log_types[$l];
	}
	$in_logs_text=implode($sep,orr($in_logs_f,array()));
	return blue(orr($in_logs_text,AG_MAIN_OBJECT." record only"));
}

function which_logs( $log,$pre="" )
{
// take a log record, and return an array of which logs it's in
	global $log_types;
	$in_logs_list=array();
	foreach ($log_types as $key=>$value)
	{
		if ($log["in_$key"]==sql_true())
		{
			array_push($in_logs_list,"$pre$key");
		}
	}
	return $in_logs_list;
}

// display links to logs that references this $log
function show_log_references($log_id)
{
	if (!$log_id)
	{	
		return false;
	}
	$refs = get_log_references($log_id);
	return sql_num_rows($refs)>0
		?  bigger(bold("This log is referred to in the following logs:")) 
			.  show_log_heads($refs)
		:	"";
}

function log_show1( $ary, $clients, $staff,$photos="N", $log_refs=true )
// (never use variable names from examples like $ary--they live on
// and are too much of a pain to change!)
{
// takes one log as an array, and displays it

	global $colors,
        $show_bugzilla_url,$log_types, $PRINTER_FRIENDLY,$UID;
	$cl_links = get_clients_f( $clients, "<br>",$photos);
	$al_links = get_staff_f( $staff, "<br>");
	$auth_warning = "(You are not authorized to read this log entry.)";
	$in_logs = which_logs($ary,"log_");
	// This is an odd construction, but if the log is in the
	// "client file only", it's not in any logs.  In this case,
	// we're granting authorization here.  In the future, we might
	// want to add a has_per("client") option to control all
	// access to client data, but for not anyone can view client pages.
      $authorized = (!$in_logs) // client_only entry
		|| has_perm($in_logs,"R") // log_specific permission
		|| in_array($UID,$staff)  // Flagged to user's attention
		|| in_array($UID,get_staff_clients($cl)); //staff assigned to client

// Computer Flag Fields Here:
// need to be 'Y' to display, 
// ??? We should use the array in log_add.php called $fields which has
// valid field names and their labels

	$flags = sql_true($ary["was_police"]) ? oline(bold("Police Were Called")) : "";
	$flags .= sql_true($ary["was_medics"]) ? oline(bold("Medics Were Called")) : "";
	$flags .= sql_true($ary["was_assault_client"]) ? oline(bold("Client Injured or Assaulted")) : "";
	$flags .= sql_true($ary["was_assault_staff"]) ? oline(bold("Staff Injured or Assaulted")) : "";
	$flags .= sql_true($ary["was_threat_staff"]) ? oline(bold("Staff Threatened")) : "";
	$flags .= sql_true($ary["was_drugs"]) ? oline(bold("Drugs or Alcohol Involved")) : "";
	$flags .= sql_true($ary["was_bar"])  ? oline(bold("Client(s) Barred")) : "";
	$text_color=$colors["text"];
	if ($PRINTER_FRIENDLY=="Y")
	{
    	$cl_links = strtolower(get_clients_f( $clients, ", "));
    	$al_links = strtolower(get_staff_f( $staff, ", "));
        $text_color="";
		$display =  ($authorized ? oline(bigger(bold(underline($ary["subject"]))) 
                    . " (log #" . $ary["log_id"] . ", @ " . datetimeof($ary["added_at"],"US")
					. ") ") 
                    . oline($ary["log_text"]) : oline(red(italic($auth_warning)))) 
                    . (dateof($ary["occurred_at"]) ? 
                        oline("Event time was " .
                        datetimeof($ary["occurred_at"],"US")) : "")
                    . oline(red($flags))
                    . tablestart("")  . row( 
                        rightcell(bold("Alerts:  "),"VALIGN=TOP") 
                        . cell(orr($al_links,"No Staff Alerts")))  
                    . row(
                        rightcell(bold(ucwords(AG_MAIN_OBJECT)."s:  "),"VALIGN=TOP") 
                        . cell($cl_links ? $cl_links : "No ".ucwords(AG_MAIN_OBJECT)."s Referenced"))
                    . row(rightcell(bold("Posted by:  "))
                        . cell(strtolower(staff_link($ary["added_by"]))
                        . smaller(" (in logs " . which_logs_f($ary) . ")")))
					. tableend()
                    . oline(($ary["md5_sum"] ?
		                smaller( "(MD5 = " . $ary["md5_sum"] .")&nbsp; ",3 ) : "")
        		    . ( $ary["old_hash_code"] ?
		                smaller( "(old_hash_code = " . $ary["old_hash_code"]
                        .")" ) : ""))
                    ;
	}
	else
	{
		$display =
			oline(bold(bigger($ary["Title"],2))
				. smaller( " (logs: " ) 
				. which_logs_f($ary). smaller(")"),2)
			. tablestart("",'width=90% class=""') 
			. ($flags ? row(cell(red($flags),"colspan=3")) : "")
			.  row( cell( $cl_links ?  
					  bold("Concerning ".ucwords(AG_MAIN_OBJECT)."s:<br>$cl_links") :
					  "(no ".AG_MAIN_OBJECT."s are referenced in this log entry)",
					  'class="client"')
				  . cell( $al_links ? bold("Staff Alerts:<br>" . $al_links) 
					    : "No Staff Alerts", 'class="staff"')
				  . cell( oline("Posted by " . staff_link($ary["added_by"])) .
					    ( $ary["added_at"] ? 
						oline("Posted at " 
							. bold(dateof($ary["added_at"]) .  " " 
								 . timeof($ary["added_at"])))
						: "" )
					    . ( dateof($ary["occurred_at"]) ?  
						  oline("Event time was "
							  . bold(dateof($ary["occurred_at"]) . " " 
								   . timeof($ary["occurred_at"] ))) 
						  :  "" )
					    . ( $ary["md5_sum"] ?
						  oline(smaller( "(MD5 = " . $ary["md5_sum"] .")" )) : "")
					    . ( $ary["old_hash_code"] ?
						  oline(smaller( "(old_hash_code = " . $ary["old_hash_code"] .")" )) : "")
					    ,'class="info"' ))
			. tableend()
			. oline("");

		// Show Log Entry Here:
		$log_text=htmlspecialchars($ary["log_text"]);
		$log_text=nl2br(wordwrap($log_text,80));
		$log_text = hot_link_objects( $log_text);
		if ( $ary["subject"] )
		{
			$subject = hot_link_objects(webify($ary['subject']));
			$subject = oline( bigger(center(bold(underline($subject))),2));
		}
		$log_text = oline(bigger( $log_text,1),2 );
		$display .= $authorized 
// 			? oline(box($subject . $log_text,$text_color,"border=7"))
			? table(row(cell($subject . $log_text)))
			: oline(bigger(red(italic("(You are not authorized to read this log entry.)"))));
		// This is a huge performance drain, especially for "show all logs on 1 page"
		if ($log_refs)
		{
			$display .= $ary["log_id"] ? show_log_references($ary["log_id"]) : "";
		}
	} // end output if
	return $display;
}

function get_log_references($logid)
{
/*
	MAKE SURE that changes to get_log_references or
	hot_link_log are made in both functions, so that
	whatever logs are hot_linked are also reflected
	as references.
*/
	global $log_table, $log_select_sql;
	if (!$logid)
	{
		return false;
	}
/*
	// This does on-the-fly matching, which is very slow with large logs
	$filter["~*:log_text"]="logs?([#, &-]|and|entry|no|number|([0-9]{1,7}([#, &-]|and)+)?)*$logid([^0-9]|$)";
	$filter["~*:subject"]="logs?([#, &-]|and|entry|no|number|([0-9]{1,7}([#, &-]|and)+)?)*$logid([^0-9]|$)";
*/
	$filter["FIELDIN:log_id"]="(SELECT from_id FROM reference WHERE to_id = $logid AND to_table='log')";
	return agency_query($log_select_sql,array($filter));
}

function log_show( $logs,$photos="N",$reverse=false )
{
	global $log_table_id;
	$count = sql_num_rows( $logs );
	$log_refs=true;
	if ($count > 1) {
		$output .= smaller(italic(oline("(Viewing multiple logs.)",2)));
	}

	while ($ary = sql_fetch_assoc($logs)) {
		$ary["Title"]="Log " . $ary["$log_table_id"];
		$clients = get_clients_for_log( $ary["log_id"], "NF");
		$staff = get_alerts_for_log( $ary["log_id"], "NF");
 		$tmp = oline(log_show1( $ary, $clients, $staff,$photos,$log_refs ))
 				. hrule();
		if ($reverse) {
			$output = $tmp . $output;
		} else {
			$output .= $tmp;
		}
	}
	return $output;
}

function show_log_types($picks="",$formvar="",$sep="")
{
// create options to choose which logs to view
    global $log_types;
	$formvar=orr($formvar,"pick_logs");
	$picks=orr($picks,array());
	// set up checkboxes for each log
    foreach($log_types as $code => $label)
    {
        $key="in_$code";
        $output .= span(formcheck("{$formvar}[$key]",$picks[$key]) . "&nbsp;$label $sep");
    }
    return $output;    
}

function show_pick_logs()
{
// allow user to select which logs to view
	global $SHOW_LOGS,$logs_per_screen,$DISPLAY;
	$output = formto($_SERVER['PHP_SELF'])
		. table(row(cell(
				     oline( 
					     smaller(hard_space("Logs per page "))
					     . formvartext("logs_index_count",$logs_per_screen,"size=5")
					     . formcheck("show_photos",$DISPLAY["photos"]) 
					     . smaller(hard_space(" Show Photos"))
					     . formcheck("pick_logs[flagged_only]",$SHOW_LOGS["flagged_only"]) 
					     . smaller(hard_space(' "Red-flagged" Only')),2),' style="white-space: nowrap;"'))
			  ,'',' cellpadding="0" cellspacing="0" class="pick"')
		. smaller(hard_space("Logs to view: ") . show_log_types($SHOW_LOGS))
		. hiddenvar("action","pick_logs")
		. button("View","SUBMIT") . smaller(help("Logs","","Help!",'class="fancyLink"')) 
		. formend();
	return $output;
}

function get_log_engine($filter,$order='',$limit='',$def,$control=null) //same params as get_generic
{ //this would be named get_log, but that function already exists
	$log_table = $def['table'];

	$ref_def = get_def('client_ref');
	$ref_table = $ref_def['table'];

	$a_def = get_def('alert');
	$alert_table = $a_def['table'];
	if (array_key_exists('client_id',$filter)) { //we'll be requiring a left join on the client_ref table
		$sql = "SELECT DISTINCT log.*,
                          array(SELECT t.staff_id FROM $alert_table AS t WHERE t.ref_id=$log_table.log_id) AS _staff_alert_ids,
array(SELECT distinct client_id FROM $ref_table WHERE ref_table='LOG' AND ref_id=log.log_id) AS _client_links,
/*
						array(SELECT distinct client_link(client_id) FROM $ref_table WHERE ref_table='LOG' AND ref_id=log.log_id) AS _client_links,
*/
                          array(SELECT coalesce(staff_id,0) FROM staff_assign WHERE client_id IN 
                                     (SELECT distinct client_id FROM $ref_table WHERE ref_table='LOG' AND ref_id=log.log_id)
                                      AND staff_assign_date_end IS NULL
                          ) AS _case_mgr_id
                  FROM {$log_table} AS log 
                     LEFT JOIN ${ref_table} AS ref ON (ref.ref_table='LOG' AND ref.ref_id=log.log_id)";
		$def['sel_sql']=$sql;
	} elseif ($control['action']=='list' && in_array('client_id',$control['list']['fields'])) {
		$sql="SELECT DISTINCT log.*, ref.client_id 
                  FROM {$log_table} AS log 
                     LEFT JOIN ${ref_table} AS ref ON (ref.ref_table='LOG' AND ref.ref_id=log.log_id)";
		$def['sel_sql']=$sql;
	}

	return get_generic($filter,$order,$limit,$def,$control);
}

function engine_record_perm_log($control,$rec,$def) {

	global $UID;

	if ($control['action']=='add') { return true; }
	if (isset($rec['_client_links'])) {
		$clients = sql_to_php_array($rec['_client_links']);
	} else {
		$clients = get_clients_for_log( $rec['log_id'],'NF');
	}

	$clients = array_filter($clients);

	if (isset($rec['_case_mgr_id'])) {
		$cm = sql_to_php_array($rec['_case_mgr_id']);
	} else {
		$cm = get_staff_clients($clients);
	}

	if (isset($rec['_staff_alert_ids'])) {
		$staff_alert=sql_to_php_array($rec['_staff_alert_ids']);
	} else {
		$staff_alert=get_alerts_for_log( $rec['log_id'],'NF' );
	}

	$in_logs=which_logs($rec,'log_');

	$perm = (!$in_logs) // client_only entry
			|| has_perm($in_logs,'R') // log_specific permission
			|| in_array($UID,$staff_alert)  // Flagged to user's attention
			|| in_array($UID,$cm); //staff assigned to client
	return $perm;
}

function db_links_array($arr)
{
	foreach ($arr as $key=>$link)
	{
		$arr[$key]=stripslashes($link);
	}
	return $arr;
}

function unduplicate_log_staff($oldid,$newid)
{
	$def = get_def('log');

	$log['md5_sum']= md5($log['log_text'] . $log['added_by'] . $log['subject']
		 . $log['added_at'] . $log['added_at'] );

	$sql = "UPDATE ".$def['table_post']." SET added_by={$newid},
                  md5_sum=md5(log_text||{$newid}||COALESCE(subject,'')||added_at),
                  sys_log=COALESCE(sys_log||'\n','')||'Old added_by: '||added_by||'\nOld md5: '||md5_sum WHERE added_by={$oldid}";
	$res = sql_query($sql);
	if (!$res) {
		outline('Failed to update log table with '.$sql);
		return false;
	}
	$rows = sql_affected_rows($res);
	outline('Updated '.$rows.' rows in log table. Changed added_by from '.$oldid.' to '.$newid);
	return $res;
}

function post_clients($logid, $posterid)
{
	// POST REFERENCES FOR THE GLOBAL ARRAY OF $CLIENTS

	global $CLIENTS;
	if (count($CLIENTS) == 0)
	{
		return true;
	}
	foreach($CLIENTS as $x )
	{
		$result=post_client1($logid, $posterid, $x);
		if ( ! $result )
		{
			sql_warn("clientref insert query failed:  $query<BR>");
			return $result;
		}
	}
	return $result;
}

function post_client1($logid, $posterid, $clientid)
{
	// POST REF FOR  1 CLIENT
	$def = get_def('client_ref');

	$posttime = date("Y-m-d H:i:s");

	$ver_query = "SELECT * FROM ".$def['table']."
            WHERE ref_id = $logid
            AND client_id = $clientid
            AND ref_table='LOG'";
	$ver = sql_query( $ver_query ) or
		sql_warn("Couldn't do verify query for post_client1: "
			   . $ver_query );
	if (sql_num_rows( $ver ) >=1 )
	{
		outline( alert_mark() .
			   "Reference to ".AG_MAIN_OBJECT." " . client_link( $clientid )
			   . " for log # $logid already exists.");
		return false;
	}
	$new_rec = array(
			     'client_id'=>$clientid,
			     'ref_id'   =>$logid,
			     'ref_table'=>'LOG',
			     'added_by' =>$posterid,
			     'changed_by'=>$posterid);
	$query = sql_insert($def['table_post'],$new_rec);
	$result = sql_query($query) or
            sql_warn("clientref insert query failed:  $query<br />");
	return $result;
}

function get_clients_for_log( $logno, $separator="",$photos="N" )
{
	// pass $separator as "NF" to return just array of IDs

	global $client_table, $client_table_id;

	$def = get_def('client_ref');

	$get_sql="SELECT client_id from ".$def['table'];
	$fil = array("ref_table"=>"LOG","ref_id"=>$logno);

	$refs=agency_query( $get_sql,$fil );
	$clients = sql_fetch_column($refs,"client_id");
	if ( $separator=="NF")
	{
		return $clients; // just the IDs
	}
	else
	{
		return get_clients_f( $clients,"",$photos );
	}
}

function get_clients_f( $clients,$separator="",$photos="N" )
{
	// take an array of client IDS, and return a formatted string
	$result=array();
	$separator=orr($separator,"<br>");
	$count=count($clients);
	foreach ($clients as $x)
	{
		array_push($result,($photos=="Y" ? client_photo($x,.35) : "" )
			     . client_link( $x )
			     . smaller(" ($x)"));
	}
	return $separator==='ARRAY'
		? $result
		: implode($result,$separator);
}

/* log/engine functions */
function form_log($rec,$def,$control)
{
	//fixme: this is an initial attempt at a log add form, for when logs are engine-compliant
	//still not working: client and staff refs

	$in_logs = array();
	foreach ($rec as $key=>$value) {

		$label = label_generic($key,$def,$control['action']);
		$field = form_field_generic($key,$value,$def,$control,$dummy);
		if (preg_match('/^in_/',$key)) { // in logs
			$in_logs[] = $field.' '.$label;
		} elseif ($key=='occurred_at') {
			$occurred_at = table(rowrlcell($label,$field),'',' class=""');
		} elseif (preg_match('/^was/',$key)) {
			$log_flags .= rowrlcell($label,$field);
		} elseif (in_array($key,array('subject','log_text'))) {
			$text .= rowrlcell($label,$field);
		} else {
			$rest .= hiddenvar('rec['.$key.']',$value);
		}


	}
	
	global $colors;
	$out = //which logs 
		box(html_heading_3('Specify which logs:')
		     . implode(oline(),$in_logs),$colors['pick'])

		.oline()
		//refs
		. add_staff_alert_form_generic($rec,$def,$control)
		.oline()
		//late entry
		. box(html_heading_3('Late Entry?')
			. oline(smaller(" &nbsp; If so, please enter the following:"),2)
			. $occurred_at
			,$colors["addl"] )

		.oline()
		// additional information
		. box(html_heading_3('Additional Information')
			. oline('Please answer Yes or No to the following:',2)
			. table($log_flags,'','class=""'),$colors['alert'])

		. oline()
		. table($text) //log subject and text

		.$rest; //hiddenvars

	
	return $out;
}

//function view_log($rec,$def,$action,$control='',$control_array_variable='control')
function view_logx($rec,$def,$action,$control='',$control_array_variable='control')
{
	$flags = $in_logs = array();
	foreach ($rec as $key => $value) {
		if (preg_match('/^in_/',$key) && sql_true($value)) { //in logs
			$label = label_generic($key,$def,$action,$formatting=false);
			$in_logs[] = blue($label);
		} elseif (preg_match('/^was_/',$key) && sql_true($value)) { //flags
			$label = label_generic($key,$def,'view',$formatting=false); //always use 'view' action as labels change
			$flags[] = bold(red($label));
		} elseif (in_array($key,array('log_text','subject'))) {
			$def['fields'][$key]['data_type'] = 'text';
			$$key = value_generic($value,$def,$key,'view');
		}
	}


	//post times
	$added_at    = dateof($rec['added_at']).' '.timeof($rec['added_at']);
	$post_times = 'Posted at '.bold($added_at);

	if ($occurred_at = $rec['occurred_at']) {
		$occurred_at = dateof($occurred_at).' '.timeof($occurred_at);
		$post_times .= oline().'Event time was '.bold($occurred_at);
	}

	$client_refs = orr(get_client_refs_generic($rec,'view',$def),'(no clients are referenced in this log entry)');

	$tdef = $def;
	$tdef['object']='LOG';
	if ($staff_alerts = get_staff_alerts_generic($rec,$action,$tdef,$control)) {
		$staff_alerts = oline(bold('Staff Alerts:')).$staff_alerts;
	} else {
		$staff_alerts = 'No Staff Alerts';
	}

	$out = oline(($action=='list' ? bold(bigger('Log '.$rec['log_id'].' ',2)) : '' ) .'(logs: '.implode(', ',$in_logs).')',2)
		. implode(oline(),$flags)

		//refs/author info
		. table(row(
				cell($client_refs,'class="client"') //client refs
				. cell($staff_alerts,'class="staff"') //staff alerts
				. cell(oline('Posted by '.staff_link($rec['added_by']))
					 . $post_times,'class="info"')))

		. div(html_heading_2(underline($subject),' style="text-align: center;"')
			. $log_text,'','class="generalTable"');

	return $out;
}

/* fixme
function generate_list_medium_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	return generate_list_long_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num);
}

function generate_list_long_log($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num)
{
	if (!in_array($control['format'],array('long','medium'))) {
		return generate_list_generic($result,$fields,$max,$position,$total,$control,$def,$control_array_variable,&$rec_num);
	}

	$pos=$control['list']['position'];
      $mx=$control['list']['max'];

      while ( $x<$mx and $pos<$total) {
		$a = sql_fetch_assoc($result,$pos);

		$link = link_engine(array('object'=>$def['object'],'id'=>$a[$def['id_field']]),'View');

		$out .= div(view_log($a,$def,'list',$control) . html_heading_6($link)); 

		$pos++;
		$x++;
	}

	return $out . list_links($max,$position,$total,$control,$control_array_variable);

}
*/
/* end log/engine functions */
?>
