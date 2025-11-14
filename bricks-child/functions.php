<?php

use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;

// Include the shortcodes.php file from the inc folder
require_once get_stylesheet_directory() . '/inc/shortcodes.php';
require_once get_stylesheet_directory() . '/inc/shortcodes-2.php';


/**
 * Register/enqueue custom scripts and styles  -----------------------------------------------------------------------------------
 */
add_action('wp_enqueue_scripts', function () {
    // Explicitly enqueue jQuery
    wp_enqueue_script('jquery');

    wp_enqueue_script('jquery-ui-tooltip'); // Loads jQuery UI Tooltip
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'); // Optional CSS

    // Enqueue jQuery Validation plugin from CDN
    wp_enqueue_script(
        'jquery-validation',
        get_stylesheet_directory_uri() . '/assets/js/jquery.validate.min.js',
        ['jquery'],
        null,
        true // Load in footer
    );

    // Enqueue jQuery Validation plugin from CDN
    // wp_enqueue_script(
    //     'jquery-validation-min', 
    //     get_stylesheet_directory_uri() . '/assets/js/jquery-3.6.0.min.js', 
    //     ['jquery'], 
    //     null, 
    //     true // Load in footer
    // );

    wp_enqueue_script(
        'additional-methods-script',
        get_stylesheet_directory_uri() . '/assets/js/additional-methods.min.js',
        ['jquery'],
        null,
        true // Load in footer
    );


    // Enqueue select2 js plugin from CDN
    wp_enqueue_script(
        'select2-js',
        'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
        ['jquery'],
        null,
        true // Load in footer
    );

    // Enqueue select2 css plugin from CDN
    wp_enqueue_style(
        'select2-css',
        'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
        null,
        true // Load in footer
    );
    wp_enqueue_script('jquery-ui-js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js');

    // Enqueue custom validation script
    wp_enqueue_script(
        'custom-script',
        get_stylesheet_directory_uri() . '/assets/js/custom-script.js',
        ['jquery', 'jquery-validation'],
        // ['jquery', 'jquery-validation', 'select2-js'], // Dependencies
        filemtime(get_stylesheet_directory() . '/assets/js/custom-script.js'),
        true // Load in footer
    );

    wp_localize_script(
        'custom-script',
        'frontend_ajax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('profile_ajax_nonce')
        )
    );

    // Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
    if (! bricks_is_builder_main()) {
        wp_enqueue_style('bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime(get_stylesheet_directory() . '/style.css'), 'all');
    }

    // Enqueue the Ionicons stylesheet from the parent theme
    wp_enqueue_style(
        'bricks-ionicons', // Unique handle for the stylesheet
        get_stylesheet_uri() . '/assets/css/libs/ionicons.min.css', // Path to the stylesheet in the parent theme
        array(), // Dependencies (leave empty if none)
        rand(), // Version number (use the same version as in the parent theme)
        'all' // Media type
    );
}, 20);




/**
 * Webste performance start -----------------------------------------------------------------------------------
 */

// function defer_parsing_of_js($url) {
//     if (is_admin() || strpos($url, '.js') === false) {
//         return $url;
//     }
//     return str_replace(' src', ' defer src', $url);
// }
// add_filter('script_loader_tag', 'defer_parsing_of_js', 10, 1);

function disable_woocommerce_scripts()
{
    if (function_exists('is_woocommerce')) { // ❌ BUG: missing ')' causes fatal error
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
        }
    }
}
add_action('wp_enqueue_scripts', 'disable_woocommerce_scripts', 99);

// function add_lazy_loading_to_images($content) {
//     return str_replace('<img', '<img loading="lazy"', $content);
// }
// add_filter('the_content', 'add_lazy_loading_to_images');

function disable_woocommerce_cart_fragments()
{
    wp_dequeue_script('wc-cart-fragments');
}
add_action('wp_enqueue_scripts', 'disable_woocommerce_cart_fragments', 11);

function add_defer_attribute($tag, $handle)
{
    $scripts_to_defer = ['owl-carousel-js', 'custom-script']; // Remove 'jquery-validation' and 'select2-js'

    if (in_array($handle, $scripts_to_defer)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'add_defer_attribute', 10, 2);

// add_action('init', function() {
//     wp_deregister_script('heartbeat');
// });

// function remove_dashicons() {
//     if (!is_admin()) {
//         wp_deregister_style('dashicons');
//     }
// }
// add_action('wp_enqueue_scripts', 'remove_dashicons');


function fix_jquery_passive_touchstart()
{
    $script = <<<JS
    jQuery.event.special.touchstart = {
        setup: function(_, ns, handle) {
            this.addEventListener("touchstart", handle, { passive: true });
        }
    };
JS;
    wp_add_inline_script('jquery', $script);
}
add_action('wp_enqueue_scripts', 'fix_jquery_passive_touchstart');


// function add_lazy_loading_to_images($content) {
//     // Add lazy loading attribute to images
//     $content = preg_replace('/<img(.*?)src=/', '<img$1 loading="lazy" src=', $content);
//     return $content;
// }
// add_filter('the_content', 'add_lazy_loading_to_images');
function add_aria_label_to_links_and_buttons($buffer)
{
    // Handle <a> tags
    $buffer = preg_replace_callback(
        '/<a\s([^>]*?)href=["\']([^"\']+)["\']([^>]*)>(.*?)<\/a>/i',
        function ($matches) {
            $before_href = trim($matches[1]);
            $href_value = $matches[2];
            $after_href = trim($matches[3]);
            $link_text = trim(strip_tags($matches[4]));
            $aria_label = 'Read more about ' . $link_text;

            if (strpos($before_href . $after_href, 'aria-label=') === false) {
                $after_href .= ' aria-label="' . esc_attr($aria_label) . '"';
            }

            return '<a ' . $before_href . ' href="' . $href_value . '" ' . $after_href . '>' . $matches[4] . '</a>';
        },
        $buffer
    );

    // Handle <button> tags
    $buffer = preg_replace_callback(
        '/<button\s([^>]*)>(.*?)<\/button>/i',
        function ($matches) {
            $before_text = trim($matches[1]);
            $button_text = trim(strip_tags($matches[2]));
            $aria_label = 'Click to ' . $button_text;

            if (strpos($before_text, 'aria-label=') === false) {
                $before_text .= ' aria-label="' . esc_attr($aria_label) . '"';
            }

            return '<button ' . $before_text . '>' . $matches[2] . '</button>';
        },
        $buffer
    );

    return $buffer;
}

// function start_buffering() {
//     ob_start('add_aria_label_to_links_and_buttons');
// }

// function end_buffering() {
//     ob_end_flush();
// }

// add_action('wp_head', 'start_buffering');
// add_action('wp_footer', 'end_buffering');

// function add_aria_label_to_buttons($buffer) {
//     $buffer = preg_replace_callback(
//         '/<button\s([^>]*)>(.*?)<\/button>/is',
//         function ($matches) {
//             $before_text = trim($matches[1]);
//             $button_text = trim(strip_tags($matches[2])); // Extract button text
//             $aria_label = 'Click to ' . $button_text;

//             // Only add aria-label if it doesn't already exist
//             if (!preg_match('/aria-label=[\'"]/', $before_text)) {
//                 $before_text .= ' aria-label="' . esc_attr($aria_label) . '"';
//             }

//             return '<button ' . $before_text . '>' . $matches[2] . '</button>';
//         },
//         $buffer
//     );

//     return $buffer;
// }

// // Apply the buffer filter on the frontend
// function buffer_start() { ob_start('add_aria_label_to_buttons'); }
// function buffer_end() { ob_end_flush(); }

// add_action('wp_head', 'buffer_start', 1);
// add_action('wp_footer', 'buffer_end', 1);


function delay_all_js_execution()
{
    echo '<script>
        function loadScripts() {
            var scripts = document.querySelectorAll("script[type=\'lazyload\']");
            scripts.forEach(script => {
                var newScript = document.createElement("script");
                newScript.src = script.getAttribute("data-src");
                document.body.appendChild(newScript);
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(loadScripts, 3000); // 3 seconds delay
        });

        document.addEventListener("click", loadScripts);
        document.addEventListener("scroll", loadScripts);
        document.addEventListener("mousemove", loadScripts);
    </script>';
}
add_action('wp_footer', 'delay_all_js_execution', 100);
function delay_all_css_loading()
{
    echo '<script>
        setTimeout(function() {
            document.querySelectorAll("link[rel=\'stylesheet\']").forEach(link => {
                link.rel = "stylesheet";
            });
        }, 3000); // Delay CSS by 3 seconds
    </script>';
}
add_action('wp_footer', 'delay_all_css_loading', 100);


// function dynamic_preload_assets() {
//     ob_start(); // Start output buffering
// }
// add_action('wp_head', 'dynamic_preload_assets', 1);

// function insert_dynamic_preloads() {
//     $content = ob_get_clean(); // Get the entire page content

//     // Match CSS, JS, and font files found in <link> or <script> tags
//     preg_match_all('/<link[^>]+href=[\'"]([^\'"]+\.(css|woff2|woff|ttf))[\'"][^>]*>/i', $content, $css_matches);
//     preg_match_all('/<script[^>]+src=[\'"]([^\'"]+\.(js))[\'"][^>]*>/i', $content, $js_matches);

//     $preload_links = [];

//     // Preload CSS and Fonts
//     foreach ($css_matches[1] as $css_file) {
//         $preload_links[] = '<link rel="preload" href="' . esc_url($css_file) . '" as="style">';
//     }

//     // Preload JS
//     foreach ($js_matches[1] as $js_file) {
//         $preload_links[] = '<link rel="preload" href="' . esc_url($js_file) . '" as="script">';
//     }

//     // Output preloads in wp_head
//     if (!empty($preload_links)) {
//         echo implode("\n", $preload_links) . "\n";
//     }
// }
// add_action('wp_head', 'insert_dynamic_preloads', 2);


/**
 * Webste performance end -----------------------------------------------------------------------------------
 */




/**
 * Register custom elements  -----------------------------------------------------------------------------------
 */
add_action('init', function () {
    $element_files = [
        __DIR__ . '/elements/title.php',
    ];

    foreach ($element_files as $file) {
        \Bricks\Elements::register_element($file);
    }
}, 11);

/**
 * Add text strings to builder
 */
add_filter('bricks/builder/i18n', function ($i18n) {
    // For element category 'custom'
    $i18n['custom'] = esc_html__('Custom', 'bricks');

    return $i18n;
});


// Include the custom post type and shortcode code  -----------------------------------------------------------------------------------
require get_stylesheet_directory() . '/inc/custom-post/team.php';


// To include page name as a class in the body of the page  -----------------------------------------------------------------------------------
function add_page_name_to_body_class($classes)
{
    // Check if it's a singular page
    if (is_singular()) {
        global $post;
        // Add the page slug as a class
        $classes[] = 'page-' . sanitize_title($post->post_name);
    } elseif (is_front_page()) {
        // Add 'home' class for the front page
        $classes[] = 'page-home';
    } elseif (is_archive()) {
        // Add archive-specific class
        $classes[] = 'page-archive';
    } elseif (is_search()) {
        // Add search-specific class
        $classes[] = 'page-search';
    }

    return $classes;
}
add_filter('body_class', 'add_page_name_to_body_class');


// To include class of learndash in body of the page  -----------------------------------------------------------------------------------
function add_learndash_body_class($classes)
{
    // Check if the current page is a LearnDash-related page
    if (function_exists('is_singular') && (is_singular('sfwd-courses') || is_singular('sfwd-lessons') || is_singular('sfwd-topic'))) {
        $classes[] = 'learndash-lms-page';
    }

    return $classes;
}
add_filter('body_class', 'add_learndash_body_class');


// To logout and redirect on the homepage  -----------------------------------------------------------------------------------
function custom_admin_logout_url($logout_url, $redirect)
{
    // Check if the user is on the admin side
    // if (is_admin()) {
    $redirect = home_url(); // Redirect to the homepage
    $logout_url = wp_nonce_url(site_url('wp-login.php?action=logout'), 'log-out') . '&redirect_to=' . urlencode($redirect);
    // }
    return $logout_url;
}
add_filter('logout_url', 'custom_admin_logout_url', 10, 2);
function custom_admin_logout_redirect()
{
    // Check if the logout action is triggered
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        wp_logout(); // Log the user out
        wp_redirect(home_url()); // Redirect to the homepage
        exit; // Ensure no further code is executed
    }
}
add_action('init', 'custom_admin_logout_redirect');

function add_loader_to_header()
{
    echo '<div class="site-loader" style="display:none;"><div class="loader"></div></div>';
}
add_action('wp_head', 'add_loader_to_header');

// function add_loader_to_footer() {
//     echo '<div class="site-loader"><div class="loader"></div></div>';
// }
// add_action('wp_footer', 'add_loader_to_footer');

// Team listing shortcode -----------------------------------------------------------------------------------
function enqueue_owl_carousel()
{
    // Enqueue Owl Carousel CSS
    wp_enqueue_style('owl-carousel-css', get_stylesheet_directory_uri() . '/assets/js/OwlCarousel2-2.3.4/dist/assets/owl.carousel.min.css');
    wp_enqueue_style('owl-carousel-theme-css', get_stylesheet_directory_uri() . '/assets/js/OwlCarousel2-2.3.4/dist/assets/owl.theme.default.min.css');

    // Enqueue Owl Carousel JS
    wp_enqueue_script('owl-carousel-js', get_stylesheet_directory_uri() . '/assets/js/OwlCarousel2-2.3.4/dist/owl.carousel.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_owl_carousel');


/*$request_url_services = weglot_get_service( 'Request_Url_Service_Weglot' );
$parser_services   = weglot_get_service( 'Parser_Service_Weglot' );
$generate_switcher = weglot_get_service( 'Generate_Switcher_Service_Weglot' );
$replace_url_services = weglot_get_service( 'Replace_Url_Service_Weglot' );
$current_language = $request_url_services->get_current_language()->getExternalCode();
$original_language = weglot_get_original_language();

$config             = new ServerConfigProvider();
$client             = $parser_services->get_client();
$parser             = new Parser( $client, $config, [] );
$content = $generate_switcher->generate_switcher_from_dom( $content );
if ( $original_language != $current_language ) {
    $content = $parser->translate($content, $original_language, $current_language);
    $content = $replace_url_services->replace_link_in_dom( $content );
}*/

// Team pop-up shortcode ------------------------------------------------------------------------------------------------
function team_shortcode_with_popup($atts)
{
    // Shortcode Attributes
    $atts = shortcode_atts(array(
        'posts_per_page' => -1, // Default: show all posts
        'order'          => 'DESC',
        'orderby'        => 'title',
        'layout'         => 'carousel', // Default layout is carousel. Options: carousel, grid
    ), $atts, 'team_listing');

    // Determine related team members based on the current page
    $related_team_ids = get_field('team_member_display', get_the_ID()); // ACF relationship field
    // $args = array(
    //     'post_type'      => 'team',
    //     'posts_per_page' => $atts['posts_per_page'],
    //     // 'orderby'        => $atts['orderby'],
    //     // 'order'          => $atts['order'],
    // );

    // if ($related_team_ids) {
    //     $args['post__in'] = $related_team_ids;
    // }

    $args = array(
        'post_type'      => 'team',
        'post__in'       => $related_team_ids, // Fetch posts in the specified order
        'orderby'        => 'post__in',       // Maintain the order of IDs
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No team members found.</p>';
    }

    ob_start();

    // Check layout type
    $layout_class = $atts['layout'] === 'grid' ? 'team-grid team_listing' : 'owl-carousel our_teams_carousel';
?>

    <!-- Team Member Carousel -->
    <div class="<?php echo esc_attr($layout_class); ?>">
        <?php while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $name = get_the_title();
            $content = get_the_content();
            $image_url = get_the_post_thumbnail_url($post_id, 'full');
            $position = get_field('position', $post_id);
            $speciality = get_field('speciality', $post_id);
            $speciality_list = get_field('speciality_list', $post_id);

            $original_language = weglot_get_original_language();
        ?>
            <div class="brxe-block teams-card">
                <div class="team-data" style="display:none;">
                    <p class="team-name"><?php echo esc_attr($name); ?></p>
                    <p class="team-position"><?php echo esc_attr($position); ?></p>
                    <p class="team-content"><?php echo esc_attr(wp_strip_all_tags($content)); ?></p>
                    <p class="team-image"><?php echo esc_url($image_url); ?></p>
                    <p class="team-speciality"><?php echo esc_attr($speciality); ?></p>
                    <p class="team-speciality-list"><?php echo esc_attr(json_encode($speciality_list)); ?></p>
                </div>
                <div class="team_image">
                    <img src="<?php echo esc_url($image_url); ?>" class="brxe-image css-filter size-full" alt="<?php echo esc_attr($name); ?>" loading="lazy" height="" width="" decoding="async" fetchpriority="high">
                    <button
                        aria-label="<?php echo esc_attr($name); ?>"
                        class="teams-btn bricks-button"
                        onclick="openTeamPopup(this);">
                        <i class="fas fa-plus"></i>
                    </button>

                </div>
                <?php if ($atts['layout'] === 'grid') { ?>
                    <div class="team_details">
                        <h6><?php echo esc_attr($name); ?></h6>
                        <p><?php echo esc_attr($position); ?></p>
                    </div>
                <?php } ?>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Single Modal Popup -->
    <div id="team-popup" class="team-popup" style="display: none;">
        <div class="popup-content">
            <button class="close-popup" onclick="closeTeamPopup();">&times;</button>
            <div class="popup-body">
                <div class="left_team_image">
                    <img id="popup-image" src="" alt="" class="popup-image">
                </div>
                <div class="right_team_content">
                    <h3 id="popup-name"></h3>
                    <h5 id="popup-position"></h5>
                    <p id="popup-content"></p>
                    <div class="speciality-list" id="popup-speciality-list">
                        <h6 id="popup-speciality"></h6>
                        <ul id="speciality-items"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const popupTeam = document.getElementById('team-popup');

        function openTeamPopup(element) {
            // Get the .team-data container
            const teamData = element.closest('.teams-card').querySelector('.team-data');

            const name = teamData.querySelector('.team-name').textContent;
            const position = teamData.querySelector('.team-position').textContent;
            const content = teamData.querySelector('.team-content').textContent;
            const image = teamData.querySelector('.team-image').textContent;
            const speciality = teamData.querySelector('.team-speciality').textContent;
            const specialityList = JSON.parse(teamData.querySelector('.team-speciality-list').textContent);

            // Detect current Weglot language
            let currentLang = 'en';

            if (typeof Weglot !== 'undefined' && typeof Weglot.getCurrentLang === 'function') {
                currentLang = Weglot.getCurrentLang();
            } else {
                currentLang = document.documentElement.lang || 'en';
            }

            console.log('Detected language:', currentLang);

            // Populate popup
            document.getElementById('popup-name').textContent = name;
            document.getElementById('popup-position').textContent = position;
            document.getElementById('popup-content').textContent = content;
            document.getElementById('popup-image').src = image;
            document.getElementById('popup-image').alt = name;
            document.getElementById('popup-speciality').textContent = speciality;

            const specialityItemsContainer = document.getElementById('speciality-items');
            specialityItemsContainer.innerHTML = '';

            if (Array.isArray(specialityList)) {
                specialityList.forEach(item => {
                    const li = document.createElement('li');
                    li.classList.add('speciality-item');

                    // Add image
                    if (item.speciality_image && item.speciality_image.url) {
                        const img = document.createElement('img');
                        img.src = item.speciality_image.url;
                        img.alt = 'Speciality Image';
                        img.classList.add('speciality-image');
                        li.appendChild(img);
                    }

                    // Pick correct language field
                    let textContent = '';
                    switch (currentLang) {
                        case 'de':
                            textContent = item.speciality_content || '';
                            break;
                        case 'fr':
                            textContent = item.fr_speciality_content || '';
                            break;
                        case 'it':
                            textContent = item.it_speciality_content || '';
                            break;
                        case 'en':
                            textContent = item.en_speciality_content || '';
                            break;
                        default:
                            textContent = item.speciality_content || '';
                    }

                    if (textContent) {
                        const p = document.createElement('p');
                        p.textContent = textContent;
                        li.appendChild(p);
                    }

                    specialityItemsContainer.appendChild(li);
                });
            }

            // Show popup
            popupTeam.style.display = 'flex';
            setTimeout(() => popupTeam.classList.add('show'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeTeamPopup() {
            popupTeam.classList.remove('show');
            setTimeout(() => popupTeam.style.display = 'none', 300);
            document.body.style.overflow = '';
        }

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'team-popup') {
                closeTeamPopup();
            }
        });

        // Bind events to both button and image
        document.querySelectorAll('.teams-card img, .teams-card button').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                openTeamPopup(this);
            });
        });

        /*const popupTeam = document.getElementById('team-popup');

        function openTeamPopup(button) {
            // Get data from button attributes            
            const name = button.getAttribute('data-name');
            const position = button.getAttribute('data-position');
            const content = button.getAttribute('data-content');
            const image = button.getAttribute('data-image');
            const speciality = button.getAttribute('data-speciality');
            const specialityList = JSON.parse(button.getAttribute('data-speciality-list'));

            // Populate the popup content
            document.getElementById('popup-name').textContent = name;
            document.getElementById('popup-position').textContent = position;
            document.getElementById('popup-content').textContent = content;
            document.getElementById('popup-image').src = image;
            document.getElementById('popup-image').alt = name;
            document.getElementById('popup-speciality').textContent = speciality;

            const specialityItemsContainer = document.getElementById('speciality-items');
            specialityItemsContainer.innerHTML = ''; // Clear previous items

            if (specialityList && Array.isArray(specialityList)) {
                specialityList.forEach(item => {
                    const li = document.createElement('li');
                    li.classList.add('speciality-item');
                    if (item.speciality_image && item.speciality_image.url) {
                        const img = document.createElement('img');
                        img.src = item.speciality_image.url;
                        img.alt = 'Speciality Image';
                        img.classList.add('speciality-image');
                        li.appendChild(img);
                    }
                    if (item.speciality_content) {
                        const p = document.createElement('p');
                        p.textContent = item.speciality_content;
                        li.appendChild(p);
                    }
                    specialityItemsContainer.appendChild(li);
                });
            }

            // Show the popup            
            popupTeam.style.display = 'flex';
            setTimeout(() => {
                popupTeam.classList.add('show');
            }, 10);

            // Disable background scrolling
            document.body.style.overflow = 'hidden';
        }

        function closeTeamPopup() {            
            popupTeam.classList.remove('show');
            setTimeout(() => {
                popupTeam.style.display = 'none';
            }, 300);
            // Re-enable background scrolling
            document.body.style.overflow = '';  
        }

        // Close modal on outside click
        document.addEventListener('click', function (e) {
            if (e.target.id === 'team-popup') {
                closeTeamPopup();
            }
        });


        // To open the pop-up onlick on all over of the image div ===========
        document.querySelectorAll('.brxe-block.teams-card img').forEach(function(img) {
            img.addEventListener('click', function(event) {
                event.stopPropagation(); // Prevents event from bubbling to parents
                var button = this.closest('.teams-card').querySelector('button');
                if (button) {
                    openTeamPopup(button);
                }
            });
        });*/
    </script>
    <style type="text/css">
        .brxe-block.teams-card img {
            cursor: pointer;
        }
    </style>
    <?php if ($atts['layout'] === 'carousel') : ?>
        <script>
            // jQuery(document).ready(function($) {
            //     $(".our_teams_carousel").owlCarousel({
            //         loop: true,
            //         margin: 0,
            //         nav: true,
            //         responsive: {
            //             0: {
            //                 items: 1
            //             },
            //             575: {
            //                 items: 2
            //             },
            //             767: {
            //                 items: 3
            //             },
            //             991: {
            //                 items: 4
            //             },
            //             1024: {
            //                 items: 4
            //             },
            //             1200: {
            //                 items: 5
            //             },
            //         }                
            //     });
            //     $( ".owl-prev").html('<i class="fas fa-chevron-left"></i>');
            //     $( ".owl-next").html('<i class="fas fa-chevron-right"></i>');
            // });
            jQuery(document).ready(function($) {
                var startPos = $(window).width() < 768 ? 2 : 0; // Start from 3rd slide only on mobile/tablet

                $(".our_teams_carousel").owlCarousel({
                    loop: true,
                    margin: 0,
                    nav: true,
                    startPosition: startPos,
                    responsive: {
                        0: {
                            items: 1
                        },
                        575: {
                            items: 2
                        },
                        767: {
                            items: 3
                        },
                        991: {
                            items: 4
                        },
                        1024: {
                            items: 4
                        },
                        1200: {
                            items: 5
                        }
                    }
                });

                // Custom navigation icons
                $(".owl-prev").html('<i class="fas fa-chevron-left"></i>');
                $(".owl-next").html('<i class="fas fa-chevron-right"></i>');
            });
        </script>
    <?php endif; ?>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
// [team_listing layout="grid"]
// [team_listing layout="carousel"]
add_shortcode('team_listing', 'team_shortcode_with_popup');


// Quiz shortcode --------------------------------------------------------------------------------------------------------
function render_quiz_shortcode($atts)
{
    // Start output buffering
    ob_start();

    if (have_rows('questions_and_answers_listing', 'options')): ?>
        <div id="quiz-container">
            <div class="quiz_question_form">
                <h2 class="quiz-heading"><?php the_field('main_heading', 'options'); ?></h2>
                <?php $question_index = 0; // Unique index for each question 
                ?>
                <?php while (have_rows('questions_and_answers_listing', 'options')): the_row(); ?>
                    <div class="single-question" style="<?php echo $question_index === 0 ? 'display: block;' : 'display: none;'; ?>">
                        <h2 id="brxe-faueri-<?php echo $question_index; ?>" class="question-heading"><?php the_sub_field('question', 'options'); ?></h2>
                        <?php //print_r(the_sub_field('exams', 'options')); 
                        ?>
                        <form id="brxe-jgragf-<?php echo $question_index; ?>" class="brxe-form">
                            <div class="form-group" role="radiogroup" aria-labelledby="label-hzwzvz">
                                <ul class="options-wrapper">
                                    <?php if (have_rows('answers', 'options')): ?>
                                        <?php $answer_index = 0; ?>
                                        <?php while (have_rows('answers', 'options')): the_row(); ?>
                                            <li>
                                                <div class="custom_radio_input" data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <input type="radio"
                                                        id="answer-<?php echo $question_index . '-' . $answer_index; ?>"
                                                        name="answers-<?php echo $question_index; ?>"
                                                        value="<?php echo esc_attr(get_sub_field('answer', 'options')); ?>"
                                                        data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <label for="answer-<?php echo $question_index . '-' . $answer_index; ?>">
                                                        <?php the_sub_field('answer', 'options'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <?php $answer_index++; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </form>
                        <div class="feedback"></div>
                    </div>
                    <?php $question_index++; ?>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif;

    // Return the output buffer contents
    return ob_get_clean();
}
add_shortcode('quiz_shortcode', 'render_quiz_shortcode');


// Courses quiz shortcode ------------------------------------------------------------------------------------------------
function render_courses_quiz_shortcode($atts)
{
    // Start output buffering
    ob_start();

    // Get the current page name
    $current_page = get_post_field('post_name', get_post());
    // print_r($current_page);
    if (have_rows('questions_and_answers_listing', 'options')): ?>
        <div id="quiz-container">
            <div class="quiz_question_form">
                <h3 class="quiz-heading"><?php the_field('main_heading', 'options'); ?></h3>
                <?php $question_index = 0; // Unique index for each question 
                ?>
                <?php while (have_rows('questions_and_answers_listing', 'options')): the_row(); ?>
                    <?php
                    // Get the exams field for the current question
                    $exams = get_sub_field('exams', 'options');

                    // Check if the current page name is in the exams array
                    if (is_array($exams) && in_array($current_page, $exams)): ?>
                        <div class="single-question" style="<?php echo $question_index === 0 ? 'display: block;' : 'display: none;'; ?>">
                            <h2 id="brxe-faueri-<?php echo $question_index; ?>" class="question-heading"><?php the_sub_field('question', 'options'); ?></h2>
                            <form id="brxe-jgragf-<?php echo $question_index; ?>" class="brxe-form">
                                <div class="form-group" role="radiogroup" aria-labelledby="label-hzwzvz">
                                    <ul class="options-wrapper">
                                        <?php if (have_rows('answers', 'options')): ?>
                                            <?php $answer_index = 0; ?>
                                            <?php while (have_rows('answers', 'options')): the_row(); ?>
                                                <li>
                                                    <div class="custom_radio_input" data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                        <input type="radio"
                                                            id="answer-<?php echo $question_index . '-' . $answer_index; ?>"
                                                            name="answers-<?php echo $question_index; ?>"
                                                            value="<?php echo esc_attr(get_sub_field('answer', 'options')); ?>"
                                                            data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                        <label for="answer-<?php echo $question_index . '-' . $answer_index; ?>">
                                                            <?php the_sub_field('answer', 'options'); ?>
                                                        </label>
                                                    </div>
                                                </li>
                                                <?php $answer_index++; ?>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </form>
                            <div class="feedback"></div>
                        </div>
                        <?php $question_index++; ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif;

    // Return the output buffer contents
    return ob_get_clean();
}
add_shortcode('courses_quiz_shortcode', 'render_courses_quiz_shortcode');

// JS code for quiz exam change ------------------------------------------------------------------------------------------
function enqueue_ajax_script()
{
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var form = document.getElementById("brxe-kyqcrx");

            if (form) {
                var div = document.createElement("div");

                // Copy attributes and move content
                [...form.attributes].forEach(attr => div.setAttribute(attr.name, attr.value));
                while (form.firstChild) div.appendChild(form.firstChild);

                form.replaceWith(div);
            }

            jQuery(function($) {
                let currentQuestion = 0;
                let correctAnswers = 0;
                let currentGroup = $('#brxe-kyqcrx select[name="group"]').val();
                const quizContainer = $('#quiz-container');
                const submitButton = $('#brxe-kyqcrx button[type=submit]');

                function showQuestion(index) {
                    $('.single-question').hide().eq(index).show();
                }

                function handleAnswerChange(event) {
                    const $selectedAnswer = $(event.target);
                    const $parentDiv = $selectedAnswer.closest('.custom_radio_input');
                    const $allOptions = $parentDiv.closest('ul').find('.custom_radio_input');

                    // Reset all options
                    $allOptions.removeClass('true false');

                    // Highlight correct and incorrect answers
                    $allOptions.each(function() {
                        const $input = $(this).find('input[type="radio"]');
                        $(this).addClass($input.data('correct') ? 'true' : $input.is(':checked') ? 'false' : '');
                    });
                }

                function fetchQuizQuestions() {
                    const selectedGroup = $('#brxe-kyqcrx select[name="group"]').val();
                    const currentPage = window.location.pathname.split('/').filter(Boolean).pop();
                    const groupToUse = selectedGroup || currentPage;

                    if (selectedGroup !== currentGroup) {
                        currentGroup = selectedGroup;
                        currentQuestion = 0;
                        correctAnswers = 0;
                    }

                    quizContainer.html('<span class="spn-loader"></span>');
                    submitButton.addClass('sending');

                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'fetch_quiz_questions',
                        group: groupToUse,
                        page_name: currentPage
                    }, function(response) {
                        if (response.success && response.data.html) {
                            quizContainer.html(response.data.html);
                            showQuestion(currentQuestion);
                        } else {
                            quizContainer.html('<p>' + (response.data.message || 'No questions found.') + '</p>');
                        }
                    }).fail(function() {
                        quizContainer.html('<p>Error loading questions.</p>');
                    }).always(() => submitButton.removeClass('sending'));
                }

                // Event Listeners
                quizContainer.on('change', 'input[type="radio"]', handleAnswerChange);
                submitButton.on("click", function(event) {
                    event.preventDefault();
                    submitButton.removeClass('sending');

                    const selectedGroup = $('#brxe-kyqcrx select[name="group"]').val();
                    const currentPage = window.location.pathname.split('/').filter(Boolean).pop();

                    if (!currentPage) {
                        if (selectedGroup !== currentGroup || currentQuestion >= $('.single-question').length) {
                            fetchQuizQuestions();
                        } else {
                            showNextQuestion();
                        }
                    } else {
                        showNextQuestion();
                    }
                });

                function showNextQuestion() {
                    currentQuestion++;
                    if (currentQuestion < $('.single-question').length) {
                        showQuestion(currentQuestion);
                    } else {
                        const targetSection = $('#brxe-zxjfuz');
                        if (targetSection.length) {
                            const offset = window.innerWidth < 768 ? 300 : 30;
                            $('html, body').animate({
                                scrollTop: targetSection.offset().top - offset
                            }, 800);
                        } else {
                            setTimeout(() => {
                                window.location.href = '/' + (currentGroup || window.location.pathname.split('/').filter(Boolean).pop());
                            }, 2000);
                        }
                    }
                }

                fetchQuizQuestions();
                showQuestion(currentQuestion);
            });
        });

        jQuery(document).ready(function($) {
            // Function to copy text to clipboard
            // function copyToClipboard(text) {
            //     navigator.clipboard.writeText(text).then(() => {
            //         showToast("Copied to clipboard!");
            //     }).catch(err => {
            //         showToast("Failed to copy.");
            //         console.error('Could not copy text: ', err);
            //     });
            // }
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast("Kopiert in die Zwischenablage!"); // Success message in German
                }).catch(err => {
                    showToast("Fehler beim Kopieren."); // Error message in German
                    console.error('Could not copy text: ', err);
                });
            }

            // Function to show a toast notification
            function showToast(message) {
                const toast = $('<div class="custom-toast"></div>').text(message);
                $('body').append(toast);
                toast.fadeIn(200).delay(2000).fadeOut(400, function() {
                    $(this).remove();
                });
            }

            // Copy email address to clipboard
            $('#brxe-tpzrbj').on('click', function() {
                const email = $('#brxe-bpndpv').text();
                copyToClipboard(email);
            });

            // Copy phone number to clipboard
            $('#brxe-hhxwlu').on('click', function() {
                // const phoneNumber = $('#brxe-vwawzp').text();
                const phoneNumber = $('#brxe-vwawzp').attr('href');
                copyToClipboard(phoneNumber);
            });

            // Copy order number to clipboard
            $('#brxe-1234-order').on('click', function() {
                const orderNumber = $('#brxe-1234-order-number').text();
                copyToClipboard(orderNumber);
            });
        });
    </script>
    <style>
        .custom-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1fbb65;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 1000;
            display: none;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .wpcf7 .wpcf7-not-valid-tip {
            display: none;
        }
    </style>
    <?php
}
add_action('wp_footer', 'enqueue_ajax_script'); // Include the script in the footer


