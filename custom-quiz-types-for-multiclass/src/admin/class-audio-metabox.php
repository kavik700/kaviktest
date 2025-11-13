<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

/**
 * Class Audio_Metabox
 * Adds a metabox to sfwd-question post type for audio configuration
 */
class Audio_Metabox {
    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_sfwd-question', array( $this, 'save_meta_box' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media_uploader' ) );
    }

    /**
     * Enqueue WordPress media uploader
     */
    public function enqueue_media_uploader( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        global $post;
        if ( $post && $post->post_type === 'sfwd-question' ) {
            wp_enqueue_media();
            wp_enqueue_script(
                'mc-audio-metabox',
                CUSTOM_QUIZ_URL . 'admin/assets/build/main.js',
                ['jquery'],
                MC_CUSTOM_QUIZ_TYPES_VERSION,
                true
            );
            
            wp_localize_script('mc-audio-metabox', 'mcAudioMetabox', [
                'i18n' => [
                    'chooseAudio' => __('Choose Audio File', 'custom-quiz-types-for-multiclass'),
                    'useAudio' => __('Use this audio', 'custom-quiz-types-for-multiclass'),
                    'noAudioSelected' => __('No audio file selected', 'custom-quiz-types-for-multiclass'),
                ]
            ]);
        }
    }

    /**
     * Add the meta box to sfwd-question post type
     */
    public function add_meta_box() {
        add_meta_box(
            'mc_audio_options',
            __( 'Audio Options', 'custom-quiz-types-for-multiclass' ),
            array( $this, 'render_meta_box' ),
            'sfwd-question',
            'normal',
            'high'
        );
    }

    /**
     * Render the meta box content
     * 
     * @param WP_Post $post The post object
     */
    public function render_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'mc_audio_nonce', 'mc_audio_nonce' );

        // Get saved values
        $audio_url = get_post_meta( $post->ID, '_mc_audio_url', true );
        $listening_limit = get_post_meta( $post->ID, '_mc_audio_listening_limit', true );
        
        // Default to 0 (unlimited) if not set
        if ( empty( $listening_limit ) ) {
            $listening_limit = 0;
        }

        ?>
        <div class="mc-audio-metabox-wrapper">
            <p><strong><?php _e( 'Audio File:', 'custom-quiz-types-for-multiclass' ); ?></strong></p>
            
            <input type="hidden" 
                   id="mc_audio_url" 
                   name="mc_audio_url" 
                   value="<?php echo esc_attr( $audio_url ); ?>">
            
            <div id="mc_audio_preview" style="margin-bottom: 10px;">
                <?php if ( $audio_url ) : ?>
                    <div style="padding: 10px; background: #f0f0f1; border-radius: 4px;">
                        <audio controls style="width: 100%; margin-bottom: 5px;">
                            <source src="<?php echo esc_url( $audio_url ); ?>" type="audio/mpeg">
                        </audio>
                        <div style="font-size: 11px; color: #666; word-break: break-all;">
                            <?php echo esc_html( basename( $audio_url ) ); ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div style="padding: 10px; background: #f0f0f1; border-radius: 4px; color: #666; font-style: italic;">
                        <?php _e( 'No audio file selected', 'custom-quiz-types-for-multiclass' ); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" 
                    class="button button-secondary" 
                    id="mc_audio_upload_button">
                <?php _e( 'Choose Audio File', 'custom-quiz-types-for-multiclass' ); ?>
            </button>
            
            <button type="button" 
                    class="button button-secondary" 
                    id="mc_audio_remove_button"
                    <?php echo empty( $audio_url ) ? 'style="display:none;"' : ''; ?>>
                <?php _e( 'Remove Audio', 'custom-quiz-types-for-multiclass' ); ?>
            </button>
            
            <hr style="margin: 15px 0;">
            
            <p><strong><?php _e( 'Listening Limit:', 'custom-quiz-types-for-multiclass' ); ?></strong></p>
            
            <label style="display: block; margin-bottom: 10px;">
                <input type="number" 
                       name="mc_audio_listening_limit" 
                       id="mc_audio_listening_limit"
                       value="<?php echo esc_attr( $listening_limit ); ?>" 
                       min="0"
                       step="1"
                       style="width: 100%;">
                <span style="font-size: 11px; color: #666; display: block; margin-top: 3px;">
                    <?php _e( 'Number of times a student can listen to this audio. Set to 0 for unlimited.', 'custom-quiz-types-for-multiclass' ); ?>
                </span>
            </label>
        </div>

        <style>
            .mc-audio-metabox-wrapper {
                padding: 10px;
            }
            #mc_audio_upload_button {
                width: 100%;
                margin-bottom: 5px;
            }
            #mc_audio_remove_button {
                width: 100%;
            }
            #mc_audio_options {
                display: none;
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
        if ( !isset( $_POST['mc_audio_nonce'] ) || 
             !wp_verify_nonce( $_POST['mc_audio_nonce'], 'mc_audio_nonce' ) ) {
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

        // Save the audio URL
        if ( isset( $_POST['mc_audio_url'] ) ) {
            $audio_url = esc_url_raw( $_POST['mc_audio_url'] );
            update_post_meta( $post_id, '_mc_audio_url', $audio_url );
        }

        // Save the listening limit
        if ( isset( $_POST['mc_audio_listening_limit'] ) ) {
            $listening_limit = absint( $_POST['mc_audio_listening_limit'] );
            update_post_meta( $post_id, '_mc_audio_listening_limit', $listening_limit );
        }
    }
}

