<?php

/*
** Plugin Name: dotstudioPRO Video Shortcodes
** Version: 1.00
** Author: dotstudioPRO
** Author URI: http://dotstudiopro.com
*/

if (!defined('ABSPATH'))
    die();

require_once("functions.php");

// // load css into the website's front-end
// function dspapi_api_routes_enqueue_style() {
//     wp_enqueue_style( 'dspapi-api-routes-style', plugins_url( 'styles/style.css', __FILE__ ) );
// }
// add_action( 'wp_enqueue_scripts', 'dspapi_api_routes_enqueue_style' );



/** Add Menu Entry **/
function dspdev_video_shortcodes_menu() {

	add_menu_page( 'Video Shortcodes', 'Video Shortcodes', 'manage_options', 'dspdev-video-shortcodes', 'dspdev_video_shortcodes_menu_page', 'dashicons-businessman' );

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