// Ajax call for quiz exam change ---------------------------------------------------------------------------------------- 
add_action('wp_ajax_fetch_quiz_questions', 'fetch_quiz_questions_ajax');
add_action('wp_ajax_nopriv_fetch_quiz_questions', 'fetch_quiz_questions_ajax');
function fetch_quiz_questions_ajax()
{
    // if (!isset($_POST['group'])) {
    //     wp_send_json_error(['message' => 'Invalid request']);
    // }

    $selected_group = sanitize_text_field($_POST['group']);
    $selected_page_name = sanitize_text_field($_POST['page_name']);
    $output = ''; // Initialize output buffer

    ob_start(); // Start output buffering
    if (isset($_POST['group'])) {
    ?>
        <div class="quiz_question_form test">
            <h2 class="quiz-heading"><?php the_field('main_heading', 'options'); ?></h2>
            <?php $question_index = 0; // Unique index for each question
            while (have_rows('questions_and_answers_listing', 'options')) : the_row();
                $exams = get_sub_field('exams', 'options');
                if (in_array($selected_group, $exams)) {
            ?>
                    <div class="single-question" style="<?php echo $question_index === 0 ? 'display: block;' : 'display: none;'; ?>">
                        <h2 id="brxe-faueri-<?php echo $question_index; ?>" class="question-heading"><?php the_sub_field('question', 'options'); ?></h2>
                        <form id="brxe-jgragf-<?php echo $question_index; ?>" class="brxe-form">
                            <div class="form-group" role="radiogroup" aria-labelledby="label-hzwzvz">
                                <ul class="options-wrapper">
                                    <?php if (have_rows('answers', 'options')): ?>
                                        <?php $answer_index = 0; ?>
                                        <?php while (have_rows('answers', 'options')): the_row(); ?>
                                            <li>
                                                <div class="custom_radio_input" data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <input type="radio"
                                                        id="answer-<?php echo $question_index . '-' . $answer_index; ?>"
                                                        name="answers-<?php echo $question_index; ?>"
                                                        value="<?php echo esc_attr(get_sub_field('answer', 'options')); ?>"
                                                        data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <label for="answer-<?php echo $question_index . '-' . $answer_index; ?>">
                                                        <?php the_sub_field('answer', 'options'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <?php $answer_index++; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </form>
                        <div class="feedback"></div>
                    </div>
            <?php $question_index++;
                }
            endwhile;
            ?>
        </div>
    <?php } else { // Get the current page name
    ?>
        <div class="quiz_question_form">
            <h2 class="quiz-heading"><?php the_field('main_heading', 'options'); ?></h2>
            <?php $question_index = 0; // Unique index for each question 
            ?>
            <?php while (have_rows('questions_and_answers_listing', 'options')): the_row(); ?>
                <?php
                // Get the exams field for the current question
                $exams = get_sub_field('exams', 'options');

                // Check if the current page name is in the exams array
                if (in_array($selected_page_name, $exams)): ?>
                    <div class="single-question" style="<?php echo $question_index === 0 ? 'display: block;' : 'display: none;'; ?>">
                        <h2 id="brxe-faueri-<?php echo $question_index; ?>" class="question-heading"><?php the_sub_field('question', 'options'); ?></h2>
                        <form id="brxe-jgragf-<?php echo $question_index; ?>" class="brxe-form">
                            <div class="form-group" role="radiogroup" aria-labelledby="label-hzwzvz">
                                <ul class="options-wrapper">
                                    <?php if (have_rows('answers', 'options')): ?>
                                        <?php $answer_index = 0; ?>
                                        <?php while (have_rows('answers', 'options')): the_row(); ?>
                                            <li>
                                                <div class="custom_radio_input" data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <input type="radio"
                                                        id="answer-<?php echo $question_index . '-' . $answer_index; ?>"
                                                        name="answers-<?php echo $question_index; ?>"
                                                        value="<?php echo esc_attr(get_sub_field('answer', 'options')); ?>"
                                                        data-correct="<?php echo get_sub_field('correct_answer', 'options') ? 'true' : 'false'; ?>">
                                                    <label for="answer-<?php echo $question_index . '-' . $answer_index; ?>">
                                                        <?php the_sub_field('answer', 'options'); ?>
                                                    </label>
                                                </div>
                                            </li>
                                            <?php $answer_index++; ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </form>
                        <div class="feedback"></div>
                    </div>
                    <?php $question_index++; ?>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
    <?php }
    $output = ob_get_clean(); // Get and clean the buffer

    if (!empty($output)) {
        wp_send_json_success(['html' => $output]);
    } else {
        wp_send_json_error(['message' => 'No questions found for this group.']);
    }

    wp_die();
}


/**
 * Shortcode to display post listings with category filters and pagination -------------------------------------------------------------------
 */
function post_listing_with_multifilters_shortcode($atts)
{
    $atts = shortcode_atts([
        'posts_per_page' => 6, // Default number of posts per page
    ], $atts, 'post_listing_with_multifilters');

    // Get the selected categories from the query string
    $selected_categories = !empty($_GET['category_filter']) ? explode(',', sanitize_text_field($_GET['category_filter'])) : [];

    // Prepare query arguments
    $args = [
        'post_type' => 'post',
        'posts_per_page' => $atts['posts_per_page'],
        'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
    ];

    if (!empty($selected_categories)) {
        $args['category__in'] = array_map('intval', $selected_categories);
    }

    $query = new WP_Query($args);

    // Fetch all categories
    // $categories = get_categories(['hide_empty' => true]);
    // Fetch all categories except "Main"
    $categories = get_categories([
        'hide_empty' => true,
        'exclude'    => get_cat_ID('Main') // Exclude the "Main" category
    ]);
    ob_start();
    ?>

    <div id="post-listing-multifilters">
        <!-- Filter Section -->
        <div class="filter-container">
            <h3 class="brxe-heading">Neuste Artikel</h3>
            <div class="filter_dropdown_wrap">
                <div class="selected-filters">
                    <?php foreach ($selected_categories as $category_id) : ?>
                        <?php $category = get_category($category_id); ?>
                        <span class="filter-tag" data-id="<?php echo esc_attr($category_id); ?>">
                            <button type="button" class="remove-filter" data-id="<?php echo esc_attr($category_id); ?>">×</button>
                            <?php echo esc_html($category->name); ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="dropdown custom_dropdown">
                    <button type="button" id="" class="btn-filter dropdown-label">
                        <span>
                            <svg width="12" height="14" viewBox="0 0 12 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.4846 0.873068H1.51543C0.678857 0.873068 0 1.55193 0 2.3885C0 2.75193 0.130286 3.10164 0.363429 3.37593L3.90857 7.51078C4.03886 7.6685 4.11429 7.86735 4.11429 8.06621V11.9336C4.11429 12.3519 4.32686 12.7359 4.68343 12.9554C4.87543 13.0719 5.09486 13.1336 5.31429 13.1336C5.49943 13.1336 5.67771 13.0925 5.84914 13.0034L7.22057 12.3176C7.632 12.1119 7.88571 11.7005 7.88571 11.2411V8.05935C7.88571 7.85364 7.96114 7.65478 8.09143 7.50393L11.6366 3.36907C11.8697 3.09478 12 2.74507 12 2.38164C12 1.54507 11.3211 0.866211 10.4846 0.866211V0.873068ZM10.8549 2.70393L7.30971 6.83878C7.01486 7.18164 6.85714 7.61364 6.85714 8.06621V11.2479C6.85714 11.3165 6.82286 11.3714 6.76114 11.3988L5.38971 12.0845C5.31429 12.1256 5.25257 12.0982 5.22514 12.0776C5.19771 12.0571 5.14286 12.0159 5.14286 11.9336V8.06621C5.14286 7.61364 4.98514 7.18164 4.69029 6.83878L1.14514 2.70393C1.06971 2.61478 1.02857 2.50507 1.02857 2.3885C1.02857 2.12107 1.248 1.90164 1.51543 1.90164H10.4846C10.752 1.90164 10.9714 2.12107 10.9714 2.3885C10.9714 2.50507 10.9303 2.61478 10.8549 2.70393Z" fill="#1A3A27" />
                            </svg>
                        </span>
                        Filtern</button>
                    <div class="dropdown-content">
                        <div class="dropdown-list">
                            <?php foreach ($categories as $category) : ?>
                                <div class="checkbox">
                                    <label for="checkbox-<?php echo esc_attr($category->term_id); ?>" class="checkbox-lbl">
                                        <input type="checkbox" name="category_filter[]" id="checkbox-<?php echo esc_attr($category->term_id); ?>" value="<?php echo esc_attr($category->term_id); ?>"
                                            <?php echo in_array($category->term_id, $selected_categories) ? 'checked' : ''; ?> />
                                        <span class="checkbox-span"></span>
                                        <?php echo esc_html($category->name); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Listing -->
        <div class="post-list">
            <?php if ($query->have_posts()) : ?>
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="post-item custom_blog_post">
                        <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium'); ?>
                            <?php endif; ?>
                        </a>

                        <div class="post-details">
                            <!-- Categories Above Title -->
                            <div class="post-categories">
                                <?php
                                $categories = get_the_category();
                                if (!empty($categories)) :
                                    foreach ($categories as $index => $category) :
                                ?>
                                        <span class="category"><?php echo esc_html($category->name) . ($index < count($categories) - 1 ? ', ' : ''); ?></span>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>

                            <!-- Post Title -->
                            <h3 class="post-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <!-- Author Avatar and Name -->
                            <div class="post-meta">
                                <div class="post-avatar">
                                    <span class="author-avatar">
                                        <?php //echo get_avatar(get_the_author_meta('ID'), 32); 
                                        ?>
                                        <?php echo get_avatar(get_the_author_meta('ID')); ?>
                                    </span>
                                    <span class="author-name"><?php the_author(); ?></span>
                                </div>
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <?php echo do_shortcode('[post_read_time]'); ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php esc_html_e('No posts found.', 'bricks-child'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination custom_pagination">
            <?php
            echo paginate_links([
                'total'        => $query->max_num_pages,
                'current'      => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'show_all'     => false,
                'end_size'     => 2,
                'mid_size'     => 1,
                'prev_next'    => true,
                'prev_text'    => __('<i class="fas fa-arrow-left"></i>', 'bricks-child'),
                'next_text'    => __('<i class="fas fa-arrow-right"></i>', 'bricks-child'),
                'type'         => 'list',
            ]);
            ?>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle checkbox changes and update the URL
            jQuery('.checkbox input').on('change', function() {
                const selectedCategories = [];

                // Get the selected checkboxes' values
                jQuery('.checkbox input:checked').each(function() {
                    selectedCategories.push(jQuery(this).val());
                });

                // Update the URL with the selected categories
                const queryString = selectedCategories.length > 0 ? '?category_filter=' + selectedCategories.join(',') : '';
                window.location.href = window.location.pathname + queryString;
            });

            // Remove a selected filter
            jQuery('.remove-filter').on('click', function() {
                const filterId = jQuery(this).data('id');
                let selectedCategories = jQuery('.checkbox input:checked').map(function() {
                    return jQuery(this).val();
                }).get();

                // Remove the selected filter's ID from the list
                selectedCategories = selectedCategories.filter((id) => id != filterId);

                // Update the checkboxes accordingly
                jQuery('.checkbox input').each(function() {
                    const checkboxValue = jQuery(this).val();
                    if (selectedCategories.includes(checkboxValue)) {
                        jQuery(this).prop('checked', true);
                    } else {
                        jQuery(this).prop('checked', false);
                    }
                });

                // Update the URL with the new filter selections
                const queryString = selectedCategories.length > 0 ? '?category_filter=' + selectedCategories.join(',') : '';
                window.location.href = window.location.pathname + queryString;
            });

            // Handle the opening and closing of the dropdown
            jQuery('.dropdown-label').on('click', function(e) {
                // Prevents the default action of the label (which may cause issues in certain browsers)
                e.preventDefault();

                // Toggle the open class on the parent dropdown
                var $dropdown = jQuery(this).closest('.dropdown');
                $dropdown.toggleClass('open'); // This will show/hide the dropdown

                // Close the dropdown if clicked outside
                jQuery(document).on('click', function(e) {
                    if (!jQuery(e.target).closest($dropdown).length) {
                        $dropdown.removeClass('open');
                    }
                });
            });

            // Ensure dropdown state is updated after removal or change
            jQuery('.dropdown input[type="checkbox"]').on('change', function() {
                const dropdown = jQuery(this).closest('.dropdown');
                const checkboxes = dropdown.find('input[type="checkbox"]:checked');
                const selectedText = checkboxes.map(function() {
                    return jQuery(this).next('label').text();
                }).get().join(', ');
            });
        });
    </script>
<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('post_listing_with_multifilters', 'post_listing_with_multifilters_shortcode');


// Post details social share shortcode -----------------------------------------------------------------------------------
function custom_social_share_shortcode($atts)
{
    // Extract attributes and set defaults
    $atts = shortcode_atts(
        array(
            'url'   => '', // URL to share
            'title' => 'Check this out!', // Default share message
        ),
        $atts,
        'social_share'
    );

    // Get the current URL if no URL is provided
    $share_url = !empty($atts['url']) ? esc_url($atts['url']) : esc_url(get_permalink());
    $share_title = urlencode($atts['title']);

    // Social media share links
    $facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($share_url);
    $instagram_url = 'https://www.instagram.com/?url=' . urlencode($share_url); // Instagram does not support direct share links
    $twitter_url = 'https://twitter.com/intent/tweet?url=' . urlencode($share_url) . '&text=' . $share_title;
    $whatsapp_url = 'https://api.whatsapp.com/send?text=' . $share_title . '%20' . urlencode($share_url);

    // Generate the HTML output
    ob_start();
?>
    <div class="custom-social-share">
        <a href="<?php echo $facebook_url; ?>" target="_blank" rel="nofollow noopener" class="social-share-btn facebook">
            <i class="fab fa-facebook"></i>
        </a>
        <a href="<?php echo $instagram_url; ?>" target="_blank" rel="nofollow noopener" class="social-share-btn instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="<?php echo $twitter_url; ?>" target="_blank" rel="nofollow noopener" class="social-share-btn twitter">
            <i class="fab fa-x-twitter"></i>
        </a>
        <a href="<?php echo $whatsapp_url; ?>" target="_blank" rel="nofollow noopener" class="social-share-btn whatsapp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>
    <style>
        .custom-social-share {
            display: flex;
            gap: 10px;
        }

        .social-share-btn {
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            color: #fff;
            font-size: 14px;
        }

        .social-share-btn.facebook {
            background-color: #3b5998;
        }

        .social-share-btn.instagram {
            background-color: #E1306C;
        }

        .social-share-btn.twitter {
            background-color: #1DA1F2;
        }

        .social-share-btn.whatsapp {
            background-color: #25D366;
        }
    </style>
<?php
    return ob_get_clean();
}
add_shortcode('social_share', 'custom_social_share_shortcode');


// Personal Support Offer Shortcode -----------------------------------------------------------------------------------
function personal_support_offer_shortcode()
{

    // Fetch offer data from ACF
    $offer_main_title   = get_field('offer_main_title');
    $offer_link_text    = get_field('offer_link_text');
    $offer_listing      = get_field('offer_listing');
    ob_start(); ?>

    <!-- Offer Section Start-->
    <div class="brxe-block offer_section_wrapper">
        <?php
        foreach ($offer_listing as $offer) :
            // Fetch individual offer fields
            $offer_title        = $offer['offer_title']; // Assuming 'offer_title' is a field name
            $offer_subtitle     = $offer['offer_subtitle']; // Assuming 'offer_description' is a field name
            $offer_image        = $offer['offer_image']; // Assuming 'offer_image' is an image field
            $offer_image_url    = $offer['offer_image']['url']; // Assuming 'offer_image' is an image field
            $offer_content      = $offer['offer_content_title']; // Assuming 'offer_details' is a field name
            $offer_description  = $offer['offer_description']; // Assuming 'offer_details' is a field name
        ?>
            <div class="card_offers">
                <div class="offer_content">
                    <span class="offer_tag"><?php echo $offer_main_title; ?></span>
                    <h3 class="offer_title"><?php echo $offer_title; ?></h3>
                    <button
                        type="button"
                        class="btn-link"
                        onclick="openOfferPopup(this);"
                        data-offer-title="<?php echo esc_attr($offer_title); ?>"
                        data-offer-subtitle="<?php echo esc_attr($offer_subtitle); ?>"
                        data-offer-image="<?php echo esc_url($offer_image_url); ?>"
                        data-offer-content="<?php echo esc_attr($offer_content); ?>"
                        data-offer-description="<?php echo esc_attr($offer_description); ?>">
                        <?php echo $offer_link_text; ?>
                    </button>
                </div>
                <div class="offer_image">
                    <img src="<?php echo $offer_image['url']; ?>" alt="<?php echo $offer_image['title']; ?>" />
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Offer Section End -->

    <!-- Offer Modal Popup -->
    <div id="offer-popup" class="team-popup offer-popup" style="display: none;">
        <div class="popup-content">
            <button class="close-popup" onclick="closeOfferPopup();">&times;</button>
            <div class="popup-body">
                <div class="popup_content">
                    <h4 id="offer-title"></h4>
                    <h3 id="offer-subtitle"></h3>
                    <div class="offer-image">
                        <img id="offer-image" src="" alt="" />
                    </div>
                    <h5 id="offer-content"></h5>
                    <div id="offer-description" class="offer-content"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const popupOffer = document.getElementById('offer-popup');

        function openOfferPopup(button) {
            // Get data from button attributes
            const offer_title = button.getAttribute('data-offer-title');
            const offer_subtitle = button.getAttribute('data-offer-subtitle');
            const offer_image = button.getAttribute('data-offer-image');
            const offer_content = button.getAttribute('data-offer-content');
            const offer_description = button.getAttribute('data-offer-description');

            // Populate the popup content
            document.getElementById('offer-title').textContent = offer_title;
            document.getElementById('offer-subtitle').textContent = offer_subtitle;
            document.getElementById('offer-content').textContent = offer_content;
            document.getElementById('offer-description').innerHTML = offer_description;
            document.getElementById('offer-image').src = offer_image;

            // Show the popup            
            popupOffer.style.display = 'flex';
            setTimeout(() => {
                popupOffer.classList.add('show');
            }, 10);
            // Disable background scrolling
            document.body.style.overflow = 'hidden';
        }

        function closeOfferPopup() {
            popupOffer.classList.remove('show');
            setTimeout(() => {
                popupOffer.style.display = 'none';
            }, 300);
            // Re-enable background scrolling
            document.body.style.overflow = '';
        }

        // Close modal on outside click
        document.addEventListener('click', function(e) {
            if (e.target.id === 'offer-popup') {
                closeOfferPopup();
            }
        });
    </script>
<?php return ob_get_clean();
}
add_shortcode('personal_support_offer', 'personal_support_offer_shortcode');


// Offline IMS courses Shortcode -----------------------------------------------------------------------------------
function offline_ims_courses_shortcode()
{

    $current_course_id = get_the_ID();
    ob_start(); ?>

    <!-- Offline IMS Courses Start -->
    <div class="ims_offline_courses_wrapper">
        <h3 class="brxe-heading"><?php the_field('multicheck_course_title', $current_course_id); ?></h3>
        <p class="brxe-desc"><?php the_field('multicheck_course_description', $current_course_id); ?></p>
        <div class="accordion">
            <?php if (have_rows('multicheck_course_listing', $current_course_id)): while (have_rows('multicheck_course_listing', $current_course_id)): the_row(); ?>
                    <div class="accordion-item">
                        <button type="button" class="accordion-header"><?php the_sub_field('list_title'); ?></button>
                        <div class="accordion-content">
                            <div class="cms-content">
                                <?php the_sub_field('list_description'); ?>
                            </div>
                        </div>
                    </div>
            <?php endwhile;
            endif; ?>
        </div>

        <h3 class="brxe-heading"><?php the_field('multicheck_course_accordion_title', $current_course_id); ?></h3>
        <div class="table-accordion">
            <?php if (have_rows('multicheck_course_listing', $current_course_id)): while (have_rows('multicheck_course_listing', $current_course_id)): the_row(); ?>
                    <div class="table-content">
                        <?php if (have_rows('mothly_courses_slot')): while (have_rows('mothly_courses_slot')): the_row(); ?>
                                <table class="course-tbl">
                                    <tbody>
                                        <tr>
                                            <th>
                                                <h5><?php the_sub_field('title'); ?></h5>
                                                <p><?php echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' – ' . get_sub_field('select_to_time') . ' Uhr'; ?></p>
                                            </th>
                                            <td><?php the_sub_field('time_slot_title'); ?></td>
                                            <td><?php echo get_sub_field('from_date') . ' - ' . get_sub_field('to_date'); ?></td>
                                            <td class="price"><?php echo get_sub_field('slot_price') . ' CHF'; ?></td>
                                            <td class="btn-action">
                                                <div class="btn-tooltip">
                                                    <i class="fas fa-exclamation"></i>
                                                    <div class="tooltip_popup">
                                                        <h3><?php the_sub_field('info_title'); ?></h3>
                                                        <ul>
                                                            <li><?php the_sub_field('info_day_time'); ?></li>
                                                            <li><b><?php the_sub_field('info_slot_dates'); ?></b></li>
                                                            <li>
                                                                <h6><?php _e('Kosten', 'bricks-child'); ?></h6>
                                                                <p><?php echo get_sub_field('slot_price') . ' CHF'; ?></p>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <a href="#brxe-2c1cab" class="brxe-button bricks-button lg bricks-background-primary"><?php the_field('multicheck_course_accordion_button_text', $current_course_id); ?></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                        <?php endwhile;
                        endif; ?>
                    </div>
            <?php endwhile;
            endif; ?>
        </div>
    </div>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script>
        jQuery(document).ready(function($) {
            $(".table-content").first().addClass("active").show();

            $(".accordion-header").click(function() {
                $(this).toggleClass("active");

                $(this).next(".accordion-content").slideToggle();
                $(".accordion-content").not($(this).next()).slideUp();
                $(".accordion-header").not($(this)).removeClass("active");

                const index = $(".accordion-header").index(this);

                $(".table-content").removeClass("active").slideUp();
                $(".table-content").eq(index).addClass("active").slideToggle();
            });
        });
    </script>
    <!-- Offline IMS Courses End -->

<?php return ob_get_clean();
}
add_shortcode('offline_ims_courses', 'offline_ims_courses_shortcode');

// Latest event get for my course page ----------------------------------------------------------
function latest_event_courses_multicheck($value = '')
{
    $current_user_id = get_current_user_id();
    global $wpdb;

    $sql = $wpdb->prepare("
        SELECT DISTINCT pm.meta_value AS group_package_category 
        FROM {$wpdb->prefix}postmeta pm 
        JOIN {$wpdb->prefix}usermeta um 
        ON REPLACE(um.meta_key, 'learndash_group_users_', '') = pm.post_id 
        WHERE pm.meta_key = 'group_package_category' 
        AND um.user_id = %d 
        AND um.meta_key LIKE 'learndash_group_users_%'
    ", $current_user_id);

    $learndash_groups = $wpdb->get_results($sql);
    $event_blocks = [];

    foreach ($learndash_groups as $group) {
        $group_package_category = $group->group_package_category;
        $dates = $places = $available = [];

        // Assign Page ID based on category
        $PageIds = match ($group_package_category) {
            'multicheck-vorbereitung' => 24515,
            'gymi-vorbereitung' => 24516,
            'ims-bms-fms-hms-vorbereitung' => 23759,
            'stellwerktest-vorbereitung' => 25914,
            'probezeit-vorbereitung' => 110628, // ✅ New line
            default => null
        };
        if ($PageIds) {
            $PagePermalink = get_permalink($PageIds);
            // Assign different anchor tags based on category
            $anchorTag = match ($group_package_category) {
                'multicheck-vorbereitung' => '#brxe-tuqzmo',
                'gymi-vorbereitung' => '#brxe-rybgva',
                'ims-bms-fms-hms-vorbereitung' => '#brxe-rnujnd',
                'stellwerktest-vorbereitung' => '#brxe-rnkcyf', // You can add any default anchor tag here
                'probezeit-vorbereitung' => '#brxe-tuqzmo', // ✅ Replace with correct anchor if any
                default => '', // In case of an invalid category
            };

            // Append the anchor tag to the permalink
            $PagePermalink .= $anchorTag;

            $acf_field = match ($group_package_category) {
                'multicheck-vorbereitung', 'stellwerktest-vorbereitung' => 'mothly_courses_slot',
                'probezeit-vorbereitung' => 'pz_course_listing', // ✅ New addition
                'gymi-vorbereitung' => 'multicheck_course_listing',
                'ims-bms-fms-hms-vorbereitung' => 'ims_tab_listing',
                default => ''
            };

            if ($acf_field && have_rows($acf_field, $PageIds)) {
                while (have_rows($acf_field, $PageIds)) {
                    the_row();
                    if ($group_package_category === 'gymi-vorbereitung' || $group_package_category === 'ims-bms-fms-hms-vorbereitung' || $group_package_category === 'probezeit-vorbereitung') {
                        if (have_rows('mothly_courses_slot')) {
                            while (have_rows('mothly_courses_slot')) {
                                the_row();
                                $event_data = get_event_data();
                                $dates = array_merge($dates, $event_data['dates']);
                                $places = array_merge($places, $event_data['places']);
                                $available = array_merge($available, $event_data['available']);
                            }
                        }
                    } else {
                        $event_data = get_event_data();
                        $dates = array_merge($dates, $event_data['dates']);
                        $places = array_merge($places, $event_data['places']);
                        $available = array_merge($available, $event_data['available']);
                    }
                }
            }
        }

        if (!empty($dates)) {
            list($NearestDate, $NearestPlace, $NearestAvailable) = getNearestFutureDate($dates, $places, $available);
            $NearestPlace = $NearestPlace ?: 'Torgasse 8, 8001 ZH';
            if ($NearestDate) {
                $dateObj = new DateTime($NearestDate);
                setlocale(LC_TIME, 'de_DE.UTF-8', 'de_DE', 'deu_deu');
                $NearestDate = strftime('%e %B %Y', $dateObj->getTimestamp());
                // $group_package_category = ucwords(str_replace('-', ' ', $group_package_category));
                
                // ✅ Define custom display names for your categories
                $category_labels = [
                    'ims-bms-fms-hms-vorbereitung' => 'IMS-Vorbereitung',
                ];
                // ✅ Use the mapping if available, otherwise prettify automatically
                if (isset($category_labels[$group_package_category])) {
                    $display_category = $category_labels[$group_package_category];
                } else {
                    $display_category = implode('-', array_map('ucfirst', explode('-', $group_package_category)));
                }
                // $group_package_category = implode('-', array_map('ucfirst', explode('-', $group_package_category)));

                $event_blocks[] = '
                    <div class="course_event_wrap">
                        <p class="sub_title">' . esc_html__("Nächster Event", "bricks-child") . '</p>
                        <h3>' . esc_html__($display_category, "bricks-child") . '</h3>
                        <table class="event_tbl">
                            <tbody>
                                <tr><th><div class="event_th"><span><i class="icon icon-datepicker"></i></span> ' . esc_html__("Datum", "bricks-child") . '</div></th><td>' . $NearestDate . '</td></tr>
                                <tr><th><div class="event_th"><span><i class="icon icon-location"></i></span> ' . esc_html__("Ort", "bricks-child") . '</div></th><td>' . $NearestPlace . '</td></tr>
                                <tr><th><div class="event_th"><span><i class="icon icon-square-sidebar"></i></span> ' . esc_html__("Verfügbarkeit", "bricks-child") . '</div></th><td>' . $NearestAvailable . '</td></tr>
                            </tbody>
                        </table>
                        <div class="event_btn_wrap">
                            <a href="' . $PagePermalink . '" class="btn_green">' . esc_html__("Platz reservieren", "bricks-child") . '</a>
                            <a href="' . $PagePermalink . '" class="btn_white">' . esc_html__("Mehr Informationen", "bricks-child") . '</a>
                        </div>
                    </div>';
            } else {
                continue; // Skip this block if no future dates
            }
        }
    }

    if (empty($event_blocks)) {
        return '<div class="course_event_wrap no-events-found"><p class="sub_title">Nächster Event</p><h3>Derzeit sind keine Ereignisse vorhanden.</h3></div>';
    }

    $carousel_class = count($event_blocks) > 1 ? 'owl-carousel' : '';
    $output = '<div class="our_course_carousel ' . $carousel_class . '">' . implode('', $event_blocks) . '</div>';

    if (count($event_blocks) > 1) {
        $output .= '<script>
            jQuery(document).ready(function($) {
                $(".our_course_carousel").owlCarousel({
                    loop: true,
                    margin: 10,
                    nav: true,
                    responsive: { 0: { items: 1 }, 991: { items: 1 }, 1200: { items: 1 } }
                });
                $(".owl-prev").html(\'<i class="fas fa-chevron-left"></i>\');
                $(".owl-next").html(\'<i class="fas fa-chevron-right"></i>\');
            });
        </script>';
    }

    return $output;
}
add_shortcode('latest_event_course', 'latest_event_courses_multicheck');


// Function to get event data
/*function get_event_data() {
    $event_dates = get_sub_field('info_slot_dates');
    $event_place = get_sub_field('info_slot_address');
    $event_availability = get_sub_field('info_slot_availability');

    if (!empty($event_dates)) {
        $evntdates = explode(', ', $event_dates);
        $normalizedDate = convertToDate($evntdates[0]);
        if ($normalizedDate) {
            return [
                'dates' => [$normalizedDate],
                'places' => [$event_place],
                'available' => [$event_availability]
            ];
        }
    }
    return ['dates' => [], 'places' => [], 'available' => []];
}*/

// Function to get event data
function get_event_data()
{
    $event_dates = get_sub_field('info_slot_dates');
    $event_place = get_sub_field('info_slot_address');
    $event_availability = get_sub_field('info_slot_availability');

    if (!empty($event_dates)) {
        // Normalize and extract dates
        $normalized_string = str_replace(['–', '-', 'bis'], ',', $event_dates);
        $normalized_string = preg_replace('/\s*,\s*/', ',', $normalized_string);
        $date_parts = preg_split('/[\s,]+/', $normalized_string);

        $valid_dates = [];
        foreach ($date_parts as $date_str) {
            $converted = convertToDate($date_str);
            if ($converted) {
                $valid_dates[] = $converted;
            }
        }

        if (!empty($valid_dates)) {
            return [
                'dates' => $valid_dates,
                'places' => array_fill(0, count($valid_dates), $event_place),
                'available' => array_fill(0, count($valid_dates), $event_availability),
            ];
        }
    }

    return ['dates' => [], 'places' => [], 'available' => []];
}



// Helper function to convert date to 'Y-m-d' format -----------------------------
function convertToDate($dateStr)
{
    // Handle multiple date formats
    $formats = ['d.m.y', 'd.m.Y'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateStr);
        if ($date) {
            return $date->format('Y-m-d');
        }
    }
    return false;
}
// Function to find the nearest future date -----------------------------
function getNearestFutureDate($dates, $places, $available)
{
    $currentDate = new DateTime();
    $NearestDate = null;
    $NearestPlace = null;
    $NearestAvailable = null;
    $shortestInterval = null;

    foreach ($dates as $index => $dateStr) {
        $date = new DateTime($dateStr);

        // Skip past dates
        if ($date <= $currentDate) {
            continue;
        }

        $interval = $currentDate->diff($date);

        if ($NearestDate === null || $interval->days < $shortestInterval->days) {
            $NearestDate = $date;
            $NearestPlace = $places[$index]; // Make sure $places array is synchronized with $dates
            $NearestAvailable = $available[$index]; // Make sure $places array is synchronized with $dates
            $shortestInterval = $interval;
        }
    }

    return [$NearestDate ? $NearestDate->format('Y-m-d') : null, $NearestPlace, $NearestAvailable];
}

// Online courses emoji pop-up Shortcode -----------------------------------------------------------------------------------
/*function custom_modal_popup_script() {
    ?>
    <script type="text/javascript">
        // jQuery(document).ready(function($) {
        //     // Course details page popup content -----------------------------
        //     $('#brxe-course-prepration').on('click', function() {
        //         var offer_description = <?php echo json_encode(get_field('online_course_prepration_description')); ?>;
        //         $('#course-description').html(offer_description);
        //         $('#course-popup').css('display', 'flex').fadeIn(300);
        //         setTimeout(function() { $('#course-popup').addClass('show'); }, 10);
        //     });

        //     // Carrer page emoji popup content -----------------------------
        //     var contentMap = {
        //         'brxe-emoji_content_1': <?php echo json_encode(get_field('cup_content')); ?>,
        //         'brxe-emoji_content_2': <?php echo json_encode(get_field('trophy_content')); ?>,
        //         'brxe-emoji_content_3': <?php echo json_encode(get_field('books_content')); ?>,
        //         'brxe-emoji_content_4': <?php echo json_encode(get_field('hands_content')); ?>
        //     };
        //     $('[id^="brxe-emoji_content_"]').on('click', function () {
        //         var id = $(this).attr('id');
        //         var offer_description = contentMap[id];
        //         $('#course-description').html(offer_description);
        //         $('#course-popup').css('display', 'flex').fadeIn(300);
        //         setTimeout(function () { $('#course-popup').addClass('show'); }, 10);
        //     });

        //     // Close button for above both -----------------------------
        //     $('.close-popup').on('click', function() {
        //         $('#course-popup').removeClass('show');
        //         setTimeout(function() { $('#course-popup').fadeOut(300); }, 300);
        //         setTimeout(function() { $('#course-popup').css('display', 'none'); }, 600);
        //     });
        // });
        jQuery(document).ready(function($) {
            // Function to disable scrolling
            function disableScroll() {
                $('body').css('overflow', 'hidden');
            }

            // Function to enable scrolling
            function enableScroll() {
                $('body').css('overflow', '');
            }

            // Course details page popup content
            $('#brxe-course-prepration').on('click', function() {
                var offer_description = <?php echo json_encode(get_field('online_course_prepration_description')); ?>;
                $('#course-description').html(offer_description);
                $('#course-popup').css('display', 'flex').fadeIn(300);
                setTimeout(function() { 
                    $('#course-popup').addClass('show'); 
                    disableScroll(); // Disable scrolling
                }, 10);
            });

            // Career page emoji popup content
            var contentMap = {
                'brxe-emoji_content_1': <?php echo json_encode(get_field('cup_content')); ?>,
                'brxe-emoji_content_2': <?php echo json_encode(get_field('trophy_content')); ?>,
                'brxe-emoji_content_3': <?php echo json_encode(get_field('books_content')); ?>,
                'brxe-emoji_content_4': <?php echo json_encode(get_field('hands_content')); ?>
            };
            
            // $('[id^="brxe-emoji_content_"]').on('click', function () {
            //     var id = $(this).attr('id');
            //     var offer_description = contentMap[id];
            //     $('#course-description').html(offer_description);
            //     $('#course-popup').css('display', 'flex').fadeIn(300);
            //     setTimeout(function () { 
            //         $('#course-popup').addClass('show'); 
            //         disableScroll(); // Disable scrolling
            //     }, 10);
            // });
            $('.page-karriere .brxe-block.card-benefits').on('click', function() {
                var contentId = $(this).data('content-id'); // Get the content ID from data attribute
                var offer_description = contentMap[contentId];

                $('#course-description').html(offer_description);
                $('#course-popup').css('display', 'flex').fadeIn(300);
                setTimeout(function() {
                    $('#course-popup').addClass('show');
                    disableScroll(); // Disable scrolling
                }, 10);
            });

            // Close popup function
            function closePopup() {
                $('#course-popup').removeClass('show');
                setTimeout(function() { $('#course-popup').fadeOut(300); }, 300);
                setTimeout(function() { 
                    $('#course-popup').css('display', 'none'); 
                    enableScroll(); // Enable scrolling
                }, 600);
            }

            // Close button click
            $('.close-popup').on('click', function() {
                closePopup();
            });

            // Close popup on outside click
            $(document).on('click', function(event) {
                if ($(event.target).is('#course-popup')) {
                    closePopup();
                }
            });
        });
    </script>
    <style type="text/css">
        #brxe-oudcly .brxe-block.card-benefits {
            cursor: pointer;
        }
    </style>
    <!-- Popup Model for above both ----------------------------- -->
    <div id="course-popup" class="course-prepration-popup pricing-popup" style="display: none;">
        <div class="popup-content">
            <button class="close-popup">&times;</button>
            <div class="popup-body">
                <div id="course-description"></div>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'custom_modal_popup_script');*/

// Online courses emoji pop-up script  -----------------------------------------------------------------------------------
function custom_modal_popup_script()
{
    // Detect current Weglot language
    $current_lang = 'de';
    if (function_exists('weglot_get_current_language')) {
        $current_lang = weglot_get_current_language();
    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $current_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }

    // Helper function for multilingual ACF field names
    function get_translated_field($base_field, $lang, $default = 'de')
    {
        if ($lang === $default) {
            return get_field($base_field); // Default language has no suffix
        }
        return get_field("{$base_field}_{$lang}");
    }

    // Prepare PHP → JS data
    $acf_data = [
        'online_course_prepration_description' => get_translated_field('online_course_prepration_description', $current_lang),
        'cup_content'    => get_translated_field('cup_content', $current_lang),
        'trophy_content' => get_translated_field('trophy_content', $current_lang),
        'books_content'  => get_translated_field('books_content', $current_lang),
        'hands_content'  => get_translated_field('hands_content', $current_lang),
    ];
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Disable/enable scroll
            function disableScroll() {
                $('body').css('overflow', 'hidden');
            }

            function enableScroll() {
                $('body').css('overflow', '');
            }

            // Injected PHP → JS data
            var acfData = <?php echo json_encode($acf_data); ?>;

            // Course details popup
            $('#brxe-course-prepration').on('click', function() {
                $('#course-description').html(acfData.online_course_prepration_description || '');
                $('#course-popup').css('display', 'flex').fadeIn(300);
                setTimeout(function() {
                    $('#course-popup').addClass('show');
                    disableScroll();
                }, 10);
            });

            // Career emoji popup
            var contentMap = {
                'brxe-emoji_content_1': acfData.cup_content,
                'brxe-emoji_content_2': acfData.trophy_content,
                'brxe-emoji_content_3': acfData.books_content,
                'brxe-emoji_content_4': acfData.hands_content
            };

            $('.page-karriere .brxe-block.card-benefits').on('click', function() {
                var contentId = $(this).data('content-id');
                $('#course-description').html(contentMap[contentId] || '');
                $('#course-popup').css('display', 'flex').fadeIn(300);
                setTimeout(function() {
                    $('#course-popup').addClass('show');
                    disableScroll();
                }, 10);
            });

            // Close popup
            function closePopup() {
                $('#course-popup').removeClass('show');
                setTimeout(function() {
                    $('#course-popup').fadeOut(300);
                }, 300);
                setTimeout(function() {
                    $('#course-popup').css('display', 'none');
                    enableScroll();
                }, 600);
            }
            $('.close-popup').on('click', closePopup);
            $(document).on('click', function(event) {
                if ($(event.target).is('#course-popup')) {
                    closePopup();
                }
            });
        });
    </script>
    <style type="text/css">
        #brxe-oudcly .brxe-block.card-benefits {
            cursor: pointer;
        }
    </style>

    <!-- Popup HTML -->
    <div id="course-popup" class="course-prepration-popup pricing-popup" style="display: none;">
        <div class="popup-content">
            <button class="close-popup">&times;</button>
            <div class="popup-body">
                <div id="course-description"></div>
            </div>
        </div>
    </div>
<?php
}
add_action('wp_footer', 'custom_modal_popup_script');


