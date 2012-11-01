<?php

function guest_name( $id ) {
	if (!is_valid('integer_db',$id)) {
		return false;
	}
	return sql_assign( 'SELECT name_full FROM guest WHERE guest_id='.$id);
}

function guest_find_client_id($filter1,&$msg,$current_id) {
//	$name_last=$_POST['name_last'];
//	$name_first=$_POST['name_first'];
	$unit=$_POST['housing_unit_code'];
	$dob=$_POST['dob'];
	$msg1=array();

		
/*
	if (($name_last or $name_first or $dob)) {
		if (!preg_match('/[a-z _\']*$/i',$name_last)) {
			$msg1[] = 'Invalid last name';
		}
		if (!preg_match('/[a-z _\']*$/i',$name_first)) {
			$msg1[] = 'Invalid first name';
		}
*/
	if ($unit or $dob) {
		if (!preg_match('/^[a-zA-Z0-9_]*$/',$unit)) {
			$msg1[] = 'Invalid format for unit number: $unit';
		}
		if (!$dob=dateof($dob,'SQL')) {
			$msg1[] = 'Invalid Date of Birth';
		}
		if (count($msg1) >0 ) {
			$msg .= implode(oline(),$msg1);
			return false;
		}
//		$filter['ILIKE:name_first']=$name_first;
//		$filter['ILIKE:name_last']=$name_last;
		$filter['unit_no(client_id,current_date)']=$unit;
		$filter['dob']=$dob;
		$filter=array_merge($filter,$filter1);
		$clients=get_generic($filter,NULL,NULL,'client');
		if (count($clients)==0) {
			$msg .= 'Matching tenant not found';
			return false;
		}
		if (count($clients)>1) {
			$msg .= 'Found multiple matching records--don\'t know what to do!  Please tell a staff person about this.';
			return false;
		}
	} elseif ($current_id) {
		// continue current login	
		$clients=get_generic(client_filter($current_id),'','','client');
	} else {
		$msg .= 'No information submitted.  Try again';
		return false;
	}

	$msg .= 'Welcome ' . ucwords(strtolower($clients[0]['name_first'])) . ' ' . ucwords(strtolower($clients[0]['name_last']));

	return $clients[0]['client_id'];
}

function guest_select_form($unit_filter=array()) {

/*
// Uncomment this for a name-based form 
	$out = ''
	. formto()
	. html_heading_tag('Name',3)
	. 'First: '
	. formvartext('name_first')
	. oline()
	. 'Last: '
	. formvartext('name_last')
*/
	$def=get_def('housing_unit');
	$out = ''
	. formto()
	. html_heading_tag("Select your unit",2)
	. selectto('housing_unit_code')
	. selectitem('','...')
	. do_pick_sql('SELECT housing_unit_code AS value, substring(housing_unit_code FROM \'^[a-zA-Z]*([0-9]+)$\') AS label FROM ' . $def['table'] . ' WHERE ' . read_filter($unit_filter) . ' ORDER BY 1') 
	. selectend()
	. oline()
	. html_heading_tag('Date of birth: ',2)
	. formdate('dob')
	. oline('',2)
	. button('Go','','','','','class="guestTenantSelectSubmit"')
	. hiddenvar('menu','menu')
	. formend()
	; 
	return $out;
}


function guest_guest_select_form( $id ) {
	$list=get_generic(client_filter($id),NULL,NULL,'guest_visit_authorized');
	if (count($list)==0) {
		$response = 'Sorry, you have no eligible visitors.';
	
} else {
		$base_url='';
		foreach($list as $item) {
			$items[] = span(hlink($base_url.'?menu=signin_selected&guest_id='.$item['guest_id'],$item['guest_name'],'','class="guestMenuLink"'),'class="guestMenuButton"');
			//$items[] = hlink($base_url.'?menu=signin_selected&guest_id='.$item['guest_id'],span($item['guest_name'],'class="guestMenuButton"'));
		}
		//$response = implode(oline(),$items);
		$response = implode('',$items);
	}
	return $response;
}

