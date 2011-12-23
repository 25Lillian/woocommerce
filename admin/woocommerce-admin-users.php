<?php
/**
 * Functions used for modifying the users panel
 *
 * @author 		WooThemes
 * @category 	Admin
 * @package 	WooCommerce
 */

/**
 * Define columns to show on the users page
 */
add_filter('manage_users_columns', 'woocommerce_user_columns', 10, 1);

function woocommerce_user_columns( $columns ) {
	if (!current_user_can('manage_woocommerce')) return $columns;

	$columns['woocommerce_billing_address'] = __('Billing Address', 'woothemes');
	$columns['woocommerce_shipping_address'] = __('Shipping Address', 'woothemes');
	$columns['woocommerce_paying_customer'] = __('Paying Customer?', 'woothemes');
	$columns['woocommerce_order_count'] = __('Orders', 'woothemes');
	return $columns;
}
 
/**
 * Define values for custom columns
 */
add_action('manage_users_custom_column', 'woocommerce_user_column_values', 10, 3);

function woocommerce_user_column_values($value, $column_name, $user_id) {
	global $woocommerce, $wpdb;
	switch ($column_name) :
		case "woocommerce_order_count" :
			
			$count = $wpdb->get_var( "SELECT COUNT(*) 
			FROM $wpdb->posts 
			LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
			WHERE meta_value = $user_id 
			AND meta_key = '_customer_user'
			AND post_type IN ('shop_order') 
			AND post_status = 'publish'" );
			
			$value = '<a href="'.admin_url('edit.php?post_status=all&post_type=shop_order&_customer_user='.$user_id.'').'">'.$count.'</a>';
			
		break;
		case "woocommerce_billing_address" :
			$address = array(
				'first_name' 	=> get_user_meta( $user_id, 'billing_first_name', true ),
				'last_name'		=> get_user_meta( $user_id, 'billing_last_name', true ),
				'company'		=> get_user_meta( $user_id, 'billing_company', true ),
				'address_1'		=> get_user_meta( $user_id, 'billing_address_1', true ),
				'address_2'		=> get_user_meta( $user_id, 'billing_address_2', true ),
				'city'			=> get_user_meta( $user_id, 'billing_city', true ),			
				'state'			=> get_user_meta( $user_id, 'billing_state', true ),
				'postcode'		=> get_user_meta( $user_id, 'billing_postcode', true ),
				'country'		=> get_user_meta( $user_id, 'billing_country', true )
			);

			$formatted_address = $woocommerce->countries->get_formatted_address( $address );
			
			if (!$formatted_address) $value = __('N/A', 'woothemes'); else $value = $formatted_address;
			
			$value = wpautop($value);
		break;
		case "woocommerce_shipping_address" :
			$address = array(
				'first_name' 	=> get_user_meta( $user_id, 'shipping_first_name', true ),
				'last_name'		=> get_user_meta( $user_id, 'shipping_last_name', true ),
				'company'		=> get_user_meta( $user_id, 'shipping_company', true ),
				'address_1'		=> get_user_meta( $user_id, 'shipping_address_1', true ),
				'address_2'		=> get_user_meta( $user_id, 'shipping_address_2', true ),
				'city'			=> get_user_meta( $user_id, 'shipping_city', true ),			
				'state'			=> get_user_meta( $user_id, 'shipping_state', true ),
				'postcode'		=> get_user_meta( $user_id, 'shipping_postcode', true ),
				'country'		=> get_user_meta( $user_id, 'shipping_country', true )
			);

			$formatted_address = $woocommerce->countries->get_formatted_address( $address );
			
			if (!$formatted_address) $value = __('N/A', 'woothemes'); else $value = $formatted_address;
			
			$value = wpautop($value);
		break;
		case "woocommerce_paying_customer" :
			
			$paying_customer = get_user_meta( $user_id, 'paying_customer', true );
			
			if ($paying_customer) $value = '<img src="'.$woocommerce->plugin_url().'/assets/images/success.gif" alt="yes" />';
			else $value = '<img src="'.$woocommerce->plugin_url().'/assets/images/success-off.gif" alt="no" />';
			
		break;
	endswitch;
	return $value;
}
 
/**
 * Show Address Fields on edit user pages
 */
add_action( 'show_user_profile', 'woocommerce_customer_meta_fields' );
add_action( 'edit_user_profile', 'woocommerce_customer_meta_fields' );

function woocommerce_customer_meta_fields( $user ) { 
	if (!current_user_can('manage_woocommerce')) return $columns;

	$show_fields = apply_filters('woocommerce_customer_meta_fields', array(
		'billing' => array(
			'title' => __('Customer Billing Address', 'woothemes'),
			'fields' => array(
				'billing_first_name' => array(
						'label' => __('First name', 'woothemes'),
						'description' => ''
					),
				'billing_last_name' => array(
						'label' => __('Last name', 'woothemes'),
						'description' => ''
					),
				'billing_address_1' => array(
						'label' => __('Address 1', 'woothemes'),
						'description' => ''
					),
				'billing_address_2' => array(
						'label' => __('Address 2', 'woothemes'),
						'description' => ''
					),
				'billing_city' => array(
						'label' => __('City', 'woothemes'),
						'description' => ''
					),
				'billing_postcode' => array(
						'label' => __('Postcode', 'woothemes'),
						'description' => ''
					),
				'billing_state' => array(
						'label' => __('State/County', 'woothemes'),
						'description' => 'Country or state code'
					),
				'billing_country' => array(
						'label' => __('Country', 'woothemes'),
						'description' => '2 letter Country code'
					),
				'billing_phone' => array(
						'label' => __('Telephone', 'woothemes'),
						'description' => ''
					),
				'billing_email' => array(
						'label' => __('Email', 'woothemes'),
						'description' => ''
					)
			)
		),
		'shipping' => array(
			'title' => __('Customer Shipping Address', 'woothemes'),
			'fields' => array(
				'shipping_first_name' => array(
						'label' => __('First name', 'woothemes'),
						'description' => ''
					),
				'shipping_last_name' => array(
						'label' => __('Last name', 'woothemes'),
						'description' => ''
					),
				'shipping_address_1' => array(
						'label' => __('Address 1', 'woothemes'),
						'description' => ''
					),
				'shipping_address_2' => array(
						'label' => __('Address 2', 'woothemes'),
						'description' => ''
					),
				'shipping_city' => array(
						'label' => __('City', 'woothemes'),
						'description' => ''
					),
				'shipping_postcode' => array(
						'label' => __('Postcode', 'woothemes'),
						'description' => ''
					),
				'shipping_state' => array(
						'label' => __('State/County', 'woothemes'),
						'description' => __('State/County or state code', 'woothemes')
					),
				'shipping_country' => array(
						'label' => __('Country', 'woothemes'),
						'description' => __('2 letter Country code', 'woothemes')
					)
			)
		)
	));
	
	foreach( $show_fields as $fieldset ) :
		?>
		<h3><?php echo $fieldset['title']; ?></h3>
		<table class="form-table">
			<?php
			foreach( $fieldset['fields'] as $key => $field ) :
				?>
				<tr>
					<th><label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label></th>
					<td>
						<input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text" /><br/>
						<span class="description"><?php echo $field['description']; ?></span>
					</td>
				</tr>
				<?php
			endforeach;
			?>
		</table>
		<?php
	endforeach;
}