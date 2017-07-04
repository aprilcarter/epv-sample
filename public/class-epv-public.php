<?php
if(!defined('ABSPATH')) {
    die();
}

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
class Extend_Product_Vendors_Public {

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
	* Start a session for CSRF tokens
	*/
	public function start_new_session() {
	    if(!session_id()) {
	        session_start();
	    }

	    $request_time = $_SERVER['REQUEST_TIME'];
	    $timeout_duration = 86400;
	    if(isset($_SESSION['CREATE_BC_CSRF']) && $request_time - $_SESSION['CREATE_BC_CSRF'] > $timeout_duration) {
	        unset($_SESSION['epv_csrf']);
	        unset($_SESSION['CREATE_BC_CSRF']);
	    }
	}

	/**
	 * hook callback removal after_setup_theme
	 */
	public function deregister_theme_actions()
	{
		remove_action('woocommerce_before_shop_loop_item_title', 'venedor_woocommerce_thumbnail', 10);
		remove_action('woocommerce_shop_loop_item_title', 'venedor_woocommerce_shop_loop_item_title_open', 1);
	}
	
    private function curl_get($url, array $options = array()) {
        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 4
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

	/**
	* Add serial numbers to the new order email
	*
	* @param object $order
	* @param bool $sent_to_admin
	* @param bool $plain_text
	* @param object $email
	*/
    public function additional_gc_details($order) {
        global $wpdb;
        $table = $wpdb->prefix . "epv_sn";
        $items = $order->get_items();
        $trows = "";
        if($order->get_status() == "completed") {
            foreach($items as $item_id => $item) {
                $product_id = $item['product_id'];
                $product = wc_get_product($product_id);
                if(!$product) {
                    continue;
                }
                if(!term_exists($product->get_title(), "product_cat")) {
                    continue;
                }

                if($product->is_virtual()) {
                    $bar = new Barcode();
                    $gc_data = $wpdb->get_results("SELECT serial_number, pin FROM $table WHERE product_id = $product_id", 'ARRAY_A');

                    if($gc_data === null || empty($gc_data)) {
                        $sn_output = "Please contact support.";
                        continue;
                    }

                    $gc_data = $gc_data[0];
                    $serial_number = lockr_decrypt($gc_data["serial_number"], 'empire_wholesales_default');
                    $barcode = $bar->BarCode_link("CODE128", $serial_number, 50, 1);

                    if($serial_number === null) {
                        $sn_output = "Please contact support.";
                        continue;
                    }

                    $sn_output = $serial_number;
                    $sn_output = "<img src='" . EPV_URL . "lib/" . $barcode . "'>";
                } else {
                    $sn_output =  "Physical gift card";
                }
                $pin = empty($gc_data["pin"]) || preg_match('/[^0-9]/', $gc_data['pin']) ? "--" : esc_html($gc_data['pin']);
                $trows .= "<tr class='" . esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ) . "'>";
                $trows .= "<td class='td' style='text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;'>" . __($item['name'], $this->plugin_name) . "</td>";
                $trows .= "<td class='td' style='text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;'>$" . __($product->regular_price, $this->plugin_name) . "</td>";
                $trows .= "<td class='td' style='text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;'>" . __($sn_output, $this->plugin_name) . "</td>";
                $trows .= "<td class='td' style='text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;'>" . __($pin, $this->plugin_name) . "</td>";
                $trows .= "</tr>";
            }
            if($trows != ""):
            ?>
                <h4><?php _e('Your Gift Cards', $this->plugin_name); ?></h4>
                <p style="margin-bottom: 15px;"><?php _e("When you want to use one of your gift cards, enter one of these serial numbers or bring it with you to the establishment you would like to use it at. Please note, if you ordered any physical gift cards, they display here without a barcode, and will be shipped separately from each seller.", $this->plugin_name); ?></p>
                <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
                    <thead>
                        <tr>
                            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Card', $this->plugin_name ); ?></th>
                            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Value', $this->plugin_name ); ?></th>
                            <th class="td" scope="col" style="text-align:left;"><?php _e( 'Serial Number', $this->plugin_name ); ?></th>
                            <th class="td" scope="col" style="text-align:left;"><?php _e( 'PIN', $this->plugin_name ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $trows; ?>
                    </tbody>
                </table>
                <?php
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                $_SESSION['epv_csrf'] = $token;
                $_SESSION['CREATE_BC_CSRF'] = $_SERVER['REQUEST_TIME'];
                $link = home_url() . "/epv/order_details/" . $order->id . "/?token=" . $token;
                ?>

                <h4><?php _e('Print/Veiw Bar Codes and Serial Numbers', $this->plugin_name); ?></h4>
                <p><?php _e('If you would like to print the bar codes above, or your bar codes are not showing up, you can view the plain version of the table in a new web page. For the seurity of your gift cards, this link may only be used once and expires after 24 hours.', $this->plugin_name); ?></p>
                <a style="padding: 10px; background-color: #977e4a; color: white; font-weight: bold; border-radius: 10px; text-decoration: none;" target="_blank" href="<?php echo $link; ?>">View/Print</a>
            <?php endif;
        }
    }

    /**
    * Register email that will be used to resend serial numbers on customer request
    *
    * @param mixed[] @email_classes List of classes that repersent the different emails registered with Woocommerce
    * @reterns mixed[] the same list represented by $email_classes, which may or may not have been modified
    */
    public function add_resend_gc_email($email_classes) {
        // include our custom email class
        require( EPV_PATH . 'includes/class-epv-resend-gc-email.php' );

        // add the email class to the list of email classes that WooCommerce loads
        $email_classes['EPV_Resend_GC_Email'] = new EPV_Resend_GC_Email();

        return $email_classes;
    }

	/**
	* Add custom content to FUE variable list.
	*
	* Hooked to fue_before_variable_replacements in public main class.
	* Currently not in use in favor of the default mechanism for adding custom emails to Woocommere.
	* Will be used later if there is time to implement it, as it provides a friendlier interface for site managers
	*/
	public function custom_email_variables($var, $email_data, $queue_item, $fue_email) {
	    $variables = array(
	        'hello_world' => 'Hello World'
	    );
	    $var->register($variables);

	}

    /**
    * Add a custom trigger
    *
    * Allow a programmer to trigger the email from anywhere on the site
    * Hooked int fue_wc_customer_triggers. Will be implemented with custom_email_variables() if there is time
    */
	public function custom_email_trigger($triggers) {
	   $triggers["resend_request"] = __("resend request", $this->plugin_name);
	   return $triggers;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name . "-css", plugin_dir_url( __FILE__ ) . 'css/plugin-name-public.css', array(), $this->version, 'all' );
		wp_enqueue_style('parsley-css', plugins_url($this->plugin_name) . '/lib/validation/parsley/parsley.css');

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_style( 'featherlight-css', plugins_url($this->plugin_name) . '/lib/featherlight/featherlight.min.css' );

		wp_enqueue_script( 'accounting-js', plugins_url($this->plugin_name) . '/lib/validation/accounting.min.js', array('jquery'), $this->version );
		wp_enqueue_script( 'parsley-js', plugins_url($this->plugin_name) . '/lib/validation/parsley/parsley.min.js', array('jquery'), $this->version );
		wp_enqueue_script('parsley-comparison-js', plugins_url($this->plugin_name) . '/lib/validation/parsley/comparison.min.js', array('parsley-js'));
		wp_enqueue_script( 'featherlight-js', plugins_url($this->plugin_name) . '/lib/featherlight/featherlight.min.js', array('jquery') );
		wp_enqueue_script( 'list-js', '//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js', array('jquery') );
		wp_enqueue_script( $this->plugin_name . "-js", plugin_dir_url( __FILE__ ) . 'js/plugin-name-public.js', array( 'jquery', 'accounting-js', 'parsley-js', 'colorbox-js' ), $this->version, false );

		wp_localize_script($this->plugin_name . "-js", 'pve', array('ajaxUrl' => admin_url("admin-ajax.php"), 'commission' => get_option( 'wcpv_vendor_settings_default_commission' )));
	}

