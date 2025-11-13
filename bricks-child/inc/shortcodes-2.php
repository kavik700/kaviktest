<?php

// Offline Course Pricing Section Shortcode -----------------------------------------------------------------------------------
function multicheck_stellwerk_offline_courses_pricing_section_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block offline-course-pricing-wrapper">  
        <div class="offline_courses_card">
            <div class="card_header">
                <h3 class="main_title"><?php echo get_field('ms_course_slot_left_title', $current_course_id); ?></h3>
                <div class="course_price_card">
                    <?php echo get_field('ms_course_slot_center_title', $current_course_id); ?>
                </div>
                <h3 class="main_title"><?php echo get_field('course_slot_right_title', $current_course_id); ?></h3>
            </div>
            <div class="card_body">
                <h4><?php echo get_field('ms_course_slot_title', $current_course_id); ?></h4>
                <?php 
                // Track if it's the first iteration
                $is_first_loop = true;
                $page_slug = get_post_field('post_name', get_queried_object_id());

                if (have_rows('mothly_courses_slot', $current_course_id)) : 
                    while (have_rows('mothly_courses_slot', $current_course_id)) : 
                        the_row();

                        setlocale(LC_TIME, 'de_DE.UTF-8');
                        $from_date = get_sub_field('from_date');
                        $date = DateTime::createFromFormat('d.m.Y', $from_date);

                        if ($date) {
                            $formattedDate = strftime('%B %Y', $date->getTimestamp());
                            $formattedDate = ucfirst($formattedDate);
                        } else {
                            $formattedDate = 'Invalid Date';
                        }
                ?>
                <table class="course-tbl offline-course-tbl">
                    <tbody>
                        <tr>
                            <th><strong><?php echo $formattedDate; ?></strong></th>
                            <th>
                                <p><?php echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' Uhr - ' . get_sub_field('select_to_time') . ' Uhr'; ?></p>
                            </th>
                            <td><?php echo get_sub_field('time_slot_title'); ?></td>
                            <td class="dates"><?php echo get_sub_field('from_date') . ' - ' . get_sub_field('to_date'); ?></td>
                            <td class="price"><?php echo get_sub_field('slot_price') . ' CHF'; ?></td>
                            <td class="btn-action">
                                <div class="btn-tooltip">
                                    <i class="fas fa-info"></i>
                                    <div class="tooltip_popup">
                                        <h3><?php echo get_sub_field('info_title'); ?></h3>
                                        <ul>
                                            <li><?php echo get_sub_field('info_day_time'); ?></li>
                                            <li><b><?php echo get_sub_field('info_slot_dates'); ?></b></li>
                                            <li>
                                                <h6><?php echo get_sub_field('info_slot_address'); ?></h6>
                                                <p><?php echo get_sub_field('slot_price') . ' CHF'; ?></p>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <?php //if ($is_first_loop && $page_slug === 'multicheck-kurse') : ?>
                                    <!-- <a href="#brxe-tsrxqo" class="brxe-button bricks-button lg bricks-background-primary">Ausgebucht</a> -->
                                <?php //else : ?>
                                    <a href="#brxe-tsrxqo" class="brxe-button bricks-button lg bricks-background-primary"><?php echo get_sub_field('info_slot_button_text'); //echo get_field('ms_course_slot_button_text', $current_course_id); ?></a>
                                <?php //endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php 
                        $is_first_loop = false;
                    endwhile; 
                endif; 
                ?>
            </div>
        </div>       
    </div>
    <!-- Section End -->

    <?php 
    return ob_get_clean();
}
add_shortcode('ms_offline_courses_pricing_section', 'multicheck_stellwerk_offline_courses_pricing_section_shortcode');
// [ms_offline_courses_pricing_section]


