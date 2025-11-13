<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width"/>

<?php do_action( 'bricks_meta_tags' ); ?>
<?php wp_head(); ?>
</head>

<?php
do_action( 'bricks_body' );

do_action( 'bricks_before_site_wrapper' );

do_action( 'bricks_before_header' );

do_action( 'render_header' );

do_action( 'bricks_after_header' );
