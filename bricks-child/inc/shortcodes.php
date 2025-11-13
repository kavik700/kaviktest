<?php


// Course Pricing Shortcode -----------------------------------------------------------------------------------
function courses_pricing_shortcode() {

    // Get the current language groups
    // if( is_page('multicheck-vorbereitung') ) :
    // do_action('wpml_switch_language', 'de');  // Switch to German
    $current_language = apply_filters('wpml_current_language', 'de');
    $target_ids = [87908, 87910, 81616, 81614, 32332, 86815];
    $imsg_ids   = [87894, 87895, 87898, 87899, 87902, 87903];
    
    // Get the current page URL segment (e.g., "multicheck-vorbereitung")
    $current_url = $_SERVER['REQUEST_URI'];
    $url_segments = explode('/', trim($current_url, '/'));
    $current_page_category = $url_segments[0];

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

    // Get initial data by simulating the AJAX request
    $initial_data = array(
        'f-access-days' => '30', // Default value
        'f-group' => !empty($groups) ? $groups[0]->ID : ''
    );
    
    // Simulate POST data for filter_products_ajax_handler
    $_POST['data'] = http_build_query($initial_data);
    
    // Get initial pricing data
    ob_start();
    $initial_pricing = filter_products();
    ob_clean(); // Clear output buffer from AJAX handler
            
    // do_action('wpml_switch_language', ICL_LANGUAGE_CODE);  // Switch back to current language
    // Display groups or fallback
    // if (!empty($groups)) {
    //     if (count($groups) > 5) {
   
    ob_start(); ?>
    
    <!-- Course Pricing Section Start-->
    <div class="brxe-block course-pricing-wrapper">  
        <h3 class="brxe-heading section-title text-center">Preise</h3>

        <div class="course-pricing-tabs">
            <?php 
            // Get initial data by simulating the AJAX request
            $initial_data = array(
                'f-access-days' => '30', // Default value
                'f-group' => !empty($groups) ? $groups[0]->ID : ''
            );
            
            // Simulate POST data for filter_products_ajax_handler
            $_POST['data'] = http_build_query($initial_data);
            
            // Get initial pricing data
            ob_start();
            $initial_pricing = filter_products();
            ob_clean(); // Clear output buffer from AJAX handler
            
            // Convert to array if it's a JSON string
            if (is_string($initial_pricing)) {
                $initial_pricing = json_decode($initial_pricing, true);
            }
            
            if( is_page('multicheck-vorbereitung') || is_page('basic-check-vorbereitung') ) : ?>
                <div class="plan-details-group-wrap">
                    <?php if( is_page('basic-check-vorbereitung') ): ?>
                        <h3 class="brxe-heading section-subtitle">Wähle deine Basic-Check-Richtung</h3>
                        <div class="plan-details-group">
                            <form id="groups-plan-filter" class="common_filter_form groupf-form" <?php //if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>>        
                                <div class="form-group" role="group"> 
                                    <select id="form-field-plan-group" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
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

                            <?php
                            $group_title = $initial_pricing['pptitle']; // Get the group title from the filter_products function
                            $slider_images = $initial_pricing['ppsimages']; // Get the slider images from the filter_products function
                            // Check if slider images exist and are not empty
                            if (!empty($slider_images)): ?>
                                <button class="bricks-button bricks-background-primary" id="preview-popup-images"
                                    onclick="openPreviewPopup(this);"
                                    data-group-title="<?php echo esc_attr($group_title); ?>"
                                    data-slider-images="<?php echo esc_attr(json_encode($slider_images)); ?>">
                                    Vorschau
                                </button>
                            <?php endif; ?>

                            <div class="tabs">
                                <label>Zugang für</label>
                                <div class="tab-btn-wrap">
                                    <button class="tab-btn active" data-tab="30-tage">30 Tage</button>
                                    <button class="tab-btn" data-tab="60-tage">60 Tage</button>
                                    <button class="tab-btn" data-tab="90-tage">90 Tage</button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3 class="brxe-heading section-subtitle">Wähle deine Multicheck-Richtung</h3>
                        <div class="plan-details-group">
                            <form id="groups-plan-filter" class="common_filter_form groupf-form" <?php if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>>        
                                <div class="form-group" role="group"> 
                                    <select id="form-field-plan-group" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                                        <?php 
                                        // IDs to skip in French
                                        $skip_ids_fr = [86408, 86685, 21702, 21707, 27434];
                                        // Detect current language via Weglot
                                        $current_lang = function_exists('weglot_get_current_language') ? weglot_get_current_language() : '';
                                        foreach ($groups as $gpkey => $gpvalue) {             
                                            // If language is French, skip the unwanted IDs
                                            if ($current_lang === 'fr' && in_array($gpvalue->ID, $skip_ids_fr)) {
                                                continue;
                                            }         
                                            echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                                        }  
                                        ?> 
                                    </select>               
                                </div>   
                            </form>  

                            <?php
                            $group_title = $initial_pricing['pptitle']; // Get the group title from the filter_products function
                            $slider_images = $initial_pricing['ppsimages']; // Get the slider images from the filter_products function
                            // Check if slider images exist and are not empty
                            if (!empty($slider_images)): ?>
                                <button class="bricks-button bricks-background-primary" id="preview-popup-images"
                                    onclick="openPreviewPopup(this);"
                                    data-group-title="<?php echo esc_attr($group_title); ?>"
                                    data-slider-images="<?php echo esc_attr(json_encode($slider_images)); ?>">
                                    Vorschau
                                </button>
                            <?php endif; ?>

                            <div class="tabs">
                                <label>Zugang für</label>
                                <div class="tab-btn-wrap">
                                    <button class="tab-btn active" data-tab="30-tage">30 Tage</button>
                                    <button class="tab-btn" data-tab="60-tage">60 Tage</button>
                                    <button class="tab-btn" data-tab="90-tage">90 Tage</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-plan-content" id="all-tage" data-purchase-status="<?php echo isset($initial_pricing['ppstatus']) ? $initial_pricing['ppstatus'] : '0'; ?>">
                    <div class="pricing-row">
                        <div class="card-pricing-plans traning-plan">
                            <div class="plan-title">
                                <h3>Trainingsbereich</h3>
                                <p>Zugang zum Trainingsbereich</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['t_info']['price']) ? $initial_pricing['variations']['t_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['t_info']['days']) ? $initial_pricing['variations']['t_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['tf_html']) ? $initial_pricing['tf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich"
                                    data-pricing-description="<?php echo isset($initial_pricing['t_info']) ? htmlspecialchars($initial_pricing['t_info']) : ''; ?>">Mehr erfahren
                                </button>
                            </div>                    
                        </div>
                        <div class="card-pricing-plans simulation-plan">
                            <div class="plan-title">
                                <h3>Prüfungssimulation</h3>
                                <p>Zugang zur Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['s_info']['price']) ? $initial_pricing['variations']['s_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['s_info']['days']) ? $initial_pricing['variations']['s_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>                                </button>
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['sf_html']) ? $initial_pricing['sf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['s_info']) ? htmlspecialchars($initial_pricing['s_info']) : ''; ?>">Mehr erfahren
                                </button>                        
                            </div>                    
                        </div>
                        <div class="card-pricing-plans card-platinum-plans premium-plan">
                            <span class="popular-plan">Beliebteste Option</span>
                            <div class="plan-title">
                                <h3>Package</h3>
                                <p>Trainingsbereich + Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                                <span><i class="fas fa-plus"></i></span>
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['p_info']['price']) ? $initial_pricing['variations']['p_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['p_info']['days']) ? $initial_pricing['variations']['p_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['pf_html']) ? $initial_pricing['pf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['p_info']) ? htmlspecialchars($initial_pricing['p_info']) : ''; ?>">Mehr erfahren
                                </button>  
                            </div>                    
                        </div>
                    </div>
                </div>
            <?php elseif( is_page('probezeit-vorbereitung') ) : ?>
                <div class="plan-details-group-wrap">
                    <h3 class="brxe-heading section-subtitle">Wähle deinen Probezeit-Kurs</h3>
                    <div class="plan-details-group">
                        <form id="groups-plan-filters" class="common_filter_form groupf-form">        
                            <div class="form-group" role="group"> 
                                <select id="form-field-plan-groupss" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                                    <option value="Langzeitgymnasium">Langzeitgymnasium</option>
                                </select>               
                            </div>   
                        </form> 

                        <form id="groups-plan-filter" class="common_filter_form groupf-form" <?php if (count($groups) < 1 ) { echo "style='display:none;'"; } ?>>        
                            <div class="form-group" role="group"> 
                                <select id="form-field-plan-group" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                                    <?php 
                                    foreach ($groups as $gpkey => $gpvalue) {                        
                                        echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                                    }  
                                    ?> 
                                </select>               
                            </div>   
                        </form>  

                        <div class="tabs">
                            <label>Zugang für</label>
                            <div class="tab-btn-wrap">
                                <button class="tab-btn active" data-tab="30-tage">30 Tage</button>
                                <button class="tab-btn" data-tab="60-tage">60 Tage</button>
                                <button class="tab-btn" data-tab="90-tage">90 Tage</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-plan-content" id="all-tage" data-purchase-status="<?php echo isset($initial_pricing['ppstatus']) ? $initial_pricing['ppstatus'] : '0'; ?>">
                    <div class="pricing-row">
                        <div class="card-pricing-plans traning-plan">
                            <div class="plan-title">
                                <h3>Trainingsbereich</h3>
                                <p style="display:none;">Zugang zum Trainingsbereich</p>
                            </div>
                            <div class="plan-icon" style="display:none;">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['t_info']['price']) ? $initial_pricing['variations']['t_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['t_info']['days']) ? $initial_pricing['variations']['t_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['tf_html']) ? $initial_pricing['tf_html'] : ''; ?></ul> 
                                </div>
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <button 
                                    style="display:none;"
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich"
                                    data-pricing-description="<?php echo isset($initial_pricing['t_info']) ? htmlspecialchars($initial_pricing['t_info']) : ''; ?>">Mehr erfahren
                                </button>
                            </div>                    
                        </div>
                        <div class="card-pricing-plans simulation-plan">
                            <div class="plan-title">
                                <h3>Prüfungssimulation</h3>
                                <p style="display:none;">Zugang zur Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon" style="display:none;">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['s_info']['price']) ? $initial_pricing['variations']['s_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['s_info']['days']) ? $initial_pricing['variations']['s_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['sf_html']) ? $initial_pricing['sf_html'] : ''; ?></ul> 
                                </div>
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <button 
                                    style="display:none;"
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['s_info']) ? htmlspecialchars($initial_pricing['s_info']) : ''; ?>">Mehr erfahren
                                </button>                        
                            </div>                    
                        </div>
                        <div class="card-pricing-plans card-platinum-plans premium-plan">
                            <span class="popular-plan">Beliebteste Option</span>
                            <div class="plan-title">
                                <h3>Package</h3>
                                <p style="display:none;">Trainingsbereich + Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon" style="display:none;">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                                <span><i class="fas fa-plus"></i></span>
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['p_info']['price']) ? $initial_pricing['variations']['p_info']['price'] : '0'; ?></span> CHF</h4>
                                <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['p_info']['days']) ? $initial_pricing['variations']['p_info']['days'] : '0'; ?></span></p>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['pf_html']) ? $initial_pricing['pf_html'] : ''; ?></ul> 
                                </div>
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <button
                                    style="display:none;" 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['p_info']) ? htmlspecialchars($initial_pricing['p_info']) : ''; ?>">Mehr erfahren
                                </button>  
                            </div>                    
                        </div>
                    </div>
                    <?php
                    $group_title = $initial_pricing['pptitle']; // Get the group title from the filter_products function
                    $slider_images = $initial_pricing['ppsimages']; // Get the slider images from the filter_products function
                    // Check if slider images exist and are not empty
                    if (!empty($slider_images)): ?>
                    <div class="plan-details-group">
                            <button class="bricks-button bricks-background-primary" id="preview-popup-images"
                                onclick="openPreviewPopup(this);"
                                data-group-title="<?php echo esc_attr($group_title); ?>"
                                data-slider-images="<?php echo esc_attr(json_encode($slider_images)); ?>">
                                Vorschau
                            </button>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="plan-details-group-wrap">
                    <?php if( is_page('ims-bms-fms-hms-vorbereitung') ) : ?><h3 class="brxe-heading section-subtitle">Wähle deine ZAP-Richtung</h3><?php endif; ?>
                    <?php if( is_page('gymi-vorbereitung') ) : ?><h3 class="brxe-heading section-subtitle">Wähle deine Stufe</h3><?php endif; ?>
                    <?php if( is_page('stellwerktest-vorbereitung') ) : ?><h3 class="brxe-heading section-subtitle">Wähle deinen Zugang</h3><?php endif; ?>
                    <div class="plan-details-group">
                        <form id="groups-plan-filter" class="common_filter_form groupf-form" <?php if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>>        
                            <div class="form-group" role="group"> 
                                <select id="form-field-plan-group" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                                    <?php 

                                    $gparr = [];
                                    foreach ($groups as $gpkey => $gpvalue) {   
                                        $gparr[] =  $gpvalue->ID;                    
                                        echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                                    }  
                                    ?> 
                                </select>               
                            </div>   
                        </form> 

                        <?php
                        $group_title = $initial_pricing['pptitle']; // Get the group title from the filter_products function
                        $slider_images = $initial_pricing['ppsimages']; // Get the slider images from the filter_products function

                        // Check if slider images exist and are not empty
                        // if (!empty($slider_images)): ?>
                            <button class="bricks-button bricks-background-primary" id="<?php echo esc_attr($group_title); ?>"
                                onclick="openPreviewPopup(this);"
                                data-group-title="<?php echo esc_attr($group_title); ?>"
                                data-slider-images="<?php echo esc_attr(json_encode($slider_images)); ?>">
                                Vorschau
                            </button>
                        <?php //endif; ?>

                        <div class="tabs">
                            <label>Zugang für</label>
                            <div class="tab-btn-wrap">
                                <button class="tab-btn active" data-tab="30-tage">30 Tage</button>
                                <button class="tab-btn" data-tab="90-tage">90 Tage</button>
                                <?php /*if (!empty($gparr) && array_intersect($gparr, $target_ids)) { ?>
                                    <button class="tab-btn" data-tab="180-tage">Bis zur Gymiprüfung 2026</button>
                                <?php } else if( !empty($gparr) && array_intersect($gparr, $imsg_ids) ) { ?>
                                    <button class="tab-btn" data-tab="180-tage">Bis zur ZAP 2026</button>
                                <?php } else {*/ ?>
                                    <button class="tab-btn" data-tab="180-tage">180 Tage</button>
                                <?php //} ?>
                            </div>
                        </div> 
                    </div>
                </div>
                <div class="tab-plan-content" id="all-tage" data-purchase-status="<?php echo isset($initial_pricing['ppstatus']) ? $initial_pricing['ppstatus'] : '0'; ?>">
                    <div class="pricing-row">
                        <?php if (is_page('gymi-vorbereitung')) : ?>
                            <div class="card-pricing-plans card-platinum-plans pro-premium-plan">
                                <div class="cardleft_area">
                                    <span class="popular-plan">Beliebteste Option</span>
                                    <div class="plan-title">
                                        <h3 class="text-white">Premium Package</h3>
                                        <p>Trainingsbereich + Prüfungssimulation + <b>1 Nachhilfe-Lektion</b> + <b>Persönlicher Tutor</b> + Aufsatzkorrektur</p>
                                    </div>
                                    <div class="plan-icon">
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icon_3.svg' alt="practice-simulations" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icon_4.svg' alt="practice-simulations" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icons_4.svg' alt="practice-simulations" />
                                    </div>
                                    <div class="plan-price">
                                        <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['pp_info']['price']) ? $initial_pricing['variations']['pp_info']['price'] : '0'; ?></span> CHF</h4>

                                        <?php /*if (is_page('gymi-vorbereitung') && $initial_pricing['variations']['pp_info']['days'] == 180) : ?>

                                            <p>Bis zur Gymiprüfung 2026</p>

                                        <?php else :*/ ?>

                                            <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['pp_info']['days']) ? $initial_pricing['variations']['pp_info']['days'] : '0'; ?></span></p>
                                        <?php //endif; ?>
                                    </div>
                                    <hr>

                                    <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                        <?php if (isset($initial_pricing['variations']['pp_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['pp_info']['proid']); ?>"
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['pp_info']['varid']); ?>"
                                        <?php endif; ?>
                                        <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>>
                                        <?php echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                        <!-- In Kürze verfügbar! -->
                                    </button>
                                    <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['pp_info']) ? htmlspecialchars($initial_pricing['pp_info']) : ''; ?>">Mehr erfahren
                                    </button>  
                                </div>
                                <div class="cardright_area">
                                    <div class="plan-details ul-list-group">
                                        <div class="ul-list-group">
                                            <ul><?php echo isset($initial_pricing['ppf_html']) ? $initial_pricing['ppf_html'] : ''; ?></ul> 
                                        </div>
                                        <button 
                                            type="button"
                                            class="btn-link bricks-button bricks-background-primary" 
                                            onclick="openPricingPopup(this);" 
                                            data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                            data-pricing-description="<?php echo isset($initial_pricing['pp_info']) ? htmlspecialchars($initial_pricing['pp_info']) : ''; ?>">Mehr erfahren
                                        </button>  
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="card-pricing-plans traning-plan">
                            <div class="plan-title">
                                <h3>Trainingsbereich</h3>
                                <p>Zugang zum Trainingsbereich</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['t_info']['price']) ? $initial_pricing['variations']['t_info']['price'] : '0'; ?></span> CHF</h4>

                                <?php /*if( is_page('gymi-vorbereitung') && $initial_pricing['variations']['p_info']['days'] == 180 ) : ?>
                                    
                                    <p>Bis zur Gymiprüfung 2026</p>

                                <?php else :*/ ?>

                                    <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['t_info']['days']) ? $initial_pricing['variations']['t_info']['days'] : '0'; ?></span></p>

                                <?php //endif; ?>

                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>

                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['tf_html']) ? $initial_pricing['tf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich"
                                    data-pricing-description="<?php echo isset($initial_pricing['t_info']) ? htmlspecialchars($initial_pricing['t_info']) : ''; ?>">Mehr erfahren
                                </button>
                            </div>                    
                        </div>
                        <div class="card-pricing-plans simulation-plan">
                            <div class="plan-title">
                                <h3>Prüfungssimulation</h3>
                                <p>Zugang zur Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['s_info']['price']) ? $initial_pricing['variations']['s_info']['price'] : '0'; ?></span> CHF</h4>
                                
                                <?php /*if( is_page('gymi-vorbereitung') && $initial_pricing['variations']['p_info']['days'] == 180 ) : ?>
                                    
                                    <p>Bis zur Gymiprüfung 2026</p>

                                <?php else :*/ ?>
                                    
                                    <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['s_info']['days']) ? $initial_pricing['variations']['s_info']['days'] : '0'; ?></span></p>

                                <?php //endif; ?>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['sf_html']) ? $initial_pricing['sf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['s_info']) ? htmlspecialchars($initial_pricing['s_info']) : ''; ?>">Mehr erfahren
                                </button>                        
                            </div>                    
                        </div>
                        <div class="card-pricing-plans card-platinum-plans premium-plan">
                            <span class="popular-plan">Beliebteste Option</span>
                            <div class="plan-title">
                                <h3>Package</h3>
                                <p>Trainingsbereich + Prüfungssimulation</p>
                            </div>
                            <div class="plan-icon">
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                                <span><i class="fas fa-plus"></i></span>
                                <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                            </div>
                            <div class="plan-price">
                                <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['p_info']['price']) ? $initial_pricing['variations']['p_info']['price'] : '0'; ?></span> CHF</h4>

                                <?php /*if( is_page('gymi-vorbereitung') && $initial_pricing['variations']['p_info']['days'] == 180 ) : ?>
                                    
                                    <p>Bis zur Gymiprüfung 2026</p>

                                <?php else :*/ ?>

                                    <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['p_info']['days']) ? $initial_pricing['variations']['p_info']['days'] : '0'; ?></span></p>
                                <?php //endif; ?>
                            </div>
                            <hr>
                            <div class="plan-details">
                                <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                    <?php if(isset($initial_pricing['variations']['p_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['p_info']['proid']); ?>" 
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['p_info']['varid']); ?>"
                                    <?php endif; ?>
                                    <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>
                                >
                                    <?php //echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                    <?php
                                        $lang = weglot_get_current_language();
                                        $status = isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0 ? 'soon' : 'buy';

                                        $texts = [
                                            'soon' => [
                                                'de' => "In Kürze verfügbar!",
                                                'fr' => "Bientôt disponible !",
                                                'it' => "Disponibile a breve!",
                                                'en' => "Coming soon!"
                                            ],
                                            'buy' => [
                                                'de' => "Jetzt kaufen!",
                                                'fr' => "Profiter maintenant !",
                                                'it' => "Acquista ora!",
                                                'en' => "Buy now!"
                                            ]
                                        ];

                                        echo $texts[$status][$lang] ?? $texts[$status]['en'];
                                    ?>
                                </button>
                                <div class="ul-list-group"> 
                                    <ul><?php echo isset($initial_pricing['pf_html']) ? $initial_pricing['pf_html'] : ''; ?></ul> 
                                </div>
                                <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['p_info']) ? htmlspecialchars($initial_pricing['p_info']) : ''; ?>">Mehr erfahren
                                </button>  
                            </div>                    
                        </div>
                        <?php if (is_page('stellwerktest-vorbereitung')) : ?>
                            <div class="card-pricing-plans card-platinum-plans pro-premium-plan">
                                <div class="cardleft_area">
                                    <!-- <span class="popular-plan">Beliebteste Option</span> -->
                                    <div class="plan-title">
                                        <h3 class="text-white">Premium Package</h3>
                                        <p>Trainingsbereich + Prüfungssimulation + <b>1 Nachhilfe-Lektion</b> + <b>Persönlicher Tutor</b></p>
                                    </div>
                                    <div class="plan-icon">
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg' alt="training-program" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg' alt="practice-simulations" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icon_3.svg' alt="practice-simulations" />
                                        <span><i class="fas fa-plus"></i></span>
                                        <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icon_4.svg' alt="practice-simulations" />
                                        <?php if( is_page('gymi-vorbereitung') ) : ?>
                                            <span><i class="fas fa-plus"></i></span>
                                            <img src='<?php echo get_stylesheet_directory_uri(); ?>/assets/images/icons_4.svg' alt="practice-simulations" />
                                        <?php endif; ?>
                                    </div>
                                    <div class="plan-price">
                                        <h4><span class="plan-price"><?php echo isset($initial_pricing['variations']['pp_info']['price']) ? $initial_pricing['variations']['pp_info']['price'] : '0'; ?></span> CHF</h4>

                                        <?php /*if (is_page('gymi-vorbereitung') && $initial_pricing['variations']['pp_info']['days'] == 180) : ?>

                                            <p>Bis zur Gymiprüfung 2026</p>

                                        <?php else :*/ ?>

                                            <p><span class="plan-days"><?php echo isset($initial_pricing['variations']['pp_info']['days']) ? $initial_pricing['variations']['pp_info']['days'] : '0'; ?></span></p>
                                        <?php //endif; ?>
                                    </div>
                                    <hr>

                                    <button type="button" class="bricks-button bricks-background-primary btn-buy-now"
                                        <?php if (isset($initial_pricing['variations']['pp_info'])) : ?>
                                        data-proid="<?php echo esc_attr($initial_pricing['variations']['pp_info']['proid']); ?>"
                                        data-varid="<?php echo esc_attr($initial_pricing['variations']['pp_info']['varid']); ?>"
                                        <?php endif; ?>
                                        <?php if (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) : ?> disabled <?php endif; ?>>
                                        <?php echo (isset($initial_pricing['ppstatus']) && $initial_pricing['ppstatus'] == 0) ? "In Kürze verfügbar!" : "Jetzt kaufen!"; ?>
                                        <!-- In Kürze verfügbar! -->
                                    </button>
                                    <button 
                                    type="button"
                                    class="btn-link bricks-button bricks-background-primary" 
                                    onclick="openPricingPopup(this);" 
                                    data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                    data-pricing-description="<?php echo isset($initial_pricing['pp_info']) ? htmlspecialchars($initial_pricing['pp_info']) : ''; ?>">Mehr erfahren
                                    </button>  
                                </div>
                                <div class="cardright_area">
                                    <div class="plan-details ul-list-group">
                                        <div class="ul-list-group">
                                            <ul><?php echo isset($initial_pricing['ppf_html']) ? $initial_pricing['ppf_html'] : ''; ?></ul> 
                                        </div>
                                        <button 
                                            type="button"
                                            class="btn-link bricks-button bricks-background-primary" 
                                            onclick="openPricingPopup(this);" 
                                            data-pricing-title="Trainingsbereich + Prüfungssimulation"
                                            data-pricing-description="<?php echo isset($initial_pricing['pp_info']) ? htmlspecialchars($initial_pricing['pp_info']) : ''; ?>">Mehr erfahren
                                        </button>  
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>            
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.tab-btn').click(function () {
                $('.tab-btn.active').removeClass('active');
                $('.tab-content.active').removeClass('active');
                $(this).addClass('active');
                const tabId = $(this).data('tab');
                $('#' + tabId).addClass('active');
                let adays = parseInt(tabId);
                $('select[name="f-access-days"]').val(adays).trigger('change');
                $('#groups-filter').trigger('submit', { tab_change: true });
            });
        });
    </script>
    <!-- Course Pricing Section End -->

    <!-- Pricing Modal Popup -->
    <div id="pricing-popup" class="pricing-popup" style="display: none;">
        <div class="popup-content">            
            <button class="close-popup" onclick="closePricingPopup();">&times;</button>
            <div class="popup-body">                
                <div class="popup_content">
                    <div id="pricing-description" class="pricing-content"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal Popup -->
    <div id="preview-popup" class="preview-popup" style="display: none;">
        <div class="popup-content">
            <button class="close-popup" onclick="closePreviewPopup();">&times;</button>
            <div class="popup-body">
                <div class="popup_content">
                    <!-- Owl Carousel Slider -->
                    <div class="preview_owl_slider owl-carousel owl-theme"></div>
                    <h3 class="popup-title" id="popup-title"></h3>
                </div>
            </div>
        </div>
    </div>

    <script>
        /*// Popup slider   
        jQuery(document).ready(function($) { 
            $('.preview_owl_slider').owlCarousel({
                loop:true,
                margin:10, 
                items:1,
                nav:true,
                dots: false,
                autoHeight:true,
                navigationText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"]
            })
        })*/

        // Get popup element
        const popupPreview = document.getElementById('preview-popup');
        const popupTitle = document.getElementById('popup-title');

        function openPreviewPopup(button) {
            /*et title = button.getAttribute("data-group-title");
            let images = JSON.parse(button.getAttribute("data-slider-images"));*/
            // let slider = jQuery(".preview_owl_slider");

            // Set title dynamically
            // popupTitle.innerText = title;

            // Clear existing items & add new images
            /*slider.html("");
            images.forEach(img => {
                slider.append(`<div class="preview_owl_items"><div class="preview_images"><img src="${img}" alt="${title}"></div></div>`);
            });

            // Destroy and reinitialize the Owl Carousel
            slider.owlCarousel('destroy'); 
            slider.owlCarousel({ 
                loop: true, 
                margin: 10, 
                items: 1, 
                nav: true, 
                dots: false, 
                autoHeight: true,
                navigationText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"]
            });*/

            // Disable background scrolling
            document.body.style.overflow = 'hidden';

            // Show the popup            
            popupPreview.style.display = 'flex';
            setTimeout(() => { popupPreview.classList.add('show'); }, 10);
        }

        function closePreviewPopup() {            
            popupPreview.classList.remove('show');
            // Re-enable background scrolling
            document.body.style.overflow = '';            
            setTimeout(() => { popupPreview.style.display = 'none'; }, 300);
        }

        // Close modal on outside click
        document.addEventListener('click', function (e) {
            if (e.target.id === 'preview-popup') { closePreviewPopup(); }
        });


        const popupPricing = document.getElementById('pricing-popup');

        function openPricingPopup(button) {
            // Get data from button attributes
            const pricing_title       = button.getAttribute('data-pricing-title');
            const pricing_description = button.getAttribute('data-pricing-description');

            document.getElementById('pricing-description').innerHTML = pricing_description;

            // Disable background scrolling
            document.body.style.overflow = 'hidden';

            // Show the popup            
            popupPricing.style.display = 'flex';
            setTimeout(() => { popupPricing.classList.add('show'); }, 10);
        }

        function closePricingPopup() {            
            popupPricing.classList.remove('show');
            // Re-enable background scrolling
            document.body.style.overflow = '';            
            setTimeout(() => { popupPricing.style.display = 'none'; }, 300);
        }

        // Close modal on outside click
        document.addEventListener('click', function (e) {
            if (e.target.id === 'pricing-popup') { closePricingPopup(); }
        });
    </script>


