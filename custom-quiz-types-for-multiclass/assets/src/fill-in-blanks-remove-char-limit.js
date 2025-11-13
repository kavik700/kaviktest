document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a quiz page
    if (document.body.classList.contains('learndash-cpt-sfwd-quiz')) {
        // Find all fill-in-blank inputs within wpProQuiz_cloze spans
        const clozeInputs = document.querySelectorAll('.wpProQuiz_cloze input[type="text"]');
        
        // Remove all limiting attributes from each input
        clozeInputs.forEach(input => {
            input.removeAttribute('maxlength');
            input.removeAttribute('size');
            input.removeAttribute('data-wordlen');
        });
    }
});
