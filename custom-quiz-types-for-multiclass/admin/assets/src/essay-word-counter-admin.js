/**
 * Essay Word Counter Admin
 * Handles real-time visibility of word counter metabox based on answer type selection
 */

jQuery(document).ready(function($) {
    
    /**
     * Toggle word counter metabox visibility based on answer type
     */
    function toggleWordCounterMetabox() {
        var answerType = $('input[name="answerType"]:checked').val();
        var $metabox = $('#sp_essay_word_counter_settings');
        
        if (answerType === 'essay') {
            $metabox.show();
        } else {
            $metabox.hide();
        }
    }
    
    // Run on page load
    toggleWordCounterMetabox();
    
    // Watch for answer type changes (radio buttons)
    $(document).on('change', 'input[name="answerType"]', function() {
        toggleWordCounterMetabox();
    });
    
    // Also watch for LearnDash's custom events if they exist
    $(document).on('wpProQuiz_answerTypeChange', function() {
        toggleWordCounterMetabox();
    });
});

