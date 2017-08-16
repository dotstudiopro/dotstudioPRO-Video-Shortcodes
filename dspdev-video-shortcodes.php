<?php

/*
** Plugin Name: dotstudioPRO Video Shortcodes
** Version: 1.01
** Author: dotstudioPRO
** Author URI: http://dotstudiopro.com
** Description: Create shortcodes to embed videos and playlist info from your dotstudioPRO dashboard.
** License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016-2017 dotstudioPRO
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


add_action('wp_enqueue_scripts', 'dspdev_video_shortcodes_plugin_scripts_styles');
add_action('admin_enqueue_scripts', 'dspdev_video_shortcodes_plugin_scripts_styles');

function dspdev_video_shortcodes_plugin_scripts_styles()
{
    wp_enqueue_style('dspdev-video-shortcodes-style', plugin_dir_url(__FILE__) . "css/style.css");
    wp_enqueue_script('dspdev-video-shortcodes-scripts', plugin_dir_url(__FILE__) . "js/scripts.js", array("jquery"));
}