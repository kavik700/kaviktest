<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Pictogram_Images {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_pictogram_images_metabox']);
        add_action('save_post', [$this, 'save_pictogram_images'], 10, 1);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media_uploader']);
    }

    /**
     * Register the pictogram images metabox
     */
    public function add_pictogram_images_metabox() {
        add_meta_box(
            'multiclass_pictogram_images',
            'Pictogram Images',
            [$this, 'render_pictogram_images_metabox'],
            'sfwd-question',
            'normal',
            'high'
        );
    }

    /**
     * Enqueue WordPress media uploader scripts
     */
    public function enqueue_media_uploader($hook) {
        global $post;

        // Only load on question edit page
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        if (!$post || $post->post_type !== 'sfwd-question') {
            return;
        }

        // Enqueue WordPress media uploader
        wp_enqueue_media();

        // Enqueue jQuery UI Sortable
        wp_enqueue_script('jquery-ui-sortable');

        // Enqueue custom script for handling the media uploader
        wp_enqueue_script(
            'multiclass-pictogram-images',
            CUSTOM_QUIZ_URL . 'assets/js/pictogram-images.js',
            ['jquery', 'jquery-ui-sortable'],
            MC_CUSTOM_QUIZ_TYPES_VERSION,
            true
        );

        // Enqueue custom styles
        wp_enqueue_style(
            'multiclass-pictogram-images',
            CUSTOM_QUIZ_URL . 'assets/css/pictogram-images.css',
            [],
            MC_CUSTOM_QUIZ_TYPES_VERSION
        );

        // Pass data to JavaScript
        wp_localize_script('multiclass-pictogram-images', 'pictogramData', [
            'mediaTitle' => __('Select Pictogram Images', 'custom-quiz-types-for-multiclass'),
            'mediaButton' => __('Use Images', 'custom-quiz-types-for-multiclass'),
            'removeImage' => __('Remove', 'custom-quiz-types-for-multiclass'),
        ]);
    }

    /**
     * Render the pictogram images metabox
     */
    public function render_pictogram_images_metabox($post) {
        // Add nonce for security
        wp_nonce_field('multiclass_pictogram_images_nonce', 'pictogram_images_nonce');

        // Get the current question type
        $question_type = get_post_meta($post->ID, '_multiclass_question_type', true);

        // Only show if pictogram is selected
        if ($question_type !== 'pictogram') {
            echo '<p style="color: #666; font-style: italic;">';
            echo __('This metabox is only available when "Pictogram" question type is selected.', 'custom-quiz-types-for-multiclass');
            echo '</p>';
            return;
        }

        // Get existing images data
        $pictogram_images_data = get_post_meta($post->ID, '_multiclass_pictogram_images_data', true);
        if (!is_array($pictogram_images_data)) {
            $pictogram_images_data = [];
        }

        ?>
        <div class="multiclass-pictogram-images-container">
            <div class="pictogram-images-wrapper">
                <div id="pictogram-images-list" class="pictogram-images-list">
                    <?php foreach ($pictogram_images_data as $index => $image_data): ?>
                        <?php $this->render_image_item($image_data, $index); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pictogram-images-actions">
                <button type="button" class="button button-primary" id="add-pictogram-image">
                    <span class="dashicons dashicons-images-alt2" style="margin-top: 3px;"></span>
                    <?php _e('Add Images', 'custom-quiz-types-for-multiclass'); ?>
                </button>
                <p class="description">
                    <?php _e('Add images and select Yes/No for each. The answer table will be generated automatically.', 'custom-quiz-types-for-multiclass'); ?>
                </p>
            </div>

            <!-- Hidden input to store image data (IDs and yes/no values) -->
            <input type="hidden" name="multiclass_pictogram_images_data" id="pictogram-images-data" value="<?php echo esc_attr(json_encode(array_values($pictogram_images_data))); ?>" />
        </div>
        <?php
    }

    /**
     * Render a single image item
     */
    private function render_image_item($image_data, $index) {
        // Handle both old format (just ID) and new format (array with id and answer)
        if (is_array($image_data)) {
            $image_id = isset($image_data['id']) ? $image_data['id'] : 0;
            $answer = isset($image_data['answer']) ? $image_data['answer'] : '';
        } else {
            $image_id = $image_data;
            $answer = '';
        }

        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        $image_title = get_the_title($image_id);

        if (!$image_url) {
            return;
        }

        $unique_id = 'pictogram_' . $image_id . '_' . $index;
        ?>
        <div class="pictogram-image-item" data-image-id="<?php echo esc_attr($image_id); ?>" data-answer="<?php echo esc_attr($answer); ?>">
            <div class="pictogram-drag-handle" title="<?php esc_attr_e('Drag to reorder', 'custom-quiz-types-for-multiclass'); ?>">
                <span class="dashicons dashicons-menu"></span>
            </div>
            <div class="pictogram-image-preview">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_title); ?>" />
                <div class="pictogram-image-overlay">
                    <button type="button" class="button pictogram-remove-image" data-image-id="<?php echo esc_attr($image_id); ?>">
                        <span class="dashicons dashicons-no"></span>
                        <?php _e('Remove', 'custom-quiz-types-for-multiclass'); ?>
                    </button>
                </div>
            </div>
            <div class="pictogram-image-info">
                <span class="pictogram-image-title"><?php echo esc_html($image_title); ?></span>
                <div class="pictogram-answer-selection">
                    <label class="pictogram-answer-label">
                        <input type="radio" name="answer_<?php echo esc_attr($unique_id); ?>" value="yes" class="pictogram-answer-radio" <?php checked($answer, 'yes'); ?> />
                        <span class="answer-option answer-yes"><?php _e('Yes', 'custom-quiz-types-for-multiclass'); ?></span>
                    </label>
                    <label class="pictogram-answer-label">
                        <input type="radio" name="answer_<?php echo esc_attr($unique_id); ?>" value="no" class="pictogram-answer-radio" <?php checked($answer, 'no'); ?> />
                        <span class="answer-option answer-no"><?php _e('No', 'custom-quiz-types-for-multiclass'); ?></span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save pictogram images
     */
    public function save_pictogram_images($post_id) {
        // Check nonce
        if (!isset($_POST['pictogram_images_nonce']) || !wp_verify_nonce($_POST['pictogram_images_nonce'], 'multiclass_pictogram_images_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check post type
        if (get_post_type($post_id) !== 'sfwd-question') {
            return;
        }

        // Save images data (with yes/no answers)
        if (isset($_POST['multiclass_pictogram_images_data'])) {
            // WordPress adds slashes to POST data - remove them properly
            $images_json = stripslashes($_POST['multiclass_pictogram_images_data']);
            $images_data = json_decode($images_json, true);

            // Validate and sanitize
            $sanitized_data = [];
            if (is_array($images_data)) {
                foreach ($images_data as $image_item) {
                    if (is_array($image_item) && isset($image_item['id'])) {
                        $image_id = absint($image_item['id']);
                        if ($image_id && wp_attachment_is_image($image_id)) {
                            $answer = isset($image_item['answer']) ? $image_item['answer'] : '';
                            $answer = in_array($answer, ['yes', 'no'], true) ? $answer : '';
                            
                            $sanitized_data[] = [
                                'id' => $image_id,
                                'answer' => $answer
                            ];
                        }
                    }
                }
            }

            update_post_meta($post_id, '_multiclass_pictogram_images_data', $sanitized_data);
        } else {
            // If no images submitted, clear the meta
            delete_post_meta($post_id, '_multiclass_pictogram_images_data');
        }
    }

    /**
     * Get pictogram images data for a question
     */
    public static function get_question_images($question_id) {
        $images_data = get_post_meta($question_id, '_multiclass_pictogram_images_data', true);
        return is_array($images_data) ? $images_data : [];
    }

    /**
     * Get image URLs with answers for a question
     */
    public static function get_question_image_urls($question_id, $size = 'medium') {
        $images_data = self::get_question_images($question_id);
        $image_urls = [];

        foreach ($images_data as $image_item) {
            $image_id = is_array($image_item) ? $image_item['id'] : $image_item;
            $answer = is_array($image_item) && isset($image_item['answer']) ? $image_item['answer'] : '';
            
            $url = wp_get_attachment_image_url($image_id, $size);
            if ($url) {
                $image_urls[] = [
                    'id' => $image_id,
                    'url' => $url,
                    'title' => get_the_title($image_id),
                    'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                    'answer' => $answer,
                ];
            }
        }

        return $image_urls;
    }
}
