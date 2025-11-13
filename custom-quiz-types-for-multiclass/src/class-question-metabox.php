<?php
namespace Multiclass;

defined( 'ABSPATH' ) || exit;

/**
 * Class Question_Metabox
 * Adds a metabox to sfwd-question post type for free choice textbox configuration
 */
class Question_Metabox {
    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_sfwd-question', array( $this, 'save_meta_box' ) );
    }

    /**
     * Add the meta box to sfwd-question post type
     */
    public function add_meta_box() {
        add_meta_box(
            'mc_free_choice_options',
            __( 'Free Choice Options', 'multiclass' ),
            array( $this, 'render_meta_box' ),
            'sfwd-question',
            'side',
            'default'
        );
    }

    /**
     * Render the meta box content
     * 
     * @param WP_Post $post The post object
     */
    public function render_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'mc_free_choice_nonce', 'mc_free_choice_nonce' );

        // Get saved value
        $text_type = get_post_meta( $post->ID, 'mc_fc_text_type', true );
        
        // Default to single line if not set
        if ( empty( $text_type ) ) {
            $text_type = 'single';
        }

        ?>
        <div class="mc-metabox-wrapper">
            <p><strong><?php _e( 'Text Input Type:', 'multiclass' ); ?></strong></p>
            
            <label class="mc-radio-label">
                <input type="radio" 
                       name="mc_fc_text_type" 
                       value="single" 
                       <?php checked( $text_type, 'single' ); ?>>
                <?php _e( 'Single line', 'multiclass' ); ?>
            </label>
            
            <br>
            
            <label class="mc-radio-label">
                <input type="radio" 
                       name="mc_fc_text_type" 
                       value="multi" 
                       <?php checked( $text_type, 'multi' ); ?>>
                <?php _e( 'Multi line', 'multiclass' ); ?>
            </label>
        </div>

        <style>
            .mc-metabox-wrapper {
                padding: 10px;
            }
            .mc-radio-label {
                display: inline-block;
                margin: 5px 0;
            }
        </style>
        <?php
    }

    /**
     * Save the meta box data
     * 
     * @param int $post_id The post ID
     */
    public function save_meta_box( $post_id ) {
        // Check if nonce is set and valid
        if ( !isset( $_POST['mc_free_choice_nonce'] ) || 
             !wp_verify_nonce( $_POST['mc_free_choice_nonce'], 'mc_free_choice_nonce' ) ) {
            return;
        }

        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save the text type
        if ( isset( $_POST['mc_fc_text_type'] ) ) {
            $text_type = sanitize_text_field( $_POST['mc_fc_text_type'] );
            update_post_meta( $post_id, 'mc_fc_text_type', $text_type );
        }
    }
}