jQuery(document).ready(function($) {
    $('.mc-concentration_numbers .wpProQuiz_cloze').each(function() {
        var $this = $(this);
    
        // Hide the existing input
        var $input = $this.find('input');
        $input.hide();
    
        // Create a new span to wrap the radio buttons
        var radioGroup = $('<span></span>');

        const name = 'wpProQuiz_cloze_' +  Math.random().toString(36).substring(7);
    
        // Create the "Yes" radio button
        var yesRadio = $('<input>')
            .attr('type', 'radio')
            .attr('name', name)
            .attr('value', 'yes');
        radioGroup.append(yesRadio).append(' Ja ');
    
        // Create the "No" radio button
        var noRadio = $('<input>')
            .attr('type', 'radio')
            .attr('name', name)
            .attr('value', 'no');
        radioGroup.append(noRadio).append(' Nein ');
    
        // Append the radio group after the input
        $this.append(radioGroup);
    
        // Update the hidden input value when the radio buttons are changed
        radioGroup.find('input[type="radio"]').change(function() {
            var selectedValue = $(this).val();
            $input.val(selectedValue);
        });
    });

    jQuery(document).on('mc_ld_quiz_completed', () => {
        $('.mc-concentration_numbers .wpProQuiz_cloze input').prop('disabled', true);
    });
});