<?php return ob_get_clean();
}
add_shortcode('courses_pricing', 'courses_pricing_shortcode');

/******  GROUP FIX FILTER  *******/
function groups_fix_filter_shortcode()
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

    <form id="groups-filter" class="common_filter_form groupf-form">    
        <div class="form-group" role="group"> 
            <?php  
            // Get the current language groups
            if( is_page('multicheck-vorbereitung') || is_page('basic-check-vorbereitung') ) :
            ?>
            
            <select id="form-field-f9fd5d" name="f-access-days" aria-label="days" class="select2-hidden-accessible" aria-hidden="true">
                <option value="30">30 Tage Zugang</option>                
                <option value="60">60 Tage Zugang</option>                
                <option value="90">90 Tage Zugang</option>
            </select>    

            <?php elseif ( is_page('stellwerktest-vorbereitung') ) : ?>

            <select id="form-field-f9fd5d" name="f-access-days" aria-label="days" class="select2-hidden-accessible" aria-hidden="true">
                <option value="30">30 Tage Zugang</option>                
                <option value="90">90 Tage Zugang</option>
                <option value="180" class="brickssss">180 Tage Zugang</option>                
            </select>

            <?php elseif ( is_page('ims-bms-fms-hms-vorbereitung') ) : ?>

            <select id="form-field-f9fd5d" name="f-access-days" aria-label="days" class="select2-hidden-accessible" aria-hidden="true">
                <option value="30">30 Tage Zugang</option>                
                <option value="90">90 Tage Zugang</option>
                <!-- <option value="180">Bis zur ZAP 2026</option> -->
                <option value="180">180 Tage Zugang</option>
            </select>

            <?php elseif ( is_page('gymi-vorbereitung')) : ?>   

            <select id="form-field-f9fd5d" name="f-access-days" aria-label="days" class="select2-hidden-accessible" aria-hidden="true">
                <option value="30">30 Tage Zugang</option>                
                <option value="90">90 Tage Zugang</option>
                <!-- <option value="180">Bis zur Gymiprüfung 2026</option> -->
                <option value="180">180 Tage Zugang</option>
            </select>

            <?php elseif ( is_page('probezeit-vorbereitung') ) : ?>
            
            <select id="form-field-f9fd5d" name="f-access-days" aria-label="days" class="select2-hidden-accessible" aria-hidden="true">
                <option value="30">30 Tage Zugang</option>                
                <option value="60">60 Tage Zugang</option>
                <option value="90">90 Tage Zugang</option>                
            </select>     
            
            <?php endif; ?>      
        </div>
        <?php //if (count($groups) > 1 ) { 
            if( is_page('basic-check-vorbereitung') ) :
        ?>
        <div class="form-group" role="group" <?php //if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>> 
            <select id="form-field-6f1af2" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                <?php 
                // Detect current language via Weglot
                $current_lang = function_exists('weglot_get_current_language') ? weglot_get_current_language() : '';                                        
                foreach ($groups as $gpkey => $gpvalue) {                        
                    // If language is French, skip the unwanted IDs
                    echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                }  
                ?> 
            </select>               
        </div>
        <?php else: ?>
        <div class="form-group" role="group" <?php if (count($groups) < 2 ) { echo "style='display:none;'"; } ?>> 
            <select id="form-field-6f1af2" name="f-group" aria-label="Multicheck" class="select2-hidden-accessible" aria-hidden="true">
                <?php 
                // IDs to skip in French
                $skip_ids_fr = [86408, 86685, 21702, 21707, 27434];
                // Detect current language via Weglot
                $current_lang = function_exists('weglot_get_current_language') ? weglot_get_current_language() : '';
                                        
                foreach ($groups as $gpkey => $gpvalue) {                        
                    // Skip the option with ID 25676
                    // if ($gpvalue->ID == 25676 || $gpvalue->ID == 25904 || $gpvalue->ID == 25905) {
                    //     continue; // Move to the next iteration
                    // }
                    // If language is French, skip the unwanted IDs
                    if ($current_lang === 'fr' && in_array($gpvalue->ID, $skip_ids_fr)) {
                        continue;
                    }
                    echo '<option value="'.$gpvalue->ID.'">'.$gpvalue->post_title.'</option>';
                }  
                ?> 
            </select>               
        </div>
        <?php endif; //} ?>
            
        <div class="form-group submit-button-wrapper">
            <button type="submit" class="bricks-button bricks-background-primary xl">
                <span class="text">Jetzt kaufen!</span>
                <!-- <span class="loading">
                    <svg version="1.1" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g stroke-linecap="round" stroke-width="1" stroke="currentColor" fill="none" stroke-linejoin="round"><path d="M0.927,10.199l2.787,4.151l3.205,-3.838"></path><path d="M23.5,14.5l-2.786,-4.15l-3.206,3.838"></path><path d="M20.677,10.387c0.834,4.408 -2.273,8.729 -6.509,9.729c-2.954,0.699 -5.916,-0.238 -7.931,-2.224"></path><path d="M3.719,14.325c-1.314,-4.883 1.969,-9.675 6.538,-10.753c3.156,-0.747 6.316,0.372 8.324,2.641"></path></g><path fill="none" d="M0,0h24v24h-24Z"></path></svg>
                </span>  -->            
            </button>
        </div>
    </form>

    <script type="text/javascript">       
    
        jQuery(document).ready(function($) {
            
            let hasAjaxRunOnce = false; // Flag to track if AJAX has already run

            // Handle form submission
            /*$('#groups-filter').on('submit', function (e) {
                e.preventDefault(); // Prevent default form submission

                let groupId = $('#form-field-6f1af2').val(); // f-group from main form
                let groupTitle = $('#form-field-6f1af2 option:selected').text(); // Get the selected option text

                $('#form-field-plan-group').val(groupId).trigger('change'); // Sync with second form\                

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle); // Pass groupTitle to handleAjaxCall

                var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
                if (targetSection.length) {
                    const offset = window.innerWidth < 768 ? 300 : 30;
                    $('html, body').animate(
                        {
                            scrollTop: targetSection.offset().top - offset // Adjust offset as needed
                        },
                        800 // Smooth scrolling duration (800ms)
                    );
                }

            });

            // Handle f-group dropdown change in the second form
            $('#form-field-plan-group').on('change', function () {
                let groupId = $(this).val(); // Get new f-group value
                let groupTitle = $(this).find('option:selected').text(); // Get the selected option text

                // Sync change to the other form
                $('#form-field-6f1af2').val(groupId).trigger('change');

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle); // Pass groupTitle to handleAjaxCall

            });

            // Function to update form data dynamically
            function updateFormData() {
                let formData = $('#groups-filter').serializeArray(); // Serialize form data into an array
                let groupId = $('#form-field-6f1af2').val(); // Ensure latest f-group value is used

                let updatedFormData = formData.filter(item => item.name !== 'f-group'); // Remove old f-group
                updatedFormData.push({ name: 'f-group', value: groupId }); // Add updated f-group

                return $.param(updatedFormData);
            }

            // Function to handle AJAX calls
            function loadInitialData() {
                let formData = updateFormData(); // Get the initial form data
                let groupTitle = $('#form-field-6f1af2 option:selected').text(); // Get initial group title
                handleAjaxCall(formData, groupTitle); // Pass groupTitle to handleAjaxCall          
            }

            // Call the function to load initial data on page load
            loadInitialData();*/

            // Handle form submission ============================================
            /*$('#groups-filter').on('submit', function (e) {
                e.preventDefault(); // Prevent default form submission
                handleGroupFilterChange();
            });

            // Handle changes in group or access-days dropdowns inside #groups-filter
            $('#groups-filter').on('change', '#form-field-6f1af2, select[name="f-access-days"]', function () {
                handleGroupFilterChange();
            });

            // Also handle f-group dropdown change in the second form
            $('#form-field-plan-group').on('change', function () {
                let groupId = $(this).val(); // Get new f-group value
                let groupTitle = $(this).find('option:selected').text(); // Get the selected option text

                // Sync change to the other form
                $('#form-field-6f1af2').val(groupId).trigger('change');

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle); // Pass groupTitle to handleAjaxCall
            });

            // Function to handle all group filter actions (shared by submit + change)
            function handleGroupFilterChange() {
                let groupId = $('#form-field-6f1af2').val(); // f-group from main form
                let groupTitle = $('#form-field-6f1af2 option:selected').text(); // Get selected option text
                let days = $('select[name="f-access-days"]').val(); // Get access days value

                // Sync with second form
                $('#form-field-plan-group').val(groupId).trigger('change.select2');

                // Prepare and send AJAX request
                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle, days); // Pass days as an extra argument if needed

                // Scroll to target section
                var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
                if (targetSection.length) {
                    const offset = window.innerWidth < 768 ? 300 : 30;
                    $('html, body').animate(
                        { scrollTop: targetSection.offset().top - offset },
                        800 // Smooth scroll duration
                    );
                }
            }

            // Function to update form data dynamically
            function updateFormData() {
                let formData = $('#groups-filter').serializeArray(); // Serialize form data
                let groupId = $('#form-field-6f1af2').val(); // Ensure latest f-group value is used

                let updatedFormData = formData.filter(item => item.name !== 'f-group'); // Remove old f-group
                updatedFormData.push({ name: 'f-group', value: groupId }); // Add updated f-group

                return $.param(updatedFormData);
            }

            // Function to handle AJAX calls
            function loadInitialData() {
                let formData = updateFormData(); // Get the initial form data
                let groupTitle = $('#form-field-6f1af2 option:selected').text(); // Get initial group title
                let days = $('select[name="f-access-days"]').val(); // Get initial access days
                handleAjaxCall(formData, groupTitle, days); // Pass groupTitle + days
            }*/
            // Handle form submission ============================================

            let preventScroll = false; // ✅ Global flag

            // Handle form submission
            $('#groups-filter').on('submit', function (e) {
                e.preventDefault();
                handleGroupFilterChange();
            });

            // Handle changes inside #groups-filter
            $('#groups-filter').on('change', '#form-field-6f1af2, select[name="f-access-days"]', function () {
                handleGroupFilterChange();
            });

            // Change handler for #form-field-plan-group
            $('#form-field-plan-group').on('change', function () {
                preventScroll = false; // ✅ Allow scroll

                let groupId = $(this).val();
                let groupTitle = $(this).find('option:selected').text();

                // Sync values to main form + second form
                $('#form-field-6f1af2').val(groupId).trigger('change');
                $('#form-field-plan-groups').val(groupId).trigger('change.select2');

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle);
            });

            // Change handler for #form-field-plan-groups (NO SCROLL)
            $('#form-field-plan-groups').on('change', function () {
                preventScroll = true; // ✅ Don't scroll for this one

                let groupId = $(this).val();
                let groupTitle = $(this).find('option:selected').text();

                // Sync values to main form + second form
                $('#form-field-6f1af2').val(groupId).trigger('change');
                $('#form-field-plan-group').val(groupId).trigger('change.select2');

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle);
            });

            // Shared group filter handler
            function handleGroupFilterChange() {
                let groupId = $('#form-field-6f1af2').val();
                let groupTitle = $('#form-field-6f1af2 option:selected').text();
                let days = $('select[name="f-access-days"]').val();

                // Sync with the two select boxes
                $('#form-field-plan-group').val(groupId).trigger('change.select2');
                $('#form-field-plan-groups').val(groupId).trigger('change.select2');

                let formData = updateFormData();
                handleAjaxCall(formData, groupTitle, days);

                // ✅ Skip scroll if triggered by #form-field-plan-groups
                if (preventScroll) {
                    preventScroll = false; // Reset flag
                    return; // Stop scroll
                }

                // ✅ Scroll for all other triggers
                var targetSection = $('#brxe-zxjfuz'); // Replace with actual ID
                if (targetSection.length) {
                    const offset = window.innerWidth < 768 ? 300 : 30;
                    $('html, body').animate(
                        { scrollTop: targetSection.offset().top - offset },
                        800
                    );
                }
            }

            // Function to update form data dynamically
            function updateFormData() {
                let formData = $('#groups-filter').serializeArray();
                let groupId = $('#form-field-6f1af2').val();

                let updatedFormData = formData.filter(item => item.name !== 'f-group');
                updatedFormData.push({ name: 'f-group', value: groupId });

                return $.param(updatedFormData);
            }

            // Load initial data
            function loadInitialData() {
                let formData = updateFormData();
                let groupTitle = $('#form-field-6f1af2 option:selected').text();
                let days = $('select[name="f-access-days"]').val();

                handleAjaxCall(formData, groupTitle, days);
            }

            loadInitialData();

            // Function to handle AJAX calls
            function handleAjaxCall(formData, groupTitle) {
                // Helper: read cookie value
                function getCookie(name) {
                    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                    return match ? match[2] : null;
                }

                // Helper: delete cookie
                function eraseCookie(name) {
                    document.cookie = name + '=; Max-Age=0; path=/';
                }

                // ✅ Detect if we’re on Gymi or IMS/BMS/FMS/HMS page
                const pathname = window.location.pathname;
                const isGymiPage = pathname.includes('/gymi-vorbereitung/');
                const isImsBmsPage = pathname.includes('/ims-bms-fms-hms-vorbereitung/');
                const storedGroup = getCookie('selectedParentGroup');

                if ((isGymiPage || isImsBmsPage) && storedGroup) {
                    const groupSelect = $('#form-field-6f1af2');

                    if (groupSelect.length) {
                        // ✅ Set dropdown to cookie value
                        groupSelect.val(storedGroup).trigger('change.select2');
                        $('#form-field-plan-group').val(storedGroup).trigger('change.select2');

                        console.log('✅ Auto-selected parent group from cookie:', storedGroup);

                        // ✅ Erase cookie after use
                        eraseCookie('selectedParentGroup');

                        // ✅ Trigger AJAX automatically for this group
                        const groupTitle = groupSelect.find('option:selected').text();
                        const formData = updateFormData();
                        handleAjaxCall(formData, groupTitle);

                        // ✅ Scroll to target section
                        var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
                        if (targetSection.length) {
                            const offset = window.innerWidth < 768 ? 300 : 30;
                            $('html, body').animate(
                                { scrollTop: targetSection.offset().top - offset },
                                800 // Smooth scroll duration
                            );
                        }

                        return; // Exit to prevent duplicate AJAX
                    }
                }


                let groupId = $('#form-field-6f1af2').val();
                let days = $('select[name="f-access-days"]').val();
                $('.tabs .tab-btn-wrap .tab-btn').removeClass('active');
                $('button[data-tab="'+days+'-tage"]').addClass('active');

                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: { 
                        action: 'filter_products', 
                        data: formData,
                        group_title: groupTitle // Add group title to the data being sent
                    },
                    beforeSend: function () {
                        $('.site-loader').show(); 
                    },
                    // success: function (response) {
                    //     console.log("AJAX Success: ", response); 

                    //     let data = response.data;

                    //     $('.site-loader').hide(); 
                    //     $('.traning-plan .plan-details .ul-list-group ul').html(data.tf_html);
                    //     $('.simulation-plan .plan-details .ul-list-group ul').html(data.sf_html);
                    //     $('.premium-plan .plan-details .ul-list-group ul').html(data.pf_html);

                    //     if (typeof data.variations === 'object' && Object.keys(data.variations).length > 0) {
                    //         $('.traning-plan .plan-price h4 span.plan-price').text(data.variations.t_info.price);
                    //         $('.simulation-plan .plan-price h4 span.plan-price').text(data.variations.s_info.price);
                    //         $('.premium-plan .plan-price h4 span.plan-price').text(data.variations.p_info.price);
                    //     } else {
                    //         $('.traning-plan .plan-price h4 span.plan-price, .simulation-plan .plan-price h4 span.plan-price, .premium-plan .plan-price h4 span.plan-price').text('0');
                    //     }

                    //     if (hasAjaxRunOnce) {
                    //         let targetSection = $('#brxe-zxjfuz');
                    //         if (targetSection.length) {
                    //             $('html, body').animate({
                    //                 scrollTop: targetSection.offset().top - 30
                    //             }, 800);
                    //         }
                    //     }

                    //     hasAjaxRunOnce = true;
                    // },
                    success: function (response) {
                        // console.log("AJAX Success: ", response); // Log the response data
                        let data = response.data;

                        // Update the title
                        // $('#popup-title').text(data.pptitle); // Update the title element
                        $('#popup-title').text(groupTitle); // Use group title instead of data.pptitle

                        // Hide the button if pptitle is "ZAP-IMS"
                       // Show all plan buttons first
                        $('button[data-group-title]').show();

                        // Now selectively hide if it's "ZAP-IMS"
                        if (data.pptitle === 'ZAP-IMS') {
                            $(`button[data-group-title="ZAP-IMS"]`).hide();
                        }

                        // Update the slider
                        let slider = $('.preview_owl_slider'); // Assuming you have a slider element for images
                        slider.html(""); // Clear existing items
                        $('#preview-popup-images').show(); // Hide the button if no images are found
                        if (data.ppsimages && data.ppsimages.length > 0) {
                            data.ppsimages.forEach(img => {
                                slider.append(`<div class="preview_owl_items"><div class="preview_images"><img src="${img}" alt="${groupTitle}" class="zoom" data-magnify-src="${img}" /></div></div>`);
                            });

                            // Reinitialize the Owl Carousel
                            slider.owlCarousel('destroy'); 
                            slider.owlCarousel({ 
                                loop: true, 
                                margin: 10, 
                                items: 1, 
                                nav: true, 
                                dots: false, 
                                autoHeight: true,
                                navigationText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
                                // onTranslated: function() {                                
                                //     $zoom.destroy().magnify();
                                // }
                            });                            

                            //var $zoom = $('.zoom').magnify();

                            // Show the preview popup after updating the content
                            $('#preview-popup-images')[0]; // Pass the button element to the function
                        } else {
                            //$('#preview-popup-images').hide(); // Hide the button if no images are found
                        }

                        // Handle the AJAX response data here (update UI elements, pricing, etc.)
                        let tprice = $('.traning-plan .plan-price h4 span.plan-price');
                        let sprice = $('.simulation-plan .plan-price h4 span.plan-price');
                        let pprice = $('.premium-plan .plan-price h4 span.plan-price');
                        let ppprice = $('.pro-premium-plan .plan-price h4 span.plan-price');
                        let tdays = $('.traning-plan .plan-price p span.plan-days');
                        let sdays = $('.simulation-plan .plan-price p span.plan-days');
                        let pdays = $('.premium-plan .plan-price p span.plan-days');
                        let ppdays = $('.pro-premium-plan .plan-price p span.plan-days');

                        var tbuybtn = $('.traning-plan .plan-details .btn-buy-now');
                        var sbuybtn = $('.simulation-plan .plan-details .btn-buy-now');
                        var pbuybtn = $('.premium-plan .plan-details .btn-buy-now');
                        var ppbuybtn = $('.pro-premium-plan .cardleft_area .btn-buy-now');

                        $('.site-loader').hide(); // Hide loader after request completion
                        // Update the data-purchase-status attribute
                        $('#all-tage').attr('data-purchase-status', data.ppstatus);
                        $('.traning-plan .plan-details .ul-list-group ul').html(data.tf_html);
                        $('.simulation-plan .plan-details .ul-list-group ul').html(data.sf_html);
                        $('.premium-plan .plan-details .ul-list-group ul').html(data.pf_html);
                        $('.pro-premium-plan .plan-details .ul-list-group ul').html(data.ppf_html);
                        $('.traning-plan .plan-details .btn-link').attr('data-pricing-description', data.t_info);
                        $('.simulation-plan .plan-details .btn-link').attr('data-pricing-description', data.s_info);
                        $('.premium-plan .plan-details .btn-link').attr('data-pricing-description', data.p_info);
                        $('.pro-premium-plan .plan-details .btn-link').attr('data-pricing-description', data.pp_info);

                        console.log(typeof data.variations);
                        console.log("typeof data.variations");

                        console.log(data.variations);
                        console.log("data.variations");

                        console.log(Object.keys(data.variations).length);
                        console.log("Object.keys(data.variations).length");

                        console.log(data.ppstatus);
                        console.log("data.ppstatus");

                        // Translation dictionary
                        var texts = {
                            soon: {
                                de: "In Kürze verfügbar!",
                                fr: "Bientôt disponible !",
                                it: "Disponibile a breve!",
                                en: "Coming soon!"
                            },
                            buy: {
                                de: "Jetzt kaufen!",
                                fr: "Profiter maintenant !",
                                it: "Acquista ora!",
                                en: "Buy now!"
                            },
                            daysAccess: {
                                de: "Tage Zugang",
                                fr: "jours d'accès",
                                it: "giorni di accesso",
                                en: "days access"
                            },
                            days: {
                                de: "Tage",
                                fr: "jours",
                                it: "giorni",
                                en: "days"
                            },
                            zap2026: {
                                de: "Bis zur ZAP 2026",
                                fr: "Jusqu’au ZAP 2026",
                                it: "Fino allo ZAP 2026",
                                en: "Until ZAP 2026"
                            },
                            gymi2026: {
                                de: "Bis zur Gymiprüfung 2026",
                                fr: "Jusqu’à l’examen du gymnase en 2026",
                                it: "Fino all’esame Gymi del 2026",
                                en: "Until the Gymi exam 2026"
                            },
                            // Short form (for <p>)
                            lesson: {
                                de: ["Nachhilfe-Lektion", "Nachhilfe-Lektionen"],
                                fr: ["leçon de tutorat", "leçons de tutorat"],
                                it: ["lezione di tutoraggio", "lezioni di tutoraggio"],
                                en: ["tutoring lesson", "tutoring lessons"]
                            },
                            // Full form (for <ul><li>)
                            lessonFull: {
                                de: [
                                    "Nachhilfe Lektion via Zoom oder vor Ort mit einer Fachlehrperson",
                                    "Nachhilfe Lektionen via Zoom oder vor Ort mit einer Fachlehrperson"
                                ],
                                fr: [
                                    "Leçon de tutorat via Zoom ou sur place avec un enseignant spécialisé",
                                    "Leçons de tutorat via Zoom ou sur place avec un enseignant spécialisé"
                                ],
                                it: [
                                    "Lezione di tutoraggio via Zoom o in loco con un insegnante specializzato",
                                    "Lezioni di tutoraggio via Zoom o in loco con un insegnante specializzato"
                                ],
                                en: [
                                    "Tutoring lesson via Zoom or on-site with a specialized teacher",
                                    "Tutoring lessons via Zoom or on-site with a specialized teacher"
                                ]
                            },
                            // Essay correction (singular/plural template)
                            essayCorrection: {
                                de: [
                                    "Manuelle Korrektur eines Aufsatzes mit detailliertem Feedback",
                                    "Manuelle Korrektur von {count} Aufsätzen mit detailliertem Feedback"
                                ],
                                fr: [
                                    "Correction manuelle d’une rédaction avec un retour détaillé",
                                    "Correction manuelle de {count} rédactions avec un retour détaillé"
                                ],
                                it: [
                                    "Correzione manuale di un tema con feedback dettagliato",
                                    "Correzione manuale di {count} temi con feedback dettagliato"
                                ],
                                en: [
                                    "Manual correction of one essay with detailed feedback",
                                    "Manual correction of {count} essays with detailed feedback"
                                ]
                            }
                        };

                        // Detect Weglot language or fallback
                        var lang = 'en'; // Default to English

                        // Method 2: Check HTML lang attribute
                        if (lang === 'en') {
                            var htmlLang = document.documentElement.lang;
                            if (htmlLang) {
                                // Extract language code (e.g., 'fr' from 'fr-FR')
                                lang = htmlLang.split('-')[0].toLowerCase();
                            }
                        }

                        // Ensure the detected language is supported, otherwise fallback to English
                        if (!texts.soon[lang]) {
                            console.warn('Language not supported, falling back to English');
                            lang = 'en';
                        }

                        console.log('Detected language:', lang);
                       
                        // Check if pro-premium-plan exists on the page
                        const hasProPremiumPlan = $('.pro-premium-plan').length > 0;         
                        // Update <p> description (translated!)
                        if (hasProPremiumPlan) {
                            // Map days → lessons count
                            let lessonsCount = 0;
                            if (days == 30) lessonsCount = 1;
                            else if (days == 90) lessonsCount = 3;
                            else if (days == 180) lessonsCount = 6;

                            // Pick correct singular/plural
                            let lessonText = lessonsCount + " " + (lessonsCount === 1 ? texts.lesson[lang][0] : texts.lesson[lang][1]);
                            let tutoringText = lessonsCount + " " + (lessonsCount === 1 ? texts.lessonFull[lang][0] : texts.lessonFull[lang][1]);

                            // Pick correct essay correction
                            let essayText = (lessonsCount === 1)
                                ? texts.essayCorrection[lang][0]
                                : texts.essayCorrection[lang][1].replace('{count}', lessonsCount);

                            // Update <p> description
                            const planDescription = $('.pro-premium-plan .plan-title p');
                            if (planDescription.length) {
                                planDescription.html(planDescription.html().replace(
                                    /<b>.*?<\/b>/,
                                    `<b>${lessonText}</b>`
                                ));
                            }

                            // Update <ul><li> list (1st = tutoring, 2nd = essay correction)
                            const featureList = $('.pro-premium-plan .plan-details .ul-list-group ul li');
                            if (featureList.length >= 2) {
                                featureList.eq(0).text(tutoringText);
                                if (groupId != '80844') {
                                    featureList.eq(2).text(essayText);
                                }
                            }
                        }

                        if (typeof data.variations === 'object' && data.variations !== null && Object.keys(data.variations).length > 0 && data.ppstatus) {
                            
                            tprice.text(data.variations.t_info.price);
                            sprice.text(data.variations.s_info.price);
                            pprice.text(data.variations.p_info.price);
        
                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppprice.text(data.variations.pp_info.price);
                            }

                            /*if ((groupId == '32332' || groupId == '86815') && days == 180) {

                                tdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                sdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                pdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                // ppdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                // Only update pro-premium if it exists
                                if (hasProPremiumPlan) {
                                    ppdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                }

                            } else if ( groupId == '86719' || groupId == '86718' || groupId == '86716' ) {

                                if (days == 180 ) {
                                    if (groupId == '86719' || groupId == '86718') { 
                                        tdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        sdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        pdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        ppdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                    } else if (groupId == '86716')  {
                                        tdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        sdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        pdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        // ppdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                    }
                                } else {
                                    sdays.html(`<span class='plan-days'>${data.variations.s_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                    pdays.html(`<span class='plan-days'>${data.variations.p_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                    tdays.html(`<span class='plan-days'>${data.variations.t_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);                                
                                    // ppdays.html(`<span class='plan-days'>${data.variations.pp_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);                                
                                }

                                jQuery('.page-ims-bms-fms-hms-vorbereitung button[data-tab="180-tage"]').text(texts.zap2026[lang] || texts.zap2026['en']);

                            } else {*/

                                jQuery('.page-ims-bms-fms-hms-vorbereitung button[data-tab="180-tage"]').text(`180 ${texts.days[lang] || texts.days['en']}`);

                                sdays.html(`<span class='plan-days'>${data.variations.s_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                pdays.html(`<span class='plan-days'>${data.variations.p_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                tdays.html(`<span class='plan-days'>${data.variations.t_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                // ppdays.html(`<span class='plan-days'>${data.variations.pp_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);

                                // Only update pro-premium if it exists
                                if (hasProPremiumPlan) {
                                    ppdays.html(`<span class='plan-days'>${data.variations.pp_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                }
                            // }

                            tbuybtn
                                .attr('data-proid', data.variations.t_info.proid)
                                .attr('data-varid', data.variations.t_info.varid);
                            sbuybtn
                                .attr('data-proid', data.variations.s_info.proid)
                                .attr('data-varid', data.variations.s_info.varid);
                            pbuybtn
                                .attr('data-proid', data.variations.p_info.proid)
                                .attr('data-varid', data.variations.p_info.varid);
                            // ppbuybtn
                            //     .attr('data-proid', data.variations.pp_info.proid)
                            //     .attr('data-varid', data.variations.pp_info.varid);

                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppbuybtn
                                    .attr('data-proid', data.variations.pp_info.proid)
                                    .attr('data-varid', data.variations.pp_info.varid);
                            }

                            // $(tbuybtn).add(sbuybtn).add(pbuybtn).prop('disabled', false).text("Jetzt kaufen!");
                            // $(tbuybtn).add(sbuybtn).add(pbuybtn).prop('disabled', false).text(texts.buy[lang] || texts.buy['en']);
                            // For enabled "Buy Now" button
                            $(tbuybtn).add(sbuybtn).add(pbuybtn)
                                .prop('disabled', false)
                                .text(texts.buy[lang] || texts.buy['en']);

                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppbuybtn
                                    .prop('disabled', false)
                                    .text(texts.buy[lang] || texts.buy['en']);
                            }
                        } else {
                            
                            tprice.text(data.variations.t_info.price);
                            sprice.text(data.variations.s_info.price);
                            pprice.text(data.variations.p_info.price);
                            // ppprice.text(data.variations.pp_info.price);

                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppprice.text(data.variations.pp_info.price);
                            }

                            /*if ((groupId == '32332' || groupId == '86815') && days == 180) {

                                tdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                sdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                pdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                // ppdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                // Only update pro-premium if it exists
                                if (hasProPremiumPlan) {
                                    ppdays.html(`${texts.gymi2026[lang] || texts.gymi2026['en']}<br><span class="extentions-days" style="color: #F84F39 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                }

                            } else if (groupId == '86719' || groupId == '86718' || groupId == '86716') {

                                if (days == 180 ) {
                                    if (groupId == '86719' || groupId == '86718') { 
                                        tdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        sdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        pdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                        // ppdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">04.03.2026</span>`);
                                    } else if (groupId == '86716')  {
                                        tdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        sdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        pdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                        // ppdays.html(`${texts.zap2026[lang] || texts.zap2026['en']}<br><span class="extentions-days" style="color: #2590F2 !important;display: inline-block;margin-top: 8px;">02.03.2026</span>`);
                                    }
                                }

                                jQuery('.page-ims-bms-fms-hms-vorbereitung button[data-tab="180-tage"]').text(texts.zap2026[lang] || texts.zap2026['en']);

                            } else {*/

                                jQuery('.page-ims-bms-fms-hms-vorbereitung button[data-tab="180-tage"]').text(`180 ${texts.days[lang] || texts.days['en']}`);
                                
                                sdays.html(`<span class='plan-days'>${data.variations.s_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                pdays.html(`<span class='plan-days'>${data.variations.p_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                tdays.html(`<span class='plan-days'>${data.variations.t_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                // ppdays.html(`<span class='plan-days'>${data.variations.pp_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);

                                // Only update pro-premium if it exists
                                if (hasProPremiumPlan) {
                                    ppdays.html(`<span class='plan-days'>${data.variations.pp_info.days}</span> ${texts.daysAccess[lang] || texts.daysAccess['en']}`);
                                }
                            // }

                            tbuybtn
                                .attr('data-proid', data.variations.t_info.proid)
                                .attr('data-varid', data.variations.t_info.varid);
                            sbuybtn
                                .attr('data-proid', data.variations.s_info.proid)
                                .attr('data-varid', data.variations.s_info.varid);
                            pbuybtn
                                .attr('data-proid', data.variations.p_info.proid)
                                .attr('data-varid', data.variations.p_info.varid);
                            /*ppbuybtn
                                .attr('data-proid', data.variations.pp_info.proid)
                                .attr('data-varid', data.variations.pp_info.varid);*/

                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppbuybtn
                                    .attr('data-proid', data.variations.pp_info.proid)
                                    .attr('data-varid', data.variations.pp_info.varid);
                            }
                            // $(tprice).add(sprice).add(pprice).add(tdays).add(sdays).add(pdays).text('0');
                            // $(tbuybtn).add(sbuybtn).add(pbuybtn).prop('disabled', true).text("In Kürze verfügbar!");
                            // $(tbuybtn).add(sbuybtn).add(pbuybtn).prop('disabled', true).text(texts.soon[lang] || texts.soon['en']);
                            // For disabled "Coming Soon" button
                            $(tbuybtn).add(sbuybtn).add(pbuybtn)
                                .prop('disabled', true)
                                .text(texts.soon[lang] || texts.soon['en']);

                            // Only update pro-premium if it exists
                            if (hasProPremiumPlan) {
                                ppbuybtn
                                    .prop('disabled', true)
                                    .text(texts.soon[lang] || texts.soon['en']);
                            }
                        }

                        // Perform smooth scroll only after the first AJAX call
                        // if (hasAjaxRunOnce) {
                        //     var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
                        //     if (targetSection.length) {
                        //         $('html, body').animate(
                        //             {
                        //                 scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
                        //             },
                        //             800 // Smooth scrolling duration (800ms)
                        //         );
                        //     }
                        // }

                        // hasAjaxRunOnce = true; // Set the flag after the first call
                        // $('#groups-filter').on('submit', function (e, args) {
                        //     if (args && args.tab_change) {
                        //         return;
                        //     }

                        //     e.preventDefault(); // Prevent default form submission

                        //     var targetSection = $('#brxe-zxjfuz'); // Replace with your actual section ID
                        //     if (hasAjaxRunOnce) {
                        //         if (targetSection.length) {
                        //             $('html, body').animate(
                        //                 {
                        //                     scrollTop: targetSection.offset().top - 30 // Adjust offset as needed
                        //                 },
                        //                 800 // Smooth scrolling duration (800ms)
                        //             );
                        //         }
                        //     }
                        //     hasAjaxRunOnce = true; // Set the flag after the first call
                        // });

                    },
                    error: function (error) {
                        //console.error('AJAX Error:', error);
                        $('.site-loader').hide();
                    }
                });
            }


          /*  jQuery('body').on('click', 'button.btn-buy-now', function (e) {
                // Add spinner to the button
                var buttonText = jQuery(this).html();
                jQuery(this).html('<span class="spinner"></span>');
                
                // You may want to remove the spinner when the process completes
                // This would typically be done in your AJAX success callback
                // or wherever your purchase process completes
                
                var productId = jQuery(this).data('proid');
                var groupId   = jQuery(this).data('varid');

                var selang = jQuery('html').attr('lang');
                var checkoutUrl = '';
                
                if (selang == 'de-DE') {
                    checkoutUrl = '/zur-kasse';                             
                } else { 
                    checkoutUrl = '/checkout';
                }         

                checkoutUrl = '/zur-kasse';
                

                // Create direct add-to-cart URL that goes straight to checkout
                var addToCartUrl = '/<?php echo esc_attr( weglot_get_current_language() ); ?>' + checkoutUrl + '/?add-to-cart=' + productId + '&variation_id=' + groupId;
                
                console.log(addToCartUrl);

                // Redirect to the checkout page with the product added
                window.location.href = addToCartUrl;
            });*/

            jQuery('body').on('click', 'button.btn-buy-now', function (e) {
                e.preventDefault();
                
                // Add spinner to the button and disable it
                var $button = jQuery(this);
                var buttonText = $button.html();
                $button.html('<span class="spinner"></span>').prop('disabled', true);
                
                var productId = $button.data('proid');
                var groupId = $button.data('varid');
                var currentLang = jQuery('html').attr('lang') || 'de';
                
                // Build the URL without quantity parameter
                var addToCartUrl = '/zur-kasse/?add-to-cart=' + productId + '&variation_id=' + groupId;
                
                // Add language prefix for non-German languages
                if (currentLang !== 'de' && currentLang !== 'de-DE') {
                    addToCartUrl = '/' + currentLang + addToCartUrl;
                }
                
                console.log('Redirecting to:', addToCartUrl);
                
                // Redirect to the checkout page
                window.location.href = addToCartUrl;
                
                return false;
            });
        });
    </script>

    <?php return ob_get_clean();
}

add_shortcode('fix_groups_filter', 'groups_fix_filter_shortcode');

function filter_products() {
    parse_str($_POST['data'], $form_data);
    $category = sanitize_text_field($form_data['f-access-days']);
    $groupid  = sanitize_text_field($form_data['f-group']);

    $traningf    = get_field('traning_features', $groupid);
    $primiumf    = get_field('primium_features', $groupid);
    $simulationf = get_field('simulation_features', $groupid);
    $propf = get_field('pro_premium_features', $groupid);

    $ppstatus    = get_field('package_purchase_status', $groupid); // Get package purchase status

    $pptitle     = get_the_title($groupid);
    $ppsimages   = get_field('package_popup_slider_images', $groupid);

    $tfhtml = ''; foreach ($traningf as $tfkey => $tfvalue) {    $tfhtml .= "<li>".$tfvalue['feature_name']."</li>";  }
    $pfhtml = ''; foreach ($primiumf as $pfkey => $pfvalue) {    $pfhtml .= "<li>".$pfvalue['feature_name']."</li>";  }
    $sfhtml = ''; foreach ($simulationf as $sfkey => $sfvalue) { $sfhtml .= "<li>".$sfvalue['feature_name']."</li>"; }
    $ppfhtml = ''; foreach ($propf as $ppfkey => $ppfvalue) { $ppfhtml .= "<li>".$ppfvalue['feature_name']."</li>"; }

    $traning_info    = preg_replace('/\s(id|class)="[^"]*"/i', '', get_field('traning_further_information', $groupid));
    $primium_info    = preg_replace('/\s(id|class)="[^"]*"/i', '', get_field('primium_further_information', $groupid));
    $simulation_info = preg_replace('/\s(id|class)="[^"]*"/i', '', get_field('simulation_further_information', $groupid));
    $pro_premium_info = preg_replace('/\s(id|class)="[^"]*"/i', '', get_field('pro_premium_further_information', $groupid));

     /* Get product and attribute ids*/
    $current_lang = apply_filters( 'wpml_current_language', NULL );
    $productids   = get_product_ids_by_related_group($groupid, $category, $current_lang);    
    $prodcts      = array();

    foreach ($productids as $pid => $varids) {                

        foreach ($varids as $variationid) {
            
            // Get the variation object
            $variation_obj = wc_get_product($variationid);
            $group_ids = get_post_meta($variationid, '_related_group', true);

            // Check if the variation is in the current language
            $current_language_variation_id = icl_object_id($variationid, 'product_variation', false, ICL_LANGUAGE_CODE);
            
            if ($current_language_variation_id) {

                $parent_p  = wc_get_product($pid);
                $ppterms   = get_the_terms($pid, 'product_cat'); 
                $days_cat  = $ppterms['0']->name;              
                $var_att   = $variation_obj->get_variation_attributes();
                $term_slug = $var_att['attribute_pa_packages'];
                $varprice  = $variation_obj->get_price();

                if ($term_slug == 'trainingsbereich-de'    || $term_slug == 'training' ) { $term_slug = 't_info'; }
                if ($term_slug == 'pruefungssimulation-de' || $term_slug == 'simulation' ) { $term_slug = 's_info'; }
                if ($term_slug == 'premium-package-de'     || $term_slug == 'premium-package' ) { $term_slug = 'p_info'; }
                if ($term_slug == 'pro-premium-package-de' || $term_slug == 'pro-premium-package' ) { $term_slug = 'pp_info'; }

                $prodcts[$term_slug] = array('proid' => $pid, 'varid' => $variationid, 'price' => $varprice, 'days' => $days_cat);
            }
        }
    }

    return ['tf_html' => $tfhtml, 'pf_html' => $pfhtml, 'sf_html' => $sfhtml, 'ppf_html' => $ppfhtml, 't_info' => $traning_info, 'p_info' => $primium_info, 's_info' => $simulation_info, 'pp_info' => $pro_premium_info, 'variations' => $prodcts, 'ppstatus' => $ppstatus, 'pptitle' => $pptitle, 'ppsimages' => $ppsimages];
}


function filter_products_ajax_handler() {
    wp_send_json_success( filter_products() );  

    wp_reset_postdata();
    wp_die();
}
add_action('wp_ajax_filter_products', 'filter_products_ajax_handler');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_ajax_handler');


/*--- GET PRODUCT IDs By GROUP ID ---*/
function get_product_ids_by_related_group($group_id, $category_slug, $language_code) {

    $parent_ids = array();
    
    // Get parent product IDs from the 'mc_products' ACF field
    $parent_product_ids = get_field('mc_products', $group_id);

    if (!empty($parent_product_ids)) {
        foreach ($parent_product_ids as $parent_id) {
            // Get all variations for the parent product
            $product = wc_get_product($parent_id);
            if ($product && $product->is_type('variable')) {
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    // Check if the parent product has the specified category and not the 'renew' category
                    if (!has_term('renew', 'product_cat', $parent_id) && has_term($category_slug, 'product_cat', $parent_id)) {
                        $parent_ids[$parent_id][] = $variation_id;
                    }
                }
            }
        }
    }

    return !empty($parent_ids) ? $parent_ids : false;
}

