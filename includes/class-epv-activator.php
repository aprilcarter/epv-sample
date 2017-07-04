<?php

/**
 * Fired during plugin activation
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 * @author     April Carter <aprilddev@gmail.com>
 */
class Extend_Product_Vendors_Activator {

	/**
	 * Add the table for the plugin.
	 */
	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . "epv_sn";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			    id mediumint(9) NOT NULL AUTO_INCREMENT,
			    date datetime NOT NULL,
				product_id mediumint(9) NOT NULL,
				brand varchar(30) NOT NULL,
				user_id mediumint(9) NOT NULL,
				card_id varchar(20) NOT NULL,
			    serial_number varbinary(250) NOT NULL,
			    price varchar(10) NOT NULL,
			    value varchar(10) NOT NULL,
				pin varbinary(24) NULL,
				reviewed varchar(3) NOT NULL DEFAULT 'no',
				sold bool NOT NULL DEFAULT 0,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

}
