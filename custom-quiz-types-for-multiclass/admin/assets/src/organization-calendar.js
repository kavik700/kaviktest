import 'flatpickr';

export function transformTextarea(textareas) {
  textareas.each(function() {
    // Use jQuery to check if there's already an input sibling
    var $textarea = jQuery(this);
    var existingInput = $textarea.siblings('input[type="text"]');

    // Early return if an input already exists
    if (existingInput.length > 0) {
      return;
    }

    // Create a new input element
    var $input = jQuery('<input>', {
      type: 'text',
      class: $textarea.attr('class'),
      style: 'width: 100%;'
    });

    // Set the input's value to the textarea's value
    $input.val($textarea.val());

    // Insert the input as a sibling to the textarea
    $textarea.after($input);

    // Add an event listener to update the textarea's value when the input changes
    $input.on('input', function() {
      $textarea.val($input.val());
    });

    // Initialize flatpickr on the input
    flatpickr($input[0], {
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      mode: "range",
    });
  });
}

jQuery(document).ready(function($) {
    $('.addAnswer').on('click', function(e) {
      $('.wpProQuiz_matrix_answer.flatpickr-input').remove();
      transformTextarea($('.wpProQuiz_matrix_answer'))
    });
});