/**
 * Essay Word Counter
 * Counts words in essay question textareas in real-time
 */

jQuery(document).ready(function($) {
    // Only run on LearnDash quiz pages
    if (!$('body').hasClass('learndash-cpt-sfwd-quiz')) {
        return;
    }

    /**
     * Count words in a text string
     * @param {string} text - The text to count words in
     * @return {number} The number of words
     */
    function countWords(text) {
        // Trim whitespace and check if empty
        text = text.trim();
        
        if (text.length === 0) {
            return 0;
        }
        
        // Split by whitespace and filter out empty strings
        var words = text.split(/\s+/).filter(function(word) {
            return word.length > 0;
        });
        
        return words.length;
    }

    /**
     * Update word counter display
     * @param {jQuery} $textarea - The textarea element
     */
    function updateWordCounter($textarea) {
        var questionId = $textarea.data('question-id');
        var $counter = $('.sp-essay-word-counter[data-target-question-id="' + questionId + '"]');
        
        if ($counter.length) {
            var text = $textarea.val();
            var wordCount = countWords(text);
            $counter.find('.sp-word-count').text(wordCount);
        }
    }

    /**
     * Initialize word counters for all essay textareas with word counter enabled
     */
    function initializeWordCounters() {
        $('.wpProQuiz_questionEssay.sp-has-word-counter').each(function() {
            var $textarea = $(this);
            
            // Update counter on input (real-time)
            $textarea.on('input', function() {
                updateWordCounter($textarea);
            });
            
            // Update counter on paste
            $textarea.on('paste', function() {
                // Delay to allow paste content to be inserted
                setTimeout(function() {
                    updateWordCounter($textarea);
                }, 10);
            });
            
            // Update counter on cut
            $textarea.on('cut', function() {
                // Delay to allow cut to complete
                setTimeout(function() {
                    updateWordCounter($textarea);
                }, 10);
            });
            
            // Initial count (in case there's already content)
            updateWordCounter($textarea);
        });
    }

    // Initialize when DOM is ready
    initializeWordCounters();
    
    // Re-initialize when quiz questions are loaded/displayed
    // LearnDash may load questions dynamically
    $(document).on('learndash-quiz-question-loaded', function() {
        initializeWordCounters();
    });
    
    // Also watch for visibility changes (LearnDash shows/hides questions)
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                var $target = $(mutation.target);
                if ($target.hasClass('wpProQuiz_listItem') && $target.is(':visible')) {
                    var $textarea = $target.find('.wpProQuiz_questionEssay.sp-has-word-counter');
                    if ($textarea.length) {
                        updateWordCounter($textarea);
                    }
                }
            }
        });
    });
    
    // Observe all quiz list items
    $('.wpProQuiz_listItem').each(function() {
        observer.observe(this, {
            attributes: true,
            attributeFilter: ['style']
        });
    });
});

