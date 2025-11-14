<?php 
global $wpdb;

$email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';

if ( ! empty( $email ) ) {
    $query = $wpdb->prepare(
        "SELECT ID, user_login, user_email FROM {$wpdb->users} WHERE user_email = %s",
        $email
    );

    $results = $wpdb->get_results( $query );
}


?>