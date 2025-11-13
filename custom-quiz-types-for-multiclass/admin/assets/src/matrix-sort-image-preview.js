jQuery(document).ready(function($) {
    'use strict';

    // Function to check if a string is a valid image URL
    function isValidImageUrl(url) {
        if (!url || typeof url !== 'string') return false;
        
        // Remove whitespace
        url = url.trim();
        if (!url) return false;
        
        // Check if it's a valid URL
        try {
            new URL(url);
        } catch {
            return false;
        }
        
        // Check if it's an image file
        const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp'];
        const lowerUrl = url.toLowerCase();
        return imageExtensions.some(ext => lowerUrl.includes(ext));
    }

    // Function to create image preview
    function createImagePreview($textarea, imageUrl) {
        // Remove existing preview if any
        $textarea.siblings('.image-preview').remove();
        
        // Create preview container
        const $preview = $('<div class="image-preview" style="margin-top: 5px; max-width: 200px;"></div>');
        
        // Create image element
        const $img = $('<img>', {
            src: imageUrl,
            style: 'max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 3px;',
            alt: 'Preview',
            onerror: function() {
                $(this).replaceWith('<span style="color: #d63638; font-size: 12px;">Image not found</span>');
            }
        });
        
        $preview.append($img);
        $textarea.after($preview);
    }

    // Function to remove image preview
    function removeImagePreview($textarea) {
        $textarea.siblings('.image-preview').remove();
    }

    // Function to handle textarea input changes
    function handleTextareaChange() {
        const $textarea = $(this);
        const value = $textarea.val().trim();
        
        if (isValidImageUrl(value)) {
            createImagePreview($textarea, value);
        } else {
            removeImagePreview($textarea);
        }
    }

    // Function to initialize image previews for existing content
    function initializeExistingPreviews() {
        $('.matrix_sort_answer textarea[name="answerData[][sort_string]"]').each(function() {
            const $textarea = $(this);
            const value = $textarea.val().trim();
            
            if (isValidImageUrl(value)) {
                createImagePreview($textarea, value);
            }
        });
    }

    // Function to handle new answer rows
    function handleNewAnswerRow($newRow) {
        const $sortStringTextarea = $newRow.find('textarea[name="answerData[][sort_string]"]');
        
        // Add event listeners
        $sortStringTextarea.on('input', handleTextareaChange);
        $sortStringTextarea.on('blur', handleTextareaChange);
    }

    // Initialize existing previews
    initializeExistingPreviews();

    // Add event listeners to existing textareas
    $('.matrix_sort_answer textarea[name="answerData[][sort_string]"]').on('input', handleTextareaChange);
    $('.matrix_sort_answer textarea[name="answerData[][sort_string]"]').on('blur', handleTextareaChange);

    // Handle new answer rows when they're added
    $(document).on('DOMNodeInserted', '.matrix_sort_answer .answerList li', function() {
        const $newRow = $(this);
        
        // Use setTimeout to ensure the DOM is fully updated
        setTimeout(function() {
            handleNewAnswerRow($newRow);
        }, 100);
    });
}); 