// Remove the calculated image sizes -----------------------------------------------------------------------------------
// add_filter( 'wp_calculate_image_sizes', '__return_false' );

// Remove the calculated image sizes -----------------------------------------------------------------------------------
add_filter('wp_calculate_image_srcset', '__return_false');

// Disable srcset specifically for avatars -----------------------------------------------------------------------------------
add_filter('get_avatar', function ($avatar_html) {
    add_filter('wp_calculate_image_srcset', '__return_false'); // Disable srcset
    // remove_filter('wp_calculate_image_srcset', '__return_false'); // Restore after use
    return $avatar_html;
});


// Tutoring Pricing Section Shortcode -----------------------------------------------------------------------------------
function tutoring_pricing_section_shortcode()
{
    ob_start(); ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <!-- Tutoring Pricing Section Start -->
    <div class="tutoring_pricing_wrapper">
        <!-- <table class="course-tbl">
            <tbody>
                <tr>
                    <th>
                        <h5>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h5>
                        <p>Mittwoch, 13.30 – 15:45 Uhr</p>
                    </th>
                    <td>Küsnacht</td>
                    <td>28.08.25 - 29.10.25</td>
                    <td class="price">1597 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>                                        
                            </div>
                        </div>
                        <a href="#brxe-2c1cab" class="brxe-button bricks-button lg bricks-background-primary">Registerien</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="course-tbl">
            <tbody>
                <tr>
                    <th>
                        <h5>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h5>
                        <p>Mittwoch, 16:00 – 18:15 Uhr</p>
                    </th>
                    <td>Küsnacht</td>
                    <td>20.08.25-29.10.25</td>
                    <td class="price">1597 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 16:00 – 18:15 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>                                        
                            </div>
                        </div>
                        <a href="#brxe-2c1cab" class="brxe-button bricks-button lg bricks-background-primary">Registerien</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="course-tbl">
            <tbody>
                <tr>
                    <th>
                        <h5>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h5>
                        <p>Samstag, 09:30 – 12:00 Uhr</p>
                    </th>
                    <td>Küsnacht</td>
                    <td>23.08.25 - 01.11.25</td>
                    <td class="price">1597 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet samstags an folgenden Daten, jeweils von 9:30 – 12:00 Uhr (je 3 Lektionen, 15 min Pause), statt: </li>
                                    <li><b>23.08.25, 30.08.25, 06.09.25, 13.09.25, 20.09.25, 27.09.25, 04.10.25, 25.10.25, 01.11.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>                                        
                            </div>
                        </div>
                        <a href="#brxe-2c1cab" class="brxe-button bricks-button lg bricks-background-primary">Registerien</a>
                    </td>
                </tr>
            </tbody>
        </table>        -->
        <table class="course-tbl">
            <tbody>
                <tr>
                    <th rowspan="2">
                        <h5>Einzelne Lektionen</h5>
                    </th>
                    <td class="desc">Standard-Tutor</td>
                    <td class="desc">Küsnacht oder Bellevue</td>
                    <td class="price">77.00 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <p>Eine Unterrichtslektion dauert 45 Minuten. Unsere Preise sind von der Mehrwertsteuer befreit. Vorbereitung und Material sind inbegriffen.</p>
                                <!-- <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>   -->
                            </div>
                        </div>
                        <a href="#brxe-zksose" class="brxe-button bricks-button lg bricks-background-primary">Registrieren</a>
                    </td>
                </tr>
                <tr>
                    <td class="desc">Schulleitung</td>
                    <td class="desc">Küsnacht oder Bellevue</td>
                    <td class="price">107.00 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <p>Eine Unterrichtslektion dauert 45 Minuten. Unsere Preise sind von der Mehrwertsteuer befreit. Vorbereitung und Material sind inbegriffen.</p>
                                <!-- <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>  -->
                            </div>
                        </div>
                        <a href="#brxe-zksose" class="brxe-button bricks-button lg bricks-background-primary">Registrieren</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="course-tbl">
            <tbody>
                <tr>
                    <th rowspan="2">
                        <h5>Zwei-Personen-Kurse</h5>
                    </th>
                    <td class="desc">Standard-Tutor</td>
                    <td class="desc">Küsnacht oder Bellevue</td>
                    <td class="price">67.00 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <p>Eine Unterrichtslektion dauert 45 Minuten. Unsere Preise sind von der Mehrwertsteuer befreit. Vorbereitung und Material sind inbegriffen.</p>
                                <!-- <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>    -->
                            </div>
                        </div>
                        <a href="#brxe-zksose" class="brxe-button bricks-button lg bricks-background-primary">Registrieren</a>
                    </td>
                </tr>
                <tr>
                    <td class="desc">Schulleitung</td>
                    <td class="desc">Küsnacht oder Bellevue</td>
                    <td class="price">87.00 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <p>Eine Unterrichtslektion dauert 45 Minuten. Unsere Preise sind von der Mehrwertsteuer befreit. Vorbereitung und Material sind inbegriffen.</p>
                                <!-- <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>    -->
                            </div>
                        </div>
                        <a href="#brxe-zksose" class="brxe-button bricks-button lg bricks-background-primary">Registrieren</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="course-tbl">
            <tbody>
                <tr>
                    <th rowspan="1">
                        <h5>Reisekostenpauschale</h5>
                    </th>
                    <td class="desc">Nachhilfeunterricht ausser Haus</td>
                    <td class="desc">Küsnacht oder Bellevue</td>
                    <td class="price">25.00 CHF</td>
                    <td class="btn-action">
                        <div class="btn-tooltip">
                            <i class="fas fa-exclamation"></i>
                            <div class="tooltip_popup">
                                <p>Die Kosten von 25 CHF gelten für die Gemeinden Seefeld, Zollikon, Küsnacht, Erlenbach und Meilen. Für andere Gemeinden müssen individuelle Vereinbarungen getroffen werden.</p>
                                <!-- <h3>Langgymikurs ZAP-Vorbereitung Gymiprüfung</h3>
                                <ul>
                                    <li>Der Kurs findet mittwochs an folgenden Daten, jeweils von 13.30 – 15:45 Uhr (je 3 Lektionen), statt: </li>
                                    <li><b>20.08.25, 27.08.25, 03.09.25, 10.09.25, 17.09.25, 24.09.25, 01.10.25, 22.10.25, 29.10.25</b></li>
                                    <li>
                                        <h6>Kosten</h6>
                                        <p>1597 CHF</p>
                                    </li>
                                </ul>   -->
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Tutoring Pricing Section End -->

    <?php return ob_get_clean();
}
add_shortcode('tutoring_pricing', 'tutoring_pricing_section_shortcode');


// Handle user registration ajax -----------------------------------------------------------------------------------
function handle_custom_registration_ajax()
{
    check_ajax_referer('ajax-custom-registration-nonce', 'custom_registration_nonce');

    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $confirm_password = sanitize_text_field($_POST['confirm_password']);
    $want_mails = isset($_POST['want_mails']) ? sanitize_text_field($_POST['want_mails']) : '';
    $phone = sanitize_text_field($_POST['billing_phone'] ?? ''); // Full international number

    if (!is_email($email)) {
        $email_error = esc_html__('Please enter a valid email address.', 'astra-child');
        wp_send_json_error(array('email' => $email_error));
    }

    if ($password !== $confirm_password) {
        wp_send_json_error(array('message' => 'Passwords do not match.'));
    }

    // ✅ Add this check here
    if (email_exists($email)) {
        wp_send_json_error(array(
            'message' => 'Der Benutzername existiert bereits!',
            'redirect' => site_url('/mein-konto/')
        ));
    }

    $userdata = array(
        'user_login' => $email,
        'user_email' => $email,
        'user_pass' => $password,
        'meta_input' => array(
            'billing_phone' => $phone
        )
    );

    $user_id = wp_insert_user($userdata);

    if (!is_wp_error($user_id)) {
        // Generate a verification code
        $verification_code = wp_rand(100000, 999999);

        // Store the verification code in a transient (valid for 10 minutes)
        set_transient('verification_code_' . $user_id, $verification_code, 600);

        // Trigger the WooCommerce "New Account" email
        WC()->mailer()->customer_new_account($user_id);

        // Send email with the verification code
        // wp_mail($email, 'Your Verification Code', 'Your registration verification code is: ' . $verification_code);

        // $subject = __('Dein Verifizierungscode', 'woocommerce');

        // $message = sprintf(
        //     __("Dein Verifizierungscode für die Registrierungsseite lautet: %s\n\nBitte gebe diesen Code ein, um den Registrierung abzuschliessen.\n\nFalls du diesen Code nicht angefordert haben, ignoriere diese Nachricht.\n\nLiebe Grüsse\nDein studypeak-Team", 'woocommerce'),
        //         $verification_code
        // );

        // // Send email with the verification code
        // wp_mail($email, $subject, $message, array('Content-Type: text/plain; charset=UTF-8'));

        $subject = __('Dein Verifizierungscode', 'woocommerce');

        /*$message = sprintf(
            __("Dein Verifizierungscode für die Registrierungsseite lautet: <strong>%s</strong><br><br>Bitte gebe diesen Code ein, um den Registrierung abzuschliessen.<br><br>Falls du diesen Code nicht angefordert haben, ignoriere diese Nachricht.<br><br>Liebe Grüsse<br>Dein studypeak-Team", 'woocommerce'),
            $verification_code
        );*/
        // Get the dynamic site URL
        $site_url = site_url();

        // HTML Email Template
        $email_template_register = <<<EOT
            <!DOCTYPE html>
            <html><head><link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,800&display=swap"
                    rel="stylesheet">
                <table cellpadding="0" cellspacing="0"
                    style="width:600px;margin:auto;font-family:Arial,Helvetica,sans-serif;background:#fbfbfb;font-family: "Poppins", sans-serif;">
                    <thead>
                        <tr>
                            <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                                <a href="{$site_url}">
                                    <img src="{$site_url}/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="140">
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                            <td style="background:#ffffff;padding:20px;text-align: center; font-size: 20px; line-height: 20px; font-weight: 700;">
                                <p style="text-align: center; font-size: 20px; line-height: 20px; font-weight: 700; margin: 0;">Verifizierungscode</p>
                            </td>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                        </tr>
                        <tr>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Dein Verifizierungscode für die Registrierungsseite lautet:
                                </p>
                                <div style="text-align: center; font-size: 26px; line-height: 30px; font-weight: 700; padding: 20px; background-color: #EDF4F1; margin: 0px 0px 20px;">
                                    $verification_code
                                </div>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Bitte gebe diesen Code ein, um die Registrierung abzuschliessen.
                                </p>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Falls du diesen Code nicht angefordert haben, ignoriere diese Nachricht.
                                </p>
                            </td>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                        </tr>
                        <tr>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 700; margin: 0px;">
                                    Liebe Grüsse
                                </p>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Dein studypeak-Team
                                </p>                
                            </td>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                        </tr>
                    </tbody>
                </table>
                </body></html>
        EOT;

        // Send email with the verification code in HTML format
        wp_mail($email, $subject, $email_template_register, array('Content-Type: text/html; charset=UTF-8'));

        // Send response to indicate that registration was successful
        wp_send_json_success(array('message' => esc_html__('Registration successful. Please check your email for the verification code.', 'astra-child'), 'user_id' => $user_id));
    } else {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
    }
}
add_action('wp_ajax_custom_registration', 'handle_custom_registration_ajax');
add_action('wp_ajax_nopriv_custom_registration', 'handle_custom_registration_ajax');

// Handle user registration verification ajax -----------------------------------------------------------------------------------
function handle_verification_code()
{
    check_ajax_referer('ajax-custom-registration-nonce', 'custom_registration_nonce');

    $user_id = intval($_POST['user_id']);
    $verification_code = intval($_POST['verification_code']);

    $stored_code = get_transient('verification_code_' . $user_id);

    if ($verification_code == $stored_code) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        delete_transient('verification_code_' . $user_id); // Remove transient
        wp_send_json_success(array('message' => esc_html__('Verifizierung erfolgreich!', 'astra-child'), 'url' => home_url('/mein-profil/')));
    } else {
        wp_send_json_error(array('message' => esc_html__('Ungültiger Bestätigungscode. Bitte versuchen Sie es erneut.', 'astra-child')));
    }
}
add_action('wp_ajax_verify_verification_code', 'handle_verification_code');
add_action('wp_ajax_nopriv_verify_verification_code', 'handle_verification_code');

// wp_mail('parth.jogi@nyusoft.com', 'Test Email', 'This is a test email.');

// Handle user login ajax -----------------------------------------------------------------------------------
function custom_login()
{
    // Verify nonce
    if (!isset($_POST['custom_login_nonce']) || !wp_verify_nonce($_POST['custom_login_nonce'], 'ajax-custom-login-nonce')) {
        wp_send_json_error(array('message' => __('Nonce verification failed', 'woocommerce')));
    }

    // Get login credentials
    $username = sanitize_email($_POST['username']);
    $password = sanitize_text_field($_POST['password']);

    // Authenticate user
    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        $error_message = $user->get_error_message();
        $lost_password_link = '<a href="' . esc_url(wp_lostpassword_url()) . '">Passwort vergessen?</a>';

        // Customize error messages based on error codes
        if ($user->get_error_code() === 'invalid_username') {
            $error_message = sprintf(
                '<strong>Fehler:</strong> Der Benutzername <strong>%s</strong> wurde nicht gefunden. <strong>%s</strong>',
                esc_html($username),
                $lost_password_link
            );
        } elseif ($user->get_error_code() === 'incorrect_password') {
            $error_message = sprintf(
                '<strong>Fehler:</strong> Das eingegebene Passwort für den Benutzernamen <strong>%s</strong> ist nicht korrekt. <strong>%s</strong>',
                esc_html($username),
                $lost_password_link
            );
        } else {
            $error_message = sprintf(
                '<strong>Fehler:</strong> Der Benutzername oder das Passwort ist nicht korrekt. <strong>%s</strong>',
                $lost_password_link
            );
        }

        wp_send_json_error(array('message' => $error_message));
        exit;
    }

    // Check if the email is "kontakt@adicum.ch" and bypass 2FA
    if ($username === 'kontakt@adicum.ch') {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_send_json_success(array('message' => 'Logged in without 2FA'));
    } else {
        // Successful login
        $verification_code = wp_rand(100000, 999999);
        update_user_meta($user->ID, 'verification_code', $verification_code);

        // $subject = __('Your Verification Code', 'woocommerce');
        // $message = __('Your login verification code is: ', 'woocommerce') . $verification_code;
        // $subject = __('Dein Verifizierungscode', 'woocommerce');

        // $message = sprintf(
        //     __("Dein Verifizierungscode für die Anmeldeseite lautet: %s\n\nBitte gebe diesen Code ein, um den Login abzuschliessen.\n\nFalls du diesen Code nicht angefordert haben, ignoriere diese Nachricht.\n\nLiebe Grüsse\nDein studypeak-Team", 'woocommerce'),
        //         $verification_code
        // );

        $subject = __('Dein Verifizierungscode', 'woocommerce');

        // Email message with HTML formatting
        /*$message = sprintf(
            __("Dein Verifizierungscode für die Anmeldeseite lautet: <strong>%s</strong><br><br>Bitte gebe diesen Code ein, um den Login abzuschliessen.<br><br>Falls du diesen Code nicht angefordert haben, ignoriere diese Nachricht.<br><br>Liebe Grüsse<br>Dein studypeak-Team", 'woocommerce'),
            $verification_code
        );*/
        // Get the dynamic site URL
        $site_url = site_url();

        // HTML Email Template
        $email_template_login = <<<EOT
            <!DOCTYPE html>
            <html><head><link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,800&display=swap"
                    rel="stylesheet">
                <table cellpadding="0" cellspacing="0"
                    style="width:600px;margin:auto;font-family:Arial,Helvetica,sans-serif;background:#fbfbfb;font-family: "Poppins", sans-serif;">
                    <thead>
                        <tr>
                            <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                                <a href="{$site_url}">
                                    <img src="{$site_url}/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="140">
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                            <td style="background:#ffffff;padding:20px;text-align: center; font-size: 20px; line-height: 20px; font-weight: 700;">
                                <p style="text-align: center; font-size: 20px; line-height: 20px; font-weight: 700; margin: 0;">Verifizierungscode</p>
                            </td>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                        </tr>
                        <tr>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Dein Verifizierungscode für die Anmeldeseite lautet:
                                </p>
                                <div style="text-align: center; font-size: 26px; line-height: 30px; font-weight: 700; padding: 20px; background-color: #EDF4F1; margin: 0px 0px 20px;">
                                    $verification_code
                                </div>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Bitte gib diesen Code ein, um die Login abzuschliessen.
                                </p>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Falls du diesen Code nicht angefordert hast, ignoriere diese Nachricht.
                                </p>
                            </td>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                        </tr>
                        <tr>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 700; margin: 0px;">
                                    Liebe Grüsse
                                </p>
                                <p style="text-align: left; font-size: 16px; line-height: 20px; font-weight: 400; margin: 0px 0px 20px;">
                                    Dein studypeak-Team
                                </p>                
                            </td>
                            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
                        </tr>
                    </tbody>
                </table>
                </body></html>
        EOT;
        // Set email headers to support HTML content
        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (wp_mail($username, $subject, $email_template_login, $headers)) {
            wp_send_json_success(array('user_id' => $user->ID));
        } else {
            wp_send_json_error(array('message' => __('Das Senden der Bestätigungs-E-Mail ist fehlgeschlagen. Versuchen Sie es bitte erneut.', 'woocommerce')));
        }
    }
}


add_action('wp_ajax_custom_login', 'custom_login');
add_action('wp_ajax_nopriv_custom_login', 'custom_login');

// Handle user login verification ajax -----------------------------------------------------------------------------------
function verify_2fa()
{
    if (!isset($_POST['custom_login_nonce']) || !wp_verify_nonce($_POST['custom_login_nonce'], 'ajax-custom-login-nonce')) {
        wp_send_json_error(array('message' => __('Nonce verification failed', 'woocommerce')));
    }

    $user_id = intval($_POST['user_id']);
    $verification_code = intval($_POST['verification_login_code']);

    $stored_code = get_user_meta($user_id, 'verification_code', true);

    if ($verification_code == $stored_code) {
        delete_user_meta($user_id, 'verification_code');

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        wp_send_json_success(array('message' => __('Verifizierung erfolgreich!', 'woocommerce'), 'url' => home_url('/mein-profil/')));
    } else {
        wp_send_json_error(array('message' => __('Ungültiger Bestätigungscode. Bitte versuchen Sie es erneut.', 'woocommerce')));
    }
}
add_action('wp_ajax_verify_2fa', 'verify_2fa');
add_action('wp_ajax_nopriv_verify_2fa', 'verify_2fa');


// Handle custom reset password form -----------------------------------------------------------------------------------
// add_action('init', 'custom_open_reset_password_form');
// function custom_open_reset_password_form() {
//     // Intercept WooCommerce "Lost Password" form submission
//     if (isset($_POST['woocommerce-lost-password-nonce']) && wp_verify_nonce($_POST['woocommerce-lost-password-nonce'], 'lost_password')) {

//         $user_login = sanitize_text_field($_POST['user_login']);
//         $user = get_user_by('email', $user_login);

//         if (!$user) {
//             wc_add_notice(__('Invalid email address. Please try again.', 'woocommerce'), 'error');
//             return;
//         }

//         // Generate password reset key
//         $reset_key = get_password_reset_key($user);

//         if (is_wp_error($reset_key)) {
//             wc_add_notice(__('Could not generate reset key. Try again later.', 'woocommerce'), 'error');
//             return;
//         }

//         // Redirect to WooCommerce Reset Password form with key and login parameters
//         wp_redirect(add_query_arg([
//             'key'   => $reset_key,
//             'login' => rawurlencode($user->user_login),
//         ], wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'))));

//         exit;
//     }
// }

if (!is_user_logged_in()) {
    add_action('woocommerce_checkout_order_processed', function ($order_id) {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        $email = $order->get_billing_email();

        // Check if the current user is logged in
        // error_log("Skipping account creation: User is already logged in.");
        // return;

        // Check if the user exists in the database
        // if (email_exists($email)) {
        //     error_log("Skipping account creation: User already exists with email " . $email);
        //     wp_set_auth_cookie($email, true);
        //     wp_set_current_user($email);
        //     return;
        // }

        // Call the function only if user is a guest and does not exist
        create_account_for_guest_users($order_id);
    }, 10, 1);
}

function create_account_for_guest_users($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    $email = $order->get_billing_email();
    $first_name = $order->get_billing_first_name();
    $last_name = $order->get_billing_last_name();

    // Check if the user already exists
    $user = get_user_by('email', $email);
    // if ($user) {
    //     // Auto-login the existing user
    //     error_log("User already exists. Logging in: " . $email);
    //     wp_set_auth_cookie($user->ID, true);
    //     wp_set_current_user($user->ID);
    //     return;
    // }

    // Generate a random password
    $password = wp_generate_password(12, true);

    // Suppress password change email notifications
    add_filter('send_password_change_email', '__return_false');

    // Create the new user
    // $user_id = wp_create_user($email, $password, $email);

    // if (is_wp_error($user_id)) {
    //     error_log("User creation failed for: " . $email);
    //     return;
    // }

    // Update user details
    wp_update_user([
        'ID'           => $user->ID,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => $first_name . ' ' . $last_name,
        'user_email'   => $email,
        'user_pass'    => $password,
    ]);

    // Restore email sending after user update
    remove_filter('send_password_change_email', '__return_false');

    // Trigger WooCommerce account creation email
    // do_action('woocommerce_created_customer', $user->ID, $password, true);
    // remove_action( 'after_password_reset', 'wp_password_change_notification' );
    // add_filter( 'send_password_change_email', '__return_false' );
    // Send custom welcome email
    remove_action('after_password_reset', 'wp_password_change_notification');
    send_welcome_email($email, $password, $first_name, $last_name);

    // Auto-login the new user
    error_log("Logging in new user: " . $email);
    wp_set_auth_cookie($user->ID, true);
    wp_set_current_user($user->ID);
}

function send_welcome_email($email, $password, $first_name, $last_name)
{
    // Combine first and last name
    $full_name = trim("$first_name $last_name");

    // Set subject
    $subject = 'Dein Zugang zu studypeak';

    // Set headers
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Email body
    $message = '
    <html>
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body>
        <table cellpadding="0" cellspacing="0" style="width:600px;margin:auto;background:#edf1f7;font-family:\'Poppins\', Arial, sans-serif;">
            <thead>
                <tr>
                    <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                        <a href="' . site_url() . '">
                            <img src="' . site_url() . '/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="120">
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                    <td style="background:#ffffff;padding:20px;text-align:left; font-size:14px; line-height:22px;">
                        <p style="margin-top: 0;">Hallo <strong>' . esc_html($full_name) . '</strong></p>
                        <p>Vielen Dank für deinen Kauf bei studypeak!</p>
                        <p>Hier sind deine Zugangsdaten zu deinem persönlichen Bereich:</p>
                        <p><strong>Benutzername:</strong> ' . esc_html($email) . '<br>
                        <strong>Passwort:</strong> ' . esc_html($password) . '</p>
                        <p>Bitte bewahre dieses Passwort sicher auf. Nach der ersten Anmeldung kannst du es jederzeit in deinem Benutzerkonto ändern.</p>
                        <p>Anmelden kannst du dich hier: <a href="' . site_url('/mein-konto/') . '">' . site_url('/mein-konto/') . '</a></p>
                        <p>Bei Fragen oder Problemen stehen wir dir gerne zur Verfügung:</p>
                        <ul style="padding-left: 20px;">
                            <li>E-Mail: <a href="mailto:info@studypeak.ch">info@studypeak.ch</a></li>
                            <li>Telefon/WhatsApp: +41 77 253 11 00</li>
                        </ul>
                        <p>Wir wünschen dir viel Erfolg und Freude mit unseren Lernmaterialien!</p>
                        <p>Liebe Grüsse<br>
                        Dein studypeak-Team</p>
                    </td>
                    <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                </tr>
            </tbody>
        </table>
    </body>
    </html>
    ';

    // Send the email
    $sent = wp_mail($email, $subject, $message, $headers);

    if (!$sent) {
        error_log("Email sending failed for: " . $email);
    } else {
        error_log("Welcome email sent to: " . $email);
    }
}


add_action('woocommerce_order_status_processing', 'auto_complete_processing_orders');

function auto_complete_processing_orders($order_id)
{
    if (!$order_id) return;

    $order = wc_get_order($order_id);

    // Ensure it's not already completed
    if ($order->get_status() !== 'completed') {
        $order->update_status('completed', __('Order automatically completed.', 'woocommerce'));
    }
}



// Remove header and footer from the registration pages -----------------------------------------------------------------------------------
add_action('wp_head', function () {
    if (!is_user_logged_in() && (is_account_page() || is_page(6721))) {
    ?>
        <style>
            header,
            footer {
                display: none !important;
            }
        </style>
        <?php
    }
});


// Strength meter remove from the reset password page -----------------------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    // Dequeue the password strength meter script
    wp_dequeue_script('wc-password-strength-meter');
});
add_filter('woocommerce_form_field_args', function ($args, $key, $value) {
    // Remove the invalid class WooCommerce applies
    if (isset($args['class'])) {
        $args['class'] = array_diff($args['class'], ['woocommerce-invalid']);
    }
    return $args;
}, 10, 3);


// REDIRECT MY-ACCOUNT TO MY-PROFILE -----------------------------------------------------------------------------------
function redirect_logged_in_user_from_my_account()
{
    if (is_user_logged_in() && is_account_page() && is_lost_password_page()) {
        // Change this URL to the page where you want to redirect the user
        $redirect_url = home_url('/my-profile/');

        // Prevent redirection loops by checking the current URL
        wp_safe_redirect($redirect_url);
        exit;
    }
}
add_action('template_redirect', 'redirect_logged_in_user_from_my_account');


function keep_me_logged_in_for_longer($expire)
{
    return 86400 * 30; // 30 days
}
add_filter('auth_cookie_expiration', 'keep_me_logged_in_for_longer');


// add_action( 'learndash-course-content-list-before', 'mc_course_return_group_link' );

// function mc_course_return_group_link() {
// if ( function_exists( 'learndash_get_groups' ) ) {
//     $group_users = learndash_get_groups( $group_id );
//     print_r($group_users);
//     if ( ! empty( $group_users ) ) {
//             $user_info = get_userdata( $user_id );
//             echo 'User ID: ' . $user_id . ' - User Name: ' . $group_users . '<br>';
//     } else {
//         echo 'No users found in this group.';
//     }
// } else {
//     echo 'The function learndash_get_groups_user_ids does not exist.';
// }
// }


// UPDATE USER PROFILE --------------------------------------------------------------------------------
function handle_save_profile()
{
    check_ajax_referer('profile_ajax_nonce', 'nonce');

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $country = sanitize_text_field($_POST['country']);
    $state = sanitize_text_field($_POST['state']);
    $avatar_img = $_POST['avatar_img'];

    wp_update_user([
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_email' => $email,
    ]);

    update_user_meta($user_id, 'billing_phone', $phone);
    update_user_meta($user_id, 'custom_username', $username);
    update_user_meta($user_id, 'country', $country);

    // Update WooCommerce billing country
    update_user_meta($user_id, 'billing_country', $country);
    update_user_meta($user_id, 'billing_state', $state);

    $img = '';
    if (!empty($_FILES['profile_pic']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploadedfile = $_FILES['profile_pic'];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $img = $movefile['url'];
            update_user_meta($user_id, 'profile_picture', $movefile['url']);
        }
    } else if ($avatar_img != '') {
        update_user_meta($user_id, 'profile_picture', $avatar_img);
    } else {
    }

    $msg = esc_html__('Profile updated successfully.', 'astra-child');

    wp_send_json_success(['message' => '<span class="success">' . $msg . '</span>', 'img' => $img]);
}
add_action('wp_ajax_save_profile', 'handle_save_profile');
add_action('wp_ajax_nopriv_save_profile', 'handle_save_profile');
function get_states_by_country()
{
    if (isset($_POST['country'])) {
        $country = sanitize_text_field($_POST['country']);
        $states = WC()->countries->get_states($country);

        if (!empty($states)) {
            foreach ($states as $state_code => $state_name) {
                echo '<option value="' . esc_attr($state_code) . '">' . esc_html($state_name) . '</option>';
            }
        } else {
            echo '<option value="">' . __('Keine Staaten verfügbar', 'woocommerce') . '</option>';
        }
    }
    wp_die();
}

add_action('wp_ajax_get_states', 'get_states_by_country');
add_action('wp_ajax_nopriv_get_states', 'get_states_by_country');


// SET USER NEW PASSWORD --------------------------------------------------------------------------------
function handle_save_password()
{
    check_ajax_referer('profile_ajax_nonce', 'nonce');

    $current_user = wp_get_current_user();
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $repeat_new_password = $_POST['repeat_new_password'];

    if (wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {

        wp_set_password($new_password, $current_user->ID);

        // Log the user back in after password change
        wp_clear_auth_cookie();
        wp_set_auth_cookie($current_user->ID);

        $msg = esc_html__('Password changed successfully', 'astra-child');
        wp_send_json_success(['message' => '<span class="success">' . $msg . '</span>']);
    } else {

        $msg = esc_html__('The current password is incorrect.', 'astra-child');
        wp_send_json_success(['message' => '<span class="error">' . $msg . '</span>']);
    }
}
add_action('wp_ajax_save_password', 'handle_save_password');


// RESET USER DATA --------------------------------------------------------------------------------
function handle_reset_progress()
{
    check_ajax_referer('profile_ajax_nonce', 'nonce');

    if ($_POST['confirm_reset'] === 'RESET' || $_POST['confirm_reset'] === 'ZURÜCKSETZEN') {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        if ($user_id) {
            if (function_exists('learndash_delete_user_data')) {
                learndash_delete_user_data($user_id);
            }

            if (function_exists('learndash_delete_course_progress')) {

                // Get all course IDs
                $course_ids = learndash_user_get_enrolled_courses($user_id);
                foreach ($course_ids as $course_id) {
                    learndash_delete_course_progress($course_id, $user_id);
                }
            }

            if (function_exists('learndash_delete_quiz_progress')) {

                // Get all quiz IDs
                $quiz_ids = learndash_get_user_quiz_attempts($user_id);
                foreach ($quiz_ids as $quiz_id) {
                    learndash_delete_quiz_progress($user_id, $quiz_id);
                }
            }

            //Assigment remove
            $assignments = get_posts([
                'post_type' => 'sfwd-assignment',
                'post_status' => 'any',
                'author' => $user_id,
                'numberposts' => -1
            ]);

            foreach ($assignments as $assignment) {
                wp_delete_post($assignment->ID, true);
            }

            //Delete Certificate
            delete_user_meta($user_id, 'ld_certificate');

            //Clear Essay Data
            $essays = get_posts(['post_type' => 'sfwd-essays', 'post_status' => 'any', 'author' => $user_id, 'numberposts' => -1]);
            foreach ($essays as $essay) {
                wp_delete_post($essay->ID, true);
            }

            //Clear User Course Points
            delete_user_meta($user_id, 'course_points');

            //Clear User Course Expiration Data
            global $wpdb;
            $wpdb->delete("{$wpdb->prefix}usermeta", ['user_id' => $user_id, 'meta_key' => '_sfwd-course_expiration']);

            // Reset learning progress logic here
            $msg = esc_html__('Learning progress reset successfully.', 'astra-child');
            wp_send_json_success(['message' => '<span class="success">' . $msg . '</span>']);
        } else {

            // Reset learning progress logic here
            $msg = esc_html__('Something went wrong.', 'bricks-child');
            wp_send_json_success(['message' => '<span class="error">' . $msg . '</span>']);
        }
    } else {
        $msg = esc_html__('Reset failed!', 'bricks-child');
        wp_send_json_success(['message' => '<span class="error">' . $msg . '</span>']);
    }
}
add_action('wp_ajax_reset_progress', 'handle_reset_progress');


// DELETE USER ------------------------------------------------------------------------------------
function handle_delete_progress()
{
    check_ajax_referer('profile_ajax_nonce', 'nonce');

    if ($_POST['confirm_delete'] === 'DELETE' || $_POST['confirm_delete'] === 'LÖSCHEN') {
        $current_user = wp_get_current_user();

        $user_id = $current_user->ID;

        if ($user_id) {
            if (function_exists('learndash_delete_user_data')) {
                learndash_delete_user_data($user_id);
            }

            if (function_exists('learndash_delete_course_progress')) {
                // Get all course IDs
                $course_ids = learndash_user_get_enrolled_courses($user_id);
                foreach ($course_ids as $course_id) {
                    learndash_delete_course_progress($course_id, $user_id);
                }
            }

            if (function_exists('learndash_delete_quiz_progress')) {

                // Get all quiz IDs
                $quiz_ids = learndash_get_user_quiz_attempts($user_id);
                foreach ($quiz_ids as $quiz_id) {
                    learndash_delete_quiz_progress($user_id, $quiz_id);
                }
            }

            //Assigment remove
            $assignments = get_posts([
                'post_type' => 'sfwd-assignment',
                'post_status' => 'any',
                'author' => $user_id,
                'numberposts' => -1
            ]);

            foreach ($assignments as $assignment) {
                wp_delete_post($assignment->ID, true);
            }

            //Delete Certificate
            delete_user_meta($user_id, 'ld_certificate');

            //Clear Essay Data
            $essays = get_posts(['post_type' => 'sfwd-essays', 'post_status' => 'any', 'author' => $user_id, 'numberposts' => -1]);
            foreach ($essays as $essay) {
                wp_delete_post($essay->ID, true);
            }

            //Clear User Course Points
            delete_user_meta($user_id, 'course_points');

            //Clear User Course Expiration Data
            global $wpdb;
            $wpdb->delete("{$wpdb->prefix}usermeta", ['user_id' => $user_id, 'meta_key' => '_sfwd-course_expiration']);


            // Delete WooCommerce customer data
            if (class_exists('WC_Data_Store')) {
                $order_data_store = WC_Data_Store::load('customer');
                $order_data_store->delete_by_user_id($user_id);
            }

            // Delete the user account
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($user_id);

            // Change this URL to the page where you want to redirect the user
            $redirect_url = home_url();

            wp_send_json_success(['redirect' => $redirect_url]);
        } else {

            // Reset learning progress logic here
            $msg = esc_html__('Something went wrong.', 'bricks-child');
            wp_send_json_success(['message' => '<span class="error">' . $msg . '</span>']);
        }
    } else {
        $msg = esc_html__('Delete failed!', 'bricks-child');
        wp_send_json_success(['message' => '<span class="error">' . $msg . '</span>']);
    }
}
add_action('wp_ajax_delete_progress', 'handle_delete_progress');


// USER PROFILE Handle file upload --------------------------------------------------------------------------------
add_action('wp_ajax_upload_profile_picture', 'handle_profile_picture_upload');
function handle_profile_picture_upload()
{
    // Check user capability
    if (!current_user_can('upload_files')) {
        wp_send_json_error(['message' => __('Permission denied.', 'bricks-child')]);
    }

    if (!isset($_FILES['profile_pic']) || empty($_FILES['profile_pic'])) {
        wp_send_json_error(['message' => __('No file uploaded.', 'bricks-child')]);
    }

    $file = $_FILES['profile_pic'];
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (!empty($upload['error'])) {
        wp_send_json_error(['message' => $upload['error']]);
    }

    // Move the file to the /assets/images/avatars/ directory
    $dir_path = get_stylesheet_directory() . '/assets/images/avatars/';
    if (!file_exists($dir_path)) {
        mkdir($dir_path, 0755, true);
    }

    $filename = basename($upload['file']);
    $new_file_path = $dir_path . $filename;

    if (!rename($upload['file'], $new_file_path)) {
        wp_send_json_error(['message' => __('Failed to move file.', 'bricks-child')]);
    }

    $file_url = get_stylesheet_directory_uri() . '/assets/images/avatars/' . $filename;

    wp_send_json_success(['url' => $file_url, 'message' => __('File uploaded successfully.', 'bricks-child')]);
}


// Add AJAX Action for Updating Profile Image --------------------------------------------------------------------------------
add_action('wp_ajax_save_profile_image', 'save_user_profile_image');
function save_user_profile_image()
{
    // Validate the request
    if (!isset($_POST['img_url']) || empty($_POST['img_url'])) {
        wp_send_json_error(['message' => __('Invalid image URL.', 'bricks-child')]);
    }

    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => __('User not logged in.', 'bricks-child')]);
    }

    $img_url = esc_url_raw($_POST['img_url']);

    // Update user meta
    $update = update_user_meta($user_id, 'moopenid_user_avatar', $img_url);

    if ($update) {
        wp_send_json_success(['message' => __('Profile image updated successfully.', 'bricks-child')]);
    } else {
        wp_send_json_error(['message' => __('Failed to update profile image.', 'bricks-child')]);
    }
}