/*** CHECK ALREADY PURCHASE ***/
function check_group_already_activated($group_ids='')
{
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $orders = wc_get_orders( array(
        'customer_id' => $user_id,
        'status' => 'completed', // You can change this to any status you want
    ) );
    $groupid = $group_ids['0'];
    $result = check_is_expired_completely($orders, $groupid);
    
    return $result;
}

/*--- END CHECK COURSE EXPIRY CRON ---*/
function check_is_expired_completely($orders, $mygroupid)
{   
    $group_status = array();
    foreach ($orders as $order) {

        $items = $order->get_items();

        foreach ($items as $item) {
            
            $item_id = $item->get_variation_id();
            $group_ids = get_post_meta($item_id, '_related_group', true);
            $current_date = date('d-m-Y');
            $expire_date = $item->get_meta('group_expiry_date', true);
            
            if (!empty($group_ids) && !empty($expire_date)) {
             
                if (count($group_ids) >= 1) {
                    
                    $groupid = $group_ids['0'];

                    if ($mygroupid == $groupid) {

                        // Convert both dates to timestamps
                        $current_timestamp = strtotime($current_date);
                        $expire_timestamp = strtotime($expire_date);

                        if ($expire_timestamp <= $current_timestamp) {
                            $group_status[] = 'false';
                        } else { 
                            $group_status[] = 'true';
                        }
                    }
                }
            }
        }
    }

    if (!in_array('true', $group_status)) 
    {
        return true;

    } else {

        return false;
    }
}

