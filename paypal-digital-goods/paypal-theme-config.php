<?php
require_once( BBT_PL_DIR . '/paypal-digital-goods/paypal-digital-goods.class.php' );

$username = bbt_get_option('paypal_username');
$password = bbt_get_option('paypal_password');
$signature = bbt_get_option('paypal_signature');
$paypal_business_name = bbt_get_option('paypal_business_name');
$paypal_business_name = !empty($paypal_business_name) ? $paypal_business_name : esc_html__('Business Name','materialist');

PayPal_Digital_Goods_Configuration::username( trim($username) );
PayPal_Digital_Goods_Configuration::password( trim($password) );
PayPal_Digital_Goods_Configuration::signature( trim($signature) );
PayPal_Digital_Goods_Configuration::business_name( $paypal_business_name );
//PayPal_Digital_Goods_Configuration::environment( 'live' );

$return_url = ($is_profile) ? get_permalink() : bbt_get_option('paypal_return_url');
$cancel_url = ($is_profile) ? get_permalink() : bbt_get_option('paypal_cancel_url');
$notify_url = ($is_profile) ? get_permalink() : bbt_get_option('paypal_notify_url');

PayPal_Digital_Goods_Configuration::return_url( esc_url($return_url) );
PayPal_Digital_Goods_Configuration::cancel_url( esc_url($cancel_url) );
PayPal_Digital_Goods_Configuration::notify_url( esc_url($notify_url) );


$currency = bbt_get_option('paypal_currency');
PayPal_Digital_Goods_Configuration::currency( trim($currency) ); // 3 char character code, must be one of the values here: https://developer.paypal.com/docs/classic/api/currency_codes/