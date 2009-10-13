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


function process_attachment($key, $orig, $null_ok=false)
{

	/*
	 * We call this every time we have a field of type attachment, but
	 * we don't need to process an attachment every time.
	 *
	 * The parameter are the key name, the value in the key,
	 * and whether the field can be null
	 *
	 * Here are the steps to process an attachment:
	 * 1) Do some checks to make sure file upload worked.
	 * 2) Move file to temporary location. 
	 * 3) Save information in Session variable
	 * 4) Return array of information.
	 */

	$tmpfname = tempnam('', '');

	//Return the value if we already have pending attachment:
	if (is_array($orig) && $orig['uploaded'] && $orig['session_key']) {
		return $orig;
	}

	//Return the value if we already have attached attachment:
	if (is_numeric($orig)){
		return $orig;
	} 
 
	$files = $_FILES['rec'];

	//check file size: 
	if (($files['error'][$key] == 1) || ($files['error'][$key] == 2)) {
		$error = 'File size too large.';
				
	} elseif ($files['size'][$key] == 0) {
		/*
		 * If this condition holds, move_uploaded_file can still return true, so 
		 * we want to test this first. 
		 */

		if ($null_ok) {
			//It is okay if this is null and it is an optional field.
			return null;
		}
		else {
			//If field is required, then we want to return an error.
			$error = 'File is empty or does not exist.';
		}
		
	//must calculate md5 sum before moving file
	} elseif ( 	$md5sum = md5(file_get_contents($files['tmp_name'][$key])) 
			&&  move_uploaded_file($files['tmp_name'][$key], $tmpfname)) { 
		$seq_found = false;
		$x = 1;

		//find session key to use.
		while (!$seq_found) {
			if (!array_key_exists('pending_upload'.$x, $_SESSION)) {
				$seq_found = true;
			} else {
				$x++;
			}
		}
		
		$filename_orig = $files['name'][$key];
		$mime = $files['type'][$key];
		$pathinfo = pathinfo($filename_orig);

		$result = array('uploaded' => true, 
				    'session_key' => 'pending_upload'.$x,
				    );

		// Set information in Session variable
		$_SESSION['pending_upload'.$x] = array('tmp_file' => $tmpfname,
								   'filename_orig' => $filename_orig,
								   'mime_type' => $mime, 
								   // get these so we can confirm they match when posting:
								   'attachment_size' => $files['size'][$key],
								   'md5sum' => $md5sum 
								   );

		return $result;
	} 

	// We only will have made it here if the file has failed to upload, and it 
	// is neither empty nor too large:
	if (!$error) {
		$error = 'File failed to upload';
	}

	return array('error' => $error,
			 'uploaded' => false);
	
}

function post_attachment($session_key, $object, $key)
{

	/* 
	 *  Gets session key, object, fieldname
	 *  Calculate MD5 on file
	 *  Post record to attachment table, including original file name, extension, MD5 and added_by
	 *  Get back ID of new record (use RETURNING)
	 *  Moves file into permanent location
	 *  Posts record to association_attachment table.
	 *  Return association_attachment_ID
	*/

	$f_tmp = $_SESSION[$session_key]['tmp_file'];


	// confirm that this file exists & is readable:
	if (!is_file($f_tmp) || !is_readable($f_tmp)){
		return 'The pending attachment is: ' .$f_tmp.'. This is not a readable file. ';
	}

	$file_size = filesize($f_tmp);
	$md5 = md5(file_get_contents($f_tmp));

	if ($file_size != $_SESSION[$session_key]['attachment_size']) {
		return 'Pending attachment does not have expected file size. ';
	}
	
	if ($md5 !=  $_SESSION[$session_key]['md5sum']) {
		return 'Pending attachment does not have expected hash. ';
	}
	
	$pathinfo = pathinfo($_SESSION[$session_key]['filename_orig']);
	$extension	= $pathinfo['extension'];
	$rec = array(
			 'md5sum' => $md5,
			 'filename_original' => $_SESSION[$session_key]['filename_orig'],
			 'attachment_size' => $file_size,
			 'extension' => $extension,
			 'mime_type' =>  $_SESSION[$session_key]['mime_type'],
			 'added_by' => $GLOBALS['UID'],
			 'changed_by' => $GLOBALS['UID']
			 );

	$def = get_def('attachment');
	$result = agency_query(sql_insert($def['table_post'], $rec, sql_supports_returning() ? '*' : false));
	
	// in case sql doesn't support returning:
	if (!sql_supports_returning()) {
		$result = get_generic($rec,'attachment_id DESC','1', $def['table']);
		//Theoretically there could be more than one record with the properties specified by the filter. This will return the one with the largest attachment_id.
	}

	$tmp_new_rec = sql_fetch_assoc($result);
	$file_id = $tmp_new_rec['file_upload_id'];

	if (is_numeric($file_id)) {
		//move file to location given from file_name_from_attachment_id:
		$moved = rename($f_tmp, file_name_from_file_upload_id($file_id, $extension)); 
	}

	$attachment_id = $tmp_new_rec['attachment_id']; 
	
	if (!$moved) {
		return 'Unable to move pending attachment to file system. ';
	}
	
	$assoc_attachment_rec = array(
					'parent_object' => $object,
					'parent_field_name' => $key,
					'attachment_id' => $attachment_id,
					'added_by' => $GLOBALS['UID'],
					'changed_by' => $GLOBALS['UID']
					);	

	$assoc_attachment_def = get_def('association_attachment');

	$a_a_res = agency_query(sql_insert($assoc_attachment_def['table_post'], $assoc_attachment_rec, sql_supports_returning() ? '*' : false));

	// in case sql doesn't support returning:
	if (!sql_supports_returning()) {
		$a_a_res = get_generic($assoc_attachment_rec,'association_attachment_id DESC','1', $assoc_attachment_def['table']);
		//Theoretically there could be more than one record with the properties specified by the filter. This will return the one with the largest association_attachment_id.
	}

	$a_a_rec = sql_fetch_assoc($a_a_res);
	$a_attachment_id = $a_a_rec['association_attachment_id'];	

	return (is_numeric($a_attachment_id)) ? $a_attachment_id : false;
	
}