/*** HANDLE BUY NOW BUTTON ***/
function custom_buy_now()
{
    if (!isset($_POST['product_id'])) {  wp_send_json_error('Product ID is missing.'); }

    $product_id   = absint($_POST['product_id']);
    $variation_id = absint($_POST['group_id']);
    $current_page = sanitize_text_field($_POST['current_page']); // Current page name

    // Get the variation product object
    $variation_product = wc_get_product($variation_id);

    if ($variation_product && $variation_product->is_type('variation')) {
        
        // Get the variation attributes
        $variation_attributes = $variation_product->get_attributes();

        // Add the variation product to the cart
        if (WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation_attributes)) {
            // Save the current page name in session data
            WC()->session->set('added_from_page', $current_page);
            
            wp_send_json_success();
        } else {
            wp_send_json_error('Could not add product to cart.');
        }
    } else {
        wp_send_json_error('Invalid variation product.');
    }
}


add_action('wp_ajax_custom_buy_now', 'custom_buy_now');
add_action('wp_ajax_nopriv_custom_buy_now', 'custom_buy_now');


/*---------------- WOOCOMMERCE CHECKOUT -------------*/
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
// Hooking "Coupon form" after order total in checkout page with custom function
add_action('woocommerce_review_order_after_cart_contents', 'woocommerce_checkout_coupon_form_custom');

