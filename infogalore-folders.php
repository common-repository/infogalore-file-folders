<?php

/*
Plugin Name: InfoGalore File Folders
Description: Manage and publish file folders.
Version: 1.0
Author: SIA Info
Author URI: http://sia-info.lv
License: GPL2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( defined( 'INFOGALORE_FOLDERS_VERSION' ) ) {
	die( 'It seems like InfoGalore File Folders plugin is already active.' );
}

define( 'INFOGALORE_FOLDERS_VERSION', '1.0' );
define( 'INFOGALORE_FOLDERS_PLUGIN_FILE', __FILE__ );
define( 'INFOGALORE_FOLDERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/*
 * Automatically loads required class files.
 */
spl_autoload_register( function ( $class ) {
	$class_prefix = 'InfoGalore_Folders_';
	if ( 0 !== strpos( $class, $class_prefix ) ) {
		return;
	}

	$class = str_replace( '_', '-', strtolower( substr( $class, strlen( $class_prefix ) ) ) );
	if ( file_exists( $file = INFOGALORE_FOLDERS_PLUGIN_DIR . "/inc/class-$class.php" ) ) {
		require_once( $file );
	}
} );

InfoGalore_Folders_Plugin::factory(
	new InfoGalore_Folders_Settings()
)->run();