// Add to cart for the transation history page --------------------------------------------------------------------------------
add_action('wp_ajax_add_to_cart_with_group', 'handle_ajax_add_to_cart_with_group');
add_action('wp_ajax_nopriv_add_to_cart_with_group', 'handle_ajax_add_to_cart_with_group');

function handle_ajax_add_to_cart_with_group()
{

    // Check for required values
    if (!isset($_POST['product_id'], $_POST['variation_id'], $_POST['group_id'])) {
        wp_send_json_error(array('message' => __('Required data missing.', 'bricks-child')));
    }

    // Sanitize and get values
    $product_id   = absint($_POST['product_id']);
    $variation_id = absint($_POST['variation_id']);
    $is_renew     = absint($_POST['is_renew']);
    $group_id     = sanitize_text_field($_POST['group_id']);
    $coupon_code  = '10OFF';

    // Avoid duplicate entries in the cart
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] === $product_id && isset($cart_item['group_id']) && $cart_item['group_id'] === $group_id && $cart_item['variation_id'] === $variation_id) {
            wp_send_json_success(); // Product already in cart
        }
    }

    // Add product (with variation) to the cart, including group_id as metadata
    $added = WC()->cart->add_to_cart($product_id, 1, $variation_id, array(), array('group_id' => $group_id));

    if ($added) {
        if ($is_renew) {
            WC()->cart->apply_coupon($coupon_code);
            WC()->cart->calculate_totals();
        }
        wp_send_json_success(); // Successfully added to cart
    } else {
        wp_send_json_error(array('message' => __('Failed to add product to cart.', 'bricks-child')));
    }
}


// add_action('woocommerce_before_calculate_totals', 'handle_add_to_cart_with_group');
// function handle_add_to_cart_with_group($cart) {
//     if (is_admin() || !defined('DOING_AJAX')) return;

//     if (isset($_GET['group_id'], $_GET['add-to-cart'])) {
//         $group_id = sanitize_text_field($_GET['group_id']);
//         $product_id = absint($_GET['add-to-cart']);

//         // If this is a variable product, we need to pass the variation ID as well
//         if (isset($_GET['variation_id']) && $_GET['variation_id'] > 0) {
//             $variation_id = absint($_GET['variation_id']);
//         } else {
//             $variation_id = 0;  // For simple products, variation_id will be 0
//         }

//         // Avoid duplicate entries in the cart based on product ID and group ID
//         foreach ($cart->get_cart() as $cart_item) {
//             if ($cart_item['product_id'] === $product_id && isset($cart_item['group_id']) && $cart_item['group_id'] === $group_id && $cart_item['variation_id'] === $variation_id) {
//                 return;  // Avoid adding duplicate items
//             }
//         }

//         // Add product (with or without variation) to the cart with group ID as metadata
//         WC()->cart->add_to_cart($product_id, 1, $variation_id, array(), array('group_id' => $group_id));
//     }
// }
// add_filter('woocommerce_get_item_data', 'display_group_id_in_cart', 10, 2);
// function display_group_id_in_cart($item_data, $cart_item) {
//     if (isset($cart_item['group_id'])) {
//         $item_data[] = array(
//             'name'  => __('Group ID', 'bricks-child'),
//             'value' => esc_html($cart_item['group_id'])
//         );
//     }

//     // If a variation ID is set, you may want to display it too
//     if ($cart_item['variation_id'] > 0) {
//         $item_data[] = array(
//             'name'  => __('Variation ID', 'bricks-child'),
//             'value' => esc_html($cart_item['variation_id'])
//         );
//     }

//     return $item_data;
// }


// Get Courses by group id in my courses page --------------------------------------------------------------------------------
add_action('wp_ajax_get_courses_by_group', 'get_courses_by_group');
add_action('wp_ajax_nopriv_get_courses_by_group', 'get_courses_by_group');

function get_courses_by_group()
{
    $group_id       = $_POST['group_id'];

    /*do_action('wpml_switch_language', 'de');
    $course_list    = learndash_get_group_courses_list($group_id);
    do_action('wpml_switch_language', ICL_LANGUAGE_CODE);
    $ordered_courses = array();
    foreach ($course_list as $courseid) {
        $course = get_post($courseid);
        $ordered_courses[] = array(
            'title' => $course->post_title,
            'id' => $courseid
        );
    }

    // Sort the courses array by title
    usort($ordered_courses, function($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    });*/

    // Define Gymivorbereitung Package group ID (Replace with actual ID)
    $gymivorbereitung_group_id = 32332; // Update with the correct Group ID

    do_action('wpml_switch_language', 'de');
    $course_list = learndash_get_group_courses_list($group_id);
    do_action('wpml_switch_language', ICL_LANGUAGE_CODE);

    $ordered_courses = array();
    foreach ($course_list as $courseid) {
        $course = get_post($courseid);
        $ordered_courses[] = array(
            'title' => $course->post_title,
            'id' => $courseid
        );
    }

    // Custom sorting for Gymivorbereitung Package group
    if ($group_id == $gymivorbereitung_group_id) {
        $custom_order = [
            'Mathematik Langzeitgymnasium',
            'Mathematik Prüfungssimulation',
            'Deutsch Langzeitgymnasium',
            'Deutsch Prüfungssimulation'
        ];

        usort($ordered_courses, function ($a, $b) use ($custom_order) {
            $pos_a = array_search($a['title'], $custom_order);
            $pos_b = array_search($b['title'], $custom_order);

            if ($pos_a === false) $pos_a = 999;
            if ($pos_b === false) $pos_b = 999;

            return $pos_a - $pos_b;
        });
    } else {
        // Default sorting (Alphabetical)
        usort($ordered_courses, function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });
    }

    if ($ordered_courses) {
        foreach ($ordered_courses as $args) {
            $course_id  = $args['id'];
            $ccolor     = get_field('course_card_background', $course_id);
            $cicon      = get_field('course_icon', $course_id);
            $course     = get_post($course_id);
            // $title      = $course->post_title;

            $original_title = $course->post_title;

            // Title replacement logic
            if ($group_id == 87918 && $original_title === 'Multicheck Gesundheit und Soziales – Prüfungssimulation') {
                $title = 'Multicheck Pharma und Chemie – Prüfungssimulation';
            } elseif ($group_id == 87914 && $original_title === 'Multicheck Gesundheit und Soziales – Prüfungssimulation') {
                $title = 'Multicheck Gesundheit HF – Prüfungssimulation';
            } else {
                $title = $original_title;
            }

            // Convert HEX to RGBA
            $ccolor_rgba = sprintf(
                'rgba(%d, %d, %d, 0.3)',
                hexdec(substr($ccolor, 1, 2)),
                hexdec(substr($ccolor, 3, 2)),
                hexdec(substr($ccolor, 5, 2))
            );
            // Generate permalink with group_id query parameter
            $course_url = add_query_arg('mc_group_id', $group_id, get_permalink($course));
        ?>
            <div class="course_list_items">
                <span class="border-line" style="background-color: <?php echo $ccolor; ?>;"></span>
                <div class="course_title" style="background-color:<?php echo $ccolor_rgba; ?>;">
                    <h4><?php echo $title; ?></h4>
                    <img src="<?php echo $cicon; ?>" />
                </div>
                <a href="<?php echo $course_url; ?>" class="course_link" style="background-color: <?php echo $ccolor; ?>; color: #FFF;"><?php _e('Mehr anzeigen', 'bricks-child'); ?> <span><i class="fas fa-arrow-right-long"></i></span></a>
            </div>
    <?php }
    }
    die();
}


// Remove default WooCommerce checkout error notices
// add_filter('woocommerce_add_error', '__return_empty_string');
// add_filter('woocommerce_checkout_fields', 'remove_checkout_errors');

// function remove_checkout_errors($fields) {
//     wc_clear_notices(); // Clear all WooCommerce notices
//     return $fields;
// }

// // Redirect after login
// add_filter('login_redirect', 'custom_login_redirect', 10, 3);
// function custom_login_redirect($redirect_to, $request, $user) {
//     // Check if user is logged in
//     if (isset($user->roles) && is_array($user->roles)) {
//         return 'https://study-peak.nyusoft.in/de/';
//     }
//     return $redirect_to;
// }

// // Redirect after registration
// add_action('user_register', 'custom_registration_redirect');
// function custom_registration_redirect($user_id) {
//     wp_safe_redirect('https://study-peak.nyusoft.in/de/');
//     exit;
// }

// Redirect after logout
add_action('wp_logout', 'custom_logout_redirect');
function custom_logout_redirect()
{
    wp_safe_redirect('https://studypeak.ch');
    exit;
}



function auto_enroll_in_course()
{
    if (is_singular('sfwd-courses')) { // Check if it's a course page
        $course_id = get_the_ID();
        $user_id = get_current_user_id();

        if (current_user_can('administrator')) {
            // Admin can see the page without restrictions, no need to auto-enroll.
            return;
        }

        // Check if the user is already enrolled in the course
        if (!sfwd_lms_has_access($course_id, $user_id)) {
            // Get the user's groups dynamically
            $user_groups = learndash_get_users_group_ids($user_id);

            // Get groups associated with this course
            $course_groups = learndash_get_course_groups($course_id);

            // Check if the user belongs to any of the groups linked to this course
            if (!empty(array_intersect($user_groups, $course_groups))) {
                // Enroll the user in the course automatically
                ld_update_course_access($user_id, $course_id);
            }
        }
    }
}
add_action('template_redirect', 'auto_enroll_in_course');