// Offline Course Tabing Section Shortcode -----------------------------------------------------------------------------------
function multicheck_stellwerk_offline_courses_tabbing_section_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block offline-course-tabbing-wrapper"> 
        <div class="custom_nav_tabs">
            <div class="nav_tabs">
                <?php 
                if( have_rows('ms_tab_listing', $current_course_id) ): 
                    while( have_rows('ms_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="nav_items">
                    <div class="nav_link <?php if(get_row_index() === 1){ echo "active"; } ?>" data-target="tab-<?php echo get_row_index(); ?>">
                        <?php the_sub_field('ms_list_title'); ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>

            <div class="tab-content">
                <?php 
                if( have_rows('ms_tab_listing', $current_course_id) ): 
                    while( have_rows('ms_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="tab-pane <?php if(get_row_index() === 1){ echo "active"; } ?>" id="tab-<?php echo get_row_index(); ?>">
                    <?php 
                    if( have_rows('ms_inner_tab_listing') ): 
                        while( have_rows('ms_inner_tab_listing') ): 
                            the_row(); 
                    ?>
                    <div class="offline_course_content">
                        <div class="kurs-icon">
                            <img src="<?php the_sub_field('ms_inner_list_image'); ?>" alt="" />
                        </div>
                        <div class="kurs-desc">
                            <?php the_sub_field('ms_inner_list_description'); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>
        </div> 
    </div>
    <!-- Section End -->
    <script>
        jQuery(document).ready(function() {
            // Tab click functionality
            jQuery(".nav_link").click(function() {
                // Remove 'active' class from all tabs and panes
                jQuery(".nav_link").removeClass("active");
                jQuery(".tab-pane").removeClass("active");

                // Add 'active' class to the clicked tab and its corresponding pane
                jQuery(this).addClass("active");
                jQuery("#" + jQuery(this).data("target")).addClass("active");
            });
        });
    </script>

    <?php return ob_get_clean();
}
add_shortcode('ms_offline_courses_tabbing_section', 'multicheck_stellwerk_offline_courses_tabbing_section_shortcode');
// [ms_offline_courses_tabbing_section]


// Offline Gymi courses Shortcode -----------------------------------------------------------------------------------
function gymi_offline_courses_tabbing_section_shortcode() {
   $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block gymi-offline-course-tabbing-wrapper"> 
        <div class="custom_nav_tabs">
            <div class="nav_tabs">
                <?php 
                if( have_rows('g_tab_listing', $current_course_id) ): 
                    while( have_rows('g_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="nav_items">
                    <div class="nav_link <?php if(get_row_index() === 1){ echo "active"; } ?>" data-target="tab-<?php echo get_row_index(); ?>">
                        <?php the_sub_field('g_list_title'); ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>

            <div class="tab-content">
                <?php 
                if( have_rows('g_tab_listing', $current_course_id) ): 
                    while( have_rows('g_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="tab-pane <?php if(get_row_index() === 1){ echo "active"; } ?>" id="tab-<?php echo get_row_index(); ?>">
                    <?php 
                    if( have_rows('g_inner_tab_listing') ): 
                        while( have_rows('g_inner_tab_listing') ): 
                            the_row(); 
                    ?>
                    <div class="offline_course_content">
                        <?php if (get_sub_field('g_inner_list_image')) : ?>
                        <div class="kurs-icon">
                            <img src="<?php the_sub_field('g_inner_list_image'); ?>" alt="" />
                        </div>
                        <?php endif; ?>
                        <div class="kurs-desc">
                            <?php the_sub_field('g_inner_list_description'); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>
        </div> 
    </div>
    <!-- Section End -->

    <script>
        jQuery(document).ready(function() {
            // Tab click functionality
            jQuery(".nav_link").click(function() {
                // Remove 'active' class from all tabs and panes
                jQuery(".nav_link").removeClass("active");
                jQuery(".tab-pane").removeClass("active");

                // Add 'active' class to the clicked tab and its corresponding pane
                jQuery(this).addClass("active");
                jQuery("#" + jQuery(this).data("target")).addClass("active");
            });
        });
    </script>

    <?php return ob_get_clean();
}
add_shortcode('gymi_offline_courses_tabbing_section', 'gymi_offline_courses_tabbing_section_shortcode');
// [gymi_offline_courses_tabbing_section]


// Offline Course Pricing Section Shortcode -----------------------------------------------------------------------------------
function gymi_offline_courses_pricing_section_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block gymi-offline-course-pricing-wrapper">  
        <div class="offline_courses_card">
            <h3 class="brxe-heading"><?php the_field('multicheck_course_accordion_title', $current_course_id); ?></h3>
            <form id="groups-plan-filter" class="common_filter_form groupf-form">   
                <div class="form-group" role="group"> 
                    <select id="form-field-plan-group" name="f-group" aria-label="gymi" class="select2-hidden-accessible" aria-hidden="true">
                        <?php 
                        if( have_rows('multicheck_course_listing', $current_course_id) ): 
                            while( have_rows('multicheck_course_listing', $current_course_id) ): 
                                the_row(); 
                                $list_title = get_sub_field('list_title');
                                echo '<option value="category-'.get_row_index().'">'.$list_title.'</option>';
                            endwhile; 
                        endif; 
                        ?> 
                    </select>               
                </div>   
            </form>
        </div>       
        <div class="table-accordion"> 
            <?php 
            if( have_rows('multicheck_course_listing', $current_course_id) ): 
                while( have_rows('multicheck_course_listing', $current_course_id) ): 
                    the_row(); 
                    $is_active = (get_row_index() === 1) ? 'active' : '';
            ?>          
            <div class="table-content <?php echo $is_active; ?>" id="category-<?php echo get_row_index(); ?>">
                <?php 
                if( have_rows('mothly_courses_slot') ): 
                    while( have_rows('mothly_courses_slot') ): 
                        the_row(); 
                ?>
                <table class="course-tbl offline-course-tbl">
                    <tbody>
                        <tr>
                            <th>
                                <h5><?php the_sub_field('title'); ?></h5>
                                <p><?php echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' – ' . get_sub_field('select_to_time').' Uhr'; ?></p>
                            </th>
                            <!-- <td><a class="text-link" target="_blank" href="<?php //echo the_field('multicheck_course_location_url'); ?>"><?php //the_sub_field('time_slot_title'); ?></a></td> -->
                            <td><b><?php the_sub_field('time_slot_title'); ?></b></td>
                            <td class="dates"><?php echo get_sub_field('from_date').' - '.get_sub_field('to_date'); ?></td>
                            <td class="price"><?php echo get_sub_field('slot_price').' CHF'; ?></td>
                            <td class="btn-action">
                                <div class="btn-tooltip">
                                    <i class="fas fa-info"></i>
                                    <div class="tooltip_popup">
                                        <h3><?php the_sub_field('info_title'); ?></h3>
                                        <ul>
                                            <li><?php the_sub_field('info_day_time'); ?></li>
                                            <li><b><?php the_sub_field('info_slot_dates'); ?></b></li>
                                            <li>
                                                <h6><?php _e('Kosten','bricks-child'); ?></h6>
                                                <p><?php echo get_sub_field('slot_price').' CHF'; ?></p>
                                            </li>
                                        </ul>                                        
                                    </div>
                                </div>
                                <a href="#brxe-ae1d65" class="brxe-button bricks-button lg bricks-background-primary"><?php echo get_sub_field('info_slot_button_text'); ?></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>
            <?php 
                endwhile; 
            endif; 
            ?>
        </div>
    </div>
    <!-- Section End -->
    <script>
        jQuery(document).ready(function() {
            // Hide all table contents except the first one
            jQuery('.table-content').hide();
            jQuery('.table-content.active').show();

            // Listen for changes on the dropdown
            jQuery('#form-field-plan-group').change(function() {
                var selectedCategory = jQuery(this).val();

                // Hide all table contents
                jQuery('.table-content').hide();

                // Show the selected table content
                jQuery('#' + selectedCategory).show();
            });
        });
    </script>
    <?php 
    return ob_get_clean();
}
add_shortcode('gymi_offline_courses_pricing_section', 'gymi_offline_courses_pricing_section_shortcode');
// [gymi_offline_courses_pricing_section]


// Offline IMS courses Shortcode -----------------------------------------------------------------------------------
function ims_offline_courses_section_shortcode() {
   $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block ims-offline-course-tabbing-wrapper"> 
        <div class="custom_nav_tabs">
            <div class="nav_tabs">
                <?php 
                if( have_rows('ims_tab_listing', $current_course_id) ): 
                    while( have_rows('ims_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="nav_items">
                    <div class="nav_link <?php if(get_row_index() === 1){ echo "active"; } ?>" data-target="tab-<?php echo get_row_index(); ?>">
                        <?php the_sub_field('ims_list_title'); ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>

            <div class="tab-content">
                <?php 
                if( have_rows('ims_tab_listing', $current_course_id) ): 
                    while( have_rows('ims_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="tab-pane <?php if(get_row_index() === 1){ echo "active"; } ?>" id="tab-<?php echo get_row_index(); ?>">
                    <?php 
                    if( have_rows('ims_inner_tab_listing') ): 
                        while( have_rows('ims_inner_tab_listing') ): 
                            the_row(); 
                    ?>
                    <div class="offline_course_content">
                        <?php if (get_sub_field('ms_inner_list_image')) : ?>
                        <div class="kurs-icon">
                            <img src="<?php the_sub_field('ms_inner_list_image'); ?>" alt="" />
                        </div>
                        <?php endif; ?>
                        <div class="kurs-desc">
                            <?php the_sub_field('ms_inner_list_description'); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>

            <div class="offline_courses_card" id="brxe-rnujnd">
                <div class="card_body">
                    <h4><?php echo get_field('ims_course_slot_title', $current_course_id); ?></h4>
                    <?php 
                    if (have_rows('ims_tab_listing', $current_course_id)) : 
                        while (have_rows('ims_tab_listing', $current_course_id)) : 
                            the_row();
                            $ims_list_title = get_sub_field('ims_list_title');
                    ?> 
                    <div class="card-panel-price <?php if(get_row_index() === 1){ echo "active"; } ?>" id="tab-<?php echo get_row_index(); ?>">
                        <table class="course-tbl offline-course-tbl">
                            <tbody>
                            <?php 
                            if (have_rows('mothly_courses_slot')) : 
                                while (have_rows('mothly_courses_slot')) : 
                                    the_row();
                            ?>
                                <tr>
                                    <?php if(get_row_index() === 1) : ?>
                                    <th rowspan="<?php echo get_row_index() + 10; ?>">
                                        <strong><?php echo get_sub_field('title'); ?></strong>                                
                                    </th>
                                    <?php endif; ?>
                                    <th>                                
                                        <p><?php echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' Uhr - ' . get_sub_field('select_to_time') . ' Uhr'; ?></p>            
                                    </th>
                                    <td><?php echo get_sub_field('time_slot_title'); ?></td>
                                    <td class="dates"><?php echo get_sub_field('from_date') . ' - ' . get_sub_field('to_date'); ?></td>
                                    <td class="price"><?php echo get_sub_field('slot_price') . ' CHF'; ?></td>
                                    <td class="btn-action">
                                        <div class="btn-tooltip">
                                            <i class="fas fa-info"></i>
                                            <div class="tooltip_popup">
                                                <h3><?php echo get_sub_field('info_title'); ?></h3>
                                                <ul>
                                                    <li><?php echo get_sub_field('info_day_time'); ?></li>
                                                    <li><b><?php echo get_sub_field('info_slot_dates'); ?></b></li>
                                                    <li>
                                                        <p><?php echo get_sub_field('info_slot_address'); ?></p>
                                                        <p><?php echo get_sub_field('slot_price') . ' CHF'; ?></p>
                                                    </li>
                                                </ul>                                        
                                            </div>
                                        </div>
                                        <a href="#brxe-440646" class="brxe-button bricks-button lg bricks-background-primary"><?php echo get_sub_field('ims_course_slot_button_text'); ?></a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            endif; 
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
            </div> 
        </div> 
    </div>
    <!-- Section End -->

    <script>
        jQuery(document).ready(function() {
            // Hide all card-panel-price divs except the active one on page load
            jQuery(".card-panel-price").not(".active").hide();

            // Tab click functionality
            jQuery(".nav_link").click(function() {
                // Remove 'active' class from all tabs and panes
                jQuery(".nav_link").removeClass("active");
                jQuery(".tab-pane").removeClass("active");
                jQuery(".card-panel-price").removeClass("active").hide(); // Hide all card-panel-price divs

                // Add 'active' class to the clicked tab and its corresponding pane
                jQuery(this).addClass("active");
                const target = jQuery(this).data("target");
                jQuery("#" + target).addClass("active");
                jQuery(".card-panel-price#" + target).addClass("active").show(); // Show the corresponding card-panel-price
            });
        });
    </script>

    <?php return ob_get_clean();
}
add_shortcode('ims_offline_courses_section', 'ims_offline_courses_section_shortcode');
// [ims_offline_courses_section]


// Offline Course Pricing Section Shortcode -----------------------------------------------------------------------------------
function probezeit_offline_courses_pricing_section_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block offline-course-pricing-wrapper">  
        <div class="offline_courses_card">
            <div class="card_header">
                <h3 class="main_title"><?php echo get_field('pz_course_slot_left_title', $current_course_id); ?></h3>
                <div class="course_price_card">
                    <?php echo get_field('pz_course_slot_center_title', $current_course_id); ?>
                </div>
                <h3 class="main_title"><?php echo get_field('pz_course_slot_right_title', $current_course_id); ?></h3>
            </div>
            <div class="brxe-block gymi-offline-course-pricing-wrapper card_body">  
                <div class="offline_courses_card">
                    <form id="groups-plan-filter" class="common_filter_form groupf-form">   
                        <div class="form-group" role="group"> 
                            <select id="form-field-plan-group" name="f-group" aria-label="gymi" class="select2-hidden-accessible" aria-hidden="true">
                                <?php 
                                if( have_rows('pz_course_listing', $current_course_id) ): 
                                    while( have_rows('pz_course_listing', $current_course_id) ): 
                                        the_row(); 
                                        $list_title = get_sub_field('pz_course_lists_title');
                                        echo '<option value="category-'.get_row_index().'">'.$list_title.'</option>';
                                    endwhile; 
                                endif; 
                                ?> 
                            </select>               
                        </div>   
                    </form>
                    <h4 class="brxe-heading"><?php the_field('pz_course_slot_title', $current_course_id); ?></h4>
                </div>       
                <div class="table-accordion"> 
                    <?php 
                    if( have_rows('pz_course_listing', $current_course_id) ): 
                        while( have_rows('pz_course_listing', $current_course_id) ): 
                            the_row(); 
                            $is_active = (get_row_index() === 1) ? 'active' : '';
                    ?>          
                    <div class="table-content <?php echo $is_active; ?>" id="category-<?php echo get_row_index(); ?>">
                        <?php 
                        if( have_rows('mothly_courses_slot') ): 
                            while( have_rows('mothly_courses_slot') ): 
                                the_row(); 

                                setlocale(LC_TIME, 'de_DE.UTF-8');
                                $from_date = get_sub_field('from_date');
                                $date = DateTime::createFromFormat('d.m.Y', $from_date);

                                if ($date) {
                                    $formattedDate = strftime('%B %Y', $date->getTimestamp());
                                    $formattedDate = ucfirst($formattedDate);
                                } else {
                                    $formattedDate = 'Invalid Date';
                                }

                                $custom_day = get_sub_field('select_custom_day');
                                $day = !empty($custom_day) ? $custom_day : get_sub_field('select_day'). 's';
                                $from_time = get_sub_field('select_from_time');
                                $to_time = get_sub_field('select_to_time');
                        ?>
                        <?php if(get_sub_field('title')): ?><h4 class="brxe-heading-probezeit-kurse"><?php the_sub_field('title'); ?></h4><?php endif; ?>
                        <table class="course-tbl offline-course-tbl">
                            <tbody>
                                <!-- <?php //if(get_sub_field('title')): ?><tr><th colspan="6"><h4><?php //the_sub_field('title'); ?></th></h4></tr><?php //endif; ?> -->
                                <tr>
                                    <!-- <th>
                                        <h5><?php //the_sub_field('title'); ?></h5>
                                        <p><?php //echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' – ' . get_sub_field('select_to_time').' Uhr'; ?></p>
                                    </th> -->
                                    <th><strong><?php echo $formattedDate; ?></strong></th>
                                    <th>
                                        <!-- <p><?php //echo get_sub_field('select_day') . ', ' . get_sub_field('select_from_time') . ' Uhr - ' . get_sub_field('select_to_time') . ' Uhr'; ?></p> -->
                                        <p><?php echo esc_html($day . ', ' . $from_time . ' Uhr - ' . $to_time . ' Uhr'); ?></p>
                                    </th>
                                    <!-- <td><a class="text-link" target="_blank" href="<?php //echo the_field('multicheck_course_location_url'); ?>"><?php //the_sub_field('time_slot_title'); ?></a></td> -->
                                    <td><b><?php the_sub_field('time_slot_title'); ?></b></td>
                                    <td class="dates"><?php echo get_sub_field('from_date').' - '.get_sub_field('to_date'); ?></td>
                                    <td class="price"><?php echo get_sub_field('slot_price').' CHF'; ?></td>
                                    <td class="btn-action">
                                        <div class="btn-tooltip">
                                            <i class="fas fa-info"></i>
                                            <div class="tooltip_popup">
                                                <h3><?php the_sub_field('info_title'); ?></h3>
                                                <ul>
                                                    <li><?php the_sub_field('info_day_time'); ?></li>
                                                    <li><b><?php the_sub_field('info_slot_dates'); ?></b></li>
                                                    <li>
                                                        <h6><?php _e('Kosten','bricks-child'); ?></h6>
                                                        <p><?php echo get_sub_field('slot_price').' CHF'; ?></p>
                                                    </li>
                                                </ul>                                        
                                            </div>
                                        </div>
                                        <a href="#brxe-ae1d65" class="brxe-button bricks-button lg bricks-background-primary"><?php echo get_sub_field('info_slot_button_text'); ?></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php 
                            endwhile; 
                        endif; 
                        ?>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
            </div>
        </div>       
    </div>
    <!-- Section End -->
    <script>
        jQuery(document).ready(function() {
            // Hide all table contents except the first one
            jQuery('.table-content').hide();
            jQuery('.table-content.active').show();

            // Listen for changes on the dropdown
            jQuery('#form-field-plan-group').change(function() {
                var selectedCategory = jQuery(this).val();

                // Hide all table contents
                jQuery('.table-content').hide();

                // Show the selected table content
                jQuery('#' + selectedCategory).show();
            });
        });
    </script>
    <?php 
    return ob_get_clean();
}
add_shortcode('pz_offline_courses_pricing_section', 'probezeit_offline_courses_pricing_section_shortcode');
// [ms_offline_courses_pricing_section]


// Offline Course Tabing Section Shortcode -----------------------------------------------------------------------------------
function probezeit_offline_courses_tabbing_section_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block offline-course-tabbing-wrapper"> 
        <div class="custom_nav_tabs">
            <div class="nav_tabs">
                <?php 
                if( have_rows('pz_tab_listing', $current_course_id) ): 
                    while( have_rows('pz_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="nav_items">
                    <div class="nav_link <?php if(get_row_index() === 1){ echo "active"; } ?>" data-target="tab-<?php echo get_row_index(); ?>">
                        <?php the_sub_field('ms_list_title'); ?>
                    </div>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>

            <div class="tab-content">
                <?php 
                if( have_rows('pz_tab_listing', $current_course_id) ): 
                    while( have_rows('pz_tab_listing', $current_course_id) ): 
                        the_row(); 
                ?>
                <div class="tab-pane <?php if(get_row_index() === 1){ echo "active"; } ?>" id="tab-<?php echo get_row_index(); ?>">
                    <?php 
                    if( have_rows('ms_inner_tab_listing') ): 
                        while( have_rows('ms_inner_tab_listing') ): 
                            the_row(); 
                    ?>
                    <div class="offline_course_content">
                        <div class="kurs-icon">
                            <img src="<?php the_sub_field('ms_inner_list_image'); ?>" alt="" />
                        </div>
                        <div class="kurs-desc">
                            <?php the_sub_field('ms_inner_list_description'); ?>
                        </div>
                    </div>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </div>
                <?php 
                    endwhile; 
                endif; 
                ?>
            </div>
        </div> 
    </div>
    <!-- Section End -->
    <script>
        jQuery(document).ready(function() {
            // Tab click functionality
            jQuery(".nav_link").click(function() {
                // Remove 'active' class from all tabs and panes
                jQuery(".nav_link").removeClass("active");
                jQuery(".tab-pane").removeClass("active");

                // Add 'active' class to the clicked tab and its corresponding pane
                jQuery(this).addClass("active");
                jQuery("#" + jQuery(this).data("target")).addClass("active");
            });
        });
    </script>

    <?php return ob_get_clean();
}
add_shortcode('pz_offline_courses_tabbing_section', 'probezeit_offline_courses_tabbing_section_shortcode');
// [ms_offline_courses_tabbing_section]


// Grade calculation section shortcode -----------------------------------------------------------------------------------
function grade_calculation_section_form_shortcode() {
    $current_course_id = get_the_ID();
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block grade-calculation-section-wrapper">
        <div class="grade_calculation_section">
            <form class="common_form" id="common_form">
                <div class="form-group form_select">
                    <label>Wähle deine Stufe:</label>
                    <select id="level-selection">
                        <option selected disabled>Wähle deine Stufe</option>
                        <option value="Langzeitgymnasium">Langzeitgymnasium</option>
                        <option value="Kurzzeitgymnasium">Kurzzeitgymnasium</option>                        
                        <option value="BMS">BMS</option>                        
                        <option value="HMS">HMS</option>                        
                        <option value="FMS">FMS</option>                        
                    </select>
                    <span id="level-selection-error" class="select-error-message"></span>
                </div>
                
                <div class="form-group form_select">
                    <label>Zählt die Vornote?</label>
                    <select id="grade-weight-selection">
                        <option selected disabled>Zählt die Vornote</option>
                        <option value="yes">Ja, Vornote zählt</option>
                        <option value="no">Nein, Vornote zählt nicht</option>
                    </select>
                    <span id="grade-weight-selection-error" class="select-error-message"></span>
                </div>

                <div class="form-group vornoten-zählt" style="margin-top: 50px;">
                    <h4>Deine Vornoten</h4>
                </div>

                <!-- Vornoten Inputs -->
                <div class="form-group form_number langzeitgymnasium-vornoten">
                    <label>Deutsch:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-deutsch" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>   
                </div>

                <div class="form-group form_number langzeitgymnasium-vornoten">
                    <label>Mathematik:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-mathematik" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>

                <div class="form-group form_number kurzzeitgymnasium-vornoten" style="display:none;">
                    <label>Deutsch:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-deutsch-kw" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>      
                </div>

                <div class="form-group form_number kurzzeitgymnasium-vornoten" style="display:none;">
                    <label>Englisch:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-englisch" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>
                <div class="form-group form_number kurzzeitgymnasium-vornoten" style="display:none;">
                    <label>Französisch:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-franzoesisch" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>                    
                </div>

                <div class="form-group form_number kurzzeitgymnasium-vornoten" style="display:none;">
                    <label>Natur und Technik:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-natur-technik" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>
                <div class="form-group form_number kurzzeitgymnasium-vornoten" style="display:none;">
                    <label>Mathematik:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Vornote" id="vornote-mathematik-kw" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>                    
                </div>

                <div class="form-group" style="margin-top: 50px;">
                    <h4>Deine Prüfungsnoten</h4>
                </div>

                <!-- Prüfungsnoten Inputs -->
                <div class="form-group form_number">
                    <label>Deutsch (Aufsatz):</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Prüfungsnote" id="aufsatz" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>

                <div class="form-group form_number">
                    <label>Deutsch (Sprachbetrachtung und Textverständnis):</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Prüfungsnote" id="sprachbetrachtung" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>

                <div class="form-group form_number">
                    <label>Mathematik:</label>
                    <div class="custom-number-input">
                        <input type="number" class="form-control customNumberInput" min="1" max="6.0" step="0.1" placeholder="Wähle deine Prüfungsnote" id="mathePruefung" />
                        <button type="button" class="increase"><i class="fas fa-angle-up"></i></button>
                        <button type="button" class="decrease"><i class="fas fa-angle-down"></i></button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="button" class="brxe-button bricks-button lg bricks-background-light" id="calculate-grade">Note berechnen</button>
                </div>
            </form>

            <div class="total_grade_tbl">
                <h4>Deine Vornoten</h4>
                <div class="total_grade_card">
                    <table>
                        <tbody>
                            <tr>
                                <th>Vornote:</th>
                                <td id="vornote-result">0</td>
                            </tr>
                            <tr>
                                <th>Prüfungsnote:</th>
                                <td id="pruefungsnote-result">0</td>
                            </tr>
                            <tr class="total_grade">
                                <th>Gesamtnote:</th>
                                <td id="gesamtnote-result">0</td>
                            </tr>
                             <tr class="total_grade">
                                <th id="gradeStatus" colspan="2" style="text-align:center;"></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>       
    </div>
    <script type="text/javascript">
        // All translations grouped per language
        const translations = {
          de: {
            fieldEmpty: "Dieses Feld darf nicht leer sein.",
            fieldRange: "Der Wert muss zwischen 1 und 6 liegen.",
            levelError: "Bitte wählen Sie eine Stufe aus.",
            weightError: "Bitte wählen Sie, ob die Vornote zählt.",
            success: "Gratulation! Du hast die Prüfung bestanden.",
            fail: "Leider hast du die Prüfung nicht bestanden.",
            notConsidered: "Nicht berücksichtigt"
          },
          fr: {
            fieldEmpty: "Ce champ ne peut pas être vide.",
            fieldRange: "La valeur doit être comprise entre 1 et 6.",
            levelError: "Veuillez sélectionner un niveau.",
            weightError: "Veuillez indiquer si la note préliminaire compte.",
            success: "Félicitations ! Vous avez réussi l'examen.",
            fail: "Malheureusement, vous n'avez pas réussi l'examen.",
            notConsidered: "Non pris en compte"
          },
          it: {
            fieldEmpty: "Questo campo non può essere vuoto.",
            fieldRange: "Il valore deve essere compreso tra 1 e 6.",
            levelError: "Selezione un livello.",
            weightError: "Scegli se conteggiare la valutazione precedente.",
            success: "Congratulazioni! Hai superato l'esame.",
            fail: "Purtroppo non hai superato l’esame.",
            notConsidered: "Non superato"
          },
          en: {
            fieldEmpty: "This field cannot be empty.",
            fieldRange: "The value must be between 1 and 6.",
            levelError: "Please select a level.",
            weightError: "Please choose whether the preliminary grade counts.",
            success: "Congratulations! You passed the exam.",
            fail: "Unfortunately, you did not pass the exam.",
            notConsidered: "Not considered"
          }
        };

        // Detect language
        let currentLang = 'de';
        if (typeof Weglot !== 'undefined' && typeof Weglot.getCurrentLang === 'function') {
          currentLang = Weglot.getCurrentLang();
        } else {
          currentLang = document.documentElement.lang || 'de';
        }

        // Short helper function
        function t(key) {
          return translations[currentLang]?.[key] || translations['de'][key] || key;
        }

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Script Loaded');

            const levelSelection = document.getElementById('level-selection');
            const gradeWeightSelection = document.getElementById('grade-weight-selection');
            const langzeitVorleistungForm = document.getElementById('langzeitgymnasium-vornoten');
            const kurzzeitVorleistungForm = document.getElementById('kurzzeitgymnasium-vornoten');
            const calculateBtn = document.getElementById('calculate-grade');
            const gradeStatus = document.getElementById('gradeStatus');

            // function calculateFinalGrade() {
            //     const level = levelSelection.value;
            //     const withVorleistung = gradeWeightSelection.value === 'yes';

            //     const grades = getGrades(level, withVorleistung);

            //     const vorleistungGrade = calculateVorleistungGrade(grades, level);
            //     const pruefungGrade = calculatePruefungGrade(grades);
            //     let finalGrade = withVorleistung ? (vorleistungGrade + pruefungGrade) / 2 : pruefungGrade;

            //     displayResults(vorleistungGrade, pruefungGrade, finalGrade, withVorleistung);
            // }

            function calculateFinalGrade() {
                const level = levelSelection.value;
                const withVorleistung = gradeWeightSelection.value === 'yes';

                if (!validateAllFieldsFilled(level, withVorleistung)) {
                    gradeStatus.textContent = ""; // Clear any previous messages
                    document.getElementById('vornote-result').textContent = "0";
                    document.getElementById('pruefungsnote-result').textContent = "0";
                    document.getElementById('gesamtnote-result').textContent = "0";
                    return;
                }

                const grades = getGrades(level, withVorleistung);

                if (!validateGrades(grades)) {
                    document.getElementById('vornote-result').textContent = "0";
                    document.getElementById('pruefungsnote-result').textContent = "0";
                    document.getElementById('gesamtnote-result').textContent = "0";
                    return;
                }

                const vorleistungGrade = calculateVorleistungGrade(grades, level);
                const pruefungGrade = calculatePruefungGrade(grades);
                let finalGrade = withVorleistung ? (vorleistungGrade + pruefungGrade) / 2 : pruefungGrade;

                if (isNaN(finalGrade)) {
                    vorleistungGrade = 0;
                    pruefungGrade = 0;
                    finalGrade = 0;
                }

                displayResults(vorleistungGrade, pruefungGrade, finalGrade, withVorleistung);
            }



            function validateAllFieldsFilled(level, withVorleistung) {
                const requiredFields = ['aufsatz', 'sprachbetrachtung', 'mathePruefung'];

                if (withVorleistung) {
                    if (level === 'Langzeitgymnasium') {
                        requiredFields.push('vornote-deutsch', 'vornote-mathematik');
                    } else if (level === 'Kurzzeitgymnasium') {
                        requiredFields.push('vornote-deutsch-kw', 'vornote-englisch', 'vornote-franzoesisch', 'vornote-natur-technik', 'vornote-mathematik-kw');
                    } else if (level === 'BMS') {
                        requiredFields.push('vornote-deutsch-kw', 'vornote-englisch', 'vornote-franzoesisch', 'vornote-natur-technik', 'vornote-mathematik-kw');
                    } else if (level === 'HMS') {
                        requiredFields.push('vornote-deutsch-kw', 'vornote-englisch', 'vornote-franzoesisch', 'vornote-natur-technik', 'vornote-mathematik-kw');
                    } else if (level === 'FMS') {
                        requiredFields.push('vornote-deutsch-kw', 'vornote-englisch', 'vornote-franzoesisch', 'vornote-natur-technik', 'vornote-mathematik-kw');
                    }
                }

                return requiredFields.every(fieldId => {
                    const field = document.getElementById(fieldId);
                    return field && field.value.trim() !== '';
                });
            }

            function getGrades(level, withVorleistung) {
                const grades = {
                    deutschAufsatz: parseFloat(document.getElementById('aufsatz').value),
                    deutschSprachbetrachtung: parseFloat(document.getElementById('sprachbetrachtung').value),
                    mathPruefung: parseFloat(document.getElementById('mathePruefung').value)
                };

                if (withVorleistung) {
                    if (level === 'Langzeitgymnasium') {
                        grades.deutschVorleistung = parseFloat(document.getElementById('vornote-deutsch').value);
                        grades.mathVorleistung = parseFloat(document.getElementById('vornote-mathematik').value);
                    } else if (level === 'Kurzzeitgymnasium') {
                        grades.deutschVorleistungKw = parseFloat(document.getElementById('vornote-deutsch-kw').value);
                        grades.englischVorleistung = parseFloat(document.getElementById('vornote-englisch').value);
                        grades.franzosischVorleistung = parseFloat(document.getElementById('vornote-franzoesisch').value);
                        grades.naturTechnikVorleistung = parseFloat(document.getElementById('vornote-natur-technik').value);
                        grades.mathVorleistungKw = parseFloat(document.getElementById('vornote-mathematik-kw').value);
                    } else if (level === 'BMS') {
                        grades.deutschVorleistungKw = parseFloat(document.getElementById('vornote-deutsch-kw').value);
                        grades.englischVorleistung = parseFloat(document.getElementById('vornote-englisch').value);
                        grades.franzosischVorleistung = parseFloat(document.getElementById('vornote-franzoesisch').value);
                        grades.naturTechnikVorleistung = parseFloat(document.getElementById('vornote-natur-technik').value);
                        grades.mathVorleistungKw = parseFloat(document.getElementById('vornote-mathematik-kw').value);
                    } else if (level === 'HMS') {
                        grades.deutschVorleistungKw = parseFloat(document.getElementById('vornote-deutsch-kw').value);
                        grades.englischVorleistung = parseFloat(document.getElementById('vornote-englisch').value);
                        grades.franzosischVorleistung = parseFloat(document.getElementById('vornote-franzoesisch').value);
                        grades.naturTechnikVorleistung = parseFloat(document.getElementById('vornote-natur-technik').value);
                        grades.mathVorleistungKw = parseFloat(document.getElementById('vornote-mathematik-kw').value);
                    } else if (level === 'FMS') {
                        grades.deutschVorleistungKw = parseFloat(document.getElementById('vornote-deutsch-kw').value);
                        grades.englischVorleistung = parseFloat(document.getElementById('vornote-englisch').value);
                        grades.franzosischVorleistung = parseFloat(document.getElementById('vornote-franzoesisch').value);
                        grades.naturTechnikVorleistung = parseFloat(document.getElementById('vornote-natur-technik').value);
                        grades.mathVorleistungKw = parseFloat(document.getElementById('vornote-mathematik-kw').value);
                    }
                }

                return grades;
            }

            function validateGrades(grades) {
                return Object.values(grades).every(grade => !isNaN(grade) && grade >= 1 && grade <= 6);
            }

            function calculateVorleistungGrade(grades, level) {
                if (level === 'Langzeitgymnasium') {
                    return (grades.deutschVorleistung + grades.mathVorleistung) / 2;
                } else {
                    return (grades.deutschVorleistungKw + grades.englischVorleistung + grades.franzosischVorleistung + 
                            grades.naturTechnikVorleistung + grades.mathVorleistungKw) / 5;
                }
            }

            function calculatePruefungGrade(grades) {
                return (grades.deutschAufsatz * 0.25 + grades.deutschSprachbetrachtung * 0.25 + grades.mathPruefung * 0.5);
            }

            function displayResults(vorleistungGrade, pruefungGrade, finalGrade, withVorleistung) {
                const gesamtnoteResult = document.getElementById('gesamtnote-result');
                const passingGrade = withVorleistung ? 4.75 : 4.5;
                const passed = finalGrade >= passingGrade;

                // Update text content
                document.getElementById('vornote-result').textContent = withVorleistung ? vorleistungGrade.toFixed(2) : t("notConsidered");
                document.getElementById('pruefungsnote-result').textContent = pruefungGrade.toFixed(2);
                gesamtnoteResult.textContent = finalGrade.toFixed(2);

                // Apply styles based on pass/fail status
                gesamtnoteResult.style.color = passed ? 'green' : 'red';
                gradeStatus.textContent = passed ? t("success") : t("fail");
                gradeStatus.className = passed ? 'gymi-success' : 'gymi-error';
                gradeStatus.style.color = passed ? 'green' : 'red';

            }

            calculateBtn.addEventListener('click', calculateFinalGrade);
        });
    
        jQuery(document).ready(function () {
            // Show/hide fields based on level selection
            jQuery("#level-selection").on("change", function () {
                const level = jQuery(this).val();
                jQuery(".langzeitgymnasium-vornoten, .kurzzeitgymnasium-vornoten").hide();
                if (level === "Langzeitgymnasium") {
                    jQuery(".langzeitgymnasium-vornoten").show();
                } else if (level === "Kurzzeitgymnasium") {
                    jQuery(".kurzzeitgymnasium-vornoten").show();
                } else if (level === "BMS") {
                    jQuery(".kurzzeitgymnasium-vornoten").show();
                } else if (level === "HMS") {
                    jQuery(".kurzzeitgymnasium-vornoten").show();
                } else if (level === "FMS") {
                    jQuery(".kurzzeitgymnasium-vornoten").show();
                }
                jQuery("#level-selection-error").text(""); // Clear validation message
                if (jQuery("#grade-weight-selection").val() === "no") {
                    jQuery(".vornoten-zählt, .langzeitgymnasium-vornoten, .kurzzeitgymnasium-vornoten").hide();
                }
            });

            // Show/hide vornote fields based on selection
            jQuery("#grade-weight-selection").on("change", function () {
                const level = jQuery('#level-selection').val();
                if (jQuery(this).val() === "yes") {
                    if (level === 'Langzeitgymnasium') {
                        jQuery('.langzeitgymnasium-vornoten').show();
                    } else if (level === 'Kurzzeitgymnasium') {
                        jQuery('.kurzzeitgymnasium-vornoten').show();
                    } else if (level === 'BMS') {
                        jQuery('.kurzzeitgymnasium-vornoten').show();
                    } else if (level === 'HMS') {
                        jQuery('.kurzzeitgymnasium-vornoten').show();
                    } else if (level === 'FMS') {
                        jQuery('.kurzzeitgymnasium-vornoten').show();
                    }
                    jQuery('.vornoten-zählt').show();
                } else {
                    jQuery(".vornoten-zählt, .langzeitgymnasium-vornoten, .kurzzeitgymnasium-vornoten").hide();
                }
                jQuery("#grade-weight-selection-error").text(""); // Clear validation message
                if (jQuery("#grade-weight-selection").val() === "no") {
                    jQuery(".vornoten-zählt, .langzeitgymnasium-vornoten, .kurzzeitgymnasium-vornoten").hide();
                }
            });

          

            // Validate input fields on change
            jQuery(".form-control").on("input", function () {
                const input = jQuery(this);
                const value = parseFloat(input.val().trim());

                // Remove any previous error messages placed outside the .custom-number-input div
                input.closest('.form-group').find(".error-message").remove();

                if (input.val().trim() === "") {
                    input.closest('.form-group').append('<span class="error-message">' + t("fieldEmpty") + '</span>');
                } else if (value < 1 || value > 6) {
                    input.closest('.form-group').append('<span class="error-message">' + t("fieldRange") + '</span>');
                }
            });

            // Form validation on submit
            jQuery("#calculate-grade").on("click", function () {
                let isValid = true;

                 if (jQuery("#level-selection").val() === null) {
                    jQuery("#level-selection-error").text(t("levelError"));
                    isValid = false;
                }

                if (jQuery("#grade-weight-selection").val() === null) {
                    jQuery("#grade-weight-selection-error").text(t("weightError"));
                    isValid = false;
                }

                // Clear previous errors
                jQuery(".error-message").remove();

                jQuery(".form-control").each(function () {
                    const input = jQuery(this);
                    const value = parseFloat(input.val().trim());

                    // Remove existing error messages
                    input.closest('.form-group').find(".error-message").remove();

                    if (input.val().trim() === "") {
                        input.closest('.form-group').append('<span class="error-message">' + t("fieldEmpty") + '</span>');
                        isValid = false;
                    } else if (value < 1 || value > 6) {
                        input.closest('.form-group').append('<span class="error-message">' + t("fieldRange") + '</span>');
                        isValid = false;
                    }
                });

                if (isValid) {
                    calculateFinalGrade();
                }
            });
        });
    </script>
    <!-- Section End -->

    <?php 
    return ob_get_clean();
}
add_shortcode('grade_calculation_section_shortcode', 'grade_calculation_section_form_shortcode');
// [grade_calculation_section_shortcode]


// blog_details_page_silder Shortcode -----------------------------------------------------------------------------------
function blog_details_page_silder_shortcode() {
    $current_blog_id    = get_the_ID();
    $slider_title       = get_field('blog_slider_title',$current_blog_id);
    $slider_images      = get_field('blog_slider_images',$current_blog_id); 

    if ($slider_images) {
    ob_start(); ?>
    
    <!-- Section Start -->
    <div class="brxe-block blog-details-image-silder-wrapper"> 
        <div class="custom_blog_slider">
            <div class="brxe-container">                
                <div class="blog-slider-wrapper owl-carousel owl-theme">                                        
                    <?php 
                            //print_r($slider_images);
                        foreach ($slider_images as $image) :
                        $image_url = esc_url($image['url']);
                        $image_alt = esc_attr($image['title']);
                        $image_width = esc_attr($image['width']);
                        $image_height = esc_attr($image['height']);

                        echo '<div class="item test"><img src="' . $image_url . '" alt="' . $image_alt . '" width="' . $image_width . '" height="' . $image_height . '"></div>';
                    endforeach; ?>      
                                  
                </div>
            </div>
        </div> 
    </div>
    <!-- Section End -->
     <script>
        jQuery(document).ready(function($) {
            jQuery('.blog-slider-wrapper').owlCarousel({
                loop:true,
                margin:10, 
                items:1,
                nav:true,
                dots: false,
                navigationText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"]
            })
        })
     </script>

    <?php } return ob_get_clean();
}
add_shortcode('blog_details_image_silder', 'blog_details_page_silder_shortcode');
// [blog_details_image_silder]
