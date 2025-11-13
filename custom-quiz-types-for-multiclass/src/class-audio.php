<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

class Audio {
    public function __construct() {
        add_shortcode('mc_audio_player', [$this, 'render_audio_player']);
        add_action('wp_ajax_track_audio_play', [$this, 'handle_audio_play']);
        add_action('wp_ajax_nopriv_track_audio_play', [$this, 'handle_audio_play']);
    }

    /**
     * Get course ID from a question post ID
     * 
     * @param int $question_post_id The question post ID
     * @return int The course ID or 0 if not found
     */
    private function get_course_id_from_question($question_post_id) {
        // First, try to get course ID from the current quiz context (data attribute)
        if (isset($_GET['quiz_id']) || isset($_POST['quiz_id'])) {
            $quiz_post_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : intval($_POST['quiz_id']);
            $course_id = learndash_get_course_id($quiz_post_id);
            if ($course_id) {
                return $course_id;
            }
        }

        // Try to get course ID from URL parameter
        if (isset($_GET['course_id'])) {
            return intval($_GET['course_id']);
        }

        // Get all quizzes that contain this question
        $question_quizzes = learndash_get_quizzes_for_question($question_post_id, true);
        
        if (!empty($question_quizzes)) {
            // Get the first quiz and its course
            $quiz_ids = array_keys($question_quizzes);
            $quiz_post_id = $quiz_ids[0];
            $course_id = learndash_get_course_id($quiz_post_id);
            
            if ($course_id) {
                return $course_id;
            }
        }

        return 0;
    }

    /**
     * Render audio player from post meta
     * 
     * @param int $question_post_id The question post ID
     * @param int $course_id Optional course ID to avoid lookups
     * @return string The rendered audio player HTML
     */
    public function render_audio_from_meta($question_post_id, $course_id = 0) {
        $audio_url = get_post_meta($question_post_id, '_mc_audio_url', true);
        $listening_limit = get_post_meta($question_post_id, '_mc_audio_listening_limit', true);
        
        if (empty($audio_url)) {
            return '';
        }
        
        // Default to 0 (unlimited) if not set
        if (empty($listening_limit)) {
            $listening_limit = 0;
        }
        
        return $this->render_audio_player([
            'url' => $audio_url,
            'limit' => $listening_limit,
            'question_id' => $question_post_id,
            'course_id' => $course_id
        ]);
    }

