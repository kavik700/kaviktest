<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use WpProQuiz_Model_QuestionMapper;
use WpProQuiz_Model_QuizMapper;
use WpProQuiz_Model_CategoryMapper;
use WpProQuiz_Model_FormMapper;
use WpProQuiz_View_FrontQuiz;

class Image_Drag_Drop {

    public function __construct() {
        add_filter('learndash_quiz_question_result', array($this, 'accept_equivalent_grouped_answers'), 10, 2);
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        add_action('save_post', array($this, 'save_metabox_data'));
    }

    private function get_question_points( $question_id ) {
        $data = isset( $_POST['data'] ) ? (array) $_POST['data'] : null;

        if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		} else {
			$user_id = 0;
		}

		if ( isset( $data['quizId'] ) ) {
			$id = absint( $data['quizId'] );
		} else {
			$id = 0;
		}

		if ( isset( $data['quiz'] ) ) {
			$quiz_post_id = absint( $data['quiz'] );
		} else {
			$quiz_post_id = 0;
		}

        $quiz_post = get_post( $quiz_post_id );

		$view       = new WpProQuiz_View_FrontQuiz();
		$quizMapper = new WpProQuiz_Model_QuizMapper();
		$quiz       = $quizMapper->fetch( $id );
		if ( $quiz_post_id !== absint( $quiz->getPostId() ) ) {
			$quiz->setPostId( $quiz_post_id );
		}

		$questionMapper = new WpProQuiz_Model_QuestionMapper();
		$categoryMapper = new WpProQuiz_Model_CategoryMapper();

		$questionModels = $questionMapper->fetchAll( $quiz );

		$view->quiz     = $quiz;
		$view->question = $questionModels;
		$view->category = $categoryMapper->fetchByQuiz( $quiz );

		$question_count = count( $questionModels );
		ob_start();
		$quizData = $view->showQuizBox( $question_count );
		ob_get_clean();

		$json           = $quizData['json'];
        $questionData           = $json[ $question_id ];
        $points = isset( $questionData['points'] ) ? $questionData['points'] : $questionData['globalPoints'];

