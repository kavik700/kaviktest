<?php 
global $wpdb;

$email = $_GET['email']; // user-controlled input (danger 💣)

$query = "SELECT * FROM {$wpdb->users} WHERE user_email = '$email'";

$results = $wpdb->get_results($query);


?>