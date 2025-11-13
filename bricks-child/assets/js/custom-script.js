console.log('Working JS!');

// Add line below the accordion ----------------------------------------------------
jQuery(document).ready(function($) {
    jQuery(window).on("load", function() {
        // Select the main consent container
        const mainContainer = document.querySelector('.cky-consent-container');

        // Check if it does NOT have the class 'cky-hide'
        if (mainContainer && !mainContainer.classList.contains('cky-hide')) {
          // Find the overlay div that has 'cky-hide'
          const overlay = document.querySelector('.cky-overlay.cky-hide');
          
          // Remove the 'cky-hide' class from the overlay
          if (overlay) {
            overlay.classList.remove('cky-hide');
          }
        }
        jQuery(".accordion-content-wrapper ul").each(function() {
            var listItems = jQuery(this).find("li");
            // Check the count of list items
            if (listItems.length === 7) {
                listItems.eq(4).attr("style", "border-bottom: 1px solid rgba(26, 58, 39, 0.12) !important;");
            } else if (listItems.length === 2) {
                // If there are exactly 2 list items, remove the border-bottom style from the first one
                listItems.eq(0).css("border-bottom", "none");
            }
        });
    });
});

// JS start ----------------------------------------------------
jQuery(document).ready(function () {
    jQuery(".owl-next, .owl-prev").each(function () {
        jQuery(this).removeAttr("role"); // Remove incorrect role
        if (jQuery(this).hasClass("owl-next")) {
            jQuery(this).attr("aria-label", "Next slide");
        } else {
            jQuery(this).attr("aria-label", "Previous slide");
        }
    });

	// For Custom Captcha ----------------------------------------------------
	generateCaptcha();
	jQuery('#refresh-captcha').click(function () {
		generateCaptcha();
	});
});

jQuery(document).ready(function ($) {
    document.addEventListener("touchstart", function (event) {}, { passive: true });
});


// Scroll to payment while saferpay failure ----------------------------------------------------
jQuery(document).ready(function ($) {
    // Function to reset checkout fields visibility
    function resetCheckoutFields() {
        if ($('#billing_email').val() !== '') {
            $(".woocommerce-additional-fields__field-wrapper").show();
            $("#billing_email_field, .proceed-email-address").hide();
            $(".eamil_value").html($('#billing_email').val() + '<a href="javascript:void(0);" class="email-edit">Edit</a>');
        } else {
            $("#billing_email_field, .proceed-email-address").show();
            $(".woocommerce-additional-fields__field-wrapper").hide();
            $(".eamil_value").text('');
        }
    }

    // Function to smooth scroll to additional fields
    function scrollToAdditionalFields() {
        var $target = $('.woocommerce-additional-fields__field-wrapper');
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 30
            }, 800);
        }
    }

    // Run again whenever checkout is refreshed
    $(document.body).on('updated_checkout', function () {
        if ($('.woocommerce-error:contains("Saferpay")').length > 0) {
            resetCheckoutFields();
            setTimeout(function () {
                scrollToAdditionalFields();
            }, 500);
        }
    });

    // Also handle directly on page load (in case error is already there)
    if ($('.woocommerce-error:contains("Saferpay")').length > 0) {
        resetCheckoutFields();
        setTimeout(function () {
            scrollToAdditionalFields();
        }, 800);
    }
});


/*jQuery(document).ready(function ($) {
    // Function to reset checkout fields visibility
    function resetCheckoutFields() {
        if ($('#billing_email').val() !== '') {
            $(".woocommerce-additional-fields__field-wrapper").show();
            $("#billing_email_field, .proceed-email-address").hide();
            $(".eamil_value").html($('#billing_email').val() + '<a href="javascript:void(0);" class="email-edit">Edit</a>');
        } else {
            $("#billing_email_field, .proceed-email-address").show();
            $(".woocommerce-additional-fields__field-wrapper").hide();
            $(".eamil_value").text('');
        }
    }

    // Function to smooth scroll to additional fields
    function scrollToAdditionalFields() {
        var $target = $('.woocommerce-additional-fields__field-wrapper');
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 30
            }, 800);
        }
    }

    // Run on page load
    // resetCheckoutFields();

    // Run again whenever checkout is refreshed
    $(document.body).on('updated_checkout', function () {

        // If error box exists, scroll after refresh
        if ($('.woocommerce-error:contains("Saferpay")').length > 0) {
            resetCheckoutFieldsoutFields();
            setTimeout(function () {
                scrollToAdditionalFields();
            }, 500);
        }
    });

    // Also handle directly on page load (in case error is already there)
    if ($('.woocommerce-error:contains("Saferpay")').length > 0) {
        resetCheckoutFieldsoutFields();
        setTimeout(function () {
            scrollToAdditionalFields();
        }, 800);
    }
});
*/

// Scroll to order review intead of cart redirect ----------------------------------------------------
// document.addEventListener('DOMContentLoaded', function() {
//     // Select the "Warenkorb anzeigen" button
//     const viewCartButton = document.querySelector('.button.wc-forward');

//     // Add a click event listener
//     if (viewCartButton) {
//         viewCartButton.addEventListener('click', function(event) {
//             // Prevent the default action (redirecting to the cart page)
//             event.preventDefault();

//             // Scroll to the order review section
//             const orderReviewSection = document.getElementById('order_review');
//             const customerDetailsSection = document.getElementById('customer_details');
//             if (orderReviewSection) {
//                 orderReviewSection.scrollIntoView({ behavior: 'smooth' });
//                 // Add spacing before scrolling
//                 orderReviewSection.style.scrollTop = '100px';
//                 customerDetailsSection.style.scrollTop = '100px';
//             }
//         });
//     }
// });


// For Custom Captcha Function ----------------------------------------------------
function generateCaptcha() {
	var digits = '0123456789';
	var letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var chars = digits + letters;
	var captchaLength = 6
	var captcha = ''

	// Ensure at least one digit
	captcha += digits[Math.floor(Math.random() * digits.length)];

	// Ensure at least one letter
	captcha += letters[Math.floor(Math.random() * letters.length)];

	// Fill the remaining characters randomly from the combined set
	for (var i = 2; i < captchaLength; i++) {
		var randomChar = chars[Math.floor(Math.random() * chars.length)];
		captcha += randomChar;
	}

	// Shuffle the captcha string to make the digit and letter positions random
	captcha = captcha.split('').sort(function () { return 0.5 - Math.random() }).join('');
	
	if (jQuery('#custom-captcha-code').length > 0) 
	{
		document.getElementById('custom-captcha-code').value = captcha;
		document.getElementById('custom-captcha-display').innerText = captcha;
	}
}
// jQuery(document).ready(function () {
//     function isIOS() {
//         return /iPad|iPhone|iPod/.test(navigator.userAgent);
//     }

//     if (isIOS()) {
//         let williamSlide = jQuery('.owl-carousel img[alt="William Nüesch"]').closest('.owl-item');
//         williamSlide.prependTo('.owl-stage');

//         // Re-initialize Owl Carousel to reflect the change
//         jQuery('.owl-carousel').trigger('destroy.owl.carousel').owlCarousel({
//             loop: true,
//             margin: 10,
//             nav: true,
//             items: 1
//         });
//     }
// });

// jQuery(document).ready(function ($) {
//     $(".teams-card").each(function () {
//         if ($(this).find("img").attr("alt") === "William Nüesch") {
//             $(this).addClass("show-on-responsive");
//         }
//     });
// });



// Validations for contact form ----------------------------------------------------
jQuery('.wpcf7-form').validate({
    ignore: [],
    rules: {
        "your-name": {
            required: true,
        },
        "last-name": {
            required: true,
        },
        "tel-790": {
            required: true,
            // digits: true,
            minlength: 9,
            maxlength: 13,
        },
        "your-email": {
            required: true,
            email: true,
            //isemail : true,
        },
        "your-message": {
            required: true,
        }
    },
    messages: {
        "your-name": {
            required: "Bitte geben Sie Ihren Vornamen ein.",
        },
        "last-name": {
            required: "Bitte geben Sie Ihren Nachnamen ein.",
        },
        "tel-790": {
            required: "Bitte geben Sie Ihre Telefonnummer ein.",
            // digits: "Bitte geben Sie eine gültige Telefonnummer ein.",
            minlength: "Bitte geben Sie eine gültige Telefonnummer ein.",
            maxlength: "Bitte geben Sie eine gültige Telefonnummer ein.",
        },
        "your-email": {
            required: "Bitte geben Sie Ihre E-Mail-Adresse ein.",
            email: "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
            //isemail : 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
        },
        "your-message": {
            required: "Bitte geben Sie Ihre Nachricht ein.",
        }
    }

});


jQuery(document).ready(function ($) {
    const $header = $('header');
    const $childElement = $('#brxe-ulabhx');

    function handleSticky() {
        const screenWidth = $(window).width();
        const headerHeight = $header.outerHeight() + 100;
        const scrollTop = $(window).scrollTop();

        if (screenWidth > 991) {
            if (scrollTop > headerHeight) {
                $childElement.addClass('sticky_form');
            } else {
                $childElement.removeClass('sticky_form');
            }
        } else {            
            $childElement.removeClass('sticky_form');
        }
    }
    
    handleSticky();
    
    $(window).scroll(function () {
        handleSticky();
    });

    $(window).resize(function () {
        handleSticky();
    });


    // Function to check if the user is in the #brxe-zxjfuz div
    function toggleStickyClass() {
        var priceDiv = $('#brxe-zxjfuz');
        var stickyDiv = $('#brxe-ulabhx');

        if (priceDiv.length && stickyDiv.length) {
            var priceOffset = priceDiv.offset().top;
            var priceHeight = priceDiv.outerHeight();
            var scrollPosition = $(window).scrollTop();
            var windowHeight = $(window).height();

            // Check if the #brxe-price div is in the viewport
            if (scrollPosition + windowHeight > priceOffset) {
                stickyDiv.addClass('hide-sticky-bar');
            } else {
                stickyDiv.removeClass('hide-sticky-bar');
            }
        }
    }

    // Trigger the toggle function on scroll and resize
    $(window).on('scroll resize', function () {
        toggleStickyClass();
    });

    // Initial check on page load
    toggleStickyClass();

    // $("#brxe-ulabhx .form-group .bricks-button").on("click", function(event) {
    //     event.preventDefault(); // Prevent default anchor behavior
    //     var target = $("#brxe-xilfys"); // Get the target section ID
    //     if ($(target).length) {
    //         $("html, body").animate({
    //             scrollTop: $(target).offset().top - 30
    //         }, 800); // Adjust duration (800ms for smooth scrolling)
    //     }
    // });

    $(".sidebar_menu ul li a").not(":last").on("click", function(event) {
        // Check if the clicked link has the 'profile-course-sidebar' ID
        if ($(this).attr("id") !== "profile-course-sidebar") {
            event.preventDefault(); // Prevent default anchor behavior
            var target = $(this).attr("href"); // Get the target section ID
            $(".sidebar_menu ul li a").removeClass("is-active"); 
            $(this).addClass("is-active"); 
            if ($(target).length) {
                $("html, body").animate({
                    scrollTop: $(target).offset().top - 30
                }, 800); // Adjust duration (800ms for smooth scrolling)
            }
        }
    });


    // $(".sidebar_menu ul li a:not(:last-child)").on("click", function(event) {
    //     event.preventDefault(); // Prevent default anchor behavior
    //     var target = $(this).attr("href"); // Get the target section ID
    //     $(".sidebar_menu ul li a").removeClass("is-active"); 
    //     $(this).addClass("is-active"); 
    //     if ($(target).length) {
    //         $("html, body").animate({
    //             scrollTop: $(target).offset().top - 30
    //         }, 800); // Adjust duration (800ms for smooth scrolling)
    //     }
    // });


    $("#brxe-zyzhyq").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-aupyvr").on("click", function(e) {
        e.preventDefault();
        $("#brxe-sfgcodsdas").hide();
    });
    $("#brxe-02dd76").on("click", function(e) {
        e.preventDefault();
        $("#brxe-618028sdas").hide();
    });

    $("#brxe-yjzvfi").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-zyzhyqsda").on("click", function(e) {
        e.preventDefault();

        var targetSection = $('#brxe-fhqalnds');
        if (targetSection.length) {
            // Dynamically calculate offset
            var headerHeight = $('header').outerHeight() || 0; // adjust selector if needed

            // Add extra space for mobile view
            var extraOffset = window.innerWidth < 767 ? 240 : 30; 

            $('html, body').animate({
                scrollTop: targetSection.offset().top - (headerHeight + extraOffset)
            }, 800);
        }
    });

    $("#brxe-dfpdpx").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-edaowf .form-group .bricks-button").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-xilfys'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 100 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-drlgdq .offline_courses_card .brxe-button").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-440646'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $(".brxe-block .table-accordion .table-content .brxe-button").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-440646'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    // $(".ims-offline-course-tabbing-wrapper .card_body .brxe-button").on("click", function(e) {
    //     e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

    //     var targetSection = $('#brxe-440646'); // Replace with your actual section ID
    //     if (targetSection.length) {
    //         $('html, body').animate({
    //             scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
    //         }, 800); // Smooth scrolling duration (800ms)
    //     }
    // });

    $("#brxe-eaqryk #brxe-nrcjdt").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-rnkcyf'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-xhglyx #brxe-eaqryk #brxe-nrcjdt").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-be46cc'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-be46cc .brxe-block .course-tbl .brxe-button").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-440646'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-euaqzu .course-tbl .btn-action .brxe-button").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-bcb55a'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-04c278").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-cc0e72'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });
    
    $("#brxe-494511").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-440646'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    $("#brxe-hvsoxb").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#brxe-cc1774'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });

    // Scroll to order review intead of cart redirect ----------------------------------------------------
    $(".woocommerce-message .button.wc-forward").on("click", function(e) {
        e.preventDefault(); // Prevent the default behavior (e.g., page reload or link navigation)

        var targetSection = $('#order_review'); // Replace with your actual section ID
        if (targetSection.length) {
            $('html, body').animate({
                scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
            }, 800); // Smooth scrolling duration (800ms)
        }
    });
});

