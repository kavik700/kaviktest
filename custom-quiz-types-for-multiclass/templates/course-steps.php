<?php
/**
 * LearnDash LD30 Displays a Course Prev/Next navigation.
 *
 * Available Variables:
 *
 * $course_id        : (int) ID of Course
 * $course_step_post : (object) WP_Post instance of lesson/topic post
 * $user_id          : (int) ID of User
 * $course_settings  : (array) Settings specific to current course
 * $can_complete     : (bool) Can the user mark this lesson/topic complete?
 * $context		     : (string) Context of the usage. Either 'lesson', 'topic' or 'focus' use for Focus Mode header navigation.
 *
 * @since 3.0.0
 * @version 4.11.0
 *
 * @package LearnDash\Templates\LD30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $can_complete ) ) {
	$can_complete = false;
}


// TODO @37designs this is a bit confusing still, as you can still navigate left / right on lessons even with topics.
if ( ( isset( $course_step_post ) ) && ( is_a( $course_step_post, 'WP_Post' ) ) && ( in_array( $course_step_post->post_type, learndash_get_post_types( 'course' ), true ) ) ) {
	if ( learndash_get_post_type_slug( 'lesson' ) === $course_step_post->post_type ) {
		$parent_id = absint( $course_id	);
	} else {
		$parent_id = learndash_course_get_single_parent_step( $course_id, $course_step_post->ID );
	}
} else {
	$parent_id = ( get_post_type() === 'sfwd-lessons' ? absint( $course_id ) : learndash_course_get_single_parent_step( $course_id, get_the_ID() ) );
}

// If parent ID is empty then the parent is the course.
if ( empty( $parent_id ) ) {
	$parent_id = absint( $course_id );
}

$learndash_previous_step_id = learndash_previous_post_link( null, 'id', $course_step_post );
$learndash_next_step_id     = '';

$button_class           = 'ld-button ' . ( 'focus' === $context ? 'ld-button-transparent' : '' );


$is_essay = get_post_meta($course_id, '_sp_is_essay_course', true);

/*
 * See details for filter 'learndash_show_next_link' at https://developers.learndash.com/hook/learndash_show_next_link/
 *
 * @since version 2.3
 */

$current_complete = false;

if ( ( empty( $course_settings ) ) && ( ! empty( $course_id ) ) ) {
	$course_settings = learndash_get_setting( $course_id );
}

if ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::TOPIC ) === $course_step_post->post_type ) {
	$current_complete = learndash_is_topic_complete( $user_id, $course_step_post->ID, $course_id );
} elseif ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::LESSON ) === $course_step_post->post_type ) {
	$current_complete = learndash_is_lesson_complete( $user_id, $course_step_post->ID, $course_id );
}elseif ( LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::QUIZ ) === $course_step_post->post_type ) {
	$current_complete = learndash_is_quiz_complete( $user_id, $course_step_post->ID, $course_id );
}

if ( learndash_lesson_hasassignments( $course_step_post ) ) {
	$user_assignments     = learndash_get_user_assignments( $course_step_post->ID, $user_id, absint( $course_id ), 'ids' );
	$approved_assignments = learndash_assignment_list_approved( $user_assignments, $course_step_post->ID, $user_id );
	if ( ! $approved_assignments ) {
		$current_complete = false;
	}
}

$learndash_maybe_show_next_step_link = $current_complete;
//if ( ( isset( $course_settings['course_disable_lesson_progression'] ) ) && ( 'on' === $course_settings['course_disable_lesson_progression'] ) ) {

$course_lesson_progression_enabled = learndash_lesson_progression_enabled( $course_id );
if ( ! $course_lesson_progression_enabled ) {
	$learndash_maybe_show_next_step_link = true;
}

if ( $learndash_maybe_show_next_step_link !== true ) {
	$bypass_course_limits_admin_users = learndash_can_user_bypass( $user_id, 'learndash_course_progression' );
	if ( true === $bypass_course_limits_admin_users ) {
		$learndash_maybe_show_next_step_link = true;
	}
}

/**
 * Filters whether to show the next link in the course navigation.
 *
 * @since 2.3.0
 *
 * @param bool $show_next_link Whether to show next link.
 * @param int  $user_id        User ID.
 * @param int  $step_id        ID of the lesson/topic post.
 *
 */
$learndash_maybe_show_next_step_link = apply_filters( 'learndash_show_next_link', $learndash_maybe_show_next_step_link, $user_id, $course_step_post->ID );

