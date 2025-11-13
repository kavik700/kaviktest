<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Modals {
    private $modals = ['question_modal_1', 'question_modal_2', 'question_modal_3'];

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
        add_action('save_post', [$this, 'save_metaboxes']);
        add_action('wp_ajax_load_question_popup', array( $this, 'my_ajax_handler' ));
        add_action('wp_ajax_nopriv_load_question_popup', array( $this, 'my_ajax_handler' ));

        add_action('wp_ajax_get_modal_availabilities', array( $this, 'get_modal_availabilities' ));
        add_action('wp_ajax_nopriv_get_modal_availabilities', array( $this, 'get_modal_availabilities' ));
    }

    function my_ajax_handler() {
        // Check nonce for security
        check_ajax_referer('get_question_modal', 'nonce');
    
        $modal_id = intval($_POST['modalId']);
        $question_post_id = intval($_POST['questionPostId']);
    
        $status = 'on' === get_post_meta($question_post_id, 'question_modal_' . $modal_id . '_status', true);
    
        if (!$status) {
            wp_send_json_error();
        }
    
        // Get the content from post meta
        $content = get_post_meta($question_post_id, 'question_modal_' . $modal_id . '_content', true);
    
        // Process content to embed PDFs
        $content = $this->transform_pdf_links_to_embeds($content, $question_post_id, $modal_id);
    
        // Sanitize and prepare the content
        $data = '<div class="modal">' . $content . '</div>';
    
        // Return a response
        wp_send_json_success($data);
    
        // Always die in the end
        wp_die();
    }
    
    function transform_pdf_links_to_embeds($content, $question_post_id, $modal_id) {
        // Use a regular expression to find all PDF links
        $pattern = '/<a href="([^"]+\.pdf)">([^<]+)<\/a>/i';
    
        // Replace matched links with an iframe
        $replacement = '<div data-url="$1" class="adobe-dc-view" id="adobe-dc-view_' . $question_post_id . '_' . $modal_id . '"></div>';
    
        // Apply the transformation to the content
        $transformed_content = preg_replace($pattern, $replacement, $content);
    
        return $transformed_content;
    }

    function get_modal_availabilities() {

        check_ajax_referer('get_modal_availabilities', 'nonce');

        $question_post_id_collection = $_POST['question_post_id_collection'];

        $data = array();

        foreach($question_post_id_collection as $post_id) {
            $modals = array();

            for( $i = 0; $i < 4; $i++ ) {
                $status = 'on' === get_post_meta($post_id, 'question_modal_' . $i . '_status', true);

                $modals[] = $status ? array(
                    'icon'=> $status ? get_post_meta($post_id, 'question_modal_' . $i . '_icon', true) : false,
                    'auto_start'=>'on' === get_post_meta($post_id, 'question_modal_' . $i . '_auto_start', true)
                ) : false;
            }

            $data[$post_id] = $modals;
        }
    
        // Return a response
        wp_send_json_success($data);
    
        // Always die in the end
        wp_die();
    }

    public function register_metaboxes() {
        foreach ($this->modals as $modal) {
            add_meta_box(
                "{$modal}_metabox",
                esc_html__(ucfirst($modal), 'custom-quiz-types-for-multiclass'),
                [$this, 'render_metabox'],
                'sfwd-question',
                'normal',
                'default',
                ['id' => $modal]
            );
        }
    }

    public function render_metabox($post, $metabox) {
        $id = $metabox['args']['id'];
        $status_value = get_post_meta($post->ID, "{$id}_status", true);
        $editor_content = get_post_meta($post->ID, "{$id}_content", true);
        $icon_class = get_post_meta($post->ID, "{$id}_icon", true);
        $auto_start = get_post_meta($post->ID, "{$id}_auto_start", true);

        // Nonce field for security
        wp_nonce_field("save_{$id}_metabox", "{$id}_nonce");
        ?>
        <p>
            <label for="<?php echo $id; ?>_status"><?php esc_html_e('Select On/Off:', 'custom-quiz-types-for-multiclass'); ?></label>
            <select name="<?php echo $id; ?>_status" id="<?php echo $id; ?>_status">
                <option value="off" <?php selected($status_value, 'off'); ?>><?php esc_html_e('Off', 'custom-quiz-types-for-multiclass'); ?></option>
                <option value="on" <?php selected($status_value, 'on'); ?>><?php esc_html_e('On', 'custom-quiz-types-for-multiclass'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $id; ?>_auto_start"><?php esc_html_e('Auto Start:', 'custom-quiz-types-for-multiclass'); ?></label>
            <select name="<?php echo $id; ?>_auto_start" id="<?php echo $id; ?>_status">
                <option value="off" <?php selected($auto_start, 'off'); ?>><?php esc_html_e('Off', 'custom-quiz-types-for-multiclass'); ?></option>
                <option value="on" <?php selected($auto_start, 'on'); ?>><?php esc_html_e('On', 'custom-quiz-types-for-multiclass'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $id; ?>_icon"><?php esc_html_e('Icon Class:', 'custom-quiz-types-for-multiclass'); ?></label>
            <input type="text" name="<?php echo $id; ?>_icon" id="<?php echo $id; ?>_icon" value="<?php echo esc_attr($icon_class); ?>" placeholder="<?php esc_attr_e('e.g., fas fa-star', 'custom-quiz-types-for-multiclass'); ?>" />
        </p>
        <?php
        // Rich text editor
        ?>
        <p><label for="<?php echo $id; ?>_content"><?php esc_html_e('Content:', 'custom-quiz-types-for-multiclass'); ?></label></p>
        <?php
        wp_editor($editor_content, "{$id}_content", [
            'textarea_name' => "{$id}_content",
            'media_buttons' => true,
            'teeny' => true,
            'textarea_rows' => 5,
        ]);
    }

    public function save_metaboxes($post_id) {
        // Check if our nonce is set and verify it.
        foreach ($this->modals as $modal) {
            if (
                !isset($_POST["{$modal}_nonce"]) || 
                !wp_verify_nonce($_POST["{$modal}_nonce"], "save_{$modal}_metabox")
            ) {
                continue;
            }

            // Check the user's permissions.
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Save select, content, and icon values
            if (isset($_POST["{$modal}_status"])) {
                update_post_meta($post_id, "{$modal}_status", sanitize_text_field($_POST["{$modal}_status"]));
            }

            if (isset($_POST["{$modal}_content"])) {
                update_post_meta($post_id, "{$modal}_content", $_POST["{$modal}_content"]); // Use wp_kses_post if needed
            }

            if (isset($_POST["{$modal}_icon"])) {
                update_post_meta($post_id, "{$modal}_icon", sanitize_text_field($_POST["{$modal}_icon"]));
            }

            if (isset($_POST["{$modal}_auto_start"])) {
                update_post_meta($post_id, "{$modal}_auto_start", sanitize_text_field($_POST["{$modal}_auto_start"]));
            }
        }
    }
}