// In your Javascript (external .js resource or <script> tag)
jQuery(document).ready(function() {
    jQuery('.select2_dropdown').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('#brxe-rlnatk select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('#brxe-kyqcrx select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('.get_in_touch_form select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });    
    jQuery('.banner_sticky_form select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('.form_select select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('.common_filter_form select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });
    jQuery('.common_filter_forms select').select2({
        width: '100%',
        minimumResultsForSearch: Infinity,
    });

    // Target the specific buttons and update their styles
    jQuery('.mo-openid-app-icons .mo_btn').css({
        'width': '100%',
        // 'margin-left': '0', // Remove left margin for alignment
    });


    jQuery('.errors.email_err').each(function () {
        let parentP = jQuery(this).prev('p'); // Find the preceding <p> tag
        if (parentP.length) {
            jQuery(this).appendTo(parentP.find('.woocommerce-input-wrapper')); // Append the error div after the span inside <p>
        }
    });

});


// Create a MutationObserver to monitor changes to the DOM
const observer = new MutationObserver(function(mutationsList, observer) {
    mutationsList.forEach(function(mutation) {
        if (mutation.type === 'attributes') {
            const imgElement = jQuery(mutation.target);

            // Get current src and srcset attributes
            let src = imgElement.attr('src');
            let srcset = imgElement.attr('srcset');

            // Check if src contains '-150x150' and update it
            if (src && src.includes('-150x150')) {
                let updatedSrc = src.replace('-150x150', '');
                imgElement.attr('src', updatedSrc);
            }

            // Check if src contains '-150x150' and update it
            if (src && src.includes('-300x169')) {
                let updatedSrc = src.replace('-300x169', '');
                imgElement.attr('src', updatedSrc);
            }

            // Check if src contains '-150x150' and update it
            if (src && src.includes('-300x198')) {
                let updatedSrc = src.replace('-300x198', '');
                imgElement.attr('src', updatedSrc);
            }

            // Check if src contains '-150x150' and update it
            if (src && src.includes('-300x200')) {
                let updatedSrc = src.replace('-300x200', '');
                imgElement.attr('src', updatedSrc);
            }

            // Check if src contains '-150x150' and update it
            if (src && src.includes('-32x32')) {
                let updatedSrc = src.replace('-32x32', '');
                imgElement.attr('src', updatedSrc);
            }

            // Check if srcset contains '-150x150' and update it
            if (srcset && srcset.includes('-64x64')) {
                let updatedsrcset = srcset.replace('-64x64', '');
                imgElement.attr('srcset', updatedsrcset);
            }

            // Check if srcset contains '-150x150' and update it
            if (srcset && srcset.includes('-150x150')) {
                let updatedsrcset = srcset.replace('-150x150', '');
                imgElement.attr('srcset', updatedsrcset);
            }
        }
    });
});

// Configure the observer to watch for added and changed images
const config = { attributes: true, childList: true, subtree: true };

// Start observing the document for changes
observer.observe(document.body, config);

jQuery('.site-loader').hide();

// jQuery(window).on('load', function () {
//     setTimeout(function () {
//         jQuery('.site-loader').hide();
//     }, 1000);
// });


// jQuery(document).ready(function () {
//     // Get references to the button and span
//     const button = jQuery("#next-button-texts");
//     const dynamicText = jQuery("#dynamic-text");

//     // Get the list of texts from the data-texts attribute
//     const texts = dynamicText.data("texts").split(",");

//     // Track the current text index
//     let currentIndex = 0;

//     // Add click event listener to the button
//     button.on("click", function () {
//         // Increment the index and reset to 0 if it exceeds the array length
//         currentIndex = (currentIndex + 1) % texts.length;

//         // Update the text in the span
//         dynamicText.text(texts[currentIndex]);
//     });
// });

// jQuery(document).ready(function ($) {
    // Check if the body has the class "page-ueber-uns"
    // if (jQuery("body").hasClass("page-ueber-uns") || jQuery("body").hasClass("page-karriere")) {
    //     // Get references to the button and span
    //     const button = jQuery("#next-button-texts");
    //     const dynamicText = jQuery("#dynamic-text");

    //     // Get the list of texts from the data-texts attribute
    //     const textsAttr = dynamicText.data("texts") || dynamicText.attr("data-texts");
    //     if (!textsAttr) {
    //         console.error("data-texts attribute is missing or empty");
    //         return;
    //     }
    //     const texts = textsAttr.split(",");

    //     // Track the current text index
    //     let currentIndex = 0;

    //     // Add click event listener to the button
    //     button.on("click", function () {
    //         // console.log("Button clicked");
    //         // Increment the index and reset to 0 if it exceeds the array length
    //         currentIndex = (currentIndex + 1) % texts.length;

    //         // Update the text in the span
    //         dynamicText.text(texts[currentIndex]);
    //     });
    // }

    // $(document).tooltip({
    //     items: "[title]",
    //     position: {                    
    //         my: "left top",
    //         at: "left bottom",
    //         using: function( position, feedback ) {
    //         $( this ).css( position );
    //         $( "<div>" )
    //             .addClass( "arrow" )
    //             .addClass( feedback.vertical )
    //             .addClass( feedback.horizontal )
    //             .appendTo( this );
    //         }
    //     }
    // });
// });

/*jQuery(document).ready(function ($) {
    // Check if the body has the class "page-ueber-uns" or "page-karriere"
    if (jQuery("body").hasClass("page-ueber-uns") || jQuery("body").hasClass("page-karriere")) {
        function setupTextRotation(buttonSelector, textSelector) {
            const button = jQuery(buttonSelector);
            const dynamicText = jQuery(textSelector);

            // Get the list of texts from the data-texts attribute
            const textsAttr = dynamicText.data("texts") || dynamicText.attr("data-texts");
            if (!textsAttr) {
                console.error(`data-texts attribute is missing or empty for ${textSelector}`);
                return;
            }
            const texts = textsAttr.split(",");

            // Track the current text index
            let currentIndex = 0;

            // Add click event listener to the button
            button.on("click", function () {
                // Increment the index and reset to 0 if it exceeds the array length
                currentIndex = (currentIndex + 1) % texts.length;

                // Update the text in the span
                dynamicText.text(texts[currentIndex]);
            });
        }

        // Initialize text rotation for both sets of elements
        setupTextRotation("#next-button-texts", "#dynamic-text");
        if (jQuery("body").hasClass("page-ueber-uns")) {
            setupTextRotation("#next-button-texts1", "#dynamic-text1");
        }
    }
});*/

jQuery(document).ready(function ($) {
    // Get current language from HTML tag or default to 'de'
    const currentLang = $('html').attr('lang') || 'de';
    
    // Function to handle text rotation
    function setupTextRotation(buttonId, textId) {
        const $button = $(`#${buttonId}`);
        const $text = $(`#${textId}`);
        
        // Get texts for current language or fallback to German
        const textsAttr = $text.attr(`data-texts-${currentLang}`) || $text.attr('data-texts-de');
        if (!textsAttr) {
            console.error(`No texts found for language: ${currentLang} in ${textId}`);
            return;
        }
        
        const texts = textsAttr.split(',').map(t => t.trim());
        let currentIndex = 0;
        
        // Set initial text
        $text.text(texts[0]);
        
        // Add click handler
        $button.off('click').on('click', function(e) {
            e.preventDefault();
            currentIndex = (currentIndex + 1) % texts.length;
            $text.text(texts[currentIndex]);
        });
    }

    // Initialize for Über uns page
    if ($("body").hasClass("page-ueber-uns")) {
        setupTextRotation('next-button-texts', 'dynamic-text');
        setupTextRotation('next-button-texts1', 'dynamic-text1');
    }
    
    // Initialize for Karriere page
    if ($("body").hasClass("page-karriere")) {
        setupTextRotation('next-button-texts-career', 'dynamic-text-career');
    }
});


// Profile JS ===========================================================
jQuery(document).ready(function () {
    /*--- AVATAR POPUP ---*/
    jQuery('.openPopup_avatar').on('click', function (e) {
        e.preventDefault();
        jQuery('#popup_avatar').css('display', 'flex');
    });

    jQuery('#closePopup').on('click', function (e) {
        e.preventDefault();
        jQuery('#popup_avatar').fadeOut(300);
    });

    /*--- IMAGE UPLOAD ---*/
    jQuery('#profile_pic').on('change', function () {
        jQuery('.site-loader').show();
        var formData = new FormData();
        formData.append('action', 'upload_profile_picture');
        formData.append('profile_pic', jQuery(this)[0].files[0]);

        jQuery.ajax({
            url: frontend_ajax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    jQuery('.site-loader').hide();
                    var imgUrl = response.data.url;
                    jQuery('#profile-picture-preview').attr('src', imgUrl);
                    // alert('Image uploaded successfully!');
                    // Reload the page to reflect the changes
                    location.reload(); // This will reload the page
                } else {
                    alert(response.data.message || 'Image upload failed.');
                }
            },
            error: function () {
                alert('An error occurred during upload.');
            }
        });
    });

    /*--- SET AVATAR ---*/
    jQuery('#set_avatar').on('click', function (e) {
        e.preventDefault();
        jQuery('.site-loader').show();
        var isChecked = jQuery('input[name="avatar"]:checked').length > 0;

        // if (isChecked) {
            var imgId = jQuery('input[name="avatar"]:checked').attr('id');
            var imgUrl = jQuery('.' + imgId).attr('src');
            saveProfileImage(imgUrl);
        // } else {
        //     alert('Please select an avatar or upload an image.');
        // }
    });

    /*--- SAVE PROFILE IMAGE ---*/
    function saveProfileImage(imgUrl) {
        jQuery.ajax({
            url: frontend_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_profile_image',
                img_url: imgUrl,
            },
            success: function (response) {
                // if (response.success) {
                    jQuery('.site-loader').hide();
                    // alert('Profile image updated successfully!');
                    jQuery('#profile-picture-preview').attr('src', imgUrl);
                    jQuery('#popup_avatar').fadeOut(300);
                    // Reload the page to reflect the changes
                    location.reload(); // This will reload the page
                // } else {
                //     alert(response.data.message || 'Failed to update profile image.');
                // }
            },
            error: function () {
                alert('An error occurred while saving the profile image.');
            }
        });
    }
});

