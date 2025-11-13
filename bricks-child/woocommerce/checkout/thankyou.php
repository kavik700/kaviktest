<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
$user_id 	  = $current_user->ID;
$fname 		  = $current_user->first_name; 
$lname 		  = $current_user->last_name; 
$uname 		  = $fname;
if (empty($fname)) { $uname = $current_user->display_name; } ?>

<div class="woocommerce-order thank_you_page">

	<?php if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );		

		if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay">
					<?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay">
						<?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) ); ?>

			<div class="woo-order-overview thankyou_order_details order_details">

				
					<div class="order_view" id="order-details">
						<h2 class="sub_title"><?php esc_html_e('Order confirmation', 'woocommerce'); ?> #<?= $order->get_order_number(); ?> <span id="brxe-1234-order-number" style="display:none;"><?php echo $order->get_order_number(); ?></span><button id="brxe-1234-order"><i class="icon icon-copy"></i></button></h2>
						<!-- <p class="sub_desc"><a href="mailto:wnuesch@yahoo.fr"> wnuesch@yahoo.fr </a> <?php //esc_html_e('geprüft, welche Einrichtungsanweisungen keinen Zugriff auf E-Mails ermöglichen? ', 'woocommerce'); ?></p>
						<p><a href="#">Überprüfen Sie die unterstützten Antworten</a></p> -->
						<?php 
						$added_from_page = $order->get_meta('_added_from_page'); 

						// Define an array of pages and their corresponding titles
						$page_titles = array(
						    'multicheck-vorbereitung' => 'Multicheck-Vorbereitung!',
						    'gymi-vorbereitung'       => 'Gymivorbereitung!',
						    'ims-bms-fms-hms-vorbereitung'        => 'ZAP-IMS Vorbereitung!',
						    'stellwerktest-vorbereitung'  => 'Stellwerktest Vorbereitung!',
						);

						// Extract the page slug from the URL
						$parsed_url = parse_url($added_from_page, PHP_URL_PATH);
						$page_slug = trim($parsed_url, '/');
						$matched_title = 'Ihre Vorbereitung!'; // Default title

						// Check if the page slug matches any predefined title
						foreach ($page_titles as $slug => $title) {
						    if (strpos($page_slug, $slug) !== false) {
						        $matched_title = $title;
						        break;
						    }
						}
						?>

						<div class="thank-you-blog">
						    <h3>Beginnen Sie mit der <?php echo esc_html($matched_title); ?></h3>
						    <a href="<?php echo home_url('meine-kurse'); ?>" class="btn">Los geht's</a>
						    <p>Wir haben dir soeben eine <b>Bestätigungs-E-Mail mit deinen Zugangsdaten</b> geschickt.</p>
						</div>	
						<p><a href="javascript:void(0)" onclick="openCartPopup();" class="btn view-ordr-detail" id="cart_link">Bestelldetails anzeigen</a></p>
					</div>
					<div class="pricing-popup cart_popup" id="cart_popup" style="display: none;">
						<div class="popup-content order-detail-popup">
							<button class="close-popup" onclick="closeCartPopup();">×</button>
							<h3>Bestellübersicht</h3>
							<p>Auftragsbestätigung <span class="ordr-number"> #<?= $order->get_order_number(); ?> </span></p>
							<div class="order-overview">
								<div class="order_detail_view" id="order-details-inner">
									<?php 

										do_action('woocommerce_order_details_before_order_table_items', $order);

										$order_items = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));

										// Set the LearnDash group access dates
										$user_id = $order->get_user_id(); // Get the user ID from the order
										//exit;
										foreach ($order_items as $item_id => $item) {

											$itemid 	   = $item->get_id();
											$item_name 	   = $item->get_name();
											$variationid   = $item->get_variation_id();
											$product 	   = $item->get_product();
											$attributes    = $product->get_attributes();
											$accessdays    = explode(' - ', $item_name)[1];
											$quantity 	   = $item->get_quantity();
											$accessdays    = $accessdays * $quantity;
											$product_title = $product->get_name();
											$product_price = $product->get_price();
							    			$product_id    = $product->get_parent_id();
							    			$package_name  = $attributes['pa_packages']; 
							    			$prodata       = explode(' - ', $product_title);

											$gname   = isset($prodata[0]) ? $prodata[0] : '';
											$aday    = isset($prodata[1]) ? $prodata[1] . ' Tage Zugang' : '';
											$pkgname = isset($prodata[2]) ? implode(' - ', array_slice($prodata, 2)) : '';

											// Extract access days from the product data for later use
											$accessday = isset($prodata[1]) ? $prodata[1] : '';

											error_log($product_id);
											error_log('$product_id');

											$target_ids = [87908, 87910, 81616, 81614, 32332, 86815];
											$imsg_ids 	= [87894, 87895, 87898, 87899, 87902, 87903];
											$bmsg_ids   = [87895, 87894];
									        $hmsg_ids   = [87902, 87903];
									        $fmsg_ids   = [87898, 87899];
											$gparr = get_post_meta($variationid, '_related_group', true);
											// if (!empty($gparr) && array_intersect($gparr, $target_ids) && has_term("180-de", 'product_cat', $product_id)) {
											// 	$aday = "Bis zur Gymiprüfung";
											// 	$formatted_product_title = trim("$gname - $aday - $pkgname", " -");
											// } elseif (!empty($gparr) && array_intersect($gparr, $imsg_ids) && has_term("180-de", 'product_cat', $product_id)) {
											// 	$aday = "Bis zur ZAP";
											// 	$formatted_product_title = trim("$gname - $aday - $pkgname", " -");
											// } else {
												$formatted_product_title = trim("$gname - $aday - $pkgname", " -");
											// }

											// Determine access days based on product category for Pro Premium Package
											$accesssDays = '';
											if (has_term("30-de", 'product_cat', $product_id)) {
											    $accesssDays = '30';
											} elseif (has_term("90-de", 'product_cat', $product_id)) {
											    $accesssDays = '90';
											} elseif (has_term("180-de", 'product_cat', $product_id)) {
											    $accesssDays = '180';
											}
							    		?>
							    			<div class="thanks_cart_box">
												<div class="order-item">
													<div class="order_item_left">

														<h3 class="title"> <?= $formatted_product_title; ?> </h3>

														<?php if ( $package_name == 'trainingsbereich-de' || $package_name == 'training') { ?>

															<div class="icon_card_order checkout_new">
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e('Training Program', 'woocommerce'); ?></p>
												                </div>
												            </div>

														<?php } else if ( $package_name == 'pruefungssimulation-de' || $package_name == 'simulation') { ?>

															<div class="icon_card_order checkout_new">
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg'?>" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e('Practice Simulations', 'woocommerce'); ?></p>
												                </div>						             
												            </div>

														<?php } else if ( $package_name == 'premium-package-de' || $package_name == 'premium-package' ) { ?>

															<div class="icon_card_order checkout_new">
																<div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e('Training Program', 'woocommerce'); ?></p>
												                </div>
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg'?>" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e('Practice Simulations', 'woocommerce'); ?></p>
												                </div>						                
												            </div>
														<?php } else if ($package_name == 'pro-premium-package-de' || $package_name == 'pro-premium-package' ) {
															$tutoringLessons = 0;
															$tutoringText = '';

															// Use the category-based access days
											                if ($accesssDays == '30') {
											                    $tutoringLessons = 1;
											                    $tutoringText = __('1 Nachhilfe-Lektion', 'woocommerce');
											                } else if ($accesssDays == '90') {
											                    $tutoringLessons = 3;
											                    $tutoringText = __('3 Nachhilfe-Lektionen', 'woocommerce');
											                } else if ($accesssDays == '180') {
											                    $tutoringLessons = 6;
											                    $tutoringText = __('6 Nachhilfe-Lektionen', 'woocommerce');
											                } else {
											                    // Fallback: try to use the original accessday variable
											                    if ($accessday == '30') {
											                        $tutoringLessons = 1;
											                        $tutoringText = __('1 Nachhilfe-Lektion', 'woocommerce');
											                    } else if ($accessday == '90') {
											                        $tutoringLessons = 3;
											                        $tutoringText = __('3 Nachhilfe-Lektionen', 'woocommerce');
											                    } else if ($accessday == '180') {
											                        $tutoringLessons = 6;
											                        $tutoringText = __('6 Nachhilfe-Lektionen', 'woocommerce');
											                    } else {}
											                }
														?>
															<div class="icon_card_order checkout_new">
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/training-program.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e("Training Program", "woocommerce"); ?></p>
												                </div>
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/practice-simulations.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e("Practice Simulations", "woocommerce"); ?></p>
												                </div>
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/icon_3_color.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php echo $tutoringText; ?></p>
												                </div>
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/icon_4_color.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e("Persönlicher Tutor", "woocommerce"); ?></p>
												                </div>
												                <?php if ($gname !== 'Stellwerktest Vorbereitung') : ?>
												                <div class="card_icon">
												                    <div class="icon_circle">
												                        <img decoding="async" src="<?= get_stylesheet_directory_uri(); ?>/assets/images/icons_4_color.svg" alt="Icon" class="w-10 h-10">
												                    </div>
												                    <p class="card_img_txt"><?php esc_html_e("Aufsatzkorrektur", "woocommerce"); ?></p>
												                </div>
											                    <?php endif; ?>
												            </div>
												        <?php } ?>

													</div>

													<div class="order_item_right">
														<p class="item_num"> <?= get_woocommerce_currency_symbol() . ' ' . $product_price.'.00'; ?> </p>
													</div>
												</div>
												<?php
												$group_expiry_date = $item->get_meta('group_expiry_date'); // Stored expiry from previous orders

												$completion_date = $order->get_date_completed();
												$order_date = $order->get_date_created(); // Fallback to order creation

												if ($completion_date) {
												    $accdays = '+' . $accessdays . ' days';

												    // Clone and calculate
												    $start_date_obj = clone $completion_date;
												    

													// if (!empty($gparr) && array_intersect($gparr, $target_ids) && has_term('180-de', 'product_cat', $product_id)) 
											        // {        
											        //     $end_date_obj = DateTime::createFromFormat('d.m.Y', '02.03.2026');
											        //     $expire_obj   = DateTime::createFromFormat('d.m.Y', '02.03.2026');
											        //     $expire_obj->modify('+1 days');
											        // }  else if ( !empty($group_ids) && count(array_intersect($group_ids, $bmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id) ) {
											        //     $end_date_obj = DateTime::createFromFormat('d.m.Y', '04.03.2026');
											        //     $expire_obj   = DateTime::createFromFormat('d.m.Y', '04.03.2026');
											        //     $expire_obj->modify('+1 days');
											        // } else if ( !empty($group_ids) && count(array_intersect($group_ids, $hmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id) ) {
											        //     $end_date_obj = DateTime::createFromFormat('d.m.Y', '02.03.2026');
											        //     $expire_obj   = DateTime::createFromFormat('d.m.Y', '02.03.2026');
											        //     $expire_obj->modify('+1 days');
											        // } else if ( !empty($group_ids) && count(array_intersect($group_ids, $fmsg_ids)) > 0 && has_term('180-de', 'product_cat', $product_id) ) {
											        //     $end_date_obj = DateTime::createFromFormat('d.m.Y', '04.03.2026');
											        //     $expire_obj   = DateTime::createFromFormat('d.m.Y', '04.03.2026');
											        //     $expire_obj->modify('+1 days');
											        // } else {
											        	$end_date_obj   = clone $completion_date;
											        	$expire_obj     = clone $completion_date;
											        	$end_date_obj->modify($accdays);
											        	$expire_obj->modify($accdays)->modify('+1 days');
											        // }

												    // Format start & end dates
												    $sdate = strftime('%e. %B %Y', strtotime($start_date_obj->format('Y-m-d')));
												    $edate = strftime('%e. %B %Y', strtotime($end_date_obj->format('Y-m-d')));

												    // Get expiry from meta or fallback to calculated
												    if ($group_expiry_date) {
												        $expiry_obj = DateTime::createFromFormat('d-m-Y', $group_expiry_date);
												    } else {
												        $expiry_obj = $expire_obj;
												    }

												    error_log($expiry_obj->format('Y-m-d'));
													error_log('$expiry_obj date');

												    $formatted_expire = strftime('%e. %B %Y', strtotime($expiry_obj->format('Y-m-d')));
												    ?>
												    <div class="start_end_date">
												        <p class="date sdate"><?php esc_html_e('Startdatum', 'woocommerce'); echo ' ' . esc_html($sdate); ?></p>
												        <p class="date edate"><?php esc_html_e('Enddatum', 'woocommerce'); echo ' ' . esc_html($formatted_expire); ?></p>
												    </div>
												<?php 
													} else {
														echo '<p>Bestellung ist noch nicht abgeschlossen.</p>';
													}
												?>
											</div>
							    			<?php
										}
										do_action('woocommerce_order_details_after_order_table_items', $order);
									?>
								</div>

								<div class="order-summary">
									<?php foreach ($order->get_order_item_totals() as $key => $total) {

										if ($key !== 'payment_method' && $key !== 'Transaction Id') { 

											if ($key == 'cart_subtotal') { ?>
												<div class="thank_subtotal"> 
													<h2 class="txt"> <?= 'Zwischensumme (Mwst. inklusive)'; //echo esc_html($total['label']); ?> </h2>
													<?php echo wp_kses_post($total['value']); ?>
												</div>
											<?php } elseif ($key == 'order_total') { ?>
												<div class="thank_subtotal"> 
													<h2 class="txt"> <?= 'Gesamt'; //echo esc_html($total['label']); ?> </h2>
													<?php echo wp_kses_post($total['value']); ?>
												</div>
											<?php } else {}
										}
									}?>
								</div>
							</div>

							<div class="pdf_section text-center">
								<p class="para">
									<?php 
									    printf(
									        esc_html__('Ihr Abonnement ist so eingestellt, dass es sich %1$snicht automatisch%2$s verlängert. Es endet automatisch am Ablaufdatum. Falls Sie Ihr Abonnement verlängern möchten, können Sie dies einfach und bequem unter %3$shttps://studypeak.ch/kaufhistorie/%4$s vornehmen. Ihre Lernfortschritte bleiben erhalten, egal ob Sie verlängern oder nicht.', 'woocommerce'),
									        '<strong>', '</strong>', '<a href="https://studypeak.ch/kaufhistorie/">', '</a>'
									    );
									   ?>
								</p>
								<a href="javascript:void();" onclick="window.print()"><img decoding="async" src="<?php echo get_stylesheet_directory_uri(). '/assets/images/print-copy.svg'?>" alt="Icon" class="w-10 h-10"> <?php esc_html_e('Print copy','woocommerce'); ?> </a>
								<style type="text/css">
									/* Print-specific styles */
							        @media print {
							        	body {-webkit-print-color-adjust: exact;}
							            header, footer, .pdf_section.text-center a, .cta_section, .order_confirmation_dv, .order-header, .other_content, #order-details, .thank-you-blog, .main_page_title, .close-popup, article > h1 {
							                display: none !important;
							            }
							            .card_icon .icon_circle {
							                background-color: #FFF !important;
							                print-color-adjust: exact; 
							            }
							            
							            #print-content {
							                margin: 0;
							                padding: 0;
							                border: none;
							            }

							            @page { margin: 0; }
							        }
								</style>

							</div>
						</div>

					</div>

						

			</div>

		<?php endif; ?>

		<?php //do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php //do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>
	<style type="text/css">article > h1 { display: none !important; }</style>
</div>
