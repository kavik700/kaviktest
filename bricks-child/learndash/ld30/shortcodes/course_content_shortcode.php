<?php
/**
 * LearnDash LD30 Displays content of course
 *
 * Available Variables:
 * $course_id                  : (int) ID of the course
 * $course                     : (object) Post object of the course
 * $course_settings            : (array) Settings specific to current course
 *
 * $courses_options            : Options/Settings as configured on Course Options page
 * $lessons_options            : Options/Settings as configured on Lessons Options page
 * $quizzes_options            : Options/Settings as configured on Quiz Options page
 *
 * $user_id                    : Current User ID
 * $logged_in                  : User is logged in
 * $current_user               : (object) Currently logged in user object
 *
 * $course_status              : Course Status
 * $has_access                 : User has access to course or is enrolled.
 * $has_course_content         : Course has course content
 * $lessons                    : Lessons Array
 * $quizzes                    : Quizzes Array
 * $lesson_progression_enabled : (true/false)
 *
 * @since 3.0.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $has_course_content ) :

	$shortcode_instance = ( isset( $atts ) && ! empty( $atts ) ? $atts : array() );
	$shortcode_instance = htmlspecialchars( wp_json_encode( $shortcode_instance ) );

	global $course_pager_results;

	if ( ( isset( $atts['wrapper'] ) ) && ( true === $atts['wrapper'] ) ) {
		?>
		<div class="learndash-wrapper">
		<?php
	}
	?>
		<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above?>
		<div class="ld-item-list ld-lesson-list <?php echo esc_attr( 'ld-course-content-' . $course_id ); ?>" data-shortcode_instance="<?php echo $shortcode_instance; ?>">
			<?php
				// Show only for users who can see the course
				// if ( ! empty( $has_access ) ) {
					/*echo '<div class="ld-progress">';
			            echo learndash_course_progress(array(
			                'user_id'   => $user_id,
			                'course_id' => $course_id,
			                'array'     => false // return HTML markup
			            ));
			        echo '</div>';*/
				    // Get raw progress numbers from LearnDash
				    $p = learndash_course_progress( array(
				        'user_id'   => (int) $user_id,
				        'course_id' => (int) $course_id,
				        'array'     => true,           // return data, not HTML
				    ) );

				    // Defensive defaults
				    $completed  = isset( $p['completed'] ) ? (int) $p['completed'] : 0;
				    $total      = isset( $p['total'] )     ? (int) $p['total']     : 0;
				    $percentage = $total > 0 ? floor( ( $completed / $total ) * 100 ) : 0;
				    ?>
				    
				    <?php
				// }
			?>
			<div class="ld-section-heading" style="padding: 15px 30px; display: block;">
				<div class="ld-progress" bis_skin_checked="1">
					<div class="ld-progress-heading" bis_skin_checked="1">
						<div class="ld-progress-label" bis_skin_checked="1"><?php esc_html_e('Kurs Fortschritt','your-text-domain'); ?></div>
						<div class="ld-progress-stats" bis_skin_checked="1">
							<div class="ld-progress-percentage ld-secondary-color" bis_skin_checked="1"><?php echo esc_html( $percentage ); ?>% <?php esc_html_e('Vollständig','your-textdomain'); ?></div>
						</div> <!--/.ld-progress-stats-->
					</div>
					<div class="ld-progress-bar" bis_skin_checked="1">
						<div class="ld-progress-bar-percentage ld-secondary-background" style="width:<?php echo esc_attr( $percentage ); ?>%" bis_skin_checked="1"></div>
					</div>
				</div>
			</div>
			
			<div class="ld-section-heading">

				<?php
				/** This action is documented in themes/ld30/templates/course.php */
				do_action( 'learndash-course-heading-before', $course_id, $user_id );
				?>

				<h2>
				    <?php
				    // Get the custom title from post meta
				    // $custom_title = LearnDash_Custom_Label::get_label('course');
				    // // print_r($custom_title);
				    // if ($custom_title === 'Course') {
				        // Display the custom title if it exists
				        echo esc_html(get_the_title()); // Default course title
				    // } else {
				    //     // Use the custom course label from LearnDash settings
				    //     printf(
				    //         // translators: placeholder: Course.
				    //         esc_html_x('%s Content', 'learndash'),
				    //         LearnDash_Custom_Label::get_label('course') // Retrieves the custom label for "Course"
				    //     );
				    // }
				    ?>
				</h2>


				<?php
				/** This action is documented in themes/ld30/templates/course.php */
				do_action( 'learndash-course-heading-after', $course_id, $user_id );
				?>

				<div class="ld-item-list-actions" data-ld-expand-list="true">

					<?php
					/** This action is documented in themes/ld30/templates/course.php */
					do_action( 'learndash-course-expand-before', $course_id, $user_id );
					?>

					<?php
					// Only display if there is something to expand.
					if ( $has_topics ) :
						?>
						<div class="ld-expand-button ld-primary-background" id="<?php echo esc_attr( 'ld-expand-button-' . $course_id ); ?>" data-ld-expands="<?php echo esc_attr( 'ld-item-list-' . $course_id ); ?>" data-ld-expand-text="<?php echo esc_attr_e( 'Expand All', 'learndash' ); ?>" data-ld-collapse-text="<?php echo esc_attr_e( 'Collapse All', 'learndash' ); ?>">
							<span class="ld-icon-arrow-down ld-icon"></span>
							<span class="ld-text"><?php echo esc_html_e( 'Expand All', 'learndash' ); ?></span>
						</div> <!--/.ld-expand-button-->
						<?php
						/** This filter is documented in themes/ld30/templates/course.php */
						if ( apply_filters( 'learndash_course_steps_expand_all', false, $course_id, 'course_lessons_listing_main' ) ) :
							?>
							<script>
								jQuery( function(){
									setTimeout(function(){
										jQuery("<?php echo esc_attr( '#ld-expand-button-' . $course_id ); ?>").click();
									}, 1000);
								});
							</script>
							<?php
						endif;

					endif;

					/** This action is documented in themes/ld30/templates/course.php */
					do_action( 'learndash-course-expand-after', $course_id, $user_id );
					?>

				</div> <!--/.ld-item-list-actions-->
			</div> <!--/.ld-section-heading-->

			<?php
			/** This action is documented in themes/ld30/templates/course.php */
			do_action( 'learndash-course-content-list-before', $course_id, $user_id );

			/**
			 * Content content listing
			 *
			 * @since 3.0.0
			 *
			 * ('listing.php');
			 */

			 learndash_get_template_part(
				'course/listing.php',
				array(
					'course_id'                  => $course_id,
					'user_id'                    => $user_id,
					'lessons'                    => $lessons,
					'lesson_topics'              => ! empty( $lesson_topics ) ? $lesson_topics : [],
					'quizzes'                    => $quizzes,
					'has_access'                 => $has_access,
					'course_pager_results'       => $course_pager_results,
					'lesson_progression_enabled' => $lesson_progression_enabled,
					'context'                    => 'course_content_shortcode',
				),
				true
			);

			/** This action is documented in themes/ld30/templates/course.php */
			do_action( 'learndash-course-content-list-after', $course_id, $user_id );
			?>

		</div> <!--/.ld-item-list-->

	<?php
	if ( ( isset( $atts['wrapper'] ) ) && ( true === $atts['wrapper'] ) ) {
		?>
		</div> <!--/.learndash-wrapper-->
		<?php
	}

endif; ?>