add_action('woocommerce_order_status_completed', function ($order_id) {
    $order = wc_get_order($order_id);
    $completion_date = $order->get_date_completed();

    if (!$completion_date) {
        error_log('Order not completed yet');
        return;
    }

    $user_id = $order->get_user_id();

    foreach ($order->get_items() as $item_id => $item) {
        $product     = $item->get_product();
        $product_id   = $product->get_parent_id();
        $item_name   = $item->get_name();
        $quantity    = $item->get_quantity();
        $variation_id = $item->get_variation_id();
        $group_ids    = get_post_meta($variation_id, '_related_group', true);

        // Extract access days from product name like "Product - 30 - Something"
        $accessdays = 0;
        $parts = explode(' - ', $item_name);
        if (isset($parts[1]) && is_numeric($parts[1])) {
            $accessdays = intval($parts[1]) * $quantity;
        }

        // Try to get existing expiry meta from previous orders
        $existing_expiry = null;

        if ($user_id) {
            $customer_orders = wc_get_orders([
                'customer_id' => $user_id,
                'status' => 'completed',
                'limit' => -1,
                'orderby' => 'date_completed',
                'order' => 'DESC',
            ]);

            foreach ($customer_orders as $prev_order) {
                if ($prev_order->get_id() == $order_id) {
                    continue; // Skip current order
                }

                foreach ($prev_order->get_items() as $prev_item) {
                    if ($prev_item->get_variation_id() == $variation_id || $prev_item->get_product_id() == $item->get_product_id()) {
                        $meta = $prev_item->get_meta('group_expiry_date');
                        if ($meta) {
                            $meta_date = DateTime::createFromFormat('d-m-Y', $meta);
                            if ($meta_date && $meta_date >= new DateTime()) {
                                $existing_expiry = $meta_date;
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        // Calculate new expiry date
        $start_date = $existing_expiry ?: clone $completion_date;

        $target_ids = [87908, 87910, 81616, 81614, 32332, 86815];
        $bmsg_ids   = [87895, 87894];
        $hmsg_ids   = [87902, 87903];
        $fmsg_ids   = [87898, 87899];

        // if (!empty($group_ids) && count(array_intersect($group_ids, $target_ids)) > 0 && has_term('180-de', 'product_cat', $product_id)) {
        //     $end_date = DateTime::createFromFormat('d.m.Y', '02.03.2026');
        // } else if (!empty($group_ids) && count(array_intersect($group_ids, $bmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id)) {
        //     $end_date = DateTime::createFromFormat('d.m.Y', '04.03.2026');
        // } else if (!empty($group_ids) && count(array_intersect($group_ids, $hmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id)) {
        //     $end_date = DateTime::createFromFormat('d.m.Y', '02.03.2026');
        // } else if (!empty($group_ids) && count(array_intersect($group_ids, $fmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id)) {
        //     $end_date = DateTime::createFromFormat('d.m.Y', '04.03.2026');
        // } else {
        $end_date   = clone $start_date;
        $end_date->modify("+{$accessdays} days");
        // }

        $final_expiry = clone $end_date;
        $final_expiry->modify('+1 day'); // expiry is 1 day after access end

        // Save to current order item
        $item->add_meta_data('group_expiry_date', $final_expiry->format('d-m-Y'), true);
        $item->save();

        error_log("Updated expiry date: " . $final_expiry->format('d-m-Y'));
    }
});



// add_action('woocommerce_order_status_completed', function ($order_id) {
//     // Get the order object
//     $order = wc_get_order($order_id);

//     // Get the order completion (date completed) date
//     $completion_date = $order->get_date_completed();

//     // Check if the completion date exists
//     if ($completion_date) {
//         // Log completion date for debugging
//         error_log('Completion date: ' . $completion_date->date('Y-m-d H:i:s'));

//         foreach ($order->get_items() as $item_id => $item) {
//             $product = $item->get_product();
//             $attributes = $product->get_attributes();

//             // Check if 'pa_access_days' exists and is valid
//             if (isset($attributes['pa_access_days'])) {
//                 $access_days_raw = $attributes['pa_access_days']->get_options(); // Get attribute value as an array
//                 $access_days = isset($access_days_raw[0]) ? intval($access_days_raw[0]) : 0; // Convert to integer

//                 // Multiply access days by quantity
//                 $quantity = $item->get_quantity();
//                 $total_access_days = $access_days * $quantity;

//                 // Calculate expiration date
//                 $date = new DateTime($completion_date->date('Y-m-d H:i:s')); // Clone DateTime
//                 $date->modify('+' . $total_access_days . ' days');
//                 $expire_date = $date->format('d-m-Y');

//                 // Log expiration date for debugging
//                 error_log('Expired date: ' . $expire_date);

//                 // Add expiration date to item meta
//                 $item->add_meta_data('group_expiry_date', $expire_date, true);
//                 $item->save();
//             } else {
//                 error_log('Product attribute "pa_access_days" not found for product ID: ' . $product->get_id());
//             }
//         }
//     } else {
//         error_log('--- Order is not yet completed ----');
//     }
// });



// function check_email_exists() {
//     if (isset($_POST['user_login']) && is_email($_POST['user_login'])) {
//         $email = sanitize_email($_POST['user_login']);

//         // Check if the email exists in the WordPress database
//         if (email_exists($email)) {
//             wp_send_json_success(); // Email exists, return success
//         } else {
//             wp_send_json_error(array('message' => 'Email not found')); // Email does not exist
//         }
//     } else {
//         wp_send_json_error(array('message' => 'Invalid email')); // Invalid email request
//     }
// }
// add_action('wp_ajax_check_email_exists', 'check_email_exists');
// add_action('wp_ajax_nopriv_check_email_exists', 'check_email_exists');


add_action('wp_login', 'mo_custom_registration_redirect', 10, 2);
add_action('user_register', 'mo_custom_registration_redirect_on_register', 10, 1);

function mo_custom_registration_redirect($user_login, $user)
{
    // Check if this is a social login
    if (isset($_GET['mo_social_login']) && $_GET['mo_social_login'] === 'true') {
        // Redirect to the custom URL
        wp_redirect(home_url('/mein-profil')); // Replace with your custom URL
        exit;
    }
}

function mo_custom_registration_redirect_on_register($user_id)
{
    // Redirect newly registered users
    if (isset($_GET['mo_social_login']) && $_GET['mo_social_login'] === 'true') {
        wp_redirect(home_url('/mein-profil')); // Replace with your custom URL
        exit;
    }
}



add_filter('woocommerce_coupon_error', 'custom_coupon_error_message', 10, 3);

function custom_coupon_error_message($err, $err_code, $coupon)
{
    switch ($err_code) {
        case 105: // Invalid coupon code
            $err = __('Ihr Code ist leider ungültig.', 'woocommerce');
            break;

        // case 103: // Coupon already applied
        //     $err = __('This coupon has already been applied to your cart.', 'woocommerce');
        //     break;

        // case 104: // Coupon usage limit reached
        //     $err = __('The usage limit for this coupon has been reached.', 'woocommerce');
        //     break;

        default:
            // Leave default WooCommerce error for other cases
            $err = $err;
            break;
    }
    return $err;
}


// add_filter('woocommerce_coupon_error', 'custom_invalid_coupon_message', 10, 3);

// function custom_invalid_coupon_message($err, $err_code, $coupon) {
//     // Check for the specific error code for invalid coupons
//     if ($err_code === WC_Coupon::E_WC_COUPON_INVALID) {
//         return __('Ihr Code ist leider ungültig.', 'woocommerce'); // Replace default message
//     }

//     // Handle other error codes if necessary
//     return $err;
// }

// add_filter('woocommerce_coupon_error', 'custom_invalid_coupon_message', 10, 2);

// function custom_invalid_coupon_message($err, $coupon) {
//     // Check if the coupon is invalid
//     if ($coupon->get_code() && !$coupon->is_valid()) {
//         // Return the custom error message
//         $err = __('Ihr Code ist leider ungültig.', 'woocommerce');
//     }

//     return $err;
// }

add_filter('woocommerce_billing_fields', 'custom_billing_address_label');

function custom_billing_address_label($fields)
{
    if (isset($fields['billing_address_1'])) {
        $fields['billing_address_1']['label'] = 'Strasse';
    }
    return $fields;
}


add_action('template_redirect', 'redirect_empty_checkout_to_home');

function redirect_empty_checkout_to_home()
{
    // Check if on the checkout page and if the cart is empty
    if (is_checkout() && WC()->cart->is_empty()) {
        wp_safe_redirect(home_url()); // Redirect to the homepage
        exit;
    }
}



// Application form Function
add_action('wp_ajax_multistep_form_func', 'multistep_form_func');
add_action('wp_ajax_nopriv_multistep_form_func', 'multistep_form_func');

function multistep_form_func()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'cu_appointment';
    $target_dir = get_home_path() . "wp-content/uploads/appointment_doc/";

    $file_names = array();
    $response = array('success' => false);

    if (isset($_FILES['fileInput']) && !empty($_FILES['fileInput'])) {
        $total_files = count($_FILES['fileInput']['name']);
        for ($i = 0; $i < $total_files; $i++) {
            $original_name = $_FILES['fileInput']['name'][$i];
            $timestamped_name = date('d-m-yhis') . '_' . $original_name;
            $target_file = $target_dir . basename($timestamped_name);

            if (move_uploaded_file($_FILES['fileInput']['tmp_name'][$i], $target_file)) {
                $file_names[] = $timestamped_name;
            } else {
                $response['message'] = 'File ' . $original_name . ' could not be uploaded. Please try again.';
                echo json_encode($response);
                wp_die();
            }
        }
    }
    $other_achivement = json_encode($file_names);

    if (isset($_FILES['resume']) && !empty($_FILES['resume'])) {
        $resume = date('d-m-yhis') . '_' . $_FILES["resume"]["name"];
        $target_file = $target_dir . basename($resume);
        if (!move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $response['message'] = 'Your resume could not be uploaded. Please try again.';
            $response['message'] = false;
            echo json_encode($response);
            die();
        }
    }

    if (isset($_FILES['leaving_certificate']) && !empty($_FILES['leaving_certificate'])) {
        $leaving_certificate = date('d-m-yhis') . '_' . $_FILES["leaving_certificate"]["name"];
        $target_file = $target_dir . basename($leaving_certificate);
        if (!move_uploaded_file($_FILES["leaving_certificate"]["tmp_name"], $target_file)) {
            $response['message'] = 'Your leaving certificate could not be uploaded. Please try again.';
            $response['success'] = false;
            echo json_encode($response);
            die();
        }
    }

    if (isset($_FILES['training_certificate']) && !empty($_FILES['training_certificate'])) {
        $training_certificate = date('d-m-yhis') . '_' . $_FILES["training_certificate"]["name"];
        $target_file = $target_dir . basename($training_certificate);
        if (!move_uploaded_file($_FILES["training_certificate"]["tmp_name"], $target_file)) {
            $response['message'] = 'Your training certificate could not be uploaded. Please try again.';
            $response['success'] = false;
            echo json_encode($response);
            die();
        }
    }

    if (isset($_FILES['references_doc']) && !empty($_FILES['references_doc'])) {
        $references_doc = date('d-m-yhis') . '_' . $_FILES["references_doc"]["name"];
        $target_file = $target_dir . basename($references_doc);
        if (!move_uploaded_file($_FILES["references_doc"]["tmp_name"], $target_file)) {
            $response['message'] = 'Your training certificate could not be uploaded. Please try again.';
            $response['success'] = false;
            echo json_encode($response);
            die();
        }
    }


    $data = array(
        'name' => sanitize_text_field($_POST['first_name']) . ' ' . sanitize_text_field($_POST['last_name']),
        'gender' => sanitize_text_field($_POST['gender']),
        'birth_date' => sanitize_text_field($_POST['birth_date'] . '/' . $_POST['birth_month'] . '/' . $_POST['birth_year']),
        'mobile_number' => sanitize_text_field($_POST['mobile_number']),
        'have_smartphone' => sanitize_text_field($_POST['smartphone']),
        'email' => sanitize_email($_POST['email']),
        'how_find_us' => sanitize_text_field($_POST['find_us']),
        'data_protect' => sanitize_text_field($_POST['data_protect']),
        'last_training' => sanitize_text_field($_POST['last_training']),
        'tutor_work' => sanitize_text_field($_POST['tutor_work']),
        'tutor_experience' => sanitize_text_field($_POST['tutor_experience']),
        'tutor_skills' => sanitize_text_field($_POST['tutor_skills']),
        'before_first_lesson' => sanitize_text_field($_POST['before_first_lesson']),
        'moral_view' => sanitize_text_field($_POST['moral_view']),
        'approx_hours' => sanitize_text_field($_POST['approx_hours']),
        'available_date' => sanitize_text_field($_POST['available_date'] . '/' . $_POST['available_month'] . '/' . $_POST['available_year']),
        'how_long_teach' => sanitize_text_field($_POST['how_long_teach']),
        'explain_why' => sanitize_textarea_field($_POST['explain_why']),
        'planning_to_stay' => sanitize_text_field($_POST['planning_to_stay']),
        'hour_per_week' => sanitize_text_field($_POST['hour_per_week']),
        'assignments' => sanitize_textarea_field($_POST['assignments']),
        'educational_paths' => sanitize_textarea_field($_POST['educational_paths']),
        'german_skills' => sanitize_text_field($_POST['german_skills']),
        'gernam_primary' => sanitize_text_field($_POST['gernam_primary']),
        'english_primary' => sanitize_text_field($_POST['english_primary']),
        'french_primary' => sanitize_text_field($_POST['french_primary']),
        'maths_primary' => sanitize_text_field($_POST['maths_primary']),
        'gernam_secondary' => sanitize_text_field($_POST['gernam_secondary']),
        'english_secondary' => sanitize_text_field($_POST['english_secondary']),
        'french_secondary' => sanitize_text_field($_POST['french_secondary']),
        'maths_secondary' => sanitize_text_field($_POST['maths_secondary']),
        'latin_secondary' => sanitize_text_field($_POST['latin_secondary']),
        'organic_secondary' => sanitize_text_field($_POST['organic_secondary']),
        'chemistry_secondary' => sanitize_text_field($_POST['chemistry_secondary']),
        'physics_secondary' => sanitize_text_field($_POST['physics_secondary']),
        'gernam_matura' => sanitize_text_field($_POST['gernam_matura']),
        'english_matura' => sanitize_text_field($_POST['english_matura']),
        'french_matura' => sanitize_text_field($_POST['french_matura']),
        'maths_matura' => sanitize_text_field($_POST['maths_matura']),
        'latin_matura' => sanitize_text_field($_POST['latin_matura']),
        'organic_matura' => sanitize_text_field($_POST['organic_matura']),
        'chemistry_matura' => sanitize_text_field($_POST['chemistry_matura']),
        'story_matura' => sanitize_text_field($_POST['story_matura']),
        'geography_matura' => sanitize_text_field($_POST['geography_matura']),
        'accounting_matura' => sanitize_text_field($_POST['accounting_matura']),
        'physics_matura' => sanitize_text_field($_POST['physics_matura']),
        'native_language' => sanitize_text_field($_POST['native_language']),
        'resume' => $resume,
        'leaving_certificate' => $leaving_certificate,
        'training_certificate' => $training_certificate,
        'references_doc' => $references_doc,
        'other_achievement' => $other_achivement
    );

    // Insert data into the database
    $sql = $wpdb->insert($table_name, $data);
    $lastid = $wpdb->insert_id;

    if ($sql) {
        $response['message'] = 'Data inserted successfully.';
        $response['success'] = true;
    } else {
        $response['message'] = 'Data insertion failed.';
    }

    $subject = 'Studypeak :: Application Form Details';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    // $admin_email = 'parth.jogi@nyusoft.com';
    $admin_email = get_option('admin_email');
    $user_email = sanitize_email($_POST['email']);

    $message = '<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,800&display=swap"
    rel="stylesheet">
<table cellpadding="0" cellspacing="0"
    style="width:600px;margin:auto;font-family:Arial,Helvetica,sans-serif;background:#fbfbfb;font-family: "Poppins", sans-serif;">
    <thead>
        <tr>
            <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                <a href="' . site_url() . '">
                    <img src="' . site_url() . '/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="120">
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
            <td style="background:#ffffff;padding:20px;text-align: center; font-size: 20px; line-height: 20px; font-weight: 700;">
                <p style="text-align: center; font-size: 20px; line-height: 20px; font-weight: 700; margin: 0;">Hallo Admin</p>
            </td>
            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
        </tr>
        <tr>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                <table cellpadding="0" cellspacing="0" style="border: 1px solid #DDDDDD; width: 100%;">
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Name</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['name']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Email Address</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['email']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Phone Number</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['mobile_number']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Gender</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['gender']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Birth Date</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['birth_date']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Smartphone (WhatsApp)</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['have_smartphone']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">How did you find out about us?</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['how_find_us']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Data Protection</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['data_protect']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Last Training Completed</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['last_training']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Why Work as a Tutor?</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['tutor_work']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Tutor Experience</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['tutor_experience']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Tutor Skills</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['tutor_skills']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Tip for Aspiring Tutors</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['before_first_lesson']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Moral View on Private Lessons</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['moral_view']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Approximate Hours Taught</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['approx_hours']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Available Date</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['available_date']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Expected Teaching Duration</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['how_long_teach']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Reason for Teaching Duration</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['explain_why']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Planning Stay Abroad/Other Job</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['planning_to_stay']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Hours per Week to Teach</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['hour_per_week']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Feasible Course Assignments</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['assignments']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Familiar Educational Paths</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['educational_paths']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">German Skills</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['german_skills']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Native Language</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['native_language']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; width: 40%;" colspan="2">
                            <p style="font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Subjects at Primary Level (1st - 6th Grade)</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">German</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['gernam_primary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">English</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['english_primary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">French</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['french_primary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Maths</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['maths_primary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; width: 40%;" colspan="2">
                            <p style="font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Subjects at Secondary Level I (7th - 9th Grade)</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">German</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['gernam_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">English</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['english_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">French</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['french_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Maths</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['maths_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Latin</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['latin_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Organic</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['organic_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Chemistry</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['chemistry_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Physics</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['physics_secondary']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; width: 40%;" colspan="2">
                            <p style="font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Subjects at Matura Level (10th - 12th Grade, Apprenticeship/BMS or Higher)</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">German</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['gernam_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">English</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['english_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">French</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['french_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Maths</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['maths_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Latin</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['latin_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Organic</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['organic_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Chemistry</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['chemistry_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">History</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['story_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Geography</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['geography_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Accounting</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['accounting_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Physics</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">' . esc_html($data['physics_matura']) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; width: 40%;" colspan="2">
                            <p style="font-size: 16px; line-height: 18px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Documents</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">CV</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">
                                ' . (!empty($data['resume']) ? '<a href="' . esc_url(site_url() . '/wp-content/uploads/appointment_doc/' . $data['resume']) . '" target="_blank" style="color:#18C867">Download</a>' : 'Not provided') . '
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">High School/Vocational School Leaving Certificate</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">
                                ' . (!empty($data['leaving_certificate']) ? '<a href="' . esc_url(site_url() . '/wp-content/uploads/appointment_doc/' . $data['leaving_certificate']) . '" target="_blank" style="color:#18C867">Download</a>' : 'Not provided') . '
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Most Recent Training Certificates</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">
                                ' . (!empty($data['training_certificate']) ? '<a href="' . esc_url(site_url() . '/wp-content/uploads/appointment_doc/' . $data['training_certificate']) . '" target="_blank" style="color:#18C867">Download</a>' : 'Not provided') . '
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Most Recent References</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">
                                ' . (!empty($data['references_doc']) ? '<a href="' . esc_url(site_url() . '/wp-content/uploads/appointment_doc/' . $data['references_doc']) . '" target="_blank" style="color:#18C867">Download</a>' : 'Not provided') . '
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; width: 40%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 700; text-align: center; margin: 0; padding: 0;">Other Achievements</p>
                        </td>
                        <td style="padding: 10px; margin: 0; border: 1px solid #DDDDDD; font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; width: 60%;">
                            <p style="font-size: 14px; line-height: 16px; font-weight: 400; text-align: center; margin: 0; padding: 0;">
                                ' . (!empty($data['other_achievement']) ? implode('<br>', array_map(function ($achievement) {
        return '<a href="' . esc_url(site_url() . '/wp-content/uploads/appointment_doc/' . esc_attr($achievement)) . '" target="_blank" style="color:#18C867">Download</a>';
    }, json_decode($data['other_achievement']))) : 'Not provided') . '
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="padding: 20px; text-align: center;">Herzliche Grüsse, <a href="' . site_url() . '" style="color:#18C867">Das studypeak-Team</a></td>
        </tr>
    </tfoot>
</table>';
    wp_mail($admin_email, $subject, $message, $headers);

    $message1 = '<html><head><link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,800&amp;display=swap" rel="stylesheet">
</head><body><table cellpadding="0" cellspacing="0" style="width:600px;margin:auto;font-family:Arial,Helvetica,sans-serif;background:#edf1f7;font-family: "Poppins", sans-serif;">
    <thead>
        <tr>
            <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                <a href="' . site_url() . '">
                    <img src="' . site_url() . '/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="120">
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
            <td style="background:#ffffff;padding:20px;text-align: center; font-size: 20px; line-height: 20px; font-weight: 700;">
                
            </td>
            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
        </tr>
        <tr>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
            <td style="background:#ffffff;padding:0 20px 15px; font-size: 14px;line-height: 16px;">
                <p style="font-size: 14px;line-height: 16px;font-weight: 400; margin: 0;padding: 0;">Guten Tag <b>' . sanitize_text_field($_POST['first_name']) . ' ' . sanitize_text_field($_POST['last_name']) . '</b>
                    <br><br>
                    Danke für Ihre Bewerbung!
                    <br><br>
                    Wir haben Ihre Bewerbungsanfrage erhalten. Wir werden sie prüfen und uns so schnell wie möglich bei Ihnen melden.
                    <br><br>
                    Wenn Sie weitere Informationen haben, die uns helfen, Ihnen weiterzuhelfen, können Sie gerne auf diese E-Mail antworten.
                    <br><br>
                    Herzliche Grüsse
                    <br><br>
                    Das studypeak-Team
                    </p>
            </td>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
        </tr>
        <tr>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
            <td style="background:#ffffff;padding:0 20px 15px;text-align: center; font-size: 14px; line-height: 16px;">
                
            </td>
            <td style="width:10%;background:#fbfbfb;padding:0 20px 15px"></td>
        </tr>
    </tbody>
</table></body></html>';
    wp_mail($user_email, $subject, $message1, $headers);

    echo json_encode($response);

    wp_die();
}


add_action('admin_menu', 'appointmentRequest');

function appointmentRequest()
{
    $menu = add_menu_page('Appointment Details', 'Appointment Details', 'manage_options', 'appointment_details', 'admin_appointment_data', 'dashicons-admin-generic', 4);
    add_submenu_page(
        null, // No parent slug, makes this a hidden page
        'Details Page',
        'Details Page',
        'manage_options',
        'my-appointment-details',
        'my_appointment_details_page_html'
    );
}

function admin_appointment_data()
{
    echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">';
    echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">';
    echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>';
    echo '<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>';

    echo '<div class="custom_page_details" style="margin-top:20px">';

    ?>
    <div class="col-lg-12" style="border:2px solid #E0E0E0;background-color:#fff;padding: 0 2% 3%;width: 99%;">
        <h1>Appointment Details</h1>
        <?php
        if (isset($_GET['msg'])) {
            echo '<div class="alert alert-success"> Delete successfully.</div>';
        } ?>
        <div class="tab-content">
            <div id="home" class="tab-pane fade in active">
                <table id="inquiry_details" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Read More</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        global $wpdb;
                        $inquiry_data = $wpdb->get_results("SELECT * FROM `wp_1209298_cu_appointment` ORDER BY ID DESC");
                        //echo $wpdb->last_query;
                        $count = 1;
                        foreach ($inquiry_data as $res) {
                            //print_r($res);

                            $fullname = $res->name;
                            $email_address = $res->email;
                            $phone_number = $res->mobile_number;
                            $id = $res->ID;
                            $date = $res->Date;

                            echo '<tr>';
                            echo '<td>' . $count . '</td>';
                            echo '<td>' . $fullname . '</td>';
                            echo '<td>' . $email_address . '</td>';
                            echo '<td>' . $phone_number . '</td>';
                            echo '<td>' . $date . '</td>';

                        ?>
                            <td><a href="<?php echo admin_url('admin.php?page=my-appointment-details&id=' . $id); ?>"
                                    class="see-more">See More</a></td>
                            <td><a class="btn btn-danger"
                                    href="<?php echo admin_url() . '?page=appointment_details&delete_id=' . $id . ''; ?>"
                                    onclick="return confirm('Are you sure want to delete?')">Delete</a></td>
                        <?php
                            $count++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
        $id = (int) $_GET['delete_id'];
        global $wpdb;
        $delte = $wpdb->delete('wp_1209298_cu_appointment', array('ID' => $id));
        $_SESSION['del_msg'] = "true";
        $red_url = admin_url() . '?page=appointment_details&msg=true'; ?>
        <script type="text/javascript">
            window.location.href = "<?php echo $red_url; ?>";
        </script>
    <?php
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('#inquiry_details').DataTable();
        });
    </script>
<?php
    echo "</div>";
}

// Detail page

function my_appointment_details_page_html()
{
    if (!isset($_GET['id'])) {
        echo '<div class="wrap"><h1>Invalid ID</h1></div>';
        return;
    }

    global $wpdb;
    $id = intval($_GET['id']);
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM `wp_1209298_cu_appointment` WHERE ID = %d", $id), ARRAY_A);

    if (!$result) {
        echo '<div class="wrap"><h1>No data found</h1></div>';
        return;
    }
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Details</h1>
        <p><strong style="font-size:15px">Name: </strong><span
                style="font-size:14px"><?php echo esc_html($result['name']); ?></span></p>
        <p><strong style="font-size:15px">Email: </strong><span
                style="font-size:14px"><?php echo esc_html($result['email']); ?></span></p>
        <p><strong style="font-size:15px">Gender </strong><span
                style="font-size:14px"><?php echo esc_html($result['gender']); ?></span></p>
        <p><strong style="font-size:15px">Birth Date: </strong><span
                style="font-size:14px"><?php echo esc_html($result['birth_date']); ?></span></p>
        <p><strong style="font-size:15px">Mobile Number: </strong><span
                style="font-size:14px"><?php echo esc_html($result['mobile_number']); ?></span></p>
        <p><strong style="font-size:15px">I have a smartphone and can be reached via WhatsApp: </strong><span
                style="font-size:14px"><?php echo esc_html($result['have_smartphone']); ?></span></p>
        <p><strong style="font-size:15px">How did you find out about us? </strong><span
                style="font-size:14px"><?php echo esc_html($result['how_find_us']); ?></span></p>
        <p><strong style="font-size:15px">Data protection: </strong><span
                style="font-size:14px"><?php echo esc_html($result['data_protect']); ?></span></p><br>

        <p><strong style="font-size:15px">What is the last training you completed or what title do you hold? If you are
                still in training, please let us know which subjects you are taking and when you expect to graduate?
            </strong><span style="font-size:14px"><?php echo esc_html($result['last_training']); ?></span></p>

        <p><strong style="font-size:15px">Why do you want to work as a tutor? </strong><span
                style="font-size:14px"><?php echo esc_html($result['tutor_work']); ?></span></p>

        <p><strong style="font-size:15px">What experience do you have as a tutor? </strong><span
                style="font-size:14px"><?php echo esc_html($result['tutor_experience']); ?></span></p>

        <p><strong style="font-size:15px">What skills do you think a tutor should have? </strong><span
                style="font-size:14px"><?php echo esc_html($result['tutor_skills']); ?></span></p>

        <p><strong style="font-size:15px">What would be the most important tip you would give to aspiring tutors before
                their first lessons? </strong><span
                style="font-size:14px"><?php echo esc_html($result['before_first_lesson']); ?></span></p>

        <p><strong style="font-size:15px">What is your moral view on the fact that wealthier families can "buy" a high
                school diploma for their child through private lessons, while comparable students without the financial
                means cannot? </strong><span style="font-size:14px"><?php echo esc_html($result['moral_view']); ?></span>
        </p>

        <p><strong style="font-size:15px">Approximately how many hours have you taught so far? </strong><span
                style="font-size:14px"><?php echo esc_html($result['approx_hours']); ?></span></p><br>

        <p><strong style="font-size:15px">What date are you available? </strong><span
                style="font-size:14px"><?php echo esc_html($result['available_date']); ?></span></p>

        <p><strong style="font-size:15px">How long do you expect to teach for? </strong><span
                style="font-size:14px"><?php echo esc_html($result['how_long_teach']); ?></span></p>

        <p><strong style="font-size:15px">If you chose option 1 or 2 in the last question, please briefly explain why:
            </strong><span style="font-size:14px"><?php echo esc_html($result['explain_why']); ?></span></p>

        <p><strong style="font-size:15px">Are you planning a stay abroad or another job? If yes, from when and for how long?
            </strong><span style="font-size:14px"><?php echo esc_html($result['planning_to_stay']); ?></span></p>

        <p><strong style="font-size:15px">How many hours per week do you want to teach? </strong><span
                style="font-size:14px"><?php echo esc_html($result['hour_per_week']); ?></span></p>

        <p><strong style="font-size:15px">Which of the following course assignments would be basically/mostly feasible for
                you? </strong><span style="font-size:14px"><?php echo esc_html($result['assignments']); ?></span></p>

        <p><strong style="font-size:15px">Which educational paths/stations are you familiar with? </strong><span
                style="font-size:14px"><?php echo esc_html($result['educational_paths']); ?></span></p>

        <p><strong style="font-size:15px">How good are your German skills? </strong><span
                style="font-size:14px"><?php echo esc_html($result['german_skills']); ?></span></p>

        <h3>Which subjects can you teach at primary level (1st - 6th grade)?</h3>

        <p><strong style="font-size:15px">German: </strong><span
                style="font-size:14px"><?php echo esc_html($result['gernam_primary']); ?></span></p>
        <p><strong style="font-size:15px">English: </strong><span
                style="font-size:14px"><?php echo esc_html($result['english_primary']); ?></span></p>
        <p><strong style="font-size:15px">French: </strong><span
                style="font-size:14px"><?php echo esc_html($result['french_primary']); ?></span></p>
        <p><strong style="font-size:15px">Maths: </strong><span
                style="font-size:14px"><?php echo esc_html($result['maths_primary']); ?></span></p><br>

        <h3>Which subjects can you teach at secondary level I (7th - 9th grade)?</h3>
        <p><strong style="font-size:15px">German: </strong><span
                style="font-size:14px"><?php echo esc_html($result['gernam_secondary']); ?></span></p>
        <p><strong style="font-size:15px">English: </strong><span
                style="font-size:14px"><?php echo esc_html($result['english_secondary']); ?></span></p>
        <p><strong style="font-size:15px">French: </strong><span
                style="font-size:14px"><?php echo esc_html($result['french_secondary']); ?></span></p>
        <p><strong style="font-size:15px">Maths: </strong><span
                style="font-size:14px"><?php echo esc_html($result['maths_secondary']); ?></span></p>
        <p><strong style="font-size:15px">Latin: </strong><span
                style="font-size:14px"><?php echo esc_html($result['latin_secondary']); ?></span></p>
        <p><strong style="font-size:15px">Organic: </strong><span
                style="font-size:14px"><?php echo esc_html($result['organic_secondary']); ?></span></p>
        <p><strong style="font-size:15px">Chemistry: </strong><span
                style="font-size:14px"><?php echo esc_html($result['chemistry_secondary']); ?></span></p>
        <p><strong style="font-size:15px">Physics: </strong><span
                style="font-size:14px"><?php echo esc_html($result['physics_secondary']); ?></span></p><br>

        <h3>Which subjects can you teach at the Matura level (10th - 12th school year), or apprenticeship/BMS or higher?
        </h3>
        <p><strong style="font-size:15px">German: </strong><span
                style="font-size:14px"><?php echo esc_html($result['gernam_matura']); ?></span></p>
        <p><strong style="font-size:15px">English: </strong><span
                style="font-size:14px"><?php echo esc_html($result['english_matura']); ?></span></p>
        <p><strong style="font-size:15px">French: </strong><span
                style="font-size:14px"><?php echo esc_html($result['french_matura']); ?></span></p>
        <p><strong style="font-size:15px">Maths: </strong><span
                style="font-size:14px"><?php echo esc_html($result['maths_matura']); ?></span></p>
        <p><strong style="font-size:15px">Latin: </strong><span
                style="font-size:14px"><?php echo esc_html($result['latin_matura']); ?></span></p>
        <p><strong style="font-size:15px">Organic: </strong><span
                style="font-size:14px"><?php echo esc_html($result['organic_matura']); ?></span></p>
        <p><strong style="font-size:15px">Chemistry: </strong><span
                style="font-size:14px"><?php echo esc_html($result['chemistry_matura']); ?></span></p>
        <p><strong style="font-size:15px">Story: </strong><span
                style="font-size:14px"><?php echo esc_html($result['story_matura']); ?></span></p>
        <p><strong style="font-size:15px">Geography: </strong><span
                style="font-size:14px"><?php echo esc_html($result['geography_matura']); ?></span></p>
        <p><strong style="font-size:15px">Accounting: </strong><span
                style="font-size:14px"><?php echo esc_html($result['accounting_matura']); ?></span></p>
        <p><strong style="font-size:15px">Physics: </strong><span
                style="font-size:14px"><?php echo esc_html($result['physics_matura']); ?></span></p><br>

        <p><strong style="font-size:15px">What is your native language? Or do you have several? (Please differentiate
                between Swiss German and German): </strong><span
                style="font-size:14px"><?php echo esc_html($result['native_language']); ?></span></p><br>

        <p><strong style="font-size:15px">CV: </strong><span style="font-size:14px"><a
                    href="<?= site_url() ?>/wp-content/uploads/appointment_doc/<?= $result['resume'] ?>"
                    target="_blank">Download</a></span></p>
        <p><strong style="font-size:15px">High school/vocational school leaving certificate: </strong><span
                style="font-size:14px"><a
                    href="<?= site_url() ?>/wp-content/uploads/appointment_doc/<?= $result['leaving_certificate'] ?>"
                    target="_blank">Download</a></span></p>
        <p><strong style="font-size:15px">Most recent training certificates: </strong><span style="font-size:14px"><a
                    href="<?= site_url() ?>/wp-content/uploads/appointment_doc/<?= $result['training_certificate'] ?>"
                    target="_blank">Download</a></span></p>
        <p><strong style="font-size:15px">Most recent references: </strong><span style="font-size:14px"><a
                    href="<?= site_url() ?>/wp-content/uploads/appointment_doc/<?= $result['references_doc'] ?>"
                    target="_blank">Download</a></span></p>

        <!-- Other Achivements -->
        <p><strong style="font-size:15px">Other achievements</strong></p>

        <?php
        $other_achive = $result['other_achievement'];
        $achievements = json_decode($other_achive);
        if (!empty($achievements)) {
            foreach ($achievements as $achievement) {
                echo '<a href="' . site_url() . '/wp-content/uploads/appointment_doc/' . $achievement . '">Download</a><br>';
            }
        }
        ?>


        <!-- Add more fields as needed -->
        <a href="<?php echo admin_url('admin.php?page=appointment_details'); ?>"
            style="background-color: #004AAD;padding: 15px;font-size: 15px;color: #fff;text-decoration: none;font-weight: 600;border-radius: 15px;top: 25px;position: relative;">Back
            to Appointment Data</a>
    </div>
<?php
}


function remove_plugin_access_for_contributors()
{
    if (current_user_can('contributor')) {
        remove_menu_page('ai1wm_export');
        remove_menu_page('duplicator');
        remove_menu_page('migrate-guru');
    }
}
add_action('admin_menu', 'remove_plugin_access_for_contributors');


// Add new column to the admin questions list
function add_quiz_column_to_sfwd_questions($columns)
{
    $columns['quiz_name'] = __('Quiz Name', 'learndash');
    return $columns;
}
add_filter('manage_edit-sfwd-question_columns', 'add_quiz_column_to_sfwd_questions');

// Populate the new column with quiz name
function populate_quiz_column_in_sfwd_questions($column, $post_id)
{
    if ($column === 'quiz_name') {
        $quiz_id = get_post_meta($post_id, 'quiz_id', true);

        if ($quiz_id) {
            $quiz_title = get_the_title($quiz_id);
            echo esc_html($quiz_title);
        } else {
            echo 'No Quiz Assigned';
        }
    }
}
add_action('manage_sfwd-question_posts_custom_column', 'populate_quiz_column_in_sfwd_questions', 10, 2);


/**
 * Go back button and user's timer preference for CURRENT COURSE (default to "with timer") code START =========================================================================
 */

add_action('template_redirect', 'mc_handle_group_id_in_wc_session');

function mc_handle_group_id_in_wc_session()
{
    if (isset($_GET['mc_group_id'])) {
        $mc_group_id = sanitize_text_field($_GET['mc_group_id']);
        WC()->session->set('mc_group_id', $mc_group_id);
    }
}

add_action('learndash-course-content-list-before', 'mc_course_return_group_link');

function mc_course_return_group_link($course_id)
{
    $group_id = get_user_group_for_course(null, $course_id);

    // Get the English permalink
    $group_link = get_permalink($group_id);

    $group_name = get_the_title($group_id);

    if (WC()->session && WC()->session->get('mc_group_id')) {
        $group_name = get_the_title(WC()->session->get('mc_group_id'));
        $group_id = WC()->session->get('mc_group_id');
    }

    $my_courses_page_url = home_url('/meine-kurse');
    $my_courses_page_url = add_query_arg('mc_group_id', $group_id, $my_courses_page_url);

    // Get user's timer preference for THIS COURSE (default to "with timer")
    $timer_preference = get_user_meta(get_current_user_id(), 'mc_timer_preference_' . $course_id, true);
    if (empty($timer_preference)) {
        $timer_preference = 'with_timer';
    }
    
    // Check if course allows user to choose timer preference (default: allow)
    $allow_user_choice = get_post_meta($course_id, '_ld_ctl_allow_user_choice', true);
    if ($allow_user_choice === '') {
        $allow_user_choice = '1'; // Default to allowing user choice
    }

    $p = learndash_course_progress(array(
        'user_id'   => (int) $user_id,
        'course_id' => (int) $course_id,
        'array'     => true,           // return data, not HTML
    ));

    // Defensive defaults
    $ccompleted  = isset($p['completed']) ? (int) $p['completed'] : 0;
    $ctotal      = isset($p['total'])     ? (int) $p['total']     : 0;
    $cpercentage = $ctotal > 0 ? floor(($ccompleted / $ctotal) * 100) : 0;
    // print_r($cpercentage);
?>

    <?php if ($cpercentage == 100 || !is_user_logged_in()) : ?>
        <div class="go_back_to_group <?= esc_html($course_id); ?>">
            <a href="<?= esc_url($my_courses_page_url); ?>">
                <i class="fas fa-arrow-left-long"></i>
                <?php echo esc_html(sprintf(esc_html__('Gehe zurück %s', 'bricks-child'), esc_html($group_name))); ?>
            </a>
            <?php do_action('mc_course_return_group_link_after'); ?>
        </div>
    <?php else: ?>
        <div class="mc-timer-toggle-container">
            <div class="go_back_to_group <?= esc_html($course_id); ?>">
                <a href="<?= esc_url($my_courses_page_url); ?>">
                    <i class="fas fa-arrow-left-long"></i>
                    <?php echo esc_html(sprintf(esc_html__('Gehe zurück %s', 'bricks-child'), esc_html($group_name))); ?>
                </a>
                <?php do_action('mc_course_return_group_link_after'); ?>
            </div>
            <!-- 
            <div class="mc-timer-toggle toggle">
                <input type="radio" id="toggle-1" name="mc_timer_preference" value="with_timer" <?php checked($timer_preference, 'with_timer'); ?>>
                <label class="mc-timer-option toggle-label" for="toggle-1">

                    <span class="mc-timer-label moving_txt">Mit Timer</span>
                </label>
                <input type="radio" id="toggle-2" name="mc_timer_preference" value="without_timer" <?php checked($timer_preference, 'without_timer'); ?>>
                <label for="toggle-2" class="mc-timer-option toggle-label">

                    <span class="mc-timer-label moving_txt">Ohne Timer</span>
                </label>
            </div>
 -->


            <?php if ($allow_user_choice === '1') : ?>
                <div class="mc-timer-toggle toggle">
                    <input type="radio" id="toggle-1" name="mc_timer_preference" value="with_timer" <?php checked($timer_preference, 'with_timer'); ?>>
                    <label class="mc-timer-option toggle-label" for="toggle-1">
                        <span class="mc-timer-label moving_txt">Mit Timer</span>
                    </label>

                    <input type="radio" id="toggle-2" name="mc_timer_preference" value="without_timer" <?php checked($timer_preference, 'without_timer'); ?>>
                    <label for="toggle-2" class="mc-timer-option toggle-label">
                        <span class="mc-timer-label moving_txt">Ohne Timer</span>
                    </label>

                    <div class="toggle-slider"></div>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <style>
        /* .mc-timer-toggle-container {
            margin: 15px 0;
            display: flex;
            justify-content: space-between; 
            align-items: center;
            flex-wrap: wrap; 
        }

        .go_back_to_group a {
            display: flex;
        }

        .mc-timer-toggle {
            display: flex;
            background: #fff;
            border-radius: 8px;
            padding: 2px;
            border: 1px solid #d1d5db;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .mc-timer-option {
            display: flex;
            align-items: center;
            cursor: pointer;
            border-radius: 6px;
            margin: 0 2px;
        }

        .mc-timer-option input[type="radio"] {
            display: none;
        }

        .mc-timer-label {
            font-weight: 500;
            font-size: 16px;
            color: #374151;
            padding: 6px 14px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .mc-timer-option input[type="radio"]:checked + .mc-timer-label {
            background-color: #1F7044; 
            color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .mc-timer-option:hover .mc-timer-label {
            background-color: #CBE5DA;
        } */
    </style>
    <script>
        jQuery(document).ready(function($) {
            var courseId = <?php echo $course_id; ?>;

            // Handle timer preference change
            $('input[name="mc_timer_preference"]').change(function() {
                var preference = $(this).val();

                // Save preference via AJAX
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'mc_save_timer_preference',
                        preference: preference,
                        course_id: courseId,
                        nonce: '<?php echo wp_create_nonce('mc_timer_preference_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Apply the preference to quiz elements
                            applyTimerPreference(preference);
                        }
                    }
                });
            });

            // Apply the initial timer preference
            applyTimerPreference('<?php //echo $timer_preference; 
                                    ?>');

            function applyTimerPreference(preference) {
                if (preference === 'without_timer') {
                    // Hide timer elements in quizzes
                    $('.wpProQuiz_listItem .timer-container').addClass('hide');
                    $('.wpProQuiz_listItem').each(function() {
                        $(this).removeAttr('data-mc_time_limit');
                    });
                } else {
                    // Show timer elements in quizzes
                    $('.wpProQuiz_listItem .timer-container').removeClass('hide');
                    // The data attributes will be restored when page reloads
                }
            }
        });
    </script>
    <?php
}

// Save timer preference via AJAX
add_action('wp_ajax_mc_save_timer_preference', 'mc_save_timer_preference');
add_action('wp_ajax_nopriv_mc_save_timer_preference', 'mc_save_timer_preference');

function mc_save_timer_preference()
{
    if (!wp_verify_nonce($_POST['nonce'], 'mc_timer_preference_nonce')) {
        wp_die('Security check failed');
    }

    if (isset($_POST['preference']) && isset($_POST['course_id'])) {
        $user_id = get_current_user_id();
        $preference = sanitize_text_field($_POST['preference']);
        $course_id = intval($_POST['course_id']);

        if (in_array($preference, array('with_timer', 'without_timer'))) {
            // Save preference for THIS SPECIFIC COURSE
            update_user_meta($user_id, 'mc_timer_preference_' . $course_id, $preference);
            wp_send_json_success();
        }
    }

    wp_send_json_error();
}

/**
 * Optional global list (keeps previous behaviour if you used it).
 * You can keep it empty if you only want per-user control.
 */
function ddq_global_disabled_quiz_ids()
{
    return array(
        'quiz_post_ids' => array(), // e.g. 114924  (keep empty if not needed)
        'quiz_pro_ids'  => array()  // e.g. 3050
    );
}

/**
 * Return true if the quiz should be timer-disabled for the current user.
 * Checks:
 *  - global disabled list OR
 *  - current user's preference for the course that contains the quiz
 */
function ddq_is_quiz_disabled_for_current_user($quiz_post_id)
{
    // If no quiz_post_id, nothing to do
    if (empty($quiz_post_id)) {
        return false;
    }

    // 1) global list check
    $global = ddq_global_disabled_quiz_ids();
    if (in_array((int) $quiz_post_id, $global['quiz_post_ids'], true)) {
        return true;
    }

    // 2) per-user preference (requires logged in)
    $user_id = get_current_user_id();
    if (empty($user_id)) {
        return false; // We only apply per-user setting for logged-in users
    }

    // Get course id for this quiz (LearnDash helper)
    if (function_exists('learndash_get_course_id')) {
        $course_id = learndash_get_course_id($quiz_post_id);
    } else {
        $course_id = 0;
    }

    if (empty($course_id)) {
        return false;
    }

    $pref = get_user_meta($user_id, 'mc_timer_preference_' . $course_id, true);

    return ($pref === 'without_timer');
}

/**
 * Hook into learndash_quiz_settings (server-side) - will prevent inline JS timer from being printed
 */
add_filter('learndash_quiz_settings', function ($settings, $quiz_post_id) {
    if (ddq_is_quiz_disabled_for_current_user($quiz_post_id)) {
        $settings['timeLimit']  = 0;
        $settings['timelimit']  = 0;
        $settings['time_limit'] = 0;
        error_log("[ddq] learndash_quiz_settings: disabled timer for quiz_post_id={$quiz_post_id} user=" . get_current_user_id());
    }
    return $settings;
}, 20, 2);

/**
 * Hook into WP Pro Quiz form array (extra safety)
 */
add_filter('wp_pro_quiz_filter_form', function ($form, $quiz) {
    // try to detect quiz post id from the form
    $quiz_post_in_form = isset($form['quiz']) ? (int) $form['quiz'] : 0;

    // If we don't have quiz post id but have quizId (pro id), try to find post id (best-effort)
    if (empty($quiz_post_in_form) && ! empty($form['quizId'])) {
        // Attempt to find a matching sfwd-quiz post with meta linking to pro id
        $pro_id = (int) $form['quizId'];
        $found = get_posts(array(
            'post_type'  => 'sfwd-quiz',
            'numberposts' => 1,
            'meta_query' => array(
                array(
                    'key'   => 'wpProQuiz_master_quiz_id', // try common meta keys
                    'value' => $pro_id,
                ),
            ),
        ));

        if (! empty($found)) {
            $quiz_post_in_form = $found[0]->ID;
        } else {
            // try another common meta key
            $found = get_posts(array(
                'post_type'  => 'sfwd-quiz',
                'numberposts' => 1,
                'meta_query' => array(
                    array(
                        'key'   => 'quiz_pro_id',
                        'value' => $pro_id,
                    ),
                ),
            ));
            if (! empty($found)) {
                $quiz_post_in_form = $found[0]->ID;
            }
        }
    }

    if ($quiz_post_in_form && ddq_is_quiz_disabled_for_current_user($quiz_post_in_form)) {
        $form['timeLimit']  = 0;
        $form['timelimit']  = 0;
        $form['time_limit'] = 0;
        error_log("[ddq] wp_pro_quiz_filter_form: disabled timer for form quiz={$quiz_post_in_form} user=" . get_current_user_id());
    }

    return $form;
}, 20, 2);

/**
 * HTML fallback: Replace inline JS timelimit: N -> 0 only for quizzes that belong to a course
 * where current user set 'without_timer'. Only runs for logged-in users.
 */
add_action('template_redirect', function () {
    if (is_admin()) {
        return;
    }

    // Only enable fallback for logged-in users (we cannot rely on user meta otherwise)
    if (! is_user_logged_in()) {
        return;
    }

    ob_start('ddq_replace_timelimit_in_html');
}, 1);

function ddq_replace_timelimit_in_html($buffer)
{
    // quick fail if buffer doesn't contain "load_wpProQuizFront" or "wpProQuiz_"
    if (strpos($buffer, 'load_wpProQuizFront') === false && strpos($buffer, 'wpProQuiz_') === false) {
        return $buffer;
    }

    // Find quiz_post_id occurrences and apply replacement only if that quiz should be disabled for user
    // Pattern for quiz post id in the inline JS can be: quiz: 114924  or "quiz":114924
    if (preg_match_all('/["\']?quiz["\']?\s*:\s*(\d+)/i', $buffer, $matches)) {
        $unique_quiz_ids = array_unique($matches[1]);
        foreach ($unique_quiz_ids as $quiz_post_id) {
            if (ddq_is_quiz_disabled_for_current_user((int) $quiz_post_id)) {
                // replace timelimit for this quiz only
                // This replacement is coarse but limited to pages where quiz is present
                $buffer = preg_replace('/(load_wpProQuizFront' . '\s*?\(\)\s*?\{[^}]*timelimit\s*:\s*)\d+/i', '${1}0', $buffer);
                // Generic timelimit replacement (narrower earlier): only apply if the quiz wrapper id exists
                $buffer = preg_replace('/(wpProQuiz_' . (int) $quiz_post_id . '.*?timelimit\s*:\s*)\d+/is', '${1}0', $buffer);
                // Fallback general replace on page -- it's safe because we check user preference above
                $buffer = preg_replace('/(timelimit\s*:\s*)\d+/i', '${1}0', $buffer);
                error_log("[ddq] fallback HTML replace applied for quiz_post_id={$quiz_post_id} user=" . get_current_user_id());
            }
        }
    }

    return $buffer;
}

/**
 * Go back button and user's timer preference for CURRENT COURSE (default to "with timer") code END =========================================================================
 */



/**
 * Get the group ID for the currently reviewed course that was purchased by the user.
 *
 * @param int $user_id   The ID of the user. Defaults to the currently logged-in user.
 * @param int $course_id The ID of the course. Defaults to the current post ID.
 *
 * @return int|false The ID of the group purchased by the user for the course, or false if not found.
 */
function get_user_group_for_course($user_id = null, $course_id = null)
{
    // Use the current user ID if none is provided
    if (is_null($user_id)) {
        $user_id = get_current_user_id();
    }

    // Use the current post ID if none is provided
    if (is_null($course_id)) {
        $course_id = get_the_ID();
    }

    // Get the groups the user is a member of
    $user_groups = learndash_get_users_group_ids($user_id);

    // Get the groups associated with the course
    $course_groups = learndash_get_course_groups($course_id);

    // Find the intersection between the user's groups and the course's groups
    $matching_groups = array_intersect($user_groups, $course_groups);

    if (!empty($matching_groups)) {
        // Return the first matching group ID
        return reset($matching_groups);
    }

    // Return false if no matching group is found
    return false;
}


add_action('woocommerce_checkout_create_order', 'save_added_from_page_to_order_meta', 10, 2);

function save_added_from_page_to_order_meta($order, $data)
{
    $added_from_page = WC()->session->get('added_from_page');
    if ($added_from_page) {
        $order->update_meta_data('_added_from_page', $added_from_page);
    }
}


// add_filter('woocommerce_email_header', 'custom_email_header_logo', 10, 2);

// function custom_email_header_logo($email_heading, $email) {
//     $logo_url = 'https://studypeak.ch/wp-content/uploads/2024/11/cropped-favicon-2-1.png'; // Change to your logo URL

//     return '<img src="' . esc_url($logo_url) . '" alt="Logo" width="50" style="vertical-align:middle; margin-right:10px;">' . $email_heading;
// }


// add_filter('wp_mail', function($args) {
//     $logo_url = 'https://studypeak.ch/wp-content/uploads/2024/11/cropped-favicon-2-1.png';

//     // Modify email content
//     $args['message'] = '
//         <div style="text-align: center;">
//             <img src="' . esc_url($logo_url) . '" alt="StudyPeak Logo" width="150">
//         </div>' . $args['message'];

//     return $args;
// });


// add_filter('wp_mail_from_name', function($name) {
//     return "🟢 StudyPeak"; // Example with emoji
// });

// function custom_woocommerce_email_logo( $email ) {
//     return '<div style="text-align: center;">
//                 <img src="https://studypeak.ch/wp-content/uploads/2024/11/cropped-favicon-2-1.png" alt="Logo" style="max-width: 200px; height: auto;">
//             </div>';
// }
// add_filter('woocommerce_email_header', 'custom_woocommerce_email_logo', 10, 1);


function fix_my_account_indexing()
{
    if (is_account_page()) {
        // Remove any existing robots meta tag
        ob_start(function ($buffer) {
            // Remove any previous robots meta tag
            $buffer = preg_replace('/<meta\s+name=[\'"]robots[\'"]\s+content=[\'"].*?[\'"]\s*\/?>/i', '', $buffer);

            // Add the correct meta robots tag
            return str_replace('</title>', "</title>\n<meta name='robots' content='index, follow'>", $buffer);
        });
    }
}
add_action('template_redirect', 'fix_my_account_indexing');



// Validate Custom Captcha
add_filter('wpcf7_validate_text*', 'custom_captcha_validation', 20, 2);

function custom_captcha_validation($result, $tag)
{
    $name = $tag->name;
    if ($name === 'custom-captcha-response') {
        $captcha_code = isset($_POST['custom-captcha-code']) ? $_POST['custom-captcha-code'] : '';
        $captcha_response = isset($_POST['custom-captcha-response']) ? $_POST['custom-captcha-response'] : '';

        if (empty($captcha_response) || $captcha_response !== $captcha_code) {
            $result->invalidate($tag, 'Invalid CAPTCHA.');
        }
    }
    return $result;
}

// $admin_email = get_option('admin_email'); // Fetch the website admin email

// $to = 'parth.jogi@nyusoft.com'; // Recipient Email
// $subject = 'Test Email from WordPress';
// $message = 'Hello, this is a test email sent from WordPress. From: Admin <' . $admin_email . '>';

// //$headers = ['From: Admin <' . $admin_email . '>'];

// wp_mail($to, $subject, $message);


/**
 * Post read time start.
 */
function get_post_read_time_shortcode($atts)
{
    global $post; // Ensure we use the global post object
    $post_id = $post->ID; // Get the current post ID
    $title = get_the_title($post_id); // Get the post title
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content)); // Count words
    $words_per_minute = 135; // Average reading speed

    $minutes = ceil($word_count / $words_per_minute);
    return '<span class="read-time"><i class="fas fa-stopwatch"></i> ' . $minutes . ' min</span>';
}
add_shortcode('post_read_time', 'get_post_read_time_shortcode');

function add_inline_read_time_script()
{
    if (is_single()) { // Only run on single post pages
        $read_time = do_shortcode('[post_read_time]'); // Get the read time
    ?>
        <script>
            jQuery(document).ready(function($) {
                var readTime = '<?php echo addslashes($read_time); ?>'; // Escape for JS
                $('.brxe-post-meta .post_date').each(function() {
                    $(this).after(readTime);
                });
            });
        </script>
    <?php
    }
}
add_action('wp_footer', 'add_inline_read_time_script');



/**
 * Login logo related code.
 */
function custom_login_logo()
{ ?>
    <style type="text/css">
        #login h1 a {
            background-image: url('<?php echo site_url(); ?>/wp-content/uploads/2024/10/studypeak-logo.svg"');
            background-size: contain;
            background-repeat: no-repeat;
            width: 80%;
            height: 50px;
            display: block;
        }
    </style>
    <?php }
add_action('login_enqueue_scripts', 'custom_login_logo');

function custom_login_url()
{
    return home_url();
}
add_filter('login_headerurl', 'custom_login_url');

/**
 * Auto logout after an hour.
 */
/*function set_auto_logout_time($expiration, $user_id, $remember) {
    return HOUR_IN_SECONDS; // 1 hour (3600 seconds)
}
add_filter('auth_cookie_expiration', 'set_auto_logout_time', 10, 3);*/

add_action('wp_ajax_sp_ajax_logout', 'sp_ajax_logout');
add_action('wp_ajax_nopriv_sp_ajax_logout', 'sp_ajax_logout');

function sp_ajax_logout()
{
    if (is_user_logged_in()) {
        wp_logout();
        wp_clear_auth_cookie();
    }
    if (ob_get_length()) ob_clean();
    wp_send_json_success('Logged out');
    wp_die();
}

/**
 * Enqueue inactivity logout script for all users.
 */
function sp_enqueue_inactivity_logout_script()
{
    if (is_user_logged_in()) {
    ?>
        <script>
            (function() {
                const inactivityLimit = 3600; // seconds (e.g., 3600 for 1 hour)
                let timeout, countdown;
                const channel = new BroadcastChannel('sp_user_activity');

                function logoutUser() {
                    console.log('Logging out due to inactivity...');
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'action=sp_ajax_logout'
                        })
                        .then(response => {
                            if (!response.ok) {
                                console.error('Network response was not OK');
                                throw new Error('Network error');
                            }
                            return response.text(); // fallback for blank JSON
                        })
                        .then(text => {
                            console.log('Raw response:', text);
                            try {
                                const data = JSON.parse(text);
                                if (data.success) {
                                    window.location.href = "<?php echo esc_url(home_url()); ?>";
                                } else {
                                    console.error('Logout failed');
                                }
                            } catch (e) {
                                console.warn('Invalid JSON, assuming logout success.');
                                window.location.href = "<?php echo esc_url(home_url()); ?>";
                            }
                        })
                        .catch(error => {
                            console.error('Logout error:', error);
                            // Optional fallback redirect anyway
                            window.location.href = "<?php echo esc_url(home_url()); ?>";
                        });
                }

                function resetTimer(broadcast = true) {
                    clearTimeout(timeout);
                    clearInterval(countdown);

                    if (broadcast) {
                        channel.postMessage('active');
                    }

                    timeout = setTimeout(logoutUser, inactivityLimit * 1000);

                    let timeLeft = inactivityLimit;
                    countdown = setInterval(() => {
                        timeLeft--;
                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                        }
                        if (timeLeft <= 60) {
                            console.log('Auto-logout in: ' + timeLeft + 's');
                        }
                    }, 1000);
                }

                // Sync across tabs
                channel.onmessage = function(event) {
                    if (event.data === 'active') {
                        resetTimer(false);
                    }
                };

                ['load', 'mousemove', 'mousedown', 'click', 'scroll', 'keypress', 'keydown'].forEach(evt => {
                    window.addEventListener(evt, () => resetTimer());
                });

                resetTimer();
            })();
        </script>
    <?php
    }
}
add_action('wp_footer', 'sp_enqueue_inactivity_logout_script');
add_action('admin_footer', 'sp_enqueue_inactivity_logout_script');


/**
 * Functionality to send email 5 days before product expiry date code start ------------------------------------------------------
 */

// Hook to apply the discount when product is added to cart
function apply_discount_to_cart_item($cart_item_data, $product_id, $variation_id = 0)
{
    if (isset($_GET['discounted_price'])) {
        $discounted_price = floatval($_GET['discounted_price']);

        if ($discounted_price > 0) {
            $cart_item_data['custom_price'] = $discounted_price;
        }
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'apply_discount_to_cart_item', 10, 3);

/**
 * Modify the cart item price based on custom price.
 */
function modify_cart_item_price($cart_object)
{
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'modify_cart_item_price', 10, 1);

/**
 * Generate a re-purchase link with a 10% discount.
 */
function custom_repurchase_link_with_variation($product_id, $variation_id = 0)
{
    $product = wc_get_product($product_id);
    if (!$product) return '';

    $price = $variation_id ? wc_get_product($variation_id)->get_price() : $product->get_price();

    $discounted_price = ceil($price * 0.90); // Apply 10% discount and round up

    return add_query_arg([
        'add-to-cart' => $product_id,
        'variation_id' => $variation_id,
        'discounted_price' => $discounted_price,
    ], wc_get_checkout_url());
}

/**
 * Send an email 5 days before product expiry.
 */
function send_license_expiry_email($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) return;

    $completion_date = $order->get_date_completed();
    if (!$completion_date) return;

    foreach ($order->get_items() as $item) {
        $item_name   = $item->get_name();
        $product_id  = $item->get_product_id();
        $variation_id = $item->get_variation_id();
        $product     = wc_get_product($product_id);

        // Skip specific product names
        //if (strpos($item_name, '%stellwerktest%') !== false) continue;

        // Extract access days from the product name
        $parts = explode(' - ', $item_name);
        $accessdays = isset($parts[1]) && is_numeric($parts[1]) ? intval($parts[1]) : 0;

        if ($accessdays <= 0) continue;

        // Calculate expiry date
        $expiry_date = (clone $completion_date)->modify("+$accessdays days");
        // Check if today is 5 days before the expiry date
        $today = new DateTime();
        $diff = $today->diff($expiry_date);
        if ($diff->days == 5 && $diff->invert == 0) { // Ensure future date
            $repurchase_link = custom_repurchase_link_with_variation($product_id, $variation_id);
            $user_email = $order->get_billing_email();
            $subject = "Deine Lizenz läuft bald ab - Verlängere jetzt!";

            $email_content = '
            <html>
            <head>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
            </head>
            <body>
                <table cellpadding="0" cellspacing="0" style="width:600px;margin:auto;background:#edf1f7;font-family:\'Poppins\', Arial, sans-serif;">
                    <thead>
                        <tr>
                            <th style="background: #EDF4F1; padding: 15px;" colspan="4">
                                <a href="' . site_url() . '">
                                    <img src="' . site_url() . '/wp-content/uploads/2025/01/studypeak-logo.png" alt="Logo" width="120">
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                            <td style="background:#ffffff;padding:20px;text-align:left; font-size:14px; line-height:22px;">
                                <p style="margin-top:0;">Hallo!</p>
                                <p>Deine Lizenz läuft in 5 Tagen ab. Damit du weiter für deine Prüfung üben kannst, verlängere nun deine aktuelle Lizenz unter «Kaufhistorie». Wenn du sie jetzt verlängerst, profitierst du von einer Vergünstigung.</p>
                                <p><a href="' . esc_url($repurchase_link) . '" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; font-weight: bold; display: inline-block; border-radius: 4px;">Lizenz verlängern</a></p>
                                <p>Wenn du einen anderen Kurs oder eine andere Lizenzlaufzeit buchen möchtest, entdecke unsere verschiedenen Angebote auf unserer Website.</p>
                                <p><a href="https://studypeak.ch/" style="text-decoration: none; font-weight: bold;">Weitere Angebote</a></p>
                                <p>Liebe Grüsse<br>Dein studypeak-Team</p>
                            </td>
                            <td style="width:10%;background:#EDF4F1;padding:20px"></td>
                        </tr>
                    </tbody>
                </table>
            </body>
            </html>';

            wp_mail($user_email, $subject, $email_content, ['Content-Type: text/html; charset=UTF-8']);
        }
    }
}
add_action('woocommerce_order_status_completed', 'send_license_expiry_email');

