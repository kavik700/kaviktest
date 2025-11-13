<?php

namespace Multiclass;

defined('ABSPATH') || exit;

class Numerical_Processing {

    // Initialize the class
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox_data']);
    }

    // Add the metabox to the sfwd-question CPT
    public function add_metabox() {
        add_meta_box(
            'np_interval_ms_metabox', // Metabox ID
            esc_html__('Numerical Processing Capacity Interval (ms)', 'custom-quiz-types-for-multiclass'), // Title
            [$this, 'render_metabox'], // Callback function to render the input field
            'sfwd-question', // Post type
            'side', // Context (location on screen)
            'default' // Priority
        );
    }

    // Render the metabox content
    public function render_metabox($post) {
        // Retrieve the current value from the post meta
        $value = get_post_meta($post->ID, 'np_interval_ms', true);

        if( '' === $value ) {
            $value = 2000;
        }
        ?>
        <label for="np_interval_ms"><?php echo esc_html__('Set the interval in milliseconds:', 'custom-quiz-types-for-multiclass'); ?></label>
        <input type="number" id="np_interval_ms" name="np_interval_ms" value="<?php echo esc_attr($value); ?>" />
        <?php
    }

    // Save the metabox data when the post is saved
    public function save_metabox_data($post_id) {
        // Check if the input is set
        if (isset($_POST['np_interval_ms'])) {
            // Validate and sanitize the input
            $sanitized_value = intval($_POST['np_interval_ms']);
            // Update the post meta
            update_post_meta($post_id, 'np_interval_ms', $sanitized_value);
        }
    }
}