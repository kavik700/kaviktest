import interact from 'interactjs'

let interactInstance = null;

function calculateNewPosition(target, x, y, width, height, parentRect) {
    const newX = Math.min(Math.max(0, x), parentRect.width - width);
    const newY = Math.min(Math.max(0, y), parentRect.height - height);
    return { x: newX, y: newY };
}

function updateElementPosition(target, x, y, width, height) {
    const answerType = jQuery('input[name="answerType"]:checked').val();
    
    target.style.transform = `translate(${x}px, ${y}px)`;
    target.setAttribute("data-x", x);
    target.setAttribute("data-y", y);

    switch(answerType) {
        case 'matrix_sort_answer':
            let textareas = document.querySelectorAll(`textarea[name="answerData[][sort_string]"]`);
            textareas.forEach((textarea) => {
                if (textarea.value === target.dataset.mc_identifier) {
                    jQuery(textarea).closest('tr').find('.wpProQuiz_matrix_answer').val(JSON.stringify({
                        x: x,
                        y: y,
                        width: width,
                        height: height
                    }));
                }
            });
            break;

        case 'single':
            jQuery('#speech_bubble_x').val(x);
            jQuery('#speech_bubble_y').val(y);
            jQuery('#speech_bubble_width').val(width);
            jQuery('#speech_bubble_height').val(height);
            break;
    }
}

function initializeInteract() {
    interactInstance = interact(".selectable-area")
    .draggable({
        onmove: function (event) {
            const target = event.target;
            const parentRect = target.parentElement.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();
        
            const x = (parseFloat(target.getAttribute("data-x")) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute("data-y")) || 0) + event.dy;
        
            const { x: newX, y: newY } = calculateNewPosition(target, x, y, targetRect.width, targetRect.height, parentRect);
            updateElementPosition(target, newX, newY, targetRect.width, targetRect.height);
        }
    });

    // Add resizable if enabled
    if (jQuery('#mc_enable_resize').is(':checked')) {
        interactInstance.resizable({
            edges: { left: true, right: true, bottom: true, top: true },
            onmove: function (event) {
                const target = event.target;
                const parentRect = target.parentElement.getBoundingClientRect();
                
                let x = parseFloat(target.getAttribute("data-x")) || 0;
                let y = parseFloat(target.getAttribute("data-y")) || 0;

                const newWidth = Math.min(event.rect.width, parentRect.width - x);
                const newHeight = Math.min(event.rect.height, parentRect.height - y);

                target.style.width = `${newWidth}px`;
                target.style.height = `${newHeight}px`;

                x += event.deltaRect.left;
                y += event.deltaRect.top;

                const { x: newX, y: newY } = calculateNewPosition(target, x, y, newWidth, newHeight, parentRect);
                updateElementPosition(target, newX, newY, newWidth, newHeight);
            }
        });
    }
}

// Initialize on page load
jQuery(document).ready(function() {
    // Initial setup
    initializeInteract();

    // Handle resize toggle
    jQuery('#mc_enable_resize').on('change', function() {
        const isResizeEnabled = jQuery(this).is(':checked');
        const container = jQuery('#parent-container');
        
        container.toggleClass('resize-disabled', !isResizeEnabled);
        
        if (interactInstance) {
            if (isResizeEnabled) {
                interactInstance.resizable({
                    edges: { left: true, right: true, bottom: true, top: true },
                    onmove: function (event) {
                        const target = event.target;
                        const parentRect = target.parentElement.getBoundingClientRect();
                        
                        let x = parseFloat(target.getAttribute("data-x")) || 0;
                        let y = parseFloat(target.getAttribute("data-y")) || 0;

                        const newWidth = Math.min(event.rect.width, parentRect.width - x);
                        const newHeight = Math.min(event.rect.height, parentRect.height - y);

                        target.style.width = `${newWidth}px`;
                        target.style.height = `${newHeight}px`;

                        x += event.deltaRect.left;
                        y += event.deltaRect.top;

                        const { x: newX, y: newY } = calculateNewPosition(target, x, y, newWidth, newHeight, parentRect);
                        updateElementPosition(target, newX, newY, newWidth, newHeight);
                    }
                });
            } else {
                interactInstance.resizable(false);
            }
        }
    });
});