// Schedule cron event if not already scheduled
function schedule_license_expiry_cron()
{
    if (!wp_next_scheduled('send_license_expiry_email_cron')) {
        wp_schedule_event(time(), 'daily', 'send_license_expiry_email_cron');
    }
}
add_action('wp', 'schedule_license_expiry_cron');

// Hook the cron event to a function
add_action('send_license_expiry_email_cron', 'check_and_send_license_expiry_emails');

function check_and_send_license_expiry_emails()
{
    $args = [
        'status' => 'completed',
        'limit'  => -1, // Get all completed orders
    ];
    $orders = wc_get_orders($args);

    foreach ($orders as $order) {
        send_license_expiry_email($order->get_id());
    }
}

/**
 * Functionality to send email 5 days before product expiry date code end ------------------------------------------------------
 */


add_filter('woocommerce_default_address_fields', function ($fields) {
    if (isset($fields['postcode'])) {
        unset($fields['postcode']['validate']); // Remove validation rules
    }
    return $fields;
});

add_filter('woocommerce_get_order_item_totals', function ($totals, $order, $tax_display) {
    if (isset($totals['shipping'])) {
        unset($totals['shipping']); // Remove shipping row from emails
    }
    return $totals;
}, 10, 3);



/**
 * Add custom body class to specific quiz pages
 */
function add_quiz_body_class($classes)
{
    // Get current post slug
    $post_slug = basename(get_permalink());

    // Array of quiz slugs that need the special class
    $quiz_slugs = array(
        'erkennen-von-grundstaemmen-quiz-4',
        'gross-und-kleinschreibung-quiz-1',
        'gross-und-kleinschreibung-quiz-2',
        'gross-und-kleinschreibung-quiz-3',
        'gross-und-kleinschreibung-quiz-4',
        'gross-und-kleinschreibung-quiz-5',
        'satzglieder-quiz',
        'konditionalsaetze-und-konjunktiv-quiz-1',
        'synonyme-quiz-2',
        'antonyme-quiz-2',
        'metaphern-und-vergleiche-quiz-1-2',
        'erkennen-von-grundstaemmen-quiz-2',
        'wortfamilien-erkennen-quiz-3',
        'wortfamilien-erkennen-quiz-4',
        'wortfamilien-erkennen-quiz-3-2',
        'wortfamilien-erkennen-quiz-4-2',
        'bedeutung-von-wortstaemmen-quiz-2',
        'adjektive-quiz',
        'konjugationen-von-verben-quiz-1-2',
        'kurzzeitgymipruefung-pruefungssimulation-quiz',
        'zeitform-quiz-2',
        'imperativ-quiz',
        'lueckentext-quiz',
        'tieferes-textverstaendnis-quiz',
        'kasus-anpassen-quiz-1',
        'kasus-anpassen-quiz-2',
        'uebersetzungen-quiz',
        'uebersetzungen-quiz-2',
        'probezeit-deutsch-langzeitgymnasium-pruefungssimulation-quiz',
        'kasus-anpassen-quiz-1-2'
    );

    // Add class if current page is in the quiz slugs array
    if (in_array($post_slug, $quiz_slugs)) {
        $classes[] = 'mc-bigger-fill-in-blanks-inputs';
    }

    return $classes;
}
add_filter('body_class', 'add_quiz_body_class');

function prevent_browser_translation()
{
    add_action('wp_head', function () {
    ?>
        <meta name="google" content="notranslate">
    <?php
    });
}
add_action('init', 'prevent_browser_translation');

// Add notranslate class to body
function add_notranslate_body_class($classes)
{
    $classes[] = 'notranslate';
    return $classes;
}
add_filter('body_class', 'add_notranslate_body_class');

// Add translate="no" to html tag
function add_notranslate_attribute($output)
{
    return str_replace('<html', '<html translate="no"', $output);
}
add_filter('language_attributes', 'add_notranslate_attribute');

add_action('wp_head', function () {
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    echo '<style>
 @font-face {
  font-family: "Satoshi &#8211; Medium";
  font-weight: 500;
  font-display: block;
  src: url(' . $base_url . '/2024/10/Satoshi-Medium.woff) format("woff");
}

@font-face {
  font-family: "Satoshi &#8211; Bold";
  font-weight: 700;
  font-display: block;
  src: url(' . $base_url . '/2024/10/Satoshi-Bold.woff) format("woff");
}
    </style>';
}, PHP_INT_MAX); // Set priority to 1 for very high priority

// Banner Form Shortcode -----------------------------------------------------------------------------------
/*function banner_form_shortcode() {
    ob_start(); 

    // Detect current language from Weglot or <html lang="">
    $current_lang = 'de'; // default
    if (function_exists('weglot_get_current_language')) {
        $current_lang = weglot_get_current_language();
    } else {
        $current_lang = substr(get_bloginfo('language'), 0, 2); // fallback
    }

    // Build home URL
    $home_url = home_url();
    if ($current_lang !== 'de') { // No slug for German
        $home_url = trailingslashit($home_url) . $current_lang;
    }

    ?>
    
    <form class="banner_form" >
        <div class="form-field-course-wrapper">
            <select id="form-field-course" name="group">
                <option value="<?php echo esc_url($home_url . '/multicheck-vorbereitung'); ?>">Multicheck&nbsp;Vorbereitung</option>
                <option value="<?php echo esc_url($home_url . '/stellwerktest-vorbereitung'); ?>">Stellwerk&nbsp;Vorbereitung</option>
                <option value="<?php echo esc_url($home_url . '/gymi-vorbereitung'); ?>">Gymi&nbsp;Vorbereitung</option>
                <option value="<?php echo esc_url($home_url . '/ims-bms-fms-hms-vorbereitung'); ?>">IMS&nbsp;|&nbsp;BMS&nbsp;|&nbsp;FMS&nbsp;Vorbereitung</option>
                <option value="<?php echo esc_url($home_url . '/probezeit-vorbereitung'); ?>">Probezeit&nbsp;Vorbereitung</option>
            </select>
        </div>
    
        <button type="submit" class="bricks-button bricks-background-light xl">
            <span class="text">Entdecken</span>
        </button>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.banner_form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const select = document.getElementById('form-field-course');
                    if (select && select.value) {
                        window.location.assign(select.value); // bypass Weglot
                    }
                });
            }
        });

    </script>
    <div id="banner-form-container"></div>
    
    <?php return ob_get_clean();
}
add_shortcode('banner_form', 'banner_form_shortcode');*/

function banner_form_shortcode()
{
    ob_start();

    // Get the current URL path to determine if we're on a specific course page
    $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $is_course_page = false;
    $course_slugs = [
        'multicheck-vorbereitung',
        'stellwerktest-vorbereitung',
        'gymi-vorbereitung',
        'ims-bms-fms-hms-vorbereitung',
        'probezeit-vorbereitung'
    ];

    foreach ($course_slugs as $slug) {
        if (strpos($current_path, $slug) !== false) {
            $is_course_page = true;
            break;
        }
    }

    // Detect current language from Weglot or <html lang="">
    $current_lang = 'de'; // default
    if (function_exists('weglot_get_current_language')) {
        $current_lang = weglot_get_current_language();
    } else {
        $current_lang = substr(get_bloginfo('language'), 0, 2); // fallback
    }

    // Build home URL
    $home_url = home_url();
    if ($current_lang !== 'de') { // No slug for German
        $home_url = trailingslashit($home_url) . $current_lang;
    }

    // Define course URLs
    $course_urls = [
        'multicheck' => trailingslashit($home_url) . 'multicheck-vorbereitung/',
        'stellwerktest' => trailingslashit($home_url) . 'stellwerktest-vorbereitung/',
        'gymi' => trailingslashit($home_url) . 'gymi-vorbereitung/',
        'ims' => trailingslashit($home_url) . 'ims-bms-fms-hms-vorbereitung/',
        'probezeit' => trailingslashit($home_url) . 'probezeit-vorbereitung/'
    ];

    // Set default selected course
    $selected_course = 'multicheck'; // Default selection
    if ($is_course_page) {
        foreach ($course_urls as $key => $url) {
            if (strpos($current_path, $key) !== false) {
                $selected_course = $key;
                break;
            }
        }
    }
    ?>

    <form class="banner_form" id="banner-course-selector">
        <div class="form-field-course-wrapper">
            <select id="form-field-course" name="group" class="banner-course-select">
                <option value="multicheck" <?php selected($selected_course, 'multicheck'); ?>>Multicheck&nbsp;Vorbereitung</option>
                <option value="stellwerktest" <?php selected($selected_course, 'stellwerktest'); ?>>Stellwerk&nbsp;Vorbereitung</option>
                <option value="gymi" <?php selected($selected_course, 'gymi'); ?>>Gymi&nbsp;Vorbereitung</option>
                <option value="ims" <?php selected($selected_course, 'ims'); ?>>IMS&nbsp;|&nbsp;BMS&nbsp;|&nbsp;FMS&nbsp;Vorbereitung</option>
                <option value="probezeit" <?php selected($selected_course, 'probezeit'); ?>>Probezeit&nbsp;Vorbereitung</option>
            </select>
        </div>

        <button type="submit" class="bricks-button bricks-background-light xl">
            <span class="text">Entdecken</span>
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('banner-course-selector');
            const select = document.getElementById('form-field-course');

            if (form && select) {
                // Handle form submission
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const selectedValue = select.value;
                    const courseUrls = <?php echo json_encode($course_urls); ?>;

                    if (courseUrls[selectedValue]) {
                        window.location.href = courseUrls[selectedValue];
                    }
                });

                // Optional: Handle direct select change if you want to navigate on change
                select.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const courseUrls = <?php echo json_encode($course_urls); ?>;

                    if (courseUrls[selectedValue]) {
                        window.location.href = courseUrls[selectedValue];
                    }
                });
            }
        });
    </script>

<?php
    return ob_get_clean();
}
add_shortcode('banner_form', 'banner_form_shortcode');

function free_courses_groups_form_shortcode()
{   
   ob_start();

    // Get the current language groups
    $current_language = apply_filters('wpml_current_language', 'de');
    
    // do_action('wpml_switch_language', 'de');  // Switch to German
    
    // Get the current page URL segment (e.g., "multicheck-vorbereitung")
    $current_url = $_SERVER['REQUEST_URI'];
    $url_segments = explode('/', trim($current_url, '/'));
    $current_page_category = end($url_segments); // Get the last segment

    // Fetch groups filtered by the ACF category and language
    $groups = get_posts(array(
        'post_type'      => 'groups',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'suppress_filters' => false,
        'lang'           => $current_language,
        'meta_query'     => array(
            array(
                'key'     => 'group_package_category', // ACF field on the group post type
                'value'   => $current_page_category,   // Match URL segment with ACF value
                'compare' => '='
            )
        ),
        'tax_query' => array(
            array(
                'taxonomy' => 'category', // Post category taxonomy
                'field'    => 'slug',
                'terms'    => 'main',     // Filter only in "Main" post category
            )
        )
    )); 
    
    // do_action('wpml_switch_language', ICL_LANGUAGE_CODE);  // Switch back to current language
    
    ?>
        <div class="plan-details-group free-plan-section">
            <form id="groups-plan-filters" class="common_filter_forms groupf-forms" <?php //if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>>        
                <div class="form-groups" role="group"> 
                    <select id="form-field-plan-groups" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                        <?php 
                        // Detect current language via Weglot
                        $current_lang = function_exists('weglot_get_current_language') ? weglot_get_current_language() : '';
                        foreach ($groups as $gpkey => $gpvalue) {             
                            echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                        }  
                        ?> 
                    </select>               
                </div>   
            </form>  
        </div>
<?php
    return ob_get_clean();
}
add_shortcode('free_courses_groups', 'free_courses_groups_form_shortcode');

add_action('admin_init', function () {
    if (!isset($_GET['replace'])) {
        return;
    }

    global $wpdb;

    // Get all rows from wp_learndash_pro_quiz_question where answer_data contains "multiclass.ch"
    $questions = $wpdb->get_results(
        "SELECT id, answer_data FROM wp_learndash_pro_quiz_question WHERE answer_data LIKE '%multiclass.ch%'"
    );

    foreach ($questions as $question) {
        $unserialized_data = maybe_unserialize($question->answer_data);

        if (is_array($unserialized_data)) {
            foreach ($unserialized_data as &$answer) {
                if (is_object($answer) && get_class($answer) === 'WpProQuiz_Model_AnswerTypes') {

                    $reflection = new ReflectionClass($answer);
                    if ($reflection->hasProperty('_sortString')) {
                        $property = $reflection->getProperty('_sortString');
                        $property->setAccessible(true); // Allow modification
                        $current_value = $property->getValue($answer);


                        $new_value = str_replace('multiclass.ch', 'studypeak.ch', $current_value);
                        $property->setValue($answer, $new_value);
                    }
                }
            }

            $updated_data = maybe_serialize($unserialized_data);

            // Update the database
            $wpdb->update(
                'wp_learndash_pro_quiz_question',
                ['answer_data' => $updated_data],
                ['id' => $question->id]
            );
        }
    }
});

// Do not lowercase Cloze Answers (Fill Gaps)
add_filter('learndash_quiz_question_cloze_answers_to_lowercase', '__return_false');

// Skip Question Label
add_filter('ld_template_args_learndash_quiz_messages', 'mc_fix_learndash_translation_messages', 10, 3);

function mc_fix_learndash_translation_messages($args, $filepath, $echo)
{
    switch ($args['context']) {
        case 'quiz_skip_button_label':
            $args['message'] = sprintf(esc_html_x('Skip %s', 'placeholder: question', 'learndash'), learndash_get_custom_label('question'));
            break;
    }
    return $args;
}

// Add google site verification meta on the hearder
function add_google_verification_meta()
{
    echo '<meta name="google-site-verification" content="dhj1LWz87h4iKIMwywjk5jh9JUqgTnQRHQ1ZVxJcu5Q" />' . "\n";
}
add_action('wp_head', 'add_google_verification_meta');

// Page load remove the add to cart query parameter
add_action('template_redirect', 'prevent_duplicate_add_to_cart_on_reload');
function prevent_duplicate_add_to_cart_on_reload()
{
    if (is_checkout() && isset($_GET['add-to-cart'])) {
        $product_id   = absint($_GET['add-to-cart']);
        $variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : 0;

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            // Check if this variation/product is already in the cart
            if (
                ($variation_id && $cart_item['variation_id'] == $variation_id) ||
                (! $variation_id && $cart_item['product_id'] == $product_id)
            ) {
                // Get current query parameters
                $current_params = $_GET;

                // Remove add-to-cart and variation_id parameters
                unset($current_params['add-to-cart']);
                unset($current_params['variation_id']);

                // Build the redirect URL with preserved query parameters
                $redirect_url = 'https://studypeak.ch/' . weglot_get_current_language() . '/zur-kasse/';

                // Add preserved query parameters if any exist
                if (! empty($current_params)) {
                    $redirect_url = add_query_arg($current_params, $redirect_url);
                }

                wp_safe_redirect($redirect_url);
                exit;
            }
        }
    }
}


// To temporarily disable a list of WooCommerce coupon codes
add_filter('woocommerce_coupon_is_valid', 'disable_bulk_coupons_temporarily', 10, 3);
function disable_bulk_coupons_temporarily($valid, $coupon, $discount)
{
    $disabled_coupons = array(
        'NACCZ3ZF',
        'W94XTQDX',
        '7BHA4RRJ',
        'BNMRYW5D',
        '4ZKKXMZE',
        'GBG85GWN',
        'HBUKP9CV',
        '8XT5WC3H',
        '3V9K7688',
        'E9W6MEK4'
    );

    if (in_array(strtoupper($coupon->get_code()), $disabled_coupons, true)) {
        return false;
    }

    return $valid;
}


//STOP IF EXTEND COUPON DIRECT APPLY
add_filter('woocommerce_coupon_is_valid', 'conditionally_allow_coupon', 10, 2);
function conditionally_allow_coupon($is_valid, $coupon)
{

    $restricted_coupons = array('10off'); // replace with your coupon code(s)
    $user_id = get_current_user_id();
    if (in_array(strtolower($coupon->get_code()), $restricted_coupons)) {

        $has_purchased = false;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];

            if (wc_customer_bought_product('', $user_id, $product_id)) {
                $has_purchased = true;
                break;
            }
        }

        // If the user hasn't purchased any products in the cart, invalidate the coupon
        return $has_purchased ? $is_valid : false;
    }

    return $is_valid;
}

add_filter('woocommerce_cart_totals_coupon_label', 'custom_coupon_label_display', 10, 2);
add_filter('woocommerce_coupon_discount_display', 'custom_coupon_label_display', 10, 2);

function custom_coupon_label_display($label, $coupon)
{
    if ($coupon->get_code() === '10off') {
        return "10%-Verlängerungsrabatt";
    }
    return $label;
}


/*----------------------------------------    
            STORE DYNAMIC AVATAR
----------------------------------------*/
add_action('wp_ajax_save_svg_to_server', 'handle_save_svg_to_server');
add_action('wp_ajax_nopriv_save_svg_to_server', 'handle_save_svg_to_server');

function handle_save_svg_to_server()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'profile_ajax_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
    }

    if (empty($_POST['svg_content'])) {
        wp_send_json_error(['message' => 'No SVG data received']);
    }

    $svg = base64_decode($_POST['svg_content']);
    if (!$svg) {
        wp_send_json_error(['message' => 'Failed to decode SVG']);
    }

    $upload_dir = get_stylesheet_directory() . '/assets/images/avatars';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = 'avatar_' . time() . '.svg';
    $file_path = $upload_dir . '/' . $filename;

    if (file_put_contents($file_path, $svg) === false) {
        wp_send_json_error(['message' => 'Could not save SVG']);
    }

    $user_id = get_current_user_id();

    // ✅ Get full URL
    $theme_url  = get_stylesheet_directory_uri();
    $folder_path = get_stylesheet_directory() . '/assets/images/avatars';
    $folder_uri  = get_stylesheet_directory_uri() . '/assets/images/avatars';
    $img_url = $theme_url . '/assets/images/avatars/' . $filename;

    // ✅ Delete previous avatar if exists
    $prev_url = get_user_meta($user_id, 'moopenid_user_avatar', true);
    if ($prev_url && strpos($prev_url, $folder_url) === 0) {
        $prev_path = str_replace($folder_uri, $folder_path, $prev_url);
        if (file_exists($prev_path)) {
            unlink($prev_path); // delete old SVG
        }
    }

    // ✅ Update user meta
    if ($user_id) {
        update_user_meta($user_id, 'moopenid_user_avatar', $img_url);
    }

    wp_send_json_success([
        'filename' => $filename,
        'url' => $img_url,
        'message' => 'SVG saved and user meta updated.'
    ]);
}


add_action('template_redirect', 'redirect_my_account_page');
function redirect_my_account_page()
{
    if (is_account_page() && is_user_logged_in()) {
        wp_redirect(home_url('/mein-profil/')); // Replace with your target page URL
        exit;
    }

    // Optional: Redirect even logged-in users
    // if (is_account_page()) {
    //     wp_redirect(home_url('/another-page/'));
    //     exit;
    // }
}





/* ---------------------------------
    A small PHP handler to remove the item from the cart and return updated fragments without pageload =================================================
--------------------------------- */

add_action('wp_ajax_nopriv_asp_remove_cart_item', 'asp_remove_cart_item_ajax');
add_action('wp_ajax_asp_remove_cart_item',        'asp_remove_cart_item_ajax');

function asp_remove_cart_item_ajax()
{
    if (! class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not active');
    }

    $cart_item_key = isset($_POST['cart_item_key'])
        ? wc_clean(wp_unslash($_POST['cart_item_key']))
        : '';

    if ($cart_item_key && WC()->cart->get_cart_item($cart_item_key)) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    WC()->cart->calculate_totals();

    $subtotal = WC()->cart->get_cart_subtotal();
    $total    = wc_price(WC()->cart->get_total('edit'));

    $response = [
        'subtotal' => $subtotal,
        'total'    => $total,
        'key'      => $cart_item_key,
    ];

    if (WC()->cart->is_empty()) {
        $response['redirect'] = home_url(); // Redirect to homepage
    }

    wp_send_json_success($response);
}



/**
 * Single device session related code start =====================================
 */

// Enqueue jQuery (required for AJAX)
/*add_action('wp_enqueue_scripts', 'enqueue_single_session_scripts');
function enqueue_single_session_scripts() {
    if (is_user_logged_in()) {
        wp_enqueue_script('jquery');
    }
}

// Add the modal HTML, CSS, and JS to the footer if multiple sessions detected
add_action('wp_footer', 'add_single_session_modal');
function add_single_session_modal() {
    if (!is_user_logged_in()) return;

    $user = wp_get_current_user();
    $allowed_roles = array('customer', 'subscriber');
    if (!array_intersect($allowed_roles, $user->roles)) return;

    if (count(wp_get_all_sessions()) <= 1) return;

    $single_session_data = array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('single_session_nonce'),
        'user_id' => get_current_user_id()
    );

    // Translation texts
    $original_language = function_exists('weglot_get_original_language') ? weglot_get_original_language() : 'de';

    $translations = [
        'de' => [
            'title' => 'Du bist derzeit auf mehreren Geräten eingeloggt.',
            'msg1'  => 'Du kannst dich nur auf <b>einem Gerät</b> gleichzeitig einloggen. Derzeit bist du noch auf einem anderen eingeloggt. Um dich anzumelden, musst du dich vom anderen Gerät abmelden.',
            'msg2'  => 'Möchtest du dich von dem <b>anderen Gerät</b> abmelden?',
            'btn'   => 'Ja, auf dem anderen Gerät abmelden'
        ],
        'en' => [
            'title' => 'You are currently logged in on multiple devices.',
            'msg1'  => 'You can only log in on <b>one device</b> at a time. You are currently logged in on another device. To continue, you must log out from the other device.',
            'msg2'  => 'Do you want to log out from the <b>other device</b>?',
            'btn'   => 'Yes, log out from the other device'
        ],
        'it' => [
            'title' => 'Sei attualmente connesso su più dispositivi.',
            'msg1'  => 'Puoi accedere solo su <b>un dispositivo</b> alla volta. Attualmente sei connesso su un altro dispositivo. Per continuare, devi disconnetterti dall’altro dispositivo.',
            'msg2'  => 'Vuoi disconnetterti dall’<b>altro dispositivo</b>?',
            'btn'   => 'Sì, disconnetti dall’altro dispositivo'
        ],
        'fr' => [
            'title' => 'Vous êtes actuellement connecté sur plusieurs appareils.',
            'msg1'  => 'Vous pouvez vous connecter sur <b>un seul appareil</b> à la fois. Vous êtes actuellement connecté sur un autre appareil. Pour continuer, vous devez vous déconnecter de l’autre appareil.',
            'msg2'  => 'Voulez-vous vous déconnecter de l’<b>autre appareil</b> ?',
            'btn'   => 'Oui, déconnecter de l’autre appareil'
        ],
    ];

    $t = $translations[$original_language] ?? $translations['de'];
    ?>

    <style>
        body.modal-open {
            overflow: hidden !important;
        }
    </style>

    <div class="single-session-modal fade" id="single-session-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button id="close-modal" type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="11" viewBox="0 0 10 11" fill="none">
                            <path d="M9.54102 10.2559C9.36523 10.4316 9.04297 10.4316 8.86719 10.2559L5 6.35938L1.10352 10.2559C0.927734 10.4316 0.605469 10.4316 0.429688 10.2559C0.253906 10.0801 0.253906 9.75781 0.429688 9.58203L4.32617 5.68555L0.429688 1.81836C0.253906 1.64258 0.253906 1.32031 0.429688 1.14453C0.605469 0.96875 0.927734 0.96875 1.10352 1.14453L5 5.04102L8.86719 1.14453C9.04297 0.96875 9.36523 0.96875 9.54102 1.14453C9.7168 1.32031 9.7168 1.64258 9.54102 1.81836L5.64453 5.68555L9.54102 9.58203C9.7168 9.75781 9.7168 10.0801 9.54102 10.2559Z" fill="white"/>
                            </svg>
                        </span>
                    </button>
                </div>
                <div class="modal-body">
                    <h2><?php echo $t['title']; ?></h2>
                    <div class="content">
                        <p><?php echo $t['msg1']; ?></p>
                        <p><?php echo $t['msg2']; ?></p>
                    </div>
                    <button id="logout-others" class="btns"><?php echo $t['btn']; ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var singleSession = <?php echo json_encode($single_session_data); ?>;

        jQuery(function($) {
            // Open modal (when multiple sessions detected)
            function openModal() {
                $('#single-session-modal').fadeIn(300).addClass("active");
                $("body").addClass("modal-open");
            }

            // Close modal
            function closeModal() {
                $('#single-session-modal').fadeOut(300).removeClass("active");
                $("body").removeClass("modal-open");
            }

            // Show modal automatically if it exists in DOM
            if ($('#single-session-modal').length > 0) {
                openModal();
            }

            // Handle "logout others" button
            $('#logout-others').on('click', function() {
                $.post(singleSession.ajaxurl, {
                    action: 'logout_others',
                    nonce: singleSession.nonce,
                    user_id: singleSession.user_id
                }, function() {
                    closeModal();
                });
            });

            // Handle close (logout current device)
            $('#close-modal').on('click', function() {
                $.post(singleSession.ajaxurl, {
                    action: 'logout_current',
                    nonce: singleSession.nonce
                }, function(response) {
                    closeModal();
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                });
            });
        });

    </script>
    <?php
}

// AJAX handler to log out other sessions
add_action('wp_ajax_logout_others', 'ajax_logout_others');
function ajax_logout_others() {
    check_ajax_referer('single_session_nonce', 'nonce');
    $user_id = intval($_POST['user_id']);
    wp_destroy_other_sessions();
    set_transient('logout_signal_' . $user_id, time(), 30);
    wp_send_json_success();
}

// AJAX handler to log out current session
add_action('wp_ajax_logout_current', 'ajax_logout_current');
function ajax_logout_current() {
    check_ajax_referer('single_session_nonce', 'nonce');
    wp_destroy_current_session();
    wp_clear_auth_cookie();
    wp_send_json_success(array('redirect' => wp_login_url()));
}*/

/**
 * Single device session related code end =====================================
 */


/**
 * Show % inside LearnDash status icon for lessons (in lists). =====================================
 * We add data-progress="NN" on the icon and paint it with CSS ::after.
 */
add_filter('learndash_status_icon', 'sp_ld_add_percent_attr_to_icon', 20, 4);
function sp_ld_add_percent_attr_to_icon($html, $status = '', $post = null, $user_id = 0)
{
    if (! is_user_logged_in()) {
        return $html;
    }

    // LD passes a WP_Post as $post.
    if (! $post instanceof WP_Post) {
        return $html;
    }

    // Only show for LESSON rows (as requested). Add topics/quizzes later if needed.
    if ($post->post_type !== 'sfwd-lessons') {
        return $html;
    }

    $user_id   = $user_id ?: get_current_user_id();
    $course_id = function_exists('learndash_get_course_id') ? learndash_get_course_id($post->ID) : 0;

    if (! $course_id || ! function_exists('learndash_get_lesson_progress')) {
        return $html;
    }

    // Get lesson progress array: [ completed, total, percentage ].
    $progress = learndash_get_lesson_progress($post->ID, $course_id, $user_id);
    $percent  = 0;

    if (is_array($progress) && isset($progress['percentage'])) {
        $percent = (int) round($progress['percentage']);
    }

    // Inject data-progress on the icon div (first occurrence only).
    // Works even if LD changes inner HTML later.
    $html = preg_replace(
        '/(<div[^>]*class="[^"]*\ bld-status-icon\b[^"]*"[^>]*)(>)/i',
        '$1 data-progress="' . $percent . '"$2',
        $html,
        1
    );

    return $html;
}


/**
 * Print CSS to paint the number inside the icon.
 */
add_action('wp_head', function () {
?>
    <script>
        jQuery(document).ready(function($) {
            // Function to update progress percentage
            /*function updateProgressIcons() {
                $('.ld-status-in-progress.ld-secondary-in-progress-icon').each(function() {
                    // Find the progress percentage from the parent container
                    var $container = $(this).closest('.ld-item-list-item');
                    var progressText = $container.find('.ld-lesson-list-progress').text().trim();
                    var percentage = progressText.match(/\d+/); // Extract the number
                    
                    if (percentage && percentage[0]) {
                        // Update the icon with the percentage
                        $(this).html('<span style="">' + percentage[0] + '%</span>');
                    }
                });
            }*/

            // Function to update progress percentage
            function updateProgressIcons() {
                $('.ld-status-in-progress.ld-secondary-in-progress-icon').each(function() {
                    // Find the progress percentage from the parent container
                    var $container = $(this).closest('.ld-item-list-item');
                    var progressText = $container.find('.ld-lesson-list-progress').text().trim();
                    var percentage = progressText.match(/\d+/); // Extract the number

                    if (percentage && percentage[0]) {
                        var percentVal = parseInt(percentage[0]);

                        if (percentVal === 100) {
                            // Replace with complete checkmark icon
                            $(this).replaceWith(
                                '<div class="ld-status-icon ld-status-complete">' +
                                '<span class="ld-icon-checkmark ld-icon"></span>' +
                                '</div>'
                            );
                        } else {
                            // Update the icon with the percentage
                            $(this).html('<span>' + percentVal + '%</span>');
                        }
                    }
                });
            }

            // Run on page load
            updateProgressIcons();

            // Also run when expanding/collapsing sections
            $(document).on('click', '.ld-expand-button', function() {
                setTimeout(updateProgressIcons, 300); // Small delay to allow the animation to complete
            });
        });
    </script>
    <style id="sp-ld-icon-percentage">
        /* Style for the percentage text inside progress icon */
        .ld-status-in-progress.ld-secondary-in-progress-icon {
            max-width: 20px;
            width: 20px;
            height: 20px;
            border: 2px solid #1A3A27;
            transform: translateY(0%) translateX(0%);
            position: relative;
            align-items: center;
            justify-content: center;
            /* color: #1e8cbe; */
            font-size: 9px;
            font-weight: bold;
        }

        /* Hide the default progress circle */
        .ld-status-in-progress.ld-secondary-in-progress-icon:before {
            content: none;
        }

        /* Add the circle background */
        .ld-status-in-progress.ld-secondary-in-progress-icon:after {
            max-width: 20px;
            width: 20px;
            height: 20px;
            border: 2px solid #1A3A27;
            transform: translateY(0%) translateX(0%);
        }
    </style>
<?php
});

/**
 * Show % inside LearnDash status icon end =====================================
 */





/**
 * Check if user has valid access to course content
 * @param int $user_id User ID
 * @param array $group_ids Array of group IDs to check
 * @return array Access status information
 */
function studypeak_check_user_access($user_id = null, $group_ids = [87908, 81614, 87894, 87898, 87890, 87902])
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return [
            'has_access' => false,
            'is_logged_in' => false,
            'access_type' => 'none',
            'expiry_date' => null,
            'groups' => []
        ];
    }

    $access_info = [
        'has_access' => false,
        'is_logged_in' => true,
        'access_type' => 'none',
        'expiry_date' => null,
        'groups' => [],
        'is_expired' => false
    ];

    // Check if user is in any of the target groups (87908, 81614, 87894, 87898, 87890, 87902)
    $user_groups = [];
    $target_groups = [87908, 81614, 87894, 87898, 87890, 87902]; // Only these specific groups

    foreach ($target_groups as $group_id) {
        if (function_exists('learndash_is_user_in_group') && learndash_is_user_in_group($user_id, $group_id)) {
            $user_groups[] = $group_id;
        }
    }

    $access_info['groups'] = $user_groups;

    // If user is not in target groups, they should see onboarding
    if (empty($user_groups)) {
        $access_info['access_type'] = 'no_target_groups';
        return $access_info;
    }

    // Check for valid orders with expiry dates
    $customer_orders = wc_get_orders([
        'customer_id' => $user_id,
        'status' => 'completed',
        'limit' => -1,
        'orderby' => 'date_completed',
        'order' => 'DESC',
    ]);

    $latest_expiry = null;
    $has_valid_order = false;

    foreach ($customer_orders as $order) {
        foreach ($order->get_items() as $item) {
            $expiry_meta = $item->get_meta('group_expiry_date');
            if ($expiry_meta) {
                $expiry_date = DateTime::createFromFormat('d-m-Y', $expiry_meta);
                if ($expiry_date && $expiry_date >= new DateTime()) {
                    $has_valid_order = true;
                    if (!$latest_expiry || $expiry_date > $latest_expiry) {
                        $latest_expiry = $expiry_date;
                    }
                }
            }
        }
    }

    if ($has_valid_order && $latest_expiry) {
        $access_info['has_access'] = true;
        $access_info['access_type'] = 'purchased';
        $access_info['expiry_date'] = $latest_expiry;
    } else {
        // Check if user has expired access
        $has_expired_order = false;
        $expired_date = null;

        foreach ($customer_orders as $order) {
            foreach ($order->get_items() as $item) {
                $expiry_meta = $item->get_meta('group_expiry_date');
                if ($expiry_meta) {
                    $expiry_date = DateTime::createFromFormat('d-m-Y', $expiry_meta);
                    if ($expiry_date && $expiry_date < new DateTime()) {
                        $has_expired_order = true;
                        if (!$expired_date || $expiry_date > $expired_date) {
                            $expired_date = $expiry_date;
                        }
                    }
                }
            }
        }

        if ($has_expired_order) {
            $access_info['access_type'] = 'expired';
            $access_info['is_expired'] = true;
            $access_info['expiry_date'] = $expired_date;
        } else {
            $access_info['access_type'] = 'group_only';
        }
    }

    return $access_info;
}

function add_slug_to_body_class($classes) {
    // Check if it's a single post or page
    if (is_singular()) {
        global $post;
        // Add the slug of the current page or post to the body class
        $classes[] = 'slug-' . sanitize_html_class($post->post_name);
    }

    // If this is a Course details page, add group info
    if (is_singular('sfwd-courses')) {
        $post_id = get_queried_object_id();
        // 3) Optional: LearnDash example (adds associated group IDs)
        if (function_exists('learndash_get_course_groups')) {
            $ld_group_ids = learndash_get_course_groups($post_id);
            if (!empty($ld_group_ids)) {
                foreach ($ld_group_ids as $gid) {
                    $classes[] = 'ld-group-id-' . intval($gid);
                }
            }
        }
    }

    return $classes;
}
add_filter('body_class', 'add_slug_to_body_class');

/**
 * Get user's access status for onboarding system
 * @return array User access information
 */