    /**
     * Render the audio player shortcode
     */
    public function render_audio_player($atts) {
        $atts = shortcode_atts([
            'url' => '',
            'limit' => 0,
            'question_id' => 0,
            'course_id' => 0,
        ], $atts, 'mc_audio_player');

        if (empty($atts['url'])) {
            return '';
        }

        $listening_limit = intval($atts['limit']);
        $question_id = intval($atts['question_id']);
        $course_id = intval($atts['course_id']);
        
        // If we don't have a question_id, try to get it from the current post
        if (!$question_id) {
            global $post;
            if ($post) {
                $question_id = $post->ID;
            }
        }

        $output = '';
        
        // Always wrap with tracking if we have a question_id
        if ($question_id > 0) {
            $user_id = get_current_user_id();
            
            // Get course ID for the meta key prefix (use passed course_id if available)
            if (!$course_id) {
                $course_id = $this->get_course_id_from_question($question_id);
            }
            
            $play_count_key = 'sp_course_' . $course_id . '_audio_play_count_' . $question_id . '_' . $user_id;
            $play_count = get_user_meta($user_id, $play_count_key, true);
            $play_count = $play_count ? intval($play_count) : 0;

            $output .= '<div class="mc-audio-wrapper" data-question-id="' . esc_attr($question_id) . '" data-listening-limit="' . esc_attr($listening_limit) . '" data-play-count="' . esc_attr($play_count) . '">';
            
            // Only show limit info if there's actually a limit
            if ($listening_limit > 0) {
                $output .= '<div class="mc-audio-limit-info">';
                $remaining = max(0, $listening_limit - $play_count);
                
                if ($remaining > 0) {
                    $time_text = $remaining != 1 ? __('times', 'custom-quiz-types-for-multiclass') : __('time', 'custom-quiz-types-for-multiclass');
                    $output .= '<p style="color: #666;">🎧 ' . sprintf(__('You can listen to this audio %d more %s (%d/%d).', 'custom-quiz-types-for-multiclass'), $remaining, $time_text, $play_count, $listening_limit) . '</p>';
                } else {
                    $output .= '<p style="color: #666;">🔒 ' . sprintf(__('No more plays available (used %d/%d).', 'custom-quiz-types-for-multiclass'), $play_count, $listening_limit) . '</p>';
                }
                
                $output .= '</div>';
            }
        }

        // Custom audio player without seek controls
        $output .= '<div class="mc-custom-audio-player">';
        $output .= '<audio class="mc-audio-element" preload="metadata">';
        $output .= '<source src="' . esc_url($atts['url']) . '" type="audio/mpeg">';
        $output .= 'Your browser does not support the audio element.';
        $output .= '</audio>';
        $output .= '<div class="mc-audio-controls">';
        
        // Check if play button should be disabled
        $is_disabled = false;
        if ($listening_limit > 0 && $question_id > 0) {
            $user_id = get_current_user_id();
            
            // Get course ID for the meta key prefix (use passed course_id if available)
            $disabled_check_course_id = $course_id;
            if (!$disabled_check_course_id) {
                $disabled_check_course_id = $this->get_course_id_from_question($question_id);
            }
            
            $play_count_key = 'sp_course_' . $disabled_check_course_id . '_audio_play_count_' . $question_id . '_' . $user_id;
            $play_count = get_user_meta($user_id, $play_count_key, true);
            $play_count = $play_count ? intval($play_count) : 0;
            $is_disabled = $play_count >= $listening_limit;
        }
        
        $output .= '<button class="mc-audio-play-btn" type="button" aria-label="Play"' . ($is_disabled ? ' disabled' : '') . '>';
        $output .= '<svg class="play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>';
        $output .= '<svg class="pause-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;"><path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"></path></svg>';
        $output .= '</button>';
        $output .= '<span class="mc-audio-time">0:00 / 0:00</span>';
        $output .= '<button class="mc-audio-volume-btn" type="button" aria-label="Volume">';
        $output .= '<svg class="volume-on" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
        $output .= '<svg class="volume-off" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>';
        $output .= '</button>';
        $output .= '</div>';
        $output .= '</div>';
        
        // Add inline CSS
        $output .= '<style>
            .mc-custom-audio-player {
                border: 1px solid #d4c4bb;
                border-radius: 6px;
                padding: 12px 16px;
                max-width: 450px;
                margin: 10px 0;
                background: #edf4f1;
            }
            .mc-audio-controls {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .mc-audio-play-btn {
                width: 40px;
                height: 40px;
                background: #5a8f7b;
                color: white;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: background 0.2s ease;
            }
            .mc-audio-play-btn:hover:not(:disabled) {
                background: #4a7565;
            }
            .mc-audio-play-btn:disabled {
                background: #c5d1cc;
                cursor: not-allowed;
                opacity: 0.6;
            }
            .mc-audio-time {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 14px;
                color: #3d5a4d;
                flex: 1;
                font-variant-numeric: tabular-nums;
            }
            .mc-audio-volume-btn {
                width: 32px;
                height: 32px;
                background: transparent;
                color: #6b8577;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: color 0.2s ease;
            }
            .mc-audio-volume-btn:hover {
                color: #5a8f7b;
            }
            .mc-audio-element {
                display: none;
            }
        </style>';

        if ($question_id > 0) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Handle AJAX request to track audio plays
     */
    public function handle_audio_play() {
        check_ajax_referer('mc_audio_tracking', 'nonce');

        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $user_id = get_current_user_id();

        if (!$question_id || !$user_id) {
            wp_send_json_error('Invalid data');
            return;
        }

        // Get course ID for the meta key prefix
        $course_id = $this->get_course_id_from_question($question_id);

        // Always track the play count
        $play_count_key = 'sp_course_' . $course_id . '_audio_play_count_' . $question_id . '_' . $user_id;
        $play_count = get_user_meta($user_id, $play_count_key, true);
        $play_count = $play_count ? intval($play_count) : 0;

        $play_count++;
        update_user_meta($user_id, $play_count_key, $play_count);

        // Get the listening limit from the AJAX request (comes from shortcode)
        $listening_limit = isset($_POST['listening_limit']) ? intval($_POST['listening_limit']) : 0;

        wp_send_json_success([
            'play_count' => $play_count,
            'limit' => $listening_limit,
            'remaining' => $listening_limit > 0 ? max(0, $listening_limit - $play_count) : -1,
            'unlimited' => $listening_limit <= 0
        ]);
    }
}

