<?php
/**
 * Exit if accessed directly
 *
 * @package    disable-wp-user-login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Function to create the user table.
 */
function dwul_install() {

	global $wpdb;
	$table_name = $wpdb->prefix . 'dwul_disable_user_email';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '%s'", $table_name ) != $table_name ) {

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
                    id int(10) NOT NULL AUTO_INCREMENT,
                    useremail varchar(200) NOT NULL,
                    PRIMARY KEY id (id)
                  ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}
