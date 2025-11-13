<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-reset-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

// do_action( 'woocommerce_before_reset_password_form' );
?>
<section class="common_forms_section reset_password_form_sec">
	<div class="brxe-container">
		<a href="javascript:window.history.back();" class="prev_page_link"><i class="fas fa-arrow-left-long"></i></a>
		<div class="common_forms_wrap">			
			<div class="form_left_content">
				<div class="form_logo">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-white.svg" alt="logo" />
				</div>
				<h1>Trete studypeak bei und erlebe deinen persönlichen Lernerfolg.</h1>
				<p>Melde dich an, um aktiv zu lernen!</p>
			</div>
			<div class="form_right_content">
				<form method="post" class="woocommerce-ResetPassword lost_reset_password">
					<div class="form_body">
						<div class="form_header">
							<h2 class="title">Passwort zurücksetzen</h2>
						</div>
					
						<p class="paragraph_desc"><?php echo apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Bitte gebe unten dein neues Passwort ein und klicke auf „Passwort ändern“, um fortzufahren.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

						<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
							<label for="password_1"><?php esc_html_e( 'Neues Passwort', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" placeholder="Neues Passwort" />
						</p>
						<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
							<label for="password_2"><?php esc_html_e( 'Passwort wiederholen', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" placeholder="Passwort erneut eingeben" />
						</p>

						<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
						<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

						<div class="clear"></div>

						<?php do_action( 'woocommerce_resetpassword_form' ); ?>

						<p class="woocommerce-form-row form-row">
							<input type="hidden" name="wc_reset_password" value="true" />
							<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Kennwort ändern', 'woocommerce' ); ?>"><?php esc_html_e( 'Passwort ändern', 'woocommerce' ); ?></button>
						</p>

						<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
<?php
//do_action( 'woocommerce_after_reset_password_form' );

