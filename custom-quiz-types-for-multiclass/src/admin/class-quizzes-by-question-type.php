<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Quizzes_By_Question_Type {
    
    /**
     * MultiClass question types
     */
    private $multiclass_question_types = [
        'default' => 'Default Learndash',
        'organization_calendar' => 'Organization Calendar',
        'color_combination' => 'Color Combination',
        'concentration' => 'Concentration',
        'concentration_numbers' => 'Concentration Numbers',
        'concentration_numbers_short_term' => 'Concentration Numbers Short Term',
        'image_drag_drop' => 'Image Drag&Drop',
        'customer_contact' => 'Customer Contact',
        'calculator' => 'Calculator',
        'numerical_processing' => 'Numerical Processing',
        'different_perspective' => 'Different Perspective',
        'hide_answers' => 'Hide Answers',
        'pictogram' => 'Pictogram',
        'audio' => 'Audio',
    ];

    /**
     * LearnDash answer types
     */
    private $learndash_answer_types = [
        'single' => 'Single choice',
        'multiple' => 'Multiple choice',
        'free_answer' => '"Free" choice',
        'sort_answer' => '"Sorting" choice',
        'matrix_sort_answer' => '"Matrix Sorting" choice',
        'cloze_answer' => 'Fill in the blank',
        'assessment_answer' => 'Assessment',
        'essay' => 'Essay / Open Answer',
    ];

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
    }

    /**
     * Add admin menu under LearnDash LMS
     */
    public function add_admin_menu() {
        add_submenu_page(
            'learndash-lms',
            __( 'Quizzes by Question Type', 'custom-quiz-types-for-multiclass' ),
            __( 'Quizzes by Question Type', 'custom-quiz-types-for-multiclass' ),
            'manage_options',
            'quizzes-by-question-type',
            array( $this, 'render_admin_page' )
        );
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles( $hook ) {
        if ( 'learndash-lms_page_quizzes-by-question-type' !== $hook ) {
            return;
        }

        // Add inline styles
        wp_add_inline_style( 'wp-admin', '
            .mc-quiz-type-section {
                margin-bottom: 30px;
            }
            .mc-quiz-type-section h2 {
                background: #f0f0f1;
                padding: 15px;
                margin: 0 0 15px 0;
                border-left: 4px solid #2271b1;
            }
            .mc-quiz-type-group {
                margin-bottom: 25px;
                background: #fff;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
            }
            .mc-quiz-type-group h3 {
                background: #f9f9f9;
                margin: 0;
                padding: 12px 15px;
                border-bottom: 1px solid #c3c4c7;
                font-size: 14px;
            }
            .mc-quiz-type-group .quiz-list {
                padding: 10px 15px;
            }
            .mc-quiz-type-group .quiz-list ul {
                margin: 0;
                padding-left: 20px;
            }
            .mc-quiz-type-group .quiz-list li {
                margin-bottom: 8px;
            }
            .mc-quiz-type-group .quiz-list a {
                text-decoration: none;
                font-weight: 500;
            }
            .mc-quiz-type-group .no-quizzes {
                padding: 10px 15px;
                color: #666;
                font-style: italic;
            }
            .mc-question-count {
                color: #666;
                font-weight: normal;
                font-size: 13px;
            }
        ' );
    }

    /**
     * Render the admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <!-- MultiClass Question Types Section -->
            <div class="mc-quiz-type-section">
                <h2><?php _e( 'MultiClass Question Types', 'custom-quiz-types-for-multiclass' ); ?></h2>
                <?php $this->render_multiclass_question_types(); ?>
            </div>

            <!-- LearnDash Answer Types Section -->
            <div class="mc-quiz-type-section">
                <h2><?php _e( 'LearnDash Answer Types', 'custom-quiz-types-for-multiclass' ); ?></h2>
                <?php $this->render_learndash_answer_types(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render MultiClass question types groups
     */
    private function render_multiclass_question_types() {
        foreach ( $this->multiclass_question_types as $type_value => $type_label ) {
            $quizzes = $this->get_quizzes_by_multiclass_type( $type_value );
            $this->render_quiz_group( $type_label, $quizzes );
        }
    }

    /**
     * Render LearnDash answer types groups
     */
    private function render_learndash_answer_types() {
        foreach ( $this->learndash_answer_types as $type_value => $type_label ) {
            $quizzes = $this->get_quizzes_by_learndash_type( $type_value );
            $this->render_quiz_group( $type_label, $quizzes );
        }
    }

    /**
     * Render a quiz group
     */
    private function render_quiz_group( $type_label, $quizzes ) {
        ?>
        <div class="mc-quiz-type-group">
            <h3>
                <?php echo esc_html( $type_label ); ?>
                <span class="mc-question-count">
                    (<?php echo count( $quizzes ); ?> <?php echo _n( 'quiz', 'quizzes', count( $quizzes ), 'custom-quiz-types-for-multiclass' ); ?>)
                </span>
            </h3>
            <div class="quiz-list">
                <?php if ( ! empty( $quizzes ) ) : ?>
                    <ul>
                        <?php foreach ( $quizzes as $quiz ) : ?>
                            <li>
                                <a href="<?php echo esc_url( get_edit_post_link( $quiz['quiz_id'] ) ); ?>" target="_blank">
                                    <?php echo esc_html( get_the_title( $quiz['quiz_id'] ) ); ?>
                                </a>
                                <span class="mc-question-count">
                                    - <?php echo $quiz['question_count']; ?> <?php echo _n( 'question', 'questions', $quiz['question_count'], 'custom-quiz-types-for-multiclass' ); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="no-quizzes">
                        <?php _e( 'No quizzes found with this question type.', 'custom-quiz-types-for-multiclass' ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get quizzes by MultiClass question type
     *
     * @param string $type The question type
     * @return array Array of quiz data
     */
    private function get_quizzes_by_multiclass_type( $type ) {
        // Get all questions with this multiclass type
        $questions = get_posts( array(
            'post_type' => 'sfwd-question',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_key' => '_multiclass_question_type',
            'meta_value' => $type,
            'fields' => 'ids',
        ) );

        if ( empty( $questions ) ) {
            return array();
        }

        // Get quizzes that contain these questions
        $quizzes = array();
        
        foreach ( $questions as $question_id ) {
            // Use LearnDash function to get all quizzes for this question (handles shared mode)
            $question_quizzes = learndash_get_quizzes_for_question( $question_id, true );
            
            if ( ! empty( $question_quizzes ) ) {
                foreach ( $question_quizzes as $quiz_id => $quiz_title ) {
                    if ( ! isset( $quizzes[ $quiz_id ] ) ) {
                        $quizzes[ $quiz_id ] = array(
                            'quiz_id' => $quiz_id,
                            'question_count' => 0,
                        );
                    }
                    
                    $quizzes[ $quiz_id ]['question_count']++;
                }
            }
        }

        // Sort by quiz title
        usort( $quizzes, function( $a, $b ) {
            return strcasecmp( get_the_title( $a['quiz_id'] ), get_the_title( $b['quiz_id'] ) );
        } );

        return $quizzes;
    }

    /**
     * Get quizzes by LearnDash answer type
     *
     * @param string $type The answer type
     * @return array Array of quiz data
     */
    private function get_quizzes_by_learndash_type( $type ) {
        // Get all questions with this answer type
        $questions = get_posts( array(
            'post_type' => 'sfwd-question',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_key' => 'question_type',
            'meta_value' => $type,
            'fields' => 'ids',
        ) );

        if ( empty( $questions ) ) {
            return array();
        }

        // Get quizzes that contain these questions
        $quizzes = array();
        
        foreach ( $questions as $question_id ) {
            // Use LearnDash function to get all quizzes for this question (handles shared mode)
            $question_quizzes = learndash_get_quizzes_for_question( $question_id, true );
            
            if ( ! empty( $question_quizzes ) ) {
                foreach ( $question_quizzes as $quiz_id => $quiz_title ) {
                    if ( ! isset( $quizzes[ $quiz_id ] ) ) {
                        $quizzes[ $quiz_id ] = array(
                            'quiz_id' => $quiz_id,
                            'question_count' => 0,
                        );
                    }
                    
                    $quizzes[ $quiz_id ]['question_count']++;
                }
            }
        }

        // Sort by quiz title
        usort( $quizzes, function( $a, $b ) {
            return strcasecmp( get_the_title( $a['quiz_id'] ), get_the_title( $b['quiz_id'] ) );
        } );

        return $quizzes;
    }
}
