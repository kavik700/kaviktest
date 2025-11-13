<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Customer_Contact {
    public function __construct() {
        // Hook into the 'add_meta_boxes' action
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        // Hook into the 'save_post' action
        add_action('save_post', [$this, 'save_meta_box_data']);
    }

    public function add_meta_box() {
        // Add a meta box to the sfwd-question post type
        add_meta_box(
            'speech_bubble_coordinates', // ID
            esc_html__('Speech Bubble Coordinates', 'custom-quiz-types-for-multiclass'), // Title
            [$this, 'render_meta_box_content'], // Callback
            'sfwd-question', // Post type
            'normal', // Context
            'high' // Priority
        );
    }

    public function render_meta_box_content($post) {
        // Add a nonce field for security
        wp_nonce_field('save_speech_bubble_data', 'speech_bubble_meta_box_nonce');

        // Retrieve the current values (if they exist)
        $coordinates = get_post_meta($post->ID, '_speech_bubble_coordinates', true);
        $coordinates = json_decode($coordinates, true);

        $x = isset($coordinates['x']) ? esc_attr($coordinates['x']) : '';
        $y = isset($coordinates['y']) ? esc_attr($coordinates['y']) : '';
        $width = isset($coordinates['width']) ? esc_attr($coordinates['width']) : '';
        $height = isset($coordinates['height']) ? esc_attr($coordinates['height']) : '';
        $position = isset($coordinates['position']) ? esc_attr($coordinates['position']) : 'left';

        // Output the fields
        echo '<label for="speech_bubble_x">' . esc_html__('X Coordinate', 'custom-quiz-types-for-multiclass') . '</label>';
        echo '<input type="text" id="speech_bubble_x" name="speech_bubble_x" value="' . esc_attr($x) . '" size="25" />';
        echo '<br /><br />';

        echo '<label for="speech_bubble_y">' . esc_html__('Y Coordinate', 'custom-quiz-types-for-multiclass') . '</label>';
        echo '<input type="text" id="speech_bubble_y" name="speech_bubble_y" value="' . esc_attr($y) . '" size="25" />';
        echo '<br /><br />';

        echo '<label for="speech_bubble_width">' . esc_html__('Width', 'custom-quiz-types-for-multiclass') . '</label>';
        echo '<input type="text" id="speech_bubble_width" name="speech_bubble_width" value="' . esc_attr($width) . '" size="25" />';
        echo '<br /><br />';

        echo '<label for="speech_bubble_height">' . esc_html__('Height', 'custom-quiz-types-for-multiclass') . '</label>';
        echo '<input type="text" id="speech_bubble_height" name="speech_bubble_height" value="' . esc_attr($height) . '" size="25" />';
        echo '<br /><br />';

        // New selectbox for speech bubble position
        echo '<label for="speech_bubble_position">' . esc_html__('Speech Bubble Position', 'custom-quiz-types-for-multiclass') . '</label>';
        echo '<select id="speech_bubble_position" name="speech_bubble_position">';
        echo '<option value="left"' . selected($position, 'left', false) . '>' . esc_html__('Left', 'custom-quiz-types-for-multiclass') . '</option>';
        echo '<option value="right"' . selected($position, 'right', false) . '>' . esc_html__('Right', 'custom-quiz-types-for-multiclass') . '</option>';
        echo '</select>';
    }

    public function save_meta_box_data($post_id) {
        // Check if our nonce is set and valid
        if (!isset($_POST['speech_bubble_meta_box_nonce']) || !wp_verify_nonce($_POST['speech_bubble_meta_box_nonce'], 'save_speech_bubble_data')) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if it's an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Sanitize and prepare the data
        $x = isset($_POST['speech_bubble_x']) ? sanitize_text_field($_POST['speech_bubble_x']) : '';
        $y = isset($_POST['speech_bubble_y']) ? sanitize_text_field($_POST['speech_bubble_y']) : '';
        $width = isset($_POST['speech_bubble_width']) ? sanitize_text_field($_POST['speech_bubble_width']) : '';
        $height = isset($_POST['speech_bubble_height']) ? sanitize_text_field($_POST['speech_bubble_height']) : '';
        $position = isset($_POST['speech_bubble_position']) ? sanitize_text_field($_POST['speech_bubble_position']) : 'left';

        // Combine data into a single JSON object
        $coordinates = json_encode([
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
            'position' => $position
        ]);

        // Save the JSON object as a single meta field
        update_post_meta($post_id, '_speech_bubble_coordinates', $coordinates);

        $mc_incorrect_messages = array();
        foreach( $_POST['answerData'] as $pos => $args ) {
            $mc_incorrect_messages[$pos] = sanitize_text_field( $args['incorrectMessage'] );
        }

        update_post_meta($post_id, '_mc_cc_incorrect_answers', $mc_incorrect_messages);
    }
}