jQuery(document).ready(function($) {
    // var profilePic = document.getElementById('profile_pic');

    // if (profilePic) {

    //     profilePic.addEventListener('change', function(event) {
    //         var file = event.target.files[0];
    //         if (file && file.type.match('image.*')) {
    //             var reader = new FileReader();
    //             reader.onload = function(e) {
    //                 var img = document.getElementById('profile-picture-preview');
    //                 img.src = e.target.result;
    //             }
    //             reader.readAsDataURL(file);
    //         } else {
    //             alert('Please select a valid image file.');
    //         }
    //     });
    // }

    // /*--- SET AVATAR ---*/
    // jQuery('#set_avatar').on('click', function (e) {

    //     var isChecked = $('input[name="avatar"]:checked').length > 0;
    //     if(isChecked) {
            
    //         var imgid = $('input[name="avatar"]:checked').attr('id');
    //         var imgurl = $('.'+imgid).attr('src');

    //         $('#profile-picture-preview').attr('src', imgurl);
    //         $('.user-section img').attr('src', imgurl);
    //         $('input[name="avatar_img"]').val(imgurl);
    //         $('#closePopup').click();

    //     } else {

    //         jQuery(this).prev().html('Please select avatar.');
    //     }
    // });

    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'save_profile');
        formData.append('nonce', frontend_ajax.nonce);

        // Input fields
        var fname = jQuery('#first_name');
        var lname = jQuery('#last_name');
        var uname = jQuery('#username');
        var email = jQuery('#email');
        var phone = jQuery('#phone');
        var lang = jQuery('html').attr('lang');
        var errors = []; // Array to track errors

        jQuery('div.error').remove(); // Clear previous error messages

        // Utility function to add an error message
        const addError = (field, messageEn, messageDe) => {
            const message = lang === 'de-DE' ? messageDe : messageEn;
            field.after('<div class="error">' + message + '</div>');
            errors.push(field);
        };

        // Validate fields
        if (!fname.val().trim()) {
            addError(fname, 'First name field is required!', 'Das Feld „Vorname“ ist erforderlich!');
        }

        if (!lname.val().trim()) {
            addError(lname, 'Last name field is required!', 'Das Feld „Nachname“ ist erforderlich!');
        }

        if (!uname.val().trim()) {
            addError(uname, 'Username field is required!', 'Das Feld „Benutzername“ ist erforderlich.');
        }

        const emailVal = email.val().trim();
        if (!emailVal) {
            addError(email, 'Email field is required!', 'Das Feld „E-Mail“ ist erforderlich!');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
            addError(email, 'Enter a valid email address!', 'Geben Sie eine gültige E-Mail-Adresse ein!');
        }

        // Validate phone
        const phoneVal = phone.val().trim();
        if (!phoneVal) {
            addError(phone, 'Phone number is required!', 'Das Feld „Telefonnummer“ ist erforderlich!');
        } else if (!/^[0-9+\-() ]+$/.test(phoneVal)) {
            addError(phone, 'Enter a valid phone number!', 'Geben Sie eine gültige Telefonnummer ein!');
        }

        // Stop form submission if there are errors
        if (errors.length > 0) {
            errors[0].focus(); // Focus the first field with an error
            return;
        }
        $.ajax({
            url: frontend_ajax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response.data);
                jQuery('form#profile-form .message').append(response.data.message);

                if (response.data.img) 
                {
                    jQuery('#profile-picture-preview').attr('src', response.data.img);
                }

                setTimeout(function () {
                    jQuery('form#profile-form .message').html('');
                }, 4000);
            }
        });
    });

    $('#password-form').on('submit', function (e) {
        e.preventDefault();

        var currentPasswordField = $('#current_password');
        var newPasswordField = $('#new_password');
        var repeatNewPasswordField = $('#repeat_new_password');

        var currentPassword = currentPasswordField.val().trim();
        var newPassword = newPasswordField.val().trim();
        var repeatNewPassword = repeatNewPasswordField.val().trim();

        jQuery('div.error').remove(); // Remove previous errors
        var lang = jQuery('html').attr('lang');
        // alert(lang);
        var minLength = 6;
        var uppercase = /[A-Z]/;
        var specialChar = /[!@#$%^&*(),.?":{}|<>]/;

        var hasErrors = false; // Track if there are any errors

        // Validate current password
        if (currentPassword === '') {
            var currentPasswordError = 'Current password is required.';
            if (lang == 'de-DE') {
                currentPasswordError = 'Das aktuelle Passwort ist erforderlich.';
            }
            currentPasswordField.after('<div class="error">' + currentPasswordError + '</div>');
            hasErrors = true;
        }

        // Validate new password
        if (!hasErrors && newPassword === '') {
            var newPasswordError = 'New password is required.';
            if (lang == 'de-DE') {
                newPasswordError = 'Das neue Passwort ist erforderlich.';
            }
            newPasswordField.after('<div class="error">' + newPasswordError + '</div>');
            hasErrors = true;
        }

        if (!hasErrors && repeatNewPassword === '') {
            var repeatNewPasswordError = 'Repeat new password is required.';
            if (lang == 'de-DE') {
                repeatNewPasswordError = 'Das Wiederholen des neuen Passworts ist erforderlich.';
            }
            repeatNewPasswordField.after('<div class="error">' + repeatNewPasswordError + '</div>');
            hasErrors = true;
        }

        // Validate password match
        if (!hasErrors && newPassword !== repeatNewPassword) {
            var mismatchError = 'The new password fields do not match.';
            if (lang == 'de-DE') {
                mismatchError = 'Die neuen Passwortfelder stimmen nicht überein.';
            }
            repeatNewPasswordField.after('<div class="error">' + mismatchError + '</div>');
            hasErrors = true;
        }

        // Check new password length
        if (!hasErrors && newPassword.length < minLength) {
            var lengthError = 'Password must be at least ' + minLength + ' characters long.';
            if (lang == 'de-DE') {
                lengthError = 'Das Passwort muss mindestens ' + minLength + ' Zeichen lang sein.';
            }
            newPasswordField.after('<div class="error">' + lengthError + '</div>');
            hasErrors = true;
        }

        // Check for uppercase letter
        if (!hasErrors && !uppercase.test(newPassword)) {
            var uppercaseError = 'Password must contain at least one uppercase letter.';
            if (lang == 'de-DE') {
                uppercaseError = 'Das Passwort muss mindestens einen Großbuchstaben enthalten.';
            }
            newPasswordField.after('<div class="error">' + uppercaseError + '</div>');
            hasErrors = true;
        }

        // Check for special character
        if (!hasErrors && !specialChar.test(newPassword)) {
            var specialCharError = 'Password must contain at least one special character.';
            if (lang == 'de-DE') {
                specialCharError = 'Das Passwort muss mindestens ein Sonderzeichen enthalten.';
            }
            newPasswordField.after('<div class="error">' + specialCharError + '</div>');
            hasErrors = true;
        }

        // Stop submission if there are errors
        if (hasErrors) {
            return;
        }

        // Prepare form data for AJAX request
        var formData = {
            action: 'save_password',
            nonce: frontend_ajax.nonce,
            current_password: currentPassword,
            new_password: newPassword,
            repeat_new_password: repeatNewPassword
        };

        jQuery('form#password-form .message').html(''); // Clear previous messages

        $.post(frontend_ajax.ajaxurl, formData, function (response) {
            // Reset the form
            document.getElementById('password-form').reset();
            jQuery('form#password-form .message').append('<span class="success">' + response.data.message + '</span>');

            // Remove success message after 4 seconds
            setTimeout(function () {
                jQuery('form#password-form .message').html('');
            }, 4000);
        });
    });


    $('#reset-progress-form').on('submit', function(e) {
        e.preventDefault();

        var resetkey = $('#confirm_reset').val();

        jQuery('form#reset-progress-form .message').html('');

        if (resetkey == '' || (resetkey !== 'RESET' && resetkey !== 'ZURÜCKSETZTEN')) {

            var lang = jQuery('html').attr('lang');
            var passerr = 'Please enter "RESET" text in the box before submit.';            
            if (lang == 'de-DE') { passerr = 'Bitte geben Sie vor dem Absenden den Text „RESET“ in das Feld ein.'; }            
            
            jQuery('form#reset-progress-form .message').append('<span class="error">'+passerr+'</span>');
            return;
        }

        var formData = {
            action: 'reset_progress',
            nonce: frontend_ajax.nonce,
            confirm_reset: resetkey
        };

        $.post(frontend_ajax.ajaxurl, formData, function(response) {
            // Reset the form
            document.getElementById('reset-progress-form').reset();
            jQuery('form#reset-progress-form .message').append(response.data.message);
            setTimeout(function () {
                 jQuery('form#reset-progress-form .message').html('');
            }, 4000);
        });
    });

    $('#delete-progress-form').on('submit', function(e) {
        e.preventDefault();

        var deletekey = $('#confirm_delete').val();
        
        jQuery('form#reset-progress-form .message').html('');

        if (deletekey == '' || (deletekey !== 'DELETE' && deletekey !== 'LÖSCHEN')) {

            var lang = jQuery('html').attr('lang');
            var passerr = 'Please enter "DELETE" text in the box before submit.';           
            if (lang == 'de-DE') { passerr = 'Bitte geben Sie vor dem Absenden den Text „LÖSCHEN“ in das Feld ein.'; }           
            
            jQuery('form#delete-progress-form .message').html('<span class="error">'+passerr+'</span>');
            return;
        }

        // jQuery('form#delete-progress-form .message').html('');

        var formData = {
            action: 'delete_progress',
            nonce: frontend_ajax.nonce,
            confirm_delete: deletekey
        };
        // jQuery('form#delete-progress-form .message').html('');
        $.post(frontend_ajax.ajaxurl, formData, function(response) {
            console.log(response);
            document.getElementById('delete-progress-form').reset();
            if (response) {
                
                if (response.data.redirect) {               
                    
                    window.location.href = response.data.redirect;

                } else {

                    jQuery('form#delete-progress-form .message').append(response.data.message);
                }
            }
        });
    });
});


jQuery(document).ready(function($) {
    var loginURL = '/mein-konto/'; // Replace with your actual login page URL

    $('.woocommerce-error .showlogin')
        .attr('href', loginURL)
        .text('Bitte anmelden') // Change the link text if needed
        .off('click') // Remove WooCommerce's click handler
        .on('click', function(e) {
            e.stopPropagation(); // Stop bubbling up
        });

    // Remove the inner <div> by unwrapping its contents
    $('.woocommerce-error > div').contents().unwrap();
});



