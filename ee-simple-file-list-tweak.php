<?php
/*
Plugin Name: Simple File List Tweak
Description: Allows for modifying the operation of Simple File List.
Version: 1.0.1
Author: Mitchell Bennis - support@simplefilelist.com
Author URI: https://simplefilelist.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('eeSFLtweak_Version', '1.0.1');

function eeSFLtweak_Enqueue() {
	
	// Enqueue CSS file
	wp_enqueue_style('ee-simple-file-list-tweak-css', plugins_url('/css/style.css', __FILE__));
	
	// Enqueue JavaScript file
	$eeDependents = array('jquery'); // Requires jQuery
	wp_enqueue_script('ee-simple-file-list-tweak-js', plugins_url('/js/scripts.js', __FILE__), array('jquery'), eeSFLtweak_Version, true);
}
add_action('wp_enqueue_scripts', 'eeSFLtweak_Enqueue');

?>