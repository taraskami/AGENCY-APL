<?php

$quiet=true;
require_once 'includes.php';
if (!has_perm('JILS_IMPORT','W')) {
	outline(bigger(bold("You do not have permissions to import JILS records")));
	page_close();
	exit;
}

$jils_text=$_REQUEST['jils_text'];
$client_id=$_REQUEST['client_id'];
//$jils_link=hlink('http://ingress.kingcounty.gov/inmatelookup/SearchJailData.aspx','JILS',NULL,'target="_blank"');
$jils_link=hlink('http://ingress.kingcounty.gov/inmatelookup/startpage.aspx','JILS',NULL,'target="_blank"');

if (!is_client($client_id)) {
	outline('Invalid or missing client ID.  Stopping');
	page_close();
	exit;
}

if ($jils_text) {

	$jils_parse_regex = '/^.*Other Jail Search Resources(.*)Custody.Facility.*Booking Events:(.*)Data Accuracy Disclaimer.*$/s';
//	$jils_parse_regex = '/^.*Other Jail Search Resources(.*)Custody\/Facility.*Booking Events:(.*)Data Accuracy Disclaimer.*$/s';
//	outline("Client: " . client_link($client_id));
	if (preg_match($jils_parse_regex,$jils_text,$matches)) {
		$name=trim($matches[1]);
		$booking=$matches[2];
		$bookings=explode("\n",$booking);
		$jail_template=array(
			'client_id'=>$client_id,
			'jail_date_accuracy'=>'E',
			'jail_date_source_code'=>'JILS',
			'jail_facility_code'=>'KCCF', // FIXME: Correct code?
			'jail_county_code'=>'KING',
			'added_by'=>$GLOBALS['UID'],
			'changed_by'=>$GLOBALS['UID'],
			'sys_log'=>'Added with jils_import, name='.$name
		);
		foreach($bookings as $b) {
			if (preg_match('/^BA: (.*) Book Date: (.*) Release Date: (.*)$/',$b,$m)) {
//outline("Matched $b" . dump_array($m));
				$j=$jail_template;
				$j['ba_number']=$m[1];
				$j['jail_date']=datetimeof($m[2],'SQL');
				if (($j['jail_date_end']=datetimeof($m[3],'SQL'))) {
					$j['jail_date_end_accuracy']='E';
					$j['jail_date_end_source_code']='JILS';
				}
				$jails[]=$j;
				if(upsert_jail_record($j)) {
					$success++;
				} else {
					$fail++;
				}
			}
		}
//		outline('Name: ' . webify($name));
//		outline('Jail records: ' . dump_array($jails));
//		outline('Booking: ' . dump_array($bookings));
//outline(red("matched: " . dump_array($matches)));
	} else {
		outline("Raw Form: " . webify($jils_text));
		outline("Matches: " . dump_array($matches));
preg_match('/.*/s',$jils_text,$matches);
outline(red("Full match: " . dump_array($matches)));

	}
}
	$title=$client_title='Importing Jail Records for ' . client_link($client_id);
	$form =
	formto()
	. formtextarea('jils_text',$jils_text)
	. hiddenvar('client_id',$client_id)
	. oline()
	. bold('3. Press ')
	. button()
	;
	agency_top_header();

	if ($success or $fail) {
		outline(bigger(bold("Results for " . client_link($client_id))),2);
		out( $success ? oline(bigger(bold("$success records imported successfully"))) : '');
		out( $fail ? oline(bigger(bold("$fail records failed to import"))) : '');
		outline();
		outline(bold("You can close this page now"));
	} else {
		outline(span(bigger(bold($client_title)),'class="engineTitle"'),2);
		outline(bold('1. Lookup the client in  '. $jils_link . '.'),2);
		outline(bold('2. Copy the page, and paste it into this box:'));
		out($form);
	}
page_close();
exit;

?>