//function link_attachment($value, $action, $key)
function link_attachment($value, $key, $short_format=false)
{
	/*
	 * Link to a pending or stored attachment.
	 * Parameters are the value in the field, 
	 * the current action, and the key.
	 */

	//$value is allowed to be blank for an optional field
	if (!$value) {
		return null;
	}

	if (is_array($value) && $value['session_key']) {
		//haven't yet uploaded file, but have session key of pending upload
		return  link_pending_attachment($value['session_key']);
	}
	
	if (is_numeric($value)) {
		$a_attachment_id = $value;
	} else { 
		log_error('Unexpected value for attachment. It is: '. $value);
		return $value;
	}


	/*
	 * If we get here, we should be linking
	 * to an attachment which is already in the database.
	 */
	
	//get association_attachment record:
	$a_attachment_def = get_def('association_attachment');
	$a_attachment_filter = array($a_attachment_def['id_field'] => $a_attachment_id);
	$a_attachment_rec = sql_fetch_assoc(get_generic($a_attachment_filter, '', '', $a_attachment_def));
		
	//get attachment record:
	$file_def = get_def('attachment');
	$file_filter = array($file_def['id_field'] => $a_attachment_rec['attachment_id']);
	$file_rec = sql_fetch_assoc(get_generic($file_filter, '', '', $file_def));

	//check if null and return $attachment_id
	if (!$file_rec) {
		log_error('Failed to find attachment with association_attachment_id: ' . $a_attachment_id);
		return $a_attachment_id;
	}

	$extension = $file_rec['extension'];

	//call function to create the label:
	$label = attachment_label($a_attachment_rec, $a_attachment_id, $extension);

	$size = ' (' .human_readable_size($file_rec['attachment_size']) .')';

	//only show other_text if action is not list
	//if ($action != 'list') {
	if (!$short_format) {
		$other_text = oline('Attached at: ' . datetimeof($a_attachment_rec['added_at'], 'US')) 
			. oline(' Attached by: ' . staff_name($a_attachment_rec['added_by']))
			. ($file_rec['added_at'] != $a_attachment_rec['added_at']? oline('Uploaded at: ' . datetimeof($file_rec['added_at'], 'US')): '')
			. ($file_rec['added_by'] != $a_attachment_rec['added_by']? oline('Uploaded by: ' . staff_name($file_rec['added_by'])) : '');
	}

	$control['object'] = 'association_attachment';
	$control['id'] = $a_attachment_id; 
	$control['action'] = 'download';
	
	return link_engine($control, $label) . oline(smaller($size)) .smaller($other_text );
}

function link_pending_attachment($session_key)
{				
	/*
	 * Link to pending file.
	 * Take session key where information is stored.
	 */

	$control['object'] = 'association_attachment'; //hacky, as nothing is yet in attachment
	$control['id'] = $session_key;
	$control['action'] = 'download';

	return link_engine($control, $_SESSION[$session_key]['filename_orig']) 
		. smaller(' (pending)'
			    . ' ('. human_readable_size($_SESSION[$session_key]['attachment_size']) .')');

}


