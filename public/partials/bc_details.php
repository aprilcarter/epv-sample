<?php 
if(!defined('ABSPATH')) {
    die();    
}


if(!isset($_SESSION['epv_csrf']) || empty($_SESSION['epv_csrf'])) {
    ?>
    <h1 class="page-title title">Hey there!</h1>
    <p style="font-size: 16px; max-width: 500px;">
    To keep the wrong people from getting your gift cards, we made the link in your email only good for the first click, right from your email.
    If you need to see or print your gift cards again, please visit your orders in your <a href="/my-account" style="color: #977e4a; text-decoration: none; font-weight: bold;">account</a>
    to resend your gift cards, or <a href="/contact-us" style="color: #977e4a; text-decoration: none; font-weight: bold">contact us</a>.
    </p>
    <?php
    wp_die();
}

unset($_SESSION['epv_csrf']);