jQuery(document).ready(function($) {

    // Target feedback messages for correct and incorrect answers
    $(".wpProQuiz_correct").each(function () {
        $(this).append('<p class="extra-message">'+messageCorrect+'</p>');
    });
    
    $(".wpProQuiz_incorrect").each(function () {
        $(this).append('<p class="extra-message">'+messageIncorrect+'</p>');
    });


    var lang = jQuery('html').attr('lang');
    
    /*-- Register Form Script ---*/
    var passerrtxt = 'Passwords do not match.';
    var passerrtxt1 = 'Please enter your password.';
    var passerrtxt2 = 'Your password must be between 8-16 characters.';
    var passerrtxt3 = 'Please confirm your password.';
    var emailerrortxt = 'Please enter a valid email address.';
    var emailerrortxt1 = 'Please enter an email address.';
    var emailerrortxt2 = 'Invalid email address.';
    var tcerrtxt = 'You must accept the terms and conditions.';
    var ajxerrtxt = 'An error occurred. Please try again.';
    var userId = null;
    var phoneerrtxt1 = 'Please enter your Phonenumber.';     // Required
    var phoneerrtxt2 = 'Please enter a valid phone number.'; // Invalid

    if (lang == 'de-DE') {
        passerrtxt = 'Passwörter stimmen nicht überein.';
        passerrtxt1 = 'Bitte geben Sie Ihr Passwort ein.';
        passerrtxt2 = 'Dein Passwort muss zwischen 8 und 16 Zeichen lang sein.';
        passerrtxt3 = 'Bitte bestätigen Sie Ihr Passwort.';
        emailerrortxt = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        emailerrortxt1 = 'Bitte geben Sie eine E-Mail-Adresse ein.';
        emailerrortxt2 = 'Ungültige E-Mail-Adresse.';
        tcerrtxt = 'Du musst die AGB akzeptieren.';
        ajxerrtxt = 'Es ist ein Fehler aufgetreten. Versuchen Sie es erneut.';
        phoneerrtxt1 = 'Bitte geben Sie Ihr Telefonnummer ein.';
        phoneerrtxt2 = 'Bitte geben Sie eine gültige Telefonnummer ein.';
    }

    // Initialize intl-tel-input
    var phoneInput = document.querySelector("#billing_phone");
    // var iti;

    // --- ADD THIS AFTER THE ABOVE BLOCK ---
    $.validator.addMethod("phoneValid", function(value, element) {
        return this.optional(element) || (window.iti && window.iti.isValidNumber());
    }, phoneerrtxt2);

    $.validator.addMethod("simplePhone", function(value, element) {
        // Remove all non-digits
        var digits = value.replace(/\D/g, '');
        return this.optional(element) || (digits.length >= 10 && digits.length <= 15);
    }, phoneerrtxt2); //"Bitte geben Sie eine gültige Telefonnummer ein.");
    
    $("#custom-registration-form").validate({
        rules: {
            email: {
                required: true,
                email: true,
                isemail: true
            },
            password: {
                required: true,
                pass: true
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            },
            accept_term_condition: {
                required: true
            },
            billing_phone: {
                required: true,
                simplePhone: true
            }
        },
        messages: {
            email: {
                required: emailerrortxt1,
                email: emailerrortxt,
                isemail: emailerrortxt
            },
            password: {
                required: passerrtxt1,
                pass: passerrtxt2
            },
            confirm_password: {
                required: passerrtxt3,
                equalTo: passerrtxt
            },
            accept_term_condition: {
                required: tcerrtxt
            },
            billing_phone: {
                required: phoneerrtxt1,
                simplePhone: phoneerrtxt2
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "accept_term_condition") {
                error.insertAfter(".group_term_condition .switch");
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function(form) {
            var email = $('#email').val();
            var password = $('#password').val();
            var fullPhone = $('#billing_phone').val();
            var confirmPassword = $('#confirm_password').val();
            var nonce = $('#custom_registration_nonce').val();
            var isWantMail = $('#want_mails').prop('checked');
            const messageContainer = $('#message');
            messageContainer.html('');

            // Get full international number
            // var fullPhone = iti.getNumber();

            const data = new FormData();
            data.append('action', 'custom_registration');
            data.append('email', email);
            data.append('billing_phone', fullPhone); // ← send full number
            data.append('password', password);
            data.append('confirm_password', confirmPassword);
            data.append('custom_registration_nonce', nonce);
            data.append('want_mails', isWantMail);

            // Show loader
            jQuery('.site-loader').show();

            fetch(frontend_ajax.ajaxurl, {
                method: 'POST',
                body: data,
            })
            .then(response => response.json())
            .then(result => {
                // Hide loader
                jQuery('.site-loader').hide();
                if (result.success) {
                    messageContainer.html('<div class="success">' + result.data.message + '</div>');
                    $('#custom-registration-form').hide();
                    $('.verification-container').show(); // Show verification form
                    userId = result.data.user_id; // Store user ID for verification
                } else {
                    // Remove previous error messages
                    $('.email-existed, .other-err').remove();

                    if (result.data.email) {
                        $('#email').after('<div class="error email-existed">' + result.data.email + '</div>');
                    } else {
                        messageContainer.html('<div class="error other-err">' + result.data.message + '</div>');
                    }

                    // ✅ Redirect logic
                    if (result.data.redirect) {
                        setTimeout(function() {
                            window.location.href = result.data.redirect;
                        }, 1300); // 3-second delay
                    }
                }
            })
            .catch(error => {
                messageContainer.html('<div class="error">' + ajxerrtxt + '</div>');
            });
        }
    });

    // 2FA Verification Process
    $('.verify_btn').on('click', function() {
        const verificationCode = $('#verification_code').val();
        const verificationMessage = $('#verification-message');
        var nonce = $('#custom_registration_nonce').val();

        verificationMessage.html('');
        if (verificationCode) {
            const data = new URLSearchParams({
                'action': 'verify_verification_code',
                'custom_registration_nonce': nonce,
                'verification_code': verificationCode,
                'user_id': userId
            });

            fetch(frontend_ajax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    verificationMessage.html('<div class="success">' + result.data.message + '</div>');
                    setTimeout(function() {
                        window.location.href = result.data.url; // Redirect after successful verification
                    }, 3000);
                } else {
                    verificationMessage.html('<div class="error">' + result.data.message + '</div>');
                }
            })
            .catch(error => {
                verificationMessage.html('<div class="error">' + ajxerrtxt + '</div>');
            });
        } else {
            verificationMessage.html('<div class="error">Bitte gib den Bestätigungscode ein.</div>');
        }
    });

    // Custom email validation method
    jQuery.validator.addMethod("isemail", function(value, element) {
        return this.optional(element) || /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/.test(value);
    }, emailerrortxt2);

    // Custom password validation method
    jQuery.validator.addMethod("pass", function(value, element) {
        return this.optional(element) ||
            // value.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%#=+_*?&])[A-Za-z\d@$!%*?&]{8,16}$/);
            // value.match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%#=+_*?&])[A-Za-z\d@$!%#=+_*?&]{8,16}$/);
            value.match(/^.{8,16}$/);
    });


    /*-- Login Form Script ---*/
    var emailerr = 'Please enter your email address.';
    var emailerr1 = 'Please enter a valid email address.';
    var passerr = 'Please provide a password.';
    var passerr1 = 'Your password must be at least 6 characters long.';
    var messageCorrect = 'You are right. Poverty means the same as need.';
    var messageIncorrect = 'Wrong! Poverty means the same as need.';

    if (lang == 'de-DE') {
        emailerr = 'Geben Sie bitte Ihre Email-Adresse ein.';
        emailerr1 = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
        passerr = 'Bitte geben Sie ein Passwort ein.';
        passerr1 = 'Ihr Passwort muss mindestens 6 Zeichen lang sein.';
        messageCorrect = 'Sie haben Recht. Armut bedeutet dasselbe wie Not.'; 
        messageIncorrect = 'Falsch! Armut bedeutet dasselbe wie Not.';
    }

    $("#woo-login-form").validate({
        rules: {
            username: {
                required: true,
                email: true
            },
            password: {
                required: true,
                pass: true
            }
        },
        messages: {
            username: {
                required: emailerr,
                email: emailerr1
            },
            password: {
                required: passerr,
                pass: passerrtxt2
            }
        },
        submitHandler: function(form) {
            // Disable the button to prevent multiple clicks
            const loginButton = $('button[type="submit"]');
            loginButton.prop('disabled', true);

            var email = $('#username').val();
            var password = $('#password').val();
            var nonce = $('#custom_login_nonce').val();

            const data = new URLSearchParams();
            data.append('action', 'custom_login');
            data.append('username', email);
            data.append('password', password);
            data.append('custom_login_nonce', nonce);

            // Show loader
            jQuery('.site-loader').show();
            
            fetch(frontend_ajax.ajaxurl, {
                method: 'POST',
                body: data,
            })
            .then(response => response.json())
            .then(result => {
                // Hide loader
                jQuery('.site-loader').hide();
                if (email === 'kontakt@adicum.ch') {
                    // Skip 2FA for this specific user and log them in directly
                    // Block this specific user and show an alert
                    // alert("You can't login in the website!");
                    window.location = "/mein-profil/";  // Redirect to homepage or another URL
                }else{
                    if (result.success) {
                        $('#woo-login-form').hide();
                        $('#twofa-verification-form').show();
                        $('#user_id').val(result.data.user_id);
                    } else {
                        $('#form-message').html('<div class="error">' + result.data.message + '</div>');
                    }
                }
            })
            .catch(error => {
                $('#form-message').html('<div class="error">An error occurred. Please try again.</div>');
            })
            .finally(() => {
                // Re-enable the button after processing
                loginButton.prop('disabled', false);
            });
        }
    });

    $("#verify_2fa_btn").on("click", function() {
        const userId = $('#user_id').val();
        const verificationCode = $('#verification_login_code').val();
        const verificationMessage = $('#verification-message');
        var nonce = $('#custom_login_nonce').val();

        verificationMessage.html('');
        if (verificationCode && userId) {
            const data = new URLSearchParams({
                'action': 'verify_2fa',
                'custom_login_nonce': nonce,
                'verification_login_code': verificationCode,
                'user_id': userId
            });
            
            fetch(frontend_ajax.ajaxurl, {
                method: 'POST',
                body: data
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    verificationMessage.html('<div class="success">' + result.data.message + '</div>');
                    setTimeout(function() {
                        window.location.href = result.data.url;
                    }, 3000);
                } else {
                    verificationMessage.html('<div class="error">' + result.data.message + '</div>');
                }
            })
            .catch(error => {
                verificationMessage.html('<div class="error">An error occurred: ' + error.message + '</div>');
            });
        } else {
            verificationMessage.html('<div class="error">Bitte gib den Bestätigungscode ein.</div>');
        }
    });


    // Define error messages
    var passerrtxt = 'Passwords do not match.';
    var passerrtxt1 = 'Please enter your password.';
    var passerrtxt2 = 'Your password must be between 8-16 characters.';
    var passerrtxt3 = 'Please confirm your password.';
    
    if (lang == 'de-DE') {
        // German Error Messages
        var passerrtxt = 'Passwörter stimmen nicht überein.';
        var passerrtxt1 = 'Bitte gib dein Passwort ein.';
        var passerrtxt2 = 'Dein Passwort muss zwischen 8 und 16 Zeichen lang sein.';
        var passerrtxt3 = 'Bitte bestätige dein Passwort.';
    }

    // Remove WooCommerce's inline validation attributes
    $('form.woocommerce-ResetPassword input').removeAttr('required aria-required aria-invalid');

    // Add custom method for password strength validation
    $.validator.addMethod("strongPassword", function (value, element) {
        return this.optional(element) || /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,16}$/.test(value);
    }, passerrtxt2);

    // Initialize jQuery Validation
    $('form.woocommerce-ResetPassword').validate({
        rules: {
            password_1: {
                required: true,
                pass: true
            },
            password_2: {
                required: true,
                equalTo: "#password_1"
            }
        },
        messages: {
            password_1: {
                required: passerrtxt1,
                pass: passerrtxt2
            },
            password_2: {
                required: passerrtxt3,
                equalTo: passerrtxt
            }
        },
        errorPlacement: function (error, element) {
            // Place error messages after the `show-password-input` span
            if (element.closest('.woocommerce-form-row').length) {
                error.insertAfter(element.closest('.woocommerce-form-row').find('.password-input'));
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            form.submit(); // Allow form submission if validation passes
        }
    });

    // Target the form submission
    $('form.woocommerce-ResetPassword.lost_reset_password').on('submit', function (e) {
        setTimeout(() => {
            $(this).find('button[type="submit"]').prop('disabled', false);
        }, 100); // Short delay to ensure WooCommerce's script doesn't interfere
    });

    // Define error messages
    var emailErrTxt = 'Bitte geben Sie eine E-Mail-Adresse ein.';
    var emailInvalidTxt = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';

    // Remove WooCommerce's inline validation attributes
    $('form.woocommerce-LostPassword input').removeAttr('required aria-required aria-invalid');

    // Initialize jQuery Validation
    $('form.woocommerce-LostPassword').validate({
        rules: {
            user_login: {
                required: true,
                email: true
            }
        },
        messages: {
            user_login: {
                required: emailErrTxt,
                email: emailInvalidTxt
            }
        },
        errorPlacement: function (error, element) {
            // Place error messages appropriately
            error.insertAfter(element);
        },
        highlight: function (element) {
            $(element).addClass('error-input'); // Highlight invalid input
        },
        unhighlight: function (element) {
            $(element).removeClass('error-input'); // Remove highlight from valid input
        },
        submitHandler: function (form) {
            // Enable the submit button before submitting the form
            $(form).find('button[type="submit"]').removeAttr('disabled');
            form.submit(); // Allow form submission if validation passes
        }
    });

    // Target the form submission
    $('form.woocommerce-LostPassword.lost_reset_password').on('submit', function (e) {
        setTimeout(() => {
            $(this).find('button[type="submit"]').prop('disabled', false);
        }, 100); // Short delay to ensure WooCommerce's script doesn't interfere
    });


    // My Profile
    var readURL = function(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.profile-pic').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $(".file-upload").on('change', function(){
        readURL(this);
    });

    $(".upload-button1").on('click', function() {
       $(".file-upload").click();
    });


    // accordion
    $(".accordion_item > a").on("click", function () {
        if ($(this).hasClass("active")) {
            $(this).removeClass("active");
            $(this).siblings(".accordion_body").slideUp(200);
            $(".icon_arrow > i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
        } else {
            $(".icon_arrow > i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
            $(this).find(".icon_arrow > i").removeClass("fa-chevron-down").addClass("fa-chevron-up");
            $(".accordion_item > a").removeClass("active");
            $(this).addClass("active");
            $(".accordion_body").slideUp(200);
            $(this).siblings(".accordion_body").slideDown(200);
        }
    });
});


