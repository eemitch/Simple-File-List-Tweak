<?php
/*
Plugin Name: Simple File List Tweak
Description: Allows for modifying the operation of Simple File List.
Version: 1.1.1
Author: Mitchell Bennis - support@simplefilelist.com
Author URI: https://simplefilelist.com
License: EULA | https://simplefilelist.com/end-user-license-agreement/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define('eeSFL_Tweak_Version', '1.0.1');

// Include in the Front-End
function eeSFL_Tweak_Enqueue() {
	
	// Enqueue CSS file
	wp_enqueue_style('ee-simple-file-list-tweak-css', plugins_url('/css/style.css', __FILE__));
	
	// Enqueue JavaScript file
	$eeDependents = array('jquery'); // Requires jQuery
	wp_enqueue_script('ee-simple-file-list-tweak-js', plugins_url('/js/scripts.js', __FILE__), array('jquery'), eeSFL_Tweak_Version, true);
}
add_action('wp_enqueue_scripts', 'eeSFL_Tweak_Enqueue');



// Run Functions Upon Page Load
function eeSFL_Tweak_Setup() {
	
	global $eeSFL;
	
	// SFL SETUP
	if( class_exists('eeSFL_MainClass') === FALSE ) {	
		
		if(function_exists('eeSFL_Setup')) { eeSFL_Setup(); }
	}
	
	$eeSFL->eeLog[eeSFL_Go]['notice']['TWEAK'][] = eeSFL_noticeTimer() . ' - Simple File List TWEAK is Loading...';
	
	
	// TWEAK ---------------------





	
	// Output the SFL Log File
	// echo '<pre>SFL Log: ';
	// print_r($eeSFL->eeLog);
	// echo '</pre>';
	// wp_die();
	
	
}
add_action('init', 'eeSFL_Tweak_Setup');

?>