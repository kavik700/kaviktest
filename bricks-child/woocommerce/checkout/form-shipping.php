<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

<?php //wc_get_template('checkout/terms.php'); ?>
	<div class="woocommerce-shipping-fields" style="display:none;">
		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields( 'shipping' );

				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>	
	</div>
<?php endif; ?>

<div class="woocommerce-additional-fields">
	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

		<div class="woocommerce-additional-fields__field-wrapper" style="display: none;">
			
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>


			<?php $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			if (!wp_doing_ajax()) {
				do_action('woocommerce_review_order_before_payment');
			} ?>
			<div id="payment" class="woocommerce-checkout-payment">
				<div class="order-payment-main">
					<?php //if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>
						<h3 class="cards">
							<span class="checkout_inner_title"> <?php esc_html_e('2. Payment', 'woocommerce'); ?></span>
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/all-payment-cardss.svg" alt="All cards"/>
						</h3>
					<?php //endif; ?>

					<?php if (WC()->cart->needs_payment()): ?>
						<ul class="nav nav-tabs wc_payment_methods payment_methods methods">
							<?php
							if (!empty($available_gateways)) {
								foreach ($available_gateways as $gateway) {
									wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
								}
							} else {
								echo '<li>';
								wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__('Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce') : esc_html__('Please fill in your details above to see available payment methods.', 'woocommerce')), 'notice'); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
								echo '</li>';
							}
							?>
						</ul>
					<?php endif; ?>
				</div>
				<div class="form-row place-order abc">
					<noscript>
						<?php
						/* translators: $1 and $2 opening and closing emphasis tags respectively */
						printf(esc_html__('Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce'), '<em>', '</em>');
						?>
						<br /><button type="submit"
							class="button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
							name="woocommerce_checkout_update_totals"
							value="<?php esc_attr_e('Update totals', 'woocommerce'); ?>"><?php esc_html_e('Update totals', 'woocommerce'); ?></button>
					</noscript>

					<?php wc_get_template('checkout/terms.php'); ?>

					<?php do_action('woocommerce_review_order_before_submit'); ?>

					<?php 
					$order_button_text = 'Place an order';
					echo apply_filters('woocommerce_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" name="woocommerce_checkout_place_order" id="place_order" value="Place an order" data-value="Place an order">Bestellung aufgeben</button>'); // @codingStandardsIgnoreLine ?>

					<?php do_action('woocommerce_review_order_after_submit'); ?>

					<?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
				</div>
			</div>
			<?php
			if (!wp_doing_ajax()) {
				do_action('woocommerce_review_order_after_payment');
			} ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>
