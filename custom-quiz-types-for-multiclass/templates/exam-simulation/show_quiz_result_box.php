<?php
/**
 * Displays Quiz Result Box
 *
 * Available Variables:
 *
 * @var object $quiz_view      WpProQuiz_View_FrontQuiz instance.
 * @var object $quiz           WpProQuiz_Model_Quiz instance.
 * @var array  $shortcode_atts Array of shortcode attributes to create the Quiz.
 * @var int    $question_count Number of Question to display.
 * @var array  $result         Array of Quiz Result Messages.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\Legacy\Quiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="display: none;" class="wpProQuiz_sending">
	<p>
		<div>
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_complete_message',
					// translators: placeholder: Quiz.
					'message'      => sprintf( esc_html_x( '%s complete. Results are being recorded.', 'placeholder: Quiz', 'learndash' ), \LearnDash_Custom_Label::get_label( 'quiz' ) ),
				)
			)
		);
		?>
		</div>
		<div>
			<dd class="course_progress">
				<div class="course_progress_blue sending_progress_bar" style="width: 0%;">
				</div>
			</dd>
		</div>
	</p>
</div>

<div style="display: none;" class="wpProQuiz_results">
	
	<?php
	$course_id = learndash_get_course_id( $quiz->getPostId() );
	if ( ! empty( $course_id ) ) {
		$next_item = \Multiclass\Exam_Simulation::find_next_topic_or_quiz(wp_get_current_user(), $course_id, $quiz->getPostId());
		if ($next_item) {
			$next_quiz_link = get_permalink($next_item['ID']);
			?>
			<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;min-height: 250px;" class="ld-quiz-next-quiz">
				<a style="text-decoration:none" href="<?php echo esc_url($next_quiz_link); ?>" class="ld-button">
					<?php echo sprintf(esc_html__('Go to Next %s', 'custom-quiz-types-for-multiclass'), $next_item['type'] ); ?>
				</a>
			</div>
			<?php
		} else {
			$course_link = get_permalink( $course_id );
			$course_link = add_query_arg( 'only-answers', 'true', $course_link );
			?>
			<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;min-height: 250px;" class="ld-quiz-no-next-quiz">
				<p><?php echo esc_html__('No more quizzes available in this course.', 'custom-quiz-types-for-multiclass'); ?></p>
				<a style="text-decoration:none; margin-top: 10px;" href="<?php echo esc_url($course_link); ?>" class="ld-button">
					<?php echo esc_html__('Check Answers', 'custom-quiz-types-for-multiclass'); ?>
				</a>
			</div>
			<?php
		}

		if( 17269 === $quiz->getPostId() ) {
			?>
			<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;min-height: 250px;" class="ld-quiz-next-quiz">
				<a style="text-decoration:none" href="<?php echo esc_url($next_quiz_link); ?>" class="ld-button">
					<?php echo sprintf(esc_html__('Go to Next %s', 'custom-quiz-types-for-multiclass'), $next_item['type'] ); ?>
				</a>
			</div>
			<?php
		}
	}
	?>

	<div class="ld-quiz-actions" style="margin: 10px 0px;">
		
	</div>
	
	<?php
	// Check if quiz contains any questions with more than 1 point
	$has_multi_point_question = false;
	$question_mapper = new WpProQuiz_Model_QuestionMapper();
	$questions = $question_mapper->fetchAll($quiz);
	
	if (!empty($questions)) {
		foreach ($questions as $question) {
			if ($question->getPoints() > 1) {
				$has_multi_point_question = true;
				break;
			}
		}
	}
	
	if ($has_multi_point_question) {
		?>
		<div class="wpProQuiz_high_points_explanation" style="margin-top: 30px; padding: 25px 30px; background: linear-gradient(135deg, #7c8fe6 0%, #a18cd1 100%); border-radius: 12px; box-shadow: 0 4px 15px rgba(124, 143, 230, 0.25);">
			<div style="display: flex; align-items: flex-start; gap: 15px;">
				<div style="flex-shrink: 0; width: 40px; height: 40px; background-color: rgba(255, 255, 255, 0.25); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
					💡
				</div>
				<div style="flex: 1;">
					<h4 style="margin: 0 0 12px 0; color: #fff; font-size: 18px; font-weight: 600; letter-spacing: -0.3px;">
						<?php echo esc_html__('Wieso hat das Quiz eine so hohe Maximalpunktzahl?', 'custom-quiz-types-for-multiclass'); ?>
					</h4>
					<p style="margin: 0; line-height: 1.7; color: rgba(255, 255, 255, 0.95); font-size: 15px;">
						<?php echo esc_html__('Jedes einzelne Feld im Quiz ist ein Punkt. Hast du also zwei Felder nicht richtig ausgefüllt, fehlen dir 2 Punkte zur Maximalpunktzahl. Nun besteht das Quiz aus sehr vielen einzelnen Feldern, welche die hohe Maximalpunktzahl erklären. So kannst du durch die vielen einzelnen Punkte besser erkennen, ob du beim nächsten Lösen Fortschritte gemacht und nun mehr Felder richtig gelöst hast.', 'custom-quiz-types-for-multiclass'); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>
