<?php

/**
 *
 * @package           Extend_Product_Vendors
 *
 * Plugin Name:       Extend Product Vendors
 * Description:       This plugin encapsulates the changes made to the Woocommerce Product Vendors plugin needed to enable unique handling for giftcard items.
 * Version:           1.0.0
 * Author:            Decographic Inc.
 * Author URI:        http://decographic.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       extend-product-vendors
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * constant that contains plugin root path;
 *
 * @var string
 */
define("EPV_PATH", plugin_dir_path(__FILE__));
define("EPV_URL", plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate_epv() {
	require_once EPV_PATH . 'includes/class-plugin-name-activator.php';
	Extend_Product_Vendors_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_epv() {
	require_once EPV_PATH . 'includes/class-plugin-name-deactivator.php';
	Extend_Product_Vendors_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_epv' );
register_deactivation_hook( __FILE__, 'deactivate_epv' );

require EPV_PATH . 'lib/barcode/barcode.class.php';

/**
 * Woocommerce override functions
 */
require EPV_PATH . 'includes/epv-woocommerce-functions.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require EPV_PATH . 'includes/class-plugin-name.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_epv() {

	$plugin = new Extend_Product_Vendors(EPV_PATH);
	$plugin->run();

}
run_epv();
