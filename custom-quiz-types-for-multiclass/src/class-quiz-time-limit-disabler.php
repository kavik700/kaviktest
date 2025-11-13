<?php
namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Quiz_Time_Limit_Disabler {
    public function __construct() {
        add_filter('body_class', array($this, 'add_body_class'));

        add_action('add_meta_boxes', array($this, 'add_time_limit_metabox'));
        add_action('save_post', array($this, 'save_time_limit_meta'));

        add_action('init', array($this, 'handler'));
    }

    public function add_body_class($classes) {
        global $post;
        $feature_enabled = false;
        
        if (is_singular('sfwd-quiz') && $post) {
            $feature_enabled = get_post_meta($post->ID, '_cqt_disable_time_limit', true) === 'yes';
        }
        
        if ($feature_enabled) {
            $classes[] = 'cqt-quiz-time-limit-disabler';
        }
        return array_unique($classes);
    }

    public function add_time_limit_metabox() {
        add_meta_box(
            'cqt_time_limit_settings',
            __('Quiz Time Limit Settings', 'custom-quiz-types'),
            array($this, 'render_time_limit_metabox'),
            'sfwd-quiz',
            'side',
            'default'
        );
    }

    public function render_time_limit_metabox($post) {
        wp_nonce_field('cqt_time_limit_nonce', 'cqt_time_limit_nonce');
        $disable_time_limit = get_post_meta($post->ID, '_cqt_disable_time_limit', true);
        ?>
        <p>
            <label>
                <input type="checkbox" name="cqt_disable_time_limit" value="yes" <?php checked($disable_time_limit, 'yes'); ?>>
                <?php _e('Allow users to deactivate time limit for this quiz', 'custom-quiz-types'); ?>
                <br>
                <br>
                <small><?php _e('Note: This feature only works if the time limit feature is activated by LearnDash settings for this quiz.', 'custom-quiz-types'); ?></small>
            </label>
        </p>
        <?php
    }

    public function save_time_limit_meta($post_id) {
        if (!isset($_POST['cqt_time_limit_nonce']) || 
            !wp_verify_nonce($_POST['cqt_time_limit_nonce'], 'cqt_time_limit_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $disable_time_limit = isset($_POST['cqt_disable_time_limit']) ? 'yes' : 'no';
        update_post_meta($post_id, '_cqt_disable_time_limit', $disable_time_limit);
    }

    public function handler() {
        add_action('wp_print_footer_scripts', function() {
            $post_id = get_the_ID();

            $feature_enabled = get_post_meta($post_id, '_cqt_disable_time_limit', true) === 'yes';

            if( ! $feature_enabled ) {
                return;
            }

            if(empty($_GET['action']) || $_GET['action'] !== 'disable-time-limit') {
                return;
            }
    
            global $wp_filter;
            
            // Remove all callbacks at priority 999
            if (isset($wp_filter['wp_print_footer_scripts']->callbacks[999])) {
                $callbacks = $wp_filter['wp_print_footer_scripts']->callbacks[999];
                unset($wp_filter['wp_print_footer_scripts']->callbacks[999]);
                
                // Re-add them one by one, wrapping the quiz one
                foreach ($callbacks as $key => $callback) {
                    if (isset($callback['function']) && is_array($callback['function']) 
                        && is_object($callback['function'][0]) 
                        && get_class($callback['function'][0]) === 'WpProQuiz_View_FrontQuiz') {
                        
                        // Add our wrapped version
                        add_action('wp_print_footer_scripts', function() use ($callback) {
                            ob_start();
                            call_user_func($callback['function']);
                            $content = ob_get_clean();
                            
                            // Define your dynamic time limit here (in seconds)
                            $dynamic_time_limit = 0; // Example: 9000 seconds
                            
                            // Replace the timelimit in the JavaScript initialization
                            $content = preg_replace(
                                '/timelimit:\s*\d+/',
                                'timelimit: ' . $dynamic_time_limit,
                                $content
                            );
                            
                            echo $content;
                        }, 999);
                    } else {
                        // Re-add other callbacks as is
                        add_action('wp_print_footer_scripts', $callback['function'], 999);
                    }
                }
            }
        }, 998);
    }
}