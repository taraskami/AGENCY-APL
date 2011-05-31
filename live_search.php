<?php

$AG_FULL_INI = false;
$quiet='Y';
include 'includes.php';

$term=$_REQUEST['term'];
$type=$_REQUEST['type'];
preg_match('/^[a-z_]*$/i',$type) or die("Bad type passed");
preg_match('/^[a-z_ 0-9()]*$/i',$type) or die("Bad term passed");

$def=get_def($type);

if (has_perm($def['perm_view'])) {
	$filter=array("ILIKE:{$type}_name({$type}_id) || ' (' || {$type}_id ||')'"=>'%'.$term.'%');
	$results=sql_fetch_column(agency_query("SELECT {$type}_name({$type}_id) || ' (' || {$type}_id || ')' AS {$type}_name FROM {$type}",$filter,NULL,100),"{$type}_name");
	echo(json_encode($results));
}

page_close(TRUE); // silent
exit;

?>	
