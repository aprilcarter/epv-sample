<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/includes
 * @author     April Carter <aprilddev@gmail.com>
 */
class Extend_Product_Vendors {
	/**
	 * path to the root of the plugin
	 *
	 * @var string
	 */
	private $plugin_path;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Extend_Product_Vendors_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_path) {

		$this->plugin_name = 'extend-product-vendors';
		$this->version = '1.0.0';
		$this->plugin_path = $plugin_path;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_form_hooks();
		$this->define_customer_vendor_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Extend_Product_Vendors_Loader. Orchestrates the hooks of the plugin.
	 * - Extend_Product_Vendors_i18n. Defines internationalization functionality.
	 * - Extend_Product_Vendors_Admin. Defines all hooks for the admin area.
	 * - Extend_Product_Vendors_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plugin-name-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plugin-name-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-plugin-name-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-plugin-name-public.php';

		/**
		* Defines all form-related actions
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-extend-product-vendors-form.php';

		/**
		* Defines the default sell gift card page sidebar content.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-extend-product-vendors-widget.php';

		/**
		* Defines the default shop sidebar content.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-epv-shop-sidebar-widget.php';

		/**
		* Defines sidebars
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-epv-sidebars.php';

		/**
		* Shop specific display and functionality.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-epv-woocommerce-shop.php';

		/**
		* Woocommerce My Account changes and additions.
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-epv-myaccount.php';

		/**
		* Customer additional vendor information
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-epv-customer-vendor.php';

		$this->loader = new Extend_Product_Vendors_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Extend_Product_Vendors_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Extend_Product_Vendors_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	* Set up additional vendor information about customers who sell gift cards
	*
	* @since 1.0.0
	* @access private
	*/
	private function define_customer_vendor_hooks() {
	    $customer_vendor_class = new EPV_Customer_Vendor($this->get_plugin_name(), $this->get_version());

	    $this->loader->add_action('init', $customer_vendor_class, 'customer_vendor_permissions');
	    $this->loader->add_action('woocommerce_new_customer', $customer_vendor_class, 'add_cust_vendor_to_customer');
	}


	/**
	 * Register form hooks
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_form_hooks()
	{
		$form_class = new Extend_Product_Vendors_form($this->get_plugin_name(), $this->get_version());

		$this->loader->add_shortcode('gift-card-sale-form', $form_class, 'gift_card_sale_form');
		$this->loader->add_shortcode('gift-card-sale-page', $form_class, 'gift_card_sale_page');
		$this->loader->add_action('wp_ajax_save_gc_sale', $form_class, 'process_gc_sale_form');
		$this->loader->add_action('wp_ajax_nopriv_save_gc_sale', $form_class, 'process_gc_sale_form');
		$this->loader->add_action('epv_woocommerce_login_form', $form_class, 'detatched_login_form');
		$this->loader->add_filter( 'woocommerce_login_redirect', $form_class, 'from_product_listing_form', 10, 2 );
		$this->loader->add_action( 'init', $form_class, 'set_brands' );
		$this->loader->add_action( 'wp_ajax_seller_removed_card', $form_class, 'seller_remove_card' );
        $this->loader->add_action( 'wp_ajax_nopriv_seller_removed_card', $form_class, 'seller_remove_card' );
        $this->loader->add_action( 'admin_init', $form_class, 'admin_init_actions' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Extend_Product_Vendors_Admin( $this->get_plugin_name(), $this->get_version() );

		//debug
		$this->loader->add_action('admin_footer', $plugin_admin, 'debug_admin');

        //load resources
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//Default sidebar (front-end)
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'register_sidebars' );
		$this->loader->add_action( 'widgets_init', $plugin_admin, 'epv_load_widget' );

		//Vendor admin action restrictions
		$this->loader->add_action( 'admin_head', $plugin_admin, 'colorbox_hack' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'admin_redirect_for_gc' );
		$this->loader->add_action( 'admin_footer-post.php', $plugin_admin, 'disable_vendor_gc_cat_selection' );
        $this->loader->add_action( 'admin_footer-post-new.php', $plugin_admin, 'disable_vendor_gc_cat_selection' );
        $this->loader->add_action( 'admin_head', $plugin_admin, 'remove_vendor_admin_meta_boxes' );

        //Allow site admins to request new serial numbers for review
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'send_serial_numbers_for_review' );

        //Vendor admin display
        $this->loader->add_action( 'admin_body_class', $plugin_admin, 'vendor_back_end_class' );
        $this->loader->add_filter( 'pre_site_transient_update_core', $plugin_admin, 'epv_remove_notifications_for_vendors' );
        $this->loader->add_filter( 'pre_site_transient_update_plugins', $plugin_admin, 'epv_remove_notifications_for_vendors' );
        $this->loader->add_filter( 'pre_site_transient_update_themes', $plugin_admin,'epv_remove_notifications_for_vendors' );

        //Emails
        $this->loader->add_filter( 'woocommerce_email_classes', $plugin_admin, 'add_get_new_gift_cards_email' );
        $this->loader->add_action( 'wp_ajax_trigger_newgcs_email', $plugin_admin, 'trigger_get_new_gift_cards_email' );

		//Brand attribute image meta
		$this->loader->add_action('pa_brand_add_form_fields', $plugin_admin, 'add_brand_image_meta_field', 10, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Extend_Product_Vendors_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_sidebars = new Extend_Product_Vendors_Sidebars( $this->get_plugin_name(), $this->get_version() );
		$plugin_shop = new EPV_Woocommerce_Shop( $this->get_plugin_name(), $this->get_version() );
		$plugin_myaccount = new EPV_Myaccount( $this->get_plugin_name(), $this->get_version() );

		//debug
		$this->loader->add_action( 'wp_footer', $plugin_public, 'debug', 1 );

		//emails
		$this->loader->add_action( 'woocommerce_email_after_order_table', $plugin_public, 'additional_gc_details' );

		//general
		$this->loader->add_action('after_setup_theme', $plugin_public, 'deregister_theme_actions');
		$this->loader->add_action('init', $plugin_public, 'start_new_session', 1);

		//resources
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		//sidebars
		$this->loader->add_action( 'widgets_init', $plugin_sidebars, 'register_sidebars' );
		$this->loader->add_action( 'widgets_init', $plugin_sidebars, 'epv_load_widgets' );

		//shop
		$this->loader->add_action('woocommerce_before_shop_loop_item_title', $plugin_shop, 'mod_venedor_woocommerce_thumbnail', 10);
		$this->loader->add_action('woocommerce_shop_loop_item_title', $plugin_shop,  'mod_venedor_woocommerce_shop_loop_item_title_open', 1);
		//$this->loader->add_filter( 'woocommerce_product_subcategories_hide_empty', $plugin_shop, 'show_empty_categories', 10, 1 );
		$this->loader->add_filter('query_vars', $plugin_shop, 'brands_pagination_query_vars');
		$this->loader->add_filter('woocommerce_product_subcategories_args', $plugin_shop, 'paginated_brands_list');
		$this->loader->add_filter('woocommerce_pagination_args', $plugin_shop, 'brands_list_pagination_links');
		$this->loader->add_filter('pre_get_posts', $plugin_shop, 'set_brands_paginaton_query_vars');
		$this->loader->add_action('woocommerce_order_status_changed', $plugin_shop, 'update_sold_status', 10, 3);

        //my account
        $this->loader->add_action( 'woocommerce_before_my_account', $plugin_myaccount, 'add_listings_chart', 10 );
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions', $plugin_myaccount, 'epv_add_resend_sn', 10, 2 );
		$this->loader->add_filter( 'woocommerce_new_customer_data', $plugin_myaccount, 'add_customer_name' );
	    $this->loader->add_action( 'woocommerce_created_customer', $plugin_myaccount, 'add_customer_phone' );
	    $this->loader->add_filter( 'woocommerce_valid_order_statuses_for_cancel', $plugin_myaccount, 'disable_my_orders_cancel_action' );

	    //rewrite rules
	    $this->loader->add_action('plugins_loaded', $plugin_public, 'rewrite_init');
	    $this->loader->add_filter('query_vars', $plugin_public, 'add_bc_query_vars');
	    $this->loader->add_action('wp_loaded', $plugin_public, 'bc_flush_rules');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Extend_Product_Vendors_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
