<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Metabox_Answer_Types {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox_data'], PHP_INT_MAX);
    }

    // Register the metabox
    public function add_metabox() {
        add_meta_box(
            'multiclass_question_types',        // Metabox ID
            'MultiClass Question Types',             // Title of the metabox
            [$this, 'render_metabox'],      // Callback function
            'sfwd-question',                // Post type
            'side',                         // Context
            'default'                       // Priority
        );
    }

    // Render the metabox content
    public function render_metabox($post) {
        // Add a nonce field for security
        wp_nonce_field('multiclass_question_types_nonce', 'multiclass_question_nonce');

        // Retrieve current value from the custom field, if it exists
        $selected_option = get_post_meta($post->ID, '_multiclass_question_type', true);

        // Define the radio button options
        $options = [
            'default' => 'Default Learndash',
            'organization_calendar' => 'Organization Calendar',
            'color_combination' => 'Color Combination',
            'concentration' => 'Concentration',
            'concentration_numbers' => 'Concentration Numbers',
            'concentration_numbers_short_term' => 'Concentration Numbers Short Term',
            'image_drag_drop' => 'Image Drag&Drop',
            'customer_contact' => 'Customer Contact',
            'calculator' => 'Calculator',
            'numerical_processing' => 'Numerical Processing',
            'different_perspective' => 'Different Perspective',
            'hide_answers' => 'Hide Answers',
            'pictogram' => 'Pictogram',
            'audio' => 'Audio',
        ];

        // Display the radio buttons
        foreach ($options as $value => $label) {
            echo '<label>';
            echo '<input type="radio" name="multiclass_question_type" value="' . esc_attr($value) . '" ' . checked($selected_option, $value, false) . ' />';
            echo esc_html($label);
            echo '</label><br />';
        }
    }

    // Save the metabox data
    public function save_metabox_data($post_id) {
        // Check nonce for security
        if (!isset($_POST['multiclass_question_nonce']) || !wp_verify_nonce($_POST['multiclass_question_nonce'], 'multiclass_question_types_nonce')) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (isset($_POST['post_type']) && $_POST['post_type'] === 'sfwd-question') {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }

        // Save the radio button value
        if (isset($_POST['multiclass_question_type'])) {
            update_post_meta($post_id, '_multiclass_question_type', sanitize_text_field($_POST['multiclass_question_type']));
            update_post_meta($post_id, 'question_points', sanitize_text_field(\Multiclass\Core::COLOR_COMBINATION_ITEM_COUNT)); // TODO: fix
        }
    }
}