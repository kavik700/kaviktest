<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use \Multiclass\Core, WpProQuiz_Model_QuestionMapper, DateTime;

class Ajax {
    public function __construct() {
        $endpoints = array(
            'mc_get_answers',
            'mc_report_problem'
        );

        foreach($endpoints as $endpoint) {
            add_action( 'wp_ajax_' . $endpoint, array( $this, $endpoint ) );
        }
    }

    function get_corresponding_date_in_current_week($input_date) {
        // Create a DateTime object from the input date
        $original_date = new DateTime($input_date);
        
        // Get the day of the week for the original date, adjusted for Monday as the start of the week
        $original_week_day = ($original_date->format('N') - 1);
        
        // Get the current date
        $current_date = new DateTime();
        
        // Get the day of the week for the current date, adjusted for Monday as the start of the week
        $current_week_day = ($current_date->format('N') - 1);
        
        // Calculate the difference in days between the original date's weekday and today's weekday
        $day_difference = $original_week_day - $current_week_day;
        
        // Adjust the current date by the day difference
        $current_date->modify("$day_difference days");
        
        // Set the time to match the original date's time
        $current_date->setTime($original_date->format('H'), $original_date->format('i'));
        
        // Return the result as a formatted string
        return $current_date->format('Y-m-d H:i');
    }

    public function mc_get_answers() {
        $questions = $_POST['data'];
        $answers = array();

        foreach($questions as $args) {
            $question_post_id = $args['question_post_id'];
            $question_pro_id = $args['question_pro_id'];

            $question_type = get_post_meta( $question_post_id, '_multiclass_question_type', true );

            $question_mapper   = new WpProQuiz_Model_QuestionMapper();
            $question = $question_mapper->fetch( $question_pro_id );

            switch( $question_type ) {
                case 'color_combination':
                    $signature = $args['signature'];

                    $data = json_decode( Core::decrypt_data( $signature ) );
                    
                break;

                case 'organization_calendar':
                    $answer_data = $question->getAnswerData();

                    $events = array();

                    foreach($answer_data as $id=>$answer) {
                        list($startDateTimeStr, $endDateTimeStr) = explode(' to ', $answer->getAnswer());



                        $events[] = array(
                            'id'=>$id,
                            'title'=>$answer->getSortString(),
                            'start'=>Core::get_iso8601_date($this->get_corresponding_date_in_current_week($startDateTimeStr)),
                            'end'=>Core::get_iso8601_date($this->get_corresponding_date_in_current_week($endDateTimeStr)),
                            'extendedProps'=>array(
                                'is_default'=>(0===intval($answer->getPoints()))
                            )
                        );
                    }

                    $data = array(
                        'events'=>$events
                    );
                break;
            }

            $answers[$question_post_id] = array(
                'type'=>$question_type,
                'data'=>$data,
            );
        }

        wp_send_json_success( $answers );
    }

    public function mc_report_problem() {
        $data = $_POST['data'];

        $resource_id = $data['resource_id'];
        $resource_type = $data['resource_type'];
        $issues = $data['issues'];
        $details = $data['details'];

        $admin_email = get_option('admin_email');
        $subject = __('Multiclass: Problem Reported by User', 'custom-quiz-types-for-multiclass');
        $quiz_url = learndash_get_step_permalink($resource_id);

        switch( $resource_type ) {
            case 'quiz':
                $question_post_id = $data['question_post_id'];
                $question_url = admin_url('post.php?post=' . $question_post_id . '&action=edit');

                $message = sprintf(
                    __("<html><body><p>A problem has been reported on a %s.</p><p><strong>Quiz:</strong> %d - <a href='%s'>%s</a></p><p><strong>Question:</strong> %d - <a href='%s'>%s</a></p><p><strong>Reporting User:</strong> %s (#%d)</p><p><strong>Issues:</strong> %s</p><p><strong>Details:</strong> %s</p></body></html>", 'custom-quiz-types-for-multiclass'),
                    esc_html($resource_type),
                    intval($resource_id),
                    esc_url($quiz_url),
                    esc_url($quiz_url),
                    intval($data['question_post_id']), // Assuming question_id is part of the $data array
                    esc_url($question_url),
                    esc_url($question_url),
                    esc_html(wp_get_current_user()->display_name),
                    intval(wp_get_current_user()->ID),
                    implode(', ', array_map('esc_html', $issues)),
                    !empty($details) ? esc_html($details) : 'N/A'
                );
            break;

            case 'topic':
                $topic_url = learndash_get_step_permalink($resource_id);

                $message = sprintf(
                    __("<html><body><p>A problem has been reported on a %s.</p><p><strong>Topic:</strong> %d - <a href='%s'>%s</a></p><p><strong>Reporting User:</strong> %s (#%d)</p><p><strong>Issues:</strong> %s</p><p><strong>Details:</strong> %s</p></body></html>", 'custom-quiz-types-for-multiclass'),
                    esc_html($resource_type),
                    intval($resource_id),
                    esc_url($topic_url),
                    esc_url($topic_url),
                    esc_html(wp_get_current_user()->display_name),
                    intval(wp_get_current_user()->ID),
                    implode(', ', array_map('esc_html', $issues)),
                    !empty($details) ? esc_html($details) : 'N/A'
                );
            break;
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        if (wp_mail($admin_email, $subject, $message, $headers)) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('There was an error sending the email. Please try again.', 'custom-quiz-types-for-multiclass'));
            }
    }
}