jQuery(document).ready(function($) {
    const Quiz = {
        container:null,
        init: function(listItemContainer) {
            const container = $(listItemContainer);
            container.find('.arrow').on('click', function() {
                container.find('.arrow').removeClass('selected');
                $(this).addClass('selected');

                const selectedDegree = $(this).data('degree');
                container.find('.wpProQuiz_questionInput').val(selectedDegree);
            });
        }
    }

    $('.mc-different_perspective').on('mc_question_ready', function() {
        Quiz.init(this);
    });
});