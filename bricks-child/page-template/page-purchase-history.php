<?php
/*
Template Name: Purchase History Template
*/
get_header();

if (!is_user_logged_in()) { wp_redirect(home_url('/mein-konto/')); exit; }

$current_user   = wp_get_current_user();
$user_id        = $current_user->ID;
?>
<section class="purchase_history_section">
    <div class="brxe-container">
        <a href="<?php echo home_url(); ?>" class="prev_link"><span><i class="fas fa-arrow-left-long"></i></span><?php _e('Gehe zurück', 'bricks-child'); ?></a>
        <?php 
        // Get the current user's orders
        $orders = wc_get_orders(array(
            'customer_id' => $user_id,
            'status' => 'completed', // You can change this to any status you want
        ));

        if ($orders) { ?>
        <div class="tbl_responsive">
            <table class="custom_tbl">
                <thead>
                    <tr>
                        <!-- <th class="numbers"><?php //_e('Sr. Nr', 'bricks-child'); ?></th> -->
                        <th class="dates"><?php _e('Datum', 'bricks-child'); ?></th>
                        <th><?php _e('Produktname', 'bricks-child'); ?></th>
                        <th class="status"><?php _e('Status', 'bricks-child'); ?></th>
                        <th><?php _e('Gesamtmenge', 'bricks-child'); ?></th>
                        <th class="actions"><?php _e('Aktionen', 'bricks-child'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $i = 1;
                    foreach ($orders as $order) {
                        $order_id = $order->get_id();
                        $order_date = $order->get_date_completed();
                        // Convert WC_DateTime to DateTime
                        $date = new DateTime($order_date->date('Y-m-d H:i:s'));

                        // Set locale to German
                        setlocale(LC_TIME, 'de_DE.UTF-8'); // Ensure your server supports this locale

                        // Set the start date as the completion date
                        $formatted_date = strftime('%e %B %Y', strtotime($order_date->date('Y-m-d')));
                        // $formatted_date = date('j F Y', strtotime($order_date));

                        foreach ($order->get_items() as $item) {



                            $item_id = $item->get_id();
                            $product_id = $item->get_product_id();
                            $product = wc_get_product($product_id);
                            $name_arr = explode(' - ', $item->get_name());
                            $product_name = $name_arr['0'].' - '.$name_arr['2'];
                            $product_quantity = $item->get_quantity();
                            $product_total = $item->get_total();
                            $formatted_total = wc_price($product_total);
                            
                            // Get the expiration date and check if expired
                            $expire_date = $item->get_meta('group_expiry_date', true);
                            $current_timestamp = strtotime(date('d-m-Y'));
                            $expire_timestamp = strtotime($expire_date);
                            $isexpired = false;
                            $test = true;
                            $var_id = $item->get_variation_id();
                                $group_ids = get_post_meta($var_id, '_related_group', true);
                                $groupid = $group_ids[0];
                            if ($expire_timestamp < $current_timestamp) {
                                $order_status = '<span class="status_error">'.esc_html__('Abgelaufen', 'bricks-child').'</span>';
                                $isexpired = true;
                                $var_id = $item->get_variation_id();
                                $group_ids = get_post_meta($var_id, '_related_group', true);
                                $groupid = $group_ids[0];
                            } else {
                                $order_status = '<span class="status_success">'.esc_html__('Aktiv', 'bricks-child').'</span>';
                            }

                            // Only show renew button if expired
                            $renew_button = $isexpired ? '<a href="javascript:void(0)" class="renew_btn" data-variation-id="'.$var_id.'" data-group-id="'.$groupid.'" data-product-id="'.$product_id.'"><i class="fas fa-redo"></i> '.__('Erneuern', 'bricks-child').'</a>' : '';
                            $extend_button = $test ? '<a href="javascript:void(0)" class="renew_btn" data-variation-id="'.$var_id.'" data-group-id="'.$groupid.'" data-product-id="'.$product_id.'"><i class="fas fa-arrows-rotate"></i> '.__('Verlängern', 'bricks-child').'</a>' : '';
                            ?>
                            <tr>
                                <!-- <td data-title='<?php //_e('Sr. Nr', 'bricks-child'); ?>'><?php //echo $i; ?></td> -->
                                <td data-title='<?php _e('Datum', 'bricks-child'); ?>'><?php echo $formatted_date; ?></td>
                                <td data-title='<?php _e('Produktname', 'bricks-child'); ?>'><?php echo $product_name; ?></td>
                                <td data-title='<?php _e('Status', 'bricks-child'); ?>'><?php echo $order_status; ?></td>
                                <td data-title='<?php _e('Gesamtmenge', 'bricks-child'); ?>'><?php echo $formatted_total; ?></td>
                                <td data-title='<?php _e('Aktionen', 'bricks-child'); ?>'>
                                    <div class="actions_btn">
                                        <a href="<?php echo home_url('/details-zum-kaufverlauf/').'?id='.base64_encode($item_id.'_'.$order_id); ?>" class="view_btn"><i class="far fa-eye"></i> <?php _e('Ansehen', 'bricks-child'); ?></a>
                                        
                                        <?php echo $extend_button; ?>
                                        <?php echo $renew_button; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php $i++; } } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="ph-empty-content">
            <h2><?php _e('Sie haben keine Kaufhistorie.', 'bricks-child'); ?></h2>
        </div>
        <?php } ?>
    </div>
</section>
<script>
    jQuery(document).ready(function($) {
        $('.renew_btn').on('click', function(e) {
            e.preventDefault();

            // Fetch data from the clicked button
            var variationId = $(this).data('variation-id');
            var groupId = $(this).data('group-id');
            var productId = $(this).data('product-id');

            if (!variationId || !groupId || !productId) {
                alert('<?php _e('Required data missing.', 'bricks-child'); ?>');
                return;
            }

            // Show the site-wide loader
            // $('.site-loader').fadeIn();

            // Perform the AJAX request to add the product to the cart
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>', // The URL to send the request to
                type: 'POST',
                data: {
                    action: 'add_to_cart_with_group',
                    product_id: productId,
                    variation_id: variationId,
                    group_id: groupId,
                    is_renew: 1,
                },
                success: function(response) {
                    // Handle success - maybe show a message or redirect to the cart page
                    if (response.success) {
                        window.location.href = "<?php echo esc_url(wc_get_checkout_url()); ?>"; // Redirect to checkout
                    } else {
                        alert('<?php _e('Error adding to cart.', 'bricks-child'); ?>');
                    }
                    // $('.site-loader').fadeOut(); // Hide the loader
                },
                error: function() {
                    alert('<?php _e('Something went wrong, please try again later.', 'bricks-child'); ?>');
                    // $('.site-loader').fadeOut(); // Hide the loader
                }
            });
        });
    });
</script>

<?php get_footer(); ?>