jQuery(document).ready(function($) {
    const Core = {
        init: function(container) {
            let $this = $(container);

            if ($this.hasClass('has-autoload-modal')) {
                $this.on('mc_question_popup_closed', function() {
                    $this.find('.speech-bubble').fadeIn(2000);
                });
            } else {
                setTimeout(function() {
                    $this.find('.speech-bubble').fadeIn(1000); // You can replace fadeIn with slideDown for a sliding effect
                }, 2000);
            }

            $this.find('.wpProQuiz_questionInput').on('click', function(e) {
                const pos = parseInt($(this).parents('.wpProQuiz_questionListItem').data('pos'));
                if( parseInt($this.data('mc_signature')) === pos ) {
                    const correctAnswer = $(e.target).parents('.wpProQuiz_questionListItem');
                    correctAnswer.find('label').addClass( 'correct' );
                    setTimeout(function(){
                        $this.find('.wpProQuiz_QuestionButton[name="next"]').click();
                    }, 1000);
                }else{
                    $this.find('.wpProQuiz_questionList .wpProQuiz_questionListItem').hide();
                    $this.find('.mc-cc-error-message').show();
                    $this.find(`.mc-cc-error-message .mc-cc-err-${pos}`).show();
                }
            });

            $this.find('.mc-err-back').on('click', function() {
                $this.find('.wpProQuiz_questionList .wpProQuiz_questionListItem').show();
                $this.find(`.mc-cc-error-message, .mc-cc-err`).hide();
            });
        }
    }

    $('.mc-customer_contact').on('mc_question_ready', function() {
        Core.init(this);
    });
});
