<?php
/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$user_id 	  = $current_user->ID;
$fname 		  = $current_user->first_name; 
$lname 		  = $current_user->last_name; 
$uname 		  = $fname;
if (empty($fname)) { $uname = $current_user->display_name; }


?>

<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
	<?php
	/**
	 * Filter the message shown after a checkout is complete.
	 *
	 * @since 2.2.0
	 *
	 * @param string         $message The message.
	 * @param WC_Order|false $order   The order created during checkout, or false if order data is not available.
	 */
	
	//$message = apply_filters( 'woocommerce_thankyou_order_received_text', esc_html( __( 'Thank you for your purchase', 'woocommerce' ) ), $order );


	

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<div class="main_page_title"><h1 class="title">'.__( 'Vielen Dank für Ihren Einkauf', 'woocommerce' ).', '.$uname.'!</h1></div>'; ?>
</p>