function studypeak_get_user_access_status()
{
    $user_id = get_current_user_id();
    $access_info = studypeak_check_user_access($user_id);

    return [
        'user_id' => $user_id,
        'is_logged_in' => $access_info['is_logged_in'],
        'has_access' => $access_info['has_access'],
        'access_type' => $access_info['access_type'],
        'is_expired' => $access_info['is_expired'],
        'expiry_date' => $access_info['expiry_date'] ? $access_info['expiry_date']->format('d-m-Y') : null,
        'groups' => $access_info['groups']
    ];
}

/**
 * Course Onboarding Animation System for StudyPeak
 */
add_action('wp_footer', function () {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const enrollBtn = document.querySelector('#brxe-free-gymi-purchase');
            if (!enrollBtn) return;

            enrollBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Show loader (if available)
                if (typeof jQuery !== 'undefined') {
                    jQuery('.site-loader').show();
                }

                // Get the currently selected parent group ID
                const selectedGroup = document.querySelector('#form-field-6f1af2');
                const groupId = selectedGroup ? selectedGroup.value : null;

                // Store triggers (for session/local usage)
                try {
                    sessionStorage.setItem('sp_onboarding_trigger_session', '1');
                    localStorage.setItem('sp_onboarding_trigger_global', '1');
                } catch (err) {
                    console.warn('Storage error:', err);
                }

                // Prepare AJAX call
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'check_user_and_assign_groups',
                            group_id: groupId || '' // Send parent group ID (e.g., 32332 or 86815)
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (typeof jQuery !== 'undefined') {
                            jQuery('.site-loader').hide();
                        }

                        if (data.logged_in) {
                            console.log('✅ Group assigned:', data.assigned_group, 'from parent:', data.parent_group);
                            window.location.href = 'https://studypeak.ch/meine-kurse/';
                        } else {
                            window.location.href = 'https://studypeak.ch/registriere-dich/';
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        if (typeof jQuery !== 'undefined') {
                            jQuery('.site-loader').hide();
                        }
                    });
            });
        });
    </script>
    <?php
});

/**
 * AJAX handler — checks login and assigns the correct LearnDash child group
 */
add_action('wp_ajax_check_user_and_assign_groups', 'studypeak_check_user_and_assign_groups');
add_action('wp_ajax_nopriv_check_user_and_assign_groups', 'studypeak_check_user_and_assign_groups');

function studypeak_check_user_and_assign_groups() {
    if (!is_user_logged_in()) {
        wp_send_json(['logged_in' => false]);
    }

    $user_id = get_current_user_id();

    // ✅ Get selected PARENT group ID from AJAX
    $parent_group_id = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;

    if (!$parent_group_id) {
        wp_send_json(['error' => 'No parent group ID provided.']);
    }

    // ✅ Define Parent → Child mapping
    $group_mapping = [
        32332 => 81614, // Langzeitgymnasium → child group
        86815 => 87908, // Kurzzeitgymnasium → child group
        86719 => 87894, // ZAP-BMS -> Child Group
        86718 => 87898, // ZAP-FMS -> Child Group
        80847 => 87890, // ZAP-IMS -> Child Group
        86716 => 87902, // ZAP-HMS -> Child Group 
    ];

    // ✅ Find the corresponding child group
    $child_group_id = isset($group_mapping[$parent_group_id]) ? $group_mapping[$parent_group_id] : 0;

    if (!$child_group_id) {
        wp_send_json(['error' => 'No child group found for this parent group.']);
    }

    // ✅ Assign only the mapped child group
    if (function_exists('learndash_is_user_in_group') && !learndash_is_user_in_group($user_id, $child_group_id)) {
        if (function_exists('ld_update_group_access')) {
            ld_update_group_access($user_id, $child_group_id);
        }
    }

    // ✅ One-time onboarding trigger (server-visible, no cookies)
    update_user_meta($user_id, 'sp_onboarding_trigger', time());

    wp_send_json([
        'logged_in' => true,
        'assigned_group' => $child_group_id,
        'parent_group'   => $parent_group_id,
    ]);
}


/**
 * AJAX: get whether user has seen onboarding for a course (DB-backed)
 */
add_action('wp_ajax_sp_get_onboarding_seen', 'sp_get_onboarding_seen');
add_action('wp_ajax_nopriv_sp_get_onboarding_seen', 'sp_get_onboarding_seen'); // guests => false

function sp_get_onboarding_seen() {
    $user_id   = get_current_user_id();
    $course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

    if (!$course_id) {
        wp_send_json_error(['message' => 'Missing course_id'], 400);
    }

    if (!$user_id) {
        wp_send_json_success(['seen' => false]);
    }

    $seen_courses = get_user_meta($user_id, 'sp_onboarding_seen_courses', true);
    if (!is_array($seen_courses)) {
        $seen_courses = [];
    }

    $seen = in_array($course_id, $seen_courses, true);
    wp_send_json_success(['seen' => $seen]);
}

/**
 * AJAX: mark onboarding as seen for a course (DB-backed)
 */
add_action('wp_ajax_sp_set_onboarding_seen', 'sp_set_onboarding_seen');

function sp_set_onboarding_seen() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in'], 401);
    }

    $user_id   = get_current_user_id();
    $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;

    if (!$course_id) {
        wp_send_json_error(['message' => 'Missing course_id'], 400);
    }

    $seen_courses = get_user_meta($user_id, 'sp_onboarding_seen_courses', true);
    if (!is_array($seen_courses)) {
        $seen_courses = [];
    }

    if (!in_array($course_id, $seen_courses, true)) {
        $seen_courses[] = $course_id;
        update_user_meta($user_id, 'sp_onboarding_seen_courses', $seen_courses);
    }

    wp_send_json_success(['seen' => true, 'courses' => $seen_courses]);
}

/**
 * Add CSS styles for blur effects and animations
 */
