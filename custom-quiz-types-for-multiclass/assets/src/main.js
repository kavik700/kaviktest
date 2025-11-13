import './main.scss';
import './color-combination';
import './organization-calendar';
import './jquery-modal';
import './show-answers';
import './concentration';
import './concentration-number';
import './concentration-numbers-short-term';
import './question-time-limit';
import './topic-time-limit';
import './customer-concact';
import './image-drag-drop';
import './calculator';
import './numerical-processing';
import './different-perspective';
import './exam-simulation';
import './report-problem';
import './quiz-time-limit-disabler';
import './hide-quiz-grades';
import './fill-in-blanks-remove-char-limit';
import './audio-tracking';
import './essay-word-counter';

jQuery(document).ready(function($) {
    // Flag to track if the completion has already been detected
    var triggered = false;
  
    // Monitor changes to the quiz container
    var quizObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // Check if the quiz result is visible and hasn't been detected yet
            if ($('.wpProQuiz_results').is(':visible') && !triggered) {
                $(document).trigger( 'mc_ld_quiz_completed' )
  
                // Optionally disconnect the observer if no further monitoring is needed
                quizObserver.disconnect();
            }
        });
    });
  
    // Target the quiz container element specifically for result changes
    var resultContainer = document.querySelector('.wpProQuiz_results');
    if (resultContainer) {
        // Start observing the result container for changes
        quizObserver.observe(resultContainer, { attributes: true, childList: true, subtree: true });
    }

    // Iterate over each element with the class .wpProQuiz_listItem
    $('.wpProQuiz_listItem').each(function () {
        // Select the current target node
        var targetNode = this;

        // Options for the observer (which mutations to observe)
        var config = { attributes: true, childList: false, subtree: false };

        // Callback function to execute when mutations are observed
        var callback = function (mutationsList, observer) {
            for (var mutation of mutationsList) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    var displayStyle = $(mutation.target).css('display');
                    if (displayStyle === 'list-item') {
                        // Example: triggering a custom event
                        $(mutation.target).trigger('listItemDisplayed');
                    }
                }
            }
        };

        // Create an observer instance linked to the callback function
        var observer = new MutationObserver(callback);

        // Start observing the current target node for configured mutations
        observer.observe(targetNode, config);
    });

    // Handle question solved events (both correct and incorrect answers) for each quiz
    $('.wpProQuiz_content').each(function() {
        const $quizContent = $(this);
        
        $quizContent.on('questionSolvedCorrect questionSolvedIncorrect', function(event) {
            const questionItem = event.values.item[0];

            $(questionItem).find('.mc-quiz-answer-state--after').show();
            $(questionItem).find('.mc-quiz-answer-state--before').hide();
        });
    });
  });