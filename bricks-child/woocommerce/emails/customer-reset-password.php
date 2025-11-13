<?php
/**
 * Customer Reset Password email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-reset-password.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$user = get_user_by( 'login', $user_login ); // Retrieve the user object by login
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Hallo %s', 'woocommerce' ), esc_html( $user->first_name . ' ' . $user->last_name ) ); ?></p>

<p><?php esc_html_e( 'Du hast angefordert, dein Passwort zurückzusetzen. Klicke auf den folgenden Link, um ein neues Passwort festzulegen:', 'woocommerce' ); ?></p>

<p>
	<a class="link" href="<?php echo esc_url( add_query_arg( array( 'key' => $reset_key, 'id' => $user_id, 'login' => rawurlencode( $user_login ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ) ); ?>">
		<?php echo esc_url( add_query_arg( array( 'key' => $reset_key, 'id' => $user_id, 'login' => rawurlencode( $user_login ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) ) ); ?>
	</a>
</p>

<p><?php esc_html_e( 'Dieser Link ist für eine begrenzte Zeit gültig und kann nur einmal verwendet werden. Falls du diese Anfrage nicht gestellt hast, kannst du diese E-Mail einfach ignorieren. Dein Passwort bleibt dann unverändert.', 'woocommerce' ); ?></p>

<p><?php esc_html_e( 'Bei Fragen oder Problemen stehen wir dir gerne zur Verfügung:', 'woocommerce' ); ?></p>
<ul>
	<li><?php esc_html_e( 'E-Mail: info@studypeak.ch', 'woocommerce' ); ?></li>
	<li><?php esc_html_e( 'Telefon/WhatsApp: +41 77 253 11 00', 'woocommerce' ); ?></li>
</ul>

<p><?php esc_html_e( 'Herzliche Grüsse', 'woocommerce' ); ?></p>
<p><?php esc_html_e( 'Dein studypeak-Team', 'woocommerce' ); ?></p>

<?php

do_action( 'woocommerce_email_footer', $email );
?>
