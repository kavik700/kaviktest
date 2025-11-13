<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use WpProQuiz_Model_QuestionMapper, DateTime, DateTimeZone, LD_QuizPro;

class Core {
    const COLOR_COMBINATION_ITEM_COUNT = 8;

    public function __construct() {
        add_filter( 'learndash_template', array($this, 'gedanken_aussern_quiz_result_box'), 10, 3 );

        add_filter( 'learndash_template', function( $filepath, $name ) {
            // Skip if the filepath is already our custom path
            if (strpos($filepath, CUSTOM_QUIZ_FILE_PATH) === 0) {
                return $filepath;
            }

            if( ! in_array( $name, array( 'shortcodes/profile/quiz-row.php', 'quiz/partials/show_quiz_questions_box.php', 'modules/course-steps.php', 'quiz/partials/show_quiz_check_page_box.php', 'quiz/partials/show_quiz_time_limit_box.php' ), true )  ) {
                return $filepath;
            }

            switch( $name ) {
                case 'shortcodes/profile/quiz-row.php':
                    return CUSTOM_QUIZ_FILE_PATH . 'templates/quiz-row.php';

                case 'quiz/partials/show_quiz_questions_box.php':
                    return CUSTOM_QUIZ_FILE_PATH . 'templates/show_quiz_questions_box.php';

                case 'quiz/partials/show_quiz_check_page_box.php':
                    return CUSTOM_QUIZ_FILE_PATH . 'templates/show_quiz_check_page_box.php';

                case 'modules/course-steps.php':
                    return CUSTOM_QUIZ_FILE_PATH . 'templates/course-steps.php';

                case 'quiz/partials/show_quiz_time_limit_box.php':
                    return CUSTOM_QUIZ_FILE_PATH . 'templates/show_quiz_time_limit_box.php';
            }

            return $filepath;
        }, 10, 2 );

        add_filter( 'learndash_ques_free_answer_correct', function( $correct, $questionData, $userResponse ) {
            $multiclass_question_type = get_post_meta( $questionData['question_post_id'], '_multiclass_question_type', true );

            switch( $multiclass_question_type ) {
                case 'color_combination':
                    $color_combination_points = $this->check_points($userResponse);

                    $total_point = self::COLOR_COMBINATION_ITEM_COUNT;
                    $points_to_pass = $total_point/2;
    
                    return ($color_combination_points>=$points_to_pass);
                break;

                case 'concentration':
                    $correct_points = (int) $userResponse;
                    
                    $question_mapper = new WpProQuiz_Model_QuestionMapper();
                    $question = $question_mapper->fetch($questionData['id']);
                    $total_point = $question->getPoints();
                    
                    // Calculate percentage of correct answers (70% threshold)
                    $percentage = ($correct_points / $total_point) * 100;
                    return ($percentage >= 70);
                break;

                case 'numerical_processing':
                    $args = explode(':', $userResponse);

                    if( is_array( $args ) && 2 === count($args) && $args[0] === $args[1] ) {
                        return true;
                    }
                break;
            }
            
            return $correct;
        }, 10, 3 );

        add_filter( 'learndash_ques_free_answer_pts', function($points, $questionData, $userResponse) {
            $multiclass_question_type = get_post_meta( $questionData['question_post_id'], '_multiclass_question_type', true );

            switch( $multiclass_question_type ) {
                case 'color_combination':
                    $answers = $this->decode( $userResponse );
                    $color_combination_points = $this->check_points($userResponse);
        
                    $points += $color_combination_points;
        
                return $points;

                case 'concentration':
                    $multiclass_question_type = get_post_meta( $questionData['question_post_id'], '_multiclass_question_type', true );
                
                    $correct_rounds = $userResponse;
                    $points += $correct_rounds;

                return $points;

                case 'numerical_processing':
                    $args = explode(':', $userResponse);

                    if( is_array( $args ) && 2 === count($args) && $args[0] === $args[1] ) {
                        $points++;
                    }
        
                return $points;
            }

            return $points;
        }, 10, 3 );

        


        add_filter('wp_insert_post_data', array( $this, 'override_post_content_with_question_shortcode' ), 10, 2);

        add_shortcode( 'question_organizational_skills', array( $this, 'render_question_organizational_skills' ) );
        add_shortcode( 'color_combination', array( $this, 'render_color_combination' ) );
        add_shortcode( 'concentration', array( $this, 'render_concentration' ) );

        add_action('wp_enqueue_scripts', function() {
            if (is_page() && !is_page(12680)) {
                return;
            }
            
            wp_enqueue_script('custom-quiz-script', CUSTOM_QUIZ_URL . 'assets/build/main.js', ['jquery', 'jquery-ui-core', 'jquery-ui-draggable'], MC_CUSTOM_QUIZ_TYPES_VERSION, true);
            wp_localize_script('custom-quiz-script', 'customQuizAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'i18n' => array(
                    'timeIsUp' => esc_html__( 'Time is up!', 'custom-quiz-types-for-multiclass' ),
                    'wrongQuestions' => esc_html__( 'Wrong Questions', 'custom-quiz-types-for-multiclass' ),
                    'correctQuestions' => esc_html__( 'Correct Questions', 'custom-quiz-types-for-multiclass' ),
                    'reportProblemError' => esc_html__( 'An error occurred while submitting your report. Please try again. If the error persists, send an email to info@multiclass.ch detailing your issue.', 'custom-quiz-types-for-multiclass' ),
                    'reportProblemSuccess' => esc_html__( 'Your report has been submitted successfully.', 'custom-quiz-types-for-multiclass' ),
                    'sendingReport' => esc_html__( 'Dein Hinweis wird gesendet...', 'custom-quiz-types-for-multiclass' ),
                    'confirmReload' => esc_html__( 'This action will reload the page. Do you want to continue?', 'custom-quiz-types-for-multiclass' ),
                ),
                'get_question_modal_nonce'=>wp_create_nonce('get_question_modal'),
                'get_modal_availabilities_nonce'=>wp_create_nonce('get_modal_availabilities'),
                'delete_and_archive_quiz_data_nonce'=>wp_create_nonce('delete_and_archive_quiz_data_' . get_the_ID()),
                'colorCombinationTotalPoint'=>self::COLOR_COMBINATION_ITEM_COUNT,
                'calculatorIcon' => CUSTOM_QUIZ_URL . 'assets/lib/jquery.calculator.package-2.0.1/calculator.png',
                'adobeClientId'=>ADOBE_CLIENT_ID
            ]);
            
            // Audio tracking localization
            wp_localize_script('custom-quiz-script', 'mcAudioTracking', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mc_audio_tracking'),
                'i18n' => [
                    'youCanListen' => __('You can listen to this audio %d more %s (%d/%d).', 'custom-quiz-types-for-multiclass'),
                    'noMorePlays' => __('No more plays available (used %d/%d).', 'custom-quiz-types-for-multiclass'),
                    'time' => __('time', 'custom-quiz-types-for-multiclass'),
                    'times' => __('times', 'custom-quiz-types-for-multiclass'),
                    'limitReached' => __('You have reached the listening limit for this audio.', 'custom-quiz-types-for-multiclass'),
                ]
            ]);
        