/*****CHECKOUT PAGE*****/
jQuery('body').on('click', ".cart_link", function () {
    jQuery('#cart_popup').show();
    jQuery('body').addClass('body_fix');
});

jQuery('body').on('click', "#closePopup", function () {
    jQuery('#cart_popup').hide();
    jQuery('body').removeClass('body_fix');
});

jQuery('body').off('click', '.plus, .minus').on('click', '.plus, .minus', function (e) {
    
    e.preventDefault();

    var input = jQuery(this).closest('.quantity').find('input[type="number"]');
    var currentVal = parseFloat(input.val());
    var max = parseFloat(input.attr('max'));
    var min = parseFloat(input.attr('min'));
    var step = input.attr('step');

    var pid = jQuery(input).attr('product-id');
    var pitemkey = jQuery('#cart_item-key'+pid).val();

    // Handle increment or decrement based on the button clicked
    if (jQuery(this).hasClass('plus')) {
        if (max && (currentVal >= max)) {
            input.val(max);
        } else if (step) {
            input.val(currentVal + parseFloat(step));
        } else {
            input.val(currentVal + 1);
        }
    } else {
        if (min && (currentVal <= min)) {
            input.val(min);
        } else if (step) {
            input.val(currentVal - parseFloat(step));
        } else {
            input.val(currentVal - 1);
        }
    }

    jQuery('.site-loader').show();

    var ajaxurl = frontend_ajax.ajaxurl;
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'update_cart',
            product_id: pid,
            quantity: input.val(),
            cart_item_key : pitemkey
        },
        success: function (response) {
            console.log(response);
            if (response.success) {
                jQuery('.cart-subtotal p .woocommerce-Price-amount.amount,  .order-total .order_total_price strong .woocommerce-Price-amount.amount').replaceWith(response.data.cart_subtotal);
                jQuery('.psubtotal' + pid + ' .woocommerce-Price-amount.amount').replaceWith(response.data.product_subtotal);
            } else {
                console.log(response.data);
            }
            jQuery('.site-loader').hide();
        },
        error: function (xhr, status, error) {
            console.error(error);
            jQuery('.site-loader').hide();
        }
    });
});

// jQuery('body').on('click','#place_order',function(){
//     if (!jQuery('.term_condition').is(':checked')) {
//         jQuery('.checkout_check_err.error').text('Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.');
//         jQuery('.checkout_check_err.error').show();
//         event.preventDefault();
//     } else {
//         jQuery('.checkout_check_err.error').hide();
//     }
// });

jQuery('body').on('change','.term_condition',function(){
    if(jQuery(this).is(':checked')){
        jQuery('.checkout_check_err.error').hide();
    }else{
        jQuery('.checkout_check_err.error').text('Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.');
        jQuery('.checkout_check_err.error').show();
    }
});

jQuery(document).ready(function () {

    // jQuery('body').on('change', '#billing_term_condition', function () {


    //     if (jQuery(this).is(':checked')) {
    //         jQuery('.errors.billing_check_err').hide();

    //     } else {

    //         jQuery('.email-edit').click();
    //         jQuery('.errors.billing_check_err').text('Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.')
    //             .show();
    //     }
    // });


    var maxSize = 5 * 1024 * 1024;
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Initialize intl-tel-input
    var iti;
    /*jQuery(document).ready(function ($) {
        var phoneInput = document.querySelector('#billing_phone');
        if (phoneInput) {
            iti = window.intlTelInput(phoneInput, {
                initialCountry: 'ch',
                preferredCountries: ['ch', 'de', 'at', 'fr', 'it'],
                separateDialCode: true,
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
            });
        }
    });*/
    jQuery(function ($) {
        const phoneInput = document.querySelector("#billing_phone");

        if (phoneInput) {
            window.intlTelInput(phoneInput, {
                initialCountry: "ch",
                preferredCountries: ["ch", "de", "at", "fr", "it"],
                localizedCountries: {
                    ad: "Andorra",
                    ae: "Vereinigte Arabische Emirate",
                    af: "Afghanistan",
                    ag: "Antigua und Barbuda",
                    ai: "Anguilla",
                    al: "Albanien",
                    am: "Armenien",
                    ao: "Angola",
                    ar: "Argentinien",
                    as: "Amerikanisch-Samoa",
                    at: "Österreich",
                    au: "Australien",
                    aw: "Aruba",
                    ax: "Ålandinseln",
                    az: "Aserbaidschan",
                    ba: "Bosnien und Herzegowina",
                    bb: "Barbados",
                    bd: "Bangladesch",
                    be: "Belgien",
                    bf: "Burkina Faso",
                    bg: "Bulgarien",
                    bh: "Bahrain",
                    bi: "Burundi",
                    bj: "Benin",
                    bl: "St. Barthélemy",
                    bm: "Bermuda",
                    bn: "Brunei Darussalam",
                    bo: "Bolivien",
                    bq: "Bonaire, Sint Eustatius und Saba",
                    br: "Brasilien",
                    bs: "Bahamas",
                    bt: "Bhutan",
                    bw: "Botsuana",
                    by: "Belarus",
                    bz: "Belize",
                    ca: "Kanada",
                    cc: "Kokosinseln",
                    cd: "Kongo-Kinshasa",
                    cf: "Zentralafrikanische Republik",
                    cg: "Kongo-Brazzaville",
                    ch: "Schweiz",
                    ci: "Côte d’Ivoire",
                    ck: "Cookinseln",
                    cl: "Chile",
                    cm: "Kamerun",
                    cn: "China",
                    co: "Kolumbien",
                    cr: "Costa Rica",
                    cu: "Kuba",
                    cv: "Cabo Verde",
                    cw: "Curaçao",
                    cx: "Weihnachtsinsel",
                    cy: "Zypern",
                    cz: "Tschechien",
                    de: "Deutschland",
                    dj: "Dschibuti",
                    dk: "Dänemark",
                    dm: "Dominica",
                    do: "Dominikanische Republik",
                    dz: "Algerien",
                    ec: "Ecuador",
                    ee: "Estland",
                    eg: "Ägypten",
                    eh: "Westsahara",
                    er: "Eritrea",
                    es: "Spanien",
                    et: "Äthiopien",
                    fi: "Finnland",
                    fj: "Fidschi",
                    fk: "Falklandinseln",
                    fm: "Mikronesien",
                    fo: "Färöer",
                    fr: "Frankreich",
                    ga: "Gabun",
                    gb: "Vereinigtes Königreich",
                    gd: "Grenada",
                    ge: "Georgien",
                    gf: "Französisch-Guayana",
                    gg: "Guernsey",
                    gh: "Ghana",
                    gi: "Gibraltar",
                    gl: "Grönland",
                    gm: "Gambia",
                    gn: "Guinea",
                    gp: "Guadeloupe",
                    gq: "Äquatorialguinea",
                    gr: "Griechenland",
                    gt: "Guatemala",
                    gu: "Guam",
                    gw: "Guinea-Bissau",
                    gy: "Guyana",
                    hk: "Sonderverwaltungsregion Hongkong",
                    hn: "Honduras",
                    hr: "Kroatien",
                    ht: "Haiti",
                    hu: "Ungarn",
                    id: "Indonesien",
                    ie: "Irland",
                    il: "Israel",
                    im: "Isle of Man",
                    in: "Indien",
                    io: "Britisches Territorium im Indischen Ozean",
                    iq: "Irak",
                    ir: "Iran",
                    is: "Island",
                    it: "Italien",
                    je: "Jersey",
                    jm: "Jamaika",
                    jo: "Jordanien",
                    jp: "Japan",
                    ke: "Kenia",
                    kg: "Kirgisistan",
                    kh: "Kambodscha",
                    ki: "Kiribati",
                    km: "Komoren",
                    kn: "St. Kitts und Nevis",
                    kp: "Nordkorea",
                    kr: "Südkorea",
                    kw: "Kuwait",
                    ky: "Kaimaninseln",
                    kz: "Kasachstan",
                    la: "Laos",
                    lb: "Libanon",
                    lc: "St. Lucia",
                    li: "Liechtenstein",
                    lk: "Sri Lanka",
                    lr: "Liberia",
                    ls: "Lesotho",
                    lt: "Litauen",
                    lu: "Luxemburg",
                    lv: "Lettland",
                    ly: "Libyen",
                    ma: "Marokko",
                    mc: "Monaco",
                    md: "Republik Moldau",
                    me: "Montenegro",
                    mf: "St. Martin",
                    mg: "Madagaskar",
                    mh: "Marshallinseln",
                    mk: "Nordmazedonien",
                    ml: "Mali",
                    mm: "Myanmar",
                    mn: "Mongolei",
                    mo: "Sonderverwaltungsregion Macau",
                    mp: "Nördliche Marianen",
                    mq: "Martinique",
                    mr: "Mauretanien",
                    ms: "Montserrat",
                    mt: "Malta",
                    mu: "Mauritius",
                    mv: "Malediven",
                    mw: "Malawi",
                    mx: "Mexiko",
                    my: "Malaysia",
                    mz: "Mosambik",
                    na: "Namibia",
                    nc: "Neukaledonien",
                    ne: "Niger",
                    nf: "Norfolkinsel",
                    ng: "Nigeria",
                    ni: "Nicaragua",
                    nl: "Niederlande",
                    no: "Norwegen",
                    np: "Nepal",
                    nr: "Nauru",
                    nu: "Niue",
                    nz: "Neuseeland",
                    om: "Oman",
                    pa: "Panama",
                    pe: "Peru",
                    pf: "Französisch-Polynesien",
                    pg: "Papua-Neuguinea",
                    ph: "Philippinen",
                    pk: "Pakistan",
                    pl: "Polen",
                    pm: "St. Pierre und Miquelon",
                    pr: "Puerto Rico",
                    ps: "Palästinensische Autonomiegebiete",
                    pt: "Portugal",
                    pw: "Palau",
                    py: "Paraguay",
                    qa: "Katar",
                    re: "Réunion",
                    ro: "Rumänien",
                    rs: "Serbien",
                    ru: "Russland",
                    rw: "Ruanda",
                    sa: "Saudi-Arabien",
                    sb: "Salomonen",
                    sc: "Seychellen",
                    sd: "Sudan",
                    se: "Schweden",
                    sg: "Singapur",
                    sh: "St. Helena",
                    si: "Slowenien",
                    sj: "Spitzbergen und Jan Mayen",
                    sk: "Slowakei",
                    sl: "Sierra Leone",
                    sm: "San Marino",
                    sn: "Senegal",
                    so: "Somalia",
                    sr: "Suriname",
                    ss: "Südsudan",
                    st: "São Tomé und Príncipe",
                    sv: "El Salvador",
                    sx: "Sint Maarten",
                    sy: "Syrien",
                    sz: "Eswatini",
                    tc: "Turks- und Caicosinseln",
                    td: "Tschad",
                    tg: "Togo",
                    th: "Thailand",
                    tj: "Tadschikistan",
                    tk: "Tokelau",
                    tl: "Timor-Leste",
                    tm: "Turkmenistan",
                    tn: "Tunesien",
                    to: "Tonga",
                    tr: "Türkei",
                    tt: "Trinidad und Tobago",
                    tv: "Tuvalu",
                    tw: "Taiwan",
                    tz: "Tansania",
                    ua: "Ukraine",
                    ug: "Uganda",
                    us: "Vereinigte Staaten",
                    uy: "Uruguay",
                    uz: "Usbekistan",
                    va: "Vatikanstadt",
                    vc: "St. Vincent und die Grenadinen",
                    ve: "Venezuela",
                    vg: "Britische Jungferninseln",
                    vi: "Amerikanische Jungferninseln",
                    vn: "Vietnam",
                    vu: "Vanuatu",
                    wf: "Wallis und Futuna",
                    ws: "Samoa",
                    ye: "Jemen",
                    yt: "Mayotte",
                    za: "Südafrika",
                    zm: "Sambia",
                    zw: "Simbabwe",
                },
                nationalMode: false,
                separateDialCode: true,
            });
        }
    });


    jQuery('#billing_email').on('keyup', function () {
        var email = jQuery(this).val();
        if (jQuery('#billing_email').val() === "") {
            jQuery('.errors.email_err').text("Das Feld E-Mail-Adresse ist ein Pflichtfeld.");
            jQuery('.errors.email_err').show();
            jQuery('.woocommerce-additional-fields__field-wrapper').hide();
            isValid = false;
        } else if (!emailRegex.test(jQuery('#billing_email').val())) {
            jQuery('.errors.email_err').text("Bitte geben Sie eine gültige E-Mail-Adresse ein.");
            jQuery('.errors.email_err').show();
            jQuery('.woocommerce-additional-fields__field-wrapper').hide();
            isValid = false;
        } else {
            jQuery('.errors.email_err').hide();
        }
    });


    jQuery('.proceed-email-address').on('click', function (e) {

        const postcregex = /^\d+$/;

        // Clear previous error messages
        jQuery('#error-messages').empty();

        // Array to hold error messages
        let fielderror = true;

        // Check each field
        jQuery('.woocommerce-input-wrapper input.input-text').each(function () {
            let fieldId = jQuery(this).attr('id');
            let fieldValue = jQuery(this).val().trim();

            if (fieldValue === '' && fieldId != 'billing_email' && fieldId != 'billing_address_2' && fieldId != 'billing_postcode'  && fieldId != 'billing_phone') {
                fielderror = false;
                //jQuery(this).find('.error').remove();
                if (jQuery(this).next('.error').length === 0) {
                    jQuery(this).after('<span class="error"> Das Feld '+ jQuery(this).attr('placeholder') +' ist ein Pflichtfeld.</span>');
                }               
            } else if (!postcregex.test(fieldValue) && fieldId == 'billing_postcode') {

                if (jQuery(this).next('.error').length === 0) {
                    jQuery(this).after('<span class="error"> Geben Sie eine gültige Postleitzahl / ZIP ein.</span>');
                    // jQuery(this).after('<span class="error"> Enter valid '+ jQuery(this).attr('placeholder') +'.</span>');
                } 

            } else {
                if (fieldId != 'billing_email') {
                    jQuery(this).next('.error').remove();
                }
            }
        });

               
        var email = jQuery('#billing_email').val();
        var isfvalidate = jQuery('.checkout_mailadd_form p').hasClass('woocommerce-invalid-required-field');
        var istcvalid = jQuery('.billing_form_tc .term_condition');
        var phoneInput = document.querySelector('#billing_phone');
        var phoneValid = true;

        // --- Phone Validation ---
        if (phoneInput && iti) {
            if (phoneInput.value.trim() === "") {
                phoneValid = false;
                if (jQuery('#billing_phone').next('.error').length === 0) {
                    jQuery('#billing_phone').after('<span class="error"> Das Feld Telefonnummer ist ein Pflichtfeld.</span>');
                }
            } else if (!iti.isValidNumber()) {
                phoneValid = false;
                if (jQuery('#billing_phone').next('.error').length === 0) {
                    jQuery('#billing_phone').after('<span class="error"> Bitte geben Sie eine gültige Telefonnummer ein.</span>');
                }
            } else {
                jQuery('#billing_phone').next('.error').remove();
                // Save the full international number
                jQuery('#billing_phone').val(iti.getNumber());
            }
        }

        if (jQuery('#billing_email').val() === "") {
        
            jQuery('.errors.email_err').text("Das Feld E-Mail-Adresse ist ein Pflichtfeld.");
            jQuery('.errors.email_err').show();
            jQuery('.woocommerce-additional-fields__field-wrapper').hide();
            isValid = false;
        
        } else if (!emailRegex.test(jQuery('#billing_email').val())) {
        
            jQuery('.errors.email_err').text("Bitte geben Sie eine gültige E-Mail-Adresse ein.");
            jQuery('.errors.email_err').show();
            jQuery('.woocommerce-additional-fields__field-wrapper').hide();
            isValid = false;
        
        } else if (isfvalidate === true) {

            jQuery('.errors.email_err').text("Please enter required fields.");
            jQuery('.errors.email_err').show();
            isValid = false; 

        // } else if (!jQuery(istcvalid).is(":checked")) {

        //     jQuery('.errors.billing_check_err').text("Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.");
        //     jQuery('.errors.billing_check_err').show();
        //     isValid = false; 

        } else if (fielderror == false) {
            isValid = false; 
        } else {
            jQuery('.errors.email_err').hide();
            jQuery('.errors.billing_check_err').hide();
            jQuery('.form-loader').show();
            setTimeout(function () {
                jQuery('.form-loader').hide();
            }, 1000);
            jQuery("#billing_email_field, .proceed-email-address").hide();
            jQuery('.eamil_value').html(email + '<a href="javascript:void(0);" class="email-edit">Edit</a>');
            jQuery('.woocommerce-additional-fields__field-wrapper').show();
        }
    });

    jQuery('body').on('click', '.email-edit', function (e) {
        jQuery('.form-loader').show();
        jQuery("#billing_email_field, .proceed-email-address").show();
        jQuery('.woocommerce-additional-fields__field-wrapper').hide();
        jQuery('.eamil_value').text('');
        setTimeout(function () {
            jQuery('.form-loader').hide();
        }, 1000);
    });
});


