<?php

//$AG_FULL_INI = false;
$quiet='Y';
include 'includes.php';

$term=$_REQUEST['term'];
$type=$_REQUEST['type'];
preg_match('/^[a-z_]*$/i',$type) or die("Bad type passed");
preg_match('/^[a-z_ ,0-9()]*$/i',$type) or die("Bad term passed");

$def=get_def($type);

if (has_perm($def['perm_view'])) {
	$alias = ($type==AG_MAIN_OBJECT_DB)
		? " || COALESCE(name_alias,'')"
		: '';
	$filter=array("ILIKE:{$type}_name({$type}_id) || ' (' || {$type}_id ||') ' $alias"=>'%'.$term.'%');
	$name = ( $GLOBALS['AG_DEMO_MODE'] and ($type==AG_MAIN_OBJECT_DB))
		? "'XXXXXX'"
		: $type . '_name('.$type.'_id)';
//toggle_query_display();
	$results=sql_fetch_column(agency_query("SELECT $name || ' (' || {$type}_id || ')' AS {$type}_name FROM {$type}",$filter,NULL,100),"{$type}_name");
	echo(json_encode($results));
}

page_close(TRUE); // silent
exit;

?>	
