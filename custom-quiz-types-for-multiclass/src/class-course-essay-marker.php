<?php
namespace Multiclass;

defined( 'ABSPATH' ) || exit;

/**
 * Class Course_Essay_Marker
 * Adds a metabox to sfwd-courses post type for marking courses as essay type
 */
class Course_Essay_Marker {
    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        add_filter( 'learndash_get_label_course_step_next', array($this, 'maybe_show_essay_specific_next_step_label_for_lesson'), 10, 2 );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_sfwd-courses', array( $this, 'save_meta_box' ) );
        add_filter( 'body_class', array( $this, 'add_essay_body_class' ) );
        add_filter( 'learndash_template', array($this, 'maybe_show_essay_specific_result_box'), 10, 3 );
        add_filter( 'learndash_template', array($this, 'load_essay_specific_quiz_messages'), 10, 3 );
    }

    /**
     * Add the meta box to sfwd-courses post type
     */
    public function add_meta_box() {
        add_meta_box(
            'sp_course_essay_marker',
            __( 'Essay Course Settings', 'custom-quiz-types-for-multiclass' ),
            array( $this, 'render_meta_box' ),
            'sfwd-courses',
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
        wp_nonce_field( 'sp_course_essay_marker_nonce_action', 'sp_course_essay_marker_nonce' );

        // Get current value
        $is_essay = get_post_meta( $post->ID, '_sp_is_essay_course', true );
        $checked = ( $is_essay === '1' ) ? 'checked' : '';

        ?>
        <div class="sp-course-essay-marker">
            <label>
                <input type="checkbox" 
                       name="sp_is_essay_course" 
                       id="sp_is_essay_course" 
                       value="1" 
                       <?php echo $checked; ?> />
                <?php _e( 'Mark this course as Essay Course', 'custom-quiz-types-for-multiclass' ); ?>
            </label>
            <p class="description">
                <?php _e( 'Enable this option to mark this course as an essay-based course.', 'custom-quiz-types-for-multiclass' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save the meta box data
     * 
     * @param int $post_id The post ID
     */
    public function save_meta_box( $post_id ) {
        // Check if nonce is set
        if ( ! isset( $_POST['sp_course_essay_marker_nonce'] ) ) {
            return;
        }

        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['sp_course_essay_marker_nonce'], 'sp_course_essay_marker_nonce_action' ) ) {
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
        if ( isset( $_POST['sp_is_essay_course'] ) && $_POST['sp_is_essay_course'] === '1' ) {
            update_post_meta( $post_id, '_sp_is_essay_course', '1' );
        } else {
            delete_post_meta( $post_id, '_sp_is_essay_course' );
        }
    }

    /**
     * Add body class if viewing a quiz or topic from an essay course
     * 
     * @param array $classes Array of body classes
     * @return array Modified array of body classes
     */
    public function add_essay_body_class( $classes ) {
        // Check if we're on a quiz or topic page
        if ( is_singular( 'sfwd-quiz' ) || is_singular( 'sfwd-topic' ) ) {
            $post_id = get_the_ID();
            $course_id = learndash_get_course_id( $post_id );
            
            // Check if the course is marked as essay
            if ( $course_id ) {
                $is_essay = get_post_meta( $course_id, '_sp_is_essay_course', true );
                
                if ( $is_essay === '1' ) {
                    $classes[] = 'sp-essay-course';
                }
            }
        }
        
        return $classes;
    }

    function maybe_show_essay_specific_result_box( $filepath, $name, $args ) {
		if( 'quiz/partials/show_quiz_result_box.php' !== $name  ) {
			return $filepath;
		}

		$quiz_post_id = $args['quiz']->getPostId();

		$course_id = learndash_get_course_id( $quiz_post_id );

		$is_essay = get_post_meta($course_id, '_sp_is_essay_course', true);
		if (!$is_essay) {
			return $filepath;
		}

		return CUSTOM_QUIZ_FILE_PATH . 'templates/essay-course/show_quiz_result_box.php';
	}

    function maybe_show_essay_specific_next_step_label_for_lesson( $default_value, $step_post_type ) {
        $current_post_id = get_the_ID();
        if (get_post_type($current_post_id) !== 'sfwd-topic') {
            return $default_value;
        }

        $course_id = learndash_get_course_id($current_post_id);

        $is_essay = get_post_meta($course_id, '_sp_is_essay_course', true);
        if (!$is_essay) {
            return $default_value;
        }

        return esc_html__('Write an essay', 'custom-quiz-types-for-multiclass');
    }

    function load_essay_specific_quiz_messages( $filepath, $name, $args ) {
        if( 'learndash_quiz_messages' !== $name  ) {
            return $filepath;
        }

        // Check if quiz_post_id exists in args
        if( ! array_key_exists( 'quiz_post_id', $args ) ) {
            return $filepath;
        }

        // The 'quiz_post_id' in args is actually the WpProQuiz ID, not the WordPress post ID
        $quiz_pro_id = $args['quiz_post_id'];
        
        // Convert pro quiz ID to WordPress post ID
        $quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_pro_id );
        if ( empty( $quiz_post_id ) ) {
            return $filepath;
        }

        $course_id = learndash_get_course_id( $quiz_post_id );

        $is_essay = get_post_meta($course_id, '_sp_is_essay_course', true);
        if (!$is_essay) {
            return $filepath;
        }

        return CUSTOM_QUIZ_FILE_PATH . 'templates/essay-course/learndash_quiz_messages.php';
    }
}