// Rendering html for "Coupon form" with custom function
function woocommerce_checkout_coupon_form_custom()
{   
    ?>
    <tr>
        <td>
            <a href="<?= home_url(); ?>" class="checkout_question"><?php _e('Haben Sie Interesse an weiteren Vorbereitungskursen?', 'woocommerce'); ?></a>
        </td>
    </tr>
    <tr class="coupon-form">
        <td> <?php wc_get_template( 'checkout/form-coupon.php', array( 'checkout' => WC()->checkout(), )); ?> </td>
    </tr>
    <tr>
        <td> <p class="cart_small_title"><?php _e('Information about the Package', 'woocommerce'); ?></p> </td>
    </tr>
    <tr>
        <td>
            <p class="cart_dis">
                <?php _e('Ihr Paket endet automatisch, wenn es abläuft. Sie werden 7 Tage vor Ablauf der Lizenz daran erinnert. Sie können Ihre Lizenz jederzeit erneuern. Für weitere Informationen über die Verlängerung, <a href="javascript:void(0);" id="cart_link" onclick="openCartPopup(this);" class="cart_link heir" >Klicken Sie hier.</a>', 'woocommerce'); ?>                
            </p>
            <div class="pricing-popup cart_popup" id="cart_popup" style="display: none;">
                <div class="popup-content">
                    <button class="close-popup" onclick="closeCartPopup();">&times;</button>
                    <div class="popup-body">
                        <p class="course-description">Ihr Abonnement <b>endet automatisch</b>, wenn es abläuft. Sie erhalten 7 Tage vor Ablauf eine Erinnerung. Sie können Ihre Lizenz jederzeit über unsere Website verlängern. Weitere Informationen zur Erneuerung finden Sie im Web Shop. Verlängern Sie Ihr Abonnement, bevor es abläuft, damit Sie unsere Dienste weiterhin ohne Unterbrechung nutzen können. Sie haben kein Interesse an einer automatischen Verlängerung? Bei uns müssen Sie sich <b>keine Sorgen</b> um automatische Verlängerungen machen. Ihr Abonnement wird <b>nicht automatisch</b> verlängert, so dass Sie die volle Kontrolle über Ihre Zahlungen und Dienste haben. Wenn Sie Hilfe benötigen, wenden Sie sich bitte innerhalb von 30 Tagen nach dem Kauf an unseren Kundendienst. Wenn Sie Fragen haben oder weitere Unterstützung benötigen, wenden Sie sich bitte an unser Kundendienstteam.</p>
                        <!-- <a href="#" class="common_btn" id="closePopupBtn">Click Here To Return</a> -->
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <?php
}

