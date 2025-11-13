<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use WpProQuiz_Model_QuestionMapper;

/**
 * Class Essay_Teacher_Comment
 * Adds a metabox to sfwd-essays post type for teacher comments
 */
class Essay_Teacher_Comment {
    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_sfwd-essays', array( $this, 'save_meta_box' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_filter( 'the_content', array( $this, 'display_teacher_comment' ) );
    }

    /**
     * Add the meta box to sfwd-essays post type
     */
    public function add_meta_box() {
        add_meta_box(
            'essay_teacher_feedback',
            __( 'Teacher\'s Feedback', 'custom-quiz-types-for-multiclass' ),
            array( $this, 'render_meta_box' ),
            'sfwd-essays',
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
        // Add a nonce field for security
        wp_nonce_field( 'essay_teacher_feedback_nonce', 'essay_teacher_feedback_nonce_field' );

        // Retrieve current values from the custom fields
        $teacher_comment = get_post_meta( $post->ID, '_essay_teacher_comment', true );

        ?>
        <div class="essay-teacher-feedback-metabox">
            <p>
                <label for="essay_teacher_comment"><strong><?php _e( 'Comment:', 'custom-quiz-types-for-multiclass' ); ?></strong></label><br>
                <textarea id="essay_teacher_comment" name="essay_teacher_comment" rows="8" style="width: 100%; margin-top: 5px;" placeholder="<?php _e( 'Enter your feedback for the student...', 'custom-quiz-types-for-multiclass' ); ?>"><?php echo esc_textarea( $teacher_comment ); ?></textarea>
            </p>

            <p class="description" style="margin-top: 10px; font-style: italic;">
                <?php _e( 'This feedback will be displayed to the student on the frontend.', 'custom-quiz-types-for-multiclass' ); ?>
            </p>
        </div>

        <style>
        .essay-teacher-feedback-metabox {
            padding: 0;
        }
        .essay-teacher-feedback-metabox p {
            margin-bottom: 15px;
        }
        .essay-teacher-feedback-metabox label {
            display: block;
            margin-bottom: 3px;
        }
        .essay-teacher-feedback-metabox input[type="checkbox"] {
            margin-right: 5px;
        }
        .essay-teacher-feedback-metabox .description {
            color: #666;
            font-size: 12px;
            margin-bottom: 0;
        }
        </style>
        <?php
    }

    /**
     * Save the meta box data when the post is saved
     * 
     * @param int $post_id The post ID
     */
    public function save_meta_box( $post_id ) {
        // Check if our nonce is set and verify it
        if ( ! isset( $_POST['essay_teacher_feedback_nonce_field'] ) || 
             ! wp_verify_nonce( $_POST['essay_teacher_feedback_nonce_field'], 'essay_teacher_feedback_nonce' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save the teacher's comment
        if ( isset( $_POST['essay_teacher_comment'] ) ) {
            $teacher_comment = sanitize_textarea_field( $_POST['essay_teacher_comment'] );
            update_post_meta( $post_id, '_essay_teacher_comment', $teacher_comment );
        }


    }

    /**
     * Display teacher's comment on single essay posts for students
     * 
     * @param string $content The post content
     * @return string Modified content with teacher's comment
     */
    public function display_teacher_comment( $content ) {
        // Only display on single sfwd-essays posts
        if ( ! is_single() || get_post_type() !== 'sfwd-essays' ) {
            return $content;
        }


        // Get the current user and post author
        $current_user = wp_get_current_user();
        $post_author_id = get_post_field( 'post_author', get_the_ID() );

        // Only show to the essay author (student) or users who can edit essays (teachers/admins)
        if ( $current_user->ID != $post_author_id && ! current_user_can( 'edit_others_posts' ) ) {
            return $content;
        }

        $teacher_comment = get_post_meta( get_the_ID(), '_essay_teacher_comment', true );

        if ( empty( $teacher_comment ) ) {
            return $content;
        }

        // Build the teacher's comment HTML
        $comment_html = '<div class="essay-teacher-feedback-display">';
        $comment_html .= '<h3>' . __( 'Teacher\'s Feedback', 'custom-quiz-types-for-multiclass' ) . '</h3>';
        $comment_html .= '<div class="teacher-comment-content">';
        $comment_html .= wpautop( esc_html( $teacher_comment ) );
        $comment_html .= '</div>';
        
        $comment_html .= '</div>';

        return $content . $comment_html;
    }

    /**
     * Enqueue styles for the teacher's comment display
     */
    public function enqueue_styles() {
        if ( is_single() && get_post_type() === 'sfwd-essays' ) {
            $css = "
                .essay-teacher-feedback-display {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .essay-teacher-feedback-display h3 {
                    margin-top: 0;
                    color: #495057;
                    border-bottom: 2px solid #007cba;
                    padding-bottom: 10px;
                }
                .teacher-comment-content {
                    margin-top: 15px;
                    line-height: 1.6;
                }
            ";
            
            wp_add_inline_style( 'wp-block-library', $css );
        }
    }

}