	/**
	* Associate custom templates and add rewrite rules
	*/
	public function rewrite_init() {
	    add_filter('template_include', array($this, 'bc_template'));
	    add_filter('init', array($this, 'bc_rewrite_rules'));
	}

	/**
	* Add query variables for bar code display page
	*
	* @param string[] $vars Query variables array to add to
	*/
	public function add_bc_query_vars($vars) {
	    array_push($vars, 'bc_order');
	    return $vars;
	}

	/**
	* Flush rewrite rules if custom rules are not present in the rewrite array
	*/
    public function bc_flush_rules() {
        $rules = get_option('rewrite_rules');
        if(!isset($rules['^epv/order_details/([^/]*)/?$'])) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

	/**
	* Load custom template if the appropriate query variable has data in it
	*/
	public function bc_template($template) {
	    $bc_order = get_query_var('bc_order');
	    if($bc_order) {
	        return plugin_dir_path(__FILE__) . "partials/bc-details.php";
	    }
	    return $template;
	}

	/**
	* Set the route associated with the custom url
	*/
    public function bc_rewrite_rules() {
        add_rewrite_rule('^epv/order_details/([^/]*)/?$', 'index.php?bc_order=$matches[1]', 'top');
    }

	/**
	* Footer debug
	*/
	public function debug() {

	    if(get_current_user_id() == 9) {
	        echo "<pre>";
            /*$mailer = WC()->mailer();
            $mails = $mailer->get_emails();
            $order_id = 1329;
            $order = new WC_Order($order_id);
            $vendors_group = WC_Product_Vendors_Utils::get_vendors_from_order( $order );
            $user = new WP_User(25);
            $vendor = get_terms(array(
                'taxonomy' => WC_PRODUCT_VENDORS_TAXONOMY,
                'hide_empty' => false,
                'name' => $user->data->display_name
            ));
            $data = get_term_meta($vendor[0]->term_id, 'vendor_data');
	        //1276
            //$mailer = WC()->mailer();
            //$mails = $mailer->get_emails();
            if(!empty($mails)) {
               //WC_Product_Vendors_Order_Email_To_Vendor
               //$mails['WC_Product_Vendors_Order_Email_To_Vendor']->trigger(1336);
               foreach($mails as $mail_object => $mail) {
               }
            }*/
            echo "</pre>";
	    }
	}

}