add_action('wp', 'remove_checkout_payment_methods');

function remove_checkout_payment_methods()
{
    if (is_checkout()) {
        remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    }
}

// Add space after currency symbol
function custom_woocommerce_price_format( $formatted_price, $price, $args ) {
    // Ensure the formatted price has a space after the currency symbol
    $currency_symbol = get_woocommerce_currency_symbol();
    $formatted_price = str_replace($currency_symbol, $currency_symbol . ' ', $formatted_price);

    return $formatted_price;
}
add_filter('wc_price', 'custom_woocommerce_price_format', 10, 3);

add_action('wp_ajax_update_cart', 'update_cart');
add_action('wp_ajax_nopriv_update_cart', 'update_cart');

function update_cart()
{
    if (!class_exists('WC_Cart')) {
        wp_send_json_error('WooCommerce is not active.');
    }

    // Check if the required parameters are set
    if (!isset($_POST['product_id']) || !isset($_POST['quantity']) || !isset($_POST['cart_item_key'])) {
        wp_send_json_error('Missing required parameters.');
    }


    $product_id = absint($_POST['product_id']);
    $quantity = absint($_POST['quantity']);
    $cart_item_key = wc_clean($_POST['cart_item_key']); // Sanitize cart item key

    // Get WooCommerce cart object
    $cart = WC()->cart;

    // Update the quantity in WooCommerce session or database
    $cart->set_quantity($cart_item_key, $quantity);

    // Get updated cart subtotal
    $cart_subtotal = $cart->get_cart_subtotal();

    // Initialize subtotal variable
    $product_subtotal = 0;

    // Loop through cart items to find the product
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

        $item_product_id = $cart_item['variation_id'];

        // Check if the current cart item matches the product ID
        if ( $item_product_id === $product_id ) {
            // Calculate subtotal for the current cart item
            $item_subtotal = $cart_item['line_total']; // Use line_total for line total including taxes
            // Or use $cart_item['line_subtotal'] for subtotal excluding taxes
            
            // Accumulate subtotal
            $product_subtotal += $item_subtotal;
        }
    }


    // Get formatted price
    $formatted_price = wc_price( $product_subtotal );


    // Send success response with updated cart subtotal
    wp_send_json_success(array(
        'cart_subtotal' => $cart_subtotal,
        'product_subtotal' => $formatted_price,
    ));

    wp_die();
}

