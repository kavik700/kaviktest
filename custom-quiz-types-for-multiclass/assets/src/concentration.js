jQuery(document).ready(function($) {
    const Quiz = {
        container:null,
        init: function(listItemContainer) {
            this.container = $(listItemContainer);

            var currentPosition = this.getRandomPosition();
            this.setRedBox(currentPosition.x, currentPosition.y);

            const self = this;

            let correctCount = 0;
            let currentRound = 1;

            const combinationCount = this.container.data('mc_total_points');
        
            this.container.find('#coordinate-input').on('input', function() {
                var input = $(this).val();
                if (input.length === 2) {
                    var x = parseInt(input.charAt(0));
                    var y = parseInt(input.charAt(1));
        
                    if (x === currentPosition.x && y === currentPosition.y) {
                        currentRound++;
                        self.container.find('.current-round').text(currentRound);

                        currentPosition = self.getRandomPosition();
                        self.setRedBox(currentPosition.x, currentPosition.y);
                        correctCount++;

                        self.container.find('.wpProQuiz_questionList .wpProQuiz_questionInput').val(correctCount);

                        if( correctCount === combinationCount ) {
                            self.container.find('.concentration-container .grid-container').hide();
                            self.container.find('.wpProQuiz_QuestionButton[name="next"]').click();
                        }
                    } else {
                    }
        
                    $(this).val('');
                }
            });
        },
        getRandomPosition: function() {
            var x = Math.floor(Math.random() * 3) + 1;
            var y = Math.floor(Math.random() * 3) + 1;
            return { x: x, y: y };
        },
        setRedBox: function(x, y) {
            $('.grid-item').removeClass('red-box');
            $('.grid-item').each(function() {
                if ($(this).data('x') == x && $(this).data('y') == y) {
                    $(this).addClass('red-box');
                }
            });
        }
    }

    $('.concentration-container').parents('.wpProQuiz_listItem').on('mc_question_ready', function() {
        Quiz.init(this);
    });
});