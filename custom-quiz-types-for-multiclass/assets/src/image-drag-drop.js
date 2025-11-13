jQuery(document).ready(function($) {
    const Core = {
        init: function(container) {
            let $this = $(container);
            let touchStartX, touchStartY;
            let isDragging = false;
            let $draggedElement = null;

            // Function to update z-indices of dropzones
            const updateDropzoneZIndices = () => {
                const $dropzones = $this.find('.dropzone');
                const baseZIndex = 9;
                
                // First pass: set all dropzones to base z-index
                $dropzones.each(function() {
                    $(this).css('z-index', baseZIndex);
                });
                
                // Second pass: increase z-index for dropzones with children
                $dropzones.each(function() {
                    if ($(this).children().length > 0) {
                        $(this).css('z-index', baseZIndex + 1);
                    }
                });
            };

            // Update z-indices on init
            updateDropzoneZIndices();
            
            // Store original wrapper reference for each draggable (for return functionality)
            $this.find('.shake-wrapper .draggable').each(function() {
                const $wrapper = $(this).closest('.shake-wrapper');
                $(this).data('originalWrapper', $wrapper);
            });
            
            // Add return buttons to any existing placed images
            $this.find('.draggable.cover').each(function() {
                addReturnButton($(this));
            });

            // Add ARIA roles and labels for accessibility
            $this.find('.draggable').each(function() {
                $(this)
                    .attr('role', 'button')
                    .attr('aria-grabbed', 'false')
                    .attr('tabindex', '0');
            });
            
            // Use delegated events so they persist when elements are moved in DOM
            $this.on('dragstart', '.draggable', dragStart);
            $this.on('dragend', '.draggable', dragEnd);
            $this.on('keydown', '.draggable', handleKeyboardDrag);

            // Also attach events to shake-wrapper to ensure full coverage
            $this.find('.shake-wrapper').each(function() {
                const wrapper = this;
                const $wrapper = $(this);
                
                // Use native addEventListener for touch events with passive: false
                wrapper.addEventListener('touchstart', function(e) {
                    const $draggable = $wrapper.find('.draggable');
                    if ($draggable.length) {
                        handleTouchStart.call($draggable[0], e);
                        // Stop shake animation during drag
                        $wrapper.css('animation-play-state', 'paused');
                    }
                }, { passive: false });
                
                wrapper.addEventListener('touchend', function(e) {
                    const $draggable = $wrapper.find('.draggable');
                    if ($draggable.length) {
                        handleTouchEnd.call($draggable[0], e);
                        // Resume shake animation after drag
                        $wrapper.css('animation-play-state', 'running');
                    }
                }, { passive: false });
                
                wrapper.addEventListener('touchmove', function(e) {
                    const $draggable = $wrapper.find('.draggable');
                    if ($draggable.length && isDragging) {
                        handleTouchMove.call($draggable[0], e);
                    }
                }, { passive: false });
                
                // Keep mousedown for desktop
                $wrapper.on('mousedown', function(e) {
                    const $draggable = $wrapper.find('.draggable');
                    if ($draggable.length) {
                        $wrapper.css('animation-play-state', 'paused');
                    }
                });
                
                $wrapper.on('mouseup', function(e) {
                    const $draggable = $wrapper.find('.draggable');
                    if ($draggable.length) {
                        $wrapper.css('animation-play-state', 'running');
                    }
                });
            });
            


            // Attach touch events using native event delegation with passive: false
            $this[0].addEventListener('touchstart', function(e) {
                const target = e.target.closest('.draggable');
                if (target) {
                    handleTouchStart.call(target, e);
                }
            }, { passive: false, capture: true });
            
            $this[0].addEventListener('touchmove', function(e) {
                const target = e.target.closest('.draggable');
                if (target) {
                    handleTouchMove.call(target, e);
                }
            }, { passive: false, capture: true });
            
            $this[0].addEventListener('touchend', function(e) {
                const target = e.target.closest('.draggable');
                if (target) {
                    handleTouchEnd.call(target, e);
                }
            }, { passive: false, capture: true });
            
            // Make the entire initial container a drop target
            const $initialContainer = $this.find('#initialContainer');
            $initialContainer
                .attr('role', 'listbox')
                .attr('aria-dropeffect', 'move')
                .addClass('initial-drop-target')
                .on('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('dropzone-hover');
                })
                .on('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).removeClass('dropzone-hover');
                    handleInitialContainerDrop(e);
                })
                .on('dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('dropzone-hover');
                })
                .on('dragleave', function(e) {
                    e.preventDefault();
                    // Only remove hover if not entering a child element
                    if (!$(e.relatedTarget).closest('#initialContainer').length) {
                        $(this).removeClass('dropzone-hover');
                    }
                });
            
            // Attach touch events to initialContainer with passive: false
            $initialContainer[0].addEventListener('touchstart', handleTouchStart, { passive: false });
            $initialContainer[0].addEventListener('touchmove', handleTouchMove, { passive: false });
            $initialContainer[0].addEventListener('touchend', function(e) {
                    // Enhanced touch end handling for initial container
                    if (!isDragging || !$draggedElement) return;
                    
                    const $target = $(e.target);
                    if ($target.closest('#initialContainer').length || $target.attr('id') === 'initialContainer') {
                        $initialContainer.removeClass('dropzone-hover');
                        returnToInitialContainer($draggedElement);
                        showFeedback('⁠⁠Block wurde zur ursprünglichen Position gesetzt', 'info');
                        
                        // Reset dragging state
                        isDragging = false;
                        $draggedElement = null;
                        $this.find('#initialContainer').removeClass('container-dragging');
                        $('.draggable-placeholder').remove();
                        $('.dropzone').removeClass('dropzone-hover');
                    } else {
                        handleTouchEnd(e);
                    }
                });

            // Set up dropzones
            $this.find('.dropzone').each(function() {
                const dropzone = this;
                $(this)
                    .attr('role', 'listbox')
                    .attr('aria-dropeffect', 'move')
                    .on('dragover', allowDrop)
                    .on('drop', handleDropzoneDrop)
                    .on('dragenter', handleDragEnter)
                    .on('dragleave', handleDragLeave);
                
                // Attach touch events with passive: false
                dropzone.addEventListener('touchstart', handleTouchStart, { passive: false });
                dropzone.addEventListener('touchmove', handleTouchMove, { passive: false });
                dropzone.addEventListener('touchend', handleTouchEnd, { passive: false });
            });

            // Remove drop handler from children of #initialContainer
            $this.find('#initialContainer *').off('drop');

            // Remove dropzone highlight from initial container
            $this.find('#initialContainer').removeClass('dropzone dropzone-active dropzone-hover');

            // Touch event handlers
            function handleTouchStart(e) {
                if (e.touches.length > 1) return; // Ignore multi-touch
                
                // Check if touch target is the return button - if so, don't handle drag
                if ($(e.target).hasClass('return-button') || $(e.target).closest('.return-button').length) {
                    return; // Let the click event handle it
                }
                
                // Safari fix: prevent default to avoid interference
                e.preventDefault();
                
                const touch = e.touches[0];
                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
                
                // Find the draggable element - could be the target itself or within shake-wrapper
                let $element = $(e.target).closest('.draggable');
                if (!$element.length) {
                    $element = $(e.target).find('.draggable').first();
                }
                
                if ($element.length) {
                    isDragging = true;
                    $draggedElement = $element;
                    $element.addClass('dragging');
                    $element.attr('aria-grabbed', 'true');
                    
                    // Pause shake animation during drag
                    $element.closest('.shake-wrapper').css('animation-play-state', 'paused');
                    
                    // Add container-dragging class if item is being dragged from a dropzone
                    if ($element.parent().hasClass('dropzone')) {
                        $this.find('#initialContainer').addClass('container-dragging');
                    }
                    
                    // Create visual dragging clone that follows the touch
                    $('.draggable-placeholder').remove();
                    const elementBg = $element.css('background-image');
                    const $placeholder = $('<div>', {
                        class: 'draggable-placeholder',
                        css: {
                            width: $element.width(),
                            height: $element.height(),
                            'background-image': elementBg,
                            'background-size': 'cover',
                            'background-position': 'center',
                            display: 'none',
                            left: 0,
                            top: 0
                        }
                    });
                    $('body').append($placeholder);
                }
            }

            function handleTouchMove(e) {
                if (!isDragging || !$draggedElement) return;
                
                const touch = e.touches[0];
                const deltaX = touch.clientX - touchStartX;
                const deltaY = touch.clientY - touchStartY;
                
                // Update placeholder position with smooth following effect
                const $placeholder = $('.draggable-placeholder');
                const rotation = Math.max(-5, Math.min(5, deltaX / 20)); // Subtle rotation based on movement
                
                $placeholder.css({
                    left: touch.clientX - ($placeholder.width() / 2),
                    top: touch.clientY - ($placeholder.height() / 2),
                    display: 'block',
                    transform: `rotate(${rotation}deg) scale(1.1)`
                });
                
                // Find dropzone under touch
                const $dropzone = $(document.elementFromPoint(touch.clientX, touch.clientY)).closest('.dropzone');
                if ($dropzone.length) {
                    $('.dropzone').removeClass('dropzone-hover');
                    $dropzone.addClass('dropzone-hover');
                    // Scale up placeholder when over dropzone
                    $placeholder.css('transform', `rotate(${rotation}deg) scale(1.15)`);
                } else {
                    $('.dropzone').removeClass('dropzone-hover');
                }
                
                e.preventDefault(); // Prevent scrolling while dragging
            }

            function handleTouchEnd(e) {
                if (!isDragging || !$draggedElement) return;
                
                // Safari fix: better touch target detection
                let $target, $dropzone;
                if (e.changedTouches && e.changedTouches.length > 0) {
                    const touch = e.changedTouches[0];
                    const elementAtPoint = document.elementFromPoint(touch.clientX, touch.clientY);
                    if (elementAtPoint) {
                        $target = $(elementAtPoint);
                        $dropzone = $target.closest('.dropzone');
                    }
                }
                
                // Fallback if touch target detection fails
                if (!$target || !$target.length) {
                    $target = $(e.target);
                    $dropzone = $target.closest('.dropzone');
                }
                
                // Remove dragging state
                $draggedElement.removeClass('dragging');
                $draggedElement.attr('aria-grabbed', 'false');
                $this.find('#initialContainer').removeClass('container-dragging');
                $('.draggable-placeholder').remove();
                $('.dropzone').removeClass('dropzone-hover');
                
                // Resume shake animation
                $draggedElement.closest('.shake-wrapper').css('animation-play-state', 'running');
                
                // Handle drop
                if ($dropzone && $dropzone.length) {
                    // Prevent multiple items in the same dropzone
                    if ($dropzone.find('.draggable').length > 0) {
                        showFeedback('In diesem Feld ist bereits ein Block!', 'error');
                        return;
                    }
                    
                    // Add animation class
                    $draggedElement.addClass('cover animate-drop');
                    
                    // Capture the element reference before it gets nulled
                    const $elementToAnimate = $draggedElement;
                    
                    // Use Promise to handle animation
                    new Promise(resolve => {
                        $elementToAnimate.on('animationend', resolve);
                        $dropzone.append($elementToAnimate);
                    }).then(() => {
                        $elementToAnimate.removeClass('animate-drop');
                        
                        // Add return button to the placed image
                        addReturnButton($elementToAnimate);
                        
                        // Update z-indices after drop
                        updateDropzoneZIndices();
                    });
                    
                    const targetPos = $draggedElement.parents('.dropzone').data('signature');
                    const signature = $draggedElement.data('signature');
                    const $targetLi = $this.find(`li[data-pos="${signature}"]`);
                    
                    // Animate the list item movement
                    $targetLi.addClass('animate-move');
                    $this.find(`li.wpProQuiz_questionListItem[data-pos="${targetPos}"] .wpProQuiz_maxtrixSortCriterion`).append($targetLi);
                    
                    showFeedback('Block wurde platziert!', 'success');
                } else if ($target && ($target.closest('#initialContainer').length || $target.attr('id') === 'initialContainer')) {
                    // Return to initial container using shared function
                    returnToInitialContainer($draggedElement);
                    showFeedback('⁠⁠Block wurde zur ursprünglichen Position gesetzt', 'info');
                }
                
                isDragging = false;
                $draggedElement = null;
            }

            function handleKeyboardDrag(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const $element = $(e.target);
                    $element.attr('aria-grabbed', 'true');
                    // Simulate drag start
                    const dragEvent = new DragEvent('dragstart', {
                        bubbles: true,
                        cancelable: true
                    });
                    e.target.dispatchEvent(dragEvent);
                }
            }

            function dragStart(event) {
                const $element = $(event.target);
                $element.addClass('dragging');
                $element.attr('aria-grabbed', 'true');
                
                // Pause shake animation during drag
                $element.closest('.shake-wrapper').css('animation-play-state', 'paused');
                
                // Add container-dragging class only if item is being dragged from a dropzone
                if ($element.parent().hasClass('dropzone')) {
                    $this.find('#initialContainer').addClass('container-dragging');
                }
                
                // Remove any existing placeholder before creating a new one
                $('.draggable-placeholder').remove();
                
                // Create placeholder with actual image
                const placeholderBg = $element.css('background-image');
                const $placeholder = $('<div>', {
                    class: 'draggable-placeholder',
                    css: {
                        width: Math.min($element.width(), 120),
                        height: Math.min($element.height(), 120 * ($element.height() / $element.width())),
                        'background-image': placeholderBg,
                        'background-size': '100% 100%',
                        'background-repeat': 'no-repeat',
                        display: 'none',
                        left: 0,
                        top: 0,
                        'border': '2px solid #007bff',
                        'border-radius': '4px',
                        'box-shadow': '0 4px 8px rgba(0,0,0,0.3)'
                    }
                });
                $('body').append($placeholder);
                
                // Create a properly sized ghost image for better visual feedback
                const originalWidth = $element.width();
                const originalHeight = $element.height();
                
                // Calculate a reasonable preview size (max 120px width while maintaining aspect ratio)
                const maxPreviewWidth = 120;
                const aspectRatio = originalHeight / originalWidth;
                const previewWidth = Math.min(originalWidth, maxPreviewWidth);
                const previewHeight = previewWidth * aspectRatio;
                
                // Create ghost with same background image
                const ghostBg = $element.css('background-image');
                const ghost = $('<div>').css({
                    'width': previewWidth + 'px',
                    'height': previewHeight + 'px',
                    'background-image': ghostBg,
                    'background-size': '100% 100%',
                    'background-repeat': 'no-repeat',
                    'position': 'absolute',
                    'top': '-1000px',
                    'left': '-1000px',
                    'opacity': '0.9',
                    'z-index': '9999',
                    'border': '2px solid #007bff',
                    'border-radius': '4px',
                    'box-shadow': '0 4px 8px rgba(0,0,0,0.3)'
                });
                
                $('body').append(ghost);
                
                // Set the custom drag image
                if (event.originalEvent && event.originalEvent.dataTransfer) {
                    try {
                        event.originalEvent.dataTransfer.setDragImage(ghost[0], previewWidth / 2, previewHeight / 2);
                    } catch (e) {
                        console.log('setDragImage failed, using default');
                    }
                    
                    // Clean up the ghost after drag operation
                    setTimeout(() => {
                        ghost.remove();
                    }, 1000);
                }
                
                // Store the data
                event.originalEvent.dataTransfer.setData('text/plain', event.target.id);
                
                // Remove active dropzone highlighting
                $this.find('.dropzone').removeClass('dropzone-active');

                // Add mousemove handler for placeholder with enhanced visual effects
                let lastX = event.pageX || 0;
                $(document).on('mousemove.dragPlaceholder', function(e) {
                    const deltaX = e.pageX - lastX;
                    const rotation = Math.max(-5, Math.min(5, deltaX * 0.5)); // Subtle rotation
                    lastX = e.pageX;
                    
                    $placeholder.css({
                        left: e.pageX - ($placeholder.width() / 2),
                        top: e.pageY - ($placeholder.height() / 2),
                        display: 'block',
                        transform: `rotate(${rotation}deg) scale(1.1)`
                    });
                });
            }

            function dragEnd(event) {
                const $element = $(event.target);
                $element.removeClass('dragging');
                $element.attr('aria-grabbed', 'false');
                
                // Resume shake animation after drag
                $element.closest('.shake-wrapper').css('animation-play-state', 'running');
                
                // Remove container-dragging class from initial container
                $this.find('#initialContainer').removeClass('container-dragging');
                
                // Remove ghost image and always remove placeholder
                $('.dragging-ghost').remove();
                $('.draggable-placeholder').remove();
                
                // Remove mousemove handler
                $(document).off('mousemove.dragPlaceholder');
                
                // Remove active dropzone highlighting
                $this.find('.dropzone').removeClass('dropzone-active');
            }

            function handleDragEnter(event) {
                event.preventDefault();
                const $target = $(event.target);
                if ($target.hasClass('dropzone')) {
                    $('.dropzone').removeClass('dropzone-hover');
                    $target.addClass('dropzone-hover');
                    // Insert placeholder into this dropzone
                    if ($target.find('.draggable-placeholder').length === 0) {
                        $target.append('<div class="draggable-placeholder"></div>');
                    }
                }
            }

            function handleDragLeave(event) {
                event.preventDefault();
                const $target = $(event.target);
                if ($target.hasClass('dropzone')) {
                    $target.removeClass('dropzone-hover');
                    $target.find('.draggable-placeholder').remove();
                }
            }
            
            function allowDrop(event) {
                event.preventDefault();
            }
            
                        function handleInitialContainerDrop(event) {
                event.preventDefault();
                event.stopPropagation();

                const data = event.originalEvent.dataTransfer.getData('text');
                const $draggedElement = $this.find(`#${data}`);
                
                if (!$draggedElement.length) return;
                
                // Remove hover state
                $this.find('.dropzone').removeClass('dropzone-hover');
                
                // Return element to initial container with wrapper
                returnToInitialContainer($draggedElement);
        
                // Show feedback
                showFeedback('⁠⁠Block wurde zur ursprünglichen Position gesetzt', 'info');
            }
            
            // Function to add return button to placed images
            function addReturnButton($element) {
                // Remove any existing return button
                $element.find('.return-button').remove();
                
                const $returnBtn = $('<button>', {
                    class: 'return-button',
                    text: '↶',
                    title: 'Click to return to initial container'
                });
                
                // Handle click (desktop and mobile fallback)
                $returnBtn.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Get the draggable element dynamically instead of using closure
                    const $draggableElement = $(this).parent('.draggable');
                    returnToInitialContainer($draggableElement);
                    showFeedback('Artikel zruggä!', 'info');
                });
                
                // Also handle touchend for better mobile support
                $returnBtn[0].addEventListener('touchend', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Get the draggable element dynamically
                    const $draggableElement = $(this).parent('.draggable');
                    returnToInitialContainer($draggableElement);
                    showFeedback('Artikel zruggä!', 'info');
                }, { passive: false });
                
                $element.append($returnBtn);
            }
            
            // Separate function to handle returning to initial container
            function returnToInitialContainer($element) {
                $element.addClass('animate-return');
                $element.removeClass('cover');
                
                // Remove return button
                $element.find('.return-button').remove();
                
                // Get or create wrapper
                let $wrapperToMove;
                const $originalWrapper = $element.data('originalWrapper');
                
                if ($originalWrapper && $originalWrapper.length) {
                    // Try to reuse original wrapper
                    $originalWrapper.empty().append($element);
                    $wrapperToMove = $originalWrapper;
                } else {
                    // Create new wrapper with shake classes
                    const shakeClass = 'shake_' + Math.random().toString(36).substr(2, 9);
                    $wrapperToMove = $('<div class="shake-wrapper ' + shakeClass + '">').append($element);
                    
                    // Generate shake animation for new wrapper
                    setTimeout(() => {
                        if (typeof generateShakeAnimation === 'function') {
                            generateShakeAnimation($wrapperToMove[0]);
                        }
                    }, 100);
                }
                
                // Re-set the originalWrapper data so it persists for next time
                $element.data('originalWrapper', $wrapperToMove);
                
                // Reset styles
                $element.css({
                    'opacity': '1',
                    'visibility': 'visible',
                    'display': 'block'
                });
                
                // Append to container immediately
                $initialContainer.append($wrapperToMove);
                
                // Handle animation cleanup
                setTimeout(() => {
                    $element.removeClass('animate-return');
                    updateDropzoneZIndices();
                }, 300);
            }
            
            function handleDropzoneDrop(event) {
                event.preventDefault();
                event.stopPropagation();

                const data = event.originalEvent.dataTransfer.getData('text');
                const $draggedElement = $this.find(`#${data}`);
                const $target = $(event.target).closest('.dropzone');
                
                // Remove hover state
                $this.find('.dropzone').removeClass('dropzone-hover');

                // Prevent multiple items in the same dropzone
                if ($target.find('.draggable').length > 0) {
                    showFeedback('In diesem Feld ist bereits ein Block!', 'error');
                    return;
                }
            
                // Add animation class
                $draggedElement.addClass('cover animate-drop');
                
                // Capture the element reference before it might get nulled
                const $elementToAnimate = $draggedElement;
                
                // Use Promise to handle animation
                new Promise(resolve => {
                    $elementToAnimate.on('animationend', resolve);
                    $target.append($elementToAnimate);
                }).then(() => {
                    $elementToAnimate.removeClass('animate-drop');
                    
                    // Add return button to the placed image
                    addReturnButton($elementToAnimate);
                    
                    // Update z-indices after drop
                    updateDropzoneZIndices();
                });
        
                const targetPos = $draggedElement.parents('.dropzone').data('signature');
                const signature = $draggedElement.data('signature');
                const $targetLi = $this.find(`li[data-pos="${signature}"]`);
                
                // Animate the list item movement
                $targetLi.addClass('animate-move');
                $this.find(`li.wpProQuiz_questionListItem[data-pos="${targetPos}"] .wpProQuiz_maxtrixSortCriterion`).append($targetLi);
                
                // Show success feedback
                showFeedback('Block wurde platziert!', 'success');
            }

            function showFeedback(message, type) {
                // Remove any existing notifications immediately
                $this.find('.drag-drop-feedback').remove();
                const $feedback = $('<div>', {
                    class: `drag-drop-feedback ${type}`,
                    text: message
                });
                
                $this.append($feedback);
                
                // Remove feedback after 2 seconds
                setTimeout(() => {
                    $feedback.fadeOut(() => $feedback.remove());
                }, 2000);
            }
        }
    }

    // Add CSS for animations and visual feedback
    const style = `
        .dragging {
            opacity: 0.4 !important;
            cursor: grabbing !important;
            transform: scale(0.9) !important;
            transition: transform 0.15s ease, opacity 0.15s ease !important;
            z-index: 1000 !important;
            position: relative !important;
            filter: brightness(0.8) !important;
        }
        
        .draggable-placeholder {
            position: fixed;
            pointer-events: none;
            z-index: 10000;
            opacity: 0.95;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3),
                        0 4px 12px rgba(0, 0, 0, 0.2),
                        0 0 0 2px rgba(0, 123, 255, 0.3);
            transition: transform 0.05s ease-out;
            will-change: transform, left, top;
            border: 2px solid rgba(0, 123, 255, 0.5);
        }
        
        .dropzone-active {
            border: 2px dashed #ccc;
        }
        
        .dropzone-hover {
            border: 3px solid #007bff;
            background-color: rgba(0, 123, 255, 0.15);
            animation: dropzonePulse 1s ease-in-out infinite;
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.3),
                        inset 0 0 20px rgba(0, 123, 255, 0.1);
        }
        
        @keyframes dropzonePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 20px rgba(0, 123, 255, 0.3),
                           inset 0 0 20px rgba(0, 123, 255, 0.1);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 0 30px rgba(0, 123, 255, 0.5),
                           inset 0 0 30px rgba(0, 123, 255, 0.2);
            }
        }
        
        .dropzone {
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        #initialContainer {
            min-height: 100px;
            border: 3px dashed #007bff;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-start;
            cursor: default;
            position: relative;
        }
        
        #initialContainer::before {
            content: "↩ Drag items here or use the ↶ button to return them";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #6c757d;
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        #initialContainer:empty::before {
            opacity: 1;
        }
        
        #initialContainer.dropzone-hover, #initialContainer.dropzone-active {
            border-color: #28a745 !important;
            background-color: rgba(40, 167, 69, 0.15) !important;
            border-width: 4px !important;
        }
        
        #initialContainer.dropzone-hover::before {
            content: "✓ Drop here to return item";
            opacity: 1 !important;
            color: #28a745;
            font-weight: bold;
        }
        
        #initialContainer .draggable {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
            position: relative !important;
            transform: none !important;
        }
        
        /* Ensure shake-wrapper is fully draggable */
        .shake-wrapper {
            cursor: grab;
            width: 100%;
            position: relative;
        }
        
        .shake-wrapper:active,
        .shake-wrapper .draggable.dragging {
            cursor: grabbing;
        }
        
        /* Make the entire shake-wrapper area draggable */
        .shake-wrapper .draggable {
            pointer-events: auto;
            touch-action: none;
            width: 100%;
            height: 100%;
        }
        
        .animate-drop {
            animation: dropIn 0.3s ease-out;
        }
        
        .animate-return {
            animation: returnToStart 0.3s ease-out;
        }
        
        .animate-move {
            animation: moveItem 0.3s ease-out;
        }
        
        .return-button {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 30px;
            height: 30px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .return-button:hover {
            background: #dc3545;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .return-button:active {
            transform: scale(0.95);
        }
        
        .drag-drop-feedback {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }
        
        .drag-drop-feedback.success {
            background-color: #28a745;
        }
        
        .drag-drop-feedback.info {
            background-color: #17a2b8;
        }
        
        .drag-drop-feedback.alert, .drag-drop-feedback.error {
            background-color: #dc3545;
            color: #fff !important;
        }
        
        @keyframes dropIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes returnToStart {
            from { transform: scale(1.2); opacity: 0.5; }
            to { transform: scale(1); opacity: 1; }
        }
        
        @keyframes moveItem {
            from { transform: translateY(-10px); opacity: 0.5; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    
    // Add styles to document
    $('<style>').text(style).appendTo('head');

    $('.mc-image_drag_drop').on('mc_question_ready', function() {
        Core.init(this);
    });
});