function file_name_from_file_upload_id($id, $extension)
{
	/* 
	 * Gives the stored file name of an attachment 
	 * with the given ID and extension
	 */

	$path = AG_ATTACHMENT_LOCATION;
	return $path.'agency_attachment_'.$id.'.'.$extension;
		
}

function attachment_label($a_attachment_rec, $a_attachment_id, $extension, $datetime='')
{
	/*
	 * Gives the label of the file.
	 * The datetime is included if it is passed as a parameter.
	 */

	//get parent record:
	$parent_def = get_def($a_attachment_rec['parent_object']);
	$parent_filter = array($a_attachment_rec['parent_field_name'] => $a_attachment_id);
	$parent_rec = sql_fetch_assoc(get_generic($parent_filter, '', '', $parent_def));

	$parent_id_field = $parent_def['id_field'];


	if (array_key_exists(AG_MAIN_OBJECT.'_id', $parent_rec)) {
		// check if there is a client id.
		$prefix = 'cl'.$parent_rec[AG_MAIN_OBJECT.'_id'].'_';
	} elseif (array_key_exists('staff_id', $parent_rec)) {
		// check if there is a staff id.
		$prefix = 'st'.$parent_rec['staff_id'].'_';
	} else {
		//show parent object if there isn't a main object
		$prefix = $a_attachment_rec['parent_object'].'_';
	}

	//get label from config:
	$field_label = strtolower(label_generic($a_attachment_rec['parent_field_name'], $parent_def, 'view', false)); 
	return $prefix . $field_label 
		. ( $datetime ?  '_'.$datetime : '')
		. ( $extension ? '.'.$extension : '');
}


function get_attachment_content($id, &$mesg) 
{
	/* 
	 * $id should be a number for an association_attachment_id,
	 * or $id should be a session key for a pending file, of the 
	 * form pending_uploadXX, where XX is a number.
	 *
	 * Return array containing the following information about the attached file:
	 * filename to output, expected filesize, expected md5sum, 
	 * mime-type, and the contents of the file.
	 */

	if (is_numeric($id)) {

		//retrieve association_attachment record corresponding to this association attachment ID
		$a_attachment_def = get_def('association_attachment');
		$a_attachment_filter = array($a_attachment_def['id_field'] =>$id);
		$a_attachment_rec = sql_fetch_assoc(get_generic($a_attachment_filter, '', '', $a_attachment_def));
		
		//needed for permission checking:
		$parent_def = get_def($a_attachment_rec['parent_object']);
		
		// This checks that they have permissions for the parent object
		if ( ! has_perm($parent_def['perm_view'])){
			$mesg = 'You do not have the permission for this.'; 
			return false;
		}
		
		//retrieve attachment record referred to by this association_attachment record
		$att_def = get_def('attachment');
		$att_filter = array($att_def['id_field'] => $a_attachment_rec['attachment_id']);
		$att_rec = sql_fetch_assoc(get_generic($att_filter, '', '', $att_def));
			
		$extension = $att_rec['extension'];
		$datetime = dateof($att_rec['added_at'], 'SQL'). '_'. timeof($att_rec['added_at'], '24');
		
		//set name of file to give to user:
		$attachment_info['output_filename'] = attachment_label($a_attachment_rec, $id, $extension, $datetime );
		
		//get stored file:
		$file = file_name_from_file_upload_id($att_rec['file_upload_id'], $extension);
		
		if (is_readable($file)) {
			$attachment_info['attachment_contents'] = file_get_contents($file);	
		} else {
			$mesg = 'File '. $file .' is not readable. We were looking for attachment with association attachment id: '. $id . '.';
			return false;
		}
		
		$attachment_info['expected_size'] = $att_rec['attachment_size'];
		$attachment_info['expected_md5'] = $att_rec['md5sum'];			
		$attachment_info['mime_type'] = $att_rec['mime_type'];
		
	} elseif (preg_match('/^pending_upload[0-9]*$/', $id)){ 
		
		//set appropriate variables:
		if (is_readable($_SESSION[$id]['tmp_file'])) {
			$attachment_info['attachment_contents'] = file_get_contents($_SESSION[$id]['tmp_file']);
		} else {
			$mesg .= oline('Pending attachment with session key '. $id.' is not readable.');
			return false;
		}
		
		$attachment_info['expected_size'] = $_SESSION[$id]['attachment_size'];
		$attachment_info['expected_md5'] = $_SESSION[$id]['md5sum'];
		$attachment_info['mime_type'] = $_SESSION[$id]['mime_type'];
		$attachment_info['output_filename'] = 'pending_'.$_SESSION[$id]['filename_orig'];
		
	} else {
		$mesg .= oline('Trying to download file with unexpected ID. ID is: '. $id .
				   '. This does not refer to a pending or uploaded attachment.');
		return false;
	}
	
	return $attachment_info;
}

?>
