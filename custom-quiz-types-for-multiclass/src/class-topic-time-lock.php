<?php

namespace Multiclass;

defined('ABSPATH') || exit;

class Topic_Time_Lock {
    private $post_type = 'sfwd-topic';
    private $time_meta_key = '_sfwd_topic_time_lock';

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_time_lock_metabox']);
        add_action('save_post', [$this, 'save_time_lock_metabox_data']);
        add_action('template_redirect', [$this, 'handle_frontend_access'], 10);
        add_action('learndash-content-tabs-content-before', array(__CLASS__, 'topic_time_lock_deadline'));
        //add_action('template_redirect', [$this, 'restrict_access_based_on_time_lock'], 20); - disabled due to https://trello.com/c/6QOwgoMz/216-eignungspr%C3%BCfung-ims-st-gallen-pr%C3%BCfungssimulation-topics-are-not-accessible-redirecting-to-the-main-course
    }

    public function add_time_lock_metabox() {
        add_meta_box(
            'sfwd_topic_time_lock',
            esc_html__('Time Lock (in seconds)', 'custom-quiz-types-for-multiclass'),
            [$this, 'render_time_lock_metabox'],
            $this->post_type,
            'side',
            'high'
        );
    }

    public function render_time_lock_metabox($post) {
        $time_lock = get_post_meta($post->ID, $this->time_meta_key, true);
        wp_nonce_field('save_sfwd_topic_time_lock', 'sfwd_topic_time_lock_nonce');
        ?>
        <label for="sfwd_topic_time_lock"><?php esc_html_e('Enter time lock in seconds:', 'custom-quiz-types-for-multiclass'); ?></label>
        <input type="number" name="sfwd_topic_time_lock" id="sfwd_topic_time_lock" value="<?php echo esc_attr($time_lock); ?>" min="0" />
        <?php
    }

    public function save_time_lock_metabox_data($post_id) {
        if (!isset($_POST['sfwd_topic_time_lock_nonce']) || !wp_verify_nonce($_POST['sfwd_topic_time_lock_nonce'], 'save_sfwd_topic_time_lock')) {
            return;
        }

        if (isset($_POST['sfwd_topic_time_lock'])) {
            $time_lock = absint($_POST['sfwd_topic_time_lock']);
            update_post_meta($post_id, $this->time_meta_key, $time_lock);
        }
    }

    public function handle_frontend_access() {
        if (is_singular(array('sfwd-topic', 'sfwd-lesson', 'sfwd-quiz', 'sfwd-question')) && (current_user_can('customer') || current_user_can('subscriber'))) {
            global $post;

            $course_id = learndash_get_course_id($post->ID);
            if (!$course_id) {
                return;
            }

            $current_user_id = get_current_user_id();
            $user_meta_key = '_time_lock_gmt_' . $course_id;

            $stored_lock_data = get_user_meta($current_user_id, $user_meta_key, true);
            $stored_lock_data = $stored_lock_data ? json_decode($stored_lock_data, true) : [];

            $stored_lock_time_gmt = isset($stored_lock_data['lock_time_gmt']) ? $stored_lock_data['lock_time_gmt'] : null;
            $time_lock_topic_id = isset($stored_lock_data['time_lock_topic_id']) ? $stored_lock_data['time_lock_topic_id'] : null;

            $time_lock = get_post_meta($post->ID, $this->time_meta_key, true);
            if (!$time_lock && !$stored_lock_time_gmt) {
                return;
            }

            $is_time_block_topic = '' !== $time_lock;

            if ($is_time_block_topic && !$stored_lock_time_gmt) {
                $current_time_gmt = current_time('timestamp', true);
                $lock_time_gmt = date('Y-m-d H:i:s', $current_time_gmt + $time_lock);

                $lock_data = [
                    'lock_time_gmt' => $lock_time_gmt,
                    'time_lock_topic_id' => $post->ID
                ];
                update_user_meta($current_user_id, $user_meta_key, json_encode($lock_data));
            }

            if (!$is_time_block_topic && $stored_lock_time_gmt && current_time('timestamp', true) < strtotime($stored_lock_time_gmt)) {
                if ($time_lock_topic_id) {
                    wp_redirect(get_permalink($time_lock_topic_id));
                    exit;
                }
            }
        }
    }

    public static function topic_time_lock_deadline() {
        $time_lock_data = \Multiclass\Core::has_course_time_lock();

        if( empty( $time_lock_data ) ) {
            return;
        }

        $is_current_page_is_time_lock_topic = ( (int) get_post_meta( get_the_ID(), '_sfwd_topic_time_lock', true ) > 0 );
        ?>
        <div id="mc-topic-time-lock-deadline" style="<?php echo $time_lock_data['status'] ? 'display: inline;' : 'display: none;'; ?>">
            <?php
            echo esc_html(sprintf(
                __("The 60-minute timer is now active. You can continue with the Pictogram Recall quiz on %s at %s.", 'custom-quiz-types-for-multiclass'),
                $time_lock_data['formatted_date'],
                $time_lock_data['formatted_time']
            ));
            ?>
        </div>
        <div id="mc-topic-time-lock-deadline-passed" style="<?php echo !$time_lock_data['status'] && $is_current_page_is_time_lock_topic ? 'display: inline;' : 'display: none;'; ?>">
            <?php echo esc_html__( "You can continue now!", 'custom-quiz-types-for-multiclass' ); ?>
        </div>
        <?php
    }   
}

new Topic_Time_Lock();
