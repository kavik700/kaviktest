<?php
/*
Template Name: Courses Template
*/
get_header();

if (!is_user_logged_in()) { wp_redirect(home_url('/mein-konto/')); exit; }
    
$current_user           = wp_get_current_user();
$user_id                = $current_user->ID;
$profile_picture_url    = get_user_meta($user_id, 'moopenid_user_avatar', true);
$fname                  = $current_user->first_name; 
$lname                  = $current_user->last_name; 
$uname                  = $fname.' '.$lname;
$country                = get_user_meta($user_id, 'billing_country', true);
$state                  = get_user_meta($user_id, 'billing_state', true);
// Use WooCommerce to get full country and state names
$billing_country        = WC()->countries->countries[$country];
$billing_state          = WC()->countries->states[$country][$state];
$location               = $billing_state.', '.$billing_country;
if (empty($fname)) { $uname = $current_user->display_name; }
if (empty($profile_picture_url)) {  $profile_picture_url = wp_get_attachment_url('6813'); }    
?>
<section class="course_listing_section">
    <div class="brxe-container">        
        <div class="course_events_wrap">
            <div class="course_event_facts_box">
                <div class="course_event_profile">
                    <div class="user_profile">
                        <img src="<?php echo esc_url($profile_picture_url); ?>" alt="profile-image" />
                    </div>
                    <h2 class="user_name"><?php echo $uname; ?></h2>
                    <?php if ($billing_country || $billing_state) : ?>
                        <p class="user_location">
                            <?php 
                            if ($billing_state && $billing_country) {
                                echo $billing_state . ', ' . $billing_country;
                            } elseif ($billing_state) {
                                echo $billing_state;
                            } elseif ($billing_country) {
                                echo $billing_country;
                            }
                            ?>
                        </p>
                    <?php endif; ?>
                    <a href="<?php echo home_url('/mein-profil'); ?>" class="btn_setting"><?php _e('Einstellungen anpassen','bricks-child'); ?></a>
                </div>
                <div class="course_event_content">
                    <h3><?php _e('studypeak Fakten & Zahlen','bricks-child'); ?></h3>
                    <div class="review_rating">
                        <h4><?php _e('9,000+','bricks-child'); ?></h4>
                        <p><?php _e('Übungsfragen','bricks-child'); ?></p>
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/review_star.svg" class="star_img"/>
                    </div>
                    <p><?php _e('studypeak ist eine einzigartige Lernplattform zur Vorbereitung verschiedenste Prüfungen. Bei uns kannst du dich mit zahlreichen Übungsfragen und Lösungen gezielt auf deine Prüfung vorbereiten. ','bricks-child'); ?></p>
                    <!-- <p class="small"><?php //_e('1 Unbegrenzten Zugriff auf alle Übungsfragen erhältst du mit unserem unschlagbaren Online-Paket schon ab nur 87 CHF.','bricks-child'); ?></p> -->
                </div>
            </div>
            <?php echo do_shortcode('[latest_event_course]'); ?>
            <?php //echo do_shortcode('[next_event_module]'); ?>
        </div>
        <div class="online_kurs_wrap">
            <div class="achievements_wrap" style="margin-bottom: 50px;">
                <h3><?php _e('Meine Meilensteine','bricks-child'); ?></h3>
                <p>Dein Ziel ist es, so viele Trophäen wie möglich zu ergattern, je mehr, desto besser! Wenn du im Kurs immer weiter fortschreitest und die korrekten Antworten auf die Quizze hast, wirst du automatisch mehr Trophäen sammeln. Hast du schliesslich alle Trophäen vollständig gesammelt, schaltest du einige Vorteile frei. Viel Spass beim Sammeln!</p>
                <div style="margin-top: 20px;">
                    <?php echo do_shortcode('[ld_my_achievements]'); ?>
                </div>
            </div>

            <h3><?php _e('Die Meilensteine erklärt','bricks-child'); ?></h3>
            <div class="custom_accordion new-medal-section" style="margin-bottom: 50px;">  
                <div class="accordion_item">
                    <a href="#">
                       <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/learning-king-icon.png" class="star_img"/><?php _e('Lernkönig','bricks-child'); ?>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('Sobald du den gesamten Kurs geschafft hast, erhältst du das Abzeichen für den Lernkönig oder die Lernkönigin. Dann kannst du mächtig stolz sein, denn du hast einen unserer Kurse vollständig, sogar mit Bravour, abgeschlossen!','bricks-child'); ?></p>
                    </div>
                </div>
                <div class="accordion_item">
                    <a href="#">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/medals-lerner-icon.png" class="star_img"/><?php _e('Medaillen Lerner','bricks-child'); ?>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('Immer wenn du ein Kapitel abschliesst, erhältst du eine Medaille. Harte und fleissige Arbeit soll sich schliesslich bezahlt machen.','bricks-child'); ?></p>
                    </div>
                </div>
                <div class="accordion_item">
                    <a href="#">
                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/champion-icon.png" class="star_img"/><?php _e('Wissenschampion','bricks-child'); ?>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('Du erhältst die Trophäe, sobald du das Quiz richtig gelöst hast. Es belohnt damit dein Wissen und die Fähigkeit, dieses Wissen anzuwenden!','bricks-child'); ?></p>
                    </div>
                </div>
            </div>

            <?php if (current_user_can('administrator')): ?>
            <div class="essays_wrap" style="margin-bottom: 50px;">
                <h3><?php _e('Meine Aufsätze','bricks-child'); ?></h3>
                <a href="/meine-essays/" class="my-essay-btn">
                    <?php _e('Meine Essays anzeigen','bricks-child'); ?>
                </a>
            </div>
            <?php endif; ?>

            <h3><?php _e('Mein studypeak-Onlinekurs','bricks-child'); ?></h3>
            <div class="custom_accordion">  
                <div class="accordion_item">
                    <a href="#">
                        <i class="icon icon-square-check"></i><?php _e('Testsimulationen','bricks-child'); ?> <span><?php _e('Teste dein Wissen','bricks-child'); ?></span>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('Die Prüfungssimulationen bieten einen Eindruck der echten Prüfungssituation und zeigen auf wie es ist, die Prüfung unter Zeitdruck zu absolvieren. In unserem exklusiven Simulationstests können sich Schülerinnen und Schüler einen Eindruck von ihrem aktuellen Wissensstand verschaffen und ein Gefühl für die Prüfungsaufgaben bekommen. Wir haben komplett neue Tests auf Basis der aktuellen Prüfungen entwickelt, mit welchen sie sich ideal vorbereiten können. Die Tests werden ausgewertet, wobei teilweise Musterlösungen vorgeschlagen werden.  welche bei der Vorbereitung auf die Prüfung helfen sollen. Bei Bedarf kann das Testergebnis gegen eine zusätzliche Gebühr mit dem Schulleiter besprochen werden.','bricks-child'); ?></p>
                    </div>
                </div>
                <div class="accordion_item">
                    <a href="#">
                        <i class="icon icon-square-sidebar"></i><?php _e('studypeak-Akademie','bricks-child'); ?> <span><?php _e('100% professionelle und gezielte Unterstützung','bricks-child'); ?></span>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('In unserer studypeak-Akademie bereiten dich Experten gezielt auf die verschiedenen Aufgaben und Kenntnisse der Prüfungen vor. In unseren verschiedensten Kursen lernst du genau das, was du für die individuellen Prüfungen brauchst. Viel Spass dabei!','bricks-child'); ?></p>
                    </div>
                </div>
                <div class="accordion_item">
                    <a href="#">
                        <i class="icon icon-square-play"></i><?php _e('Übungen','bricks-child'); ?> <span><?php _e('Erweitere dein Wissen spielerisch','bricks-child'); ?></span>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i></span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e('Erweitere dein Wissen durch spielerische Übungen, um dich ideal auf die Prüfungen vorzubereiten. Je mehr du lernst und übst, desto einfacher werden dir die Aufgaben in der Prüfung fallen.','bricks-child'); ?></p>
                    </div>
                </div>
                <div class="accordion_item">
                    <a href="#">
                        <i class="icon icon-square-grid"></i><?php _e('Lernplan','bricks-child'); ?> <span><?php _e('Herunterladen, ausdrucken, lernen','bricks-child'); ?></span>
                        <span class="icon_arrow"><i class="fas fa-chevron-down"></i> </span>
                    </a>
                    <div class="accordion_body">
                        <p><?php _e("Unser Lernplan unterstützt dich bei deiner Vorbereitung und gibt dir eine durchdachte Vorlage für dein Lern- und Übungsprogramm. Runterladen, (optional) ausdrucken und los geht's. Viel Erfolg!","bricks-child"); ?></p>
                        <?php 
                            /*$current_user_id = get_current_user_id();
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
                            
                            $category_links = [];

                            foreach ($learndash_groups as $group) {
                                $group_package_category = $group->group_package_category;
                                
                                // Assign Page ID and Button Text based on category
                                $category_data = match ($group_package_category) {
                                    'multicheck-vorbereitung' => [
                                        'pdf_link' => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Multicheck.pdf',
                                        'button_link' => 'https://studypeak.ch/multicheck-kurse/#brxe-tuqzmo',
                                        'button_text' => 'Lernplan Multicheck'
                                    ],
                                    'gymi-vorbereitung' => [
                                        'pdf_link' => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Gymipruefung.pdf',
                                        'button_link' => 'https://studypeak.ch/gymi-kurse/#brxe-gymi',
                                        'button_text' => 'Lernplan Gymiprüfung'
                                    ],
                                    'ims-bms-fms-hms-vorbereitung' => [
                                        'pdf_link' => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-IMS-Pruefung.pdf',
                                        'button_link' => 'https://studypeak.ch/ims-kurse/#brxe-ims',
                                        'button_text' => 'Lernplan IMS Prüfung'
                                    ],
                                    'stellwerktest-vorbereitung' => [
                                        'pdf_link' => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Stellwerktest.pdf',
                                        'button_link' => 'https://studypeak.ch/stellwerktest-kurse/#brxe-stellwerk',
                                        'button_text' => 'Lernplan Stellwerktest'
                                    ],
                                    default => null
                                };
                                if ($category_data) {
                                    $category_links[] = $category_data;
                                }
                            }

                            // Display only once, but list all PDFs
                            if (!empty($category_links)) { ?>
                                <div class="event_btn_wrap event_pdf_btn">
                                    <?php foreach ($category_links as $data) { ?>
                                        <a href="<?= esc_url($data['pdf_link']); ?>" class="btn_green event_pdf_link" download>
                                             <?= esc_html($data['button_text']); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php }*/
                        ?>

                        <?php
                            $user_id = get_current_user_id();
                            if (!$user_id) return;

                            global $wpdb;

                            // Step 1: Fetch all completed WooCommerce orders for the current user
                            $user_orders = wc_get_orders([
                                'customer_id' => $user_id,
                                'status'      => 'completed',
                                'limit'       => -1,
                            ]);

                            $purchased_group_ids = [];

                            /*if (!empty($orders)) {
                                foreach ($orders as $order) {
                                    foreach ($order->get_items() as $item) {
                                        $variation_id = $item->get_variation_id();
                                        $group_ids = get_post_meta($variation_id, '_related_group', true);
                                        $expiry_date = $item->get_meta('group_expiry_date');

                                        if (!$group_ids) continue;
                                        if (!is_array($group_ids)) {
                                            $group_ids = [$group_ids];
                                        }

                                        if ($expiry_date) {
                                            $expiry_date_obj = DateTime::createFromFormat('d-m-Y', $expiry_date);
                                            if ($expiry_date_obj && $expiry_date_obj >= new DateTime()) {
                                                foreach ($group_ids as $gid) {
                                                    $active_groups[] = intval($gid);
                                                }
                                            }
                                        }
                                    }
                                }
                            }*/

                            // Step 2: Loop through each order and collect related LearnDash group IDs
                            if (!empty($user_orders)) {
                                foreach ($user_orders as $order) {
                                    foreach ($order->get_items() as $order_item) {
                                        $variation_id = $order_item->get_variation_id();
                                        $related_groups = get_post_meta($variation_id, '_related_group', true);

                                        if (empty($related_groups)) continue;

                                        // Ensure it’s always an array
                                        if (!is_array($related_groups)) {
                                            $related_groups = [$related_groups];
                                        }

                                        foreach ($related_groups as $group_id) {
                                            $purchased_group_ids[] = intval($group_id);
                                        }
                                    }
                                }
                            }

                            // Step 3: Stop if the user hasn’t purchased any groups
                            /*if (empty($purchased_group_ids)) {
                                return;
                            }*/

                            // Step 4: Get the ACF field “group_package_category” for each purchased group
                            $purchased_group_categories = [];

                            foreach ($purchased_group_ids as $group_id) {
                                $package_category = get_post_meta($group_id, 'group_package_category', true);

                                $category_details = match ($package_category) {
                                    'multicheck-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Multicheck.pdf',
                                        'button_label' => 'Lernplan Multicheck'
                                    ],
                                    'gymi-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Gymipruefung.pdf',
                                        'button_label' => 'Lernplan Gymiprüfung'
                                    ],
                                    'ims-bms-fms-hms-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-IMS-Pruefung.pdf',
                                        'button_label' => 'Lernplan IMS Prüfung'
                                    ],
                                    'stellwerktest-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Stellwerktest.pdf',
                                        'button_label' => 'Lernplan Stellwerktest'
                                    ],
                                    /*'probezeit-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Probezeit.pdf',
                                        'button_label' => 'Lernplan Probezeit'
                                    ],
                                    'basic-check-vorbereitung' => [
                                        'pdf_url'     => 'https://studypeak.ch/wp-content/uploads/2025/02/Lernplan-Basic-Check.pdf',
                                        'button_label' => 'Lernplan Basic Check'
                                    ],*/
                                    default => null
                                };

                                if ($category_details) {
                                    // Use category slug as key to prevent duplicate buttons
                                    $purchased_group_categories[$package_category] = $category_details;
                                }
                            }

                            // Step 5: Display the Lernplan PDF buttons for purchased group categories
                            if (!empty($purchased_group_categories)) { ?>
                                <div class="event_btn_wrap event_pdf_btn">
                                    <?php foreach ($purchased_group_categories as $category) { ?>
                                        <a href="<?= esc_url($category['pdf_url']); ?>" class="btn_green event_pdf_link" download>
                                            <?= esc_html($category['button_label']); ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } 
                        ?>
    
                    </div>
                </div>                
                <?php 
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

                $has_multicheck = false;

                foreach ($learndash_groups as $group) {
                    if ($group->group_package_category === 'multicheck-vorbereitung') {
                        $has_multicheck = true;
                        break;
                    }
                }

                // Show accordion only if Multicheck purchased
                if ($has_multicheck): ?>
                    <div class="accordion_item">
                        <a href="#">
                            <i class="icon icon-datepicker"></i><?php _e('Timer Multicheck','bricks-child'); ?> 
                            <span><?php _e('Informationen zum Timer für den Multicheck','bricks-child'); ?></span>
                            <span class="icon_arrow"><i class="fas fa-chevron-down"></i> </span>
                        </a>
                        <div class="accordion_body">
                            <p><strong><?php _e("Wichtige Info zu unserem Timer","bricks-child"); ?></strong></p>

                            <p><?php _e("Unsere Zeituhr ist im <strong>Trainingsbereich länger eingestellt</strong> als beim richtigen Multicheck. Dies ist bewusst gewählt und soll dafür sorgen, dass man die Zeit zwar im Blick behält, jedoch auch in Ruhe für den Multicheck üben und sich Zeit lassen kann, eine schwierige Aufgabe in Ruhe zu verstehen.","bricks-child"); ?></p>

                            <p><?php _e("In unserer <strong>Prüfungssimulation</strong> haben wir die <strong>Zeituhr</strong> so angepasst, dass sie <strong>dem originalen Multicheck</strong> entspricht. Die Prüfungssimulation soll den richtigen Multicheck und die Prüfungssituation simulieren, daher auch der Zeitdruck, der damit einhergeht.","bricks-child"); ?></p>

                            <p><?php _e("Danke für euer Verständnis und viel Spass beim Üben!","bricks-child"); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="course_listing_wrap">
            <h3><?php _e('Kurs auswählen', 'bricks-child'); ?></h3>
            <form class="common_form">
                <div class="form-group form_select">
                    <?php
                    // Switch to English language
                    do_action('wpml_switch_language', 'de');
                    // Get the group ID from the URL
                    $current_group_id = isset($_GET['mc_group_id']) ? intval($_GET['mc_group_id']) : null;

                    // Get the user's group IDs dynamically in the English language
                    $group_ids = learndash_get_users_group_ids($user_id);

                    // Switch back to the current language
                    do_action('wpml_switch_language', ICL_LANGUAGE_CODE);

                    // Reorder groups: Always make sure "TRAININGSBEREICH" is first, and "Prüfungssimulation" is below it
                    usort($group_ids, function($a, $b) {
                        $title_a = get_the_title($a);
                        $title_b = get_the_title($b);

                        // Check if "Trainingsbereich" should come first
                        if (strpos($title_a, 'Trainingsbereich') !== false && strpos($title_b, 'Trainingsbereich') === false) {
                            return -1; // "Trainingsbereich" comes first
                        } elseif (strpos($title_b, 'Trainingsbereich') !== false && strpos($title_a, 'Trainingsbereich') === false) {
                            return 1; // "TRAININGSBEREICH" comes first
                        }
                        
                        // Check if "Prüfungssimulation" should come after "TRAININGSBEREICH"
                        if (strpos($title_a, 'Prüfungssimulation') !== false && strpos($title_b, 'Prüfungssimulation') === false) {
                            return -1; // "Prüfungssimulation" comes after
                        } elseif (strpos($title_b, 'Prüfungssimulation') !== false && strpos($title_a, 'Prüfungssimulation') === false) {
                            return 1; // "Prüfungssimulation" comes after
                        }

                        // Default alphabetical order for other cases
                        return strcasecmp($title_a, $title_b);
                    });
                    ?>

                    <?php if (!empty($group_ids)) { ?>
                    <!-- Displaying the select dropdown for available groups -->
                    <select class="form-control" id="course_group_id" name="course_group_id">
                        <option value="" disabled><?php esc_html_e('Kurs auwählen', 'bricks-child'); ?></option>
                        <?php 
                        foreach ($group_ids as $index => $group_id) {
                            // Select the first option by default or the one matching the URL parameter
                            $selected = ($group_id === $current_group_id || (is_null($current_group_id) && $index === 0)) ? 'selected' : '';
                        ?>
                            <option class="package-<?php echo esc_attr($group_id); ?>" value="<?php echo esc_attr($group_id); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html(get_the_title($group_id)); ?>
                            </option>
                        <?php }
                        ?>
                    </select>
                    <?php } else {
                        echo "<h6>Sie haben keine Kurse gefunden</h6>";
                    } ?>
                </div>
            </form>

            <div class="course_listing_card">
                <?php 
                // do_action('wpml_switch_language', 'de');

                // // Get courses for the selected group (or the first group if none selected)
                // $courses = !empty($group_ids) ? learndash_get_group_courses_list($group_ids[0]) : array();

                // do_action('wpml_switch_language', ICL_LANGUAGE_CODE);

                do_action('wpml_switch_language', 'de');

                // Get courses for the selected group (or the first group if none selected)
                /*$group_to_use = !empty($current_group_id) ? $current_group_id : (isset($group_ids[0]) ? $group_ids[0] : null);
                $courses = !empty($group_to_use) ? learndash_get_group_courses_list($group_to_use) : array();

                do_action('wpml_switch_language', ICL_LANGUAGE_CODE);

                $ordered_courses = array();
                foreach ($courses as $courseid) {
                    $course = get_post($courseid);
                    $ordered_courses[] = array(
                        'title' => $course->post_title,
                        'id' => $courseid
                    );
                }

                // Sort the courses array by title
                usort($ordered_courses, function($a, $b) {
                    return strcasecmp($a['title'], $b['title']);
                });

                foreach ($ordered_courses as $course) {
                    $courseid = $course['id'];
                    $ccolor = get_field('course_card_background', $courseid);
                    $cicon = get_field('course_icon', $courseid);
                    $course = get_post($courseid);
                    // print_r($course);
                    // Convert HEX to RGBA
                    $ccolor_rgba = sprintf(
                        'rgba(%d, %d, %d, 0.3)',
                        hexdec(substr($ccolor, 1, 2)),
                        hexdec(substr($ccolor, 3, 2)),
                        hexdec(substr($ccolor, 5, 2))
                    );
                    // Generate permalink with group_id query parameter
                    $course_url = add_query_arg( 'mc_group_id', $group_to_use, get_permalink($course) );
                    ?>
                    <div class="course_list_items">
                        <span class="border-line" style="background-color: <?php echo esc_attr($ccolor); ?>;"></span>
                        <div class="course_title" style="background-color: <?php echo esc_attr($ccolor_rgba); ?>;">
                            <h4><?php echo esc_html($course->post_title); ?></h4>
                            <img src="<?php echo esc_url($cicon); ?>" />
                        </div>
                        <a href="<?php echo esc_url($course_url); ?>" class="course_link" style="background-color: <?php echo esc_attr($ccolor); ?>; color: #FFF;">
                            <?php _e('Mehr anzeigen', 'bricks-child'); ?> <span><i class="fas fa-arrow-right-long"></i></span>
                        </a>
                    </div>
                <?php } */

                // Define Gymivorbereitung Package group ID (Replace with actual ID)
                $gymivorbereitung_group_id = 32332; // Change this to the actual Group ID

                // Get the selected group ID
                $group_to_use = !empty($current_group_id) ? $current_group_id : (isset($group_ids[0]) ? $group_ids[0] : null);

                // Fetch courses for the selected group
                $courses = !empty($group_to_use) ? learndash_get_group_courses_list($group_to_use) : array();

                do_action('wpml_switch_language', ICL_LANGUAGE_CODE);

                $ordered_courses = array();
                foreach ($courses as $courseid) {
                    $course = get_post($courseid);
                    $ordered_courses[] = array(
                        'title' => $course->post_title,
                        'id' => $courseid
                    );
                }

                // Custom sorting for Gymivorbereitung Package group
                if ($group_to_use == $gymivorbereitung_group_id) {
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

                // Output the courses
                foreach ($ordered_courses as $course) {
                    $courseid = $course['id'];
                    $ccolor = get_field('course_card_background', $courseid);
                    $cicon = get_field('course_icon', $courseid);
                    $course = get_post($courseid);
                    
                    $original_title = $course->post_title;

                    // Title replacement logic
                    if ($group_to_use == 87918 && $original_title === 'Multicheck Gesundheit und Soziales – Prüfungssimulation') {
                        $display_title = 'Multicheck Pharma und Chemie – Prüfungssimulation';
                    } elseif ($group_to_use == 87914 && $original_title === 'Multicheck Gesundheit und Soziales – Prüfungssimulation') {
                        $display_title = 'Multicheck Gesundheit HF – Prüfungssimulation';
                    } else {
                        $display_title = $original_title;
                    }

                    $ccolor_rgba = sprintf(
                        'rgba(%d, %d, %d, 0.3)',
                        hexdec(substr($ccolor, 1, 2)),
                        hexdec(substr($ccolor, 3, 2)),
                        hexdec(substr($ccolor, 5, 2))
                    );

                    $course_url = add_query_arg('mc_group_id', $group_to_use, get_permalink($course));
                    ?>
                    <div class="course_list_items">
                        <span class="border-line" style="background-color: <?php echo esc_attr($ccolor); ?>;"></span>
                        <div class="course_title" style="background-color: <?php echo esc_attr($ccolor_rgba); ?>;">
                            <h4><?php echo $display_title; ?><?php //echo esc_html($course->post_title); ?></h4>
                            <img src="<?php echo esc_url($cicon); ?>" />
                        </div>
                        <a href="<?php echo esc_url($course_url); ?>" class="course_link" style="background-color: <?php echo esc_attr($ccolor); ?>; color: #FFF;">
                            <?php _e('Mehr anzeigen', 'bricks-child'); ?> <span><i class="fas fa-arrow-right-long"></i></span>
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    // jQuery('#course_group_id').on('change',function(){
    //     var group_id = jQuery(this).val();
    //     jQuery('.site-loader').show();
    //     // var ajaxurl = frontend_ajax.ajaxurl;
    //     jQuery.ajax({
    //         url: '<?php echo admin_url("admin-ajax.php"); ?>',
    //         type: 'POST',   
    //         data: {
    //             action : 'get_courses_by_group',
    //             group_id : group_id,
    //         },
    //         success : function (response){
    //             jQuery('.site-loader').hide();
    //             jQuery('.course_listing_card').html(response);
    //         },
    //     }); 
    // });

    jQuery(document).ready(function () {
        // Function to call the AJAX request
        function fetchCoursesByGroup(group_id) {
            if (!group_id) return; // Exit if no group_id is provided
            jQuery('.site-loader').show();
            jQuery.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                type: 'POST',
                data: {
                    action: 'get_courses_by_group',
                    group_id: group_id,
                },
                success: function (response) {
                    jQuery('.site-loader').hide();
                    jQuery('.course_listing_card').html(response);
                },
            });
        }

        // Handle onchange event
        jQuery('#course_group_id').on('change', function () {
            var group_id = jQuery(this).val();
            fetchCoursesByGroup(group_id);
        });

        // Handle URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const group_id = urlParams.get('group_id');
        if (group_id) {
            fetchCoursesByGroup(group_id);
        }
    });
</script>
<style>
    .my-essay-btn {
        display: block;
        width: 250px;
        font-weight: 500;
        font-size: 16px;
        line-height: 22px;
        text-align: center;
        padding: 15px 20px !important;
        color: #1A3A27;
        background-color:#ddf1ee;
        border-radius: 10px;
        transition: 0.5s ease-in-out all;
    }

    .my-essay-btn:hover {
        background-color:#bcf1e9;
    }
</style>
<?php get_footer(); ?>