            wp_enqueue_style('custom-quiz-style', CUSTOM_QUIZ_URL . 'assets/build/main.css', [], MC_CUSTOM_QUIZ_TYPES_VERSION);
            wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200');

             // Enqueue the CSS file
            wp_enqueue_style( 'jquery-calculator-css', CUSTOM_QUIZ_URL . '/assets/lib/jquery.calculator.package-2.0.1/jquery.calculator.css' );

            wp_enqueue_script( 'jquery-plugin', CUSTOM_QUIZ_URL . '/assets/lib/jquery.calculator.package-2.0.1/jquery.plugin.js', array('jquery'), null, true );

            // Enqueue the jQuery calculator script
            wp_enqueue_script( 'jquery-calculator-js', CUSTOM_QUIZ_URL . '/assets/lib/jquery.calculator.package-2.0.1/jquery.calculator.js', array('jquery', 'jquery-plugin'), null, true );
        });

        add_action('admin_enqueue_scripts', function() {
            wp_enqueue_script('custom-quiz-script', CUSTOM_QUIZ_URL . 'admin/assets/build/main.js', ['jquery', 'wp-element', 'wp-editor', 'wp-data', 'wpProQuiz_admin_javascript'], MC_CUSTOM_QUIZ_TYPES_VERSION, true);
            wp_enqueue_style('custom-quiz-style', CUSTOM_QUIZ_URL . 'admin/assets/build/main.css', [], MC_CUSTOM_QUIZ_TYPES_VERSION);
            wp_localize_script('custom-quiz-script', 'customQuizAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'colorCombinationTotalPoint'=>self::COLOR_COMBINATION_ITEM_COUNT,
                'dragDropGroupNonce' => wp_create_nonce('mc_drag_drop_groups_nonce')
            ]);
        
            // Fix: Use unique handle for flatpickr CSS
            wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], '4.6.13');
        });

        add_action('admin_footer', array( $this, 'custom_pdf_media_button' ));

        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox_data']);

        
        add_action('learndash-content-tabs-content-before', array($this, 'topic_time_limit'));

        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_script('adobe-view-sdk', 'https://acrobatservices.adobe.com/view-sdk/viewer.js', [], null, true);
        });

        add_action( 'mc_report_problem', array( $this, 'render_report_problem' ), 10, 3 );
    }

    public function render_report_problem($resource_type, $resource_id, $report_problem_options_args=array()) {
        $report_problem_options = array(
            'is_not_correct' => esc_html__( 'Is not correct', 'custom-quiz-types-for-multiclass' ),
            'has_errors' => esc_html__( 'Has errors', 'custom-quiz-types-for-multiclass' ),
            'is_unclear' => esc_html__( 'Is unclear', 'custom-quiz-types-for-multiclass' ),
            'is_confusing' => esc_html__( 'Is confusing', 'custom-quiz-types-for-multiclass' ),
            'other' => esc_html__( 'Other', 'custom-quiz-types-for-multiclass' ),
        );

        if( ! empty( $report_problem_options_args ) ) {
            $report_problem_options = $report_problem_options_args;
        }

        require CUSTOM_QUIZ_FILE_PATH . 'templates/report-problem.php';
    }

    public function gedanken_aussern_quiz_result_box($filepath, $name, $args) {
        if( 'quiz/partials/show_quiz_result_box.php' !== $name  ) {
			return $filepath;
		}

		$quiz_post_id = $args['quiz']->getPostId();

		$course_id = learndash_get_course_id( $quiz_post_id );

		return CUSTOM_QUIZ_FILE_PATH . 'templates/show_quiz_result_box.php';
    }

    public static function has_course_time_lock() {
        global $post;
    
        if (!$post) {
            return false;
        }
    
        $course_id = learndash_get_course_id($post->ID);
        if (!$course_id) {
            return false;
        }
    
        $current_user_id = get_current_user_id();
        $user_meta_key = '_time_lock_gmt_' . $course_id;
    
        // Get stored lock data (deserialized)
        $stored_lock_data = get_user_meta($current_user_id, $user_meta_key, true);

        if( empty( $stored_lock_data ) ) {
            return false;
        }

        $stored_lock_data = $stored_lock_data ? json_decode($stored_lock_data, true) : [];
    
        if (isset($stored_lock_data['lock_time_gmt'])) {
            $stored_lock_time_gmt = $stored_lock_data['lock_time_gmt'];
    
            // Convert GMT time to the site's timezone
            $stored_lock_time = new DateTime($stored_lock_time_gmt, new DateTimeZone('GMT'));
            
            // Determine the site's timezone
            $site_timezone = get_option('timezone_string');

            if (!$site_timezone) {
                // Fallback to gmt_offset if timezone_string is not set
                $gmt_offset = get_option('gmt_offset');
                $site_timezone = $gmt_offset ? timezone_name_from_abbr('', $gmt_offset * 3600, 0) : 'UTC';
            }

            try {
                $stored_lock_time->setTimezone(new DateTimeZone($site_timezone));
            } catch (Exception $e) {
                // Fallback to 'UTC' in case of an invalid timezone
                $stored_lock_time->setTimezone(new DateTimeZone('UTC'));
            }
    
            // Format to Swiss format (dd.mm.YYYY H:i:s)
            $formatted_date = $stored_lock_time->format('d.m.Y');
            $formatted_time = $stored_lock_time->format('H:i:s');

            $current_time = new DateTime('now', new DateTimeZone($site_timezone));

            return array(
                'status' => $current_time < $stored_lock_time,
                'formatted_date' => $formatted_date,
                'formatted_time' => $formatted_time,
            );
        }

        return array(
            'status' => false,
        );
    } 

    function topic_time_limit() {
        global $post;
        $mc_time_limit_seconds = (int) get_post_meta( $post->ID, '_mc_question_time_limit', true );

         if( $mc_time_limit_seconds > 0 ): ?>
            <div data-mc_time_limit="<?php echo esc_attr( $mc_time_limit_seconds ); ?>" class="timer-container">
                    <?php esc_html_e( 'Remaining time:', 'custom-quiz-types-for-multiclass' ); ?>
                    <span class="timer">0:00</span>
                    <div class='times-up'></div>
            </div>
        <?php
        endif;
    }

    function countMatchingItems(array $array1, array $array2): int {
        $matchCount = 0;
    
        // Ensure both arrays have the same number of elements for comparison
        $length = min(count($array1), count($array2));
    
        for ($i = 0; $i < $length; $i++) {
            if ($array1[$i] === $array2[$i]) {
                $matchCount++;
            }
        }
    
        return $matchCount;
    }

    private function check_points($userResponse) {
        $answers = $this->decode( $userResponse );
        $color_combination_points = 0;

        foreach($answers as $answer) {
            $args = explode('|', $answer);

            if( ! is_array( $args ) || 2 !== count( $args ) ) {
                return false;
            }

            $expected_answer = explode( ',', self::decrypt_data($args[0]) );
            $given_answer = explode( ',', $args[1] );

            $color_combination_points += $this->countMatchingItems( $expected_answer, $given_answer );
        }

        return $color_combination_points;
    }

    function decode($str) {
        $obj = [];
        $items = explode(':', $str);
    
        foreach ($items as $item) {
            $parts = explode('|', $item);
    
            if (count($parts) === 3) {
                $key = $parts[0]; // Use the index as the key
                $value = $parts[1] . '|' . $parts[2]; // Concatenate the second and third parts for the value
    
                $obj[$key] = $value;
            }
        }
    
        return $obj;
    }

    function custom_pdf_media_button() {
        // Only add this to the post editor screen
        if (get_current_screen()->base !== 'post') {
            return;
        }
    
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // Extend the media button functionality
                $('#insert-media-button').on('click', function (event) {
                    event.preventDefault();
    
                    var custom_uploader = wp.media({
                        title: 'Select a PDF',
                        library: { type: 'application/pdf' },
                        button: { text: 'Insert PDF' },
                        multiple: false
                    }).on('select', function () {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        
                        // Insert the PDF Embedder plugin shortcode into the editor
                        wp.media.editor.insert('[pdf-embedder url="' + attachment.url + '"]');
                    }).open();
                });
            });
        </script>
        <?php
    }

      /**
         * Encrypt a string.
         *
         * @param string $data The data to encrypt.
         * @return string The encrypted data.
         */
        static function encrypt_data($data) {
            $key = hash('sha256', CUSTOM_QUIZ_TYPES_ENCRYPTION, true);
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        }

        /**
         * Decrypt a string.
         *
         * @param string $data The data to decrypt.
         * @return string The decrypted data.
         */
        static function decrypt_data($data) {
            $key = hash('sha256', CUSTOM_QUIZ_TYPES_ENCRYPTION, true);
            $data = base64_decode($data);
            $iv_length = openssl_cipher_iv_length('aes-256-cbc');
            $iv = substr($data, 0, $iv_length);
            $encrypted_data = substr($data, $iv_length);
            return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
        }

    function override_post_content_with_question_shortcode($data, $postarr) {
        if( intval( $postarr['ID'] ) < 1 ) {
            return $data;
        }

        // Check the post type if you only want to modify a specific post type
        if ($data['post_type'] === 'sfwd-question') {
            $question_type = $_POST['multiclass_question_type'];

            switch( $question_type ) {
                case 'organization_calendar':
                    $shortcode = '[question_organizational_skills id="'.$postarr['ID'].'"]';
                break;

                case 'color_combination':
                    $shortcode = '[color_combination id="'.$postarr['ID'].'"]';
                break;

                case 'concentration':
                    $shortcode = '[concentration id="'.$postarr['ID'].'"]';
                break;

                default:
                return $data;
            }

            $data['post_content'] = $shortcode;
        }
    
        return $data;
    }

    private function shuffle_colors() {
        // Define the colors you want to use
        $colors = ['red', 'blue', 'black'];

        // Create an array with exactly 8 items
        $initialColors = [];

        // Fill the array until it has 8 items
        while (count($initialColors) < self::COLOR_COMBINATION_ITEM_COUNT) {
            $initialColors[] = $colors[array_rand($colors)];
        }

        // Randomly shuffle the colors
        shuffle($initialColors);

        return $initialColors;
    }

    function render_concentration($args) {
        $id = $args['id'];

        $question_id = get_post_meta( $id, 'question_pro_id', true );
        
        // Get the question object to access points
        $question_mapper = new WpProQuiz_Model_QuestionMapper();
        $question = $question_mapper->fetch($question_id);
        $round = $question->getPoints();

        ?>
        <div class="concentration-container">
            <div class="round-header">
                <div class="round-badge">
                    <span class="round-label"><?php esc_html_e( 'Round', 'custom-quiz-types-for-multiclass' ); ?></span>
                    <span class="round-numbers">
                        <span class="current-round">1</span>
                        <span class="round-separator">/</span>
                        <span class="total-rounds"><?php echo esc_html( $round ); ?></span>
                    </span>
                </div>
            </div>
            <h3><?php esc_html_e( 'Enter Coordinates', 'custom-quiz-types-for-multiclass' ); ?></h3>
            <p><?php esc_html_e( 'One of the nine fields will appear red. It has precisely defined coordinates. Capture the coordinates as quickly as possible. Enter them into the provided field without spaces or commas.', 'custom-quiz-types-for-multiclass' ); ?></p>

            <div class="grid-container">
                <!-- Y-axis Labels -->
                <div></div>
                <div class="axis-label"></div>
                <div class="axis-label"></div>
                <div class="axis-label"></div>

                <!-- Grid Rows -->
                <div class="axis-label has-y-axis-label"><span id="y-axis-label"><?php esc_html_e( 'Y-Axis', 'custom-quiz-types-for-multiclass' ); ?></span>3</div>
                <div class="grid-item" data-x="1" data-y="3"></div>
                <div class="grid-item" data-x="2" data-y="3"></div>
                <div class="grid-item" data-x="3" data-y="3"></div>
                
                <div class="axis-label">2</div>
                <div class="grid-item" data-x="1" data-y="2"></div>
                <div class="grid-item" data-x="2" data-y="2"></div>
                <div class="grid-item" data-x="3" data-y="2"></div>
                
                <div class="axis-label">1</div>
                <div class="grid-item" data-x="1" data-y="1"></div>
                <div class="grid-item" data-x="2" data-y="1"></div>
                <div class="grid-item" data-x="3" data-y="1"></div>

                <!-- X-axis Labels -->
                <div></div>
                <div class="axis-label">1</div>
                <div class="axis-label">2</div>
                <div class="axis-label has-x-axis-label ">3<span id="x-axis-label"><?php esc_html_e( 'X-Axis', 'custom-quiz-types-for-multiclass' ); ?></span></div>
            </div>

            <p style="margin-top:15px"><?php esc_html_e( 'Enter the coordinates here without spaces or commas (1st digit X-axis, 2nd digit Y-axis):', 'custom-quiz-types-for-multiclass' ); ?></p>
            <input type="text" id="coordinate-input" placeholder="<?php esc_attr_e( 'e.g. 23', 'custom-quiz-types-for-multiclass' ); ?>" maxlength="2">
        </div>
        <?php
    }

    function render_color_combination($args) {
        $id = $args['id'];

        $question_id = get_post_meta( $id, 'question_pro_id', true );

        $mc_time_limit_seconds = get_post_meta( $id, '_mc_question_time_limit', true );

        $combinations = array(
            $this->shuffle_colors()
        );
        ?>

       <div class="color-combination-container" data-mc_time_limit="<?php echo esc_attr( $mc_time_limit_seconds ); ?>" data-signature="<?php echo esc_attr( self::encrypt_data( json_encode( $combinations ) ) ); ?>">

        <?php foreach($combinations as $id=>$colors): ?>
        <div data-combination_id="<?php echo esc_attr( $id ); ?>" class="color-combination hide">
            <h4><?php echo esc_html_e( 'Color Combination', 'custom-quiz-types-for-multiclass' ); ?></h4>

            <div class="circle-container model hide">
                <?php
                // Generate circles with initial colors
                foreach ($colors as $color) {
                    echo "<div class='circle' style='background-color: $color;' data-color='0'></div>";
                }
                ?>
            </div>

            <div class="circle-container answers hide" data-signature="<?php echo esc_attr( self::encrypt_data( implode(',', $colors ) ) ); ?>">
                <?php
                for( $i = 0; $i < count( $colors ); $i++ ) {
                    echo "<div class='circle' data-color='0'></div>";
                }
                ?>
            </div>

            <!-- <div class="actions">
                <a class="hide remember-btn"><?php esc_html_e( 'Show answers (cuts 20 seconds)', 'custom-quiz-types-for-multiclass' ); ?></a>
            </div> -->
        </div>
        <?php
        endforeach;
        ?>
        </div>
        <?php
    }

    /**
     * Calculates the duration between two datetime strings.
     *
     * @param string $timeRange A string in the format "YYYY-MM-DD HH:MM to YYYY-MM-DD HH:MM".
     * @return string The duration in "HH:MM" format.
     */
    function calculateDuration($timeRange)
    {
        // Split the raw string into start and end datetime strings
        list($startDateTimeStr, $endDateTimeStr) = explode(' to ', $timeRange);

        // Create DateTime objects from the strings
        $startDateTime = new DateTime($startDateTimeStr);
        $endDateTime = new DateTime($endDateTimeStr);

        // Calculate the interval
        $interval = $startDateTime->diff($endDateTime);

        // Calculate hours and minutes
        $hours = $interval->h + ($interval->days * 24); // Include days as hours if they span multiple days
        $minutes = $interval->i;

        // Return the result in the desired format
        return sprintf("%02d:%02d", $hours, $minutes);
    }

    /**
     * Calculates the total duration in minutes between two datetime strings.
     *
     * @param string $timeRange A string in the format "YYYY-MM-DD HH:MM to YYYY-MM-DD HH:MM".
     * @return int The total duration in minutes.
     */
    function calculateTotalMinutes($timeRange)
    {
        // Split the raw string into start and end datetime strings
        list($startDateTimeStr, $endDateTimeStr) = explode(' to ', $timeRange);

        // Create DateTime objects from the strings
        $startDateTime = new DateTime($startDateTimeStr);
        $endDateTime = new DateTime($endDateTimeStr);

        // Calculate the interval
        $interval = $startDateTime->diff($endDateTime);

        // Calculate total minutes
        $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        // Return the total duration in minutes
        return $totalMinutes;
    }

    public static function get_iso8601_date($datetime) {
        $dateTime = new DateTime($datetime);
        return $dateTime->format('Y-m-d\TH:i:s');
    }

    public function render_question_organizational_skills($args) {
        $id = $args['id'];

        $question_id = get_post_meta( $id, 'question_pro_id', true );

        $question_mapper   = new WpProQuiz_Model_QuestionMapper();
        $question = $question_mapper->fetch( $question_id );

        $answer_data = $question->getAnswerData();

        $default_events = array();

        foreach($answer_data as $id=>$answer) {
            if( $answer->getPoints() > 0 ) {
                continue;
            }

            list($startDateTimeStr, $endDateTimeStr) = explode(' to ', $answer->getAnswer());

            $default_events[] = array(
                'id'=>$id,
                'title'=>$answer->getSortString(),
                'start'=>self::get_iso8601_date($startDateTimeStr),
                'end'=>self::get_iso8601_date($endDateTimeStr),
                'extendedProps'=>array(
                    'is_default'=>true
                )
            );
        }

        $answer_array_positions = array();
        foreach ( $answer_data as $q_idx => $q ) {
            $datapos                             = LD_QuizPro::datapos( $question->getId(), $q_idx );
            $answer_array_positions[$q_idx] = $datapos;
        }

        ?>
            <div data-default_events="<?php echo esc_html(json_encode($default_events)); ?>" class="calendar"></div>

            <div class='external-events-backup'>
                <p>
                    <strong><?php esc_html_e( 'Events', 'custom-quiz-types-for-multiclass' ); ?></strong>
                </p>

                <?php
                foreach( $answer_data as $key => $answer ): if( empty( $answer->getSortString() ) || $answer->getPoints() < 1 ) continue; $duration = $this->calculateDuration( $answer->getAnswer() );

                $pos = $answer_array_positions[$key];

                ?>
                <div style="padding:<?php echo $this->calculateTotalMinutes($answer->getAnswer())/6; ?>px 10px" data-duration="<?php echo esc_attr( $duration ); ?>" data-id="<?php echo esc_attr($key); ?>" data-pos="<?php echo esc_attr($pos); ?>" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                    <div class='fc-event-main'><?php echo esc_html( $answer->getSortString() ); ?> (<?php echo esc_attr( $duration ); ?>)</div>
                </div>
                <?php endforeach; ?>
            </div>

             <div class='external-events-container'>
                <p>
                    <strong><?php esc_html_e( 'Events', 'custom-quiz-types-for-multiclass' ); ?></strong>
                </p>

                <div class="external-events">
                    <?php 
                    // Shuffle the keys while preserving key-value relationships
                    $keys = array_keys($answer_data);
                    shuffle($keys);
                    
                    foreach( $keys as $key ): 
                        $answer = $answer_data[$key];
                        if( empty( $answer->getSortString() ) || $answer->getPoints() < 1 ) continue; 
                        $duration = $this->calculateDuration( $answer->getAnswer() ); ?>
                    <div style="padding:<?php echo $this->calculateTotalMinutes($answer->getAnswer())/6; ?>px 10px" data-duration="<?php echo esc_attr( $duration ); ?>" data-id="<?php echo esc_attr($key); ?>" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                        <div class='fc-event-main'><?php echo esc_html( $answer->getSortString() ); ?> (<?php echo esc_attr( $duration ); ?>)</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php
    }

    // Function to add the metabox
    public function add_metabox() {
        add_meta_box(
            'question_time_limit',
            esc_html__('Time Limit (seconds)', 'custom-quiz-types-for-multiclass'),
            [$this, 'display_metabox'],
            array( 'sfwd-question', 'sfwd-topic' ),
            'side',
            'high'
        );

        // Add new metabox for question instructions
        add_meta_box(
            'question_instructions',
            esc_html__('Question Instructions', 'custom-quiz-types-for-multiclass'),
            [$this, 'display_instructions_metabox'],
            'sfwd-question',
            'normal',
            'high'
        );

         // Add new metabox for quiz settings
         add_meta_box(
            'quiz_display_settings',
            esc_html__('Quiz Display Settings', 'custom-quiz-types-for-multiclass'),
            [$this, 'display_quiz_settings_metabox'],
            'sfwd-quiz',
            'side',
            'high'
        );

        // Add new metabox for ungraded response explanation
        add_meta_box(
            'ungraded_response_explanation',
            esc_html__('Ungraded Response Explanation', 'custom-quiz-types-for-multiclass'),
            [$this, 'display_ungraded_response_metabox'],
            'sfwd-question',
            'normal',
            'high'
        );
    }

    // Function to display the metabox
    public function display_metabox($post) {
        // Nonce field for security
        wp_nonce_field('save_question_time_limit', 'question_time_limit_nonce');

        // Get the current value of the time limit
        $question_time_limit = get_post_meta($post->ID, '_mc_question_time_limit', true);
        $tri_coutdown_enabled = get_post_meta($post->ID, '_mc_tri_coutdown_enabled', true);

        ?>
        <p>
            <label for="question_time_limit"><?php esc_html_e('Enter the time limit in seconds:', 'custom-quiz-types-for-multiclass'); ?></label>
            <input type="number" id="question_time_limit" name="question_time_limit" value="<?php echo esc_attr($question_time_limit); ?>" min="0" style="width:100%;" />
        </p>

        <p>
            
            <input <?php checked($tri_coutdown_enabled, 'yes'); ?> type="checkbox" value="yes" id="tri_coutdown" name="tri_coutdown" style="width:auto;" />
            <label for="tri_coutdown"><?php esc_html_e('Enable 3-2-1 countdown:', 'custom-quiz-types-for-multiclass'); ?></label>
        </p>
        <?php
    }

    // New function to display quiz settings metabox
    public function display_quiz_settings_metabox($post) {
        wp_nonce_field('save_quiz_display_settings', 'quiz_display_settings_nonce');
        
        $show_question_titles = get_post_meta($post->ID, '_mc_show_question_titles', true);
        ?>
        <p>
            <input <?php checked($show_question_titles, 'yes'); ?> type="checkbox" value="yes" id="show_question_titles" name="show_question_titles" style="width:auto;" />
            <label for="show_question_titles"><?php esc_html_e('Show question titles?', 'custom-quiz-types-for-multiclass'); ?></label>
        </p>
        <?php
    }

    // Add this new method to display the ungraded response explanation metabox
    public function display_ungraded_response_metabox($post) {
        // Nonce field for security
        wp_nonce_field('save_ungraded_response_explanation', 'ungraded_response_explanation_nonce');

        // Get the current value of the explanation
        $explanation = get_post_meta($post->ID, '_mc_ungraded_response_explanation', true);
        ?>
        <p>
            <textarea 
                id="ungraded_response_explanation" 
                name="ungraded_response_explanation" 
                style="width: 100%; min-height: 150px;"
            ><?php echo esc_textarea($explanation); ?></textarea>
        </p>
        <?php
    }

    // Add this new method to display the instructions metabox
    public function display_instructions_metabox($post) {
        // Nonce field for security
        wp_nonce_field('save_question_instructions', 'question_instructions_nonce');

        // Get the current value of the instructions
        $instructions = get_post_meta($post->ID, '_mc_question_instructions', true);
        ?>
        <p>
            <label for="question_instructions"><?php esc_html_e('Enter instructions that will appear above the question buttons:', 'custom-quiz-types-for-multiclass'); ?></label>
            <textarea 
                id="question_instructions" 
                name="question_instructions" 
                style="width: 100%; min-height: 100px;"
                placeholder="<?php esc_attr_e('Enter instructions for the question here...', 'custom-quiz-types-for-multiclass'); ?>"
            ><?php echo esc_textarea($instructions); ?></textarea>
        </p>
        <?php
    }

    // Function to save the metabox data
    public function save_metabox_data($post_id) {
        // Check if nonce is set
        if (!isset($_POST['question_time_limit_nonce']) && !isset($_POST['quiz_display_settings_nonce']) && !isset($_POST['ungraded_response_explanation_nonce']) && !isset($_POST['question_instructions_nonce'])) {
            return;
        }

        // Check if it's an autosave (don't save data during autosave)
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Handle question time limit settings
        if (isset($_POST['question_time_limit_nonce'])) {
            // Verify the nonce for security
            if (wp_verify_nonce($_POST['question_time_limit_nonce'], 'save_question_time_limit')) {
                // Sanitize and save the input data
                if (isset($_POST['question_time_limit'])) {
                    $time_limit = sanitize_text_field($_POST['question_time_limit']);
                    update_post_meta($post_id, '_mc_question_time_limit', $time_limit);
                } else {
                    delete_post_meta($post_id, '_mc_question_time_limit');
                }

                if (isset($_POST['tri_coutdown'])) {
                    $tri_coutdown_enabled = sanitize_text_field($_POST['tri_coutdown']);
                    update_post_meta($post_id, '_mc_tri_coutdown_enabled', $tri_coutdown_enabled);
                } else {
                    delete_post_meta($post_id, '_mc_tri_coutdown_enabled');
                }
            }
        }

        // Handle quiz display settings
        if (isset($_POST['quiz_display_settings_nonce'])) {
            // Verify the nonce for security
            if (wp_verify_nonce($_POST['quiz_display_settings_nonce'], 'save_quiz_display_settings')) {
                if (isset($_POST['show_question_titles'])) {
                    update_post_meta($post_id, '_mc_show_question_titles', 'yes');
                } else {
                    delete_post_meta($post_id, '_mc_show_question_titles');
                }
            }
        }

        // Handle ungraded response explanation
        if (isset($_POST['ungraded_response_explanation_nonce'])) {
            // Verify the nonce for security
            if (wp_verify_nonce($_POST['ungraded_response_explanation_nonce'], 'save_ungraded_response_explanation')) {
                if (isset($_POST['ungraded_response_explanation'])) {
                    $explanation = wp_kses_post($_POST['ungraded_response_explanation']);
                    update_post_meta($post_id, '_mc_ungraded_response_explanation', $explanation);
                } else {
                    delete_post_meta($post_id, '_mc_ungraded_response_explanation');
                }
            }
        }

        // Handle question instructions
        if (isset($_POST['question_instructions_nonce'])) {
            // Verify the nonce for security
            if (wp_verify_nonce($_POST['question_instructions_nonce'], 'save_question_instructions')) {
                if (isset($_POST['question_instructions'])) {
                    $instructions = wp_kses_post($_POST['question_instructions']);
                    update_post_meta($post_id, '_mc_question_instructions', $instructions);
                } else {
                    delete_post_meta($post_id, '_mc_question_instructions');
                }
            }
        }
    }
}