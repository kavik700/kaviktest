<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use LearnDash_Custom_Label;

class Exam_Simulation {
    
    public function __construct() {
        // Initialize the class
        add_action('init', array($this, 'init'));
    }

    public function init() {
		add_action( 'learndash-course-content-list-before', array($this, 'question_answers'), 10, 2 );

		add_filter( 'learndash_template', array($this, 'maybe_hide_quiz_result_box_for_exam_simulation'), 10, 3 );

		// Add metabox to course edit page
		add_action('add_meta_boxes', array($this, 'add_exam_simulation_metabox'));
		
		// Save metabox data
		add_action('save_post', array($this, 'save_exam_simulation_metabox'));

		add_filter('body_class', array($this, 'add_exam_simulation_body_class'));

		add_filter( 'learndash_course_table_class', array($this, 'add_exam_simulation_table_class'), 10, 1 );

		add_action('mc_course_return_group_link_after', array($this, 'add_exam_simulation_return_group_link_after'));

		// Add AJAX handler for delete_and_archive_quiz_data
		add_action('wp_ajax_delete_and_archive_quiz_data', array($this, 'ajax_delete_and_archive_quiz_data'));

		// Add user and course IDs to body tag
		add_action('wp_head', array($this, 'add_user_course_ids_to_body'));
    }

	public function add_exam_simulation_return_group_link_after() {
		$course_id = get_the_ID();
		$activate_exam_simulation = get_post_meta($course_id, '_activate_exam_simulation', true);
		if( ! $activate_exam_simulation ) {
			return;
		}

		$completed_all_quizzes = self::has_completed_all_quizzes(get_current_user_id(), $course_id);
		
		if( ! $completed_all_quizzes ) {
			return;
		}
		
		?>
		<div class="exam-simulation-buttons">
			<a class="btn" id="btn-repeat">
				<?php echo esc_html__('Wiederholen', 'custom-quiz-types-for-multiclass'); ?>
			</a>
			<?php if( ! isset($_GET['archived']) || $_GET['archived'] !== '1' ): ?>
			<a href="<?php echo esc_url(add_query_arg(array('archived' => '1', 'only-answers' => 'true'))); ?>" class="btn" id="btn-previous-scores">
				<?php echo esc_html__('Vorherige Ergebnisse', 'custom-quiz-types-for-multiclass'); ?>
			</a>
			<?php endif; ?>
			<a class="btn" target="_blank" href="<?php echo esc_url(add_query_arg(array('certificate' => '1', 'course-id' => $course_id))); ?>" id="btn-certificate">
				<?php echo esc_html__('Zertifikat', 'custom-quiz-types-for-multiclass'); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Delete all quiz data for a specific course from _sfwd-quizzes and archive them.
	 * Also archives and resets audio play count data for the course.
	 *
	 * @param int $user_id   The user ID.
	 * @param int $course_id The course ID to delete quiz data for.
	 * @return bool True on success, false on failure.
	 */
	public function delete_and_archive_quiz_data_for_course( $user_id, $course_id ) {
		$user_id   = absint( $user_id );
		$course_id = absint( $course_id );

		if ( ! $user_id || ! $course_id ) {
			return false;
		}

		// Get all quiz attempts.
		$quiz_attempts = get_user_meta( $user_id, '_sfwd-quizzes', true );
		if ( empty( $quiz_attempts ) || ! is_array( $quiz_attempts ) ) {
			return false;
		}

		$to_archive = array();
		$to_keep    = array();

		foreach ( $quiz_attempts as $attempt ) {
			if ( isset( $attempt['course'] ) && absint( $attempt['course'] ) === $course_id ) {
				$to_archive[] = $attempt;
			} else {
				$to_keep[] = $attempt;
			}
		}

		// Get timestamp for this archive (used for both quiz and audio data)
		$timestamp = time();

		// Archive the removed attempts.
		if ( ! empty( $to_archive ) ) {
			// Get existing archived data
			$archived = get_user_meta( $user_id, '_mc-sfwd-quizzes-archived', true );
			if ( ! is_array( $archived ) ) {
				$archived = array();
			}

			// Initialize course array if it doesn't exist
			if ( ! isset( $archived[ $course_id ] ) ) {
				$archived[ $course_id ] = array();
			}

			// Add current timestamp as key for this archive
			$archived[ $course_id ][ $timestamp ] = $to_archive;

			update_user_meta( $user_id, '_mc-sfwd-quizzes-archived', $archived );
		}

		// Update the user's quiz attempts.
		update_user_meta( $user_id, '_sfwd-quizzes', $to_keep );

		// Archive and reset audio play count data for this course
		$this->archive_and_reset_audio_data_for_course( $user_id, $course_id, $timestamp );

		return true;
	}

	/**
	 * Archive and reset audio play count data for a specific course.
	 *
	 * @param int $user_id   The user ID.
	 * @param int $course_id The course ID to archive audio data for.
	 * @param int $timestamp The timestamp to use for this archive.
	 * @return bool True on success, false on failure.
	 */
	private function archive_and_reset_audio_data_for_course( $user_id, $course_id, $timestamp ) {
		global $wpdb;

		$user_id   = absint( $user_id );
		$course_id = absint( $course_id );

		if ( ! $user_id || ! $course_id ) {
			return false;
		}

		// Find all audio play count meta keys for this course
		$meta_key_pattern = 'sp_course_' . $course_id . '_audio_play_count_%';
		
		$audio_meta_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value 
				FROM {$wpdb->usermeta} 
				WHERE user_id = %d 
				AND meta_key LIKE %s",
				$user_id,
				$meta_key_pattern
			),
			ARRAY_A
		);

