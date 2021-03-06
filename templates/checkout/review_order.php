<?php global $woocommerce; ?>
<div id="order_review">
	
	<table class="shop_table">
		<thead>
			<tr>
				<th><?php _e('Product', 'woocommerce'); ?></th>
				<th><?php _e('Qty', 'woocommerce'); ?></th>
				<th><?php _e('Totals', 'woocommerce'); ?></th>
			</tr>
		</thead>
		<tfoot>
		
			<tr class="cart-subtotal">
				<th colspan="2"><strong><?php _e('Cart Subtotal', 'woocommerce'); ?></strong></th>
				<td><?php echo $woocommerce->cart->get_cart_subtotal(); ?></td>
			</tr>
			
			<?php if ($woocommerce->cart->get_discounts_before_tax()) : ?>
			
			<tr class="discount">
				<th colspan="2"><?php _e('Cart Discount', 'woocommerce'); ?></th>
				<td>-<?php echo $woocommerce->cart->get_discounts_before_tax(); ?></td>
			</tr>
			
			<?php endif; ?>
			
			<?php if ($woocommerce->cart->needs_shipping()) : ?>
			
			<tr class="shipping">
				<th colspan="2"><?php _e('Shipping', 'woocommerce'); ?></th>
				<td>
				<?php
					
					$available_methods = $woocommerce->shipping->get_available_shipping_methods();
					
					if (sizeof($available_methods)>0) :
						
						echo '<select name="shipping_method" id="shipping_method">';
						
						foreach ($available_methods as $method ) :
							
							echo '<option value="'.esc_attr($method->id).'" ';
							
							if ($method->id==$_SESSION['_chosen_shipping_method']) echo 'selected="selected"';
							
							echo '>'.esc_html($method->label).' &mdash; ';
							
							if ($method->cost>0) :
							
								if ($woocommerce->cart->display_totals_ex_tax || !$woocommerce->cart->prices_include_tax) :

									echo woocommerce_price($method->cost);
									if ( $method->get_shipping_tax()>0 && $woocommerce->cart->prices_include_tax ) :
										echo ' ' . $woocommerce->countries->ex_tax_or_vat();
									endif;

								else :

									echo woocommerce_price($method->cost + $method->get_shipping_tax());
									if ( $method->get_shipping_tax()>0 && !$woocommerce->cart->prices_include_tax ) :
										echo ' ' . $woocommerce->countries->inc_tax_or_vat();
									endif;

								endif;
								
							else :
								echo __('Free', 'woocommerce');
							endif;
							
							echo '</option>';

						endforeach;
						
						echo '</select>';
						
					else :
						
						if ( !$woocommerce->customer->get_shipping_country() || !$woocommerce->customer->get_shipping_state() || !$woocommerce->customer->get_shipping_postcode() ) : 
							echo '<p>'.__('Please fill in your details above to see available shipping methods.', 'woocommerce').'</p>';
						else :
							echo '<p>'.__('Sorry, it seems that there are no available shipping methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce').'</p>';
						endif;
						
					endif;
			
				?></td>
				
			</tr>

			<?php endif; ?>
			
			<?php 
				if ($woocommerce->cart->get_cart_tax()) :

					if (isset($woocommerce->cart->taxes) && sizeof($woocommerce->cart->taxes)>0) :
					
						$has_compound_tax = false;
						
						foreach ($woocommerce->cart->taxes as $key => $tax) : if ($woocommerce->cart->tax->is_compound( $key )) : $has_compound_tax = true; continue; endif;

							?>
							<tr class="tax-rate tax-rate-<?php echo $key; ?>">
								<th colspan="2"><?php echo $woocommerce->cart->tax->get_rate_label( $key ); ?></th>
								<td><?php echo woocommerce_price($tax); ?></td>
							</tr>
							<?php
							
						endforeach;
						
						if ($has_compound_tax && !$woocommerce->cart->prices_include_tax) :
							?>
							<tr class="order-subtotal">
								<th colspan="2"><strong><?php _e('Order Subtotal', 'woocommerce'); ?></strong></th>
								<td><?php echo $woocommerce->cart->get_cart_subtotal( true ); ?></td>
							</tr>
							<?php
						endif;
						
						foreach ($woocommerce->cart->taxes as $key => $tax) : if (!$woocommerce->cart->tax->is_compound( $key )) continue;

							?>
							<tr class="tax-rate tax-rate-<?php echo $key; ?>">
								<th colspan="2"><?php echo $woocommerce->cart->tax->get_rate_label( $key ); ?></th>
								<td><?php echo woocommerce_price($tax); ?></td>
							</tr>
							<?php
							
						endforeach;
					
					else :
					
						?>
						<tr class="tax">
							<th colspan="2"><?php _e('Tax', 'woocommerce'); ?></th>
							<td><?php echo $woocommerce->cart->get_cart_tax(); ?></td>
						</tr>
						<?php
					
					endif;	
				endif;
			?>

			<?php if ($woocommerce->cart->get_discounts_after_tax()) : ?>
			
			<tr class="discount">
				<th colspan="2"><?php _e('Order Discount', 'woocommerce'); ?></th>
				<td>-<?php echo $woocommerce->cart->get_discounts_after_tax(); ?></td>
			</tr>
			
			<?php endif; ?>
			
			<?php do_action('woocommerce_before_order_total'); ?>
			
			<tr class="total">
				<th colspan="2"><strong><?php _e('Order Total', 'woocommerce'); ?></strong></th>
				<td><strong><?php echo $woocommerce->cart->get_total(); ?></strong></td>
			</tr>
			
			<?php do_action('woocommerce_after_order_total'); ?>
			
		</tfoot>
		<tbody>
			<?php
				if (sizeof($woocommerce->cart->get_cart())>0) : 
					foreach ($woocommerce->cart->get_cart() as $item_id => $values) :
						$_product = $values['data'];
						if ($_product->exists() && $values['quantity']>0) :
							echo '
								<tr>
									<td class="product-name">'.$_product->get_title().$woocommerce->cart->get_item_data( $values ).'</td>
									<td>'.$values['quantity'].'</td>
									<td>' . $woocommerce->cart->get_product_subtotal( $_product, $values['quantity'] ) . '</td>
								</tr>';
						endif;
					endforeach; 
				endif;
			?>
		</tbody>
	</table>
	
	<div id="payment">
		<?php if ($woocommerce->cart->needs_payment()) : ?>
		<ul class="payment_methods methods">
			<?php 
				$available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
				if ($available_gateways) : 
					// Chosen Method
					if (sizeof($available_gateways)) :
						$default_gateway = get_option('woocommerce_default_gateway');
						if (isset($_SESSION['_chosen_payment_method']) && isset($available_gateways[$_SESSION['_chosen_payment_method']])) :
							$available_gateways[$_SESSION['_chosen_payment_method']]->set_current();
						elseif (isset($available_gateways[$default_gateway])) :
							$available_gateways[$default_gateway]->set_current();
						else :
							current($available_gateways)->set_current();
						endif;
					endif;
					foreach ($available_gateways as $gateway ) :
						?>
						<li>
						<input type="radio" id="payment_method_<?php echo $gateway->id; ?>" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php if ($gateway->chosen) echo 'checked="checked"'; ?> />
						<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->title; ?> <?php echo apply_filters('woocommerce_gateway_icon', $gateway->icon(), $gateway->id); ?></label> 
							<?php
								if ($gateway->has_fields || $gateway->description) : 
									echo '<div class="payment_box payment_method_'.$gateway->id.'" style="display:none;">';
									$gateway->payment_fields();
									echo '</div>';
								endif;
							?>
						</li>
						<?php
					endforeach;
				else :
				
					if ( !$woocommerce->customer->get_country() ) :
						echo '<p>'.__('Please fill in your details above to see available payment methods.', 'woocommerce').'</p>';
					else :
						echo '<p>'.__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce').'</p>';
					endif;
					
				endif;
			?>
		</ul>
		<?php endif; ?>

		<div class="form-row">
		
			<noscript><?php _e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce'); ?><br/><input type="submit" class="button-alt" name="update_totals" value="<?php _e('Update totals', 'woocommerce'); ?>" /></noscript>
		
			<?php $woocommerce->nonce_field('process_checkout')?>
			
			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
			
			<input type="submit" class="button alt" name="place_order" id="place_order" value="<?php echo apply_filters('woocommerce_order_button_text', __('Place order', 'woocommerce')); ?>" />
			
			<?php if (woocommerce_get_page_id('terms')>0) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php _e('I accept the', 'woocommerce'); ?> <a href="<?php echo esc_url( get_permalink(woocommerce_get_page_id('terms')) ); ?>" target="_blank"><?php _e('terms &amp; conditions', 'woocommerce'); ?></a></label>
				<input type="checkbox" class="input-checkbox" name="terms" <?php if (isset($_POST['terms'])) echo 'checked="checked"'; ?> id="terms" />
			</p>
			<?php endif; ?>
			
			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
			
		</div>
		
		<div class="clear"></div>

	</div>
	
</div>