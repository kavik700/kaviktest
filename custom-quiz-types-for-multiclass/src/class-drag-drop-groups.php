<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Drag_Drop_Groups {

    public function __construct() {
        add_action( 'save_post', array( $this, 'save_drag_drop_groups' ), 10, 2 );
        add_action( 'admin_footer', array( $this, 'render_drag_drop_groups_data' ) );
    }

    /**
     * Save drag drop groups data during post save
     *
     * @param int $post_id The post ID
     * @param WP_Post $post The post object
     */
    public function save_drag_drop_groups( $post_id, $post ) {
        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check if user can edit this post
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if we're on a question post type
        if ( $post->post_type !== 'sfwd-question' ) {
            return;
        }

        // Check if drag drop groups data is present
        if ( ! isset( $_POST['drag_drop_groups_data'] ) ) {
            return;
        }

        // Get groups data (validate JSON structure first)
        $groups_data = $_POST['drag_drop_groups_data'];
        
        if ( empty( $groups_data ) ) {
            // If empty, delete the meta
            delete_post_meta( $post_id, '_mc_drag_drop_groups' );
            return;
        }

        // Handle escaped JSON (remove escaped quotes)
        $groups_data = stripslashes( $groups_data );

        // Validate JSON structure before decoding
        if ( ! $this->is_valid_json( $groups_data ) ) {
            return;
        }

        // Decode JSON data
        $groups = json_decode( $groups_data, true );
        
        if ( ! is_array( $groups ) ) {
            return;
        }

        // Sanitize and validate groups data
        $sanitized_groups = array();
        foreach ( $groups as $group ) {
            if ( isset( $group['groupId'] ) && isset( $group['answerIds'] ) && is_array( $group['answerIds'] ) ) {
                $sanitized_groups[] = array(
                    'groupId' => intval( $group['groupId'] ),
                    'answerIds' => array_map( 'intval', $group['answerIds'] )
                );
            }
        }

        // Save to post meta
        update_post_meta( $post_id, '_mc_drag_drop_groups', $sanitized_groups );
    }

    /**
     * Render existing drag drop groups data as JSON for JavaScript
     */
    public function render_drag_drop_groups_data() {
        global $post;

        // Only render on question edit pages
        if ( ! $post || $post->post_type !== 'sfwd-question' ) {
            return;
        }

        // Check if user can edit this post
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return;
        }

        // Get existing groups
        $groups = get_post_meta( $post->ID, '_mc_drag_drop_groups', true );
        
        if ( ! $groups || ! is_array( $groups ) ) {
            $groups = array();
        }

        // Output the hidden input with the value during SSR
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Place the hidden input within the matrix_sort_answer div
                if ($('.matrix_sort_answer').length > 0) {
                    const hiddenInput = $('<input type="hidden" id="drag-drop-groups-data" name="drag_drop_groups_data" value="<?php echo esc_attr( json_encode( $groups ) ); ?>">');
                    $('.matrix_sort_answer').append(hiddenInput);
                }
            });
        </script>
        <?php
    }

    /**
     * Get drag drop groups for a specific post
     *
     * @param int $post_id The post ID
     * @return array Array of groups
     */
    public static function get_drag_drop_groups( $post_id ) {
        $groups = get_post_meta( $post_id, '_mc_drag_drop_groups', true );
        
        if ( ! $groups || ! is_array( $groups ) ) {
            return array();
        }

        return $groups;
    }

    /**
     * Validate JSON structure
     *
     * @param string $json_data JSON data to validate
     * @return bool True if valid, false if not
     */
    private function is_valid_json( $json_data ) {
        json_decode( $json_data );
        return json_last_error() === JSON_ERROR_NONE;
    }
} 