		if ( empty( $audio_meta_keys ) ) {
			return false;
		}

		// Prepare audio data for archiving
		$audio_data_to_archive = array();
		foreach ( $audio_meta_keys as $meta ) {
			$audio_data_to_archive[ $meta['meta_key'] ] = $meta['meta_value'];
			
			// Delete the meta key
			delete_user_meta( $user_id, $meta['meta_key'] );
		}

		// Archive the audio data
		if ( ! empty( $audio_data_to_archive ) ) {
			// Get existing archived audio data
			$archived_audio = get_user_meta( $user_id, '_mc-audio-play-counts-archived', true );
			if ( ! is_array( $archived_audio ) ) {
				$archived_audio = array();
			}

			// Initialize course array if it doesn't exist
			if ( ! isset( $archived_audio[ $course_id ] ) ) {
				$archived_audio[ $course_id ] = array();
			}

			// Add current timestamp as key for this archive
			$archived_audio[ $course_id ][ $timestamp ] = $audio_data_to_archive;

			update_user_meta( $user_id, '_mc-audio-play-counts-archived', $archived_audio );
		}

		return true;
	}

	public function add_exam_simulation_table_class($class) {
		if ( ! isset( $_GET['only-answers'] ) ) {
			return $class;
		}

		return $class . ' hide';
	}

	private static function get_all_course_items($course_id) {
		$course_items = [];
		$order = 0;
	
		// Get lessons
		$lessons = learndash_get_course_lessons_list($course_id, null, array('num' => -1));
		foreach ($lessons as $lesson) {
			// Get and add topics first
			$topics = learndash_get_topic_list($lesson['post']->ID);
			foreach ($topics as $topic) {
				$course_items[] = [
					'ID' => $topic->ID,
					'type' => LearnDash_Custom_Label::get_label('topic'),
					'parent' => $lesson['post']->ID,
					'order' => $order++
				];
	
				// Add topic quizzes immediately after their topics
				$topic_quizzes = learndash_get_lesson_quiz_list($topic->ID);
				foreach ($topic_quizzes as $quiz) {
					$course_items[] = [
						'ID' => $quiz['post']->ID,
						'type' => LearnDash_Custom_Label::get_label('quiz'),
						'parent' => $topic->ID,
						'order' => $order++
					];
				}
			}
	
			// Add lesson quizzes after topics
			$lesson_quizzes = learndash_get_lesson_quiz_list($lesson['post']->ID);
			foreach ($lesson_quizzes as $quiz) {
				$course_items[] = [
					'ID' => $quiz['post']->ID,
					'type' => LearnDash_Custom_Label::get_label('quiz'),
					'parent' => $lesson['post']->ID,
					'order' => $order++
				];
			}
		}
	
		// Get course-level quizzes last
		$direct_quizzes = learndash_get_course_quiz_list($course_id);
		foreach ($direct_quizzes as $quiz) {
			$course_items[] = [
				'ID' => $quiz['post']->ID,
				'type' => LearnDash_Custom_Label::get_label('quiz'),
				'parent' => 0,
				'order' => $order++
			];
		}
	
		return $course_items;
	}

	public static function find_next_topic_or_quiz($user, $course_id, $current_item_id = null) {
		$course_items = self::get_all_course_items($course_id);
		
		// If no current_item_id, return the first item
		if (!$current_item_id) {
			return !empty($course_items) ? $course_items[0]['ID'] : null;
		}
		
		// Find current item index
		$current_index = array_search($current_item_id, array_column($course_items, 'ID'));
		if ($current_index === false) {
			return null;
		}
		
		// Check if there's a next item
		$next_index = $current_index + 1;
		if (!array_key_exists($next_index, $course_items)) {
			return null;
		}
		
		return $course_items[$next_index];
	}

	/**
     * Add a custom body class if exam simulation is activated
     *
     * @param array $classes An array of body classes.
     * @return array Modified array of body classes.
     */
    public function add_exam_simulation_body_class($classes) {
        if (is_singular('sfwd-courses') || is_singular('sfwd-quiz')) {
            if (is_singular('sfwd-courses')) {
                $course_id = get_the_ID();
            } else {
                $quiz_id = get_the_ID();
                $course_id = learndash_get_course_id($quiz_id);
            }
            
            $exam_simulation_activated = get_post_meta($course_id, '_activate_exam_simulation', true);
            
            if ($exam_simulation_activated) {
                $classes[] = 'exam-simulation-active';
            }
        }
        return $classes;
    }

	function maybe_hide_quiz_result_box_for_exam_simulation( $filepath, $name, $args ) {
		if( 'quiz/partials/show_quiz_result_box.php' !== $name  ) {
			return $filepath;
		}

		$quiz_post_id = $args['quiz']->getPostId();

		$course_id = learndash_get_course_id( $quiz_post_id );

		$exam_simulation_activated = get_post_meta($course_id, '_activate_exam_simulation', true);
		if (!$exam_simulation_activated) {
			return $filepath;
		}

		return CUSTOM_QUIZ_FILE_PATH . 'templates/exam-simulation/show_quiz_result_box.php';
	}

    public function question_answers($course_id, $user_id) {
		$exam_simulation_activated = get_post_meta($course_id, '_activate_exam_simulation', true);
		if (!$exam_simulation_activated) {
			return;
		}

        // Check if we should show archived attempts
        $show_archived = isset($_GET['archived']) && $_GET['archived'] === '1';
        
        if ($show_archived) {
            $course_quiz_attempts = $this->get_user_archived_quiz_attempts($user_id);
        } else {
            $course_quiz_attempts = learndash_get_user_profile_quiz_attempts($user_id);
        }

		// Check if user completed all quizzes in the course
		$all_quizzes_completed = self::has_completed_all_quizzes($user_id, $course_id);
		// Bail if not all quizzes are completed
		if (!$all_quizzes_completed) {
			return;
		}

        self::showModalWindow();
		?>
		<div class="mc-exam-simulation-results">
		<?php

		if( $show_archived ) {
		?>
		<div class="exam-simulation-archives-header">
			<h4><?php echo esc_html__( 'Auf diesem Bildschirm findest du archivierte Prüfungssimulationsergebnisse.', 'custom-quiz-types-for-multiclass' ); ?></h4>
		</div>
		<?php
			echo '<div class="exam-simulation-archives">';
			?>
			<?php
			foreach( $course_quiz_attempts[ $course_id ] as $timestamp => $course_quiz_attempts ) {
				$archive_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
				?>
				<div class="exam-simulation-archive-accordion">
					<div class="exam-simulation-archive-header">
						<h3><?php echo esc_html__( 'archiviert am:', 'custom-quiz-types-for-multiclass' ) . ' ' . esc_html( $archive_date ); ?></h3>
						<span class="exam-simulation-archive-toggle">+</span>
					</div>
					<div class="exam-simulation-archive-content" style="display: none;">
						<?php
						learndash_get_template_part(
							'shortcodes/profile/quizzes.php',
							array(
								'user_id'       => $user_id,
								'course_id'     => $course_id,
								'quiz_attempts' => $course_quiz_attempts,
							),
							true
						);
						?>
					</div>
				</div>
				<?php
			}

			if (empty($course_quiz_attempts[$course_id])) {
				echo '<div class="exam-simulation-no-results">';
				echo '<p>' . esc_html__('Keine Resultate vorhanden', 'custom-quiz-types-for-multiclass') . '</p>';
				echo '</div>';
			}

			echo '</div>';
			?>
			<script>
			jQuery(document).ready(function($) {
				$('.exam-simulation-archive-header').on('click', function() {
					var content = $(this).next('.exam-simulation-archive-content');
					var toggle = $(this).find('.exam-simulation-archive-toggle');
					
					content.slideToggle(200);
					toggle.text(content.is(':visible') ? '-' : '+');
				});
			});
			</script>
			<?php
		} else {
			$quizzes = $course_quiz_attempts[$course_id];

			// Move quizzes with 'mc_hide_grades' == 1 to the end
			if ( is_array( $quizzes ) && ! empty( $quizzes ) ) {
				$quizzes_with_hidden_grades = array();
				$quizzes_without_hidden_grades = array();

				foreach ( $quizzes as $i => $quiz ) {
					$quiz_post_id = isset( $quiz['quiz'] ) ? $quiz['quiz'] : 0;
					if ( $quiz_post_id && get_post_meta( $quiz_post_id, 'mc_hide_grades', true ) === '1' ) {
						$quizzes_with_hidden_grades[] = $quiz;
						$quizzes[$i]['hide_grades'] = true;
					} else {
						$quizzes_without_hidden_grades[] = $quiz;
						$quizzes[$i]['hide_grades'] = false;
					}
				}

				$quizzes = array_merge( $quizzes_without_hidden_grades, $quizzes_with_hidden_grades );
				$course_quiz_attempts[$course_id] = $quizzes;
			}

			learndash_get_template_part(
				'shortcodes/profile/quizzes.php',
				array(
					'user_id'       => $user_id,
					'course_id'     => $course_id,
					'quiz_attempts' => $course_quiz_attempts,
				),
				true
			);
		}
		?>
		</div>
		<?php
    }

    /**
	 * Show Modal Window
	 *
	 * @since 2.3.0
	 */
	public static function showModalWindow() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Better to keep it this way.
		static $show_only_once = false;

		/**
		 * Added for LEARNDASH-2754 to prevent loading the inline CSS when inside
		 * the Gutenberg editor publish/update. Need a better way to handle this.
		 */
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		if ( ! $show_only_once ) {
			$show_only_once = true;
			?>
			<style>
			.wpProQuiz_blueBox {
				padding: 20px;
				background-color: rgb(223, 238, 255);
				border: 1px dotted;
				margin-top: 10px;
			}
			.categoryTr th {
				background-color: #F1F1F1;
			}
			.wpProQuiz_modal_backdrop {
				background: #000;
				opacity: 0.7;
				top: 0;
				bottom: 0;
				right: 0;
				left: 0;
				position: fixed;
				z-index: 159900;
			}
			.wpProQuiz_modal_window {
				position: fixed;
				background: #FFF;
				top: 40px;
				bottom: 40px;
				left: 40px;
				right: 40px;
				z-index: 160000;
			}
			.wpProQuiz_actions {
				display: none;
				padding: 2px 0 0;
			}

			.mobile .wpProQuiz_actions {
				display: block;
			}

			tr:hover .wpProQuiz_actions {
				display: block;
			}
			</style>
			<div id="wpProQuiz_user_overlay" style="display: none;">
				<div class="wpProQuiz_modal_window" style="padding: 20px; overflow: scroll;">
					<input type="button" value="<?php echo esc_html__( 'Schliessen', 'custom-quiz-types-for-multiclass' ); ?>" class="button-primary" style=" position: fixed; top: 48px; right: 59px; z-index: 160001;" id="wpProQuiz_overlay_close">

					<div id="wpProQuiz_user_content" style="margin-top: 20px;"></div>

					<div id="wpProQuiz_loadUserData" class="wpProQuiz_blueBox" style="background-color: #F8F5A8; display: none; margin: 50px;">
						<img alt="load" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />
						<?php echo esc_html__( 'Laden', 'custom-quiz-types-for-multiclass' ); ?>
					</div>
				</div>
				<div class="wpProQuiz_modal_backdrop"></div>
			</div>
			<?php
		}
	}

	/**
	 * Add Exam Simulation metabox to course edit page
	 */
	public function add_exam_simulation_metabox() {
		add_meta_box(
			'exam_simulation_metabox',
			esc_html__('Exam Simulation Settings', 'custom-quiz-types-for-multiclass'),
			array($this, 'render_exam_simulation_metabox'),
			'sfwd-courses',
			'side',
			'default'
		);
	}

	/**
	 * Render Exam Simulation metabox content
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_exam_simulation_metabox($post) {
		// Add nonce for security
		wp_nonce_field('exam_simulation_metabox', 'exam_simulation_nonce');

		// Get current value
		$activate_exam_simulation = get_post_meta($post->ID, '_activate_exam_simulation', true);

		// Output checkbox
		?>
		<label for="activate_exam_simulation">
			<input type="checkbox" id="activate_exam_simulation" name="activate_exam_simulation" value="1" <?php checked($activate_exam_simulation, '1'); ?>>
			<?php echo esc_html__('Activate Exam Simulation?', 'custom-quiz-types-for-multiclass'); ?>
		</label>
		<?php
	}

	/**
	 * Save Exam Simulation metabox data
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_exam_simulation_metabox($post_id) {
		// Check if our nonce is set and verify it
		if (!isset($_POST['exam_simulation_nonce']) || !wp_verify_nonce($_POST['exam_simulation_nonce'], 'exam_simulation_metabox')) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		// Check the user's permissions
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		// Save the checkbox value
		$activate_exam_simulation = isset($_POST['activate_exam_simulation']) ? '1' : '0';
		update_post_meta($post_id, '_activate_exam_simulation', $activate_exam_simulation);
	}

	/**
	 * AJAX handler for delete_and_archive_quiz_data
	 */
	public function ajax_delete_and_archive_quiz_data() {
		// Get user ID and course ID first
		$user_id = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
		$course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;

		if (!$user_id || !$course_id) {
			wp_send_json_error('Invalid user ID or course ID');
		}

		// Verify nonce with course ID
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_and_archive_quiz_data_' . $course_id)) {
			wp_send_json_error('Invalid nonce');
		}

		// Call the delete and archive function
		$result = $this->delete_and_archive_quiz_data_for_course($user_id, $course_id);

		if ($result) {
			wp_send_json_success('Quiz data deleted and archived successfully');
		} else {
			wp_send_json_error('Failed to delete and archive quiz data');
		}
	}

	/**
	 * Add user ID and course ID to body tag
	 */
	public function add_user_course_ids_to_body() {
		if (!is_singular('sfwd-courses')) {
			return;
		}

		$user_id = get_current_user_id();
		$course_id = get_the_ID();

		if (!$user_id || !$course_id) {
			return;
		}

		?>
		<script>
			document.body.setAttribute('data-user-id', '<?php echo esc_js($user_id); ?>');
			document.body.setAttribute('data-course-id', '<?php echo esc_js($course_id); ?>');
		</script>
		<?php
	}

	/**
	 * Gets the user's archived quiz attempts
	 *
	 * @param int $user_id Optional. The ID of the user to get archived quiz attempts. Default 0.
	 *
	 * @return array An array of archived quiz attempts, otherwise empty array.
	 */
	public function get_user_archived_quiz_attempts( $user_id = 0 ) {
		$user_id = absint( $user_id );
		$user    = get_user_by( 'id', $user_id );

		$quiz_attempts = array();

		if ( ! $user ) {
			return $quiz_attempts;
		}

		$archived_data = get_user_meta( $user_id, '_mc-sfwd-quizzes-archived', true );
		if ( empty( $archived_data ) || ! is_array( $archived_data ) ) {
			return $quiz_attempts;
		}

		foreach ( $archived_data as $course_id => $timestamps ) {
			foreach( $timestamps as $timestamp => $course_attempts ) {
				foreach ( $course_attempts as $quiz_attempt ) {
					$c = learndash_certificate_details( $quiz_attempt['quiz'], $user_id );
					$quiz_attempt['post'] = get_post( $quiz_attempt['quiz'] );

					if ( get_current_user_id() == $user_id && ! empty( $c['certificateLink'] ) && ( ( isset( $quiz_attempt['percentage'] ) && $quiz_attempt['percentage'] >= $c['certificate_threshold'] * 100 ) ) ) {
						$quiz_attempt['certificate'] = $c;
						if ( ( isset( $quiz_attempt['certificate']['certificateLink'] ) ) && ( ! empty( $quiz_attempt['certificate']['certificateLink'] ) ) ) {
							$quiz_attempt['certificate']['certificateLink'] = add_query_arg( array( 'time' => $quiz_attempt['time'] ), $quiz_attempt['certificate']['certificateLink'] );
						}
					}

					if ( ! isset( $quiz_attempt['course'] ) ) {
						$quiz_attempt['course'] = $course_id;
					}

					$quiz_attempts[ $course_id ][ $timestamp ][ $course_id ][] = $quiz_attempt;
				}
			}
		}

		return $quiz_attempts;
	}

	/**
	 * Check if the user has completed all quizzes in the course.
	 * Caches the result for the duration of the PHP process.
	 *
	 * @param int $user_id
	 * @param int $course_id
	 * @return bool
	 */
	public static function has_completed_all_quizzes($user_id, $course_id) {
		static $cache = array();
		$user_id = absint($user_id);
		$course_id = absint($course_id);
		$cache_key = $user_id . '_' . $course_id;
		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$course_quizzes = array();
		$lessons = learndash_get_course_lessons_list($course_id);
		foreach ($lessons as $lesson) {
			$lesson_quizzes = learndash_get_lesson_quiz_list($lesson['post']->ID, $user_id, $course_id);
			$course_quizzes = array_merge($course_quizzes, $lesson_quizzes);
		}
		$topics = learndash_get_topic_list($course_id);
		foreach ($topics as $topic) {
			$topic_quizzes = learndash_get_lesson_quiz_list($topic->ID, $user_id, $course_id);
			$course_quizzes = array_merge($course_quizzes, $topic_quizzes);
		}
		$course_level_quizzes = learndash_get_course_quiz_list($course_id, $user_id);
		$course_quizzes = array_merge($course_quizzes, $course_level_quizzes);

		$all_quizzes_completed = true;
		foreach ($course_quizzes as $quiz) {
			$quiz_id = $quiz['post']->ID;
			$quiz_attempts = learndash_get_user_quiz_attempts($user_id, $quiz_id);
			if (empty($quiz_attempts)) {
				$all_quizzes_completed = false;
				break;
			}
		}
		$cache[$cache_key] = $all_quizzes_completed;
		return $all_quizzes_completed;
	}
}
