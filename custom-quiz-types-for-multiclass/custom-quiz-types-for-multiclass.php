<?php
/**
 * Plugin Name:     Custom Quiz Types For Multiclass
 * Description:     Adds custom quiz types for Learndash
 * Author:          Mustafa Kapusuz
 * Text Domain:     custom-quiz-types-for-multiclass
 * Domain Path:     /languages
 * Version:         0.38.1
 *
 * @package         Custom_Quiz_Types_For_Multiclass
 */

 define('CUSTOM_QUIZ_TYPES_ENCRYPTION', AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY . NONCE_KEY);
 define( 'CUSTOM_QUIZ_URL', plugin_dir_url(__FILE__) );
 define( 'CUSTOM_QUIZ_FILE_PATH', plugin_dir_path(__FILE__) );

 require_once  plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';

define( 'MC_CUSTOM_QUIZ_TYPES_VERSION', '0.38.1' );

// Initialize installation
new \Multiclass\Install();

new \Multiclass\Core();
new \Multiclass\Metabox_Answer_Types();
new \Multiclass\Modals();
new \Multiclass\Ajax;
new \Multiclass\Image_Drag_Drop;
new \Multiclass\Customer_Contact;
new \Multiclass\Numerical_Processing;
new \Multiclass\Topic_Time_Lock;
new \Multiclass\Exam_Simulation;
new \Multiclass\Question_Metabox();
new \Multiclass\Pictogram;
new \Multiclass\Pictogram_Images();
new \Multiclass\Quiz_Time_Limit_Disabler;
new \Multiclass\Hide_Quiz_Grades;
new \Multiclass\Drag_Drop_Groups();
new \Multiclass\Essay_Teacher_Comment();
new \Multiclass\Student_Essay_Submissions_Shortcode();
new \Multiclass\Course_Essay_Marker();
new \Multiclass\Quizzes_By_Question_Type();
new \Multiclass\Audio();
new \Multiclass\Audio_Metabox();
new \Multiclass\Essay_Word_Counter();

function custom_quiz_types_load_textdomain() {
    load_plugin_textdomain( 'custom-quiz-types-for-multiclass', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'custom_quiz_types_load_textdomain' );