        return $points;
    }
    
    /**
     * Accepts equivalent grouped answers for matrix sort questions.
     * 
     * This function processes matrix sort questions where answers can be grouped together
     * and treated as equivalent. For example, if "A" and "a" are in the same group,
     * both answers are accepted as correct for that matrix sorting location.
     * 
     * @param array $results The quiz results array containing:
     *                       - 'e': Extra data with question type, user response, and correct answers
     *                       - 'p': Points earned
     *                       - 'c': Whether the answer is correct
     * @param int   $question_id The LearnDash question pro ID
     * 
     * @return array Modified results array with updated points and correctness:
     *               - 'p': Calculated points based on grouped answer validation
     *               - 'c': Boolean indicating if all answers are correct
     * 
     * @since 1.0.0
     * $processed_results = accept_equivalent_grouped_answers($results, 123);
     */
    public function accept_equivalent_grouped_answers( $results, $question_id ) {
      
        $extra = $results['e'];

        $type = $extra['type'];

        if( 'matrix_sort_answer' != $type ) {
            return $results;
        }

        $userResponse = $extra['r'];

        $points = $results['p'];

        $correct = true;

        // Get the post ID from the question pro ID
        $post_id = learndash_get_question_post_by_pro_id( $question_id );
        
        // Get drag drop groups if they exist
        $groups = array();
        if ( $post_id ) {
            $groups = \Multiclass\Drag_Drop_Groups::get_drag_drop_groups( $post_id );
        }

        // Create a mapping of answer indices to their group IDs
        $answer_to_group = array();
        if ( ! empty( $groups ) ) {
            foreach ( $groups as $group ) {
                if ( isset( $group['answerIds'] ) && is_array( $group['answerIds'] ) ) {
                    foreach ( $group['answerIds'] as $answer_id ) {
                        $correct_answer = $extra['c'][$answer_id];
                        $answer_to_group[ $correct_answer ] = $group['groupId'];
                    }
                }
            }
        }

        $points = $this->get_question_points( $question_id );

        if ( ! empty( $extra['c'] ) ) {
            foreach ( $extra['c'] as $answerIndex => $answer ) {
                $user_answer = $userResponse[ $answerIndex ];

                if( isset( $answer_to_group[ $answer ] ) ) {
                    $group_id = $answer_to_group[ $answer ];
                   
                    $correct_answer_group_id = $answer_to_group[ $answer ];
                    $user_answer_group_id = $answer_to_group[ $user_answer ];

                    if( $user_answer_group_id != $correct_answer_group_id ) {
                        $correct = false;
                    } elseif ( is_array( $points ) ) {
                        $earned_points += learndash_format_course_points( $points[ $answerIndex ] );
                    }
                }else {
                    if ( ! isset( $userResponse[ $answerIndex ] ) || $userResponse[ $answerIndex ] != $answer ) {
                        $correct = false;
                    } elseif ( is_array( $points ) ) {
                        $earned_points += learndash_format_course_points( $points[ $answerIndex ] );
                    }
                }
            }
        }

        if ( $correct ) {
            if ( ! is_array( $points ) ) {
                $earned_points = $points;
            }
        }

        $results['p'] = $earned_points;
        $results['c'] = $correct;

        return $results;
    }

    public function save_metabox_data($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['mc_drag_drop_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['mc_drag_drop_nonce'], 'mc_drag_drop_save')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save the settings
        $enable_resize = isset($_POST['mc_enable_resize']) ? '1' : '0';
        $show_normalize = isset($_POST['mc_show_normalize']) ? '1' : '0';
        $normalize_width = isset($_POST['mc_normalize_width']) ? intval($_POST['mc_normalize_width']) : '';
        $normalize_height = isset($_POST['mc_normalize_height']) ? intval($_POST['mc_normalize_height']) : '';

        update_post_meta($post_id, '_mc_enable_resize', $enable_resize);
        update_post_meta($post_id, '_mc_show_normalize', $show_normalize);
        update_post_meta($post_id, '_mc_normalize_width', $normalize_width);
        update_post_meta($post_id, '_mc_normalize_height', $normalize_height);
    }

    public function add_metabox() {
        add_meta_box(
            'custom_metabox_id',
            esc_html__('Interactive Draggable Area', 'custom-quiz-types-for-multiclass'),
            array($this, 'render_metabox_content'),
            'sfwd-question',
            'normal',
            'high'
        );
    }

    public function render_metabox_content($post) {
        // Add nonce for security
        wp_nonce_field('mc_drag_drop_save', 'mc_drag_drop_nonce');

        // Get saved values
        $enable_resize = get_post_meta($post->ID, '_mc_enable_resize', true);
        $show_normalize = get_post_meta($post->ID, '_mc_show_normalize', true);
        $normalize_width = get_post_meta($post->ID, '_mc_normalize_width', true);
        $normalize_height = get_post_meta($post->ID, '_mc_normalize_height', true);

        // Set defaults if not set
        $enable_resize = $enable_resize === '' ? '1' : $enable_resize;
        $show_normalize = $show_normalize === '' ? '0' : $show_normalize;

        // Get the featured image URL and dimensions
        $background_image = '';
        $image_width = 300;  // Default width
        $image_height = 300; // Default height

        if (has_post_thumbnail($post->ID)) {
            $background_image = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
            $image_metadata = wp_get_attachment_metadata(get_post_thumbnail_id($post->ID));

            if ($image_metadata) {
                $image_width = $image_metadata['width'];
                $image_height = $image_metadata['height'];
            }
        }

        $question_id = get_post_meta( $post->ID, 'question_pro_id', true );

        $question_mapper   = new WpProQuiz_Model_QuestionMapper();
        $question = $question_mapper->fetch( $question_id );

        $answer_data = $question->getAnswerData();
        $answer_type = $question->getAnswerType();
        ?>
        <style>
            #parent-container {
                width: <?php echo esc_attr($image_width); ?>px;
                height: <?php echo esc_attr($image_height); ?>px;
                background-image: url('<?php echo esc_url($background_image); ?>');
                background-size: cover;
                position: relative;
                overflow: hidden;
                border: 2px solid black;
                box-sizing: border-box;
            }

            .selectable-area {
                width: 100px;
                height: 100px;
                background-color: transparent;
                position: absolute;
                cursor: move;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 2px solid blue;
                box-sizing: border-box;
            }

            .resize-disabled .selectable-area {
                cursor: move !important;
                border: none !important;
            }

            .resize-disabled .selectable-area .interact-resize-handle {
                display: none !important;
            }

            .interact-resize-handle {
                background: blue;
                border-radius: 50%;
                width: 10px !important;
                height: 10px !important;
            }

            .speech-bubble {
                position: relative;
                background-color: #00aaff;
                color: #fff;
                padding: 10px 20px;
                border-radius: 10px;
                width: max-content;
                max-width: 300px;
                font-size: 16px;
                line-height: 1.4;
            }

            .speech-bubble::after {
                content: '';
                position: absolute;
                bottom: -20px; /* Position at the bottom */
                left: 20px;    /* Adjust based on the width of the bubble */
                border-width: 10px;
                border-style: solid;
                border-color: #00aaff transparent transparent transparent;
            }

            #custom_metabox_id {
                overflow: scroll;
            }

            .resize-controls {
                margin-bottom: 15px;
                padding: 10px;
                background: #f5f5f5;
                border: 1px solid #ddd;
            }
        </style>

        <div class="resize-controls">
            <label>
                <input type="checkbox" id="mc_enable_resize" name="mc_enable_resize" <?php checked($enable_resize, '1'); ?>> Enable Resize
            </label>
            <div id="normalize-controls" style="display: <?php echo $enable_resize === '0' ? 'block' : 'none'; ?>; margin-top: 10px;">
                <label>
                    <input type="checkbox" id="mc_show_normalize" name="mc_show_normalize" <?php checked($show_normalize, '1'); ?>> Normalize Sizes
                </label>
                <div id="normalize-inputs" style="display: <?php echo $show_normalize === '1' ? 'block' : 'none'; ?>; margin-top: 10px;">
                    <label style="margin-right: 15px;">
                        Width: <input type="number" id="mc_normalize_width" name="mc_normalize_width" min="1" placeholder="Width" value="<?php echo esc_attr($normalize_width); ?>">
                    </label>
                    <label>
                        Height: <input type="number" id="mc_normalize_height" name="mc_normalize_height" min="1" placeholder="Height" value="<?php echo esc_attr($normalize_height); ?>">
                    </label>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Add resize-disabled class to parent container if resize is disabled
            function updateResizeClass() {
                if (!$('#mc_enable_resize').is(':checked')) {
                    $('#parent-container').addClass('resize-disabled');
                } else {
                    $('#parent-container').removeClass('resize-disabled');
                }
            }

            // Function to toggle input validation
            function toggleInputValidation(show) {
                if (show) {
                    $('#mc_normalize_width, #mc_normalize_height').prop('disabled', false).attr('min', '1');
                } else {
                    $('#mc_normalize_width, #mc_normalize_height').prop('disabled', true).removeAttr('min');
                }
            }

            // Initial check
            updateResizeClass();

            $('#mc_enable_resize').on('change', function() {
                updateResizeClass();
                if (!this.checked) {
                    $('#normalize-controls').show();
                    // Enable validation if normalize is checked
                    if ($('#mc_show_normalize').is(':checked')) {
                        toggleInputValidation(true);
                    }
                } else {
                    $('#normalize-controls').hide();
                    $('#normalize-inputs').hide();
                    toggleInputValidation(false);
                    // Reset elements to their original size when resize is enabled
                    $('.selectable-area').each(function() {
                        $(this).css({
                            'width': $(this).data('original-width') || '100px',
                            'height': $(this).data('original-height') || '100px'
                        });
                    });
                }
            });

            $('#mc_show_normalize').on('change', function() {
                if (this.checked) {
                    $('#normalize-inputs').show();
                    toggleInputValidation(true);
                    applyNormalizedSizes();
                } else {
                    $('#normalize-inputs').hide();
                    toggleInputValidation(false);
                    // Reset elements to their original size
                    $('.selectable-area').each(function() {
                        $(this).css({
                            'width': $(this).data('original-width') || '100px',
                            'height': $(this).data('original-height') || '100px'
                        });
                    });
                }
            });

            // Store original sizes when page loads
            $('.selectable-area').each(function() {
                $(this).data('original-width', $(this).width());
                $(this).data('original-height', $(this).height());
            });

            // Function to apply normalized sizes
            function applyNormalizedSizes() {
                if (!$('#mc_show_normalize').is(':checked')) return;
                
                var width = $('#mc_normalize_width').val();
                var height = $('#mc_normalize_height').val();
                
                if (width && height) {
                    $('.selectable-area').css({
                        'width': width + 'px',
                        'height': height + 'px',
                        'background-size': '100% 100%'
                    });
                }
            }

            // Apply normalized sizes when width/height inputs change
            $('#mc_normalize_width, #mc_normalize_height').on('input', function() {
                applyNormalizedSizes();
            });

            // Initial setup - handle all possible states
            function initializeNormalizeState() {
                // If normalize is checked, ensure the controls and inputs are visible
                if ($('#mc_show_normalize').is(':checked')) {
                    // If resize is enabled, we need to show normalize controls
                    if ($('#mc_enable_resize').is(':checked')) {
                        $('#normalize-controls').show();
                    }
                    $('#normalize-inputs').show();
                    toggleInputValidation(true);
                    applyNormalizedSizes();
                } else {
                    // Disable validation for hidden inputs
                    toggleInputValidation(false);
                }
            }

            // Call initialization
            initializeNormalizeState();
        });
        </script>

        <div id="parent-container">
            <?php 
                switch( $answer_type ) {
                    case 'matrix_sort_answer':
                        foreach( $answer_data as $option ): 
                            $is_img = false !== wp_http_validate_url($option->getSortString());
                        
                            if( $is_img ) {
                                $img_url =  $option->getSortString();

                                if( ! $img_url ) {
                                    continue;
                                }
    
                                list($width, $height) = getimagesize($img_url);
    
                                $y = 0;
                                $x = 0;
    
                                if( $option->getAnswer() ) {
                                    $args = json_decode( $option->getAnswer() );
    
                                    if( is_object( $args ) ) {
                                        $width = $args->width;
                                        $height = $args->height;
                                        $y = $args->y;
                                        $x = $args->x;
                                    }
                                }
                                
                                
                                ?>
                                    <div class="selectable-area" data-x="<?php echo esc_attr($x); ?>" data-y="<?php echo esc_attr($y); ?>" data-mc_identifier="<?php echo esc_attr($img_url); ?>" style="background-image: url('<?php echo \Multiclass\Smart_Crop::cached_crop_to_largest_component( $img_url ); ?>'); width: <?php echo esc_attr($width); ?>px; height: <?php echo esc_attr($height); ?>px;top:0;position:absolute;transform:translate(<?php echo esc_attr($x); ?>px, <?php echo esc_attr($y); ?>px); background-repeat:no-repeat; background-size:contain"></div>
                                <?php
                            }else{
                                $width = 100;
                                $height = 100;
                                $x = 0;
                                $y = 0;

                                $y = 0;
                                $x = 0;
    
                                if( $option->getAnswer() ) {
                                    $args = json_decode( $option->getAnswer() );
    
                                    if( is_object( $args ) ) {
                                        $width = $args->width;
                                        $height = $args->height;
                                        $y = $args->y;
                                        $x = $args->x;
                                    }
                                }
                                ?>
                                <div class="selectable-area" data-x="<?php echo esc_attr($x); ?>" data-y="<?php echo esc_attr($y); ?>" data-mc_identifier="<?php echo esc_attr($option->getSortString()); ?>" style="width: <?php echo esc_attr($width); ?>px; height: <?php echo esc_attr($height); ?>px;top:0;position:absolute;transform:translate(<?php echo esc_attr($x); ?>px, <?php echo esc_attr($y); ?>px); background-repeat:no-repeat; background-size:contain"><?php echo esc_html($option->getSortString()); ?></div>
                                <?php
                            }
                            
                        
                           
                        endforeach;
                    break;

                    case 'single':
                        $coordinates = get_post_meta($post->ID, '_speech_bubble_coordinates', true);
                        $coordinates = json_decode($coordinates, true);

                        $x = isset($coordinates['x']) ? esc_attr($coordinates['x']) : '';
                        $y = isset($coordinates['y']) ? esc_attr($coordinates['y']) : '';
                        $width = isset($coordinates['width']) ? esc_attr($coordinates['width']) : '';
                        $height = isset($coordinates['height']) ? esc_attr($coordinates['height']) : '';

                        $invalid_answer_messages = get_post_meta( $post->ID, '_mc_cc_incorrect_answers', true );
                        ?>
                            <div data-invalid_answers="<?php echo esc_attr( json_encode( $invalid_answer_messages ) ); ?>" class="speech-bubble selectable-area" data-mc_identifier="<?php echo isset( $img_url ) ? esc_attr($img_url) : ''; ?>" style="width: <?php echo esc_attr($width); ?>px; height: <?php echo esc_attr($height); ?>px;top:0;position:absolute;transform:translate(<?php echo esc_attr($x); ?>px, <?php echo esc_attr($y); ?>px); ">
                                <p><?php echo esc_html( $question->getTipMsg() ); ?></p>
                            </div>
                        <?php
                    break;
                }
            ?>
        </div>

        <?php
    }
}