const cartPricing = document.getElementById('cart_popup');

function openCartPopup(button) {

    // Show the popup            
    cartPricing.style.display = 'flex';
    setTimeout(() => { cartPricing.classList.add('show'); }, 10);
}

function closeCartPopup() {       
         
    cartPricing.classList.remove('show');
    jQuery('body').removeClass('body_fix');
    setTimeout(() => { cartPricing.style.display = 'none'; }, 300);
}

// Close modal on outside click
document.addEventListener('click', function (e) {
    if (e.target.id === 'cart_popup') { closeCartPopup(); }
});

jQuery(document).ready(function($) {
    $('form.checkout_form').on('submit', function(e) {
        // Check if the checkbox is checked
        if (!$('#terms').prop('checked')) {
            // Prevent form submission
            e.preventDefault();
            
            // Check if the error label is already added, to avoid duplication
            if ($('#terms-error').length === 0) {
                // Append an error label after the checkbox
                $('#terms').closest('p.validate-required')
                    .append('<span id="terms-error" class="error billing_check_err">Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.</span>');
                // $('#terms').closest('.woocommerce-terms-and-conditions-wrapper .validate-required')
                //     .after('<span id="terms-error" class="error">Bitte akzeptieren Sie die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie.</span>');
            }
        } else {
            // Remove error message if the checkbox is checked
            $('#terms-error').remove();
        }
    });
});

/***** END CHECKOUT PAGE *****/


