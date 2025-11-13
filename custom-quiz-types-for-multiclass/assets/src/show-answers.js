jQuery(document).on('mc_ld_quiz_completed', function(){
    const data = [];

    jQuery('body').addClass('mc-quiz-completed');

    jQuery('.calendar, .color-combination-container').each(function(){
        const container = jQuery(this).parents('.wpProQuiz_listItem');

        const questionProId = parseInt(container.data('question-meta').question_pro_id);
        const questionPostId = parseInt(container.data('question-meta').question_post_id);

        container.addClass( `question-post-id-${questionPostId}` );
  
        data.push({
          'question_post_id': questionPostId,
          'question_pro_id': questionProId,
          'signature': jQuery(this).data('signature')
        });
      });


      jQuery('.mc-numerical_processing').each(function(){
        const container = jQuery(this);

        const questionPostId = parseInt(container.data('question-meta').question_post_id);

        const signature = container.data('signature');

        container.addClass( `question-post-id-${questionPostId}` );

        jQuery(document).trigger('mc_answer_ready_numerical_processing', {
          'htmlClass': `question-post-id-${questionPostId}`,
          'data': signature
        });
      });

     jQuery.post(
        customQuizAjax.ajax_url,
        {
            action: 'mc_get_answers',
            data
        },
        function(response) {
          for( let questionPostId in response.data ) {
            const answer = response.data[questionPostId];
            jQuery(document).trigger('mc_answer_ready_'+ (answer.type), {
              'htmlClass': `question-post-id-${questionPostId}`,
              'data': answer.data
            })
          }
        }
     );
  });