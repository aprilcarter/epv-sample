<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/public
 * @author     April Carter <aprilddev@gmail.com>
 */
class Extend_Product_Vendors_Sidebars {
  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
  * Register all widget areas.
  *
  * @since 1.0.0
  */
  public function register_sidebars() {
    register_sidebar(array(
      'name' => __('EPV Sidebar', $this->plugin_name),
      'id' => 'epv-sidebar',
      'description' => __('Default sidebar on the Sell Gift Card page.', $this->plugin_name)
    ));
  }

  /**
  * Register default and custom widgets.
  *
  * All "default" sidebar content is added via a widget for ease of replacement or removal
  *
  * @since 1.0.0
  */
  public function epv_load_widgets() {
    register_widget('epv_sidebar_widget');
    register_widget('epv_shop_sidebar_widget');
  }
}
