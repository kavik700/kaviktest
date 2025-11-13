<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lost-password-confirmation.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

// wc_print_notice( esc_html__( 'Password reset email has been sent.', 'woocommerce' ) );
?>

<section class="common_forms_section reset_password_form_sec">
	<div class="brxe-container">
		<a href="<?php echo home_url(); ?>" class="prev_page_link"><i class="fas fa-arrow-left-long"></i></a>
		<div class="common_forms_wrap">
			<div class="form_left_content">
				<div class="form_logo">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-white.svg" alt="logo" />
				</div>
				<h1><?php _e('Treten Sie StudyPeak bei und erleben Sie Ihren persönlichen Lernerfolg ', 'bricks-child'); ?></h1>
				<p><?php _e('Melden Sie sich an, um aktiv zu lernen!', 'bricks-child'); ?></p>
			</div>
			<div class="form_right_content">
				<form method="post" class="woocommerce-LostPassword lost_reset_password" id="woocommerce-LostPassword" novalidate="novalidate">		
					<div class="form_body">
						<div class="form_header">
							<h2 class="title"><?php _e('Passwort vergessen', 'bricks-child'); ?></h2>
						</div>
						<p class="paragraph_desc forgot_success"><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Wir haben dir eine E-Mail mit einem Link geschickt, über den du dein Passwort zurücksetzen kannst.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>
