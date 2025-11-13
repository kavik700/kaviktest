jQuery(document).ready(function($) {
    const Question_Time_Limit = {
        container: null,
        timerInterval: null,
        totalSeconds: 0,
        hasTimeLimit: false,
        hasTriCoutdown: false,

        init(container) {
            this.container = $(container);
            this.totalSeconds = this.container.data('mc_time_limit');
            this.hasTimeLimit = parseInt( this.totalSeconds )>0;
            this.hasTriCoutdown = this.container.hasClass('has-tri-coutdown');

            const self = this;

            if( this.hasTriCoutdown ) {
                this.startTriCoutdown();
                this.container.on( 'mc_tri_coutdown_completed', function() {
                    self.triggerReady();

                    if( ! self.hasTimeLimit ) {
                        return;
                    }

                    self.startCountdown()
                } );
            }else if(this.hasTimeLimit){
                this.triggerReady();
                this.startCountdown();
            }else{
                this.triggerReady();
            }
        },
        triggerReady() {
            this.container.trigger(`mc_question_ready`);
        },
        triggerTimeEnded() {
            this.container.trigger('mc_question_time_ended');
        },
        startTriCoutdown() {
            let countdown = 4;

            const interval = setInterval(() => {
                countdown--;
                this.container.find('.coutdown').text(countdown);

                if (countdown === 0) {
                    clearInterval(interval);
                    this.container.find('.coutdown').addClass('hide');
                    this.container.find('.wpProQuiz_question').removeClass('hide');
                    this.container.trigger( 'mc_tri_coutdown_completed' );
                }

                if (this.quizCompleted) {
                    clearInterval(interval);
                }
            }, 1000);
        },
        startCountdown() {
            this.container.find('.timer-container').removeClass('hide');
            this.updateTimerDisplay();

            this.timerInterval = setInterval(() => {
                if (this.quizCompleted) {
                    clearInterval(this.timerInterval);
                }

                if (this.totalSeconds > 0) {
                    this.totalSeconds--;
                    this.updateTimerDisplay();
                } else {
                    clearInterval(this.timerInterval);
                    this.container.find('.timer-container .times-up').text(customQuizAjax.i18n.timeIsUp);
                    this.container.find('.wpProQuiz_QuestionButton[name="next"]').attr('style', 'float:right; display: inline-block !important');

                    this.container.find('.wpProQuiz_question').addClass('hide');

                    this.triggerTimeEnded();
                }
            }, 1000);
        },
        updateTimerDisplay() {
            const minutes = Math.floor(this.totalSeconds / 60);
            const seconds = this.totalSeconds % 60;
            this.container.find('.timer').text(`${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
        },
    };

    $('.wpProQuiz_listItem').on('listItemDisplayed', function() {
        Question_Time_Limit.init(this);
    });
});