// Only complete lessons/topics or external quizzes.
if (
	! in_array(
		$course_step_post->post_type,
		learndash_get_post_type_slug(
			[
				LDLMS_Post_Types::LESSON,
				LDLMS_Post_Types::TOPIC,
			]
		),
		true
	)
	&& ! (
		$course_step_post->post_type === LDLMS_Post_Types::get_post_type_slug( LDLMS_Post_Types::QUIZ )
		&& learndash_course_steps_is_external( $course_step_post->ID )
	)
) {
	$can_complete                        = false;
	$current_complete                    = false;
	$learndash_maybe_show_next_step_link = false;
}

if ( true === (bool) $learndash_maybe_show_next_step_link ) {
	$learndash_next_step_id = learndash_next_post_link( null, 'id', $course_step_post );
} elseif ( ( ! is_user_logged_in() ) && ( empty( $learndash_next_step_id ) ) ) {
	$learndash_next_step_id = learndash_next_post_link( null, 'id', $course_step_post );

	if ( ! empty( $learndash_next_step_id ) ) {
		if ( ! learndash_is_sample( $learndash_next_step_id ) ) {
			if ( ( ! isset( $course_settings['course_price_type'] ) ) || ( 'open' !== $course_settings['course_price_type'] ) ) {
				$learndash_next_step_id = '';
			}
		}
	}
}

/**
 * Filters to override next step post ID.
 *
 * @since 3.1.2
 *
 * @param int $learndash_next_step_id The next step post ID.
 * @param int $course_step_post       The current step WP_Post ID.
 * @param int $course_id              The current Course ID.
 * @param int $user_id                The current User ID.
 *
 * @return int $learndash_next_step_id
 */
$learndash_next_step_id = apply_filters( 'learndash_next_step_id', $learndash_next_step_id, $course_step_post->ID, $course_id, $user_id );

/**
 * Check if we need to show the Mark Complete form. see LEARNDASH-4722
 */
$parent_lesson_id = 0;
if ( $course_step_post->post_type == 'sfwd-lessons' ) {
	$parent_lesson_id = $course_step_post->ID;
} elseif ( $course_step_post->post_type == 'sfwd-topic' || $course_step_post->post_type == 'sfwd-quiz' ) {
	if ( LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Courses_Builder', 'shared_steps' ) == 'yes' ) {
		$parent_lesson_id = learndash_course_get_single_parent_step( $course_id, $course_step_post->ID );
	} else {
		$parent_lesson_id = learndash_get_setting( $course_step_post, 'lesson' );
	}
}
if ( ! empty( $parent_lesson_id ) ) {
	$lesson_access_from = ld_lesson_access_from( $parent_lesson_id, $user_id, $course_id );
	if ( ( empty( $lesson_access_from ) ) || ( ! empty( $bypass_course_limits_admin_users ) ) ) {
		$complete_button = learndash_mark_complete( $course_step_post );
	} else {
		$complete_button = '';

	}
} else {
	$complete_button = learndash_mark_complete( $course_step_post );
}

if ( ( true === $current_complete ) && ( is_a( $course_step_post, 'WP_Post' ) ) ){
	$incomplete_button = learndash_show_mark_incomplete( $course_step_post );
} else {
	$incomplete_button = '';
}

$time_lock_active = \Multiclass\Core::has_course_time_lock()['status'];

