<?php
namespace Bricks;

do_action( 'bricks_before_footer' );

do_action( 'render_footer' );

do_action( 'bricks_after_footer' );

do_action( 'bricks_after_site_wrapper' );

wp_footer(); ?>

<script>
	jQuery(document).ready(function () {
		var current_fs, next_fs, previous_fs; //fieldsets
		var opacity;
		var current = 1;
		var steps = jQuery("fieldset").length;

		function validateStep1(currentFieldset) {
			var isValid = true;

			//  First name
			// var nameRegex = /^[a-zA-Z\s]+$/;
			// Regex to allow letters with accents (diacritical marks), spaces, and prevent numbers or special characters
			// Updated regex to allow accented characters and spaces
			var nameRegex = /^[a-zA-Z\u00C0-\u024F]+(?:\s[a-zA-Z\u00C0-\u024F]+)*$/u;

			// First name and last name validation
			if (jQuery('.first_name').val().trim() === "" || jQuery('.last_name').val().trim() === "") {
			    jQuery('.errors.first_err').text("Bitte geben Sie Ihren Namen ein.");
			    jQuery('.errors.first_err').show();
			    isValid = false;
			} else if (!nameRegex.test(jQuery('.first_name').val().trim()) || !nameRegex.test(jQuery('.last_name').val().trim())) {
			    jQuery('.errors.first_err').text("Bitte geben Sie einen gültigen Namen ein.");
			    jQuery('.errors.first_err').show();
			    isValid = false;
			} else {
			    jQuery('.errors.first_err').hide();
			}


			// Mobile number
			var mobileNumberRegex = /^[0-9]{10}$/;

			var mobileNumber = jQuery.trim(jQuery('.mobile_number').val());

			if (mobileNumber === "") {
				jQuery('.errors.mobile_number_err').text("Bitte geben Sie Ihre Mobilnummer ein.");
				jQuery('.errors.mobile_number_err').show();
				isValid = false;
			} else if (!mobileNumberRegex.test(mobileNumber)) {
				jQuery('.errors.mobile_number_err').text("Bitte geben Sie eine gültige Mobilnummer ein.");
				jQuery('.errors.mobile_number_err').show();
				isValid = false;
			} else {
				jQuery('.errors.mobile_number_err').hide();
			}



			// Email address
			var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Regex pattern to match a basic email format
			if (jQuery('.email').val() === "") {
				jQuery('.errors.email_err').text("Bitte geben Sie Ihre E-Mail-Adresse ein.");
				jQuery('.errors.email_err').show();
				isValid = false;
			} else if (!emailRegex.test(jQuery('.email').val())) {
				jQuery('.errors.email_err').text("Bitte geben Sie eine gültige E-Mail-Adresse ein.");
				jQuery('.errors.email_err').show();
				isValid = false;
			} else {
				jQuery('.errors.email_err').hide();
			}


			// About us
			if (jQuery('.find_us').val() === "") {
				jQuery('.errors.find_us_err').show();
				isValid = false;
			} else {
				jQuery('.errors.find_us_err').hide();
			}

			// Data protection
			if (jQuery('input[name="data_protect"]:checked').length === 0) {
				jQuery('.errors.data_protect_err').show();
				isValid = false;
			} else {
				jQuery('.errors.data_protect_err').hide();
			}

			// Smartphone
			if (!jQuery('input[name="smartphone"]:checked').length) {
				jQuery('.errors.smartphone_err').show();
				isValid = false;
			} else {
				jQuery('.errors.smartphone_err').hide();
			}

			//  Gender
			if (!jQuery('input[name="gender"]:checked').length) {
				jQuery('.errors.gender_err').show();
				isValid = false;
			} else {
				jQuery('.errors.gender_err').hide();
			}

			return isValid;
		}

		function validateStep2(currentFieldset) {
			var isValid = true;
			// Last Training
			if (jQuery('.last_training').val() === "") {
				jQuery('.errors.last_training_err').show();
				isValid = false;
			} else {
				jQuery('.errors.last_training_err').hide();
			}

			// Tutor Work
			if (jQuery('.tutor_work').val() === "") {
				jQuery('.errors.tutor_work_err').show();
				isValid = false;
			} else {
				jQuery('.errors.tutor_work_err').hide();
			}

			// Tutor Experience
			if (jQuery('.tutor_experience').val() === "") {
				jQuery('.errors.tutor_experience_err').show();
				isValid = false;
			} else {
				jQuery('.errors.tutor_experience_err').hide();
			}

			// Tutor Skills
			if (jQuery('.tutor_skills').val() === "") {
				jQuery('.errors.tutor_skills_err').show();
				isValid = false;
			} else {
				jQuery('.errors.tutor_skills_err').hide();
			}

			// First Lesson
			if (jQuery('.before_first_lesson').val() === "") {
				jQuery('.errors.before_first_lesson_err').show();
				isValid = false;
			} else {
				jQuery('.errors.before_first_lesson_err').hide();
			}

			// Moral View
			if (jQuery('.moral_view').val() === "") {
				jQuery('.errors.moral_view_err').show();
				isValid = false;
			} else {
				jQuery('.errors.moral_view_err').hide();
			}

			// Approx Hours
			if (jQuery('.approx_hours').val() === "") {
				jQuery('.errors.approx_hours_err').show();
				isValid = false;
			} else {
				jQuery('.errors.approx_hours_err').hide();
			}

			return isValid;
		}


		function validateStep3(currentFieldset) {
			var isValid = true;

			// Availability
			if (jQuery('.available_date').val() === "" || jQuery('.available_month').val() === "" || jQuery('.available_year').val() === "") {
				jQuery('.errors.available_date_err').show();
				isValid = false;
			} else {
				jQuery('.errors.available_date_err').hide();
			}

			// How long teach
			if (!jQuery('input[name="how_long_teach"]:checked').length) {
				jQuery('.errors.how_long_teach_err').show();
				isValid = false;
			} else {
				jQuery('.errors.how_long_teach_err').hide();
			}

			// Hours per week
			if (!jQuery('input[name="hour_per_week"]:checked').length) {
				jQuery('.errors.hour_per_week_err').show();
				isValid = false;
			} else {
				jQuery('.errors.hour_per_week_err').hide();
			}

			// Assignments
			if (jQuery('input[name="assignments"]:checked').length === 0) {
				jQuery('.errors.assignments_err').show();
				isValid = false;
			} else {
				jQuery('.errors.assignments_err').hide();
			}

			// Education paths
			if (jQuery('input[name="educational_paths"]:checked').length === 0) {
				jQuery('.errors.educational_paths_err').show();
				isValid = false;
			} else {
				jQuery('.errors.educational_paths_err').hide();
			}

			
			// if (!jQuery('input[name="educational_paths"]:checked').length) {
			// 	jQuery('.errors.educational_paths_err').show();
			// 	isValid = false;
			// } else {
			// 	jQuery('.errors.educational_paths_err').hide();
			// }

			// German skills
			if (!jQuery('input[name="german_skills"]:checked').length) {
				jQuery('.errors.german_skills_err').show();
				isValid = false;
			} else {
				jQuery('.errors.german_skills_err').hide();
			}

			// Primary Level
			if (!jQuery('input[name="gernam_primary"]:checked').length || !jQuery('input[name="english_primary"]:checked').length || !jQuery('input[name="french_primary"]:checked').length || !jQuery('input[name="maths_primary"]:checked').length) {
				jQuery('.errors.primary_err').show();
				isValid = false;
			} else {
				jQuery('.errors.primary_err').hide();
			}

			// Secondary Level
			if (!jQuery('input[name="gernam_secondary"]:checked').length || !jQuery('input[name="english_secondary"]:checked').length || !jQuery('input[name="french_secondary"]:checked').length || !jQuery('input[name="maths_secondary"]:checked').length || !jQuery('input[name="latin_secondary"]:checked').length || !jQuery('input[name="organic_secondary"]:checked').length || !jQuery('input[name="chemistry_secondary"]:checked').length || !jQuery('input[name="physics_secondary"]:checked').length) {
				jQuery('.errors.secondary_error').show();
				isValid = false;
			} else {
				jQuery('.errors.secondary_error').hide();
			}

			// Matura Level
			if (!jQuery('input[name="gernam_matura"]:checked').length || !jQuery('input[name="english_matura"]:checked').length || !jQuery('input[name="french_matura"]:checked').length || !jQuery('input[name="maths_matura"]:checked').length || !jQuery('input[name="latin_matura"]:checked').length || !jQuery('input[name="organic_matura"]:checked').length || !jQuery('input[name="chemistry_matura"]:checked').length || !jQuery('input[name="story_matura"]:checked').length || !jQuery('input[name="geography_matura"]:checked').length || !jQuery('input[name="accounting_matura"]:checked').length || !jQuery('input[name="physics_matura"]:checked').length) {
				jQuery('.errors.matura_error').show();
				isValid = false;
			} else {
				jQuery('.errors.matura_error').hide();
			}

			return isValid;
		}


		jQuery(".next.main_first_step").click(function () {
			current_fs = jQuery(this).parent();
			next_fs = jQuery(this).parent().next();

			if (!validateStep1(current_fs)) {
				return false;
			}

			jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");

			next_fs.show();

			jQuery('#contact_detail').addClass('completed');

			current_fs.animate({ opacity: 0 }, {
				step: function (now) {
					opacity = 1 - now;

					current_fs.css({
						'display': 'none',
						'position': 'relative'
					});
					next_fs.css({ 'display': 'flex', 'opacity': opacity });
				},
				duration: 500
			});
		});

		jQuery(".next.main_second_step").click(function () {
			current_fs = jQuery(this).parent();
			next_fs = jQuery(this).parent().next();

			if (!validateStep2(current_fs)) {
				return false;
			}

			jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");

			next_fs.show();

			jQuery('#qualifications').addClass('completed');

			current_fs.animate({ opacity: 0 }, {
				step: function (now) {
					opacity = 1 - now;

					current_fs.css({
						'display': 'none',
						'position': 'relative'
					});
					next_fs.css({ 'display': 'flex', 'opacity': opacity });
				},
				duration: 500
			});
		});

		jQuery(".next.main_third_step").click(function () {
			current_fs = jQuery(this).parent();
			next_fs = jQuery(this).parent().next();

			if (!validateStep3(current_fs)) {
				return false;
			}

			jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");

			jQuery('#possible_uses').addClass('completed');

			next_fs.show();

			current_fs.animate({ opacity: 0 }, {
				step: function (now) {
					opacity = 1 - now;

					current_fs.css({
						'display': 'none',
						'position': 'relative'
					});
					next_fs.css({ 'display': 'flex', 'opacity': opacity });
				},
				duration: 500
			});
		});

		jQuery(".previous").click(function () {
			current_fs = jQuery(this).parent();
			previous_fs = jQuery(this).parent().prev();

			jQuery("#progressbar li").eq(jQuery("fieldset").index(current_fs)).removeClass("active");

			previous_fs.show();

			current_fs.animate({ opacity: 0 }, {
				step: function (now) {
					opacity = 1 - now;

					current_fs.css({
						'display': 'none',
						'position': 'relative'
					});
					previous_fs.css({ 'display': 'flex', 'opacity': opacity });
				},
				duration: 500
			});
		});

		// Buy Now Button 
		jQuery('body').on('click', 'button.buy_now', function (e) {
			var productId = jQuery(this).data('product-id');
			var groupId = jQuery(this).data('groupid');

			jQuery.ajax({
				url: frontend_ajax.ajaxurl,
				type: 'POST',
				data: {
					action: 'custom_buy_now',
					product_id: productId,
					group_id: groupId,
				},
				success: function (response) {
					if (response.success) {

						window.location.href = '/<?php echo esc_attr( weglot_get_current_language() ); ?>/zur-kasse';								
					} else {
						alert('There was an error adding the product to the cart.');
					}
				}
			});
		});
		// jQuery('#cart_link').on('click', function(e) {
		//     e.preventDefault();
		//     jQuery('#overlay, #cart_popup').show();
		//     jQuery('body').addClass('body_fix');
		// 	alert();
		// });

		// jQuery('#closePopup').on('click', function(e) {
		//     e.preventDefault();
		//     jQuery('#overlay, #cart_popup').hide();
		//     jQuery('body').removeClass('body_fix');
		// });



	});
	jQuery(".purchase_group_id").select2({
		closeOnSelect: false
		// minimumResultsForSearch: -1
	});

</script>

<?php
echo '</body>';
echo '</html>';
