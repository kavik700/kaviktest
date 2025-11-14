<?php 
global $wpdb;

$email = $_GET['email']; // example input

$query = $wpdb->prepare(
    "SELECT * FROM {$wpdb->users} WHERE user_email = %s",
    $email
);

$results = $wpdb->get_results($query);

?>