?>
<div class="ld-content-actions">

	<?php if( $is_essay ):
	?>
	<div class="ld-content-action  mc-next-step">
		<a class="ld-button " href="<?php echo esc_url( get_permalink( $course_id ) ); ?>">
		<span class="ld-icon ld-icon-arrow-left"></span>
			<span class="ld-text"><?php esc_html_e('Go back to the course view', 'custom-quiz-types-for-multiclass'); ?></span>
			
		</a>
	</div>
	<?php endif; ?>


	<?php
	/**
	 * Fires before the course steps (all locations).
	 *
	 * @since 3.0.0
	 *
	 * @param string|false $post_type Post type slug.
	 * @param int          $course_id Course ID.
	 * @param int          $user_id   User ID.
	 */
	do_action( 'learndash-all-course-steps-before', get_post_type(), $course_id, $user_id );

	/**
	 * Fires before the course steps for any context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to the context for which the hook is fired,
	 * such as `course`, `lesson`, `topic`, `quiz`, etc.
	 *
	 * @since 3.0.0
	 *
	 * @param string|false $post_type Post type slug.
	 * @param int          $course_id Course ID.
	 * @param int          $user_id   User ID.
	 */
	do_action( 'learndash-' . $context . '-course-steps-before', get_post_type(), $course_id, $user_id );
	//$learndash_current_post_type = get_post_type();

	$current_user_id = get_current_user_id();
	$user_meta_key = '_time_lock_gmt_' . $course_id;
	$stored_lock_data = get_user_meta($current_user_id, $user_meta_key, true);
	$stored_lock_data = $stored_lock_data ? json_decode($stored_lock_data, true) : [];

	$time_lock_topic_id = isset($stored_lock_data['time_lock_topic_id']) ? $stored_lock_data['time_lock_topic_id'] : null;

	$is_time_block_topic = ($time_lock_topic_id === $course_step_post->ID) && (current_user_can('customer') || current_user_can('subscriber'));

	if( ! $is_essay ):
	if( ! $is_time_block_topic ):
	?>
	<div class="ld-content-action <?php if ( ! $learndash_previous_step_id ) : ?>ld-empty<?php endif; ?>">
	<?php if ( $learndash_previous_step_id ) : ?>
		<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( learndash_get_step_permalink( $learndash_previous_step_id, $course_id ) ); ?>">
			<?php if ( is_rtl() ) { ?>
			<span class="ld-icon ld-icon-arrow-right"></span>
			<?php } else { ?>
			<span class="ld-icon ld-icon-arrow-left"></span>
			<?php } ?>
			<span class="ld-text"><?php echo esc_html( learndash_get_label_course_step_previous( get_post_type( $learndash_previous_step_id ) ) ); ?></span>
		</a>
	<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php endif; ?>
	<?php

	if ( $parent_id && 'focus' !== $context ) :
		if ( $learndash_maybe_show_next_step_link ) :
			?>
			<?php if( ! $is_essay ): ?>
			<div class="ld-content-action">
			<?php
			if ( ( true === $can_complete ) && ( true !== $current_complete ) && ( ! empty( $complete_button ) ) ) :
				echo learndash_mark_complete( $course_step_post );
			elseif ( ( true === $can_complete ) && ( true === $current_complete ) &&  ( ! empty( $incomplete_button ) ) ) :
				echo $incomplete_button;
				?>

			<?php endif; ?>
			<a href="<?php echo esc_url( learndash_get_step_permalink( $parent_id, $course_id ) ); ?>" class="ld-primary-color ld-course-step-back"><?php echo learndash_get_label_course_step_back( get_post_type( $parent_id ) ); ?></a>
			</div>
			<?php endif; ?>
			<div class="ld-content-action <?php if ( ( ! $learndash_next_step_id ) ) : ?>ld-empty<?php endif; ?> mc-next-step">
			<?php if ( $learndash_next_step_id ) : ?>
				<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_url( learndash_get_step_permalink( $learndash_next_step_id, $course_id ) ); ?>">
					<span class="ld-text"><?php echo learndash_get_label_course_step_next( get_post_type( $learndash_next_step_id ) ); ?></span>
					<?php if ( is_rtl() ) { ?>
						<span class="ld-icon ld-icon-arrow-left"></span>
						<?php } else { ?>
						<span class="ld-icon ld-icon-arrow-right"></span>
					<?php } ?>
				</a>
			<?php endif; ?>
			</div>
			<?php else : ?>
			<div class="ld-content-action">
				<a href="<?php echo esc_attr( learndash_get_step_permalink( $parent_id, $course_id ) ); ?>" class="ld-primary-color ld-course-step-back"><?php echo learndash_get_label_course_step_back( get_post_type( $parent_id ) ); ?></a>
			</div>
			<div class="ld-content-action <?php if ( ( ! $can_complete ) && ( ! $learndash_next_step_id ) ) : ?>ld-empty<?php endif; ?>">
				<?php
				if ( ( true === $can_complete ) && ( true !== $current_complete ) && ( ! empty( $complete_button ) ) ) :
					echo $complete_button;
				elseif ( $learndash_next_step_id ) :
					?>
					<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_attr( learndash_get_step_permalink( $learndash_next_step_id, $course_id ) ); ?>">
					<span class="ld-text"><?php echo learndash_get_label_course_step_next( get_post_type( $learndash_next_step_id ) ); ?></span>
						<?php if ( is_rtl() ) { ?>
						<span class="ld-icon ld-icon-arrow-left"></span>
						<?php } else { ?>
						<span class="ld-icon ld-icon-arrow-right"></span>
						<?php } ?>
					</a>
			<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php elseif ( $parent_id && 'focus' === $context ) : ?>
	<div class="ld-content-action <?php if ( ( ! $can_complete ) && ( ! $learndash_next_step_id ) ) : ?>ld-empty<?php endif; ?>">
		<?php
		if ( ( true === $can_complete ) && ( true !== $current_complete ) && ( ! empty( $complete_button ) ) ) :
			echo learndash_mark_complete( $course_step_post );
		elseif ( ( true === $can_complete ) && ( true === $current_complete ) &&  ( ! empty( $incomplete_button ) ) ) :
			echo $incomplete_button;
		elseif ( $learndash_next_step_id ) :
			?>
			<a class="<?php echo esc_attr( $button_class ); ?>" href="<?php echo esc_attr( learndash_get_step_permalink( $learndash_next_step_id, $course_id ) ); ?>">
				<span class="ld-text"><?php echo learndash_get_label_course_step_next( get_post_type( $learndash_next_step_id ) ); ?></span>
				<?php if ( is_rtl() ) { ?>
				<span class="ld-icon ld-icon-arrow-left"></span>
				<?php } else { ?>
				<span class="ld-icon ld-icon-arrow-right"></span>
				<?php } ?>
			</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php
	/**
	 * Fires after the course steps (all locations).
	 *
	 * @since 3.0.0
	 *
	 * @param string|false $post_type Post type slug.
	 * @param int          $course_id Course ID.
	 * @param int          $user_id   User ID.
	 */
	do_action( 'learndash-all-course-steps-after', get_post_type(), $course_id, $user_id );

	/**
	 * Fires after the course steps for any context.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to the context for which the hook is fired,
	 * such as `course`, `lesson`, `topic`, `quiz`, etc.
	 *
	 * @since 3.0.0
	 *
	 * @param string|false $post_type Post type slug.
	 * @param int          $course_id Course ID.
	 * @param int          $user_id   User ID.
	 */
	do_action( 'learndash-' . $context . '-course-steps-after', get_post_type(), $course_id, $user_id );
	?>

