jQuery(document).ready(function($) {
    const Core = {
        init: function(container) {
            let $this = $(container);

            $this.find('.mc-num-pc-answer').on('change', function() {
                $this.find('.wpProQuiz_questionInput').val(`${$this.data('signature')}:${$(this).val()}`);
            });

            let whiteIndices = [];
            let totals = {};

            function getRandomInt(min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function setRandomWhiteCells() {
                while (whiteIndices.length < 2) {
                    let randIndex = getRandomInt(0, 8);
                    if (!whiteIndices.includes(randIndex)) {
                        whiteIndices.push(randIndex);
                        totals[randIndex] = 0;
                    }
                }
                $this.find('.grid div').removeClass('white').text('');
                whiteIndices.forEach(index => {
                    $this.find('.grid div').eq(index).addClass('white');
                });
            }

            function startLoop() {
                const limit = 4;
                setRandomWhiteCells();
                let loopCount = 1;
                function loop() {
                    if (loopCount > (limit+1)) return;

                    const isLast = loopCount === limit+1;
                    
                    $this.find('.grid').show();

                    const numberCellIndex = whiteIndices[ loopCount % 2 ];

                    let randomNumber = getRandomInt(-9, 9);

                    if( ! isLast ) {
                        totals[numberCellIndex] += randomNumber;
                    }

                    $this.find('.grid div').text('');
                    $this.find('.grid div').eq(numberCellIndex).text(isLast ? '?' : randomNumber);

                    if( ! isLast ) {
                        setTimeout(function() {
                            $this.find('.grid').hide(0, function() {
                                setTimeout(loop, 1000);
                            });
                        }, $this.data('np_interval'));

                        loopCount++;
                    }else{
                        const answer = totals[numberCellIndex];
                        $this.data('signature', answer);

                        $this.find('.wpProQuiz_questionListItem, .mc-num-pc-answer').removeClass('v-hide');
                    }
                }
                loop();
            }

            startLoop();
        }
    }

    $('.mc-numerical_processing').on('mc_question_ready', function() {
        Core.init(this);
    });

    jQuery(document).on('mc_answer_ready_numerical_processing', (event, args) => {
        const answer = args.data;

        const container = jQuery(`.${args.htmlClass} .wpProQuiz_question`);

        container.find('.grid').remove();
        container.find('.wpProQuiz_freeCorrect').hide();
        container.find('.wpProQuiz_questionListItem label:eq(1)').html(`Richtig: ${answer}`);
    });
});
