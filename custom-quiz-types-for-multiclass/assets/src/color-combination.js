jQuery(document).ready(function($) {
    const Quiz = {
        container: null,
        quizCompleted: false,
        timerInterval: null,

        init(container) {
            this.container = $(container);

            this.bindCircleClick();

            jQuery(document).on('mc_ld_quiz_completed', () => {
                this.quizCompleted = true;
            });

            this.startQuiz();

            this.container.find('.remember-btn').click(() => {
                this.totalSeconds = Math.max(0, this.totalSeconds - 20); // TODO: connect it to the common timer
                this.playCombination();
            });
        },
        playCombination() {
            this.container.find(`.color-combination`).addClass('hide');
            this.container.find(`.color-combination`).removeClass('active');
            const container = this.container.find(`.color-combination`);
            container.removeClass('hide').addClass('active');
            container.find('.coutdown, .circle-container.answers').addClass('hide');
            container.find('.circle-container.model').removeClass('hide');
            container.find('.remember-btn').addClass('hide');

            setTimeout(() => {
                container.find('.circle-container.model').addClass('hide');
                container.find('.circle-container.answers').removeClass('hide');
                container.find('.remember-btn').removeClass('hide');
            }, 5000);
        },
        startQuiz() {
            if (this.quizCompleted) {
                return;
            }
            this.playCombination();
        },
        decode(str) {
            const obj = {};
            const items = str.split(':');
        
            items.forEach((item) => {
                const parts = item.split('|');
        
                if (parts.length === 3) {
                    const key = parts[0];
                    const value = parts[1] + '|' + parts[2];
                    obj[key] = value;
                }
            });
        
            return obj;
        },
        
        encodeObject(obj) {
            const parts = [];
                
            $.each(obj, (key, value) => {
                parts.push(key + "|" + value);
            });
    
            return parts.join(':');
        },

        bindCircleClick() {
            this.container.find('.circle-container.answers .circle').click(function() {
                const question = $(this).closest('.wpProQuiz_question');
                const input = question.find('.wpProQuiz_questionInput');
                const value = input.val() ? Quiz.decode(input.val()) : {};
        
                const colors = ['empty', 'red', 'blue', 'black'];
                let currentColor = $(this).data('color') || 0;
                currentColor = (currentColor + 1) % colors.length;
                $(this).css('background-color', colors[currentColor]);
                $(this).data('color', currentColor);
        
                const container = $(this).parents('.circle-container.answers');
                const signature = container.data('signature');
        
                const selectedColors = container.find('.circle').map(function() {
                    return colors[$(this).data('color') || 0];
                }).get();
        
                const colorString = selectedColors.join(',');
        
                const mainContainer = $(this).parents('.color-combination');
                const mainContainerId = mainContainer.data('combination_id');
        
                value[mainContainerId] = signature + '|' + colorString;
                input.val(Quiz.encodeObject(value));
            });
        },
        updateAnswerDisplay(args) {
            const container = jQuery(`.${args.htmlClass} .wpProQuiz_question .wpProQuiz_question_text`);
            let content = '<div class="color-combination-container answers">';

            for (let combinationKey in args.data) {
                const colors = args.data[combinationKey];

                content += `<div class="circle-container">`;

                for (let colorKey in colors) {
                    content += `<div class='circle' style='background-color: ${colors[colorKey]};' data-color='0'></div>`;
                }

                content += '</div>';
            }

            content += '</div>';
            container.html(content);
        }
    };

    $('.color-combination-container').parents('.wpProQuiz_listItem').on('mc_question_ready', function() {
        Quiz.init(this);
    });

    jQuery(document).on('mc_answer_ready_color_combination', (event, args) => {
        Quiz.updateAnswerDisplay(args);
    });
});
