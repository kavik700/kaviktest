<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Hide_Quiz_Grades {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_hide_grades_metabox'));
        add_action('save_post', array($this, 'save_hide_grades_setting'));
        add_filter('body_class', array($this, 'add_body_class'));
    }

    /**
     * Add metabox to quiz settings
     */
    public function add_hide_grades_metabox() {
        add_meta_box(
            'mc_hide_grades_metabox',           // Unique ID
            __('Hide Grades Settings', 'multiclass'),  // Box title
            array($this, 'render_hide_grades_metabox'),// Content callback
            'sfwd-quiz',                     // Post type
            'side',                        // Context
            'default'                        // Priority
        );
    }

    /**
     * Render metabox content
     */
    public function render_hide_grades_metabox($post) {
        // Add nonce for security
        wp_nonce_field('mc_hide_grades_nonce', 'mc_hide_grades_nonce');
        
        // Get saved value
        $hide_grades = get_post_meta($post->ID, 'mc_hide_grades', true);
        ?>
        <p>
            <input type="checkbox" id="hide_grades" name="hide_grades" value="1" <?php checked($hide_grades, '1'); ?>>
            <label for="hide_grades"><?php _e('Hide quiz results', 'multiclass'); ?></label>
        </p>

        <small>Note: This setting hides all quiz results displayed at the end of the quiz. It also hides results for free questions (worth 0 points) immediately after they are answered.</small>
        <?php
    }

    /**
     * Save metabox data
     */
    public function save_hide_grades_setting($post_id) {
        // Check if nonce is set
        if (!isset($_POST['mc_hide_grades_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['mc_hide_grades_nonce'], 'mc_hide_grades_nonce')) {
            return;
        }

        // If this is autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save the data
        $hide_grades = isset($_POST['hide_grades']) ? '1' : '0';
        update_post_meta($post_id, 'mc_hide_grades', $hide_grades);
    }

    /**
     * Add body class if viewing a quiz with hidden grades
     *
     * @param array $classes Array of body classes
     * @return array Modified array of body classes
     */
    public function add_body_class($classes) {
        if (is_singular('sfwd-quiz')) {
            $post_id = get_the_ID();
            $hide_grades = get_post_meta($post_id, 'mc_hide_grades', true);
            
            if ($hide_grades === '1') {
                $classes[] = 'mc-quiz-grades-hidden';
            }
        }
        return $classes;
    }
}