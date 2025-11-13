jQuery(document).ready(function($) {
    const Core = {
        init: function(container) {
            let $this = $(container);
            let countdownNumbers = [3, 2, 1];
            
            $this.find('.wpProQuiz_question_text').append(`<div style="display:none" class="correct-answer">${$this.data('mc_signature')}</div>`);
            $this.find('.wpProQuiz_question_text').append('<div class="countdown"></div>');

            countdownNumbers.forEach(function(number, index) {
                setTimeout(function() {
                    $this.find('.wpProQuiz_question_text .countdown').text(number);

                    if (index === countdownNumbers.length - 1) {
                        // When the countdown reaches 1, start the next part of the sequence
                        setTimeout(function() {
                            $this.find('.wpProQuiz_question_text .countdown').hide();
                            
                            // Show the correct answer for 2 seconds
                            $this.find('.correct-answer').show();

                            setTimeout(function() {
                                $this.find('.correct-answer').hide();
                                
                                // Then show the question list
                                $this.find('.wpProQuiz_questionList').css('display', 'flex');
                            }, 2000); // 2 seconds
                            
                        }, 1000);
                    }
                }, index * 1000);
            });
        }
    }

    $('.mc-concentration_numbers_short_term').on('mc_question_ready', function() {
        Core.init(this);
    });
});