// Display Only Email Field
add_filter('woocommerce_checkout_fields', 'custom_woocommerce_checkout_fields');
function custom_woocommerce_checkout_fields($fields)
{
    // Keep only the billing email field
    if (isset($fields['billing'])) {

        $fields['billing']['billing_first_name']['placeholder'] = $fields['billing']['billing_first_name']['label'];
        $fields['billing']['billing_last_name']['placeholder'] = $fields['billing']['billing_last_name']['label'];
        $fields['billing']['billing_address_1']['placeholder'] = str_replace("ß","ss",$fields['billing']['billing_address_1']['placeholder']);
        // $fields['billing']['billing_address_2']['placeholder'] = __('Apartment, suite, room etc. (optional)','woocommerce');
        $fields['billing']['billing_postcode']['placeholder'] = $fields['billing']['billing_postcode']['label'];
        $fields['billing']['billing_city']['placeholder'] = $fields['billing']['billing_city']['label'];
        $fields['billing']['billing_email']['placeholder'] = $fields['billing']['billing_email']['label'];

        // Add phone field
        $fields['billing']['billing_phone'] = array(
            'label'       => __('Telefonnummer', 'woocommerce'),
            'placeholder' => __('078 123 45 67', 'woocommerce'),
            // 'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 30,
            'type'        => 'tel',
        );

        $fields['billing'] = array(
            'billing_first_name' => $fields['billing']['billing_first_name'],
            'billing_last_name' => $fields['billing']['billing_last_name'],
            'billing_address_1' => $fields['billing']['billing_address_1'],
            // 'billing_address_2' => $fields['billing']['billing_address_2'],
            'billing_postcode' => $fields['billing']['billing_postcode'],
            'billing_city' => $fields['billing']['billing_city'],
            'billing_email' => $fields['billing']['billing_email'],
            'billing_phone'      => $fields['billing']['billing_phone'],
        );
    }

    // Ensure shipping fields are empty if they exist
    if (isset($fields['shipping'])) {
        $fields['shipping'] = array();
    }

    // Ensure order fields are empty if they exist
    if (isset($fields['order'])) {
        $fields['order'] = array();
    }

    // Optionally ensure account fields are empty if they exist
    if (isset($fields['account'])) {
        $fields['account'] = array();
    }

    return $fields;
}

