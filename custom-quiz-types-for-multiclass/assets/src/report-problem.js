jQuery(document).ready(function($) {
    // Reset modal to initial state when closed
    function resetModal() {
        // Hide loading bar
        $('.report-problem-loading-wrapper').removeClass('show').css('display', 'none');
        
        // Hide all notifications
        $('.report-problem-notification').removeClass('show').css('display', 'none');
        
        // Show all form content
        $('#report-problem-form > *').removeClass('hidden-during-loading');
        
        // Reset form
        $('#report-problem-form')[0].reset();
        
        // Clear validation errors
        $('#no-selection-notification').css('visibility', 'hidden');
        
        // Re-enable submit button
        $('.report-problem-send').prop('disabled', false).removeClass('loading');
    }
    
    // Listen for modal close event
    $(document).on('modal:close', function() {
        resetModal();
    });
    
    function showLoadingBar() {
        // Hide all notifications
        $('.report-problem-notification').removeClass('show');
        
        // Hide form content
        $('#report-problem-form > *:not(.report-problem-loading-wrapper):not(.report-problem-notification)').addClass('hidden-during-loading');
        
        // Show loading wrapper
        const $loadingWrapper = $('.report-problem-loading-wrapper');
        $loadingWrapper.css('display', 'block');
        
        // Trigger animation
        setTimeout(() => {
            $loadingWrapper.addClass('show');
        }, 10);
    }
    
    function hideLoadingBar() {
        const $loadingWrapper = $('.report-problem-loading-wrapper');
        $loadingWrapper.removeClass('show');
        setTimeout(() => {
            $loadingWrapper.css('display', 'none');
        }, 300);
    }
    
    function showFormContent() {
        $('#report-problem-form > *').removeClass('hidden-during-loading');
    }
    
    function showNotification(type = 'success') {
        // Hide all notifications first
        $('.report-problem-notification').removeClass('show').css('display', 'none');
        
        // Show the appropriate notification
        const $notification = $('.report-problem-notification-' + type);
        $notification.css('display', 'block');
        
        // Trigger animation
        setTimeout(() => {
            $notification.addClass('show');
        }, 10);
    }

    function validateForm() {
        if ($('input[name="issues[]"]:checked').length === 0) {
            $('#no-selection-notification').css('visibility', 'visible');
            return false;
        } else {
            $('#no-selection-notification').css('visibility', 'hidden');
            return true;
        }
    }

    $('input[name="issues[]"]').on('change', function() {
        validateForm();
    });

    $('.report-problem-cancel').on('click', function(event) {
        event.preventDefault();
        $('#report-problem-form')[0].reset();
    });

    $('#report-problem-form').on('submit', function(event) {
        event.preventDefault();

        if (!validateForm()) {
            return;
        }

        const $btn = $(this).find('.report-problem-send');

        $btn.prop('disabled', true)
                   .addClass('loading');

        // Show loading bar
        showLoadingBar();

        const issues = $(this).find('input[name="issues[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        const detials = $(this).find('textarea').val();

        const data = {
            action: 'mc_report_problem',
            data: {
                resource_id: $('#report-problem-container').data('resource-id'),
                resource_type: $('#report-problem-container').data('resource-type'),
                question_post_id: $('#report-problem-container').data('resource-type') === 'quiz' ? $('.wpProQuiz_listItem:visible').data('question-meta').question_post_id : null,
                issues: issues,
                details: detials
            },
            nonce: customQuizAjax.report_problem_nonce
        };

        $.ajax({
            type: 'POST',
            url: customQuizAjax.ajax_url,
            data: data,
            success: function(response) {
                hideLoadingBar();
                
                $btn.prop('disabled', false)
                       .removeClass('loading');

                if (response.success) {
                    showNotification('success');
                    // Close modal after showing notification
                    setTimeout(() => {
                        $('#report-problem-form')[0].reset();
                        $.modal.close();
                    }, 2000);
                } else {
                    showFormContent();
                    showNotification('error');
                }
            },
            error: function() {
                hideLoadingBar();
                showFormContent();
                showNotification('error');
                $btn.prop('disabled', false)
                       .removeClass('loading');
            }
        });
    });
});