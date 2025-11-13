<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use WpProQuiz_Model_QuestionMapper;

/**
 * Class Student_Essay_Submissions_Shortcode
 * Handles the [student_essay_submissions] shortcode
 */
class Student_Essay_Submissions_Shortcode {
    /**
     * Initialize the class and register the shortcode
     */
    public function __construct() {
        add_shortcode( 'student_essay_submissions', array( $this, 'student_essay_submissions_shortcode' ) );
        
        // Add meta box to quiz edit screen
        add_action( 'add_meta_boxes', array( $this, 'add_quiz_essay_display_meta_box' ) );
        
        // Save meta box data
        add_action( 'save_post', array( $this, 'save_quiz_essay_display_meta_box' ) );
    }
    
    /**
     * Add meta box to quiz edit screen
     */
    public function add_quiz_essay_display_meta_box() {
        add_meta_box(
            'quiz_essay_display_settings',
            __( 'Essay Display Settings', 'custom-quiz-types-for-multiclass' ),
            array( $this, 'render_quiz_essay_display_meta_box' ),
            'sfwd-quiz',
            'side',
            'default'
        );
    }
    
    /**
     * Render the meta box content
     */
    public function render_quiz_essay_display_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'quiz_essay_display_meta_box', 'quiz_essay_display_meta_box_nonce' );
        
        // Get current value
        $show_in_my_essays = get_post_meta( $post->ID, '_sp_show_essay_in_my_essays_screen', true );
        $checked = ( $show_in_my_essays === '1' ) ? 'checked' : '';
        ?>
        <div style="padding: 10px 0;">
            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                <input 
                    type="checkbox" 
                    name="show_essay_in_my_essays_screen" 
                    value="1" 
                    <?php echo $checked; ?>
                    style="margin-top: 2px;"
                />
                <span style="line-height: 1.5;">
                    <?php _e( 'Show essay question responses in My Essays screen', 'custom-quiz-types-for-multiclass' ); ?>
                </span>
            </label>
            <p class="description" style="margin: 8px 0 0 28px; color: #666; font-size: 12px;">
                <?php _e( 'When checked, student essay submissions from this quiz will be displayed in the My Essays screen.', 'custom-quiz-types-for-multiclass' ); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_quiz_essay_display_meta_box( $post_id ) {
        // Check if nonce is set
        if ( ! isset( $_POST['quiz_essay_display_meta_box_nonce'] ) ) {
            return;
        }
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['quiz_essay_display_meta_box_nonce'], 'quiz_essay_display_meta_box' ) ) {
            return;
        }
        
        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Check if this is the correct post type
        if ( get_post_type( $post_id ) !== 'sfwd-quiz' ) {
            return;
        }
        
        // Save or delete the meta value
        if ( isset( $_POST['show_essay_in_my_essays_screen'] ) && $_POST['show_essay_in_my_essays_screen'] === '1' ) {
            update_post_meta( $post_id, '_sp_show_essay_in_my_essays_screen', '1' );
        } else {
            update_post_meta( $post_id, '_sp_show_essay_in_my_essays_screen', '0' );
        }
    }

    /**
     * Shortcode to display student's essay submissions with teacher comments
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function student_essay_submissions_shortcode( $atts ) {
        // Parse shortcode attributes
        $atts = shortcode_atts( array(
            'user_id' => 0,
            'limit' => -1,
            'show_content' => 'yes',
            'show_comments' => 'yes',
        ), $atts, 'student_essay_submissions' );

        // Get current user if no user_id specified
        $user_id = intval( $atts['user_id'] );
        if ( ! $user_id ) {
            $current_user = wp_get_current_user();
            if ( ! $current_user->ID ) {
                return '<p>' . __( 'Please log in to view your essay submissions.', 'custom-quiz-types-for-multiclass' ) . '</p>';
            }
            $user_id = $current_user->ID;
        }

        // Get user's essay submissions
        $essays = get_posts( array(
            'post_type' => 'sfwd-essays',
            'post_status' => array( 'publish', 'draft', 'graded', 'not_graded' ),
            'author' => $user_id,
            'numberposts' => intval( $atts['limit'] ),
            'orderby' => 'date',
            'order' => 'DESC',
            'suppress_filters' => false
        ) );

        // Filter essays based on quiz settings
        $filtered_essays = array();
        foreach ( $essays as $essay ) {
            $quiz_post_id = get_post_meta( $essay->ID, 'quiz_post_id', true );
            
            // If no quiz is associated, include the essay by default
            if ( empty( $quiz_post_id ) ) {
                $filtered_essays[] = $essay;
                continue;
            }
            
            // Check if the quiz allows displaying essays in My Essays screen
            $show_in_my_essays = get_post_meta( $quiz_post_id, '_sp_show_essay_in_my_essays_screen', true );
            
            // Include essay only if setting is explicitly enabled
            if ( $show_in_my_essays === '1' ) {
                $filtered_essays[] = $essay;
            }
        }
        
        $essays = $filtered_essays;

        if ( empty( $essays ) ) {
            return '<p>' . __( 'No essay submissions found.', 'custom-quiz-types-for-multiclass' ) . '</p>';
        }

        // Start building the output
        ob_start();
        ?>
        <div class="student-essay-submissions">
            <?php foreach ( $essays as $essay ) : 
                $teacher_comment = get_post_meta( $essay->ID, '_essay_teacher_comment', true );
                $quiz_id = get_post_meta( $essay->ID, 'quiz_id', true );
                $quiz_post_id = get_post_meta( $essay->ID, 'quiz_post_id', true );
                $question_id = get_post_meta( $essay->ID, 'question_id', true );
                $course_id = get_post_meta( $essay->ID, 'course_id', true );
                
                // Debug: Check actual post status
                $actual_post_status = get_post_status( $essay->ID );
                
                // Get essay points data (following LearnDash's essay_grading_meta_box approach)
                $points_awarded = 0;
                $points_available = 0;
                $question = null;
                
                if ( ! empty( $quiz_id ) && ! empty( $question_id ) ) {
                    $question_mapper = new WpProQuiz_Model_QuestionMapper();
                    $question = $question_mapper->fetchById( intval( $question_id ), null );
                    
                    if ( $question && is_a( $question, 'WpProQuiz_Model_Question' ) ) {
                        $submitted_essay_data = learndash_get_submitted_essay_data( $quiz_id, $question->getId(), $essay );
                        
                        // Get points available from question
                        $points_available = $question->getPoints();
                        
                        // Get points awarded from submitted essay data
                        if ( isset( $submitted_essay_data['points_awarded'] ) ) {
                            $points_awarded = $submitted_essay_data['points_awarded'];
                        }
                    }
                }
                
                // Get question text
                $question_text = '';
                if ( $question && is_a( $question, 'WpProQuiz_Model_Question' ) ) {
                    $question_text = $question->getQuestion();
                }
                
                // Get quiz and course titles
                $quiz_title = '';
                $course_title = '';
                if ( $quiz_post_id ) {
                    $quiz_post = get_post( $quiz_post_id );
                    $quiz_title = $quiz_post ? $quiz_post->post_title : '';
                }
                if ( $course_id ) {
                    $course_post = get_post( $course_id );
                    $course_title = $course_post ? $course_post->post_title : '';
                }
            ?>
                <div class="essay-submission-item">
                    <div class="essay-header">
                        <h4 class="essay-title">
                            <a href="<?php echo get_permalink( $essay->ID ); ?>" target="_blank">
                                <?php echo esc_html( $essay->post_title ); ?>
                            </a>
                        </h4>
                        <div class="essay-meta">
                            <span class="essay-date">
                                <strong><?php _e( 'Submitted:', 'custom-quiz-types-for-multiclass' ); ?></strong> 
                                <?php echo get_the_date( 'F j, Y g:i A', $essay->ID ); ?>
                            </span>
                            <?php if ( $course_title ) : ?>
                                <span class="essay-course">
                                    <strong><?php _e( 'Course:', 'custom-quiz-types-for-multiclass' ); ?></strong> 
                                    <?php echo esc_html( $course_title ); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ( $quiz_title ) : ?>
                                <span class="essay-quiz">
                                    <strong><?php _e( 'Quiz:', 'custom-quiz-types-for-multiclass' ); ?></strong> 
                                    <?php echo esc_html( $quiz_title ); ?>
                                </span>
                            <?php endif; ?>
                            <span class="essay-status">
                                <strong><?php _e( 'Status:', 'custom-quiz-types-for-multiclass' ); ?></strong> 
                                <span class="status-<?php echo esc_attr( $actual_post_status ); ?>">
                                    <?php 
                                    $status_labels = array(
                                        'publish' => __( 'Published', 'custom-quiz-types-for-multiclass' ),
                                        'draft' => __( 'Draft', 'custom-quiz-types-for-multiclass' ),
                                        'graded' => __( 'Graded', 'custom-quiz-types-for-multiclass' ),
                                        'not_graded' => __( 'Not Graded', 'custom-quiz-types-for-multiclass' ),
                                    );
                                    $status_text = isset( $status_labels[ $actual_post_status ] ) ? $status_labels[ $actual_post_status ] : ucfirst( str_replace( '_', ' ', $actual_post_status ) );
                                    echo esc_html( $status_text );
                                    ?>
                                    <!-- DEBUG: Object status: <?php echo esc_html( $essay->post_status ); ?>, Actual status: <?php echo esc_html( $actual_post_status ); ?> -->
                                </span>
                            </span>
                            <?php if ( $points_available > 0 ) : ?>
                                <span class="essay-points">
                                    <strong><?php _e( 'Points:', 'custom-quiz-types-for-multiclass' ); ?></strong> 
                                    <?php echo esc_html( $points_awarded ); ?>/<?php echo esc_html( $points_available ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ( ! empty( $question_text ) ) : ?>
                        <div class="essay-question">
                            <h5><?php _e( 'Question:', 'custom-quiz-types-for-multiclass' ); ?></h5>
                            <div class="question-text">
                                <?php echo wp_kses_post( $question_text ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $atts['show_content'] === 'yes' && ! empty( $essay->post_content ) ) : ?>
                        <div class="essay-content">
                            <h5><?php _e( 'Your Submission:', 'custom-quiz-types-for-multiclass' ); ?></h5>
                            <div class="essay-text">
                                <?php echo wp_trim_words( wpautop( $essay->post_content ), 50, '...' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ( $atts['show_comments'] === 'yes' ) : ?>
                        <div class="teacher-feedback-section">
                            <h5><?php _e( 'Teacher\'s Feedback:', 'custom-quiz-types-for-multiclass' ); ?></h5>
                            <div class="teacher-feedback-content">
                                <?php if ( ! empty( $teacher_comment ) ) : ?>
                                    <?php echo wpautop( esc_html( $teacher_comment ) ); ?>
                                <?php else : ?>
                                    <em><?php _e( 'Ausstehend', 'custom-quiz-types-for-multiclass' ); ?></em>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <style>
        .essay_list {
            margin-top: 30px;
            width: 100%;
        }
        
        .student-essay-submissions {
            width: 100%;
            margin-top: 30px;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .essay-submission-item {
            background: #ffffff;
            border: 1px solid #e1e5e9;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }
        .essay-submission-item:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border-color: #d0d7de;
        }
        .essay-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f3f6;
            padding-bottom: 16px;
        }
        .essay-title {
            margin: 0 0 12px 0;
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
        }
        .essay-title a {
            color: #1f2328;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .essay-title a:hover {
            color: #0969da;
        }
        .essay-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            font-size: 14px;
            color: #656d76;
        }
        .essay-meta span {
            display: flex;
            align-items: flex-start;
            flex-direction: column;
            padding: 8px 12px;
            background: #f6f8fa;
            border-radius: 6px;
            border: 1px solid #d1d9e0;
        }
        .essay-meta strong {
            color: #1f2328;
            margin-right: 6px;
        }
        status-graded {
            background: #dafbe1;
            border-color: #a2e4b8;
            color: #1a7f37;
            font-weight: 500;
        }
        .status-draft, .status-not_graded {
            background: #fff8c5;
            border-color: #f0d90a;
            color: #9a6700;
            font-weight: 500;
        }
        .essay-points {
            background: #e6f3ff;
            border-color: #7cc7f7;
            color: #0969da;
            font-weight: 600;
        }
        .essay-question {
            margin: 20px 0;
            padding: 20px;
            background: #fff9e6;
            border: 1px solid #f0d90a;
            border-left: 4px solid #f0d90a;
            border-radius: 8px;
        }
        .essay-question h5 {
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 600;
            color: #9a6700;
        }
        .question-text {
            line-height: 1.6;
            color: #1f2328;
            text-align: left !important;
        }
        .question-text * {
            text-align: left !important;
        }
        .essay-content {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
        }
        .essay-content h5 {
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 600;
            color: #1f2328;
        }
        .essay-text {
            line-height: 1.6;
            color: #656d76;
        }
        .teacher-feedback-section {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f3ff 100%);
            border: 1px solid #c8e1ff;
            border-radius: 8px;
            position: relative;
        }
        .teacher-feedback-section::before {
            content: '💬';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 20px;
            opacity: 0.6;
        }
        .teacher-feedback-section h5 {
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: 600;
            color: #0969da;
        }
        .teacher-feedback-content {
            line-height: 1.6;
            color: #1f2328;
        }
        .no-feedback {
            margin-top: 20px;
            padding: 16px;
            background: #f6f8fa;
            border: 1px dashed #d1d9e0;
            border-radius: 8px;
            text-align: center;
        }
        .no-feedback em {
            color: #656d76;
            font-style: normal;
        }
        @media (max-width: 768px) {
            .essay-submission-item {
                padding: 20px;
                margin-bottom: 20px;
            }
            .essay-meta {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .essay-title {
                font-size: 18px;
            }
        }
        @media (max-width: 480px) {
            .essay-submission-item {
                padding: 16px;
                border-radius: 8px;
            }
            .essay-meta span {
                padding: 6px 10px;
                font-size: 13px;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