// Remove "(optional)" text even if not required
add_filter('woocommerce_form_field', 'remove_optional_text_checkout', 10, 4);
function remove_optional_text_checkout($field, $key, $args, $value) {
    if (is_checkout() && strpos($field, 'optional') !== false) {
        $field = preg_replace('#\s*<span[^>]*>(\(optional\))</span>#', '', $field);
    }
    return $field;
}

add_action('wp_enqueue_scripts', 'studypeak_enqueue_intl_tel_input');
function studypeak_enqueue_intl_tel_input()
{
    if (is_checkout() || is_page(6721)) {
        // Load correct CSS
        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css',
            array(),
            rand()
        );

        // Load JS
        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js',
            array('jquery'),
            rand(),
            true
        );

        // Load utils.js (required for formatting)
        wp_enqueue_script(
            'intl-tel-input-utils',
            'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js',
            array('intl-tel-input-js'),
            rand(),
            true
        );
    }
}


add_filter('woocommerce_get_privacy_policy_text', 'custom_privacy_policy_text');

function custom_privacy_policy_text($text) {
    return 'Diese Zahlung wird in der Schweiz abgewickelt.';
}

add_filter('woocommerce_order_button_text', 'custom_checkout_button_text');

function custom_checkout_button_text($button_text) {
    return 'Bestellung aufgeben'; // Replace with your desired text
}

function redirect_if_cart_empty() {
    // Check if the cart is empty
    if (is_cart() && WC()->cart->is_empty()) {
        // Redirect to a specific URL (replace 'your-page-url' with your desired page URL)
        wp_redirect(home_url('/multicheck-vorbereitung/'));
        exit;
    }
}
add_action('template_redirect', 'redirect_if_cart_empty');

/*---------------- END WOOCOMMERCE CHECKOUT -------------*/

/*--- CHECK COURSE EXPIRY CRON ---*/
    add_action( 'learndash_woo_ordered_course_is_expired', 'cron_learndash_woo_ordered_course_is_expired_115d18fd', 10, 0 );

    function cron_learndash_woo_ordered_course_is_expired_115d18fd() 
    {   
        // array('role' => 'customer')
        $allusers = get_users();
        $grouporderitems = array();
        foreach ($allusers as $single_user) {

            $user_id = $single_user->ID;
            $orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'status' => 'completed',
                'limit' => -1
            ));    
            
            foreach ($orders as $order) {

                $items = $order->get_items();

                foreach ($items as $item) {
                    
                    $item_id = $item->get_variation_id();
                    $completion_date = $order->get_date_completed();

                    if ($item_id) {

                        $group_ids = get_post_meta($item_id, '_related_group', true);
                        $group_expiry_date = $item->get_meta('group_expiry_date', true);
                        //error_log('check empty '. print_r($group_expiry_date, true));
                        if (empty($group_expiry_date)) {
                            
                            $product     = $item->get_product();
                            $item_name   = $item->get_name();
                            $attributes  = $product->get_attributes();
                            $accessdays    = explode(' - ', $item_name)[1];
                            $quantity    = $item->get_quantity();

                           /* error_log('check accessdays '. print_r($accessdays, true));
                            error_log('check quantity '. print_r($quantity, true));*/

                            if (!empty($accessdays) && is_numeric($accessdays) && $accessdays > 0) {
                                
                                $accessdays  = $accessdays * $quantity;
                                $date = new DateTime($completion_date->date('Y-m-d H:i:s'));
                                // Add access days to the completion date for the end date
                                $completion_date->modify('+' . $accessdays . ' days');
                                
                                // Calculate expiration date
                                $expiredate = $completion_date->modify('+1 day');
                                $expiredate = $expiredate->format('d-m-Y');

                                $item->add_meta_data('group_expiry_date', $expiredate, true);
                                $item->save();
                                //error_log('created expiry date '. $expiredate);
                                $group_expiry_date = $expiredate;
                            }

                        }

                        if (!empty($group_ids) && !empty($group_expiry_date)) {
                         
                            foreach ($group_ids as $gpkey => $groupid) {
                               
                                //$groupid = $group_ids['0'];
                                $group_ids = get_post_meta($item_id, '_related_group', true);
                                $grouporderitems[$user_id][$groupid][] = array('item' => $item, 'expiry_date' => $group_expiry_date);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($grouporderitems)) { handle_expired_group($grouporderitems); }
    }

    function handle_expired_group($users) {

        foreach ($users as $userid => $groups) {

            error_log('user id '. $userid);

            //error_log('groups '. print_r($groups, true));
            foreach ($groups as $groupid => $orderitems) {

                $group_status = array();

                 /*if($userid == 329) { error_log('orderitemss'.print_r($orderitems,true)); }*/

                foreach ($orderitems as $itemkey => $item) {
                        
                    $current_date = date('d-m-Y');
                    $expire_date  = $item['expiry_date'];
                        
                    // Convert both dates to timestamps
                    $current_timestamp = strtotime($current_date);
                    $expire_timestamp = strtotime($expire_date);

                    /*if($userid == 329) {
                        error_log('current_date'.$current_date);
                        error_log('expire_date'.$expire_date);
                        error_log('current_timestamp'.$current_timestamp);
                        error_log('expire_timestamp'.$expire_timestamp);
                    }*/

                    if ($expire_timestamp <= $current_timestamp) {
                        $group_status[] = 'false';
                        // Update meta data
                        $expiredate = '';
                        $item['item']->update_meta_data('group_expiry_date', $expiredate);
                        $item['item']->save(); // Save the item to apply changes
                    } else { 
                        $group_status[] = 'true';
                    }
                }

                /*if($userid == 329) { error_log('group_statuss'.print_r($group_status,true)); }*/

                if (!in_array('true', $group_status)) 
                {
                    // Use ld_update_group_access to remove the user from the group
                    $isunset = ld_update_group_access($userid, $groupid, true);
                   
                    if ($isunset == true) 
                    {
                        foreach ($orderitems as $itemkey => $item) 
                        {   
                            $item_id = $item['item']->get_id();
                            $variationid = $item['item']->get_variation_id();
                            $orderid = $item['item']->get_order_id();
                            $expiredate = '';
                            

                            error_log( 'success remove group #'. $groupid .' access for #'. $userid .' user and his orderid #'. $orderid .' orderitemid #'. $item_id );

                            // Update meta data
                            $item['item']->update_meta_data('group_expiry_date', $expiredate);
                            $item['item']->save(); // Save the item to apply changes                            
                        }

                    } else { 

                        error_log('userid id '. $userid);
                        error_log('groupid id '. $groupid);

                        error_log( 'fail unset user group for items'. json_encode($orderitems) ); }
                }
            }
        }
    }
/*--- END CHECK COURSE EXPIRY CRON ---*/