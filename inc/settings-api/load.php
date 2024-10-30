<?php

spl_autoload_register( function ( $class ) {
	if ( 0 !== strpos( $class, 'InfoGalore_WP_Settings_' ) ) {
		return;
	}

	$class = str_replace( '_', '-', strtolower( substr( $class, 23 ) ) );
	if ( file_exists( $file = dirname( __FILE__ ) . "/class-settings-$class.php" ) ) {
		require_once( $file );
	}
} );