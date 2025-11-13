// WPProQuiz Answer Grouping Script
// Add this script after your existing jQuery library

jQuery(document).ready(function($) {
    let selectedAnswers = [];
    let nextGroupId = 1;
    let isInitialized = false; // Flag to prevent multiple initializations
    let isReordering = false; // Flag to prevent multiple rapid reordering calls
    let reorderTimeout = null; // Timeout for debouncing reorder operations
    
    const colors = [
        '#ef4444', '#10b981', '#f59e0b', 
        '#8b5cf6', '#f97316', '#84cc16', '#dc2626', '#059669'
    ];

    function getColorForGroup(groupId) {
        return colors[(groupId - 1) % colors.length];
    }

    // Check if Image Drag&Drop mode is selected
    function isImageDragDropMode() {
        const selectedMode = $('input[name="multiclass_question_type"]:checked').val();
        return selectedMode === 'image_drag_drop';
    }

    // Get current post ID from various sources
    function getCurrentPostId() {
        // Try to get from URL parameter first
        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('post');
        if (postId) {
            return parseInt(postId);
        }
        
        // Try to get from hidden input
        const postIdInput = $('#post_ID');
        if (postIdInput.length && postIdInput.val()) {
            return parseInt(postIdInput.val());
        }
        
        // Try to get from global variable
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            try {
                return wp.data.select('core/editor').getCurrentPostId();
            } catch (e) {
                // Block editor not available
            }
        }
        
        return null;
    }

    // Initialize grouping interface
    function initGroupingInterface() {
        // Check if Image Drag&Drop mode is selected
        if (!isImageDragDropMode()) {
            // Remove any existing grouping interface if mode changed
            if ($('#answer-grouping-controls').length > 0) {
                $('#answer-grouping-controls').remove();
                // Reset initialization flag
                isInitialized = false;
            }
            return;
        }

        // Check if already initialized or interface already exists
        if (isInitialized || $('#answer-grouping-controls').length > 0) {
            return;
        }

        // Check if we're on a matrix sort answer page
        if ($('.matrix_sort_answer .answerList').length === 0) {
            return;
        }

        // Add grouping controls before the answer list
        const groupingHTML = `
            <div id="answer-grouping-controls">
                <h4>Answer Grouping</h4>
                <div id="grouping-stats">
                    0 answers selected | 0 groups created
                </div>
                <div id="grouping-actions">
                    <div>
                        <strong id="selected-count">0 answers selected:</strong>
                        <span id="selected-list"></span>
                    </div>
                    <button type="button" id="group-selected-answers" class="button-secondary" disabled>
                        Group Selected
                    </button>
                    <button type="button" id="clear-selection" class="button-secondary">
                        Clear Selection
                    </button>
                </div>
                <div id="groups-summary">
                    <h5>Current Groups:</h5>
                    <div id="groups-list"></div>
                    <div class="group-save-notice">
                        <em>Groups will be saved when you update the post using the "Update" button above.</em>
                    </div>
                </div>
            </div>
        `;
        
        $('.matrix_sort_answer .answerList').before(groupingHTML);
        
        // Add selection indicators to each answer (only if not already added)
        $('.matrix_sort_answer .answerList li:not([data-answer-id])').each(function(index) {
            const $li = $(this);
            const answerId = index; // Use index as ID (starts from 0)
            
            $li.attr('data-answer-id', answerId);
            
            // Add selection overlay
            const selectionOverlay = `
                <div class="answer-selection-overlay">
                    <div>✓</div>
                </div>
            `;
            
            // Make li relative for overlay positioning
            $li.addClass('answer-item');
            $li.append(selectionOverlay);
            
            // Add group indicator area
            const groupIndicator = `
                <div class="group-indicator">
                    <span></span>
                </div>
            `;
            
            $li.append(groupIndicator);
        });

        // Set initialization flag
        isInitialized = true;
    }

    // Toggle answer selection
    function toggleAnswerSelection(answerId) {
        // Only allow selection if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${answerId}"]`);
        
        if (selectedAnswers.includes(answerId)) {
            // Deselect
            selectedAnswers = selectedAnswers.filter(id => id !== answerId);
            $answer.find('.answer-selection-overlay').hide();
            $answer.removeClass('selected-answer');
        } else {
            // Select
            selectedAnswers.push(answerId);
            $answer.find('.answer-selection-overlay').show();
            $answer.addClass('selected-answer');
        }
        
        updateGroupingControls();
    }

    // Update grouping controls UI
    function updateGroupingControls() {
        // Only update if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const totalAnswers = $('.matrix_sort_answer .answerList li').length;
        const groupedAnswers = $('.matrix_sort_answer .answerList li[data-group-id]').length;
        const totalGroups = new Set($('.matrix_sort_answer .answerList li[data-group-id]').map(function() {
            return $(this).attr('data-group-id');
        }).get()).size;
        
        $('#grouping-stats').text(`${selectedAnswers.length} answers selected | ${totalGroups} groups created (${groupedAnswers}/${totalAnswers} answers grouped)`);
        
        if (selectedAnswers.length > 0) {
            $('#grouping-actions').show();
            $('#selected-count').text(`${selectedAnswers.length} answers selected:`);
            
            const selectedTexts = selectedAnswers.map(id => {
                const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${id}"]`);
                const criterionText = $answer.find('.wpProQuiz_matrix_answer').val();
                
                // Try to extract readable text from JSON or use first few chars
                let displayText = '';
                try {
                    const parsed = JSON.parse(criterionText);
                    displayText = `Answer ${id}`;
                } catch {
                    displayText = criterionText.substring(0, 20) + (criterionText.length > 20 ? '...' : '');
                }
                return displayText;
            });
            
            $('#selected-list').text(selectedTexts.join(', '));
            $('#group-selected-answers').prop('disabled', selectedAnswers.length < 2);
        } else {
            $('#grouping-actions').hide();
        }
        
        updateGroupsSummary();
        updateHiddenGroupData();
    }

    // Update groups summary
    function updateGroupsSummary() {
        // Only update if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const groupIds = new Set($('.matrix_sort_answer .answerList li[data-group-id]').map(function() {
            return $(this).attr('data-group-id');
        }).get());
        
        if (groupIds.size > 0) {
            $('#groups-summary').show();
            const $groupsList = $('#groups-list');
            $groupsList.empty();
            
            groupIds.forEach(groupId => {
                const $groupAnswers = $(`.matrix_sort_answer .answerList li[data-group-id="${groupId}"]`);
                const color = getColorForGroup(parseInt(groupId));
                const answerIds = $groupAnswers.map(function() {
                    return $(this).attr('data-answer-id');
                }).get();
                
                const groupItem = `
                    <div class="group-item">
                        <div class="group-color-indicator" data-group-id="${groupId}"></div>
                        <div class="group-info">
                            <strong>Group ${groupId}</strong> (${$groupAnswers.length} answers)
                            <div class="group-details">
                                Answer IDs: ${answerIds.join(', ')}
                            </div>
                        </div>
                        <button type="button" class="button-secondary ungroup-all ungroup-button" data-group-id="${groupId}">
                            Ungroup
                        </button>
                    </div>
                `;
                
                $groupsList.append(groupItem);
            });
        } else {
            $('#groups-summary').hide();
        }
    }

    // Update hidden input with current group data
    function updateHiddenGroupData() {
        // Only update if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const groups = [];
        const groupIds = new Set($('.matrix_sort_answer .answerList li[data-group-id]').map(function() {
            return $(this).attr('data-group-id');
        }).get());

        groupIds.forEach(groupId => {
            const $groupAnswers = $(`.matrix_sort_answer .answerList li[data-group-id="${groupId}"]`);
            const answerIds = $groupAnswers.map(function() {
                return parseInt($(this).attr('data-answer-id'));
            }).get();

            groups.push({
                groupId: parseInt(groupId),
                answerIds: answerIds
            });
        });

        // Store as JSON in hidden input - ensure it exists first
        let $hiddenInput = $('#drag-drop-groups-data');
        if ($hiddenInput.length === 0) {
            // Create the hidden input if it doesn't exist
            $hiddenInput = $('<input type="hidden" id="drag-drop-groups-data" name="drag_drop_groups_data" value="">');
            $('.matrix_sort_answer').append($hiddenInput);
        }
        
        $hiddenInput.val(JSON.stringify(groups));
    }

    // Group selected answers
    function groupSelectedAnswers() {
        // Only allow grouping if Image Drag&Drop mode is active
        if (!isImageDragDropMode() || selectedAnswers.length < 2) {
            return;
        }
        
        const groupId = nextGroupId;
        const color = getColorForGroup(groupId);
        
        selectedAnswers.forEach(answerId => {
            const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${answerId}"]`);
            $answer.attr('data-group-id', groupId);
            
            // Show and update group indicator
            const $indicator = $answer.find('.group-indicator');
            $indicator.show();
            $indicator.find('span').text(`G${groupId}`);
            
            // Hide selection overlay
            $answer.find('.answer-selection-overlay').hide();
        });
        
        selectedAnswers = [];
        nextGroupId++;
        updateGroupingControls();
    }

    // Ungroup answers
    function ungroupAnswers(groupId) {
        // Only allow ungrouping if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const $groupAnswers = $(`.matrix_sort_answer .answerList li[data-group-id="${groupId}"]`);
        
        $groupAnswers.each(function() {
            const $answer = $(this);
            $answer.removeAttr('data-group-id');
            $answer.find('.group-indicator').hide();
        });
        
        updateGroupingControls();
    }

    // Event handlers
    $(document).on('click', '.matrix_sort_answer .answerList li', function(e) {
        // Don't trigger on form elements or buttons
        if ($(e.target).is('input, textarea, button, a, label')) {
            return;
        }
        
        e.preventDefault();
        e.stopPropagation();
        
        const answerId = parseInt($(this).attr('data-answer-id'));
        toggleAnswerSelection(answerId);
    });

    $(document).on('click', '#group-selected-answers', function() {
        groupSelectedAnswers();
    });

    $(document).on('click', '#clear-selection', function() {
        // Only allow clearing if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        selectedAnswers.forEach(answerId => {
            const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${answerId}"]`);
            $answer.find('.answer-selection-overlay').hide();
            $answer.removeClass('selected-answer');
        });
        selectedAnswers = [];
        updateGroupingControls();
    });

    $(document).on('click', '.ungroup-all', function() {
        const groupId = $(this).attr('data-group-id');
        ungroupAnswers(groupId);
    });

    // Handle question type changes
    $(document).on('change', 'input[name="multiclass_question_type"]', function() {
        // Clear any existing selection when mode changes
        selectedAnswers.forEach(answerId => {
            const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${answerId}"]`);
            $answer.find('.answer-selection-overlay').hide();
            $answer.removeClass('selected-answer');
        });
        selectedAnswers = [];
        
        // If switching away from Image Drag&Drop mode, remove all group data
        if (!isImageDragDropMode()) {
            // Remove data-group-id from all answers
            $('.matrix_sort_answer .answerList li[data-group-id]').each(function() {
                const $answer = $(this);
                $answer.removeAttr('data-group-id');
                $answer.find('.group-indicator').hide();
            });
            
            // Clear the hidden input data
            let $hiddenInput = $('#drag-drop-groups-data');
            if ($hiddenInput.length > 0) {
                $hiddenInput.val('');
            }
            
            console.log('Question type changed - removed all group data from answers');
        }
        
        // Reinitialize the interface based on new mode
        setTimeout(function() {
            initGroupingInterface();
        }, 100);
    });

    // Handle form submission to ensure group data is included
    $(document).on('submit', '#post', function() {
        updateHiddenGroupData();
    });

    // Also update on any group changes
    $(document).on('change', '.matrix_sort_answer .answerList', function() {
        updateHiddenGroupData();
    });

    $('.matrix_sort_answer .addAnswer').on('click', function(e) {
        setTimeout(function() {
            const $newRow = $('.matrix_sort_answer .answerList li').last();
        
            if ($newRow.length > 0) {
                $newRow.removeAttr('data-group-id');
                
                const totalAnswers = $('.matrix_sort_answer .answerList li').length;
                const answerId = totalAnswers - 1; // Subtract 1 to start from 0

                $newRow.find('.group-indicator span').html('');
                $newRow.find('.group-indicator').hide();
                $newRow.find('.answer-selection-overlay').hide();
                
                $newRow.attr('data-answer-id', answerId);
                
                updateGroupingControls();
            }
        }, 100);
    });

    // Handle dynamic content - reinitialize when new answers are added
    function reinitializeGrouping() {
        // Only reinitialize if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        // Re-scan for new answers and add handlers
        $('.matrix_sort_answer .answerList li:not([data-answer-id])').each(function(index) {
            const $li = $(this);
            const totalAnswers = $('.matrix_sort_answer .answerList li').length;
            const answerId = totalAnswers - $('.matrix_sort_answer .answerList li:not([data-answer-id])').length + index - 1; // Subtract 1 to start from 0
            
            $li.attr('data-answer-id', answerId);
            
            // Add selection overlay and group indicator (same as init)
            if (!$li.find('.answer-selection-overlay').length) {
                const selectionOverlay = `
                    <div class="answer-selection-overlay">
                        <div>✓</div>
                    </div>
                `;
                
                const groupIndicator = `
                    <div class="group-indicator">
                        <span></span>
                    </div>
                `;
                
                $li.addClass('answer-item');
                $li.append(selectionOverlay);
                $li.append(groupIndicator);
            }
        });
        
        updateGroupingControls();
    }

    // Handle answer reordering - update answer IDs when order changes
    function handleAnswerReordering() {
        // Only handle reordering if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        // Prevent multiple rapid calls
        if (isReordering) {
            return;
        }
        
        isReordering = true;
        
        // Clear any existing timeout
        if (reorderTimeout) {
            clearTimeout(reorderTimeout);
        }
        
        // Debounce the operation
        reorderTimeout = setTimeout(function() {
            // Get all answer list items in their current order
            const $answerItems = $('.matrix_sort_answer .answerList li');
            
            // Store current group assignments before reordering
            const groupAssignments = {};
            $answerItems.each(function() {
                const $item = $(this);
                const currentAnswerId = $item.attr('data-answer-id');
                const groupId = $item.attr('data-group-id');
                
                if (currentAnswerId && groupId) {
                    groupAssignments[currentAnswerId] = groupId;
                }
            });
            
            // Check if reordering is actually needed
            let needsReordering = false;
            $answerItems.each(function(index) {
                const $item = $(this);
                const currentId = parseInt($item.attr('data-answer-id'));
                const expectedId = index; // Start from 0
                
                if (currentId !== expectedId) {
                    needsReordering = true;
                    return false; // Break the loop
                }
            });
            
            if (!needsReordering) {
                isReordering = false;
                return;
            }
            
            // Update answer IDs based on new order
            $answerItems.each(function(index) {
                const $item = $(this);
                const newAnswerId = index; // Start from 0
                const oldAnswerId = $item.attr('data-answer-id');
                
                // Update the answer ID
                $item.attr('data-answer-id', newAnswerId);
                
                // Restore group assignment if it existed
                if (oldAnswerId && groupAssignments[oldAnswerId]) {
                    $item.attr('data-group-id', groupAssignments[oldAnswerId]);
                    
                    // Update group indicator text
                    const $indicator = $item.find('.group-indicator');
                    $indicator.show();
                    $indicator.find('span').text(`G${groupAssignments[oldAnswerId]}`);
                }
            });
            
            // Update selected answers array with new IDs
            selectedAnswers = selectedAnswers.map(oldId => {
                // Find the item that had this old ID and get its new ID
                const $item = $answerItems.filter(`[data-answer-id]`).eq(oldId);
                return parseInt($item.attr('data-answer-id'));
            }).filter(id => !isNaN(id)); // Remove any invalid IDs
            
            // Update the UI
            updateGroupingControls();
            
            console.log('Answer order changed - updated answer IDs');
            
            // Reset the flag after a delay
            setTimeout(function() {
                isReordering = false;
            }, 500);
            
        }, 200); // Debounce delay
    }

    // Ensure answer IDs are sequential and handle any gaps
    function ensureSequentialAnswerIds() {
        // Only ensure sequential IDs if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        // Prevent multiple rapid calls
        if (isReordering) {
            return;
        }
        
        const $answerItems = $('.matrix_sort_answer .answerList li');
        let needsUpdate = false;
        
        // Check if IDs are sequential
        $answerItems.each(function(index) {
            const $item = $(this);
            const currentId = parseInt($item.attr('data-answer-id'));
            const expectedId = index; // Start from 0
            
            if (currentId !== expectedId) {
                needsUpdate = true;
                return false; // Break the loop
            }
        });
        
        if (needsUpdate) {
            console.log('Detected non-sequential answer IDs - fixing...');
            handleAnswerReordering();
        }
    }

    // Handle answer removal and renumbering
    function handleAnswerRemoval() {
        // Only handle removal if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        const $answerItems = $('.matrix_sort_answer .answerList li');
        const currentCount = $answerItems.length;
        
        // Check if any answers are missing data-answer-id
        const missingIds = $answerItems.filter(':not([data-answer-id])').length;
        
        if (missingIds > 0) {
            console.log('Detected answers without IDs - reinitializing...');
            reinitializeGrouping();
        } else {
            // Ensure IDs are sequential
            ensureSequentialAnswerIds();
        }
    }

    // Enhanced mutation observer to detect both additions/removals and reordering
    const observer = new MutationObserver(function(mutations) {
        // Only run if interface is initialized and Image Drag&Drop mode is active
        if (!isInitialized || !isImageDragDropMode()) {
            return;
        }
        
        let needsReinitialization = false;
        let needsReordering = false;
        let needsRemovalHandling = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Check if nodes were added or removed
                if (mutation.addedNodes.length > 0 || mutation.removedNodes.length > 0) {
                    if (mutation.removedNodes.length > 0) {
                        needsRemovalHandling = true;
                    } else {
                        needsReinitialization = true;
                    }
                }
            } else if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                // Only trigger reordering for specific style changes that indicate drag operations
                const target = mutation.target;
                if (target.style && (
                    target.style.transform || 
                    target.style.top || 
                    target.style.left ||
                    target.style.position === 'relative' ||
                    target.style.position === 'absolute'
                )) {
                    // Add additional check to ensure this is actually a drag operation
                    const $target = $(target);
                    if ($target.closest('.matrix_sort_answer .answerList li').length > 0) {
                        needsReordering = true;
                    }
                }
            }
        });
        
        // Handle removal first (if detected)
        if (needsRemovalHandling) {
            setTimeout(function() {
                handleAnswerRemoval();
            }, 100);
        }
        
        // Handle reordering (if detected) - with additional debouncing
        if (needsReordering && !isReordering) {
            setTimeout(function() {
                handleAnswerReordering();
            }, 100);
        }
        
        // Handle additions
        if (needsReinitialization) {
            setTimeout(function() {
                reinitializeGrouping();
            }, 100);
        }
    });

    // Start observing with enhanced configuration
    setTimeout(function() {
        const answerList = document.querySelector('.matrix_sort_answer .answerList');
        if (answerList && isInitialized && isImageDragDropMode()) {
            observer.observe(answerList, { 
                childList: true, 
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        }
    }, 200);

    // Additional observer for the entire answer list container to catch drag-and-drop events
    const containerObserver = new MutationObserver(function(mutations) {
        if (!isInitialized || !isImageDragDropMode()) {
            return;
        }
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Check if this is a reordering (same number of children, just different order)
                const $answerList = $('.matrix_sort_answer .answerList');
                const currentCount = $answerList.children('li').length;
                
                // If the count is the same but the order might have changed, handle reordering
                if (currentCount > 0) {
                    setTimeout(function() {
                        handleAnswerReordering();
                    }, 50);
                }
            }
        });
    });

    // Start observing the container
    setTimeout(function() {
        const answerContainer = document.querySelector('.matrix_sort_answer');
        if (answerContainer && isInitialized && isImageDragDropMode()) {
            containerObserver.observe(answerContainer, { 
                childList: true, 
                subtree: true 
            });
        }
    }, 200);

    // Listen for LearnDash-specific drag events
    $(document).on('sortupdate', '.matrix_sort_answer .answerList', function(event, ui) {
        if (isInitialized && !isReordering && isImageDragDropMode()) {
            setTimeout(function() {
                handleAnswerReordering();
            }, 100);
        }
    });

    // Listen for jQuery UI sortable events
    $(document).on('sortstop', '.matrix_sort_answer .answerList', function(event, ui) {
        if (isInitialized && !isReordering && isImageDragDropMode()) {
            setTimeout(function() {
                handleAnswerReordering();
            }, 100);
        }
    });

    // Listen for various drag-and-drop events that might be used by LearnDash
    $(document).on('dragend drop', '.matrix_sort_answer .answerList li', function(event) {
        if (isInitialized && !isReordering && isImageDragDropMode()) {
            setTimeout(function() {
                handleAnswerReordering();
            }, 150);
        }
    });

    // Listen for mouseup events on the answer list (fallback for drag detection)
    $(document).on('mouseup', '.matrix_sort_answer .answerList', function(event) {
        if (isInitialized && !isReordering && isImageDragDropMode()) {
            // Only trigger if this might be a drag operation
            if (event.target.closest('li')) {
                setTimeout(function() {
                    ensureSequentialAnswerIds();
                }, 200);
            }
        }
    });

    // Listen for touch events (for mobile drag-and-drop)
    $(document).on('touchend', '.matrix_sort_answer .answerList', function(event) {
        if (isInitialized && !isReordering && isImageDragDropMode()) {
            setTimeout(function() {
                handleAnswerReordering();
            }, 200);
        }
    });

    // Listen for any changes to the answer list structure
    $(document).on('DOMNodeInserted DOMNodeRemoved', '.matrix_sort_answer .answerList', function(event) {
        if (isInitialized && isImageDragDropMode()) {
            setTimeout(function() {
                if (event.type === 'DOMNodeRemoved') {
                    handleAnswerRemoval();
                } else {
                    reinitializeGrouping();
                }
            }, 100);
        }
    });

    // Initialize the grouping interface
    // Add a small delay to ensure DOM is fully loaded
    setTimeout(function() {
        initGroupingInterface();
        
        // Only proceed if interface was successfully initialized and Image Drag&Drop mode is active
        if (isInitialized && isImageDragDropMode()) {
            // Load existing groups from server-side data (if available)
            loadExistingGroups();
            
            // Set up periodic check for answer ID consistency
            setInterval(function() {
                if (isInitialized && !isReordering && isImageDragDropMode() && $('.matrix_sort_answer .answerList li').length > 0) {
                    // Only check if we haven't been reordering recently
                    ensureSequentialAnswerIds();
                }
            }, 10000); // Check every 10 seconds instead of 5
        }
    }, 100);

    // Load groups that were rendered server-side
    function loadExistingGroups() {
        // Only load groups if Image Drag&Drop mode is active
        if (!isImageDragDropMode()) {
            return;
        }

        // Check if groups data is already present in the hidden input
        let $hiddenInput = $('#drag-drop-groups-data');
        if ($hiddenInput.length === 0) {
            // If the hidden input doesn't exist, create it with empty data
            $hiddenInput = $('<input type="hidden" id="drag-drop-groups-data" name="drag_drop_groups_data" value="">');
            $('.matrix_sort_answer').append($hiddenInput);
        }
        
        const existingGroupsData = $hiddenInput.val();
        
        if (existingGroupsData) {
            try {
                const groups = JSON.parse(existingGroupsData);
                
                if (Array.isArray(groups) && groups.length > 0) {
                    // Apply existing groups
                    groups.forEach(group => {
                        // Validate group structure
                        if (group && typeof group.groupId === 'number' && Array.isArray(group.answerIds)) {
                            // Set the next group ID to avoid conflicts
                            if (group.groupId >= nextGroupId) {
                                nextGroupId = group.groupId + 1;
                            }
                            
                            // Apply group styling to answers
                            group.answerIds.forEach(answerId => {
                                const $answer = $(`.matrix_sort_answer .answerList li[data-answer-id="${answerId}"]`);
                                if ($answer.length) {
                                    $answer.attr('data-group-id', group.groupId);
                                    
                                    // Show and update group indicator
                                    const $indicator = $answer.find('.group-indicator');
                                    $indicator.show();
                                    $indicator.find('span').text(`G${group.groupId}`);
                                }
                            });
                        } else {
                            console.warn('Invalid group structure:', group);
                        }
                    });
                    
                    // Update the UI
                    updateGroupingControls();
                    console.log('Loaded', groups.length, 'existing groups from server-side data');
                }
            } catch (e) {
                console.error('Error parsing existing groups data:', e);
            }
        }
    }
});