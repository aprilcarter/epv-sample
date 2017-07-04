<?php
/**
 * Content and logic for changes to Woocommerce My Account
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/admin
 */
class EPV_Myaccount {
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
	 * Define and load dependencies for the class
	 *
	 * @since    1.0.0
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Add chart to My Account dashboard for active gift card listings.
	 *
	 * @since    1.0.0
	 */
	public function add_listings_chart() {
	    
	    if(current_user_can('customer') || current_user_can('wc_product_vendors_manager_vendor') || current_user_can('wc_product_vendors_admin_vendor')) {
          global $wpdb;
          $_pf = new WC_Product_Factory();
          $ids = array();
          $table = $wpdb->prefix . 'epv_sn';
          $user_id = get_current_user_id();
          
          $gift_cards = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id=%s", $user_id), 'ARRAY_A');
          
          /*$args = array(
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'author' => $user_id,
            'post_status' => array('pending', 'publish'),
            'fields' => 'ids',
            'tax_query' => array(
                array(
                  'taxonomy' => 'product_tag',
                  'field' => 'slug',
                  'terms' => 'gift-cards'
                )
            )
          );
          
          $gc_query = new WP_Query($args);
          wp_reset_postdata();
          
          $gc_products = $gc_query->posts;
          $ids = array();
          foreach ($gc_products as $id) {
            $ids[] = $id;
          }
          $ids = implode("', '", $ids);
          $ids = "'" . $ids . "'";
    
          $sn = $wpdb->get_results($wpdb->prepare("SELECT serial_number FROM $table WHERE product_id in (%s)", $ids), ARRAY_A);*/
          
          ob_start();
          include_once EPV_PATH . "public/partials/epv-gc-vendor-dashboard.php";
          echo ob_get_clean();
	    }
	}
	
	/**
	* Get user-friendly translations of product statuses
	* 
	* @param string $status publish, pending or sold
	*/
	public function get_product_status_string($status) {
	    switch ($status) {
	        case "publish":
	            echo "<span class='green'>ACTIVE</span>";
	            break;
	        case "pending":
	            echo "<span class='yellow'>REVIEW</span>";
	            break;
	        case "sold":
	            echo "<span class='red'>SOLD</span>";
	            break;
	        case "draft":
	            echo "<span class='gray'>HOLD<?span>";
	            break;
	        default:
	            echo "--";
	    }
	}

 
  /**
   * add button my gift card orders for resending serial number email
   *
   * @param array $actions Array of existing order actions
   * @param object $order WC_Order object for the order represented on a given line
   * @return return array $actions
   */
  function epv_add_resend_sn( $actions, $order )
  {
    $status = $order->get_status();
    if($status == "completed") {
        $items = $order->get_items();
        foreach ($items as $item) {
          $product_id = $item['product_id'];
          $tags = wp_get_post_terms($product_id, "product_tag", array('fields' => 'names'));
          if(in_array("gift-cards", $tags)) {
            $actions['resend-sn'] = array(
                'url'  => '#resend-sn',
                'name' => 'Resend eCards'
            );
            break;
          }
        }
    }

    return $actions;
  }
  
  /**
  * Send an email with all the gift cards in an order 
  *
  * Currently implemented in functions.php. Also the option to trigger this is only given if an order is processing or completed
  */
  public function request_resend_gc() {
        $error_string = "<p style='color: red; font-size: 16px'>We were unable to resend your electronic gift cards. Please <a href='/contact-us'>contact us</a> for support.</p>";
        
        if(!is_user_logged_in()) {
            wp_send_json_error(array("error" => $error_string, "order_id" => $order));
            wp_die();
        }
        
        if(!isset($_POST['order_id']) || empty($_POST['order_id'])) {
            $order = $_POST['order_id'];
            wp_send_json_error(array("error" => $error_string, "order_id" => $order));
            wp_die();
        }
        
        $order_id = $_POST['order_id'];

        $mailer = WC()->mailer();
        $mails = $mailer->get_emails();
        if ( ! empty( $mails ) ) {
            foreach ( $mails as $mail ) {
                if ( $mail->id == 'epv_resend_gc' ) {
                    $mail->trigger($product_id);
                    //$mail->recipient = wc_get_order($order_id)->billing_email;
                    wp_send_json_success(array("order_id" => $order_id, "recipient" => $mail->get_recipient()));
                    wp_die();
                }
             }
        }
        
        wp_send_json_error(array("error" => $error_string));
        wp_die();
  }
  
  /**
  * Add first name and last name to new customer array
  *
  * @param array @args wp_insert_user arguments
  */
  public function add_customer_name($args) {
        if(isset($_POST['first-name']) && !empty($_POST['first-name'])) {
          $fname = sanitize_text_field($_POST['first-name']);
        } else {
           return $args;    
        }
      
        if(isset($_POST['last-name']) && !empty($_POST['last-name'])) {
          $lname = sanitize_text_field($_POST['last-name']);
        } else {
            return $args;
        }
      
        $args['first_name'] = $fname;
        $args['last_name'] = $lname;
      
        return $args;
  }
  
  /**
  * Update new customer with billing_phone
  *
  */
  public function add_customer_phone($customer_id) {
    if(isset($_POST['phone']) && !empty($_POST['phone'])) {
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $phone = substr($phone, 0, 10);
        update_post_meta($customer_id, '_billing_phone', $phone);
    }
  }
  
  /**
  * Disable "Cancel" action in My Orders of customer account
  * 
  * Do not allow the cancel option to show up for any product status.
  *
  * @param string[] $statuses The order statuses that the cancel action should be displayed for
  */
  public function disable_my_orders_cancel_action($statuses) {
      $statuses = array();
      return $statuses;
  }

}
