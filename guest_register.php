<?php

$quiet=true;
include 'includes.php';
link_style_sheet('guest_register.css','screen',' class="styleSheetScreen"');

// Which housing project are we running at?
$local=unserialize(AG_LOCAL_PARAMETERS);
$housing_project_code=$local['guest_register_housing_project_code'];
if (!$housing_project_code) {
	outline('No housing project code in AG_LOCAL_PARAMETERS.  Stopping');
	outline('These are the local parameters: ' . dump_array($local));
	page_close();
	exit;
}	

$filter_project=array('housing_project_code'=>$housing_project_code);

$title='Welcome to ' . sql_lookup_description($housing_project_code,'l_housing_project','housing_project_code','description');

$signin_msg='Sign a guest in';
$signout_msg='Sign a guest out';
$logout_msg='Log Out';
$logout_link=span(hlink($_SERVER['SCRIPT_NAME'],'Log Out','','class="guestMenuLink"'),'class="guestMenuButton"');
$menu_link=span(hlink($_SERVER['SCRIPT_NAME'].'?menu=menu','Return to Menu','','class="guestMenuLink"'),'class="guestMenuButton"');

$SESSION_ID_VAR = 'GuestTenantID'; // the variable name to use in the session

$menu=orr($_GET['menu'],$_POST['menu']);
//outline("Menu = $menu");
switch ($menu) {
	case 'menu' :
		$filter=array('housing_project_code'=>$housing_project_code);
		if (!$id=guest_find_client_id($filter,$msg,$_SESSION[$SESSION_ID_VAR])) {
			$menu=NULL;
			break;
		} else {
			$_SESSION[$SESSION_ID_VAR]=$id;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestMenuScreen" ';
		$out = ''
		. span(hlink($link_base . '?menu=signin', $signin_msg,'','class="tenantSigninButton guestMenuLink"'),'class="guestMenuButton"')
		. span(hlink($link_base . '?menu=signout', $signout_msg,'','class="tenantSignoutButton guestMenuLink"'),'class="guestMenuButton"')
		. span(hlink($link_base . '?menu=', $logout_msg,'','class="tenantLogoutButton guestMenuLink"'),'class="guestMenuButton"');
		break;
	case 'signin' :
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninScreen" ';
		$title2 = $signin_msg;
		$form = guest_guest_select_form( $_SESSION[$SESSION_ID_VAR]);
		$out .= ''
		. div($form,'','class="visitorResults"')
		. oline()
		. $menu_link
		. oline()
		. $logout_link;
		break;
	case 'signout' :
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutScreen" ';
		$title2 = 'Which visitor is signing out?';
		$form = guest_exit_select_form( $_SESSION[$SESSION_ID_VAR]);
		$out .= ''
		. div($form,'','class="visitorResults"')
		. oline()
		. $menu_link
		. oline()
		. $logout_link;
/*
		$out=div($form1,'','class="guestTenantSelect"')
		. div('','','class="visitorResults"')
		. $menu_link;
*/
		break;
	case 'signin_selected' :
		$guest_id=$_GET['guest_id'];
		if (!(is_valid('integer_db',$guest_id) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninSelectedScreen" ';
		$title2 = 'Verify your visitor';
		$form = guest_verify($_SESSION[$SESSION_ID_VAR],$guest_id,'visit');
		$out .= $form . oline('',3) . $menu_link
		. oline()
		. $logout_link;
		break;
	case 'signout_selected' :
		$guest_id=$_GET['guest_id'];
		if (!(is_valid('integer_db',$guest_id) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutSelectedScreen" ';
		$title2 = 'Verify your departing visitor';
		$form = guest_verify($_SESSION[$SESSION_ID_VAR],$guest_id,'exit');
		$out .= $form . oline('',3) . $menu_link
		. oline()
		. $logout_link;
		break;
	case 'signin_selected_verify' :
		$guest_id=$_GET['guest_id'];
		$guest_filter=array('guest_id'=>$guest_id);
		if (!(is_valid('integer_db',$guest_id) and (count($guest=get_generic($guest_filter,'','','guest')) == 1) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSigninSelectedVerifyScreen" ';
		$name = $guest[0]['name_full'];
		if (count(get_generic($guest_filter,'','','guest_visit_current')) > 0) {
			$form = $name . ' is already registered as a current visitor';
		} elseif (post_a_guest_visit($_SESSION[$SESSION_ID_VAR],$guest_id)) { // don't name post_guest_visit, will override post_generic
			//$form = 'Your visitor has been successfully registered.';
			$form = $name . ' has been successfully registered as your visitor.';
		} else {
			$form = 'There as a problem registering ' . $name . ' as your visitor.';
		}
		$out .= $form . oline('',2) .  $menu_link
		. oline()
		. $logout_link;
		break;	
	case 'signout_selected_verify' :
		$guest_id=$_GET['guest_id'];
		if (!(is_valid('integer_db',$guest_id) and is_client($_SESSION[$SESSION_ID_VAR]))) {
			$menu=NULL;
			break;
		}
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestSignoutSelectedVerifyScreen" ';
		if (post_a_guest_exit($_SESSION[$SESSION_ID_VAR],$guest_id)) {
			$form = 'Your visitor has been successfully exited.';
		} else {
			$form = 'There as a problem exiting your visitor.';
		}
		$out .= $form . oline('',3) . $menu_link
		. oline()
		. $logout_link;
		break;	
}

if (!$menu) {
		$GLOBALS['AG_BODY_TAG_OPTIONS']=' class="guestLoginScreen" ';
		$_SESSION[$SESSION_ID_VAR]=NULL;
		$title2 = 'Please sign in';
		$form1=guest_select_form($filter_project);
		$out .= $form1;
}

//html_header($title);

//agency_top_header();
// OR
out(html_start());
out(html_heading_tag($title,1));
out($title2 ? html_heading_tag($title2,2) : '');
out($msg ? div($msg,'','class="guestResponseMessage"') : '');
out(div($out,'','class="guestHeaderBlock"'));

$footer=oline('Last refreshed at ' . datetimeof('now','US'). '.  ' . dead_link('Administration...')) . 'AGENCY Software running at ' . org_name();
//out(organization_logo_small() .html_heading_tag($title,1) . agency_logo_small());
outline(div($footer,'','class="guestAGENCYFooter"'));
page_close(true);
exit;

?>
