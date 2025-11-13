<?php
/*
Template Name: Registration Template
*/
get_header();
?>
<section class="common_forms_section">
    <div class="brxe-container">
        <a href="javascript:window.history.back();" class="prev_page_link"><i class="fas fa-arrow-left-long"></i></a>
        <div class="common_forms_wrap">
            <div class="form_left_content">
                <div class="form_logo">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-white.svg" alt="logo" />
                </div>
                <h1><?php _e('Trete studypeak bei und erlebe deinen persönlichen Lernerfolg.', 'astra-child'); ?></h1>
                <p><?php _e('Melde dich an, um aktiv zu lernen!', 'astra-child'); ?></p>
            </div>
            <div class="form_right_content">
                <div class="login_flow">                    
                    <form id="custom-registration-form" method="post" action="">
                        <div class="form_body">

                            <?php wp_nonce_field('ajax-custom-registration-nonce', 'custom_registration_nonce'); ?>
                            <div class="form_header">
                                <h2 class="title"><?php _e('Melde dich jetzt an!', 'astra-child'); ?></h2>                        
                            </div>

                            <div class="form-group">
                                <label for="email"> <?php _e('Deine E-Mail-Adresse', 'astra-child'); ?> </label>
                                <input class="form-control" type="email" id="email" placeholder="<?php _e('Gib deine E-Mail-Adresse ein', 'astra-child'); ?>" name="email" >
                                <div id="message" class="message"></div>
                            </div>
                            <div class="form-group" id="billing_phone_register_page">
                                <label for="billing_phone"> <?php _e('Deine Telefonnummer', 'astra-child'); ?> </label>
                                <input type="text" id="billing_phone" placeholder="<?php _e('Gib deine Telefonnummer ein', 'astra-child'); ?>" name="billing_phone" >
                            </div>
                            <div class="form-group">
                                <label for="password"> <?php _e('Dein Passwort', 'astra-child'); ?> </label>
                                <span class="password-input">
                                    <input class="form-control woocommerce-Input woocommerce-Input--text input-text" type="password" id="password" placeholder="<?php _e('Gib dein Passwort ein', 'astra-child'); ?>" name="password" autocomplete="new-password" >
                                </span>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"> <?php _e('Dein Passwort erneut eingeben', 'astra-child'); ?> </label>
                                <span class="password-input">
                                    <input class="form-control woocommerce-Input woocommerce-Input--text input-text" type="password" placeholder="<?php _e('Gib dein Passwort erneut ein', 'astra-child'); ?>" id="confirm_password" name="confirm_password" autocomplete="confirm-password" >
                                </span>
                            </div>
                            <div class="form-group condition group_term_condition">
                                <label for="term_condition"> 
                                    <p><b>Deine Rechte:</b> Ich habe die Allgemeinen <a href="<?php echo home_url('agbs'); ?>" target="_blank">Geschäftsbedingungen</a> und die <a href="<?php echo home_url('datenschutz'); ?>" target="_blank">Datenschutzrichtlinie</a> gelesen.</p>
                                </label>
                                <label class="switch">
                                    <input type="checkbox" id="accept_term_condition" name="accept_term_condition">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group condition">
                                <label for="want_mails">
                                    <p><b><?php _e('E-Mail von uns?', 'astra-child'); ?></b> <?php _e('Ich möchte Tipps von studypeak und Informationen zu aktuellen Produkten per E-Mail erhalten.', 'astra-child'); ?></p>
                                </label>

                                <label class="switch">
                                    <input type="checkbox" id="mail_from_us" name="accept_term_condition">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="form-group">
                                <button class="submit_btn" type="submit"> <?php _e('Melde dich jetzt an!', 'astra-child'); ?> </button>
                            </div>
                            <div class="message" id="form-message"></div>
                        </div>
                    </form>
                    
                    <div class="verification-container" style="display: none;">
                        <div class="form_body">
                            <div id="message"></div>
                            <div class="form_header">
                                <h2 class="title"><?php _e('Zwei-Faktor-Authentifizierung', 'astra-child'); ?></h2>
                                <p class="paragraph"><?php _e('Ein Bestätigungscode wurde an deine E-Mail gesendet. Bitte gebe ihn unten ein:', 'astra-child'); ?></p>
                            </div>
                            <div class="form-group">
                                <label for="verification_code"><?php _e('Bestätigungscode:', 'astra-child'); ?></label>
                                <input class="form-control" type="text" id="verification_code" placeholder="<?php _e('Bestätigungscode ein', 'astra-child'); ?>" name="verification_code">
                                <div id="verification-message"></div>
                            </div>
                            <div class="form-group login_lost_password">
                                <label class="lost_password"><?php _e('Code nicht erhalten?', 'astra-child'); ?></label>
                                <label><a href="<?php echo home_url('mein-konto') ?>"><?php _e('Code erneut senden', 'astra-child'); ?></a></label>
                            </div>
                            <div class="form-group">
                                <button class="verify_btn" type="button"><?php _e('Code überprüfen', 'astra-child'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="other_login">
                        <span>oder</span>
                    </div>
                    <div class="form_footer">
                        <div class="social-login-buttons social-login-buttons">
                            <?php echo do_shortcode('[miniorange_social_login apps="google,fb" theme="default"]'); ?>
                        </div>

                        <div class="alredy-registered">
                            <a href="<?php echo home_url('mein-konto') ?>"> <?php _e('Hast du bereits ein studypeak-Konto? Logge dich jetzt ein!', 'astra-child'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>