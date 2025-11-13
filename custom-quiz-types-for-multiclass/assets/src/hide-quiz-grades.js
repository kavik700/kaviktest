jQuery(document).ready(function($) {
    $('.wpProQuiz_content').on('changeQuestion', function(e) {
        const index = e.values.index;
        const $question = $('.wpProQuiz_listItem').eq(index);
        const isUngraded = $question.hasClass('mc-ungraded');

        if(isUngraded) {
            const $e = $('.wpProQuiz_content');
            $contain = $e.find('.wpProQuiz_reviewQuestion');
            $cursor = $contain.find('div');
            $list = $contain.find('ol');
            $items = $list.children();

            $items.eq(index).addClass('mc-review-ungraded');
        }
    });
});