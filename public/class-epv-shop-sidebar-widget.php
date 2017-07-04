<?php
/**
 * The widget that outputs the default content for the Sell Gift Card page sidebar
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/admin
 */

/**
 * The widget that outputs the default content for the Sell Gift Card page sidebar
 *
 * This allows the sidebar content to be swapped out and customized
 *
 * @package    Extend_Product_Vendors
 * @subpackage Extend_Product_Vendors/admin
 */
class Epv_Shop_Sidebar_Widget extends WP_Widget {

  /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
  private $plugin_name = 'extend-product-vendors';

  public function __construct() {

    parent::__construct(
      'epv_shop_sidebar_widget',
      __('EPV Shop Sidebar Widget', $this->plugin_name),
      array(
        'description' => __('The default shop sidebar content.', $this->plugin_name)
      )
    );
  }

  public function widget($args, $instance) {
    $departments = get_terms(array(
      'taxonomy' => 'product_cat',
      'parent' => get_term_by('slug', 'department', 'product_cat')->term_id,
      'hide_empty' => false
    ));
    echo $args['before_widget'];
    //currently, there is a custom field in the brand attributes called "my_field" which needs to be an image upload meta box, the value of which will be output here.
    ?>
    <ul class="epv-shop-sidebar-menu">
      <!--<li class="section specials">
        <p class="title"><?php _e('Specials', $this->plugin_name); ?></p>
        <a href="<?php echo $specials_url; ?>"><?php _e('On Sale ', $this->plugin_name); ?><span><?php _e('NEW', $this->plugin_name) ?></span></a>
      </li>-->
      <li class="section by-category">
        <p class="title"><?php _e('Sort By Category', $this->plugin_name); ?></p>
        <ul>
          <?php
          foreach ($departments as $dept) {
            $link = get_term_link($dept, 'product_cat');
            ?>
            <li>
              <a class="to-category" href="<?php echo esc_url($link); ?>"><?php esc_html_e($dept->name, $this->plugin_name); ?></a>
            </li>
            <?php
          }
          ?>
        </ul>
      </li>
    </ul>
    <?php
    echo $args['after_widget'];
  }

}
