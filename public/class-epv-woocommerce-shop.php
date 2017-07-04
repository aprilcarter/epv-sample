<?php
/**
 * Everything concerning the display and structure of the store iteslf.
 *
 * @since 1.0.0
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/public
 */
class EPV_Woocommerce_Shop {

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

	private $brands_per_page;

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
		$this->brands_per_page = 12;

	}

	/**
	 * Put the title and price inside the same container.
	 */
	function mod_venedor_woocommerce_shop_loop_item_title_open() {
		global $product, $woocommerce, $venedor_settings;
		//show price
		if ($venedor_settings['product-price']) {
				if ($product->get_price() != '') {
						$variable_class = '';
						if ($product->is_type( array( 'variable' ) ) && $product->get_variation_price( 'min' ) !== $product->get_variation_price( 'max' ))
								$variable_class = ' price-variable';
						if ($product->is_type( array( 'grouped' ) )) {
								$child_prices = array();
								foreach ( $product->get_children() as $child_id )
									$child_prices[] = get_post_meta( $child_id, '_price', true );
								$child_prices = array_unique( $child_prices );
								if ( ! empty( $child_prices ) ) $variable_class = ' price-variable';
						}
						// echo '<div class="price-box '. $venedor_settings['product-price-pos'] . $variable_class . '">';
						woocommerce_template_loop_price();
						// echo '</div>';
				}
		}

		//wrap title
    ?><a class="product-loop-title" href="<?php the_permalink(); ?>"><?php
	}

  /**
   * function from the Venedor theme that renders the items on the shop page.
   *
   * Defines the item image in the shop loop.
   *
   * @param type var Description
   * @return return string The html to display the image
   */
  function mod_venedor_woocommerce_thumbnail() {
      global $product, $woocommerce, $venedor_settings;

      $id = get_the_ID();
      $size = 'shop_catalog';

      $gallery = get_post_meta($id, '_product_image_gallery', true);
      $attachment_image = '';
      if ($venedor_settings['category-image-effect'] && !empty($gallery)) {
          $gallery = explode(',', $gallery);
          $first_image_id = $gallery[0];
          $attachment_image = wp_get_attachment_image($first_image_id , $size, false, array('class' => 'hover-image'));
      }
      $thumb_image = get_the_post_thumbnail($id , $size);
      $class="product-image";
      if (!$thumb_image) {
          if ( wc_placeholder_img_src() )
              $thumb_image = wc_placeholder_img( $size );
      }
      if (!$attachment_image || !$venedor_settings['category-image-effect'])
          $class="product-image no-image";
      echo '<span class="'.$class.'">';
      // show images
      echo $attachment_image;
      echo $thumb_image;
      // show hot/sale label
      woocommerce_show_product_loop_sale_flash();

  		// previous placement of price block

      // show quick view
      if ($venedor_settings['category-quickview']) : ?>
          <div class="figcaption<?php if (!$venedor_settings['category-hover']) echo ' no-hover' ?>">
              <span class="btn btn-arrow quickview-button <?php echo $venedor_settings['category-quickview-pos'] ?>" data-id="<?php echo the_ID() ?>"><span class="fa fa-search"></span></span>
          </div>
      <?php endif;

      echo '</span>';
  }

	/**
	 * show empty product categories for giftcards.
	 *
	 * Modifies WC_Query taxonomy query. Used for testing when there are not many cards in the store.
	 *
	 * @param object $q The product query
	 * @param object $instance The WC_Query instance
	 */
	public function show_empty_categories ( $show_empty ) {
	  $show_empty  =  true;
    return $show_empty;
  }

	protected function is_brands() {
		if(is_tax('product_cat', 'gift-cards-by-brand') || is_tax('product_cat', 'online') || is_tax('product_cat', 'in-store-only')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * add query variabls that will contain values for subcategory pagination
	 *
	 * @param mixed[] $vars The array of query variables
	 * @return mixed[] $vars
	 */
	public function brands_pagination_query_vars($vars)
	{
		    $vars[] = "epv_items_per_page";
			$vars[] = "epv_max_num_pages";
			$vars[] = "epv_num_items";
			$vars[] = "epv_paged";
		return $vars;
	}

	/**
	 * calculate the values for brands product subcategory pagination
	 *
	 * Undocumented function long description
	 *
	 * @param type var Description
	 * @return return type
	 */
	public function set_brands_paginaton_query_vars($query)
	{
		$parent = $query->get_queried_object();
		if(is_wp_error($parent) || !is_object($parent) || !property_exists($parent, "term_id") || !$this->is_brands()) {
		    return;
		}
		$parent = $parent->term_id;
		$term_children = get_term_children($parent, 'product_cat');
		$count = count($term_children);
		$ids = array();
		if(empty($count)) {
		    $count = 40;    
		}
		$max_num_pages = ceil($count/40);
		$paged = !isset($_GET['gcpg']) ? 1 : $_GET['gcpg'];
		
		if(isset($_GET['sort-letter']) && preg_match("/^[A-Za-z]{1}$/", $_GET['sort-letter'])) {
		   $letter = strtoupper($_GET['sort-letter']);
	       $titles = get_terms(array(
	           'taxonomy' => 'product_cat',
	           'include' => $term_children,
	           'fields' => 'id=>name'
	       ));
	       $_SESSION['sorted-ids'] = $titles;
	       foreach($titles as $id=>$title) {
	           $type = gettype($title);
	           if($type == "string") {
	               if(stripos($title[0], $letter) === 0) {
    	               $ids[] = $id;    
    	           } 
	           }
	       }
	        $query->query_vars['epv_testing'] = $titles;
	        $max_num_pages = ceil(count($ids)/40);
	        $query->query_vars['epv-sorted'] = $ids;
	    }
		
		$query->query_vars['epv_items_per_page'] = 40;
		$query->query_vars['epv_max_num_pages'] = $max_num_pages;
		$query->query_vars['epv_num_items'] = $count;
		$query->query_vars['epv_paged'] = $paged;
	}

	/**
	 * Get paginated sub category query result
	 *
	 * Change the arguments in the query for the brands list page to only get the brands relevant to the page the user is on.
	 *
	 * @param mixed[] $args Arguments for the get_terms function
	 * @return mixed[] $args
	 */
	public function paginated_brands_list($args)
	{
		global $wp_query;
		global $wpdb;
		
		if(isset($wp_query->query_vars['epv-sorted']) && !empty($wp_query->query_vars['epv-sorted'])) {
		    $args['include'] = $wp_query->query_vars['epv-sorted'];    
		}
		
		if($this->is_brands()) {
			$offset = $wp_query->query_vars['epv_items_per_page']*($wp_query->query_vars['epv_paged'] - 1);
			$args['offset'] = $offset;
			$args['number'] = $wp_query->query_vars['epv_items_per_page'];
		}
		return $args;
	}

	/**
	 * provide arguments for pagination links
	 *
	 * The subcat list is created by a separate qurey in woocommerce_sub_categories(). So pagination should be pulling from the results of that one rather than the parameters and results of the main query.
	 *
	 * @param mixed[] $args Arguments for pagination_links function
	 * @return mixed[] $args
	 */
	public function brands_list_pagination_links($args)
	{
		global $wp_query;

		if($this->is_brands()) {
			$args['total'] = $wp_query->query_vars["epv_max_num_pages"];
			$args['current'] = $wp_query->query_vars['epv_paged'];
		}
		return $args;
	}
	
	
	/**
	* Update the "sold" value in the card data table
	* 
	* @param int $post_id
	*/
	public function make_sn_available($post_id) {
        $product_tags = get_the_terms($post_id, 'product_tag');
        if($product_tags && !is_wp_error($product_tags)) {
            if(gettype($product_tags) == "array" && in_array("gift-cards", $product_tags)) {
                global $wpdb;
                $table = $wpdb->prefix . "epv_sn";
                $wpdb->update($table, array('sold' => 0), array('product_id' => $post_id));
            }    
        }
	}
	
	/**
	* Restore physical gift cards from trash when their orders are cancelled.
	* 
	* Currently, customers are only allowed to cancel orders during the "pending" status, as a fully processed payment means that the customer may have received an electronic gift card.
	*
	* @param int $order_id
	*/
	public function restore_gift_cards($order_id) {
	    $order = new WC_Order($order_id);
	    $items = $order->get_items();
	    
	    foreach($items as $item) {
	       $physical_parent = get_term_by('slug', 'in-store-only', 'product_cat')['term_id'];
	       $item_terms = get_the_terms($item['product_id'], 'product_cat');
	       if($physical_parent && $item_terms) {
    	        if(!is_wp_error($item_terms) || (gettype($item_terms) == "array" && in_array($physical_parent, $item_terms)) || (gettype($item_terms) == "integer" && $item_terms == $physical_parent)) {
    	            wp_untrash_post($item['product_id']); 
    	        }	           
	       }
	    }
	}
	
	/**
	* Change order status to completed automatically if it contains only electronic gift cards.
	* 
	* @param int $order_id
	* @param obj $order
	*/
	public function complete_electronic_orders($order_id, $order) {
	   $items = $order->get_items();
	   $non_electronic = false;
	   
	   foreach($items as $item) {
	       $product_tags = get_the_terms($item['product_id'], 'product_tag');
	       if(!in_array('gift-cards', $product_tags)) {
	           $non_electronic = true;  
	           break;
	       }
	       
	       $physical_parent = get_term_by('slug', 'in-store-only', 'product_cat');
	       $item_terms = get_the_terms($item['product_id'], 'product_cat');
	       if(!in_array($physical_parent->term_id, $item_terms)) {
	           $non_electronic = true;
	           break;
	       }
 	   }
 	   
 	   //If there are no non-electronic products in the order, complete it in the system
 	   if(!$non_electronic) {
 	       $order->update_status('completed');    
 	   }
    }
    
    private function update_sold_record($sold, $product_id) {
        $updated = $wpdb->update( $table, array('sold' => $sold), array('product_id' => $product_id) );
        if(is_wp_error($updated) || $updated === false) {
            throw new Exception("not updated");     
        }   
    }
    
    public function update_sold_status($order_id, $from, $to) {
        if($to != 'completed') {
            return;    
        }
	    global $wpdb;
	    $table = $wpdb->prefix . "epv_sn";
	    $order = new WC_Order($order_id);
	    $items = $order->get_items();
	    foreach($items as $item) {
	        $product_id = $item['product_id'];
            $product = wc_get_product($product_id);
	        $product_tags = wp_get_object_terms($product_id, 'product_tag', array('fields' => 'slugs'));
    	    if(in_array("gift-cards", $product_tags)) {
	           try {
	               $this->update_sold_record(1, $product_id);    
	           } catch(Exception $e) {
	               return false;
	           }
	           
    	    }
	    }
    }
}
?>
