<?php

/*
** Plugin Name: dotstudioPRO Video Shortcodes
** Version: 1.00
** Author: dotstudioPRO
** Author URI: http://dotstudiopro.com
*/

if (!defined('ABSPATH'))
    die();

// Plugin Update Checker
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'http://updates.wordpress.dotstudiopro.com/wp-update-server/?action=get_metadata&slug=dspdev-video-shortcodes',
	__FILE__,
	'dspdev-video-shortcodes'
);

require_once("functions.php");

/** Add Menu Entry **/
function dspdev_video_shortcodes_menu() {

	add_menu_page( 'Video Shortcodes', 'Video Shortcodes', 'manage_options', 'dspdev-video-shortcodes', 'dspdev_video_shortcodes_menu_page', 'dashicons-video-alt2' );

}

add_action( 'admin_menu', 'dspdev_video_shortcodes_menu' );

// Set up the page for the plugin, pulling the content based on various $_GET global variable contents
function dspdev_video_shortcodes_menu_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	echo "<div class='wrap'>";


	include("templates/menu.tpl.php");


	echo "</div>";

}
/** End Menu Entry **/