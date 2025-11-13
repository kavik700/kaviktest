<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// do_action('woocommerce_before_customer_login_form'); ?>

<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

	<div class="u-columns col2-set" id="customer_login">

		<div class="u-column1 col-1">

		<?php endif; ?>
		<section class="common_forms_section">
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
						<div class="login_flow login_page">
							<form id="woo-login-form" class="woocommerce-form woocommerce-form-login login" method="post">
								<?php wp_nonce_field('ajax-custom-login-nonce', 'custom_login_nonce'); ?>								
								<div class="form_body">
									<div class="form_header">
										<h2 class="title"><?php _e('Einloggen', 'astra-child'); ?></h2>
										<!-- <p class="paragraph"> <?php //_e('Practice makes Master!', 'astra-child'); ?> </p> -->
									</div>
									<?php do_action('woocommerce_login_form_start'); ?>

									<div class="form-group woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<label for="username"><?php esc_html_e('E-Mail-Adresse', 'astra-child'); ?>&nbsp;<span class="required"></span></label>
										<input type="text" placeholder="<?php esc_html_e('Gib deine E-Mail-Adresse ein', 'astra-child'); ?>" class="form-control woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" require/><?php // @codingStandardsIgnoreLine 																								?>
									</div>
									
									<div class="form-group woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
										<div class="login_lost_password">
											<label for="password"><?php esc_html_e('Passwort', 'astra-child'); ?>&nbsp;<span class="required"></span></label>
											<label class="woocommerce-LostPassword lost_password">
												<b><a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e('Passwort vergessen?', 'astra-child'); ?></a></b>
											</label>
										</div>	
										
										<input placeholder="<?php esc_html_e('Gib dein Passwort ein', 'woocommerce'); ?>" class="form-control woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" require />
									</div>

									<?php //do_action('woocommerce_login_form'); ?>

									<div class="form-group form-row">
										<!-- <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
											<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php //esc_html_e( 'Remember me', 'woocommerce' ); ?>
										</label> -->
										<?php //wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
										<button type="submit" class="submit_btn woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="login" value="<?php esc_attr_e('Let’s Go!', 'woocommerce'); ?>"><?php esc_html_e('Einloggen', 'woocommerce'); ?></button>
										<div class="message" id="form-message"></div>
									</div>
								</div>
								<?php do_action('woocommerce_login_form_end'); ?>
							</form>
							<div id="twofa-verification-form" class="verification-container" style="display:none;">
								<div class="form_body">
									<div class="form_header">
										<h2 class="title"><?php _e('Zwei-Faktor-Authentifizierung', 'bricks-child'); ?></h2>
										<p class="paragraph"><?php _e('Ein Bestätigungscode wurde an deine E-Mail gesendet. Bitte gebe ihn unten ein:', 'bricks-child'); ?></p>
									</div>

									<div class="form-group">
										<label for="verification_login_code"><?php _e('Bestätigungscode:', 'astra-child'); ?></label>
										<input class="form-control" type="text" id="verification_login_code" placeholder="<?php _e('Bestätigungscode ein', 'astra-child'); ?>" name="verification_code">
                                		<div id="verification-message"></div>
									</div>
									<div class="form-group login_lost_password">
		                                <label class="lost_password"><?php _e('Code nicht erhalten?', 'bricks-child'); ?></label>
		                                <label><a href="<?php echo home_url('mein-konto') ?>"><?php _e('Code erneut senden', 'bricks-child'); ?></a></label>
		                            </div>
									<input type="hidden" id="user_id" value="">

									<div class="form-group">
										<button type="button" id="verify_2fa_btn" class="verify_btn button"><?php _e('Code überprüfen', 'astra-child'); ?></button>
									</div>
								</div>
							</div>
							<div class="other_login">
								<span>oder</span>
							</div>
							<div class="form_footer">
								<div class="woocommerce-social-login social-login-buttons">
									<?php echo do_shortcode('[miniorange_social_login apps="google,fb" theme="default"]'); ?>
								</div>
						
								<div class="woocommerce-register-link alredy-registered">
									<?php $registerurl = home_url('registriere-dich');
									echo sprintf(esc_html__('%s', 'woocommerce'), '<a href="' . $registerurl . '">' . esc_html__('Hast du noch kein studypeak-Konto? Registriere dich jetzt!', 'astra-child') . '</a>'); ?>
								</div>				
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>

		</div>

		<div class="u-column2 col-2">

			<h2><?php esc_html_e('Register', 'woocommerce'); ?></h2>

			<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>

				<?php do_action('woocommerce_register_form_start'); ?>

				<?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine 
																																																																		?>
					</p>

				<?php endif; ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" /><?php // @codingStandardsIgnoreLine 
																																																														?>
				</p>

				<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
					</p>

				<?php else : ?>

					<p><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>

				<?php endif; ?>

				<?php do_action('woocommerce_register_form'); ?>

				<p class="woocommerce-form-row form-row">
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
				</p>

				<?php do_action('woocommerce_register_form_end'); ?>

			</form>

		</div>

	</div>
<?php endif; ?>

<?php do_action('woocommerce_after_customer_login_form'); ?>