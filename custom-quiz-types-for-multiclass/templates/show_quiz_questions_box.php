<?php
/**
 * Show Quiz Questions Box
 *
 * Available Variables:
 *
 * @var object $quiz_view      WpProQuiz_View_FrontQuiz instance.
 * @var object $quiz           WpProQuiz_Model_Quiz instance.
 * @var array  $shortcode_atts Array of shortcode attributes to create the Quiz.
 * @var int    $question_count Number of Question to display.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\Legacy\Quiz
 */

use Multiclass\Smart_Crop;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- we are inside of a template
$global_points = 0;
$json          = array();
$cat_points    = array();

if (!function_exists('isMobile')) {

    function isMobile() {

        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $mobilePhones = array(
            'iPhone', 'iPad', 'Android', 'Windows Phone', 'BlackBerry', 'Mobile', 'webOS', 'Opera Mini', 'IEMobile', 'Tablet'
        );

        foreach ($mobilePhones as $device) {
            if (strpos($userAgent, $device) !== false) {
                return true;
            }
        }

        return false;
    }

    function isTablet() {

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $tabletDevices = array(
            'iPad',               // iPads
            'Android',            // Android tablets
            'Tablet',             // Generic tablet
            'Galaxy Tab',         // Samsung Galaxy Tab
            'Kindle',             // Amazon Kindle
            'Nexus 7',            // Google Nexus tablets
            'PlayBook',           // BlackBerry PlayBook
            'Silk',               // Amazon Silk (for Kindle Fire)
        );

        foreach ($tabletDevices as $device) {
            if (strpos($userAgent, $device) !== false) {
                if (strpos($userAgent, 'Mobile') === false || strpos($userAgent, 'iPad') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    function theXY($width, $height, $space, $number, $total, $index) {

        // 7 & 5

        if ($total == 4) {
            $col = $number > 1 ? $number - 2 : $number;
            $row = $number > 1 ? 1 : 0;
        }

        if ($total == 6) {
            $col = $number > 2 ? $number - 3 : $number;
            $row = $number > 2 ? 1 : 0;
        }

        if ($total == 9) {
            $col = $number > 2 ? ($number > 5 ? $number - 6 : $number - 3) : $number;
            $row = $number > 2 ? ($number > 5 ? 2 : 1) : 0;
        }

        // Default case for other totals
        if (!isset($col)) {
            $col = $number % 3;
            $row = floor($number / 3);
        }

        if ($index == 7 || $index == 5) {
            $col = $number > 1 ? ($number > 3 ? $number - 4 : $number - 2) : $number;
            $row = $number > 1 ? ($number > 3 ? 2 : 1) : 0;
        }

        $x = $width*$col + $space*$col;
        $y = $height*$row + $space*$row;

        return ['x' => $x, 'y' => $y];
    }
}

$quiz_post_id = $quiz->getPostId();
$show_question_titles = get_post_meta($quiz_post_id, '_mc_show_question_titles', true);

// Get user's timer preference for this course
$user_id = get_current_user_id();
// Get the course ID associated with this quiz
$course_id = learndash_get_course_id($quiz_post_id);
if (empty($course_id)) {
    // Fallback: try to get course ID from URL or session if available
    if (isset($_GET['course_id'])) {
        $course_id = intval($_GET['course_id']);
    } elseif (WC()->session && WC()->session->get('mc_group_id')) {
        // Try to get course ID from the group
        $group_id = WC()->session->get('mc_group_id');
        $group_courses = learndash_group_enrolled_courses($group_id);
        if (!empty($group_courses)) {
            $course_id = $group_courses[0]; // Use first course in group
        }
    } else {
        $course_id = 0; // Set to 0 if we can't determine the course
    }
}

$timer_preference = get_user_meta($user_id, 'mc_timer_preference_' . $course_id, true);
if (empty($timer_preference)) {
    $timer_preference = 'with_timer'; // Default to with timer
}
?>

<div style="display: none;" class="wpProQuiz_quiz">
	<ol class="wpProQuiz_list">
		<?php
		$index = 0;
		foreach ( $quiz_view->question as $question ) {
			$index ++;
			$answer_array = $question->getAnswerData();

			$global_points += $question->getPoints();

			$mc_question_type = get_post_meta( $question->getQuestionPostId(), '_multiclass_question_type', true );

			if( 'pictogram' === $mc_question_type ) {
				$mc_question_type = 'concentration_numbers';
			}

			$mc_signature = '';

			switch( $mc_question_type ) {
				case 'concentration_numbers_short_term':
					$mc_correct_items = array_filter( $answer_array, function($item) {
						return $item->isCorrect();
					} );

					$mc_signature = reset( $mc_correct_items )->getAnswer();
				break;

				case 'customer_contact':
					foreach( $answer_array as $pos => $item ) {
						if( $item->isCorrect() ) {
							$mc_signature = $pos;
						}
					}
				break;
			}

			$json[ $question->getId() ]['type']             = $question->getAnswerType();
			$json[ $question->getId() ]['id']               = (int) $question->getId();
			$json[ $question->getId() ]['question_post_id'] = (int) $question->getQuestionPostId();
			$json[ $question->getId() ]['catId']            = (int) $question->getCategoryId(); // cspell:disable-line.

			if ( $question->isAnswerPointsActivated() && $question->isAnswerPointsDiffModusActivated() && $question->isDisableCorrect() ) {
				$json[ $question->getId() ]['disCorrect'] = (int) $question->isDisableCorrect();
			}

			if ( ! isset( $cat_points[ $question->getCategoryId() ] ) ) {
				$cat_points[ $question->getCategoryId() ] = 0;
			}

			$cat_points[ $question->getCategoryId() ] += $question->getPoints();

			if ( ! $question->isAnswerPointsActivated() ) {
				$json[ $question->getId() ]['points'] = $question->getPoints();
			}

			if ( $question->isAnswerPointsActivated() && $question->isAnswerPointsDiffModusActivated() ) {
				$json[ $question->getId() ]['diffMode'] = 1;
			}

			$question_meta = array(
				'type'             => $question->getAnswerType(),
				'question_pro_id'  => $question->getId(),
				'question_post_id' => $question->getQuestionPostId(),
			);

			$mc_time_limit_seconds = (int) get_post_meta( $question->getQuestionPostId(), '_mc_question_time_limit', true );
			$tri_coutdown_enabled = 'yes' === get_post_meta($question->getQuestionPostId(), '_mc_tri_coutdown_enabled', true);

			$has_autoload_modal = false;
			for ($modal_id = 1; $modal_id <= 3; $modal_id++) {
				$status = 'on' === get_post_meta($question->getQuestionPostId(), 'question_modal_' . $modal_id . '_status', true) && 'on' === get_post_meta($question->getQuestionPostId(), 'question_modal_' . $modal_id . '_auto_start', true);
				if ($status) {
					$has_autoload_modal = true;
					break;
				}
			}

			$points = $question->getPoints();

			$is_ungraded = $points === 0.0;

			// Determine if we should include timer-related attributes and classes
			$include_timer_attrs = ($timer_preference === 'with_timer');
			$timer_attr = $include_timer_attrs ? 'data-mc_time_limit="' . esc_attr( $mc_time_limit_seconds ) . '"' : '';
			$tri_coutdown_class = ($tri_coutdown_enabled && $include_timer_attrs) ? 'has-tri-coutdown' : '';
			?>
			<li data-course-id="<?php echo esc_attr($course_id); ?>" <?php echo $timer_attr; ?> data-mc_total_points="<?php echo esc_attr( $points ); ?>" data-mc_question_type="<?php echo esc_attr( $mc_question_type ); ?>" data-mc_signature="<?php echo esc_attr( $mc_signature ); ?>" class="wpProQuiz_listItem sp-question-<?php echo esc_attr( $question->getQuestionPostId() ); ?> mc-<?php echo esc_attr( $mc_question_type ); ?> <?php echo $tri_coutdown_class; ?><?php if( $has_autoload_modal ): ?> has-autoload-modal<?php endif;?><?php if( $is_ungraded ): ?> mc-ungraded<?php endif; ?>" style="display: none;" data-type="<?php echo esc_attr( $question->getAnswerType() ); ?>" data-question-meta="<?php echo htmlspecialchars( wp_json_encode( $question_meta ) ); ?>" <?php if('numerical_processing' === $mc_question_type): ?>data-np_interval="<?php echo esc_attr(get_post_meta($question->getQuestionPostId(), 'np_interval_ms', true)); ?>"<?php endif; ?>>
				<?php if( $include_timer_attrs && $mc_time_limit_seconds > 0 ): ?>
				<div class="timer-container hide">
						<?php esc_html_e( 'Verbleibende Zeit für die Frage:', 'custom-quiz-types-for-multiclass' ); ?>
						<span class="timer">0:00</span>
						<div class='times-up'></div>
				</div>

				<?php if( $include_timer_attrs && $tri_coutdown_enabled ): ?>
				<div class="coutdown">

				</div>
				<?php endif; ?>

				<?php endif; ?>
				<div class="wpProQuiz_question_page" <?php $quiz_view->isDisplayNone( $quiz->getQuizModus() != WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE && ! $quiz->isHideQuestionPositionOverview() ); ?> >
				<?php
					echo wp_kses_post(
						SFWD_LMS::get_template(
							'learndash_quiz_messages',
							array(
								'quiz_post_id' => $quiz->getID(),
								'context'      => 'quiz_question_list_2_message',
								'message'      => sprintf(
									// translators: placeholder: question, question number, questions total.
									esc_html_x( '%1$s %2$s of %3$s', 'placeholder: question, question number, questions total', 'learndash' ),
									learndash_get_custom_label( 'question' ),
									'<span>' . $index . '</span>',
									'<span>' . $question_count . '</span>'
								),
								'placeholders' => array( $index, $question_count ),
							)
						)
					);
				?>
				</div>
				<h5 style="<?php echo $quiz->isHideQuestionNumbering() ? 'display: none;' : 'display: inline-block;'; ?>" class="wpProQuiz_header">
					<?php
						echo wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'quiz_question_list_1_message',
									'message'      => '<span>' . $index . '</span>. ' . esc_html__( 'Question', 'learndash' ),
									'placeholders' => array( $index ),
								)
							)
						);
					?>

				</h5>

				<?php if ( $quiz->isShowPoints() ) { ?>
					<span
						style="font-weight: bold; float: right;">
						<?php
						echo wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'quiz_question_points_message',
									// translators: placeholder: total quiz points.
									'message'      => sprintf( esc_html_x( '%s point(s)', 'placeholder: total quiz points', 'learndash' ), '<span>' . $question->getPoints() . '</span>' ),
									'placeholders' => array( $question->getPoints() ),
								)
							)
						);

						?>
						</span>
					<div style="clear: both;"></div>
				<?php } ?>

				<?php if ( $question->getCategoryId() && $quiz->isShowCategory() ) { ?>
					<div style="font-weight: bold; padding-top: 5px;">
						<?php
							echo wp_kses_post(
								SFWD_LMS::get_template(
									'learndash_quiz_messages',
									array(
										'quiz_post_id' => $quiz->getID(),
										'context'      => 'quiz_question_category_message',
										// translators: placeholder: Quiz Category.
										'message'      => sprintf( esc_html_x( 'Category: %s', 'placeholder: Quiz Category', 'learndash' ), '<span>' . esc_html( $question->getCategoryName() ) . '</span>' ),
										'placeholders' => array( esc_html( $question->getCategoryName() ) ),
									)
								)
							);
						?>
					</div>
				<?php } ?>
				<?php
				if ('yes' === $show_question_titles) {
					?>
					<div class="question-title">
						<h5><?php echo esc_html($question->getTitle()); ?></h5>
					</div>
					<?php
				}
				?>
				<div class="wpProQuiz_question<?php if( $include_timer_attrs && $tri_coutdown_enabled ): ?> hide<?php endif;?>" style="margin: 10px 0px 0px 0px;">
					<div class="wpProQuiz_question_text">
						<?php
						if ('customer_contact' === $mc_question_type):
							$background_image = wp_get_attachment_url(get_post_thumbnail_id($question->getQuestionPostId()));

							$image_metadata = wp_get_attachment_metadata(get_post_thumbnail_id($question->getQuestionPostId()));

							if ($image_metadata) {
								$image_width = $image_metadata['width'];
								$image_height = $image_metadata['height'];
							}

							$coordinates = get_post_meta($question->getQuestionPostId(), '_speech_bubble_coordinates', true);
							$coordinates = json_decode($coordinates, true);

							$x = isset($coordinates['x']) ? esc_attr($coordinates['x']) : '';
							$y = isset($coordinates['y']) ? esc_attr($coordinates['y']) : '';
							$width = isset($coordinates['width']) ? esc_attr($coordinates['width']) : '';
							$height = isset($coordinates['height']) ? esc_attr($coordinates['height']) : '';
							$position = isset($coordinates['position']) ? esc_attr($coordinates['position']) : 'left';

							// Calculate scale ratios for each breakpoint
							$tablet_ratio = 0.75;
							$mobile_ratio = 0.5;

							$unique_id = uniqid();
						?>

						<style>
							.wpProQuiz_question_text > p {
								display: none !important;
							}

							#speech-bubble-container-<?php echo esc_attr($unique_id); ?> {
								position: relative;
								width: <?php echo esc_attr($image_width); ?>px;
								height: <?php echo esc_attr($image_height); ?>px;
								background-size: contain;
								background-repeat: no-repeat;
								background-image: url(<?php echo esc_url($background_image); ?>);
							}

							#speech-bubble-<?php echo esc_attr($unique_id); ?> {
								position: absolute;
								width: <?php echo esc_attr($width); ?>px;
								height: <?php echo esc_attr($height); ?>px;
								left: 0;
								top: 0;
								transform: translate(<?php echo esc_attr($x); ?>px, <?php echo esc_attr($y); ?>px);
							}

							/* Tablet breakpoint */
							@media (max-width: 768px) {
								#speech-bubble-container-<?php echo esc_attr($unique_id); ?> {
									width: <?php echo esc_attr($image_width * $tablet_ratio); ?>px;
									height: <?php echo esc_attr($image_height * $tablet_ratio); ?>px;
									padding: 5px;
								}

								#speech-bubble-<?php echo esc_attr($unique_id); ?> {
									width: <?php echo esc_attr($width * $tablet_ratio); ?>px;
									height: <?php echo esc_attr($height * $tablet_ratio); ?>px;
									transform: translate(<?php echo esc_attr($x * $tablet_ratio); ?>px, <?php echo esc_attr($y * $tablet_ratio); ?>px);
								}

								#speech-bubble-<?php echo esc_attr($unique_id); ?> p {
									font-size: 12px !important;
									line-height: 12px;
								}
							}

							/* Mobile breakpoint */
							@media (max-width: 480px) {
								#speech-bubble-container-<?php echo esc_attr($unique_id); ?> {
									width: <?php echo esc_attr($image_width * $mobile_ratio); ?>px;
									height: <?php echo esc_attr($image_height * $mobile_ratio); ?>px;
								}

								#speech-bubble-<?php echo esc_attr($unique_id); ?> {
									width: <?php echo esc_attr($width * $mobile_ratio); ?>px;
									height: <?php echo esc_attr($height * $mobile_ratio); ?>px;
									transform: translate(<?php echo esc_attr($x * $mobile_ratio); ?>px, <?php echo esc_attr($y * $mobile_ratio); ?>px);
									padding: 2px;
								}

								#speech-bubble-<?php echo esc_attr($unique_id); ?> p {
									font-size: 12px !important;
									line-height: 12px;
								}
							}
						</style>

						<div id="speech-bubble-container-<?php echo esc_attr($unique_id); ?>">
							<div id="speech-bubble-<?php echo esc_attr($unique_id); ?>" class="speech-bubble <?php echo esc_attr($position); ?>">
								<p><?php echo esc_html($question->getTipMsg()); ?></p>
							</div>
						</div>

						<?php endif; ?>
						<?php
							// Render audio player from meta for audio question type
							if ('audio' === $mc_question_type):
								$audio_instance = new \Multiclass\Audio();
								echo $audio_instance->render_audio_from_meta($question->getQuestionPostId(), $course_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Audio player HTML is escaped in the method
							endif;
						?>
						<?php
							$wpproquiz_question_text = $question->getQuestion();
							$wpproquiz_question_text = sanitize_post_field( 'post_content', $wpproquiz_question_text, 0, 'display' );
							$wpproquiz_question_text = wpautop( $wpproquiz_question_text );
							global $wp_embed;
							$wpproquiz_question_text = $wp_embed->run_shortcode( $wpproquiz_question_text );
							$wpproquiz_question_text = do_shortcode( $wpproquiz_question_text );
							
							// For audio question type, remove any audio shortcodes from the question text
							if ('audio' === $mc_question_type) {
								$wpproquiz_question_text = preg_replace('/\[mc_audio_player[^\]]*\]/i', '', $wpproquiz_question_text);
								$wpproquiz_question_text = trim($wpproquiz_question_text);
							}
							
							echo $wpproquiz_question_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to allow HTML / shortcode output
						?>
					</div>
					<p class="wpProQuiz_clear" style="clear:both;"></p>

					<?php
					/**
					 * Matrix Sort Answer
					 */
					?>
					<?php if ( $question->getAnswerType() === 'matrix_sort_answer' ) { 

						?>

						<div class="wpProQuiz_matrixSortString">
							<h5 class="wpProQuiz_header">
							<?php
							echo wp_kses_post(
								SFWD_LMS::get_template(
									'learndash_quiz_messages',
									array(
										'quiz_post_id' => $quiz->getID(),
										'context'      => 'quiz_question_sort_elements_header',
										'message'      => esc_html__( 'Sort elements', 'learndash' ),
									)
								)
							);
							?>
							</h5>
							<ul class="wpProQuiz_sortStringList">
							<?php
							$answer_array_new_matrix = array();
							foreach ( $answer_array as $q_idx => $q ) {
								$datapos                             = LD_QuizPro::datapos( $question->getId(), $q_idx );
								$answer_array_new_matrix[ $datapos ] = $q;
							}

							$matrix = array();
							foreach ( $answer_array as $k => $v ) {
								$matrix[ $k ][] = $k;

								foreach ( $answer_array as $k2 => $v2 ) {
									if ( $k != $k2 ) {
										if ( $v->getAnswer() == $v2->getAnswer() ) {
											$matrix[ $k ][] = $k2;
										} elseif ( $v->getSortString() == $v2->getSortString() ) {
											$matrix[ $k ][] = $k2;
										}
									}
								}
							}

							foreach ( $answer_array as $k => $v ) {
								?>
								<li class="wpProQuiz_sortStringItem" data-pos="<?php echo esc_attr( $k ); ?>">
								<?php echo $v->isSortStringHtml() ? do_shortcode( nl2br( $v->getSortString() ) ) : esc_html( $v->getSortString() ); ?>
								</li>
								<?php
							}

								$original_answer_array = $answer_array;
								$answer_array = $answer_array_new_matrix;

								$url = $original_answer_array[0]->getSortString();

								if( studypeak_is_development() ) {
									$url = str_replace( 'https://studypeak.ch', 'http://sp.local', $url );
									$url = str_replace( 'https://multiclass.ch', 'http://sp.local', $url );
								}
							?>
							</ul>
							<?php
								$have_img = count( array_filter( $original_answer_array, function($v) {
									return false !== wp_http_validate_url($v->getSortString());
								} ))>0;
							?>
							<?php if( $mc_question_type !== 'default' && $mc_question_type !== '' ): ?>
							<div class="mc-image-drag-drop-container <?php echo esc_attr( $have_img ? 'img-options' : 'text-options' ); ?>">
								<?php
									$background_image = '';
									$image_width = 300;
									$image_height = 300;
                                    $imagesQuantity = sizeof($original_answer_array);

                                    $isImg = false !== wp_http_validate_url($url);

                                    if( $isImg ) {
                                        $imageUrl = $url;
                                        $imageSize = getimagesize($imageUrl);
                                        $originalWidth = (int) $imageSize[0];
                                        $originalHeight = $imageSize[1];

                                        if( $originalWidth > 1 ) {
                                            $aspectRatio = $originalHeight / $originalWidth;
                                        }
                                    }

									if (has_post_thumbnail($question->getQuestionPostId())) {
										$background_image = wp_get_attachment_url(get_post_thumbnail_id($question->getQuestionPostId()));
										
										$image_metadata = wp_get_attachment_metadata(get_post_thumbnail_id($question->getQuestionPostId()));

										if ($image_metadata) {
											$image_width = $image_metadata['width'];
											$image_height = $image_metadata['height'];
                                            $originalWidth = $image_width;
										}
									}
								?>
								<div class="drag-drop-target-image drag-drop-target-image_<?php echo $imagesQuantity;?>"
                                    style="position:relative;
									background:url('<?php echo esc_url( $background_image ); ?>');
									background-repeat:no-repeat;
									background-size: contain;
									background-position: center;
									aspect-ratio: <?php echo esc_attr($image_width); ?> / <?php echo esc_attr($image_height); ?>;
									height: auto;
                                    ">
                                    <?php
                                    // Randomize answer_array while preserving original keys
                                    $keys = array_keys($answer_array);
                                    shuffle($keys);
                                    $shuffled_answer_array = array();
                                    foreach($keys as $key) {
                                        $shuffled_answer_array[$key] = $answer_array[$key];
                                    }
                                    $answer_array = $shuffled_answer_array;
                                    
                                    $number = 0;
                                    foreach ( $answer_array as $k => $v ) {
                                        $position =  json_decode( $v->getAnswer( ) );

                                        // Get normalize settings
                                        $show_normalize = get_post_meta($question->getQuestionPostId(), '_mc_show_normalize', true);
                                        $normalize_width = get_post_meta($question->getQuestionPostId(), '_mc_normalize_width', true);
                                        $normalize_height = get_post_meta($question->getQuestionPostId(), '_mc_normalize_height', true);

                                        // Determine dimensions based on normalize settings
                                        if ($show_normalize === '1' && $normalize_width && $normalize_height) {
                                            $width = $normalize_width;
                                            $height = $normalize_height;
                                        } else {
                                            if( is_object( $position ) ) {
                                                $width = $position->width;
                                                $height = $position->height;
                                            }
                                        }

                                        $y = $position->y;
                                        $x = $position->x;

										$y_percentage = 100 * $y / $image_height;
										$x_percentage = 100 * $x / $image_width;
										$width_percentage = 100 * $width / $image_width;
										$height_percentage = 100 * $height / $image_height;
                                        ?>
                                        <div data-signature="<?php echo esc_attr($k); ?>"
                                            class="dropzone dropzone_<?php echo $imagesQuantity;?> <?php 
                                                // Add resize-disabled class if resize is disabled
                                                $enable_resize = get_post_meta($question->getQuestionPostId(), '_mc_enable_resize', true);
                                                if ($enable_resize === '0') {
                                                    echo 'resize-disabled';
                                                }
                                            ?>"
                                            id="<?php echo $index . '-' . $number;?>"
                                            style="z-index:9 !important;
                                            width: <?php echo $width_percentage; ?>%; height: <?php echo $height_percentage; ?>%;top:0;
                                            position:absolute;
											left:<?php echo $x_percentage; ?>%;
											top:<?php echo $y_percentage; ?>%;
                                            transform:translate(0, 0);
                                            background-repeat:no-repeat; background-size:contain"></div>
                                        <?php
                                        $number++;
                                    }
                                    ?>
								</div>

								<div class="mc-image-dropdowns-answers-wrapper" style="padding-left: 10px;">
                                    <div
                                        id="initialContainer"
										style="<?php if( $have_img ): ?> aspect-ratio: <?php echo esc_attr($image_width); ?> / <?php echo esc_attr($image_height); ?>;<?php endif; ?>width: 100%; max-width:<?php echo isMobile() ? $mobileContainerWidth : esc_attr($image_width); ?>px;-webkit-transform: none !important;"
                                        class="container mc-image-dropdowns-answers <?php echo isMobile() ? 'container-mobile' : ''?> <?php 
                                            // Add resize-disabled class if resize is disabled
                                            $enable_resize = get_post_meta($question->getQuestionPostId(), '_mc_enable_resize', true);
                                            if ($enable_resize === '0') {
                                                echo 'resize-disabled';
                                            }
                                        ?>"
                                    >
                                        <?php 
                                        // Randomize original_answer_array while preserving original keys
                                        $original_keys = array_keys($original_answer_array);
                                        shuffle($original_keys);
                                        $shuffled_original_array = array();
                                        foreach($original_keys as $key) {
                                            $shuffled_original_array[$key] = $original_answer_array[$key];
                                        }
                                        $original_answer_array = $shuffled_original_array;
                                        
                                        // Normalize image dimensions if they are within 5% of each other
                                        if ($have_img) {
                                            $dimensions = array();
                                            
                                            // Collect all image dimensions
                                            foreach ($original_answer_array as $k => $v) {
                                                $is_img = false !== wp_http_validate_url($v->getSortString());
                                                if ($is_img) {
                                                    $position = json_decode($v->getAnswer());
                                                    
                                                    // Get normalize settings
                                                    $show_normalize = get_post_meta($question->getQuestionPostId(), '_mc_show_normalize', true);
                                                    $normalize_width = get_post_meta($question->getQuestionPostId(), '_mc_normalize_width', true);
                                                    $normalize_height = get_post_meta($question->getQuestionPostId(), '_mc_normalize_height', true);
                                                    
                                                    // Determine dimensions based on normalize settings
                                                    if ($show_normalize === '1' && $normalize_width && $normalize_height) {
                                                        $originalWidth = $normalize_width;
                                                        $originalHeight = $normalize_height;
                                                    } else {
                                                        if (is_object($position)) {
                                                            $originalWidth = $position->width;
                                                            $originalHeight = $position->height;
                                                        }
                                                    }
                                                    
                                                    if (isset($originalWidth) && isset($originalHeight)) {
                                                        $dimensions[$k] = array(
                                                            'width' => $originalWidth,
                                                            'height' => $originalHeight
                                                        );
                                                    }
                                                }
                                            }
                                            
                                            // Check if dimensions are within 5% of each other
                                            if (count($dimensions) > 1) {
                                                $widths = array_column($dimensions, 'width');
                                                $heights = array_column($dimensions, 'height');
                                                
                                                $max_width = max($widths);
                                                $min_width = min($widths);
                                                $max_height = max($heights);
                                                $min_height = min($heights);
                                                
                                                // Calculate percentage difference (relative to average)
                                                $avg_width_calc = ($max_width + $min_width) / 2;
                                                $avg_height_calc = ($max_height + $min_height) / 2;
                                                $width_diff_percent = (($max_width - $min_width) / $avg_width_calc) * 100;
                                                $height_diff_percent = (($max_height - $min_height) / $avg_height_calc) * 100;
                                                
                                                // Debug: uncomment to see calculation values
                                                error_log("Width diff: {$width_diff_percent}% | Height diff: {$height_diff_percent}% | Max W: {$max_width} | Min W: {$min_width} | Max H: {$max_height} | Min H: {$min_height}");
                                                
                                                // If within threshold, normalize to average dimensions
                                                if ($width_diff_percent <= 5 && $height_diff_percent <= 5) {
                                                    $avg_width = array_sum($widths) / count($widths);
                                                    $avg_height = array_sum($heights) / count($heights);
                                                    
                                                    // Store normalized dimensions
                                                    $normalized_dimensions = array(
                                                        'width' => round($avg_width),
                                                        'height' => round($avg_height)
                                                    );
                                                } else {
                                                    $normalized_dimensions = null;
                                                }
                                            } else {
                                                $normalized_dimensions = null;
                                            }
                                        }
                                        
                                        foreach ( $original_answer_array as $k => $v ):

											$is_img = false !== wp_http_validate_url($v->getSortString());

                                            if( $is_img ):
                                            $imageUrl = $v->getSortString();

											if( studypeak_is_development() ) {
												$imageUrl = str_replace( 'https://studypeak.ch', 'http://sp.local', $imageUrl );
												$imageUrl = str_replace( 'https://multiclass.ch', 'http://sp.local', $imageUrl );
											}

											$position =  json_decode( $v->getAnswer( ) );

                                            // Get normalize settings
                                            $show_normalize = get_post_meta($question->getQuestionPostId(), '_mc_show_normalize', true);
                                            $normalize_width = get_post_meta($question->getQuestionPostId(), '_mc_normalize_width', true);
                                            $normalize_height = get_post_meta($question->getQuestionPostId(), '_mc_normalize_height', true);

                                            // Determine dimensions based on normalize settings or auto-normalized dimensions
                                            if (isset($normalized_dimensions) && $normalized_dimensions !== null) {
                                                // Use auto-normalized dimensions (when images are within 5% of each other)
                                                $originalWidth = $normalized_dimensions['width'];
                                                $originalHeight = $normalized_dimensions['height'];
                                            } elseif ($show_normalize === '1' && $normalize_width && $normalize_height) {
                                                $originalWidth = $normalize_width;
                                                $originalHeight = $normalize_height;
                                            } else {
                                                if( is_object( $position ) ) {
                                                    $originalWidth = $position->width;
                                                    $originalHeight = $position->height;
                                                }
                                            }

											$width_percentage = 100 * $originalWidth / $image_width;
											$height_percentage = 100 * $originalHeight / $image_height;
											?>
											<div originalwidth="<?php echo $originalWidth; ?>" originalheight="<?php echo $originalHeight; ?>" class="shake-wrapper" style="width:<?php echo $width_percentage; ?>% !important; height:<?php echo $height_percentage; ?>% !important;">
                                            <div data-signature="<?php echo esc_attr($k); ?>"
                                                data-width="<?php echo $width_percentage; ?>%"
                                                data-height="<?php echo $height_percentage; ?>%"
                                                style="display: inline-block;background:url('<?php echo \Multiclass\Smart_Crop::cached_crop_to_largest_component( $imageUrl ); ?>'); width:100%; height:100%; background-size:100% 100%; background-repeat:no-repeat;"
                                                class="draggable draggable-image_<?php echo $imagesQuantity;?>"
                                                draggable="true" id="mc-image-drag-drop-<?php echo esc_html( $question->getQuestionPostId() ); ?>-<?php echo esc_attr($k); ?>"></div>
										</div>
                                        <?php else: ?>
                                            <div data-signature="<?php echo esc_attr($k); ?>" class="draggable" draggable="true" id="mc-image-drag-drop-<?php echo esc_html( $question->getQuestionPostId() ); ?>-<?php echo esc_attr($k); ?>"><?php echo esc_attr( $v->getSortString() ); ?></div>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

								</div>
							</div>
							<?php endif; ?>
							<div style="clear: both;"></div>
						</div>
					<?php } ?>

					<?php
					/**
					 * Print questions in a list for all other answer types
					 */
					?>
					<ul class="wpProQuiz_questionList" data-question_id="<?php echo esc_attr( $question->getId() ); ?>"
						data-type="<?php echo esc_attr( $question->getAnswerType() ); ?>">

						<?php if( 'numerical_processing' === $mc_question_type ): ?>
							<div class="grid">
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
								<div></div>
							</div>
						<?php endif; ?>

						<?php if( 'different_perspective' === $mc_question_type ): ?>
							<?php
								$img = wp_get_attachment_url(get_post_thumbnail_id($question->getQuestionPostId()));
							?>
							<div class="perspective-container">
								<div class="inner-container" style="background:url('<?php echo $img; ?>');">
									<?php foreach( [0, 45, 90, 135, 180, 225, 270, 315] as $degree ): ?>
										<div class="arrow arrow-<?php echo esc_attr( $degree ); ?> <?php echo $degree%90!==0 ? esc_attr( 'long' ) : ''; ?>" data-degree="<?php echo esc_attr( $degree ); ?>"><span class="material-symbols-outlined">arrow_downward</span></div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if( 'customer_contact' === $mc_question_type ): ?>
							<div class="mc-cc-error-message">
								<div class="mc-cc-errors">
								<?php
								$invalid_answer_messages = get_post_meta( $question->getQuestionPostId(), '_mc_cc_incorrect_answers', true );
								foreach( $answer_array as $pos => $item ) {
									?>
									<div class="mc-cc-err mc-cc-err-<?php echo esc_attr( $pos ); ?>">
										<?php echo esc_html( $invalid_answer_messages[$pos] ); ?>
									</div>
									<?php
								}
								?>
								</div>
								<button class="mc-err-back"><?php esc_html_e( 'Back', 'custom-quiz-types-for-multiclass' ); ?></button>
							</div>
						<?php endif; ?>

						<?php
						if ( $question->getAnswerType() === 'sort_answer' ) {
							$answer_array_new = array();
							foreach ( $answer_array as $q_idx => $q ) {
								$datapos                      = LD_QuizPro::datapos( $question->getId(), $q_idx );
								$answer_array_new[ $datapos ] = $q;
							}
							$answer_array = $answer_array_new;

							if ( $question->getAnswerType() === 'sort_answer' ) {
								$answer_array_org_keys = array_keys( $answer_array );

								/**
								 * Do this while the answer keys match. I just don't trust shuffle to always
								 * return something other than the original.
								 */
								$random_tries = 0;
								while ( true ) {
									// Backup so we don't get stuck because some plugin rewrote a function we are using.
									++$random_tries;

									$answer_array_randon_keys = $answer_array_org_keys;
									shuffle( $answer_array_randon_keys );
									$answer_array_keys_diff = array_diff_assoc( $answer_array_org_keys, $answer_array_randon_keys );

									// If the diff array is not empty or we have reaches enough tries, abort.
									if ( ( ! empty( $answer_array_keys_diff ) ) || ( $random_tries > 10 ) ) {
										break;
									}
								}

								$answer_array_new = array();
								foreach ( $answer_array_randon_keys as $q_idx ) {
									if ( isset( $answer_array[ $q_idx ] ) ) {
										$answer_array_new[ $q_idx ] = $answer_array[ $q_idx ];
									}
								}
								$answer_array = $answer_array_new;
							}
						}

						$answer_index = 0;
						if ( is_array( $answer_array ) ) {
							foreach ( $answer_array as $v_idx => $v ) {
								$answer_text = $v->isHtml() ? do_shortcode( nl2br( $v->getAnswer() ) ) : esc_html( $v->getAnswer() );

								if ( '' == $answer_text && ! $v->isGraded() ) {
									continue;
								}

								if ( $question->isAnswerPointsActivated() ) {
									$json[ $question->getId() ]['points'][] = $v->getPoints();
								}

								$datapos = $answer_index;
								if ( $question->getAnswerType() === 'sort_answer' || $question->getAnswerType() === 'matrix_sort_answer' ) {
									$datapos = $v_idx; // LD_QuizPro::datapos( $question->getId(), $answer_index );
								}
								?>

								<li class="wpProQuiz_questionListItem <?php echo esc_attr('numerical_processing' === $mc_question_type ? 'v-hide' : ''); ?>" data-pos="<?php echo esc_attr( $datapos ); ?>">
									<?php
									/**
									 *  Single/Multiple
									 */
									if ( $question->getAnswerType() === 'single' || $question->getAnswerType() === 'multiple' ) {
										$json[ $question->getId() ]['correct'][] = (int) $v->isCorrect();
										?>
										<span <?php echo $quiz->isNumberedAnswer() ? '' : 'style="display:none;"'; ?>></span>
										<label>
											<input class="wpProQuiz_questionInput" autocomplete="off"
													type="<?php echo $question->getAnswerType() === 'single' ? 'radio' : 'checkbox'; ?>"
													name="question_<?php echo esc_attr( $quiz->getId() ); ?>_<?php echo esc_attr( $question->getId() ); ?>"
													value="<?php echo esc_attr( ( $answer_index + 1 ) ); ?>"> <?php echo $answer_text; ?>
										</label>

										<?php
										/**
										 *  Sort Answer
										 */
									} elseif ( $question->getAnswerType() === 'sort_answer' ) {
										$json[ $question->getId() ]['correct'][] = (int) $answer_index;
										?>
										<div class="wpProQuiz_sortable">
											<?php echo $answer_text; ?>
										</div>

										<?php
										/**
										 *  Free Answer
										 */
									} elseif ( $question->getAnswerType() === 'free_answer' ) {
										$question_answer_data = learndash_question_free_get_answer_data( $v, $question );
										if ( ( is_array( $question_answer_data ) ) && ( ! empty( $question_answer_data ) ) ) {
											$json[ $question->getId() ] = array_merge( $json[ $question->getId() ], $question_answer_data );
										}

										$free_text_type = get_post_meta( $question->getQuestionPostId(), 'mc_fc_text_type', true );
										?>
										<?php if( 'numerical_processing' === $mc_question_type ): ?>
											<p class="solution-title"><?php esc_html_e( 'Lösung', 'custom-quiz-types-for-multiclass' ); ?></p>
											<label>
											<input class="mc-num-pc-answer v-hide" type="number" autocomplete="off" >
											<span class="wpProQuiz_freeCorrect" style="display:none"></span>
										</label>
										<?php endif; ?>
										<label>
											<?php if( empty( $free_text_type ) || 'single' === $free_text_type ): ?>
												<input class="wpProQuiz_questionInput" type="text" autocomplete="off" name="question_<?php echo esc_attr( $quiz->getId() ); ?>_<?php echo esc_attr( $question->getId() ); ?>" />
											<?php elseif( 'multi' === $free_text_type ): ?>
												<textarea rows="6" class="wpProQuiz_questionInput" autocomplete="off" name="question_<?php echo esc_attr( $quiz->getId() ); ?>_<?php echo esc_attr( $question->getId() ); ?>"></textarea>
											<?php endif; ?>
											<span class="wpProQuiz_freeCorrect" style="display:none"></span>
										</label>

										<?php
										/**
										 *  Matrix Sort Answer
										 */
									} elseif ( $question->getAnswerType() === 'matrix_sort_answer' ) {
										$json[ $question->getId() ]['correct'][] = (int) $answer_index;
										$msacw_value                             = $question->getMatrixSortAnswerCriteriaWidth() > 0 ? $question->getMatrixSortAnswerCriteriaWidth() : 20;
										?>
										<table>
											<tbody>
											<tr class="wpProQuiz_mextrixTr">
												<td width="<?php echo esc_attr( $msacw_value ); ?>%">
													<div
														class="wpProQuiz_maxtrixSortText"><?php echo $answer_text; ?></div>
												</td>
												<td width="<?php echo esc_attr( 100 - $msacw_value ); ?>%">
													<ul class="wpProQuiz_maxtrixSortCriterion"></ul>
												</td>
											</tr>
											</tbody>
										</table>

										<?php
										/**
										 *  Cloze Answer
										 */
									} elseif ( $question->getAnswerType() === 'cloze_answer' ) {
										$cloze_data   = learndash_question_cloze_fetch_data( $v->getAnswer() );
										$cloze_output = learndash_question_cloze_prepare_output( $cloze_data );
										echo $cloze_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

										$json[ $question->getId() ]['correct'] = isset( $cloze_data['correct'] ) ? $cloze_data['correct'] : [];

										if ( $question->isAnswerPointsActivated() ) {
											$json[ $question->getId() ]['points'] = $cloze_data['points'];
										}

										/**
										 *  Assessment answer
										 */
									} elseif ( $question->getAnswerType() === 'assessment_answer' ) {

										$assessment_data = learndash_question_assessment_fetch_data( $v->getAnswer(), $quiz->getId(), $question->getId() );

										$json[ $question->getId() ]['correct'] = isset( $assessment_data['correct'] ) ? $assessment_data['correct'] : [];

										if ( $question->isAnswerPointsActivated() ) {
											$json[ $question->getId() ]['points'] = $assessment_data['points'];
										}

										$assessment_output = learndash_question_assessment_prepare_output( $assessment_data );
										echo $assessment_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Need to output HTML / Shortcodes

										/**
										 *Essay answer
										 */
									} elseif ( $question->getAnswerType() === 'essay' ) {
										if ( $v->getGradedType() === 'text' ) :
											// Check if word counter is enabled for this question
											$word_counter_enabled = \Multiclass\Essay_Word_Counter::is_word_counter_enabled( $question->getQuestionPostId() );
											$textarea_class = $word_counter_enabled ? 'wpProQuiz_questionEssay sp-has-word-counter' : 'wpProQuiz_questionEssay';
											?>
											<textarea class="<?php echo esc_attr( $textarea_class ); ?>" rows="10" cols="40"
												name="question_<?php echo esc_attr( $quiz->getId() ); ?>_<?php echo esc_attr( $question->getId() ); ?>"
												id="wpProQuiz_questionEssay_question_<?php echo esc_attr( $quiz->getId() ); ?>_<?php echo esc_attr( $question->getId() ); ?>"
												cols="30" autocomplete="off"
												rows="10"
												data-question-id="<?php echo esc_attr( $question->getId() ); ?>"
												placeholder="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
													SFWD_LMS::get_template(
														'learndash_quiz_messages',
														array(
															'quiz_post_id' => $quiz->getID(),
															'context'      => 'quiz_essay_question_textarea_placeholder_message',
															'message'      => esc_html__( 'Type your response here', 'learndash' ),
														)
													)
												); ?>"></textarea> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,Squiz.PHP.EmbeddedPhp.ContentAfterEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
											<?php if ( $word_counter_enabled ) : ?>
												<div class="sp-essay-word-counter" data-target-question-id="<?php echo esc_attr( $question->getId() ); ?>">
													<span class="sp-word-counter-label"><?php esc_html_e( 'Wörter:', 'custom-quiz-types-for-multiclass' ); ?></span>
													<span class="sp-word-count">0</span>
												</div>
											<?php endif; ?>
											<?php elseif ( $v->getGradedType() === 'upload' ) : ?>
												<?php
													echo wp_kses_post(
														SFWD_LMS::get_template(
															'learndash_quiz_messages',
															array(
																'quiz_post_id' => $quiz->getID(),
																'context'      => 'quiz_essay_question_upload_answer_message',
																'message'      => '<p>' . esc_html__( 'Upload your answer to this question.', 'learndash' ) . '</p>',
															)
														)
													);
												?>
												<form enctype="multipart/form-data" method="post" name="uploadEssay">
													<input type='file' name='uploadEssay[]' id='uploadEssay_<?php echo esc_attr( $question->getId() ); ?>' size='35' class='wpProQuiz_upload_essay' />
													<input type="submit" id='uploadEssaySubmit_<?php echo esc_attr( $question->getId() ); ?>' value="<?php esc_html_e( 'Upload', 'learndash' ); ?>" />
													<input type="hidden" id="_uploadEssay_nonce_<?php echo esc_attr( $question->getId() ); ?>" name="_uploadEssay_nonce" value="<?php echo esc_attr( wp_create_nonce( 'learndash-upload-essay-' . $question->getId() ) ); ?>" />
													<input type="hidden" class="uploadEssayFile" id='uploadEssayFile_<?php echo esc_attr( $question->getId() ); ?>' value="" />
												</form>
												<div id="uploadEssayMessage_<?php echo esc_attr( $question->getId() ); ?>" class="uploadEssayMessage"></div>
											<?php else : ?>
												<?php esc_html_e( 'Essay type not found', 'learndash' ); ?>
											<?php endif; ?>

											<p class="graded-disclaimer">
												<?php if ( 'graded-full' == $v->getGradingProgression() ) : ?>
													<?php
													echo wp_kses_post(
														SFWD_LMS::get_template(
															'learndash_quiz_messages',
															array(
																'quiz_post_id' => $quiz->getID(),
																'context'      => 'quiz_essay_question_graded_full_message',
																'message'      => esc_html__( 'This response will be awarded full points automatically, but it can be reviewed and adjusted after submission.', 'learndash' ),
															)
														)
													);
													?>
												<?php elseif ( 'not-graded-full' == $v->getGradingProgression() ) : ?>
													<?php
														echo wp_kses_post(
															SFWD_LMS::get_template(
																'learndash_quiz_messages',
																array(
																	'quiz_post_id' => $quiz->getID(),
																	'context'      => 'quiz_essay_question_not_graded_full_message',
																	'message'      => esc_html__( 'This response will be awarded full points automatically, but it will be reviewed and possibly adjusted after submission.', 'learndash' ),
																)
															)
														);
													?>
												<?php elseif ( 'not-graded-none' == $v->getGradingProgression() ) : ?>
													<?php
														echo wp_kses_post(
															SFWD_LMS::get_template(
																'learndash_quiz_messages',
																array(
																	'quiz_post_id' => $quiz->getID(),
																	'context'      => 'quiz_essay_question_not_graded_none_message',
																	'message'      => esc_html__( 'This response will be reviewed and graded after submission.', 'learndash' ),
																)
															)
														);
													?>
												<?php endif; ?>
											</p>
										<?php
									}

									?>
								</li>
								<?php
								$answer_index ++;
							}
						}
						?>
					</ul>
					<?php if ( $question->getAnswerType() === 'sort_answer' ) { ?>
						<div class="wpProQuiz_questionList_containers">
							<p><?php esc_html_e( 'View Answers', 'learndash' ); ?>: <input type="button" class="wpProQuiz_questionList_containers_view_student wpProQuiz_questionList_containers_view_active wpProQuiz_button2" value="<?php esc_html_e( 'Student', 'learndash' ); ?>"> <input type="button" class="wpProQuiz_questionList_containers_view_correct wpProQuiz_button2" value="<?php esc_html_e( 'Correct', 'learndash' ); ?>" /></p>
							<div class="wpProQuiz_questionList_container_student"></div>
							<div class="wpProQuiz_questionList_container_correct"></div>
						</div>
					<?php } ?>
				</div>

				<div class="mc-quiz-answer-state mc-quiz-answer-state--after" style="display: none;">
					<?php
					/**
					 * Fires after a quiz question has been answered once
					 *
					 * @since [version]
					 * 
					 * @param WpProQuiz_Model_Question $question Current question object
					 * @param WpProQuiz_Model_Quiz    $quiz     Current quiz object
					 * @param int                     $index    Current question index
					 */
					do_action( 'learndash_quiz_question_after_answer', $question, $quiz, $index );
					?>
				</div>

				<div class="mc-quiz-answer-state mc-quiz-answer-state--before">
					<?php
					$question_instructions = get_post_meta($question->getQuestionPostId(), '_mc_question_instructions', true);
					if (!empty($question_instructions)) {
						echo '<div class="mc-question-instructions">';
						echo wp_kses_post($question_instructions);
						echo '</div>';
					}
					?>
					<?php
					/**
					 * Fires before a quiz question has been answered
					 *
					 * @since [version]
					 * 
					 * @param WpProQuiz_Model_Question $question Current question object
					 * @param WpProQuiz_Model_Quiz    $quiz     Current quiz object
					 * @param int                     $index    Current question index
					 */
					do_action( 'learndash_quiz_question_before_answer', $question, $quiz, $index );
					?>
				</div>

				<?php if ( ! $quiz->isHideAnswerMessageBox() ) { ?>
					<div class="wpProQuiz_response" style="display: none;">
						<?php
						$ungraded_response_explanation = get_post_meta($question->getQuestionPostId(), '_mc_ungraded_response_explanation', true);

						if( empty( $ungraded_response_explanation ) ):
						?>
						<div style="display: none;" class="wpProQuiz_correct">
							<?php if ( $question->isShowPointsInBox() && $question->isAnswerPointsActivated() ) { ?>
								<div>
									<span class="wpProQuiz_response_correct_label" style="float: left;">
									<?php
										echo wp_kses_post(
											SFWD_LMS::get_template(
												'learndash_quiz_messages',
												array(
													'quiz_post_id' => $quiz->getID(),
													'context'      => 'quiz_question_answer_correct_message',
													'message'      => esc_html__( 'Correct', 'learndash' ),
												)
											)
										);
									?>
									</span>
									<span class="wpProQuiz_response_correct_points_label" style="float: right;">
										<span class="wpProQuiz_responsePoints"></span>
										<?php echo ' / ' . esc_html( $question->getPoints() ); ?>
										<?php
										echo wp_kses_post(
											SFWD_LMS::get_template(
												'learndash_quiz_messages',
												array(
													'quiz_post_id' => $quiz->getID(),
													'context'      => 'quiz_question_answer_points_message',
													'message'      => esc_html__( 'Points', 'learndash' ),
												)
											)
										);
										?>
									</span>
									<div style="clear: both;"></div>
								</div>
							<?php } elseif ( 'essay' == $question->getAnswerType() ) { ?>
								<?php
								echo wp_kses_post(
									SFWD_LMS::get_template(
										'learndash_quiz_messages',
										array(
											'quiz_post_id' => $quiz->getID(),
											'context'      => 'quiz_essay_question_graded_review_message',
											'message'      => esc_html__( 'Grading can be reviewed and adjusted.', 'learndash' ),
										)
									)
								);
								?>
							<?php } else { ?>
								<span>
								<?php
								echo wp_kses_post(
									SFWD_LMS::get_template(
										'learndash_quiz_messages',
										array(
											'quiz_post_id' => $quiz->getID(),
											'context'      => 'quiz_question_answer_correct_message',
											'message'      => esc_html__( 'Correct', 'learndash' ),
										)
									)
								);
								?>
								</span>
							<?php } ?>
							<<?php echo esc_attr( LEARNDASH_QUIZ_ANSWER_MESSAGE_HTML_TYPE ); ?> class="wpProQuiz_AnswerMessage"></<?php echo esc_attr( LEARNDASH_QUIZ_ANSWER_MESSAGE_HTML_TYPE ); ?>>
						</div>
						<div style="display: none;" class="wpProQuiz_incorrect">
							<?php if ( $question->isShowPointsInBox() && $question->isAnswerPointsActivated() ) { ?>
								<div>
									<span style="float: left;">
										<?php
											echo wp_kses_post(
												SFWD_LMS::get_template(
													'learndash_quiz_messages',
													array(
														'quiz_post_id' => $quiz->getID(),
														'context'      => 'quiz_question_answer_incorrect_message',
														'message'      => esc_html__( 'Incorrect', 'learndash' ),
													)
												)
											);
										?>
									</span>
									<span style="float: right;"><span class="wpProQuiz_responsePoints"></span> / <?php echo esc_html( $question->getPoints() ); ?>
									<?php
										echo wp_kses_post(
											SFWD_LMS::get_template(
												'learndash_quiz_messages',
												array(
													'quiz_post_id' => $quiz->getID(),
													'context'      => 'quiz_question_answer_points_message',
													'message'      => esc_html__( 'Points', 'learndash' ),
												)
											)
										);
									?>
									</span>

									<div style="clear: both;"></div>
								</div>
							<?php } elseif ( 'essay' == $question->getAnswerType() ) { ?>
								<?php
								echo wp_kses_post(
									SFWD_LMS::get_template(
										'learndash_quiz_messages',
										array(
											'quiz_post_id' => $quiz->getID(),
											'context'      => 'quiz_essay_question_graded_review_message',
											'message'      => esc_html__( 'Grading can be reviewed and adjusted.', 'learndash' ),
										)
									)
								);
								?>
							<?php } else { ?>
								<span>
								<?php
								echo wp_kses_post(
									SFWD_LMS::get_template(
										'learndash_quiz_messages',
										array(
											'quiz_post_id' => $quiz->getID(),
											'context'      => 'quiz_question_answer_incorrect_message',
											'message'      => esc_html__( 'Incorrect', 'learndash' ),
										)
									)
								);
								?>
							</span>
							<?php } ?>
							<<?php echo esc_attr( LEARNDASH_QUIZ_ANSWER_MESSAGE_HTML_TYPE ); ?> class="wpProQuiz_AnswerMessage"></<?php echo esc_attr( LEARNDASH_QUIZ_ANSWER_MESSAGE_HTML_TYPE ); ?>>
						</div>
						<?php else: ?>
							<div class="mc-ungraded-response-explanation">
								<p><?php echo esc_html( $ungraded_response_explanation ); ?></p>
							</div>
						<?php endif; ?>
					</div>
				<?php } ?>

				<?php if ( $question->isTipEnabled() ) { ?>
					<div class="wpProQuiz_tipp" style="display: none; position: relative;">
						<div>
							<h5 style="margin: 0px 0px 10px;" class="wpProQuiz_header">
							<?php
								echo wp_kses_post(
									SFWD_LMS::get_template(
										'learndash_quiz_messages',
										array(
											'quiz_post_id' => $quiz->getID(),
											'context'      => 'quiz_hint_header',
											'message'      => esc_html__( 'Hint', 'learndash' ),
										)
									)
								);
							?>
							</h5>
							<?php
							$tip_message = apply_filters( 'comment_text', $question->getTipMsg(), null, null );
							global $wp_embed;
							$tip_message = $wp_embed->run_shortcode( $tip_message );
							echo do_shortcode( $tip_message );
							?>
						</div>
					</div>
				<?php } ?>
                <div class="wpProQuiz_buttonsContainer">
                    <?php if ( $quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_CHECK && ! $quiz->isSkipQuestionDisabled() && $quiz->isShowReviewQuestion() ) { ?>
                        <input type="button" name="skip" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                            SFWD_LMS::get_template(
                                'learndash_quiz_messages',
                                array(
                                    'quiz_post_id' => $quiz->getID(),
                                    'context'      => 'quiz_skip_button_label',
                                    // translators: placeholder: question.
                                    'message'      => sprintf( esc_html_x( 'Skip %s', 'placeholder: question', 'learndash' ), learndash_get_custom_label_lower( 'question' ) ),
                                )
                            )
                        ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style=" margin-right: 10px ;"> <!--float: left;--><?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } ?>
                    <?php if ( ! is_rtl() ) { ?>
                    <input type="button" name="back" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                        SFWD_LMS::get_template(
                            'learndash_quiz_messages',
                            array(
                                'quiz_post_id' => $quiz->getID(),
                                'context'      => 'quiz_back_button_label',
                                'message'      => esc_html__( 'Back', 'learndash' ),
                            )
                        )
                    ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style=" margin-right: 10px ; display: none;"> <!--float: left ;--> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } else { ?>
                        <input type="button" name="next" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                            SFWD_LMS::get_template(
                                'learndash_quiz_messages',
                                array(
                                    'quiz_post_id' => $quiz->getID(),
                                    'context'      => 'quiz_next_button_label',
                                    'message'      => esc_html__( 'Next', 'learndash' ),
                                )
                            )
                        ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style=" margin-right: 10px ; display: none;">  <!--float: left ;--><?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } ?>
                    <?php if ( $question->isTipEnabled() ) { ?>
                        <input type="button" name="tip" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                            SFWD_LMS::get_template(
                                'learndash_quiz_messages',
                                array(
                                    'quiz_post_id' => $quiz->getID(),
                                    'context'      => 'quiz_hint_button_label',
                                    'message'      => esc_html__( 'Hint', 'learndash' ),
                                )
                            )
                        ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton wpProQuiz_TipButton" style=" display: inline-block; margin-right: 10px ;"> <!--float: left ;--> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } ?>
                    <input type="button" name="check" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                        SFWD_LMS::get_template(
                            'learndash_quiz_messages',
                            array(
                                'quiz_post_id' => $quiz->getID(),
                                'context'      => 'quiz_check_button_label',
                                'message'      => esc_html__( 'Check', 'learndash' ),
                            )
                        )
                    ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style=" margin-right: 10px ; display: none;"> <!--float: right ;--> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php if ( ! is_rtl() ) { ?>
                    <input type="button" name="next" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                        SFWD_LMS::get_template(
                            'learndash_quiz_messages',
                            array(
                                'quiz_post_id' => $quiz->getID(),
                                'context'      => 'quiz_next_button_label',
                                'message'      => esc_html__( 'Next', 'learndash' ),
                            )
                        )
                    ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style=" display: none;"> <!--float: right;--><?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } else { ?>
                    <input type="button" name="back" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
                        SFWD_LMS::get_template(
                            'learndash_quiz_messages',
                            array(
                                'quiz_post_id' => $quiz->getID(),
                                'context'      => 'quiz_back_button_label',
                                'message'      => esc_html__( 'Back', 'learndash' ),
                            )
                        )
                    ) ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style="display: none;"> <!--float: right; --><?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
                    <?php } ?>
				</div>
				<div style="clear: both;"></div>

				<?php if ( $quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE ) { ?>
					<div style="margin-bottom: 20px;"></div>
				<?php } ?>
			</li>

		<?php } ?>
	</ol>
	<?php if ( $quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE ) { ?>
		<div>
			<input type="button" name="wpProQuiz_pageLeft" data-text="<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
				// translators: placeholder: page number.
				echo esc_html__( 'Page %d', 'learndash' );
			?>" style="float: left; display: none;" class="wpProQuiz_button wpProQuiz_QuestionButton"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,Squiz.PHP.EmbeddedPhp.ContentAfterEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
			<input type="button" name="wpProQuiz_pageRight" data-text="<?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
				// translators: placeholder: page number.
				echo esc_html__( 'Page %d', 'learndash' );
			?>" style="float: right; display: none;" class="wpProQuiz_button wpProQuiz_QuestionButton"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,Squiz.PHP.EmbeddedPhp.ContentAfterEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>

			<?php if ( $quiz->isShowReviewQuestion() && ! $quiz->isQuizSummaryHide() ) { ?>
				<input type="button" name="checkSingle" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_quiz_summary_button_label',
							'message'      => sprintf(
								// translators: placeholder: Quiz.
								esc_html_x( '%s Summary', 'Quiz Summary', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
							),
						)
					)
				); ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style="float: right;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
			<?php } else { ?>
				<input type="button" name="checkSingle" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_finish_button_label',
							'message'      => sprintf(
								// translators: placeholder: Quiz.
								esc_html_x( 'Finish %s', 'placeholder: Quiz', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Method escapes output
							),
						)
					)
				); ?>" class="wpProQuiz_button wpProQuiz_QuestionButton" style="float: right;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
			<?php } ?>

			<div style="clear: both;"></div>
		</div>
	<?php }
		do_action( 'mc_report_problem', 'quiz', $quiz->getPostId() );
	?>
