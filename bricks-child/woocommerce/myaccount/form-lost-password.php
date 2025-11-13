<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
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

// do_action( 'woocommerce_before_lost_password_form' );
?>
<section class="common_forms_section reset_password_form_sec">
	<div class="brxe-container">
		<a href="javascript:window.history.back();" class="prev_page_link"><i class="fas fa-arrow-left-long"></i></a>
		<div class="common_forms_wrap">
			<div class="form_left_content">
				<div class="form_logo">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-white.svg" alt="logo" />
				</div>
				<h1><?php _e('Trete studypeak bei und erlebe deinen persönlichen Lernerfolg.', 'bricks-child'); ?></h1>
				<p><?php _e('Melde dich an, um aktiv zu lernen!', 'bricks-child'); ?></p>
			</div>
			<div class="form_right_content">
				<form method="post" class="woocommerce-LostPassword lost_reset_password" id="woocommerce-LostPassword" novalidate="novalidate">		
					<div class="form_body">
						<div class="form_header">
							<h2 class="title"><?php _e('Passwort vergessen', 'bricks-child'); ?></h2>
						</div>

						<p class="paragraph_desc"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Bitte gebe die E-Mail-Adresse deines studypeak-Kontos ein, um den Zurücksetzungsvorgang zu starten:', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

						<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
							<label for="user_login"><?php esc_html_e( 'Ihre E-Mail-Adresse', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" placeholder="Geben Sie Ihre E-Mail-Adresse ein" autocomplete="username" required aria-required="true" />
							<!-- <div class="message" id="form-message"></div> -->
						</p>

						<div class="clear"></div>

						<?php do_action( 'woocommerce_lostpassword_form' ); ?>

						<p class="woocommerce-form-row form-row">
							<input type="hidden" name="wc_reset_password" value="true" />
							<button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Passwort vergessen', 'woocommerce' ); ?>"><?php esc_html_e( 'Passwort vergessen', 'woocommerce' ); ?></button>
						</p>

						<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

					</div>
				</form>
			</div>
		</div>
	</div>
</section>
<?php
do_action( 'woocommerce_after_lost_password_form' );
