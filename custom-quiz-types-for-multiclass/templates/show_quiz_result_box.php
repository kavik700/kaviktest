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
	<h4 class="wpProQuiz_header"><?php esc_html_e( 'Results', 'learndash' ); ?></h4>
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
					'message'      => sprintf( esc_html_x( '%s complete. Results are being recorded.', 'placeholder: Quiz', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
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
	<h4 class="wpProQuiz_header"><?php esc_html_e( 'Results', 'learndash' ); ?></h4>
	<?php
	if ( ! $quiz->isHideResultCorrectQuestion() ) {
		echo '<div class="mc-quiz-result">';
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_questions_answered_correctly_message',
					// translators: placeholder: correct answer, question count, questions.
					'message'      => '<p>' . sprintf( esc_html_x( '%1$s of %2$s %3$s answered correctly', 'placeholder: correct answer, question count, questions', 'learndash' ), '<span class="wpProQuiz_correct_answer">0</span>', '<span>' . $question_count . '</span>', learndash_get_custom_label( 'questions' ) ) . '</p>',
					'placeholders' => array( '0', $question_count ),
				)
			)
		);
		echo '</div>';
	}

	if ( ! $quiz->isHideResultQuizTime() ) {
		?>
		<p class="wpProQuiz_quiz_time">
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_your_time_message',
						// translators: placeholder: quiz time.
						'message'      => sprintf( esc_html_x( 'Your time: %s', 'placeholder: quiz time.', 'learndash' ), '<span></span>' ),
					)
				)
			);
		?>
		</p>
		<?php
	}
	?>
	<p class="wpProQuiz_time_limit_expired" style="display: none;">
	<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_time_has_elapsed_message',
					'message'      => esc_html__( 'Time has elapsed', 'learndash' ),
				)
			)
		);
		?>
	</p>

	<?php
	if ( ! $quiz->isHideResultPoints() ) {
		?>
		<p class="wpProQuiz_points">
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_have_reached_points_message',
						// translators: placeholder: points earned, points total.
						'message'      => sprintf( esc_html_x( 'You have reached %1$s of %2$s point(s), (%3$s)', 'placeholder: points earned, points total', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0', '0' ),
					)
				)
			);
		?>
		</p>
		<p class="wpProQuiz_graded_points" style="display: none;">
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_earned_points_message',
						// translators: placeholder: points earned, points total, points percentage.
						'message'      => sprintf( esc_html_x( 'Earned Point(s): %1$s of %2$s, (%3$s)', 'placeholder: points earned, points total, points percentage', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0', '0' ),
					)
				)
			);
		?>
		<br />
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_essay_possible_points_message',
						// translators: placeholder: number of essays, possible points.
						'message'      => sprintf( esc_html_x( '%1$s Essay(s) Pending (Possible Point(s): %2$s)', 'placeholder: number of essays, possible points', 'learndash' ), '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0' ),
					)
				)
			);
		?>
		<br />
		</p>
		<?php
	}

	if ( is_user_logged_in() ) {
		?>
		<p class="wpProQuiz_certificate" style="display: none ;"><?php echo LD_QuizPro::certificate_link( '', $quiz ); ?></p>
		<?php echo LD_QuizPro::certificate_details( $quiz ); ?>
		<?php
	}

	if ( $quiz->isShowAverageResult() ) {
		?>
		<div class="wpProQuiz_resultTable">
			<table>
			<tbody>
			<tr>
				<td class="wpProQuiz_resultName">
				<?php
					echo wp_kses_post(
						SFWD_LMS::get_template(
							'learndash_quiz_messages',
							array(
								'quiz_post_id' => $quiz->getID(),
								'context'      => 'quiz_average_score_message',
								'message'      => esc_html__( 'Average score', 'learndash' ),
							)
						)
					);
				?>
				</td>
				<td class="wpProQuiz_resultValue wpProQuiz_resultValue_AvgScore">
					<div class="progress-meter" style="background-color: #6CA54C;">&nbsp;</div>
					<span class="progress-number">&nbsp;</span>
				</td>
			</tr>
			<tr>
			<td class="wpProQuiz_resultName">
			<?php
				echo wp_kses_post(
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_your_score_message',
							'message'      => esc_html__( 'Your score', 'learndash' ),
						)
					)
				);
			?>
			</td>
			<td class="wpProQuiz_resultValue wpProQuiz_resultValue_YourScore">
				<div class="progress-meter">&nbsp;</div>
				<span class="progress-number">&nbsp;</span>
			</td>
		</tr>
		</tbody>
		</table>
	</div>
		<?php
	}
	?>

	<div class="wpProQuiz_catOverview" <?php $quiz_view->isDisplayNone( $quiz->isShowCategoryScore() ); ?>>
		<h4>
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'learndash_categories_header',
					'message'      => esc_html__( 'Categories', 'learndash' ),
				)
			)
		);
		?>
		</h4>

		<div style="margin-top: 10px;">
			<ol>
			<?php
			foreach ( $quiz_view->category as $cat ) {
				if ( ! $cat->getCategoryId() ) {
					$cat->setCategoryName(
						wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'learndash_not_categorized_messages',
									'message'      => esc_html__( 'Not categorized', 'learndash' ),
								)
							)
						)
					);
				}
				?>
				<li data-category_id="<?php echo esc_attr( $cat->getCategoryId() ); ?>">
					<span class="wpProQuiz_catName"><?php echo esc_attr( $cat->getCategoryName() ); ?></span>
					<span class="wpProQuiz_catPercent">0%</span>
				</li>
				<?php
			}
			?>
			</ol>
		</div>
	</div>
	<div>
		<ul class="wpProQuiz_resultsList">
			<?php foreach ( $result['text'] as $resultText ) { ?>
				<li style="display: none;">
					<div>
						<?php echo do_shortcode( apply_filters( 'comment_text', $resultText, null, null ) ); ?>
						<?php // echo do_shortcode( apply_filters( 'the_content', $resultText, null, null ) ); ?>
					</div>
				</li>
			<?php } ?>
		</ul>
	</div>
	<?php
	if ( $quiz->isToplistActivated() ) {
		if ( $quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_NORMAL ) {
			echo do_shortcode( '[LDAdvQuiz_toplist ' . $quiz->getId() . ' q="true"]' );
		}

		$quiz_view->showAddToplist();
	}
	?>
	<div class="ld-quiz-actions" style="margin: 10px 0px; flex-wrap: wrap; gap: 20px;">
		<?php
			/**
			 *  See snippet https://developers.learndash.com/hook/show_quiz_continue_buttom_on_fail/
			 *
			 * @since 2.3.0.2
			 */
			$show_quiz_continue_buttom_on_fail = apply_filters( 'show_quiz_continue_buttom_on_fail', false, learndash_get_quiz_id_by_pro_quiz_id( $quiz->getId() ) );
		?>
		<div class='quiz_continue_link
		<?php
		if ( $show_quiz_continue_buttom_on_fail == true ) {
			echo ' show_quiz_continue_buttom_on_fail'; }
		?>
		'>

		</div>
		<?php
			$course_id = learndash_get_course_id( $quiz->getPostId() );
			if ( ! empty( $course_id ) ):
		?>
			<div id="mc-go-to-course-wrapper" style="width: 100%;">
				<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;" class="ld-quiz-next-quiz">
					<a style="text-decoration:none" href="<?php echo esc_url(learndash_get_course_url($course_id)); ?>" class="ld-button">
						<?php echo esc_html__('Go to Course', 'custom-quiz-types-for-multiclass'); ?>
					</a>
				</div>	
			</div>
		<?php
			endif;
		?>
		<?php if ( ! $quiz->isBtnRestartQuizHidden() ) { ?>
			<input class="wpProQuiz_button wpProQuiz_button_restartQuiz" type="button" name="restartQuiz"
					value="<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
						echo wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'quiz_restart_button_label',
									'message'      => sprintf(
										// translators: Restart Quiz Button Label.
										esc_html_x( 'Restart %s', 'Restart Quiz Button Label', 'learndash' ),
										LearnDash_Custom_Label::get_label( 'quiz' )
									),
								)
							)
						); // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect
							?>"/><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
			<?php
		}
		if ( ! $quiz->isBtnViewQuestionHidden() ) {
			?>
			<input class="wpProQuiz_button wpProQuiz_button_reShowQuestion" type="button" name="reShowQuestion"
					value="<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
						echo wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'quiz_view_questions_button_label',
									'message'      => sprintf(
										// translators: View Questions Button Label.
										esc_html_x( 'View %s', 'View Questions Button Label', 'learndash' ),
										LearnDash_Custom_Label::get_label( 'questions' )
									),
								)
							)
						); // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect
							?>" /><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
		<?php } ?>
		<?php if ( $quiz->isToplistActivated() && $quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_BUTTON ) { ?>
			<input class="wpProQuiz_button" type="button" name="showToplist"
			value="<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
				echo wp_kses_post(
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_show_leaderboard_button_label',
							'message'      => esc_html__( 'Show leaderboard', 'learndash' ),
						)
					)
				); // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect
					?>" /><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
		<?php } ?>

		<?php
		$course_id = learndash_get_course_id( $quiz->getPostId() );
		if ( ! empty( $course_id ) ) {
			$next_item = \Multiclass\Exam_Simulation::find_next_topic_or_quiz(wp_get_current_user(), $course_id, $quiz->getPostId());
			if ($next_item) {
				$next_quiz_link = get_permalink($next_item['ID']);
				?>
				<div style="display: flex;flex-direction: column;align-items: center;justify-content: center;width: 100%;" class="ld-quiz-next-quiz">
					<a style="text-decoration:none" href="<?php echo esc_url($next_quiz_link); ?>" class="ld-button">
						<?php echo sprintf(esc_html__('Go to Next %s', 'custom-quiz-types-for-multiclass'), $next_item['type'] ); ?>
					</a>
				</div>
				<?php
			}
		}
		?>
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
