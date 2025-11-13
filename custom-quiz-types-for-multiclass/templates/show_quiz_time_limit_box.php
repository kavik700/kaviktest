<?php
/**
 * Displays Quiz Time Limit Box
 *
 * Available Variables:
 *
 * @var object  $quiz_view      WpProQuiz_View_FrontQuiz instance.
 * @var object $quiz           WpProQuiz_Model_Quiz instance.
 * @var array  $shortcode_atts Array of shortcode attributes to create the Quiz.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\Legacy\Quiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$quiz_post_id = $quiz->getPostId();

$disable_time_limit_ability_enabled = get_post_meta($quiz_post_id, '_cqt_disable_time_limit', true) === 'yes';

?>

<?php if( $disable_time_limit_ability_enabled ): ?>
<div class="cqt-time-limit-toggle-switch">
    <label class="cqt-toggle-switch">
        <input type="checkbox" id="cqt-time-limit-toggle" <?php echo (empty($_GET['action']) || $_GET['action'] !== 'disable-time-limit') ? 'checked' : ''; ?>>
        <span class="cqt-slider"></span>
    </label>
    <span id="cqt-toggle-label"><?php esc_html_e( 'Time limit', 'custom-quiz-types-for-multiclass' ); ?></span>
</div>
<?php endif; ?>

<div style="display: none;" class="wpProQuiz_time_limit">
	<div class="time">
		
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_quiz_time_limit_message',
					'message'      => esc_html__( 'Zeitlimit für das Quiz', 'learndash' ) . ': <span>0</span>',
				)
			)
		);
		?>
	</div>
	<div class="wpProQuiz_progress"></div>
</div>
