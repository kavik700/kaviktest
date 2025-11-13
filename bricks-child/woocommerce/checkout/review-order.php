<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="shop_table woocommerce-checkout-review-order-table">

		<div class="product-checkout">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
		$themepath = get_stylesheet_directory_uri();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = $cart_item['product_id'];
			$varproid   = $_product->get_id();
			$group_ids  = get_post_meta($varproid, '_related_group', true);
			$target_ids = [87908, 87910, 81616, 81614];
			$imsg_ids 	= [87894, 87895, 87898, 87899, 87902, 87903];
			$stel_ids 	= [83173, 83174];    

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) { ?>
				
				<div class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

					<?php $product_id = $_product->get_parent_id();

					    if (!empty($product_id)) {
					    	
						    $parent_product 	  = wc_get_product($product_id);
						    $parent_product_title = $parent_product->get_title();

					    } else { 

					    	$parent_product_title = $_product->get_title();
					    }

					    $prodata   = explode(' - ', $_product->get_name());
					    $groupname = '';
					    $accessday = '';
					    $pkgname   = '';
					    if (!empty($prodata)) 
					    {
					    	$groupname = $prodata['0'];
					    	$accessday = $prodata['1'];
					    	$pkgname   = $prodata['2'];
					    } 
					?>

					<div class="product-name">
						<div class="pro-group-detail">
				    		<h3 class="group-name"><?= $groupname; ?><?php echo esc_html($pkgname == 'Premium Package' ? ' Package' : ''); ?></h3>
				    		<div class="accessday-detail">Zugangstage: 
					            <?php /*if ($accessday == '180' && !empty($group_ids) && array_intersect($group_ids, $target_ids)) { ?>
					                <span class="days">Bis zur Gymiprüfung</span>
					            <?php } elseif ($accessday == '180' && !empty($group_ids) && array_intersect($group_ids, $imsg_ids)) { ?>
					                <span class="days">Bis zur ZAP</span>
					            <?php } else {*/ ?>
					                <span class="days"><?= esc_html($accessday); ?> Tage Zugang</span>
					            <?php //} ?>
					        </div>
				    		<?php //echo "<h2 class=''>".wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '</h2>&nbsp;';
							//echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key );
							echo wc_get_formatted_cart_item_data( $cart_item );?>
				    	</div>
				    	<div class="cart_price_right">				    		
				    		<div class="price_delete_sec">
								<div class="product-total psubtotal<?= $varproid; ?>">
									<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
									
									<div class="delete_p_id">
										<?php

										/* Add delete icon */
										$delete_icon = sprintf(
											'<a href="%s" class="remove" title="%s" data-product_id="%s" data-product_sku="%s"><i class="far fa-trash-can"></i></a>',
											esc_url(wc_get_cart_remove_url($cart_item_key)),
											__('Delete', 'woocommerce'),
											esc_attr($varproid),
											esc_attr($_product->get_sku())
										); 

										echo $delete_icon; ?>
									</div>
								</div>
							</div>	
				    	</div>					    
					</div>

					<div class="product-total">
						<div class="package-detail">

							<?php if ($pkgname == 'Trainingsbereich' || $pkgname == 'Training') {
								echo '<div class="icon_card checkout_new">
						                <div class="card_icon">
						                    <div class="icon_circle plan-icon">
						                        <img decoding="async" src="'.$themepath.'/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
						                    </div>
						                    <p class="card_img_txt">'.__("Training Program", "woocommerce").'</p>
						                </div>
						            </div>';

							} else if ($pkgname == 'Premium Package') {

								echo '<div class="icon_card_icon checkout_new">
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Practice Simulations", "woocommerce").'</p>
					                </div>
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Training Program", "woocommerce").'</p>
					                </div>
					            </div>';

							} else if ($pkgname == 'Simulation' || $pkgname == 'Prüfungssimulation') {

								echo '<div class="icon_card checkout_new">
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Practice Simulations", "woocommerce").'</p>
					                </div>
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
								echo '<div class="icon_card_icon checkout_new">';
					            if (empty(array_intersect($stel_ids, $group_ids))) :
				                	echo '<div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/icons_4.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Aufsatzkorrektur", "woocommerce").'</p>
					                </div>';
					            endif;

				               	echo '<div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/icon_4.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Persönlicher Tutor", "woocommerce").'</p>
					                </div>
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/icon_3.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.$tutoringText.'</p>
					                </div>
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/practice-simulations.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Practice Simulations", "woocommerce").'</p>
					                </div>
					                <div class="card_icon">
					                    <div class="icon_circle plan-icon">
					                        <img decoding="async" src="'.$themepath.'/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
					                    </div>
					                    <p class="card_img_txt">'.__("Training Program", "woocommerce").'</p>
					                </div>
					            </div>';

							} else {} ?>

						</div>
						<div class="product-quantity">

							<?php if (!has_term('renew', 'product_cat', $product_id)) { ?>

								<div class="quantity">
									<input type="button" value="-" class="minus">
									<input type="number" step="1" min="1" max="" name="quantity"
										value="<?= $cart_item['quantity']; ?>" product-id="<?= $varproid; ?>"
										title="Qty" class="input-text qty text" size="4" pattern="[0-9]*" inputmode="numeric" disabled>
									<input type="button" value="+" class="plus">
									<input type="hidden" name="cart_item-key<?= $varproid; ?>" id="cart_item-key<?= $varproid; ?>"
										value="<?= $cart_item_key; ?>">
								</div>

							<?php } ?>

						</div>
					</div>
				</div>
			<?php }
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>
	<div>

		<div class="cart-subtotal">
			<p><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></p>
			<p><?php wc_cart_totals_subtotal_html(); ?></p>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<p><?php wc_cart_totals_coupon_label( $coupon ); ?></p>
				<p><?php wc_cart_totals_coupon_html( $coupon ); ?></p>
			</div>
		<?php endforeach; ?>

		<?php /*if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

			<?php wc_cart_totals_shipping_html(); ?>

			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

		<?php endif;*/ ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="fee">
				<p><?php echo esc_html( $fee->name ); ?></p>
				<p><?php wc_cart_totals_fee_html( $fee ); ?></p>
			</div>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="tax-total">
					<p><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></p>
					<p><?php wc_cart_totals_taxes_total_html(); ?></p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<div class="order-total">
			<p><?php esc_html_e( 'Total', 'woocommerce' ); ?></p>
			<p class="order_total_price"><?php wc_cart_totals_order_total_html(); ?></p>
		</div>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</div>
</div>
