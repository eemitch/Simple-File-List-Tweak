<?php // Simple File List Tweak - Author: Mitchell Bennis | support@simplefilelist.com
// License: EULA | https://simplefilelist.com/end-user-license-agreement/


// Check if the current logged-in user has a file list yet, 
// create one automatically if not based on User ID

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeSFL_Tweak_1.0.1' ) ) exit('ERROR 98 - eeSFL_Tweak_1.0.1');

// CONFIG ----------------------------------------------------------------------------------------

// The ID of the Template List
define('eeSFL_Tweak_TemplateListID', 1); 

// This is where new file list directories will be created, based on the user login name.
$eeSFL_Tweak_Path = str_replace(ABSPATH, '', WP_CONTENT_DIR); // Strip out ABSPATH
define('eeSFL_Tweak_PathToFileLists', $eeSFL_Tweak_Path . '/uploads/file-lists/');

// FUNCTIONS -------------------------------------------------------------------------------------

// Check to See if a List for this User Exists, Then Create One If Not
function eeSFL_Tweak_ListCheck() {
	
	global $eeSFL;
	
	$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Loading eeSFL_ListCheck()';
	
	// Create a new file list, if needed, based on User ID
	$eeUserID = get_current_user_id();
	$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - UserID = ' . $eeUserID;
	
	if( $eeUserID > 1 AND !is_admin() ) { // Logged In on Front-End
		
		$eeSFL_Settings = get_option('eeSFL_Settings_' . $eeUserID);
		
		// echo '<pre>SFL Log: ';
		// print_r($eeSFL_Settings);
		// echo '</pre>';
		// wp_die();
		
		if(empty($eeSFL_Settings)) {
			
			if( eeSFL_Tweak_AutoCreateFileList($eeUserID) ) { // Auto Create a File List
				
				$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Created New List';
				
				// Proceed to Showing the List
				return TRUE;
				
			} else {
				
				$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - CANNOT Create New List';
				
				return FALSE; // Skip showing the list
			}
		
		} else {
			
			$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - List Check Good';
			
			$eeSFL->eeListID = $eeUserID;
			$eeSFL->eeSFL_GetSettings($eeSFL->eeListID); // This is now the loaded list
			
			return TRUE;
		}
	}
		
	$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Not Logged In';
	
	return FALSE; // Don't show any list
}




// Create a new file list based on the user ID from the template file list
function eeSFL_Tweak_AutoCreateFileList($eeUserID) {
	
	global $eeSFL;
	
	$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Creating a New File List';
	
	$wpUserInfo = get_userdata($eeUserID);
	
	if ($wpUserInfo) {
		
		// Get the username
		$eeUserName = $wpUserInfo->user_login;
		$eeUserEmail = $wpUserInfo->user_email;
		
		// Create the new file list directory, if needed.
		$eeUserDir = eeSFL_ValidateFileListDir($eeUserName);
		if($eeUserDir === FALSE) { 
			$eeSFL->eeLog[eeSFL_Go]['errors'][] = 'Cannot Create the File List Using ' . $eeUserName;
			return FALSE;
		} 
		$eeFileListDir = eeSFL_Tweak_PathToFileLists . $eeUserDir;
		
		// wp_die($eeFileListDir);
		
		if(eeSFL_FileListDirCheck($eeFileListDir)) {
			
			// Get the template list settings
			$eeNewListSettings = get_option('eeSFL_Settings_' . eeSFL_Tweak_TemplateListID);
			
			// Set the new List ID
			$eeSFL->eeListID = $eeUserID; // The List ID will be the User's ID
			
			// Update the array values as needed
			$eeNewListSettings['FileListDir'] = $eeFileListDir; // Relative to ABSPATH
			$eeNewListSettings['ListTitle'] = $eeUserName . ' Files';
			$eeNewListSettings['Mode'] = 'USER';
			$eeNewListSettings['Users'] = array($eeUserID);
			$eeNewListSettings['NotifyTo'] = $eeUserEmail;
			// More ?
			
			// Set Background Tasks
			$eeTasks = get_option('eeSFL_Tasks');
			
			if($eeNewListSettings['UseCache'] == 'YES') {
				$eeTasks[$eeSFL->eeListID]['Scan'] = 'YES';
			} else {
				$eeTasks[$eeSFL->eeListID]['Scan'] = 'NO';
			}
			if($eeNewListSettings['UseCacheCron'] == 'YES') {
				$eeTasks[$eeSFL->eeListID]['Background'] = 'YES';
			} else {
				$eeTasks[$eeSFL->eeListID]['Background'] = 'NO';
			}
			if($eeNewListSettings['ShowFileThumb'] == 'YES') {
				$eeTasks[$eeSFL->eeListID]['GenerateThumbs'] = 'YES';
			} else {
				$eeTasks[$eeSFL->eeListID]['GenerateThumbs'] = 'NO';
			}
			
			update_option('eeSFL_Tasks', $eeTasks);
			
			// Add the new settings to the database
			add_option('eeSFL_Settings_' . $eeSFL->eeListID, $eeNewListSettings);
			
			$eeSFL->eeSFL_GetSettings($eeSFL->eeListID); // This is now the loaded list
			
			// Index the Directory to create the file list array in the database
			$eeSFL->eeSFL_UpdateFileListArray(); // This will create eeSFL_FileList_[ID] option
			
			$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Created New File List for ' . $eeUserName . ' (ID: ' . $eeSFL->eeListID . ')';
			
		} else {
			
			return FALSE;
		}
	}

	return TRUE;
}


// Run This Before SFL ----------------------------------------------------

add_filter('pre_do_shortcode_tag', 'eeSFL_Tweak_Shortcode_Handler', 10, 4);

function eeSFL_Tweak_Shortcode_Handler($return, $tag, $attr, $m) {

	if('eeSFL' === $tag) { 
	
		if(eeSFL_Tweak_ListCheck() === TRUE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
}

?>