jQuery(document).ready(function ($) {
    // When the coupon form is submitted

    jQuery.validator.addMethod("filesize_max", function (value, element, param) {
		return this.optional(element) || (element.files[0].size <= param);
	});
	var maxSize = 5 * 1024 * 1024;

    $('form.checkout_coupon').on('submit', function () {
        // e.preventDefault();

        // Hide all error notices first
        $('.coupon-error-notice').each(function () {
            $(this).hide();
        });

        // Show only the last error notice
        // $('.coupon-error-notice').last().show();
    });

    // Multistep Js
	jQuery('.first_step_btn').on('click', function () {
		jQuery('.first_section_dv').hide();
		jQuery('.multisteps_form_main_dv').show();
		jQuery('.main_page_title .second').show();
		jQuery('.main_page_title .first').hide();
	});
	jQuery('.back_first_step').on('click', function () {
		jQuery('.multisteps_form_main_dv').hide();
		jQuery('.first_section_dv').show();
		jQuery('.main_page_title .second').hide();
		jQuery('.main_page_title .first').show();
	});

	// On calender date select add date in select 

	jQuery("#calendar-icon").click(function () {
		jQuery("#datepicker").datepicker("show");
	});

	jQuery("#datepicker").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: "yy-mm-dd",
		yearRange: "1960:" + new Date().getFullYear(),
		minDate: 0, // Disable past dates
		onSelect: function (dateText) {
			const date = new Date(dateText);
			const day = date.getDate();
			const month = date.getMonth() + 1;
			const year = date.getFullYear();

			jQuery("#available_date").val(day);
			jQuery("#available_month").val(month);
			jQuery("#available_year").val(year);

			jQuery('.errors.available_date_err').hide();
		}
	});

	// Other Achivement functionality

	var fileInputCount = 0;

	jQuery('#addFileInputButton').click(function (e) {
		e.preventDefault();
		fileInputCount++;
		var fileInputHTML = '<div class="file-input-container" id="fileInputContainer' + fileInputCount + '">' +
			'<label><input type="file" name="fileInput[]" onchange="handleFileSelect(event, ' + fileInputCount + ')" id="other_file_name' + fileInputCount + '"></label>' +
			'<div class="file-name-show">' +
			'<div class="file-name" id="fileName' + fileInputCount + '"></div>' +
			'<span class="remove-icon" onclick="removeFileInput(' + fileInputCount + ')"><i class="far fa-trash-can"></i></span>' +
			'</div>' +
			'</div>';
		jQuery('#fileInputsContainer').append(fileInputHTML);
	});

	//  Remove file after upload
	/* For Resume */
	jQuery('#resume').on('change', function () {
		if (jQuery(this).val()) {
			jQuery('#remove-resume-button').show();
		}
	});
	jQuery('#remove-resume-button').on('click', function (e) {
		e.preventDefault();
		jQuery('#resume').val('');
		jQuery('#remove-resume-button').hide();
		var fileUploadDiv = jQuery(this).closest('.file-upload');
		fileUploadDiv.removeClass('uploaded');
	});

	/* For Leaving Certificate */
	jQuery('#leaving_certificate').on('change', function () {
		if (jQuery(this).val()) {
			jQuery('#remove-leaving-button').show();
		}
	});
	jQuery('#remove-leaving-button').on('click', function (e) {
		e.preventDefault();
		jQuery('#leaving_certificate').val('');
		jQuery('#remove-leaving-button').hide();
		var fileUploadDiv1 = jQuery(this).closest('.file-upload');
		fileUploadDiv1.removeClass('uploaded');
	});

	/* For Training Certificate */
	jQuery('#training_certificate').on('change', function () {
		if (jQuery(this).val()) {
			jQuery('#remove-training-button').show();
		}
	});
	jQuery('#remove-training-button').on('click', function (e) {
		e.preventDefault();
		jQuery('#training_certificate').val('');
		jQuery('#remove-training-button').hide();
		var fileUploadDiv2 = jQuery(this).closest('.file-upload');
		fileUploadDiv2.removeClass('uploaded');
	});

	// For Reference Doc
	jQuery('#references_doc').on('change', function () {
		if (jQuery(this).val()) {
			jQuery('#remove-reference-button').show();
		}
	});
	jQuery('#remove-reference-button').on('click', function (e) {
		e.preventDefault();
		jQuery('#references_doc').val('');
		jQuery('#remove-reference-button').hide();
		var fileUploadDiv3 = jQuery(this).closest('.file-upload');
		fileUploadDiv3.removeClass('uploaded');
	});

	// Form Submit Ajax Function
	jQuery('#msform').validate({
		ignore: [],
		rules: {
			resume: {
				required: true,
				extension: "pdf",
				filesize_max: maxSize,
			},
			leaving_certificate: {
				required: true,
				extension: "pdf",
				filesize_max: maxSize,
			},
			training_certificate: {
				required: true,
				extension: "pdf",
				filesize_max: maxSize,
			},
			references_doc: {
				required: true,
				extension: "pdf",
				filesize_max: maxSize,
			}
		},
		messages: {
            resume: {
                required: "Bitte laden Sie Ihren Lebenslauf einschließlich Passfoto hoch.",
                extension: "Bitte wählen Sie eine PDF-Datei aus.",
                filesize_max: "Die Dateigröße überschreitet das zulässige Limit (5 MB). Bitte wählen Sie eine kleinere Datei.",
            },
            leaving_certificate: {
                required: "Bitte laden Sie Ihr Abschlusszeugnis der Oberschule/Berufsschule hoch.",
                extension: "Bitte wählen Sie eine PDF-Datei aus.",
                filesize_max: "Die Dateigröße überschreitet das zulässige Limit (5 MB). Bitte wählen Sie eine kleinere Datei.",
            },
            training_certificate: {
                required: "Bitte laden Sie hier Ihre neuesten Ausbildungsnachweise hoch.",
                extension: "Bitte wählen Sie eine PDF-Datei aus.",
                filesize_max: "Die Dateigröße überschreitet das zulässige Limit (5 MB). Bitte wählen Sie eine kleinere Datei.",
            },
            references_doc: {
                required: "Bitte laden Sie Ihre neuesten Referenzen hoch.",
                extension: "Bitte wählen Sie eine PDF-Datei aus.",
                filesize_max: "Die Dateigröße überschreitet das zulässige Limit (5 MB). Bitte wählen Sie eine kleinere Datei.",
            }
        },
		errorPlacement: function (error, element) {
			if (element.attr("name") == "resume") {
				error.insertAfter("#remove-resume-button");
			} else if (element.attr("name") == "leaving_certificate") {
				error.insertAfter("#remove-leaving-button");
			} else if (element.attr("name") == "training_certificate") {
				error.insertAfter("#remove-training-button");
			} else if (element.attr("name") == "references_doc") {
				error.insertAfter("#remove-reference-button");
			} else {
				error.insertAfter(element);
			}
		},
		submitHandler: function (form, event) {
			event.preventDefault();
			jQuery('.site-loader').show();
			// var formData = jQuery('#msform').serialize();
			let form1 = document.getElementById('msform');
			var formData = new FormData(form1);
			formData.append('action', 'multistep_form_func');
			var ajaxurl = frontend_ajax.ajaxurl;
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						jQuery('.site-loader').hide();
						jQuery('.thank_you_page').show();
						jQuery('#cv_and_certificates').addClass('completed');
						jQuery('fieldset').hide();
					} else {
						console.log(response.message || 'An error occurred. Please try again.');
					}
				},
				error: function (xhr, status, error) {

					console.error(error);
				}
			});
		}
	});

	// Hide validation message
	// var nameRegex = /^[a-zA-Zà-žÀ-Ž\s]+$/;
    var nameRegex = /^[a-zA-Z\u00C0-\u017F]+(?:\s[a-zA-Z\u00C0-\u017F]+)*$/u;
    jQuery('.first_name, .last_name').on('keyup', function () {
        var firstName = jQuery('.first_name').val().trim();
        var lastName = jQuery('.last_name').val().trim();

        // Check if either first name or last name is empty
        if (firstName === "" || lastName === "") {
            jQuery('.errors.first_err').text("Bitte geben Sie Ihren Namen ein.");
            jQuery('.errors.first_err').show();
        }
        // Check if the first name or last name contains numbers
        else if (/\d/.test(firstName) || /\d/.test(lastName)) {
            jQuery('.errors.first_err').text("Bitte geben Sie einen gültigen Namen ein.");
            jQuery('.errors.first_err').show();
        }
        // Check if the first name or last name contains invalid characters
        else if (!nameRegex.test(firstName) || !nameRegex.test(lastName)) {
            jQuery('.errors.first_err').text("Bitte geben Sie einen gültigen Namen ein.");
            jQuery('.errors.first_err').show();
        }
        else {
            jQuery('.errors.first_err').hide();
        }
    });



	var mobileNumberRegex = /^[0-9]{10}$/;
	jQuery('.mobile_number').on('keyup', function () {
		if (jQuery(this).val() === "") {
			jQuery('.errors.mobile_number_err').text("Bitte geben Sie Ihre Mobilnummer ein.");
			jQuery('.errors.mobile_number_err').show();
		} else if (!mobileNumberRegex.test(jQuery(this).val())) {
			jQuery('.errors.mobile_number_err').text("Bitte geben Sie eine gültige Mobilnummer ein.");
			jQuery('.errors.mobile_number_err').show();
		} else {
			jQuery('.errors.mobile_number_err').hide();
		}
	});

	var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	jQuery('.email').on('keyup', function () {
		if (jQuery('.email').val() === "") {
			jQuery('.errors.email_err').text("Bitte geben Sie Ihre E-Mail-Adresse ein.");
			jQuery('.errors.email_err').show();
			isValid = false;
		} else if (!emailRegex.test(jQuery('.email').val())) {
			jQuery('.errors.email_err').text("Bitte geben Sie eine gültige E-Mail-Adresse ein.");
			jQuery('.errors.email_err').show();
			isValid = false;
		} else {
			jQuery('.errors.email_err').hide();
		}
	});
	jQuery('.approx_hours').on('keyup', function () {
		var hours = jQuery(this).val();
		if (hours === "" || isNaN(hours) || hours < 0) {
			jQuery('.errors.approx_hours_err').text("Bitte geben Sie eine gültige Stundenzahl ein.");
			jQuery('.errors.approx_hours_err').show();
		} else {
			jQuery('.errors.approx_hours_err').hide();
		}
	});

	jQuery('input[name="gender"]').on('change', function () {
		jQuery('.errors.gender_err').hide();
	});
	jQuery('input[name="smartphone"]').on('change', function () {
		jQuery('.errors.smartphone_err').hide();
	});
	jQuery('.find_us').on('change', function () {
		if (jQuery(this).val() != '') {
			jQuery('.errors.find_us_err').hide();
		} else {
			jQuery('.errors.find_us_err').show();
		}
	});
	jQuery('.data_protect').on('change', function () {
		var data = jQuery('input[name="data_protect"]:checked').length;
		if (data === 0) {
			jQuery('.errors.data_protect_err').show();
		} else {
			jQuery('.errors.data_protect_err').hide();
		}
	});

	// Second Step    
	validateInput('.last_training', '.errors.last_training_err');
	validateInput('.tutor_work', '.errors.tutor_work_err');
	validateInput('.tutor_experience', '.errors.tutor_experience_err');
	validateInput('.tutor_skills', '.errors.tutor_skills_err');
	validateInput('.before_first_lesson', '.errors.before_first_lesson_err');
	validateInput('.moral_view', '.errors.moral_view_err');
	validateInput('.approx_hours', '.errors.approx_hours_err');

	jQuery('.available_date, .available_month, .available_year').on('change', function () {
		checkSelectBoxes();
	});

	jQuery('input[name="how_long_teach"]').on('change', function () {
		jQuery('.errors.how_long_teach_err').hide();
	});
	jQuery('input[name="hour_per_week"]').on('change', function () {
		jQuery('.errors.hour_per_week_err').hide();
	});
	jQuery('input[name="assignments"]').on('change', function () {
		var data = jQuery('input[name="assignments"]:checked').length;
		if (data === 0) {
			jQuery('.errors.assignments_err').show();
		} else {
			jQuery('.errors.assignments_err').hide();
		}
	});

    jQuery('input[name="educational_paths"]').on('change', function () {
		var data = jQuery('input[name="educational_paths"]:checked').length;
		if (data === 0) {
			jQuery('.errors.educational_paths_err').show();
		} else {
			jQuery('.errors.educational_paths_err').hide();
		}
	});


	// jQuery('input[name="educational_paths"]').on('change', function () {
	// 	jQuery('.errors.educational_paths_err').hide();
	// });
	jQuery('input[name="german_skills"]').on('change', function () {
		jQuery('.errors.german_skills_err').hide();
	});
	jQuery('input[name="gernam_primary"], input[name="english_primary"], input[name="french_primary"], input[name="maths_primary"]').on('change', function () {
		validatePrimaryButtons();
	});
	jQuery('input[name="gernam_secondary"], input[name="english_secondary"], input[name="french_secondary"], input[name="maths_secondary"], input[name="latin_secondary"], input[name="organic_secondary"], input[name="chemistry_secondary"], input[name="physics_secondary"]').on('change', function () {
		validateSecondaryButtons();
	});
	jQuery('input[name="gernam_matura"], input[name="english_matura"], input[name="french_matura"], input[name="maths_matura"], input[name="latin_matura"], input[name="organic_matura"], input[name="chemistry_matura"], input[name="story_matura"], input[name="geography_matura"], input[name="accounting_matura"], input[name="physics_matura"]').on('change', function () {
		validateMaturaButtons();
	});

	//  File Upload
	jQuery('.resume').on('change', function () {
		if (this.files && this.files.length > 0) {
			jQuery(this).closest('.file-upload').addClass('uploaded');
		}
	});

	jQuery('.leaving_certificate').on('change', function () {
		if (this.files && this.files.length > 0) {
			jQuery(this).closest('.file-upload').addClass('uploaded');
		}
	});

	jQuery('.training_certificate').on('change', function () {
		if (this.files && this.files.length > 0) {
			jQuery(this).closest('.file-upload').addClass('uploaded');
		}
	});

	jQuery('.references_doc').on('change', function () {
		if (this.files && this.files.length > 0) {
			jQuery(this).closest('.file-upload').addClass('uploaded');
		}
	});

});

function removeFileInput(id) {
	jQuery('#fileInputContainer' + id).remove();
}
function validateInput(inputClass, errorClass) {
	jQuery(inputClass).on('blur', function () {
		if (jQuery(this).val() === '') {
			jQuery(errorClass).show();
		} else {
			jQuery(errorClass).hide();
		}
	});
}
function checkSelectBoxes() {
	var allSelected = jQuery('.available_date').val() !== '' &&
		jQuery('.available_month').val() !== '' &&
		jQuery('.available_year').val() !== '';

	if (allSelected) {
		jQuery('.errors.available_date_err').hide();
	} else {
		jQuery('.errors.available_date_err').show();
	}
}
function validatePrimaryButtons() {
	var allSelected = jQuery('input[name="gernam_primary"]:checked').length > 0 &&
		jQuery('input[name="english_primary"]:checked').length > 0 &&
		jQuery('input[name="french_primary"]:checked').length > 0 &&
		jQuery('input[name="maths_primary"]:checked').length > 0;

	if (allSelected) {
		jQuery('.errors.primary_err').hide();
	} else {
		jQuery('.errors.primary_err').show();
	}
}

function validateSecondaryButtons() {
	var allSelected = jQuery('input[name="gernam_secondary"]:checked').length > 0 &&
		jQuery('input[name="english_secondary"]:checked').length > 0 &&
		jQuery('input[name="french_secondary"]:checked').length > 0 &&
		jQuery('input[name="maths_secondary"]:checked').length > 0 &&
		jQuery('input[name="latin_secondary"]:checked').length > 0 &&
		jQuery('input[name="organic_secondary"]:checked').length > 0 &&
		jQuery('input[name="chemistry_secondary"]:checked').length > 0 &&
		jQuery('input[name="physics_secondary"]:checked').length > 0;

	if (allSelected) {
		jQuery('.errors.secondary_error').hide();
	} else {
		jQuery('.errors.secondary_error').show();
	}
}

function validateMaturaButtons() {
	var allSelected = jQuery('input[name="gernam_matura"]:checked').length > 0 &&
		jQuery('input[name="english_matura"]:checked').length > 0 &&
		jQuery('input[name="french_matura"]:checked').length > 0 &&
		jQuery('input[name="maths_matura"]:checked').length > 0 &&
		jQuery('input[name="latin_matura"]:checked').length > 0 &&
		jQuery('input[name="organic_matura"]:checked').length > 0 &&
		jQuery('input[name="chemistry_matura"]:checked').length > 0 &&
		jQuery('input[name="story_matura"]:checked').length > 0 &&
		jQuery('input[name="geography_matura"]:checked').length > 0 &&
		jQuery('input[name="accounting_matura"]:checked').length > 0 &&
		jQuery('input[name="physics_matura"]:checked').length > 0;

	if (allSelected) {
		jQuery('.errors.matura_error').hide();
	} else {
		jQuery('.errors.matura_error').show();
	}
}
function handleFileSelect(event, id) {
	const input = event.target;
	const fileName = input.files[0].name;
	const fileNameElement = document.getElementById('fileName' + id);

	input.style.display = 'none';

	fileNameElement.textContent = fileName;
}

