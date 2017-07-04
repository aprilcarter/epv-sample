<!DOCTYPE html>
<html>
<?php 
if(!defined('ABSPATH')) {
    die();    
}

?>
<head>
    <style>
    body {
        font-family: "Raleway", "Helvetica Neue", Helvetica, Arial, sans-serif;    
    }
    .bc-container {
        color: #333333;
        max-width: 600px;
        width: 50%;
        margin-right: auto;
        margin-left: auto;
        padding: 15px;
        background-color: #fff;
        border-radius: 15px;
    }
    .bc-container p {
        line-height: 20px;    
    }
    .bc-container table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    .bc-container th {
        text-align: left;
        padding-top: 10px;
        padding-bottom: 10px;    
    }
    .bc-container td {
        border: 1px solid #8D8D8D;
        padding: 10px;    
    }
    .logo {
        width: 100%;    
    }
    .logo img {
        display: block;
        margin-right: auto;
        margin-left: auto;
        height: 100px;
    }
    #error-page .contact p,
    .contact h3,
    .contact p {
        margin: 0;    
    }
    </style>
</head>
    <body>
        <header>
            <div class="logo">
                <img src="http://empirewholesales.com/wp-content/uploads/2017/04/EMPIRE-WHOLESALE-LOGO-small.png" alt="Empire Wholesales">
            </div>
        </header>
        <?php
        
        //If the security tokens are not verifed, don't let them see the gift cards
        if(!isset($_SESSION['epv_csrf']) || empty($_SESSION['epv_csrf'])) {
            ?>
            <h1 class="page-title title">Hey there!</h1>
            <p style="font-size: 16px; max-width: 500px;">
            To keep the wrong people from getting your gift cards, we made the link in your email only good for the first click, right from your email.
            If you need to see or print your gift cards again, please visit your orders in your <a href="/my-account" style="color: #977e4a; text-decoration: none; font-weight: bold;">account</a>
            to resend your gift cards, or <a href="/contact-us" style="color: #977e4a; text-decoration: none; font-weight: bold">contact us</a>.
            </p>
            <div class="contact">
                <h3>Contact Us</h3>
                <p><strong>Phone: </strong>(954) 683-1494</p>
                <p><strong>Email: </strong>info@empirewholesales.com</p>
            </div>
            <?php
            wp_die();
        }
        
        $order_num = 0;
        $order_num = get_query_var('bc_order');
        if(isset($_GET['bc_order']) && !empty($_GET['bc_order'])) {
            $order_num = get_query_var('bc_order');
        }
        
        $order = new WC_Order($order_num);
        $items = $order->get_items();
?>
<div class="bc-container">
<?php
        if(is_wp_error($items) || empty($items)) {
            ?>
            <h1>Oops!</h1>
            <p>
            It doesn't look like there is anything in this order. If this isn't right, go ahead and <a href="/contact-us" style="color: #977e4a; text-decoration: none; font-weight: bold">contact us</a>.
            <?php if($order_num > 0): ?>
                Your order number is <strong><?php echo $order->get_id(); ?></strong>.
            <?php endif; ?>
            </p>
            <div class="contact">
                <h3>Contact Us</h3>
                <p><strong>Phone: </strong>(954) 683-1494</p>
                <p><strong>Email: </strong>info@empirewholesales.com</p>
            </div>
            <?php
            wp_die();
        } ?>
           
            <p style="font-size: 16px">You will only be able to use this link <strong>once</strong>. In order to get another printable link, please visit your orders in your <a href="/my-account" style="color: #977e4a; text-decoration: none; font-weight: bold;">account</a>
                to resend your gift cards, or <a href="/contact-us" style="color: #977e4a; text-decoration: none; font-weight: bold">contact us</a>. If you are here because you cannot see the bar code or serial number
                in your email account, try temporarily changing the primary email for your customer account to a different provider (for instance, if you are using Gmail, try Outlook or Yahoo), and then resending the gift cards from your orders page.</p>
            <table>
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Balance</th>
                        <th>Serial Number</th>
                        <th>PIN</th>
                    </tr>
                </thead>
                <tbody>
                
            <?php foreach($items as $item): 
                    global $wpdb;
                    $product_id = $item['product_id'];
                    $tags = wp_get_post_terms($product_id, "product_tag", array('fields' => 'names'));
                    if(!in_array('gift-cards', $tags)) {
                        continue;    
                    }
                    
                    $table = $wpdb->prefix . "epv_sn";
                    $sn_record = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE product_id=%s", $product_id), 'ARRAY_A')[0];
                    
                    $bar = new Barcode();
                    $barcode = $bar->BarCode_link("CODE128", lockr_decrypt($sn_record['serial_number'], 'empire_wholesales_default'), 50, 1);
                    $barcode_link = EPV_URL . "lib/" . $barcode;
                    $barcode_output = "<img src='" . $barcode_link . "'>";
            ?>
                    <tr>
                        <td><?php echo $sn_record['brand']; ?></td>
                        <td><?php echo $sn_record['value']; ?></td>
                        <td><?php echo $barcode_output; ?></td>
                        <td><?php echo $sn_record['pin']; ?></td>
                    </tr>
            <?php endforeach; ?>
            
                </tbody>
            </table>
            <div class="contact">
                <h3>Contact Us</h3>
                <p><strong>Phone: </strong>(954) 683-1494</p>
                <p><strong>Email: </strong>info@empirewholesales.com</p>
            </div>
        </div>
    </body>
</html>
<?php unset($_SESSION['epv_csrf']);
unset($_SESSION['CREATE_BC_CSRF']);