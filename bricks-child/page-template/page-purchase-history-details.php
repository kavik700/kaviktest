<?php
/*
    Template Name: Purchase History Details Template
*/
get_header();
if (!is_user_logged_in()) { wp_redirect(home_url('/mein-konto/')); exit; }

if (!array_key_exists('id', $_GET)) { wp_redirect(home_url('/purchase-history/')); exit; }

$current_user   = wp_get_current_user();
$user_id        = $current_user->ID; 
$ids            = base64_decode($_GET['id']);
$ids_arr        = explode('_', $ids);
$order_id       = $ids_arr['1'];
$order_item_id  = $ids_arr['0'];
$order          = wc_get_order( $order_id );

// Get all items from the order
$items          = $order->get_items();
// Initialize a variable to hold the specific order item
$specific_item  = null;

// Loop through the items to find the specific item by ID
foreach ( $items as $item_id => $item ) {  if ( $item_id == $order_item_id ) { $specific_item = $item;  break; }  } 
?>
<section class="purchase_history_detail_section">
    <div class="brxe-container">
        <a href="<?php echo home_url('/kaufhistorie/'); ?>" class="prev_link">
            <span><i class="fas fa-arrow-left-long"></i></span><?php _e('Zurück zur Kaufhistorie', 'bricks-child'); ?>
        </a>
        <div class="course_overview_wrap">
            <h2><?php _e('Bestellübersicht', 'bricks-child'); ?></h2>
            <p class="desc"><?php _e('Auftragsbestätigung', 'bricks-child'); ?> <span>#<?php echo $order->get_order_number(); ?></span></p>
            <div class="course_overview_card">
                <?php
                $themepath = get_stylesheet_directory_uri();
                foreach ( $items as $item_id => $item ) {
                    $product = $item->get_product();
                    $product_name = $product ? $product->get_name() : '';
                    $product_price = $item->get_total();
                    $item_name     = $item->get_name();
                    $accessdays    = explode(' - ', $item_name)[1];
                    $quantity      = $item->get_quantity();
                    $accessdays    = $accessdays * $quantity;

                    $prodata = explode(' - ', $product_name);
                    $groupname = $prodata[0] ?? '';
                    $accessday = $prodata[1] ?? '';
                    $pkgname   = $prodata[2] ?? '';
                    ?>
                    <div class="course_card">
                        <div class="course_info">
                            <div class="course_title">
                                <h4><?= esc_html($groupname); ?></h4>
                                <div class="course-programs">
                                    <?php 
                                    if ($pkgname == 'Trainingsbereich' || $pkgname == 'Training') {
                                        echo '<div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/training-program.svg" alt="Training Program"/>
                                                </span>
                                                <h5 class="title">'.__("Training Program", "woocommerce").'</h5>
                                            </div>';
                                    } elseif ($pkgname == 'Premium Package') {
                                        echo '<div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Practice Simulations"/>
                                                </span>
                                                <h5 class="title">'.__("Practice Simulations", "woocommerce").'</h5>
                                            </div>
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/training-program.svg" alt="Training Program"/>
                                                </span>
                                                <h5 class="title">'.__("Training Program", "woocommerce").'</h5>
                                            </div>';
                                    } elseif ($pkgname == 'Simulation' || $pkgname == 'Prüfungssimulation') {
                                        echo '<div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Practice Simulations"/>
                                                </span>
                                                <h5 class="title">'.__("Practice Simulations", "woocommerce").'</h5>
                                            </div>';
                                    } else if ($pkgname == 'Pro Premium Package') {
                                        $tutoringLessons = 0;
                                        $tutoringText = '';

                                        if ($accessday == '30') {
                                            $tutoringLessons = 1;
                                            $tutoringText = __('1 Nachhilfe-Lektion', 'woocommerce');
                                        } else if ($accessday == '90') {
                                            $tutoringLessons = 3;
                                            $tutoringText = __('3 Nachhilfe-Lektionen', 'woocommerce');
                                        } else if ($accessday == '180') {
                                            $tutoringLessons = 6;
                                            $tutoringText = __('6 Nachhilfe-Lektionen', 'woocommerce');
                                        }

                                        if ($groupname !== 'Stellwerktest Vorbereitung') {
                                            echo '
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/icons_4_color.svg" alt="Icon">
                                                </span>
                                                <h5 class="title">'.__("Aufsatzkorrektur", "woocommerce").'</h5>
                                            </div>';
                                        }
                                        echo '
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/icon_4_color.svg" alt="Icon">
                                                </span>
                                                <h5 class="title">'.__("Persönlicher Tutor", "woocommerce").'</h5>
                                            </div>
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/icon_3_color.svg" alt="Icon">
                                                </span>
                                                <h5 class="title">'.$tutoringText.'</h5>
                                            </div>
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Icon">
                                                </span>
                                                <h5 class="title">'.__("Practice Simulations", "woocommerce").'</h5>
                                            </div>
                                            <div class="course_wrap">
                                                <span class="icon">
                                                    <img src="'.$themepath.'/assets/images/training-program.svg" alt="Icon">
                                                </span>
                                                <h5 class="title">'.__("Training Program", "woocommerce").'</h5>
                                            </div>';

                                    }

                                    ?>
                                </div>
                            </div>
                            <div class="course_price"><?= wc_price($product_price); ?></div>
                        </div>
                        <div class="course_dates">
                            <?php 
                            $completion_date = $order->get_date_completed();

                            if ( $completion_date ) {
                                $accdays = '+' . $accessdays . ' days';

                                $start_date_obj = clone $completion_date;
                                $end_date_obj   = clone $completion_date;
                                $expire_obj     = clone $completion_date;

                                $end_date_obj->modify($accdays);
                                $expire_obj->modify($accdays)->modify('+1 days');

                                setlocale(LC_TIME, 'de_DE.UTF-8');

                                $sdate = strftime('%e. %B %Y', strtotime($start_date_obj->format('Y-m-d')));
                                $edate = strftime('%e. %B %Y', strtotime($end_date_obj->format('Y-m-d')));

                                // Check if a custom group expiry date is stored
                                $group_expiry = $item->get_meta('group_expiry_date');
                                if ( $group_expiry ) {
                                    $expiry_obj = DateTime::createFromFormat('d-m-Y', $group_expiry);
                                } else {
                                    $expiry_obj = $expire_obj;
                                }
                                $expire_formatted = strftime('%e. %B %Y', strtotime($expiry_obj->format('Y-m-d')));
                                ?>
                                <div class="start_date">
                                    <?php esc_html_e('Startdatum', 'woocommerce'); ?> 
                                    <?= esc_html($sdate); ?>
                                </div>
                                <div class="end_date">
                                    <?php esc_html_e('Enddatum', 'woocommerce'); ?> 
                                    <?= esc_html($expire_formatted); ?>
                                </div>
                                
                            <?php } else { ?>
                                <div class="order_status">
                                    <?php esc_html_e('Order is not yet completed.', 'woocommerce'); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>
<?php get_footer(); ?>