</div> <!--/.ld-topic-actions-->

<?php
if ( $time_lock_active ) {
	?>
	<script>
		jQuery(document).ready(function($) {
			const storedLockData = <?php echo json_encode($stored_lock_data); ?>;
			// Convert GMT string to UTC timestamp in milliseconds
			const expirationTime = Date.parse(storedLockData.lock_time_gmt + 'Z');
			
			function updateButtonStates() {
				const now = Date.now();
				const isLocked = now < expirationTime;
				
				// Toggle visibility of deadline messages
				$('#mc-topic-time-lock-deadline').css('display', isLocked ? 'inline' : 'none');
				$('#mc-topic-time-lock-deadline-passed').css('display', isLocked ? 'none' : 'inline');
				
				// Handle navigation links
				$('.ld-content-action > a').each(function() {
					const $button = $(this);
					
					if (isLocked) {
						// Disable buttons if still locked
						if (!$button.hasClass('disabled')) {
							$button.data('original-url', $button.attr('href'))
								.removeAttr('href')
								.addClass('disabled')
								.attr({
									'aria-disabled': 'true',
									'tabindex': '-1'
								});
						}
					} else {
						// Re-enable buttons if lock expired
						if ($button.hasClass('disabled')) {
							$button.attr('href', $button.data('original-url'))
								.removeClass('disabled')
								.removeAttr('aria-disabled tabindex');
						}
					}
				});

				// Handle mark complete button
				$('.learndash_mark_complete_button').each(function() {
					const $button = $(this);
					
					if (isLocked) {
						// Disable mark complete button if locked
						if (!$button.prop('disabled')) {
							$button.prop('disabled', true)
								.addClass('disabled')
								.attr({
									'aria-disabled': 'true',
									'tabindex': '-1'
								});
						}
					} else {
						// Re-enable mark complete button if lock expired
						if ($button.prop('disabled')) {
							$button.prop('disabled', false)
								.removeClass('disabled')
								.removeAttr('aria-disabled tabindex');
						}
					}
				});
				
				if (isLocked) {
					setTimeout(updateButtonStates, 1000);
				}
			}
			
			updateButtonStates();
		});
	</script>
	<?php
}
//endif;