</div>

<script>
jQuery(document).ready(function () {
    // Function to generate random number between min and max
    function getRandomNumber(min, max) {
        return Math.random() * (max - min) + min;
    }

    // Function to generate random shake animation
    function generateShakeAnimation(element) {
        const duration = getRandomNumber(0.4, 0.9); // Random duration between 0.5s and 1s
        const keyframes = [];
        
        // Generate more keyframe points for smoother animation
        for (let i = 0; i <= 100; i += 10) {
            // Use sine wave for more natural movement
            const progress = i / 100;
            const sineValue = Math.sin(progress * Math.PI * 2);
            
            // Smoother movement with smaller range
            const x = sineValue * getRandomNumber(0.5, 1);
            const y = sineValue * getRandomNumber(0.5, 1);
            // Smoother rotation with smaller range
            const rotate = sineValue * getRandomNumber(0.5, 1);
            
            keyframes.push(`${i}% { transform: translateX(${x}px) translateY(${y}px) rotate(${rotate}deg); }`);
        }
        
        // Create unique animation name and class
        const uniqueId = 'shake_' + Math.random().toString(36).substr(2, 9);
        const animationName = `shake_${uniqueId}`;
        
        // Add unique class to the element
        jQuery(element).addClass(uniqueId);
        
        // Create style element for this animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ${animationName} {
                ${keyframes.join('\n')}
            }
            .shake-wrapper.${uniqueId} {
                animation: ${animationName} ${duration}s infinite ease-in-out;
                transform-origin: center center;
                will-change: transform;
            }
        `;
        
        document.head.appendChild(style);
    }

    // Generate animations for each shake-wrapper
    jQuery('.shake-wrapper').each(function() {
        generateShakeAnimation(this);
    });

    // Handle drag start
    jQuery('.draggable').on('dragstart', function (e) {
        const dragImage = document.createElement('div');
        dragImage.style.width = '1px';
        dragImage.style.height = '1px';
        dragImage.style.opacity = '0';
        document.body.appendChild(dragImage);
        e.originalEvent.dataTransfer.setDragImage(dragImage, 0, 0);
    });
});
</script>
<?php

/*
foreach( $json as &$j ) {
	$question_type = get_post_meta( $j['question_post_id'], '_multiclass_question_type', true );

	if( 'concentration_numbers' === $question_type ) {
		foreach( $j['correct'] as &$values ) {
			foreach( $values as &$value ) {
				switch( $value ) {
					case 'Yes':
					case 'yes':
						$value = esc_html__( 'Yes', 'custom-quiz-types-for-multiclass' );
						break;
					case 'No':
					case 'no':
						$value = esc_html__( 'No', 'custom-quiz-types-for-multiclass' );
						break;
				}
			}
		}
	}
}*/

return array(
	'globalPoints' => $global_points,
	'json'         => $json,
	'catPoints'    => $cat_points,
);
