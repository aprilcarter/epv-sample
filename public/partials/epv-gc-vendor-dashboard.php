<div class="listings-chart-container">
  <h3 class="section-title">Your Gift Card Listings</h3>
  <?php if(!current_user_can('customer')): ?>
      <a href="<?php echo esc_url(home_url() . '/wp-admin/edit.php?post_type=product'); ?>" class="product-admin"><?php _e("See all listings", $this->plugin_name); ?></a>
  <?php endif; ?>
  <table class="epv-listings-chart">
    <tr>
      <th><?php _e('BRAND', $this->plugin_name); ?></th>
      <th><?php _e('CARD', $this->plugin_name); ?></th>
      <th><?php _e('SELL', $this->plugin_name); ?></th>
      <th><?php _e('FULL', $this->plugin_name); ?></th>
      <th><?php _e('EARN', $this->plugin_name); ?></th>
      <th><?php _e('STATUS', $this->plugin_name); ?></th>
      <th><?php _e('OPTIONS', $this->plugin_name); ?></th>
    </tr>
<?php if (empty($gift_cards)): ?>
  </table>
  <div class="no-listings">
    <p class="message"><?php _e('You have no listings.', $this->plugin_name) ?></p>
    <p><a href="<?php echo esc_url(home_url() . '/sell-gift-card'); ?>"><?php _e('New Gift Card', $this->plugin_name); ?></a></p>
    <p><a href="<?php echo esc_url(home_url() . '/wp-admin'); ?>"><?php _e('Other Item'); ?></a></p>
  </div>
<?php else : ?>
<?php
  $row_num = 0;
?>
<?php foreach ($gift_cards as $gift_card): ?>

  <?php
  $brand = $gift_card['brand'];
  $last4 = preg_match('/\d{4}$/', lockr_decrypt($gift_card['serial_number'], 'empire_wholesales_default'), $matches);
  $match = $last4 ? " (" . $matches[0] . ")" : "";
  $sold = (int) $gift_card['sold'];

  $sell = $gift_card['price'] ? $gift_card['price'] : 0;
  $full = $gift_card['value'] ? $gift_card['value'] : 0;
  $product = wc_get_product($gift_card['product_id']);
  $status = 'pending';
  if($product) {
    $status = get_post_status($gift_card['product_id']);  
   }
   
   if($sold) {
        $status = 'sold';   
   }
    
    if(get_current_user_id() == 25) {
        echo "<div style='display: none;'><pre>";
            var_dump("hello");
        echo "</pre></div>";
    }
  
  //link to generate shipping label and to mark as shipped if physical card has been sold

  $option = array();
  if(!$sold && $status == "pending") {
    $option['text'] = "Remove Card";
    $option['link'] = "#are-you-sure";
    $option['id'] = "remove-card";
  } else {
    /*if ($product->is_virtual()) {
      $option['text'] = '--';
    } else {
      $option['text'] = "Get shipping label";
      $option['link'] = "#shipping-label";
      $option['id'] = "get-shipping-label";
    }*/
    $option['text'] = "--";
  }
  ?>
  <tr data-row=<?php echo $row_num; ?> class="row-<?php echo $row_num; ?>" data-card-id="<?php echo $gift_card['card_id'] ?>">
    <td class="card-brand"><?php esc_html_e(__($brand, $this->plugin_name)); ?></td>
    <td class="card-last4"><?php echo $match; ?></td>
    <td>$<?php echo number_format($sell, 2); ?></td>
    <td>$<?php echo number_format($full, 2); ?></td>
    <td>$<?php echo number_format($sell * get_option( 'wcpv_vendor_settings_default_commission' )/100, 2); ?></td>
    <td class="card-status"><?php echo $this->get_product_status_string($status); ?></td>
    <td class="card-actions">
      <?php if(array_key_exists('link', $option)): ?>
        <a href="<?php echo $option['link']; ?>" id="<?php echo $option['id']; ?>"><?php _e($option['text'], $this->plugin_name); ?></a>
      <?php else: ?>
        <?php echo $option['text']; ?>
      <?php endif; ?>
    </td>
  </tr>
  <?php $row_num++; ?>
<?php endforeach; ?>
</table>
<?php endif; ?>

    <div style="display: none;">
        <div id="are-you-sure">
            <h4><?php _e('Are you sure?', $this->plugin_name); ?></h4>
            <p><?php _e('Deleting your listing will remove all information about it from our system. You will have to create a completely new listing if you change your mind.', $this->plugin_name); ?></p>
            <button id="delete-listing" data-cardid data-cardrow class="button">Delete Listing</button>
        </div>
    </div>
</div>
