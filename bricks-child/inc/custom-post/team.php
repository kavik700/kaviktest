<?php

// Function to register the 'team' custom post type
function create_team_post_type() {
    $labels = array(
        'name'               => _x('Team', 'post type general name', 'bricks-child'),
        'singular_name'      => _x('Team', 'post type singular name', 'bricks-child'),
        'menu_name'          => _x('Team', 'admin menu', 'bricks-child'),
        'name_admin_bar'     => _x('Team', 'add new on admin bar', 'bricks-child'),
        'add_new'            => _x('Add New', 'team', 'bricks-child'),
        'add_new_item'       => __('Add New Team', 'bricks-child'),
        'new_item'           => __('New Team', 'bricks-child'),
        'edit_item'          => __('Edit Team', 'bricks-child'),
        'view_item'          => __('View Team', 'bricks-child'),
        'all_items'          => __('All Team', 'bricks-child'),
        'search_items'       => __('Search Team', 'bricks-child'),
        'parent_item_colon'  => __('Parent Team:', 'bricks-child'),
        'not_found'          => __('No team member found.', 'bricks-child'),
        'not_found_in_trash' => __('No team member found in Trash.', 'bricks-child'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => array('slug' => 'team'),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        // 'supports'           => array('title', 'editor', 'thumbnail'),
        'supports'          => array('title','editor','author','thumbnail','revisions','excerpt','comments','page-attributes'),
        'menu_icon'          => 'dashicons-groups', // Icon for the admin menu
    );

    register_post_type('team', $args);
}

// Hook into the 'init' action to register the custom post type
add_action('init', 'create_team_post_type');

