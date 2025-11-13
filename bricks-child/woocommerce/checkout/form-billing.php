<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
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
<div class="woocommerce-billing-fields">
	<h2 class="checkout_title"><?php esc_html_e( 'Secure payment', 'woocommerce' ); ?></h2>
	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3 class="checkoutinner_title"><?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>

	<?php else : ?>

		<h3 class="checkout_inner_title">1. <?php esc_html_e( 'Ihre Angaben', 'woocommerce' ); ?></h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper checkout_mailadd_form">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );

		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}

		$curr_lang = apply_filters( 'wpml_current_language', NULL );
		if ($curr_lang == 'de') {
			
			$prvcyplcy = site_url('de/datenschutz');
			$trmcndtn  = site_url('de/agbs');

		} else { 

			$prvcyplcy = '/';
			$trmcndtn  = '/';
		}

		?>
		<div class="errors email_err"></div>

		<div class="form-row billing_form_tc cus_checkbox_checkout" style="display:none;">			
			<label for="billing_term_condition"><?php _e('By clicking Place Order, I accept the', 'woocommerce'); ?> 
				<input type="checkbox" name="term_condition" class="term_condition" id="billing_term_condition">
				<span class="checkbox-spn"></span>
				<a href="<?php echo $trmcndtn; ?>" target="_blank"><?php _e('Terms & Conditions', 'woocommerce'); ?></a> <?php _e('and', 'woocommerce'); ?> 
				<a href="<?php echo $prvcyplcy; ?>" target="_blank"> <?php _e('Privacy Policy', 'woocommerce'); ?></a>.
			</label>	
			<span class="errors billing_check_err"></span>				
		</div>
		
		<div class="form-row">
			<div class="email-section edit_payment">
				<div class="eamil_value"></div>
				<a href="javascript:void(0);" class="proceed-email-address"><?php esc_html_e( 'Proceed to payment', 'woocommerce' ); ?></a>
			</div>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
