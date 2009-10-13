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

$engine['log'] = array(
			     'subtitle_eval_code'=>'($total>0) ? smaller(hlink("log_browse.php?action=show_client_logs&cid=".$id,"Show full text of these logs")) : ""',
			     'subtitle_html'=>smaller(hlink('log_browse.php?action=browse','Go to log index')),
 			     'allow_add'=>false,
  			     'add_link_show'=>false,
 			     'list_hide_view_links' => true,
			     'singular'=>'log',
			     'perm'=>'any',
			     'perm_list'=>'any',
			     'list_fields'=>array('added_at','custom1','added_by','custom2','subject'),
			     'list_order'=>array('added_at'=>true),
			     'list_max'=>25,
			     'title_view'=>'"Log ".$rec["log_id"]',
			     'title_add' =>'"Add A New Log Entry"',
			     'fn'=>array(
					     'get'=>'get_log_engine'
					     ),
			     'fields'=>array(
						   'subject'=>array(
// 									  'data_type'=>'html',
									  'is_html'=>true,
									  'value_format_list'=>'smaller($x)',
									  'value_list'=>'div(hlink("log_browse.php?action=show&id=$rec[log_id]",
													  ( be_null($x) 
													    ? orr(substr($rec["log_text"],0,90),"LOG CONTAINS NO TEXT") 
													    : $x) )
												  ,"",(isset($rec["_staff_alert_ids"]) 
													 && in_array($GLOBALS["UID"],sql_to_php_array($rec["_staff_alert_ids"])))
												  ? " style=\"background-color: #FFC0C0; padding: 3px;\""
												  : null)'
									  ),
						   'added_by'=>array('label'=>'Author'),

						   'log_text' => array('null_ok' => false,
									     'comment' => 'Compose your log here'),

						   //see below for more in_xxx detail
						   'in_loga' => array( 'label'=>'Log C'),
						   'in_logb' => array('label'=>'Log B'),
						   'in_logc' => array('label'=>'Log C'),

						   //log flags
						   'was_assault_staff'  => array('label_add' => 'Staff Assaulted or Injured?',
											   'label'=>'Staff Assaulted or Injured'),
						   'was_assault_client' => array('label_add' => 'Client(s) Assaulted or Injured?',
											   'label'=>'Client(s) Assuaulted or Injured'),
						   'was_police'            => array('label_add' => 'Were Police Called?',
											   'label'=>'Police were Called'),
						   'was_medics'         => array('label_add' => 'Were Medics Called?',
											   'label'=>'Medics Were Called'),
						   'was_bar'            => array('label_add' => 'Client(s) Barred?',
											   'label'=>'Client(s) Barred'),
						   'was_drugs'          => array('label_add' => 'Drugs or Alcohol Involved?',
											   'label'=>'Drugs or Alcohol Involved'),

						   'custom1'=>array(
									  'data_type'=>'html',
									  'display'=>'hide',
									  'display_list'=>'display',
									  'label'=>'Log #/<BR>In Logs',
									  'label_format'=>'smaller($x)',
									  'value'=>'smaller($rec["log_id"]."<br />").smaller(which_logs_f($rec),2)'
									  ),
						   'custom2'=>array(
									  'data_type'=>'html',
									  'display'=>'hide',
									  'display_list'=>'display',
									  'label'=>ucwords(AG_MAIN_OBJECT).'s',
									  'value'=> 'isset($rec["_client_links"]) 
											     ? orr(implode("<BR>",client_link(sql_to_php_array($rec["_client_links"]))),
												     "(no '.AG_MAIN_OBJECT.'s referenced)")
											     : get_clients_for_log($rec["log_id"])',
									  'value_format'=>'smaller($x)'
									  ),
									  
						   )
			     );

$t_in_fields = array( 'in_loga',
			    'in_logb',
			    'in_logc'
			    );

$t_valid_record = array();
foreach ($t_in_fields as $t_f) {
	$engine['log']['fields'][$t_f]['boolean_form_type'] = 'checkbox';
	$engine['log']['fields'][$t_f]['label'] = orr($engine['log']['fields'][$t_f]['label'],ucwords(preg_replace('/^in_/','',$t_f)));
	$engine['log']['fields'][$t_f]['null_ok'] = true; // not null should be set in the db

	$t_valid_record[] = 'sql_true($rec['.$t_f.'])';
}

$engine['log']['valid_record'] = array(implode(' || ',$t_valid_record) => 'You must specify at least one log to post a log entry.');
?>
