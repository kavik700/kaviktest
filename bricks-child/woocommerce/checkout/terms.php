<?php
/**
 * Checkout terms and conditions area.
 *
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
	
	do_action( 'woocommerce_checkout_before_terms_and_conditions' );

	$curr_lang = apply_filters( 'wpml_current_language', NULL );
	if ($curr_lang == 'de') {
		
		$prvcyplcy = site_url('datenschutz');
		$trmcndtn  = site_url('agbs');

	} else { 

		$prvcyplcy = '#';
		$trmcndtn  = "#";
	} ?>

	<div class="woocommerce-terms-and-conditions-wrapper">
		<?php
		/**
		 * Terms and conditions hook used to inject content.
		 *
		 * @since 3.4.0.
		 * @hooked wc_checkout_privacy_policy_text() Shows custom privacy policy text. Priority 20.
		 * @hooked wc_terms_and_conditions_page_content() Shows t&c page content. Priority 30.
		 */ ?>

		<?php if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>

			<p class="form-row validate-required form-row billing_form_tc cus_checkbox_checkout">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" 
					<?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // WPCS: input var ok, csrf ok. ?> id="terms" />
				
					<span class="checkbox-spn"></span>
					<span class="woocommerce-terms-and-conditions-checkbox-text">
						<?php _e('By clicking Place Order, I accept the', "woocommerce"); ?> 
						<a href="<?= $trmcndtn; ?>" target="_blank"><?php _e('Terms & Conditions', "woocommerce"); ?></a> 
						<?php _e('and', "woocommerce"); ?> 
						<a href="<?= $prvcyplcy; ?>" target="_blank"><?php _e('Privacy Policy', "woocommerce"); ?></a>.

						<?php //wc_terms_and_conditions_checkbox_text(); ?>
					</span>&nbsp;
					<!-- <abbr class="required" title="<?php //esc_attr_e( 'required', 'woocommerce' ); ?>">*</abbr> -->
				</label>
				<input type="hidden" name="terms-field" value="1" />
			</p>

			<?php do_action( 'woocommerce_checkout_terms_and_conditions' ); ?>

		<?php endif; ?>
	</div>
	<?php

	do_action( 'woocommerce_checkout_after_terms_and_conditions' );
}