/***** TO ADD LESSONS ON THE ONE DIV *****/
jQuery(document).ready(function($) {
    // Select all heading divs with the class 'ld-item-list-section-heading'
    $('.ld-item-list-section-heading').each(function(index) {
        // Wrap the section with a new div 'content-listing-started'
        $(this).before('<div class="course-listing-section"></div>');

        // Move current heading and its next sibling (lessons) inside the new div
        $(this).nextUntil('.ld-item-list-section-heading').addBack().appendTo($('.course-listing-section').last());
    });


    // $(".increase").click(function () {
    //     let input = $(this).siblings(".customNumberInput");
    //     let value = parseFloat(input.val());
    //     let step = parseFloat(input.attr("step"));
    //     let max = parseFloat(input.attr("max"));

    //     if (value + step <= max) {
    //         input.val((value + step).toFixed(1)); // Set value with one decimal place
    //     }
    // });

    // $(".decrease").click(function () {
    //     let input = $(this).siblings(".customNumberInput");
    //     let value = parseFloat(input.val());
    //     let step = parseFloat(input.attr("step"));
    //     let min = parseFloat(input.attr("min"));

    //     if (value - step >= min) {
    //         input.val((value - step).toFixed(1)); // Set value with one decimal place
    //     }
    // });

    $(".increase").click(function () {
        let input = $(this).siblings(".customNumberInput");
        let value = parseFloat(input.val());
        let step = 0.1; // Always use step = 0.1
        let max = parseFloat(input.attr("max"));

        if (isNaN(value) || value === 0) {
            value = 0; // Default to 0 if empty
            input.val((value + 1).toFixed(1)); // First click adds 1
        } else {
            let newValue = value + step;
            if (newValue <= max || isNaN(max)) {
                input.val(newValue.toFixed(1)); // Add 0.1 on subsequent clicks
            }
        }
    });

    $(".decrease").click(function () {
        let input = $(this).siblings(".customNumberInput");
        let value = parseFloat(input.val());
        let step = 0.1;
        let min = parseFloat(input.attr("min"));

        if (isNaN(value)) {
            value = 1; // Default to 1 if empty or invalid
        }

        let newValue = value - step;
        if (newValue >= min || isNaN(min)) {
            input.val(newValue.toFixed(1)); // Subtract 0.1
        }
    });

});

jQuery(document).ready(function($) {
    // Get the current post ID
    var currentPostId = 12680;
    
    // Check if we're on the correct post
    if ($('body').hasClass('page-id-' + currentPostId)) {
        
          // Get URL parameters
        var urlParams = new URLSearchParams(window.location.search);
        
        // Check if ONLY mc_group_id parameter exists
        if (urlParams.has('mc_group_id') && urlParams.toString().split('&').length === 1) {
            // Scroll to the course listing section
            $('html, body').animate({
                scrollTop: $('.course_listing_wrap').offset().top - 100 // 100px offset for header
            }, 1000);
        }
    }
});


jQuery(document).ready(function($) {
    $('.learndash-cpt-sfwd-quiz-74941-current .wpProQuiz_content,.learndash-cpt-sfwd-quiz-74935-current .wpProQuiz_content').on('questionSolvedIncorrect', function(e) {
		const index = e.values.index;
        const $question = $('.wpProQuiz_listItem').eq(index);

        $('body').removeClass('mc-bigger-fill-in-blanks-inputs');

        $question.find('.wpProQuiz_cloze').css({
            height: '80px',
            width: '100%',
        });
        
        $question.find('.wpProQuiz_clozeCorrect').css({
            'white-space': 'break-spaces',
            overflow: 'hidden'
        });
		
		$question.find('.wpProQuiz_questionListItem ').css({
			'overflow': 'hidden'
		});
    });
});

jQuery(document).ready(function($) {
    $('#form-field-course').select2({
        width: '100%',        
        minimumResultsForSearch: Infinity,
        dropdownParent: $('#brxe-axfyhv')
    });
    
    /*$('.banner_form').on('submit', function(e) {
        e.preventDefault();
        var selectedUrl = $('#form-field-course').val();
        if (selectedUrl) {
            window.location.href = selectedUrl;
        }
    });*/
});


// On apply or remove coupon page reload
jQuery(document).ajaxComplete(function(event, xhr, settings) {
    // Apply coupon
    if (settings.url.indexOf('wc-ajax=apply_coupon') !== -1) {
        if (xhr.responseText.includes('woocommerce-message')) {
            setTimeout(function() {
                location.reload();
            }, 2000); // 2-second delay
        }
    }

    // Remove coupon
    if (settings.url.indexOf('wc-ajax=remove_coupon') !== -1) {
        if (xhr.responseText.includes('woocommerce-message')) {
            setTimeout(function() {
                location.reload();
            }, 2000); // 2-second delay
        }
    }
});



/*-- DYNAMIC AVATAR IMAGE --*/
jQuery(document).ready(function($) {
    $("body").delegate("#save_svg", "click", function () {
        
        var svgElement = document.getElementById('skin');
        
        if (!svgElement) {
            console.log("SVG element not found!");
            return;
        }

        var svgData     = new XMLSerializer().serializeToString(svgElement);
        var encodedSVG  = btoa(unescape(encodeURIComponent(svgData))); 
        var ajaxurl     = frontend_ajax.ajaxurl;
        $.ajax({
            url: ajaxurl,
            method: "POST",
            data: {
                action: "save_svg_to_server",
                svg_content: encodedSVG,
                nonce: frontend_ajax.nonce,
            },
            success: function (response) {

                console.log(response);
                console.log('response');

                if (response.success) {

                    console.log("SVG saved as: " + response.data.filename);

                    var imgUrl = response.data.url;
                    jQuery('.profile-pic').attr('src', imgUrl);
                    jQuery('.user_profile .brx-submenu-toggle span img').attr('src', imgUrl);
                    jQuery('#closePopup').click();
                } else {
                    console.log("Error: " + (response.data.message || "Failed"));
                }
            },
            error: function () {
                console.log("AJAX failed.");
            }
        });
    });
});



/* ---------------------------------
    JavaScript: AJAX removal and price update =================================================
--------------------------------- */

jQuery(function($){
  $('body').on('click', '.product-checkout .remove', function(e){
    e.preventDefault();
    jQuery('.site-loader').show();
    var $link   = $(this),
        href    = $link.attr('href'),
        key     = new URLSearchParams( href.split('?')[1] ).get('remove_item');

    if (! key) return;

    $.post(
      frontend_ajax.ajaxurl,
      {
        action:        'asp_remove_cart_item',
        cart_item_key: key
      },
      function(res){
        if (res.success) {
            // 1) Remove the product block
            $link.closest('.cart_item').slideUp(300, function(){
              $(this).remove();
            });

            // 2) Update Zwischensumme
            if (res.data.subtotal) {
              $('#order_review .cart-subtotal p:last').html(res.data.subtotal);
            }

            // 3) Update Gesamt
            if (res.data.total) {
              $('#order_review .order-total .order_total_price').html(
                '<strong>' + res.data.total + '</strong>'
              );
            }

            // 4) Redirect if cart is empty
            if (res.data.redirect) {
              window.location.href = res.data.redirect;
            }

            jQuery('.site-loader').hide();
        }
      },
      'json'
    );
  });
});



/*jQuery(document).ready(function($) {
    // Only proceed on this specific course page
    if ($('body').hasClass('single-sfwd-courses') && $('body').hasClass('postid-71325')) {
        
        // Get text inside the back-to-group link
        var backLinkText = $('.go_back_to_group a').text().trim();

        // Extract only the group name (remove "Gehe zurück" if needed)
        var groupName = backLinkText.replace("Gehe zurück", "").trim();

        // Update the h2 heading with the extracted group name
        $('.ld-section-heading h2').text(groupName);
    }
});
*/

const mutationObserver = new MutationObserver(function(mutations, obs) {
    const backLink = document.querySelector('.go_back_to_group a');
    const heading = document.querySelector('.ld-section-heading h2');

    if (backLink && heading) {
        let groupName = backLink.textContent.replace("Gehe zurück", "").trim();
        heading.textContent = groupName;

        obs.disconnect(); // use the local observer here
    }
});

mutationObserver.observe(document.body, {
    childList: true,
    subtree: true
});


/*(function($) { 
    'use strict';

    $(document).ready(function() {

        // Run only on body with both classes
        if ($('body').hasClass('page-id-23286') && $('body').hasClass('page-ims-vorbereitung')) {

            const groupSelect = $('select[name="f-group"]');
            const daysSelect = $('select[name="f-access-days"]');
            const imsGroupId = '80847'; // ZAP-IMS group ID
            const option180Value = '180';

            // Function to get current language from HTML or Weglot
            function getCurrentLanguage() {
                if (window.Weglot && Weglot.getCurrentLang) {
                    return Weglot.getCurrentLang();
                }
                return $('html').attr('lang') || 'de';
            }

            // Function to get translated text
            function getTranslatedText(key) {
                const translations = {
                    '180_days': {
                        'de': '180 Tage Zugang',
                        'fr': '180 jours d\'accès',
                        'en': '180 days access',
                        'it': '180 giorni di accesso'
                    },
                    'until_zap_2026': {
                        'de': 'Bis zur ZAP 2026',
                        'fr': 'Jusqu\'à ZAP 2026',
                        'en': 'Until ZAP 2026',
                        'it': 'Fino a ZAP 2026'
                    }
                };
                
                const lang = getCurrentLanguage().split('-')[0]; // Handle 'it-IT' format
                return translations[key][lang] || translations[key]['en']; // Fallback to English
            }

            // Function to update the option text
            function updateOptionText() {
                const isImsGroup = (groupSelect.val() === imsGroupId);
                const option180 = daysSelect.find('option[value="' + option180Value + '"]');
                
                // Set the appropriate text based on group and language
                const newText = isImsGroup 
                    ? getTranslatedText('180_days')
                    : getTranslatedText('until_zap_2026');
                
                option180.text(newText);
            }

            // Function to update the option text
            // function updateOptionText() {
            //     const isImsGroup = (groupSelect.val() === imsGroupId);
            //     const option180 = daysSelect.find('option[value="' + option180Value + '"]');

            //     if (isImsGroup) {
            //         // Only update text if group = 80847
            //         option180.text(getTranslatedText('180_days'));
            //     // } else {
            //         // Reset to original/default text (so we don’t overwrite with "until_zap_2026")
            //         // option180.text('180'); // <-- Replace with your original option label if different
            //     }
            // }


            // Function to refresh the Select2 instance
            function refreshSelect2() {
                const currentValue = daysSelect.val();
                const isOpen = daysSelect.next('.select2-container').hasClass('select2-container--open');
                
                // Store Select2 configuration
                const select2Config = daysSelect.data('select2')?.options?.options || {};
                
                // Destroy and recreate Select2
                daysSelect.select2('destroy');
                daysSelect.select2(select2Config);
                
                // Restore selection
                if (currentValue) {
                    daysSelect.val(currentValue).trigger('change');
                }
                
                // Reopen if it was open
                if (isOpen) {
                    setTimeout(() => daysSelect.select2('open'), 100);
                }
            }

            // Main update function
            function updateAccessDaysOption() {
                updateOptionText();
                refreshSelect2();
                
                // If Weglot is available, trigger a reinitialization
                if (window.Weglot) {
                    Weglot.initialize();
                }
            }

            // Initialize on page load
            setTimeout(updateAccessDaysOption, 100);

            // Update when group changes
            groupSelect.on('change select2:select', updateAccessDaysOption);

            // Listen for Weglot language change
            $(document).on('onLanguageChanged onWeglotLanguageChanged', updateAccessDaysOption);
        }
    });

})(jQuery);*/


// ✅ Convert the <div id="brxe-thfusb"> element into an <a> tag on page load
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('brxe-thfusb');
  if (el) {
    el.outerHTML = el.outerHTML.replace(/^<div /, '<a ').replace(/<\/div>$/, '</a>');
  }
});



// Cookie popup handling ----------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const consentContainer = document.querySelector('.cky-consent-container');
    const overlay = document.querySelector('.cky-overlay');

    if (consentContainer && overlay) {
        // Manage cky-open class based on cky-hide presence
        if (!consentContainer.classList.contains('cky-hide')) {
            overlay.classList.add('cky-open');
        } else {
            overlay.classList.remove('cky-open');
        }

        // Add click event listeners to all consent buttons
        const buttons = consentContainer.querySelectorAll('.cky-btn-customize, .cky-btn-reject, .cky-btn-accept');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                overlay.classList.remove('cky-open');
            });
        });
    }
});


jQuery(document).ready(function($) {
    jQuery("#toggle-1").click(function(){
        jQuery("#toggle-2").removeClass('active');
        jQuery("#toggle-1").addClass('active');
        jQuery("#toggle-1.active + label span").text('Mit Timer');
        jQuery("#toggle-2 + label span").text('Ohne Timer');

    });
      
    jQuery("#toggle-2").click(function(){
        jQuery("#toggle-1").removeClass('active');
        jQuery("#toggle-2").addClass('active');
        // jQuery("#toggle-2.active + label span").text('Ohne Timer');
        // jQuery("#toggle-1 + label span").text('Mit Timer');
    });
});

// jQuery("#toggle-1").on("click", function(){
//     let $moving = $(this).find(".moving_txt");

//     // Add the class
//     $moving.addClass("animate");

//     // Remove it after 5 seconds
//     setTimeout(function(){
//       $moving.removeClass("animate");
//     }, 5000);
//   });