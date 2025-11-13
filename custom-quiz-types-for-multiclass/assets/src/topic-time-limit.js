jQuery(document).ready(function($) {
    const Topic_Time_Limit = {
        container: null,
        timerInterval: null,
        totalSeconds: 0,
        hasTimeLimit: false,

        init(container) {
            this.container = container;
            this.totalSeconds = this.container.find('.timer-container').data('mc_time_limit');
            this.hasTimeLimit = parseInt( this.totalSeconds )>0;

            if(this.hasTimeLimit){
                this.startCountdown();
            }
        },
        startCountdown() {
            this.container.find('.timer-container').removeClass('hide');
            this.updateTimerDisplay();

            this.timerInterval = setInterval(() => {
                if (this.totalSeconds > 0) {
                    this.totalSeconds--;
                    this.updateTimerDisplay();
                } else {
                    clearInterval(this.timerInterval);
                    this.container.find('.timer-container .times-up').text(customQuizAjax.i18n.timeIsUp);
                    this.container.find('.ld-tab-content').hide();
                }
            }, 1000);
        },
        updateTimerDisplay() {
            const minutes = Math.floor(this.totalSeconds / 60);
            const seconds = this.totalSeconds % 60;
            this.container.find('.timer').text(`${minutes}:${seconds < 10 ? '0' : ''}${seconds}`);
        },
    };

    if( $('body').hasClass('single-sfwd-topic') ) {
        Topic_Time_Limit.init($(this).find('.learndash-wrapper'));
    }
});
