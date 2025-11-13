/**
 * Pictogram Images - WordPress Media Uploader Integration
 */
(function($) {
    'use strict';

    let mediaUploader;
    let selectedImages = [];

    $(document).ready(function() {
        initPictogramImages();
    });

    /**
     * Initialize pictogram images functionality
     */
    function initPictogramImages() {
        // Load existing images from hidden input
        loadExistingImages();

        // Add image button click handler
        $('#add-pictogram-image').on('click', function(e) {
            e.preventDefault();
            openMediaUploader();
        });

        // Remove image handler
        $(document).on('click', '.pictogram-remove-image', function(e) {
            e.preventDefault();
            removeImage($(this));
        });

        // Answer radio button change handler
        $(document).on('change', '.pictogram-answer-radio', function() {
            updateImagesData();
            // Auto-generate table when answer changes
            generatePictogramTable();
        });

        // Make images sortable (will be initialized after DOM is ready)
        initSortable();

        // Watch for question type changes to reload content
        $('input[name="multiclass_question_type"]').on('change', function() {
            handleQuestionTypeChange();
        });
    }

    /**
     * Initialize or refresh sortable
     */
    function initSortable() {
        const $list = $('#pictogram-images-list');
        
        if (!$.fn.sortable) {
            return;
        }
        
        // Remove any stray classes first
        $('.pictogram-image-item').removeClass('is-dragging ui-sortable-handle');
        
        // Destroy existing sortable if it exists
        if ($list.hasClass('ui-sortable')) {
            try {
                $list.sortable('destroy');
            } catch(e) {
                // Ignore errors during destroy
            }
        }
        
        // Small delay to ensure DOM is ready
        setTimeout(function() {
            // Initialize sortable with optimized settings
            $list.sortable({
                items: '.pictogram-image-item',
                handle: '.pictogram-drag-handle',
                cursor: 'move',
                opacity: 0.9,
                placeholder: 'pictogram-sortable-placeholder',
                tolerance: 'pointer',
                distance: 5,
                delay: 100,
                revert: 150,
                forcePlaceholderSize: true,
                start: function(event, ui) {
                    // Disable transitions during drag for better performance
                    ui.item.addClass('is-dragging');
                },
                stop: function(event, ui) {
                    // Re-enable transitions after drag
                    ui.item.removeClass('is-dragging');
                },
                update: function(event, ui) {
                    updateImagesData();
                    
                    // Auto-generate table when order changes
                    generatePictogramTable();
                    
                    // Refresh sortable after reorder
                    setTimeout(function() {
                        if ($list.hasClass('ui-sortable')) {
                            $list.sortable('refresh');
                        }
                    }, 200);
                }
            });
        }, 50);
    }

    /**
     * Load existing images from hidden input
     */
    function loadExistingImages() {
        const dataInput = $('#pictogram-images-data');
        
        if (dataInput.length && dataInput.val()) {
            try {
                selectedImages = JSON.parse(dataInput.val());
                
                // Sync the loaded data with the DOM elements
                syncDataWithDOM();
            } catch(e) {
                selectedImages = [];
            }
        }
    }

    /**
     * Sync loaded data with DOM radio buttons
     */
    function syncDataWithDOM() {
        $('#pictogram-images-list .pictogram-image-item').each(function() {
            const $item = $(this);
            const imageId = parseInt($item.data('image-id'));
            
            // Find the matching data
            const imageData = selectedImages.find(function(img) {
                return (img.id || img) === imageId;
            });
            
            if (imageData && imageData.answer) {
                // Set the correct radio button
                $item.find('.pictogram-answer-radio[value="' + imageData.answer + '"]').prop('checked', true);
                $item.attr('data-answer', imageData.answer);
            }
        });
        
        // Initialize sortable after syncing
        initSortable();
    }

    /**
     * Open WordPress media uploader
     */
    function openMediaUploader() {
        // If the uploader object already exists, reuse it
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create new media uploader
        mediaUploader = wp.media({
            title: pictogramData.mediaTitle || 'Select Pictogram Images',
            button: {
                text: pictogramData.mediaButton || 'Use Images'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });

        // When images are selected
        mediaUploader.on('select', function() {
            const attachments = mediaUploader.state().get('selection').toJSON();
            addImages(attachments);
        });

        // Open the uploader
        mediaUploader.open();
    }

    /**
     * Add images to the list
     */
    function addImages(attachments) {
        attachments.forEach(function(attachment) {
            // Check if image already exists
            const exists = selectedImages.some(function(img) {
                return (img.id || img) === attachment.id;
            });
            
            if (!exists) {
                const imageData = {
                    id: attachment.id,
                    answer: '' // Default: no answer selected
                };
                selectedImages.push(imageData);
                appendImageToList(attachment);
            }
        });

        updateImagesData();
        
        // Refresh sortable after adding new items
        initSortable();
        
        // Auto-generate table after adding images (if all have answers)
        setTimeout(function() {
            generatePictogramTable();
        }, 100);
    }

    /**
     * Append image item to list
     */
    function appendImageToList(attachment, answer) {
        const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
        const imageTitle = attachment.title || attachment.filename;
        const imageId = attachment.id;
        const uniqueId = 'pictogram_' + imageId + '_' + Date.now();
        const selectedAnswer = answer || '';

        const imageHtml = `
            <div class="pictogram-image-item" data-image-id="${imageId}" data-answer="${selectedAnswer}">
                <div class="pictogram-drag-handle" title="Drag to reorder">
                    <span class="dashicons dashicons-menu"></span>
                </div>
                <div class="pictogram-image-preview">
                    <img src="${imageUrl}" alt="${imageTitle}" />
                    <div class="pictogram-image-overlay">
                        <button type="button" class="button pictogram-remove-image" data-image-id="${imageId}">
                            <span class="dashicons dashicons-no"></span>
                            ${pictogramData.removeImage || 'Remove'}
                        </button>
                    </div>
                </div>
                <div class="pictogram-image-info">
                    <span class="pictogram-image-title">${imageTitle}</span>
                    <div class="pictogram-answer-selection">
                        <label class="pictogram-answer-label">
                            <input type="radio" name="answer_${uniqueId}" value="yes" class="pictogram-answer-radio" ${selectedAnswer === 'yes' ? 'checked' : ''} />
                            <span class="answer-option answer-yes">Yes</span>
                        </label>
                        <label class="pictogram-answer-label">
                            <input type="radio" name="answer_${uniqueId}" value="no" class="pictogram-answer-radio" ${selectedAnswer === 'no' ? 'checked' : ''} />
                            <span class="answer-option answer-no">No</span>
                        </label>
                    </div>
                </div>
            </div>
        `;

        $('#pictogram-images-list').append(imageHtml);
    }

    /**
     * Remove image from list
     */
    function removeImage($button) {
        const imageId = parseInt($button.data('image-id'));
        const $item = $button.closest('.pictogram-image-item');

        // Remove from array
        selectedImages = selectedImages.filter(function(img) {
            const id = img.id || img;
            return id !== imageId;
        });

        // Remove from DOM with animation
        $item.fadeOut(300, function() {
            $(this).remove();
            updateImagesData();
            
            // Reinitialize sortable after removing an item
            initSortable();
            
            // Auto-generate table after removing image
            generatePictogramTable();
        });
    }

    /**
     * Update hidden input with current images data
     */
    function updateImagesData() {
        // Get current order and answers from DOM
        const currentData = [];
        $('#pictogram-images-list .pictogram-image-item').each(function() {
            const $item = $(this);
            const imageId = parseInt($item.data('image-id'));
            const selectedRadio = $item.find('.pictogram-answer-radio:checked');
            const answer = selectedRadio.length ? selectedRadio.val() : '';

            currentData.push({
                id: imageId,
                answer: answer
            });
        });

        selectedImages = currentData;
        $('#pictogram-images-data').val(JSON.stringify(selectedImages));
    }

    /**
     * Handle question type change
     */
    function handleQuestionTypeChange() {
        const selectedType = $('input[name="multiclass_question_type"]:checked').val();
        const $metaboxContent = $('#multiclass_pictogram_images .inside');

        if (selectedType === 'pictogram') {
            // Show a message to save first
            $metaboxContent.html('<p style="color: #d63638; padding: 10px; background: #fff8e5; border-left: 4px solid #dba617; margin: 0;"><strong>Note:</strong> Please save/update this question to enable the image upload feature for Pictogram type.</p>');
        }
    }

    /**
     * Generate pictogram table automatically
     */
    function generatePictogramTable() {
        console.log('generatePictogramTable called');
        console.log('selectedImages:', selectedImages);
        
        // Silently exit if no images
        if (selectedImages.length === 0) {
            console.log('No images, exiting');
            return;
        }

        // Check if all images have yes/no selections
        const hasAllAnswers = selectedImages.every(function(img) {
            return img.answer === 'yes' || img.answer === 'no';
        });

        console.log('hasAllAnswers:', hasAllAnswers);
        
        // Only generate if all images have answers
        if (!hasAllAnswers) {
            console.log('Not all images have answers, exiting');
            return;
        }

        // Find the answer textarea
        const $answerTextarea = $('textarea[name="answerData[cloze][answer]"]');
        
        console.log('Answer textarea found:', $answerTextarea.length);
        console.log('Textarea selector:', 'textarea[name="answerData[cloze][answer]"]');
        
        if ($answerTextarea.length === 0) {
            console.log('Textarea not found, exiting');
            return;
        }

        // Get all image items from DOM to ensure we have the latest data
        const tableRows = [];
        let currentRow = [];
        
        $('#pictogram-images-list .pictogram-image-item').each(function(index) {
            const $item = $(this);
            const imageId = parseInt($item.data('image-id'));
            const $img = $item.find('.pictogram-image-preview img');
            const imageSrc = $img.attr('src');
            const selectedRadio = $item.find('.pictogram-answer-radio:checked');
            const answer = selectedRadio.length ? selectedRadio.val() : '';

            if (answer) {
                currentRow.push({
                    src: imageSrc,
                    answer: answer === 'yes' ? '{yes}' : '{no}'
                });

                // Create a new row every 5 images
                if (currentRow.length === 5) {
                    tableRows.push(currentRow);
                    currentRow = [];
                }
            }
        });

        // Add remaining images as last row
        if (currentRow.length > 0) {
            tableRows.push(currentRow);
        }

        // Generate table HTML
        let tableHtml = '<table border="1">\n<tbody>\n';
        
        tableRows.forEach(function(row) {
            tableHtml += '<tr>\n';
            row.forEach(function(cell) {
                tableHtml += '<td><img src="' + cell.src + '" /> ' + cell.answer + '</td>\n';
            });
            tableHtml += '</tr>\n';
        });
        
        tableHtml += '</tbody>\n</table>';

        console.log('Table HTML generated:', tableHtml);
        
        // Insert into textarea - handle both regular textarea and TinyMCE
        const textareaId = $answerTextarea.attr('id');
        
        // Check if TinyMCE is available and initialized for this textarea
        if (typeof tinymce !== 'undefined' && tinymce.get(textareaId)) {
            console.log('Using TinyMCE to set content');
            tinymce.get(textareaId).setContent(tableHtml);
        } else {
            console.log('Using jQuery to set textarea value');
            $answerTextarea.val(tableHtml);
        }

        // Trigger change event
        $answerTextarea.trigger('change');
        
        console.log('Table inserted successfully!');
    }

})(jQuery);
