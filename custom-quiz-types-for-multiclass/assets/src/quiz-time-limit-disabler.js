jQuery(document).ready(function($) {
    $('.cqt-quiz-time-limit-disabler #cqt-time-limit-toggle').on('change', function() {
        var isChecked = $(this).is(':checked');
        var confirmReload = confirm( customQuizAjax.i18n.confirmReload );

        if (confirmReload) {
            if( ! isChecked ) {
                var newAction = 'disable-time-limit';
                window.location.href = window.location.pathname + '?action=' + newAction;
            } else {
                window.location.href = window.location.pathname;
            }
        } else {
            $(this).prop('checked', !isChecked);
        }
    });
});

const sendingElement = document.querySelector('.wpProQuiz_sending');

// Create a mutation observer
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'style') {
            const isVisible = sendingElement.style.display !== 'none';
            if (isVisible) {
                jQuery('.cqt-time-limit-toggle-switch').hide();
            }
        }
    });
});

// Start observing
observer.observe(sendingElement, {
    attributes: true,
    attributeFilter: ['style']
});