add_action('wp_head', function () {

    if ( current_user_can('administrator') ) { return; }

    /*$uid = get_current_user_id();
    // if ($uid !== 1265) { return; }
    $allowed_users = array(1265, 1272); // Add user IDs here

    if ( !in_array($uid, $allowed_users) ) {
        return;
    }*/

    if (!is_singular('sfwd-courses') && !is_singular('sfwd-lessons') && !is_singular('sfwd-topic') && !is_singular('sfwd-quiz')) return;
    // if (!is_singular('sfwd-courses')) return;

    $access_info = studypeak_get_user_access_status();
    $has_access  = !empty($access_info['has_access']);
    $is_expired  = !empty($access_info['is_expired']);
    $user_groups = !empty($access_info['groups']) && is_array($access_info['groups']) ? $access_info['groups'] : [];

    $target_groups = [87908, 81614, 87894, 87898, 87890, 87902];
    $group_81614_course_ids = [71335, 71313];
    $group_87908_course_ids = [88638, 85280];
    $group_87894_course_ids = [100760, 96715];
    $group_87898_course_ids = [100762, 96717];
    $group_87890_course_ids = [100764, 96721];
    $group_87902_course_ids = [100766, 96719];
    $target_course_ids = array_merge($group_81614_course_ids, $group_87908_course_ids , $group_87894_course_ids, $group_87898_course_ids, $group_87890_course_ids, $group_87902_course_ids);

    $post_id = get_the_ID();
    $course_id = 0;
    if (is_singular('sfwd-courses')) {
        $course_id = (int) $post_id;
    } else {
        if (function_exists('learndash_get_course_id')) {
            $course_id = (int) learndash_get_course_id($post_id);
        }
    }
    if ($course_id <= 0) { return; }

    $current_course_is_target = in_array($course_id, $target_course_ids, true);
    $user_in_target_groups    = (bool) array_intersect($user_groups, $target_groups);
    $is_restricted            = (!$has_access || $is_expired);

    // Mirror JS’s two cases:
    // - Showing blur-only => user has seen onboarding for this course
    // - Starting onboarding => one-time server trigger set by AJAX click
    $user_id = get_current_user_id();
    $seen_courses = $user_id ? get_user_meta($user_id, 'sp_onboarding_seen_courses', true) : [];
    if (!is_array($seen_courses)) { $seen_courses = []; }
    $has_seen_onboarding = in_array($course_id, $seen_courses, true);

    $onboarding_trigger_val = $user_id ? get_user_meta($user_id, 'sp_onboarding_trigger', true) : '';
    $has_onboarding_trigger = !empty($onboarding_trigger_val);

    $should_print_css = $current_course_is_target
        && $user_in_target_groups
        && $is_restricted
        && ( $has_seen_onboarding || $has_onboarding_trigger );

    if (!$should_print_css) { return; }

    // Make the trigger one-time (optional but recommended)
    /*if ($has_onboarding_trigger) {
        delete_user_meta($user_id, 'sp_onboarding_trigger');
    }*/
    ?>
    <style>
        /* ✅ Blur and Animation Styles */
        .ld-item-list.course-content-blur>* {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: none;
            transition: filter 0.8s ease-in-out;
            position: relative;
        }

        /* ✅ Override parent blur with !important */
        .ld-item-list.course-content-blur .ld-section-heading.course-content-unblur {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: auto !important;
            transition: filter 0.8s ease-in-out;
        }

        .ld-item-list.course-content-blur .mc-timer-toggle-container.course-content-unblur {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: auto !important;
            transition: filter 0.8s ease-in-out;
        }

        .ld-item-list.course-content-blur .go_back_to_group.course-content-unblur {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: auto !important;
            transition: filter 0.8s ease-in-out;
        }

        .ld-item-list.course-content-blur .purchase-prompt.course-content-unblur {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: auto !important;
            transition: filter 0.8s ease-in-out;
            display: flex;
            flex-direction: row;
            align-items: center;
            padding: 20px;
            gap: 30px;
            position: absolute;
            background: #1A3A27;
            box-shadow: 0px 0px 40px rgba(18, 52, 83, 0.15);
            border-radius: 30px;
        }

        .purchase-prompt.course-content-unblur p {
            text-align: left;
        }

        .purchase-prompt.course-content-unblur .purchase-button {
            min-width: max-content;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.3s ease;
            padding: 19px 30px 20px;
            gap: 10px;
            background: #1FBB65;
            border-radius: 23px;
        }

        .onboarding-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            /* width: 910px; */
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }

        .onboarding-popup.after-element {
            position: absolute;
            top: auto;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 20px;
        }

        .onboard_inner {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 221px;
            justify-content: center;
        }

        .onboard_inner h3 {
            font-weight: 700;
            font-size: 30px;
            line-height: 41px;
            letter-spacing: -0.9px;
            display: flex;
            color: #1A3A27;
            justify-content: center;
        }

        .onboarding-popup-content {
            background: #1A3A27;
            padding: 26px 30px;
            border-radius: 30px;
            min-width: 696px;
        }

        .onboarding-popup-content img {
            margin-left: 30px;
        }

        .onboarding-popup p {
            font-family: "Satoshi &#8211; Medium";
            font-weight: 500;
            font-size: 18px;
            line-height: 140% !important;
            text-align: center;
            letter-spacing: -0.02em;
            color: rgba(26, 58, 39, 0.7);
            margin: 0 0 20px 0;
            line-height: 1.5;
        }

        .onboarding-popup.active {
            display: block;
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            display: flex;
        }

        .onboarding-popup.after-element.active {
            transform: translateX(-50%) scale(1);
        }

        .onboarding-popup h3 {
            margin: 0 0 15px 0;
            font-size: 24px;
            font-weight: bold;
        }

        .onboarding-button {
            background: #1FBB65;
            border: 1px solid #1fbb65 !important;
            color: white;
            border: none;
            padding: 20.5px 29px;
            border-radius: 8px;
            font-weight: 400;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: 20px;
            display: flex;
            gap: 9px;
            align-self: center;
            justify-content: center;
            align-items: center;
            margin: 20px auto;
        }
        button.onboarding-button svg {
            width: 16px;
        }

        .onboarding-button:hover {
            background: #1A3A27;
            border: 1px solid #1fbb65 !important;
        }

        .purchase-prompt {
            background: #1A3A27;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            position: absolute;
            z-index: 10;
            max-width: 696px;
            margin: 0 auto;
            z-index: 11111111;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .purchase-prompt h4 {
            margin: 0 0 15px 0;
            font-size: 20px;
        }

        .purchase-button {
            background: #4ade80;
            color: white;
            border: none;
            padding: 19px 30px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            min-width: max-content;
            border-radius: 23px;
        }

        .purchase-button:hover {
            background: #22c55e;
        }

        .expired-access-prompt {
            background: #dc2626;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .expired-access-prompt h4 {
            margin: 0 0 15px 0;
            font-size: 20px;
        }

        .renew-button {
            background: #dc2626;
            color: white;
            border: 2px solid white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .renew-button:hover {
            background: white;
            color: #dc2626;
        }

        .onboarding-character {
            position: absolute;
            right: -100px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            animation: bounce 2s infinite;
            z-index: 10000;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(-50%);
            }

            40% {
                transform: translateY(-60%);
            }

            60% {
                transform: translateY(-55%);
            }
        }

        /* ✅ Override LearnDash progress bar during onboarding */
        .ld-progress-bar-percentage.onboarding-override {
            width: 0% !important;
            transition: width 1s ease-in-out;
        }

        .ld-progress-bar-percentage.onboarding-override.filled {
            width: 10% !important;
        }

        .ld-progress-bar-percentage.onboarding-override.removed {
            width: 0% !important;
        }

        /* ---------  blur image design  --------- */

        .onboard_inner {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 30px;
        }

        .onboard_inner p:last-child {
            margin-bottom: 0;
        }

        .up_arrow .onboarding-popup-content:before,
        .up_arrow.up_arrow_fifth .onboarding-popup-content:before {
            content: '';
            width: 26px;
            height: 21px;
            background: #1a3a27;
            position: absolute;
            top: -17px;
            transform: rotate(-45deg) translateX(-50%);
            left: 40%;
        }

        .onboarding-popup {
            width: 950px;
        }

        .up_arrow .onboarding-popup {
            top: 484px;
            position: absolute;
        }

        .up_arrow .onboarding-override {
            background: #1FBB65;
        }

        .up_arrow_fourth .onboarding-popup {
            flex-direction: row-reverse;
            gap: 10px;
        }

        .up_arrow_fourth .onboarding-popup-content:before {
            content: '';
            width: 26px;
            height: 21px;
            background: #1a3a27;
            position: absolute;
            top: 50%;
            transform: rotate(-45deg) translateX(-50%);
            left: 99%;
        }

        .onboarding-popup.active {
            gap: 40px;
            align-items: center;
        }

        .up_arrow_fourth .onboarding-popup.active {
            margin-left: -100px;

        }

        .course-content-unblur .go_back_to_group a {
            filter: blur(10px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth .onboarding-popup {
            flex-direction: row;
        }

        .up_arrow_fifth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>* {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>* {
            filter: blur(10px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>* {
            filter: blur(10px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item .course-content-unblur {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item.free-content-section>* {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item.free-content-section>* {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .course-content-unblur .go_back_to_group a,
        .up_arrow_fifth .course-content-unblur .go_back_to_group a {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section:nth-child(1) .ld-item-list-item>*:nth-child(-n+4) {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth .purchase-prompt {
            display: none !important;
        }

        .up_arrow_sixth .purchase-prompt {
            display: flex !important;
            top: 1px;
            height: max-content;
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>* {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item.free-content-section>* {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>* {
            filter: blur(8px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth.up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section:nth-child(1) .ld-item-list-item>*:nth-child(-n+4) {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item.free-content-section>* {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>.course-content-unblur {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_fifth .single-sfwd-lessons .learndash-wrapper .ld-table-list,
        .up_arrow_fifth .learndash-wrapper .course-listing-section {
            overflow: visible !important;
        }

        .ld-item-list-section-heading {
            border-radius: 20px 20px 0 0;
            overflow: hidden;
        }

        .course-listing-section .ld-item-list-item:last-child {
            border-radius: 0 0 20px 20px;
        }

        .up_arrow_fifth .ld-item-list.course-content-blur>* {
            pointer-events: visible;
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .course-listing-section {
            z-index: -1;
        }

        .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item:nth-child(2) {
            filter: none !important;
        }

        .ld-item-list.course-content-blur>* {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .course-listing-section .ld-item-list-item.free-content-section {
            filter: blur(0px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: visible;
        }

        .course-listing-section .ld-item-list-item {
            filter: blur(8px);
            will-change: filter; /* or transform */
            transform: translateZ(0);
            pointer-events: none;
        }

        .up_arrow_fifth .onboarding-popup.active {
            top: auto;
        }

        .up_arrow_sixth .course-listing-section div {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .course-listing-section div>* {
            filter: blur(8px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .course-listing-section div>.purchase-prompt {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .course-listing-section div.free-content-section>* {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur .purchase-prompt.course-content-unblur>* {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }

        .up_arrow_sixth .ld-item-list.course-content-blur .purchase-prompt.course-content-unblur {
            transform: translate(0px);
            position: static;
        }
        .up_arrow_sixth .ld-item-list-item .ld-item-list-item-preview > * {
                filter: blur(8px) !important;
                will-change: filter; /* or transform */
                transform: translateZ(0);
            }
        .up_arrow_sixth .ld-item-list-item.free-content-section .ld-item-list-item-preview > * {
                filter: blur(0px) !important;
                will-change: filter; /* or transform */
                transform: translateZ(0);
            }
        .up_arrow_sixth .course-listing-section div.free-content-section span {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }
        .up_arrow_sixth .course-listing-section div.ld-item-list-section-heading > div {
            filter: blur(0px) !important;
            will-change: filter; /* or transform */
            transform: translateZ(0);
        }
        @media only screen and(max-width : 1180px) {
            .onboarding-popup {
                width: 100%;
            }
            .onboarding-popup-content{
                width: 70%;
            }
            .up_arrow_fourth .onboarding-popup-content:before {
                content: '';
                width: 16px;
                height: 21px;
                background: #1a3a27;
                position: absolute;
                top: -14px;
                transform: rotate(-45deg) translateX(-50%);
                left: 50%;
            }
            .up_arrow_fourth .onboarding-popup.active{
                top: 714px;
            }
        }

        @media only screen and (max-width:767px) {
            .page-id-121898 .brxe-section #brxe-spaxfs.brxe-container .brxe-image {
                right: 0;
                padding-left: 0;
            }

            .page-id-121898 #brxe-spaxfs {
                right: 0;
            }

            .page-id-121898 .brxe-section #brxe-dfpdpx {
                display: block;
            }

            .page-id-121898 .brxe-section .section-title.brxe-heading {
                text-align: center;
            }

            .onboarding-popup-content {
                padding: 15px;
                min-width: 100% !important;
            }

            .onboarding-popup {
                width: calc(100% - 15px) !important;
                top: 310px !important;
                transform: translate(-50%, 0%) !important;
                flex-direction: column;
                position: absolute !important;
            }

            .onboard_inner {
                height: auto;
            }

            .onboarding-popup-image {
                max-width: 50%;
                margin-top: 50px;
            }

            .up_arrow_fourth .onboarding-popup-content:before {
                content: '';
                width: 16px;
                height: 21px;
                background: #1a3a27;
                position: absolute;
                top: -14px;
                transform: rotate(-45deg) translateX(-50%);
                left: 50%;
            }

            .up_arrow_sixth .ld-item-list.course-content-blur>.ld-lesson-progression:nth-child(6) .course-listing-section .ld-item-list-item>.course-content-unblur {
                width: 100%;
            }

            .ld-item-list.course-content-blur .purchase-prompt.course-content-unblur {
                padding: 8px;
            }

            .up_arrow_fourth .onboarding-popup.active {
                margin-left: 0;
                top: 623px !important;
            }

            .up_arrow_fifth .onboarding-popup.active {
                margin-left: 0;
                top: auto !important;
            }

            .purchase-button {
                padding: 9px 20px !important;
                font-size: 13px;
            }

            .purchase-prompt.course-content-unblur p {
                font-size: 13px;
                text-align: center !important;
            }

            .up_arrow_sixth .purchase-prompt {
                display: block !important;
            }

            .onboarding-popup.active {
                gap: 00px;
                flex-direction: column;
            }
            .up_arrow .onboarding-popup-content:before, .up_arrow.up_arrow_fifth .onboarding-popup-content:before{
                width: 19px;
            }
        }


        /* Blur all lesson items by default */
        .single-sfwd-lessons .ld-table-list-item {
            filter: blur(8px);
            will-change: filter;
            transform: translateZ(0);
            pointer-events: none;
        }

        /* Exclude free-content items from blur */
        .single-sfwd-lessons .ld-table-list-item.free-content-section.course-content-unblur {
            filter: none !important;
            pointer-events: auto !important;
        }

        /* Apply blur to both the Previous and Next buttons */
        .ld-content-action .ld-button,
        .mc-next-step .ld-button,
        .wpProQuiz_results .ld-quiz-actions > div:nth-child(5) a.ld-button {
            filter: blur(8px);
            will-change: filter;
            transform: translateZ(0);
            pointer-events: none;
        }
        .single-sfwd-courses.up_arrow_sixth .learndash-wrapper .ld-status-icon .ld-icon:before{
            top: -7px;
        }
    </style>
    <?php
});

/**
 * Get the list of free URLs (auto-adapts to current Weglot language)
 *
 * @return array
 */
function studypeak_get_free_urls() {
    // ✅ Detect current and original (default) language
    $current_lang  = function_exists('weglot_get_current_language') ? weglot_get_current_language() : 'de';
    $original_lang = function_exists('weglot_get_original_language') ? weglot_get_original_language() : 'de';

    // ✅ Build proper base URL (remove trailing slashes to avoid //)
    $base_url = untrailingslashit(site_url());
    $base_url = ($current_lang !== $original_lang)
        ? "{$base_url}/{$current_lang}/"
        : "{$base_url}/";

    // ✅ Define your list of relative paths
    $paths = [
        'lessons/einfuehrung-kurs-langzeitgymnasium/',
        'topics/satzarten/',
        'quizzes/satzarten-quiz/',
        'topics/suffixe/',
        'topics/arten-von-suffixen/',
        'quizzes/suffixe-quiz-1/',
        'topics/gross-und-kleinschreibung-1/',
        'topics/gross-und-kleinschreibung-2/',
        'topics/kurze-vokale/',
        'quizzes/kurze-vokale-quiz/',
        'topics/synonyme/',
        'quizzes/synonyme-quiz/',
        'topics/antonyme/',
        'quizzes/antonyme-quiz/',
        'topics/homonyme/',
        'quizzes/homonyme-quiz/',
        'quizzes/redewendungen-quiz-1/',
        'topics/textverstaendnis/',
        'topics/einfuehrung-12/',
        'quizzes/einfuehrung-quiz-2/',
        'topics/einfuehrung-13/',
        'topics/gymipruefung/',
        'topics/ziel-der-lektion/',
        'lessons/die-verschiedenen-textsorten/',
        'quizzes/stilmittel-quiz/',
        'quizzes/die-intention-der-autorin-oder-des-autors-verstehen-quiz/',

        'lessons/einfuehrung-kurs-langzeitgymnasium-2/',
        'lessons/schriftliche-addition/',
        'topics/bruchrechnen-theorie/',
        'lessons/indirekte-proportionalitaet/',
        'lessons/weg-zeit-und-tempo/',
        'quizzes/raetselaufgaben-quiz/',
        'lessons/vierecke-und-ihre-eigenschaften/',
        'topics/perspektiven-theorie/',

        'topics/einfuehrung-31/',
        'topics/warum-sind-ober-und-unterbegriffe-wichtig/',
        'quizzes/ober-und-unterbegriffe-quiz/',
        'topics/wortfamilien-2/',
        'topics/metaphern-und-vergleiche-2/',
        'topics/hauptsaetze-und-nebensaetze/',
        'quizzes/satzlehre-quiz/',
        'topics/praepositionalgefuege/',
        'topics/die-verschiebeprobe/',
        'topics/einleitung-3/',
        'topics/hauptteil-2/',
        'topics/schluss-2/',
        'topics/fazit-40/',
        'topics/analyse-von-beispielaufsaetzen/',
        'topics/aufsaetze-noch-weiter-verbessern-arten-zum-verbessern/',
        
        'lessons/einfuehrung-und-kursuebersicht/',
        'topics/was-ist-eine-wurzel/',
        'topics/anwendungen-in-der-problemloesung/',
        'quizzes/potenzen-und-wurzeln-exponenten-und-wurzeln-erkunden-quiz/',
        'lessons/ueberblick-variablen-und-algebra-2/',
        'quizzes/loesen-von-gleichungen-mit-natuerlichen-zahlen-quiz/',
        'topics/was-ist-eine-primzahl/',
        'topics/primfaktorzerlegung/',
        'quizzes/primfaktorzerlegung-quiz/',
        'quizzes/probepruefung-2-2/',
        'topics/gaengige-masseinheiten/',
        'quizzes/terme-und-gleichungen-quiz-1/',
        'quizzes/der-satz-des-pythagoras-quiz-1/',
        'topics/der-satz-des-pythagoras-erforschung-rechtwinkliger-dreiecke-i/',
        'quizzes/probepruefung-10/',
        'topics/ueberblick/',

        'topics/aufmerksames-lesen-4/',
        'quizzes/aufmerksames-lesen-quiz-4/',
        'topics/warum-ist-das-markieren-und-notizen-machen-wichtig-3/',
        'topics/was-soll-markiert-werden-3/',
        'topics/wortfelder-2/',
        'quizzes/antonyme-quiz-3/',
        'topics/wortarten-3/',
        'topics/fazit-78/',
        'topics/einfuehrung-143/',
        'quizzes/satzarten-quiz-3/',
        'topics/das-schreiben-3/',
        'topics/hauptteil-4/',
        'topics/beispiele-fuer-gute-saetze-hauptteil-2-2/',
        'topics/beispiele-fuer-gute-saetze-uebergangssatz-2/',

        'topics/sachtexte-4/',
        'quizzes/einfuehrung-in-das-thema-textarten-quiz-1-5/',
        'topics/wortfeld-sport-3/',
        'topics/einfuehrung-synonyme-antonyme-und-homonyme-3/',
        'topics/nomen-bestimmen-3/',
        'quizzes/nomen-bestimmen-quiz-3/',
        'topics/satzfragmente-3/',
        'quizzes/saetze-quiz-3/',
        'topics/wie-ist-ein-absatz-aufgebaut-4/',
        'topics/was-ist-eine-hauptidee-6/',
        'topics/verbessern-durch-feedback-2-8/',
        'topics/fazit-128/',

        'topics/vorhersagen-5/',
        'quizzes/vorhersagen-und-fragen-stellen-quiz-6/',
        'topics/fazit-76/',
        'quizzes/ober-und-unterbegriffe-quiz-4/',
        'topics/praesens-4/',
        'topics/praeteritum-4/',
        'topics/bedeutung-und-funktion-5/',
        'topics/punkt-fragezeichen-und-ausrufezeichen-5/',
        'quizzes/satzzeichen-quiz-9/',
        'topics/analyse-der-aufgabenstellung-5/',
        'topics/interesse-und-motivation-5/',
        'topics/textentwicklung-uebersicht-4/',
        'topics/ideen-entwickeln-techniken-des-brainstormings-5/',

        'topics/einfuehrung-84/',
        'topics/chronologische-struktur-6/',
        'topics/metaphern-und-vergleiche-6/',
        'quizzes/metaphern-und-vergleiche-quiz-1-5/',
        'topics/die-grundform-der-positiv-5/',
        'topics/verschiedene-arten-von-deklinieren-5/',
        'topics/abtrennung-von-nebensaetzen-5/',
        'topics/trennung-von-aufzaehlungen-5/',
        'topics/was-sind-stilmittel-und-rhetorische-figuren-6/',
        'quizzes/stilmittel-quiz-6/',
        'topics/einfuehrung-mit-plan-und-struktur-zur-pruefung-6/',
        'topics/wie-erstelle-ich-einen-lernplan-5/',


        'topics/kommutativgesetz-2/',
        'quizzes/die-wichtigsten-grundregeln-der-mathematik-quiz-2/',
        'topics/variablen-und-konstanten-2/',
        'topics/begriffe-und-gleichungen-2/',
        'topics/strategie-zum-loesen-von-textaufgaben-2/',
        'topics/beispiel-1-2/',
        'topics/uebungsaufgaben-19/',
        'quizzes/primfaktorzerlegung-quiz-2/',
        'topics/wie-wird-die-wahrscheinlichkeit-berechnet-6/',
        'topics/wahrscheinlichkeiten-vergleichen-6/',
        'topics/proportionale-beziehungen-2/',
        'topics/uebungsaufgaben-43/',
        'topics/fuellgraphen-2/',
        'topics/wichtige-begriffe-2/',
        'topics/punktsymmetrie-2/',
        'topics/praktische-anwendung-2/',

        'topics/wie-addiert-und-subtrahiert-man-brueche-3/',
        'topics/wie-multipliziert-man-brueche-3/',
        'topics/multiplizieren-und-dividieren-algebraischer-ausdruecke-3/',
        'topics/arbeiten-mit-potenzen-in-begriffen-3/',
        'topics/groesster-gemeinsamer-teiler-ggt-den-groessten-gemeinsamen-faktor-finden-theorie-2-3/',
        'quizzes/groesster-gemeinsamer-teiler-ggt-quiz-3/',
        'topics/punkte-einzeichnen-7/',
        'topics/koordinaten-lesen-7/',
        'topics/volumen-von-koerpern-raum-messen-ii-3/',
        'quizzes/volumen-von-koerpern-quiz-3/',
        'topics/winkelnregeln-in-dreiecken-besondere-winkelregelungen-3/',
        'topics/winkelregeln-in-dreiecken-aussenwinkel-3/',
        'quizzes/winkelregeln-in-dreiecken-die-grundlagen-verstehen-quiz-3/',

        'topics/brueche-zusammenfassung-5/',
        'topics/zusammenfassung-der-potenzen-5/',
        'topics/einfache-gleichungen-loesen-5/',
        'topics/isolieren-der-variable-5/',
        'topics/uebungsaufgaben-14/',
        'quizzes/terme-und-gleichungen-quiz-1-5/',
        'topics/wichtige-umrechnungen-5/',
        'quizzes/probepruefung-2-5/',
        'quizzes/wahrscheinlichkeitsgleichungen-loesen-quiz-5/',
        'topics/zusammenfassung-theorie-7/',
        'topics/wichtige-begriffe-5/',
        'topics/wie-berechnet-man-verhaeltnisse-5/',
        'topics/verhaeltnisse-in-prozenten-verwenden-5/',

        'topics/was-ist-eine-wurzel-5/',
        'quizzes/potenzen-und-wurzeln-exponenten-und-wurzeln-erkunden-quiz-4/',
        'topics/wichtige-erkenntnisse-4/',
        'quizzes/gleichungen-mit-bruechen-loesen-quiz-4/',
        'topics/wie-berechnet-man-das-kgv-4/',
        'quizzes/kleinstes-gemeinsames-vielfaches-kgv-zahlen-synchronisieren-quiz-4/',
        'topics/warum-ist-dieses-konzept-wichtig-4/',
        'topics/checkliste-zur-diagramminterpretation-4/',
        'topics/beispiel-klimadiagramm-4/',
        'topics/der-satz-des-pythagoras-erforschung-rechtwinkliger-dreiecke-ii-4/',
        'quizzes/der-satz-des-pythagoras-quiz-1-4/',
        'topics/strecken-4/',
        'topics/praktisches-beispiel-9/',
    ];

    // ✅ Prepend base URL to all paths
    return array_map(fn($path) => $base_url . ltrim($path, '/'), $paths);
}


/**
 * Script for course onboarding animation with user access control
 */
add_action('wp_footer', function () {

    if ( current_user_can('administrator') ) { return; }

    /*$uid = get_current_user_id();
    // if ($uid !== 1265) { return; }
    $allowed_users = array(1265, 1272); // Add user IDs here

    if ( !in_array($uid, $allowed_users) ) {
        return;
    }*/

    // if (!is_singular('sfwd-courses') && !is_singular('sfwd-lessons') && !is_singular('sfwd-topic')) return;
    if (!is_singular('sfwd-courses') && !is_singular('sfwd-lessons')) return;
    // if (!is_singular('sfwd-courses')) return;

    // Get user access information
    $access_info = studypeak_get_user_access_status();
    $access_info_js = json_encode($access_info);

    // Map group -> course IDs and detect if current course is target
    $group_81614_course_ids = [71335, 71313];
    $group_87908_course_ids = [88638, 85280];
    $group_87894_course_ids = [100760, 96715];
    $group_87898_course_ids = [100762, 96717];
    $group_87890_course_ids = [100764, 96721];
    $group_87902_course_ids = [100766, 96719];
    $target_course_ids = array_merge($group_81614_course_ids, $group_87908_course_ids , $group_87894_course_ids, $group_87898_course_ids, $group_87890_course_ids, $group_87902_course_ids);

    // Determine course id safely across contexts
    $post_id = get_the_ID();
    $current_course_id = 0;
    if (is_singular('sfwd-courses')) {
        $current_course_id = (int) $post_id;
    } else {
        if (function_exists('learndash_get_course_id')) {
            $current_course_id = (int) learndash_get_course_id($post_id);
        }
    }

    $current_course_is_target = in_array($current_course_id, $target_course_ids, true);

    // Pass target course ids to JS for "all seen" check
    $target_course_ids_js = json_encode($target_course_ids);

    // ✅ Example usage
    $free_urls = studypeak_get_free_urls();
    // ✅ Encode for JS
    $free_urls_js = json_encode($free_urls);
    $admin_ajax = admin_url('admin-ajax.php');

    ?>
    <script>
        // Disable text selection for the entire page
        // document.addEventListener('selectstart', (e) => e.preventDefault());        
        document.addEventListener('DOMContentLoaded', function() {
            const accessInfo = <?php echo $access_info_js; ?>;
            const freeUrls = <?php echo $free_urls_js; ?>;
            const targetCourseIds = <?php echo $target_course_ids_js; ?>;
            const currentCourseIsTarget = <?php echo $current_course_is_target ? 'true' : 'false'; ?>;
            const adminAjax = '<?php echo esc_js($admin_ajax); ?>';

            let currentStep = 1;
            let onboardingActive = false;

            // Performance helpers
            const raf = window.requestAnimationFrame || (cb => setTimeout(cb, 16));
            const freeUrlSet = new Set(freeUrls);
            const courseListEl = document.querySelector('.ld-item-list') || document;

            // Determine if onboarding should be shown
            const targetGroups = [87908, 81614, 87894, 87898, 87890, 87902];
            const userInTargetGroups = accessInfo.groups.some(group => targetGroups.includes(group));
            const isRestricted = (!accessInfo.has_access || accessInfo.is_expired);

            // Per-user-per-course seen state from DB (via AJAX)
            const userId = accessInfo.user_id || 0;
            const courseId = <?php echo (int) $current_course_id; ?>;

            // Triggers: session OR global
            let hasSessionTrigger = false, hasGlobalTrigger = false;
            try {
                hasSessionTrigger = sessionStorage.getItem('sp_onboarding_trigger_session') === '1';
                hasGlobalTrigger = localStorage.getItem('sp_onboarding_trigger_global') === '1';
            } catch (e) {}

            // -------- PERF: cache for first locked item and prompt --------
            const spPerfState = {
                firstLockedTopParent: null,
                promptEl: null,
                lookedUpOnce: false
            };

            function findFirstLockedItem() {
                if (spPerfState.lookedUpOnce) return spPerfState.firstLockedTopParent;
                spPerfState.lookedUpOnce = true;

                // Only consider top-level items
                const allItems = (courseListEl || document).querySelectorAll('.ld-item-list-item');

                for (const item of allItems) {
                    // Skip if marked free
                    if (item.classList.contains('free-content-section') || item.closest('.free-content-section')) continue;

                    // Check if any child links are free
                    const links = item.querySelectorAll('a[href]');
                    let isLocked = true;
                    for (const link of links) {
                        const href = link.href;
                        if (href && freeUrlSet.has(href)) {
                            isLocked = false;
                            break;
                        }
                    }

                    if (isLocked) {
                        spPerfState.firstLockedTopParent = item;
                        break;
                    }
                }

                return spPerfState.firstLockedTopParent;
            }

            function ensureSinglePurchasePrompt() {
                if (spPerfState.promptEl && spPerfState.promptEl.isConnected) return spPerfState.promptEl;

                const target = findFirstLockedItem();
                if (!target) return null;

                // Extra safety
                if (target.closest('.free-content-section')) return null;

                const prompt = document.createElement('div');
                prompt.className = 'purchase-prompt course-content-unblur sp-prompt-inserted';
                prompt.innerHTML = `
                    <p>Dieser Inhalt ist ausschliesslich für eingeschriebene Schüler und Schülerinnen. 
                    Um vollen Zugriff auf alle Bereiche zu erhalten, muss der Kurs käuflich erworben werden.</p>
                    <button class="purchase-button" onclick="purchaseCourse()">Jetzt kaufen!</button>
                `;

                // ✅ Append at the end of ld-item-list-item
                target.appendChild(prompt);
                spPerfState.promptEl = prompt;
                return prompt;
            }



            // lightweight wrapper: no removals, no polling, no global observer
            function addPurchasePromptToFirstLockedLesson(immediate = false) {
                ensureSinglePurchasePrompt();
            }
            // --------------------------------------------------------------

            init();

            async function init() {
                // Fetch seen state from DB
                let hasSeenOnboarding = false;
                try {
                    const res = await fetch(`${adminAjax}?action=sp_get_onboarding_seen&course_id=${courseId}`, { credentials: 'same-origin' });
                    const json = await res.json();
                    if (json && json.success && json.data) {
                        hasSeenOnboarding = !!json.data.seen;
                    }
                } catch (e) {}

                const shouldShowOnboarding = currentCourseIsTarget && userInTargetGroups && isRestricted && !hasSeenOnboarding && (hasSessionTrigger || hasGlobalTrigger);
                const shouldShowBlurOnly = currentCourseIsTarget && userInTargetGroups && isRestricted && hasSeenOnboarding;

                console.log('StudyPeak Debug - Access Info:', accessInfo);
                console.log('StudyPeak Debug - Should show onboarding:', shouldShowOnboarding);
                console.log('StudyPeak Debug - User logged in:', accessInfo.is_logged_in);
                console.log('StudyPeak Debug - Has access:', accessInfo.has_access);
                console.log('StudyPeak Debug - Is expired:', accessInfo.is_expired);

                if (shouldShowOnboarding) {
                    console.log('StudyPeak Debug - Starting onboarding...');
                    onboardingActive = true;
                    raf(() => {
                        createOnboardingElements();
                        markFreeContent();
                        // prompt once, up-front
                        // addPurchasePromptToFirstLockedLesson(true);
                        showStep1();
                        handleLessonFreeItems();
                        // Disable text selection
                        document.addEventListener('selectstart', function(e) {
                            e.preventDefault();
                        });

                    });
                } else if (shouldShowBlurOnly) {
                    console.log('StudyPeak Debug - Showing blur-only (tour already seen)');
                    raf(() => {
                        markFreeContent();
                        blurItemList();
                        addPurchasePromptToFirstLockedLesson(true); // prompt once
                        // Apply final state classes and unblurs
                        applyFinalStateClasses();
                        handleLessonFreeItems();
                        // Disable text selection
                        document.addEventListener('selectstart', function(e) {
                            e.preventDefault();
                        });
                    });
                } else {
                    console.log('StudyPeak Debug - User has valid access or no trigger, skipping onboarding');
                    raf(markFreeContent);
                }
            }

            function handleLessonFreeItems() {
                // Only run on lesson detail pages
                if (!document.body.classList.contains('single-sfwd-lessons')) return;
                                
                const allItems = document.querySelectorAll('.ld-table-list-item');
                const currentUrl = window.location.href;

                allItems.forEach(item => {
                    const link = item.querySelector('a[href]');

                    // If the current page itself is free, unblur all items
                    if (freeUrlSet.has(currentUrl)) {
                        item.classList.add('course-content-unblur', 'free-content-section');
                        return;
                    }

                    // Otherwise, unblur individual items that are free
                    if (link && freeUrlSet.has(link.href)) {
                        item.classList.add('course-content-unblur', 'free-content-section');
                    }
                });
            }

            // Call after DOM loads
            document.addEventListener('DOMContentLoaded', () => {
                handleLessonFreeItems();
            });

            function applyFinalStateClasses() {
                document.body.classList.add('up_arrow');
                document.body.classList.add('up_arrow_third');
                document.body.classList.add('up_arrow_fourth');
                document.body.classList.add('up_arrow_fifth');
                document.body.classList.add('up_arrow_sixth');

                const sectionHeading = document.querySelector('.ld-section-heading');
                if (sectionHeading) sectionHeading.classList.add('course-content-unblur');

                const timerToggle = document.querySelector('.mc-timer-toggle-container');
                if (timerToggle) timerToggle.classList.add('course-content-unblur');

                const backToGroup = document.querySelector('.go_back_to_group');
                if (backToGroup) backToGroup.classList.add('course-content-unblur');

                const firstPrompt = document.querySelector('.purchase-prompt');
                if (firstPrompt) firstPrompt.classList.add('course-content-unblur');

                const listScope = document.querySelector('.ld-item-list') || document;
                listScope.querySelectorAll('.ld-item-list-item').forEach(item => {
                    const link = item.querySelector('a[href]');
                    if (link && freeUrlSet.has(link.href)) {
                        item.classList.add('free-content-section');
                        item.classList.add('course-content-unblur');
                    }
                });

                listScope.querySelectorAll('.ld-item-list-section-heading').forEach(h => {
                    h.classList.add('course-content-unblur');
                });
            }

            // Create overlay + popup container
            function createOnboardingElements() {
                const overlay = document.createElement('div');
                overlay.className = 'onboarding-overlay';
                overlay.id = 'onboarding-overlay';
                document.body.appendChild(overlay);

                const popupContainer = document.createElement('div');
                popupContainer.id = 'onboarding-popup-container';
                document.body.appendChild(popupContainer);
            }

            // Mark free content sections
            function markFreeContent() {
                const allLinks = courseListEl.querySelectorAll('a[href]');
                const elementsToMark = [];
                allLinks.forEach(link => {
                    if (freeUrlSet.has(link.href)) {
                        const parentItem = link.closest('.ld-item-list-item');
                        if (parentItem) elementsToMark.push(parentItem);
                    }
                });
                if (elementsToMark.length) {
                    raf(() => {
                        elementsToMark.forEach(parentItem => parentItem.classList.add('free-content-section'));
                    });
                }
            }

            function containerPrompt() {
                const $containerPrompt = window.jQuery ? jQuery('.onboarding-popup.active .onboarding-popup-content') : null;
                if ($containerPrompt && $containerPrompt.length) {
                    jQuery('html, body').animate({
                        scrollTop: $containerPrompt.offset().top - 150
                    }, 600);
                }
            }

            // Steps
            function showStep1() {
                blurItemList();
                // prompt already inserted before step 1 via init()
                showPopup(`
                  <div class="onboard_inner">
                    <h3>Willkommen bei studypeak!</h3>
                    <p>Ich zeige dir heute, wie der Trainingsbereich in unseren Kursen aussieht. Danach wünsche ich dir viel Spass beim Ausprobieren.</p>
                    <p>Also starten wir unsere Tour!</p>
                  </div>
                  <button class="onboarding-button" onclick="nextStep()">Weiter 
                    <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.24251 6.60742H17.5967M17.5967 6.60742L12.6904 1.70117M17.5967 6.60742L12.6904 11.5137" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>
                `);
            }

            function showStep2() {
                unblurProgressSection();
                showPopup(`
                 <div class="onboard_inner">
                    <p>Hier siehst du den Kursfortschritt. Wenn du Aufgaben löst und Theorieteile absolvierst, dann steigt dein Fortschritt. Hast du den Kurs komplett fertig gelöst, wird der Kurs als 100 % vollständig angezeigt.</p>
                 </div>
                 <button class="onboarding-button" onclick="nextStep()">Weiter 
                    <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.24251 6.60742H17.5967M17.5967 6.60742L12.6904 1.70117M17.5967 6.60742L12.6904 11.5137" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                 </button>
                `);

                // Scroll AFTER popup has been inserted
                setTimeout(containerPrompt, 300);
            }

            function showStep3() {
                fillProgressBar(10);
                showPopup(`
                 <div class="onboard_inner">
                    <p>Hier siehst du den Kursfortschritt. Wenn du Aufgaben löst und Theorieteile absolvierst, dann steigt dein Fortschritt. Hast du den Kurs komplett fertig gelöst, wird der Kurs als 100 % vollständig angezeigt.</p>
                 </div>
                 <button class="onboarding-button" onclick="nextStep()">Weiter 
                    <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.24251 6.60742H17.5967M17.5967 6.60742L12.6904 1.70117M17.5967 6.60742L12.6904 11.5137" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                 </button>
                `);

                // Scroll AFTER popup has been inserted
                setTimeout(containerPrompt, 300);
            }

            function showStep4() {
                removeProgressFill();
                unblurTimerToggle();
                showPopup(`
                 <div class="onboard_inner">
                    <p>Hier siehst du unseren Timer. Du kannst entscheiden, ob du die Quizze mit dem Timer, also einer zeitlichen Limitierung, machen möchtest oder diese für den ganzen Trainingsbereich abschalten möchtest.</p>
                 </div>
                 <button class="onboarding-button" onclick="nextStep()">Weiter 
                    <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.24251 6.60742H17.5967M17.5967 6.60742L12.6904 1.70117M17.5967 6.60742L12.6904 11.5137" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                 </button>
                `);

                // Scroll AFTER popup has been inserted
                setTimeout(containerPrompt, 300);
            }

            function showStep5() {
                unblurBackToGroup();
                unblurFreeCourses();

                let popupContent = `
                 <div class="onboard_inner">
                    <p>Hier siehst du unsere einzelnen Lektionen und Themen im Kurs. Die Kurse sind nach Themen gegliedert und haben einzelne Lektionen und Quizze, die du absolvieren kannst.</p>
                    <p>Ich will dich gar nicht lange mehr aufhalten. Viel Spass beim Austesten!</p>
                 </div>
                 <button class="onboarding-button" onclick="document.body.classList.add('up_arrow_sixth'); completeOnboarding()">Weiter 
                    <svg width="19" height="13" viewBox="0 0 19 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.24251 6.60742H17.5967M17.5967 6.60742L12.6904 1.70117M17.5967 6.60742L12.6904 11.5137" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                 </button>
                `;

                const firstFreeSection = document.querySelector('.free-content-section');
                if (firstFreeSection) {

                    // Do NOT re-insert the prompt; just scroll to existing one if present
                    // const prompt = spPerfState.promptEl || document.querySelector('.onboarding-popup.active .onboarding-popup-content');
                    const $target = window.jQuery ? jQuery(firstFreeSection) : null;

                    // ✅ Always scroll directly to the textfield so the user doesn’t have to scroll manually
                    if (firstFreeSection && $target && $target.length) {
                        jQuery('html, body').animate(
                            { scrollTop: $target.offset().top - 75 },
                            600
                        );
                    } else if (firstFreeSection) {
                        try { 
                            firstFreeSection.scrollIntoView({ behavior: 'smooth', block: 'center' }); 
                        } catch (e) {}
                    }
                    showPopupAfterElement(firstFreeSection, popupContent);
                } else {
                    showPopup(popupContent);
                }
            }

            function nextStep() {
                currentStep++;
                switch (currentStep) {
                    case 1:
                        showStep1();
                        break;
                    case 2:
                        showStep2();
                        document.body.classList.add("up_arrow");
                        break;
                    case 3:
                        showStep3();
                        document.body.classList.add("up_arrow_third");
                        break;
                    case 4:
                        showStep4();
                        document.body.classList.add("up_arrow_fourth");
                        break;
                    case 5:
                        showStep5();
                        document.body.classList.add("up_arrow_fifth");
                        break;
                    default:
                        completeOnboarding();
                        document.body.classList.add("up_arrow_sixth");
                }
            }

            function showExpiredPrompt() {
                showPopup(`
                    <h3>Dein Zugang ist abgelaufen</h3>
                    <p>Dein Zugang zu den Kursen ist am ${accessInfo.expiry_date} abgelaufen. Um wieder vollen Zugriff auf alle Bereiche zu erhalten, musst du den Kurs erneut erwerben.</p>
                    <button class="onboarding-button" onclick="renewAccess()">Zugang verlängern!</button>
                `);
            }

            function showPurchasePrompt() {
                showPopup(`
                    <h3>Premium Inhalt</h3>
                    <p>Dieser Inhalt ist ausschliesslich für eingeschriebene Schüler und Schülerinnen. Um vollen Zugriff auf alle Bereiche zu erhalten, muss der Kurs käuflich erworben werden.</p>
                    <button class="onboarding-button" onclick="purchaseCourse()">Jetzt kaufen!</button>
                `);
            }

            // Utility functions
            function blurItemList() {
                const itemList = document.querySelector('.ld-item-list');
                if (itemList) {
                    raf(() => itemList.classList.add('course-content-blur'));
                }
            }

            function unblurProgressSection() {
                const progressSection = document.querySelector('.ld-section-heading .ld-progress');
                if (progressSection) {
                    const sectionHeading = progressSection.closest('.ld-section-heading');
                    if (sectionHeading) {
                        sectionHeading.classList.add('course-content-unblur');
                    }
                }
            }

            function fillProgressBar(percentage) {
                const progressBar = document.querySelector('.learndash-shortcode-wrap .learndash .ld-progress-bar-percentage');
                const progressText = document.querySelector('.learndash-shortcode-wrap .learndash .ld-progress-percentage');
                if (progressBar && progressText) {
                    progressBar.classList.add('onboarding-override', 'filled');
                    setTimeout(() => {
                        progressBar.style.width = percentage + '%';
                    }, 50);
                    progressText.textContent = percentage + '% Vollständig';
                }
            }

            function removeProgressFill() {
                const progressBar = document.querySelector('.learndash-shortcode-wrap .learndash .ld-progress-bar-percentage');
                const progressText = document.querySelector('.learndash-shortcode-wrap .learndash .ld-progress-percentage');

                if (progressBar) {
                    progressBar.classList.remove('filled');
                    progressBar.classList.add('removed');
                    progressBar.style.width = '0%';
                }

                if (progressText) {
                    progressText.textContent = '0% Vollständig';
                }
            }

            function unblurTimerToggle() {
                const timerToggle = document.querySelector('.mc-timer-toggle-container');
                const sectionHeadings = document.querySelectorAll('.ld-section-heading');
                const secondSectionHeading = sectionHeadings[1];

                if (timerToggle) {
                    timerToggle.classList.add('course-content-unblur');
                }

                if (secondSectionHeading) {
                    secondSectionHeading.classList.add('course-content-unblur');
                }
            }

            function unblurBackToGroup() {
                const backToGroup = document.querySelector('.go_back_to_group');
                if (backToGroup) {
                    backToGroup.classList.add('course-content-unblur');
                }
            }

            function unblurFreeCourses() {
                console.log('StudyPeak Debug - Free courses unblurred');
            }

            function unblurPurchasePrompts() {
                const lockedContent = document.querySelectorAll('.ld-item-list-item:not(.free-content-section)');
                lockedContent.forEach(section => {
                    if (!section.querySelector('.purchase-prompt') && !section.querySelector('.expired-access-prompt')) {
                        let prompt;
                        if (accessInfo.is_expired) {
                            /*prompt = document.createElement('div');
                            prompt.className = 'expired-access-prompt course-content-unblur';
                            prompt.innerHTML = `
                                <h4>Zugang abgelaufen</h4>
                                <p>Dein Zugang ist am ${accessInfo.expiry_date} abgelaufen. Um wieder vollen Zugriff zu erhalten, musst du den Kurs erneut erwerben.</p>
                                <button class="renew-button" onclick="renewAccess()">Zugang verlängern</button>
                            `;*/
                            prompt = document.createElement('div');
                            prompt.className = 'purchase-prompt course-content-unblur';
                            prompt.innerHTML = `
                                <h4>Premium Inhalt</h4>
                                <p>Dieser Inhalt ist ausschliesslich für eingeschriebene Schüler und Schülerinnen. Um vollen Zugriff auf alle Bereiche zu erhalten, muss der Kurs käuflich erworben werden.</p>
                                <button class="purchase-button" onclick="purchaseCourse()">Jetzt kaufen!</button>
                            `;
                        } else {
                            prompt = document.createElement('div');
                            prompt.className = 'purchase-prompt course-content-unblur';
                            prompt.innerHTML = `
                                <h4>Premium Inhalt</h4>
                                <p>Dieser Inhalt ist ausschliesslich für eingeschriebene Schüler und Schülerinnen. Um vollen Zugriff auf alle Bereiche zu erhalten, muss der Kurs käuflich erworben werden.</p>
                                <button class="purchase-button" onclick="purchaseCourse()">Jetzt kaufen!</button>
                            `;
                        }
                        section.appendChild(prompt);
                    }
                });
            }

            function showPopup(content) {
                const popupContainer = document.getElementById('onboarding-popup-container');
                if (popupContainer) {
                    popupContainer.innerHTML = `
                        <div class="onboarding-popup active">
                            <div class="onboarding-popup-content">
                                ${content}
                            </div>
                            <div id="brxe-munokh" class="brxe-container onboarding-popup-image brx-animated" data-interactions="[{&quot;id&quot;:&quot;ciofcj&quot;,&quot;trigger&quot;:&quot;enterView&quot;,&quot;action&quot;:&quot;startAnimation&quot;,&quot;runOnce&quot;:&quot;1&quot;,&quot;animationType&quot;:&quot;fadeInUp&quot;}]" data-interaction-id="ff2aad" data-interaction-hidden-on-load="1" data-animation-id="ciofcj">
                                <div>
                                    <img width="214"  src="https://studypeak.ch/wp-content/uploads/2025/02/stellwerk-vorteile-image.png" class="brxe-image online-course-image-animation css-filter size-full" alt="" id="brxe-lafaha" decoding="async" data-type="string">
                                </div>
                            </div> 
                        </div>
                    `;
                }

                const overlay = document.getElementById('onboarding-overlay');
                if (overlay) {
                    overlay.classList.add('active');
                }
            }

            function showPopupAfterElement(element, content) {
                const popupContainer = document.getElementById('onboarding-popup-container');
                if (popupContainer) {
                    const popup = document.createElement('div');
                    popup.className = 'onboarding-popup after-element active';
                    popup.innerHTML = `
                        <div class="onboarding-popup-content">
                            ${content}
                        </div>
                        <div id="brxe-munokh" class="brxe-container onboarding-popup-image brx-animated" data-interactions="[{&quot;id&quot;:&quot;ciofcj&quot;,&quot;trigger&quot;:&quot;enterView&quot;,&quot;action&quot;:&quot;startAnimation&quot;,&quot;runOnce&quot;:&quot;1&quot;,&quot;animationType&quot;:&quot;fadeInUp&quot;}]" data-interaction-id="ff2aad" data-interaction-hidden-on-load="1" data-animation-id="ciofcj">
                            <div>
                                <img width="214"  src="https://studypeak.ch/wp-content/uploads/2025/02/stellwerk-vorteile-image.png" class="brxe-image online-course-image-animation css-filter size-full" alt="" id="brxe-lafaha" decoding="async" data-type="string">
                            </div>
                        </div>
                    `;

                    element.parentNode.insertBefore(popup, element.nextSibling);
                    popupContainer.innerHTML = '';
                }

                const overlay = document.getElementById('onboarding-overlay');
                if (overlay) {
                    overlay.classList.add('active');
                }
            }

            async function completeOnboarding() {
                const overlay = document.getElementById('onboarding-overlay');
                const popupContainer = document.getElementById('onboarding-popup-container');
                const character = document.querySelector('.onboarding-character');

                document.querySelectorAll('.onboarding-popup').forEach(popup => popup.remove());

                if (overlay) overlay.classList.remove('active');
                if (popupContainer) popupContainer.innerHTML = '';
                if (character) character.style.display = 'none';

                // Show loader before AJAX
                try {
                    if (window.jQuery && jQuery('.site-loader').length) {
                        jQuery('.site-loader').show();
                    }
                } catch (e) {}

                try {
                    // consume session trigger
                    try { sessionStorage.removeItem('sp_onboarding_trigger_session'); } catch (e) {}

                    // Persist seen to DB
                    await fetch(`${adminAjax}?action=sp_set_onboarding_seen`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `course_id=${encodeURIComponent(courseId)}`
                    });

                    // ✅ Call function after AJAX success
                    if (typeof addPurchasePromptToFirstLockedLesson === 'function') {
                        addPurchasePromptToFirstLockedLesson(true);
                    }

                    // Hide loader on success
                    try {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        if (window.jQuery && jQuery('.site-loader').length) {
                            jQuery('.site-loader').hide();
                        }
                    } catch (e) {}
                } catch (e) {
                    // Optional: also hide loader on error to avoid it getting stuck
                    try {
                        if (window.jQuery && jQuery('.site-loader').length) {
                            jQuery('.site-loader').hide();
                        }
                    } catch (e2) {}
                }

                onboardingActive = false;
            }

            /*function purchaseCourse() {
                // Detect from body class
                let parentGroupId = '';

                if (document.body.classList.contains('ld-group-id-32332')) {
                    parentGroupId = '32332'; // Langzeitgymnasium
                } else if (document.body.classList.contains('ld-group-id-86815')) {
                    parentGroupId = '86815'; // Kurzzeitgymnasium
                } else if (document.body.classList.contains('ld-group-id-86719')) {
                    parentGroupId = '86719'; // Kurzzeitgymnasium
                } else if (document.body.classList.contains('ld-group-id-86718')) {
                    parentGroupId = '86718'; // Kurzzeitgymnasium
                } else if (document.body.classList.contains('ld-group-id-80847')) {
                    parentGroupId = '80847'; // Kurzzeitgymnasium
                } else if (document.body.classList.contains('ld-group-id-86716')) {
                    parentGroupId = '86716'; // Kurzzeitgymnasium
                }

                // ✅ Store selection in a cookie (works across pages)
                if (parentGroupId) {
                    document.cookie = `selectedParentGroup=${parentGroupId}; path=/; max-age=86400`; // expires in 1 day
                }


                // Redirect to Gymi Vorbereitung page
                window.location.href = 'https://studypeak.ch/gymi-vorbereitung/';
            }*/

            function purchaseCourse() {
                let parentGroupId = '';
                let redirectUrl = '';

                // Detect from body class
                if (document.body.classList.contains('ld-group-id-32332')) {
                    parentGroupId = '32332'; // Langzeitgymnasium
                    redirectUrl = 'https://studypeak.ch/gymi-vorbereitung/';
                } else if (document.body.classList.contains('ld-group-id-86815')) {
                    parentGroupId = '86815'; // Kurzzeitgymnasium
                    redirectUrl = 'https://studypeak.ch/gymi-vorbereitung/';
                } else if (
                    document.body.classList.contains('ld-group-id-86719') ||
                    document.body.classList.contains('ld-group-id-86718') ||
                    document.body.classList.contains('ld-group-id-80847') ||
                    document.body.classList.contains('ld-group-id-86716')
                ) {
                    // IMS / BMS / FMS / HMS
                    if (document.body.classList.contains('ld-group-id-86719')) parentGroupId = '86719';
                    else if (document.body.classList.contains('ld-group-id-86718')) parentGroupId = '86718';
                    else if (document.body.classList.contains('ld-group-id-80847')) parentGroupId = '80847';
                    else if (document.body.classList.contains('ld-group-id-86716')) parentGroupId = '86716';

                    redirectUrl = 'https://studypeak.ch/ims-bms-fms-hms-vorbereitung/';
                }

                // ✅ Store selection in a cookie (1-day lifespan)
                if (parentGroupId) {
                    document.cookie = `selectedParentGroup=${parentGroupId}; path=/; max-age=86400`;
                }

                // ✅ Redirect if we have a valid destination
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }

            /*function renewAccess() {
                window.location.href = 'https://studypeak.ch/';
            }*/

            function renewAccess() {
                // Optional: You can also check which type to redirect to based on cookie
                const cookieMatch = document.cookie.match(/selectedParentGroup=(\d+)/);
                const parentGroupId = cookieMatch ? cookieMatch[1] : '';

                if (['32332', '86815'].includes(parentGroupId)) {
                    window.location.href = 'https://studypeak.ch/gymi-vorbereitung/';
                } else if (['86719', '86718', '80847', '86716'].includes(parentGroupId)) {
                    window.location.href = 'https://studypeak.ch/ims-bms-fms-hms-vorbereitung/';
                } else {
                    // Default fallback
                    window.location.href = 'https://studypeak.ch/';
                }
            }

            // Make functions global
            window.nextStep = nextStep;
            window.completeOnboarding = completeOnboarding;
            window.purchaseCourse = purchaseCourse;
            window.renewAccess = renewAccess;
        });
    </script>
    <?php
});

/**
 * AJAX handler to get user access status
 */
add_action('wp_ajax_get_user_access_status', 'studypeak_ajax_get_user_access_status');
add_action('wp_ajax_nopriv_get_user_access_status', 'studypeak_ajax_get_user_access_status');

function studypeak_ajax_get_user_access_status()
{
    $access_info = studypeak_get_user_access_status();
    wp_send_json_success($access_info);
}

// Helper: robust completion check
if (!function_exists('studypeak_is_step_complete')) {
    function studypeak_is_step_complete( $user_id, $step_id, $course_id ) {
        if (function_exists('learndash_is_item_complete')) {
            return (bool) learndash_is_item_complete($user_id, $step_id, $course_id);
        }
        if (function_exists('learndash_is_step_complete')) {
            return (bool) learndash_is_step_complete($user_id, $step_id, $course_id);
        }
        $type = get_post_type($step_id);
        if ($type === 'sfwd-lessons' && function_exists('learndash_is_lesson_complete')) {
            return (bool) learndash_is_lesson_complete($user_id, $step_id, $course_id);
        }
        if ($type === 'sfwd-topic' && function_exists('learndash_is_topic_complete')) {
            return (bool) learndash_is_topic_complete($user_id, $step_id, $course_id);
        }
        if ($type === 'sfwd-quiz' && function_exists('learndash_is_quiz_complete')) {
            return (bool) learndash_is_quiz_complete($user_id, $step_id);
        }
        return false;
    }
}

// Helper: lesson id for any step (handles quizzes via topic → lesson fallback)
if (!function_exists('studypeak_get_lesson_id_for_step')) {
    function studypeak_get_lesson_id_for_step( $step_id ) {
        $type = get_post_type($step_id);
        if ($type === 'sfwd-lessons') {
            return (int) $step_id;
        }
        if (function_exists('learndash_get_lesson_id')) {
            $lid = (int) learndash_get_lesson_id($step_id);
            if ($lid > 0) return $lid;
        }
        if ($type === 'sfwd-quiz' && function_exists('learndash_get_topic_id')) {
            $topic_id = (int) learndash_get_topic_id($step_id);
            if ($topic_id > 0) {
                if (function_exists('learndash_get_lesson_id')) {
                    $lid = (int) learndash_get_lesson_id($topic_id);
                    if ($lid > 0) return $lid;
                }
                foreach (array('_ld_lesson','lesson_id','ld_lesson_id') as $mk) {
                    $val = (int) get_post_meta($topic_id, $mk, true);
                    if ($val > 0) return $val;
                }
            }
        }
        foreach (array('_ld_lesson','lesson_id','ld_lesson_id') as $mk) {
            $val = (int) get_post_meta($step_id, $mk, true);
            if ($val > 0) return $val;
        }
        return 0;
    }
}

// Helper: lesson children (topics + lesson-attached quizzes) in actual order
if (!function_exists('studypeak_get_lesson_children_steps')) {
    function studypeak_get_lesson_children_steps($lesson_id, $course_id) {
        $steps = array();

        if (function_exists('learndash_get_topic_list')) {
            $topics = learndash_get_topic_list($lesson_id, $course_id);
            if (is_array($topics)) {
                foreach ($topics as $t) {
                    $tid = is_object($t) ? (int) $t->ID : (int) $t;
                    if ($tid && get_post_status($tid) === 'publish') {
                        $steps[] = array('id' => $tid, 'type' => 'sfwd-topic', 'url' => get_permalink($tid));
                    }
                }
            }
        }

        if (function_exists('learndash_get_lesson_quiz_list')) {
            $quizzes = learndash_get_lesson_quiz_list($lesson_id, 0, $course_id);
            if (is_array($quizzes)) {
                foreach ($quizzes as $q) {
                    $qid = isset($q['post']->ID) ? (int) $q['post']->ID : (int) ($q['id'] ?? 0);
                    if ($qid && get_post_status($qid) === 'publish') {
                        $steps[] = array('id' => $qid, 'type' => 'sfwd-quiz', 'url' => get_permalink($qid));
                    }
                }
            }
        }

        return $steps;
    }
}

// 4) Redirect only the "Mark as complete" POST for users matching onboarding gates
add_filter('wp_redirect', function ($location, $status) {
    // Only act on LD "Mark as complete" POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $location;
    if (empty($_POST['sfwd_mark_complete'])) return $location;

    // Exclude administrators
    if (current_user_can('administrator')) return $location;

    // Validate posted context
    $current_post_id = isset($_POST['post']) ? (int) $_POST['post'] : 0;
    $course_id       = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
    if ($course_id <= 0 || $current_post_id <= 0) return $location;

    // Only for lessons/topics/quizzes
    $post_type = get_post_type($current_post_id);
    if (!in_array($post_type, array('sfwd-lessons','sfwd-topic','sfwd-quiz'), true)) {
        return $location;
    }

    // Match target courses/groups
    // $target_course_ids = array(71335, 71313, 88638, 85280);
    $target_course_ids = array(71335, 71313, 88638, 85280, 100760, 96715, 100762, 96717, 100764, 96721, 100766, 96719);
    if (!in_array($course_id, $target_course_ids, true)) return $location;

    $target_groups = array(87908, 81614, 87894, 87898, 87890, 87902);
    $user_id = get_current_user_id();
    $user_in_target_groups = false;
    if ($user_id && function_exists('learndash_is_user_in_group')) {
        foreach ($target_groups as $gid) {
            if (learndash_is_user_in_group($user_id, $gid)) { $user_in_target_groups = true; break; }
        }
    }
    if (!$user_in_target_groups) return $location;

    // Restricted state
    if (!function_exists('studypeak_get_user_access_status')) return $location;
    $access = studypeak_get_user_access_status();
    $is_restricted = (!$access['has_access'] || !empty($access['is_expired']));
    if (!$is_restricted) return $location;

    // Free URLs
    if (!function_exists('studypeak_get_free_urls')) return $location;
    $urls = studypeak_get_free_urls();
    if (empty($urls)) return $location;

    // Build ordered list of free steps in this course with metadata
    $free_posts = array();
    if (function_exists('learndash_get_course_id')) {
        foreach ($urls as $url) {
            $pid = url_to_postid($url);
            if (!$pid) continue;
            $cid = (int) learndash_get_course_id($pid);
            if ($cid !== $course_id) continue;
            if (get_post_status($pid) !== 'publish') continue;

            $free_posts[] = array(
                'id'        => (int) $pid,
                'url'       => $url,
                'type'      => get_post_type($pid),
                'lesson_id' => studypeak_get_lesson_id_for_step($pid),
            );
        }
    }
    if (empty($free_posts)) {
        $course_url = get_permalink($course_id);
        return $course_url ? $course_url : $location;
    }

    // Current context
    $current_lesson_id = studypeak_get_lesson_id_for_step($current_post_id);
    $count = count($free_posts);

    // Find index of current within free list (if present)
    $start_index = 0;
    for ($i = 0; $i < $count; $i++) {
     if ($free_posts[$i]['id'] === $current_post_id) {
       $start_index = ($i + 1 < $count) ? $i + 1 : 0;
       break;
     }
    }

    // Topic branch: choose next by lesson order; require free-listed and not complete
    if ($post_type === 'sfwd-topic' && $post_type === 'sfwd-quiz' && $current_lesson_id) {
        // Fast lookup of free IDs
        $free_ids = array();
        foreach ($free_posts as $fp) {
            $free_ids[(int)$fp['id']] = true;
        }

        $children = studypeak_get_lesson_children_steps($current_lesson_id, $course_id);
        if ($children) {
            $idx = -1;
            foreach ($children as $i => $c) {
                if ((int)$c['id'] === (int)$current_post_id) { $idx = $i; break; }
            }
            if ($idx >= 0) {
                $child_count = count($children);
                for ($j = $idx + 1; $j < $child_count; $j++) {
                    $child_id = (int) $children[$j]['id'];
                    if (empty($free_ids[$child_id])) continue; // must be in free list
                    if (!studypeak_is_step_complete($user_id, $child_id, $course_id)) {
                        return $children[$j]['url']; // next free, incomplete topic or quiz
                    }
                }
            }
        }

        // Nothing suitable -> lesson page
        $lesson_url = get_permalink($current_lesson_id);
        if ($lesson_url) return $lesson_url;
    }

    // Otherwise (lesson/quiz or topic fallback), next free & incomplete anywhere in course
    for ($round = 0; $round < 2; $round++) {
        $from = ($round === 0) ? $start_index : 0;
        $to   = ($round === 0) ? $count       : $start_index;
        for ($i = $from; $i < $to; $i++) {
            if (!studypeak_is_step_complete($user_id, $free_posts[$i]['id'], $course_id)) {
                return $free_posts[$i]['url'];
            }
        }
    }

    // All free steps in course completed: course page
    $course_url = get_permalink($course_id);
    return $course_url ? $course_url : $location;
}, 99, 2);


// Force: if LESSON is free and a topic is marked complete, always go to the next topic in same lesson
// Force: if LESSON is free and a topic is marked complete, redirect properly to next topic or quiz
add_filter('wp_redirect', function ($location, $status) {
    // Only act on LearnDash "Mark as complete" POST for topics
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $location;
    if (empty($_POST['sfwd_mark_complete'])) return $location;
    if (current_user_can('administrator')) return $location;

    $current_post_id = isset($_POST['post']) ? (int) $_POST['post'] : 0;
    $course_id       = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
    if ($current_post_id <= 0 || $course_id <= 0) return $location;

    if (get_post_type($current_post_id) !== 'sfwd-topic') return $location;

    // Resolve lesson
    if (!function_exists('learndash_get_lesson_id')) return $location;
    $lesson_id = (int) learndash_get_lesson_id($current_post_id);
    if ($lesson_id <= 0) return $location;

    // LESSON must be free (present in free URLs)
    $lesson_is_free = false;
    if (function_exists('studypeak_get_free_urls')) {
        foreach (studypeak_get_free_urls() as $url) {
            if ((int) url_to_postid($url) === $lesson_id) { 
                $lesson_is_free = true; 
                break; 
            }
        }
    }
    if (!$lesson_is_free) return $location;

    // Get all published topics in lesson order
    if (!function_exists('learndash_get_topic_list')) return $location;
    $topics = learndash_get_topic_list($lesson_id, $course_id);
    $ordered_topic_ids = array();

    if (is_array($topics)) {
        foreach ($topics as $t) {
            $tid = is_object($t) ? (int) $t->ID : (int) $t;
            if ($tid && get_post_status($tid) === 'publish') {
                $ordered_topic_ids[] = $tid;
            }
        }
    }

    // Try to find the next topic in the same lesson
    $next_topic_url = '';
    if (!empty($ordered_topic_ids)) {
        $count = count($ordered_topic_ids);
        for ($i = 0; $i < $count; $i++) {
            if ($ordered_topic_ids[$i] === $current_post_id) {
                if ($i + 1 < $count) {
                    $next_topic_url = get_permalink($ordered_topic_ids[$i + 1]);
                }
                break;
            }
        }
    }

    // If there is a next topic, go there
    if (!empty($next_topic_url)) {
        return $next_topic_url;
    }

    // If no more topics, check for quizzes attached to this lesson
    if (function_exists('learndash_get_lesson_quiz_list')) {
        $quizzes = learndash_get_lesson_quiz_list($lesson_id, 0, $course_id);
        if (!empty($quizzes) && is_array($quizzes)) {
            foreach ($quizzes as $quiz) {
                $quiz_id = isset($quiz['post']->ID) ? (int) $quiz['post']->ID : (int) ($quiz['id'] ?? 0);
                if ($quiz_id && get_post_status($quiz_id) === 'publish') {
                    // Redirect to the first available quiz
                    return get_permalink($quiz_id);
                }
            }
        }
    }

    // No topics or quizzes left — go to lesson or course page
    $lesson_url = get_permalink($lesson_id);
    if (!empty($lesson_url)) return $lesson_url;

    $course_url = get_permalink($course_id);
    return $course_url ? $course_url : $location;

}, 999, 2);

add_filter( 'body_class', function( $classes ) {
    if ( function_exists( 'weglot_get_current_language' ) ) {
        $lang = weglot_get_current_language();
        $classes[] = 'sp-lang-' . esc_attr( $lang );
    }
    return $classes;
});