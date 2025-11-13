<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

/**
 * Class Essay_Word_Counter
 * Adds word counter functionality to essay questions
 */
class Essay_Word_Counter {
    
    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_word_counter_metabox' ) );
        add_action( 'save_post', array( $this, 'save_word_counter_setting' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }
    
    /**
     * Enqueue admin scripts for real-time metabox visibility
     * Note: The actual JavaScript is loaded via admin/assets/src/main.js
     * This method is kept for future extensibility
     */
    public function enqueue_admin_scripts( $hook ) {
        // JavaScript for real-time metabox visibility is included in the main admin bundle
        // See: admin/assets/src/essay-word-counter-admin.js (imported in main.js)
    }
    
    /**
     * Add metabox to question edit screen
     */
    public function add_word_counter_metabox() {
        add_meta_box(
            'sp_essay_word_counter_settings',
            esc_html__( 'Essay Word Counter', 'custom-quiz-types-for-multiclass' ),
            array( $this, 'render_word_counter_metabox' ),
            'sfwd-question',
            'side',
            'default'
        );
    }
    
    /**
     * Render the metabox content
     * 
     * @param WP_Post $post The post object
     */
    public function render_word_counter_metabox( $post ) {
        // Add nonce for security
        wp_nonce_field( 'sp_essay_word_counter_nonce_action', 'sp_essay_word_counter_nonce' );
        
        // Get current value
        $enable_word_counter = get_post_meta( $post->ID, '_sp_essay_enable_word_counter', true );
        $checked = ( $enable_word_counter === 'yes' ) ? 'checked' : '';
        
        // Get the question pro ID to check if it's an essay question
        $question_pro_id = get_post_meta( $post->ID, 'question_pro_id', true );
        
        if ( $question_pro_id ) {
            $question_mapper = new \WpProQuiz_Model_QuestionMapper();
            $question = $question_mapper->fetch( $question_pro_id );
            $is_essay = ( $question && $question->getAnswerType() === 'essay' );
        } else {
            $is_essay = false;
        }
        
        ?>
        <div class="sp-essay-word-counter-settings">
            <label>
                <input type="checkbox" 
                       name="sp_essay_enable_word_counter" 
                       id="sp_essay_enable_word_counter" 
                       value="yes" 
                       <?php echo $checked; ?> />
                <?php esc_html_e( 'Enable word counter', 'custom-quiz-types-for-multiclass' ); ?>
            </label>
            <p class="description">
                <?php esc_html_e( 'Display a real-time word counter for this essay question.', 'custom-quiz-types-for-multiclass' ); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Save the metabox data
     * 
     * @param int $post_id The post ID
     */
    public function save_word_counter_setting( $post_id ) {
        // Check if nonce is set
        if ( ! isset( $_POST['sp_essay_word_counter_nonce'] ) ) {
            return;
        }
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['sp_essay_word_counter_nonce'], 'sp_essay_word_counter_nonce_action' ) ) {
            return;
        }
        
        // Check if not autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Save or delete the meta
        if ( isset( $_POST['sp_essay_enable_word_counter'] ) && $_POST['sp_essay_enable_word_counter'] === 'yes' ) {
            update_post_meta( $post_id, '_sp_essay_enable_word_counter', 'yes' );
        } else {
            delete_post_meta( $post_id, '_sp_essay_enable_word_counter' );
        }
    }
    
    /**
     * Check if word counter is enabled for a question
     * 
     * @param int $question_post_id The question post ID
     * @return bool
     */
    public static function is_word_counter_enabled( $question_post_id ) {
        return get_post_meta( $question_post_id, '_sp_essay_enable_word_counter', true ) === 'yes';
    }
}

