import {transformTextarea} from './organization-calendar';
import './color-combination';
import './image-drag-drop';
import './drag-drop-group';
import './matrix-sort-image-preview';
import './audio-metabox';
import './essay-word-counter-admin';
import './main.scss';

jQuery(document).ready(function($){

    $('input[name="multiclass_question_type"]').on('click', function(e) {
        const questionType = $(this).val();
    
        toggle(questionType);
    });
    
    setTimeout(function() {
        const currentQuestionType = $('input[name="multiclass_question_type"]:checked').val();
      
        toggle(currentQuestionType, true);
      }, 1000);
    
    function toggle(questionType, init = false) {
        // Reset all elements to their default visible states
        function resetDefaultView() {
          $('#learndash_question_type, #post-body-content, #learndash_question_answers, #learndash_question_message_correct_answer, #learndash_question_message_incorrect_answer, #learndash_question_hint').show();
          $('#sfwd_question_combination_count').hide();
          $('#mc_audio_options').hide();
          $('.wpProQuiz_matrix_answer').show();
          $('.wpProQuiz_matrix_answer.flatpickr-input').remove();
        }
      
        // Common logic for hiding elements and setting the editor content
        function setupEditorAndHideElements(editorContent) {
          var editor = tinymce.get('content');
          editor.setContent(editorContent);
      
          $('.postarea').hide();
          $('#learndash_question_type').hide();
        }
      
        resetDefaultView(); // Start by resetting the view
      
        if (questionType === 'organization_calendar') {
          setupEditorAndHideElements('placeholder');
          $('.wpProQuiz_matrix_answer').hide();
          transformTextarea($('.wpProQuiz_matrix_answer'));
          $('#learndash-question-type-matrix_sort_answer').prop('checked', true).trigger('click');
          $('#learndash_question_answers, #learndash_question_message_correct_answer, #learndash_question_message_incorrect_answer, #learndash_question_hint').show();
        } else if (questionType === 'image_drag_drop') {
          $('#learndash-question-type-matrix_sort_answer').prop('checked', true).trigger('click');
          $('#learndash_question_answers, #learndash_question_message_correct_answer, #learndash_question_message_incorrect_answer, #learndash_question_hint').show();
        }else if (questionType === 'color_combination') {
          if (!init) {
            $('input[name="points"]').val(customQuizAjax.colorCombinationTotalPoint * 10);
          }
          setupEditorAndHideElements('placeholder');
          $('.wpProQuiz_free_text').val('placeholder');
          $('#learndash-question-type-free_answer').prop('checked', true).trigger('click');
          $('#sfwd_question_combination_count').show();
        }else if( questionType === 'concentration' ) {
          setupEditorAndHideElements('placeholder');
            $('#sfwd_question_combination_count').show();
            $('.wpProQuiz_free_text').val('placeholder');
            $('#learndash-question-type-free_answer').prop('checked', true).trigger('click');
        }else if( questionType === 'customer_contact' ) {
          const answers = $('.speech-bubble').data('invalid_answers');

          let i = 0;

          $('.classic_answer .answerList li').each(function() {
            // Create the new label and textarea elements
            var newLabel = $('<label>').text('Message with the incorrect answer');
            var newTextarea = $('<textarea>')
                .attr('rows', '2')
                .attr('cols', '50')
                .addClass('large-text wpProQuiz_text wpProQuiz_incorrect_message')
                .attr('name', 'answerData[][incorrectMessage]')
                .val(answers[i])
                .css('resize', 'vertical');
    
            // Create a new div to hold the label and textarea
            var newDiv = $('<div>').css('padding-top', '10px');
    
            // Append the label and textarea to the new div
            newDiv.append(newLabel).append('<br>').append(newTextarea);
    
            // Append the new div after the existing answer textarea
            $(this).find('textarea[name^="answerData[][answer]"]').parent().append(newDiv);

            i++;
        });
        }else if( questionType === 'audio' ) {
          // Show the audio options metabox
          $('#mc_audio_options').show();
          
          // Keep the editor visible for audio questions
          // Do NOT insert shortcode - audio will be rendered from meta directly
          $('.postarea').show();
        }
      }
      
});