import 'jquery-modal';

jQuery(document).ready(function($) {
    // Bail if not on a LearnDash quiz page
    if (!$('body').hasClass('learndash-cpt-sfwd-quiz')) {
        return;
    }

    $(document).on('click', '.question-modal', function(event) {
        event.preventDefault();
        this.blur(); // Manually remove focus from clicked link.

        const questionPostId = $(this).data('question_post_id');
        const modalId = $(this).data('modal_id');

        $('div.modal').remove();
        
        $.ajax({
            type: 'POST',
            url: customQuizAjax.ajax_url,
            data: {
                action: 'load_question_popup',
                modalId: $(this).data('modal_id'),
                questionPostId: $(this).data('question_post_id'),
                nonce: customQuizAjax.get_question_modal_nonce
            },
            success: function(response) {
                $(response.data).appendTo('body').modal();

                $('.wpProQuiz_listItem').each(function() {
                    if ($(this).is(':visible')) {
                        const listItem = $(this);
    
                        $(document).on('click', '.jquery-modal .close-modal', function() {
                            listItem.trigger('mc_question_popup_closed');
                        });

                        var divId = "adobe-dc-view_" + questionPostId + "_" + modalId;
                        var adobeDCView = new AdobeDC.View({clientId: customQuizAjax.adobeClientId, divId: divId});
                        adobeDCView.previewFile({
                            content: {location: {url: $("#" + divId).data('url')}},
                            metaData: {fileName: "File.pdf"}
                        });
                    }
                });
            }
        })
    });

     // Function to generate modal links
     function generateModalLinks(questionPostId, modals) {
        let modalLinksHtml = '';
        for (let i = 1; i < 4; i++) {
            if( ! modals[i] ) {
                continue;
            }

            modalLinksHtml += '<a data-modal_id="' + i + '" data-question_post_id="' + questionPostId + '" href="#" class="question-modal' 
            + (modals[i].auto_start ? ' auto-start' : '') 
            + '"><span class="material-symbols-outlined">' 
            + modals[i].icon
            + '</span></a>';
        }
        return modalLinksHtml;
    }

    const questionPostIds = [];

    // Iterate over each .wpProQuiz_question element
    $('.wpProQuiz_listItem').each(function() {
        // Extract question_post_id from data-question-meta attribute
        let questionMeta = $(this).data('question-meta');
        let questionPostId = questionMeta ? questionMeta.question_post_id : 0;

        questionPostIds.push(questionPostId);
    });

    $.ajax({
        type: 'POST',
        url: customQuizAjax.ajax_url,
        data: {
            action: 'get_modal_availabilities',
            question_post_id_collection: questionPostIds,
            nonce: customQuizAjax.get_modal_availabilities_nonce
        },
        success: function(response) {
            $('.wpProQuiz_listItem').each(function() {
                let questionMeta = $(this).data('question-meta');
                let questionPostId = questionMeta ? questionMeta.question_post_id : 0;
        
                const modals = response.data[questionPostId];

                let modalLinksHtml = generateModalLinks(questionPostId, modals);
                $(this).find('.wpProQuiz_question').prepend(`<div class="question-modals-container">${modalLinksHtml}</div>`);

                if ($(this).is(':visible')) {
                    $(this).find('.question-modal.auto-start').trigger('click');
                }
            });
        }
    })

    jQuery('.wpProQuiz_listItem').on('mc_question_ready', function() {
        $(this).find('.question-modal.auto-start').trigger('click');
    });
});