function guest_exit_select_form( $id ) {
	$list=get_generic(client_filter($id),NULL,NULL,'guest_visit_current');

	if (count($list)==0) {
		$response = 'Sorry, you have no current visitors.';
	} else {
		$base_url='';
		foreach($list as $item) {
			$items[] = span(hlink($base_url.'?menu=signout_selected&guest_id='.$item['guest_id'],$item['guest_name'],'','class="guestMenuLink"'),'class="guestMenuButton"');
		}
		//$response = implode(oline(),$items);
		$response = implode('',$items);
	}
	return $response;
}

function guest_verify($client_id,$guest_id,$type='visit') {
	//$type can be 'exit' or 'visit'
	$action = ($type=='visit') ? 'visiting you' : 'ending their visit';

	$g_filt = array('guest_id'=>$guest_id);
	$guest=get_generic($g_filt,NULL,NULL,'guest');
	if (count($guest) <> 1) {
		return false;
	}
	if (be_null(($ied=$guest[0]['identification_expiration_date'])) or ($ied < dateof('now','SQL')) ) {
		// Comment out this line if you don't care about ID expiration dates
		$id_warning=div('Note: No Current Identification on file for this guest','','class="idWarning"');
	}

	$client=sql_fetch_assoc(client_get($client_id));
	$client_name=$client['name_first'].' ' . $client['name_last'];
	$form = div(guest_photo($guest_id),'','class="guestPhotoContainer"') . span("You are $client_name",'class="guestClientName"')
		. span( "And  " . $guest[0]['name_full'] . " is $action.",'class="guestGuestName"')
			. oline('',3)
			. span(hlink('?menu=sign' . (($type=='exit') ? 'out' : 'in') . '_selected_verify&guest_id='.$guest_id,'Yes, this is correct','','class="guestMenuLink"'),'class="guestMenuButton"')
			. span(hlink('?menu=menu','No, this is not correct.','','class="guestMenuLink"'),'class="guestMenuButton"')
			;
	return $id_warning . $form;
}

function guest_photo( $guest_id,$sizex=NULL,$sizey=NULL ) {
	if (!$guest_id) { return false; }
	$g_filt = array('guest_id'=>$guest_id);
	$guest=get_generic($g_filt,NULL,NULL,'guest');
	if (count($guest) <> 1) {
		return false;
	}
	if ($guest[0]['guest_photo']) {
		$tmp=link_attachment($guest[0]['guest_photo'],'dummy');
		if (preg_match('/href=\"(.*)\" /',$tmp,$matches)) {
			$photo_url=$matches[1];
			$photo = httpimage($photo_url,$sizex,$size,0,'class="guestPhoto"');
		}
	}
	return $photo;
}
	

function post_a_guest_visit($client_id,$guest_id,$msg) {
	$def=get_def('guest_visit');
	$d1 = $d2 = array();
	$rec=blank_generic($def,$d1,$d2);
	$rec['guest_id']=$guest_id;
	$rec['client_id']=$client_id;
	$rec['entered_at']=datetimeof('now','SQL');
	$rec['housing_unit_code']=unit_no($client_id);
	return post_generic($rec,$def,$msg);
}	

function post_a_guest_exit($client_id,$guest_id,$msg) {
	$def=get_def('guest_visit');
	$d1 = $d2 = array();
	$filt=array('client_id'=>$client_id,'guest_id'=>$guest_id);
	$recs=get_generic($filt,NULL,NULL,'guest_visit_current');
	if (count($recs)==0) {
		$msg .= 'Current visit not found';
		return false;
	}
	$rec=$recs[0];
	$rec['exited_at']=dateof('now','SQL');
	return post_generic($rec,$def,$msg,$filt);
}


?>
