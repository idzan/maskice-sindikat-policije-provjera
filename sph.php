<?php
/*
Plugin Name: WooCommerce SPH Broj članske iskaznice
Plugin URI: https://idzan.eu
Description: Plugin za dodavanje polja za pravne osobe (R1 račun) u WooCommerceu & provjeru podataka s CSV
Version: 1.0
Author: Media X
Author URI: https://idzan.eu
License: GPLv3
*/

/**
 *  Let's enqueue our tiny script (toggle)
 */
add_action( 'wp_enqueue_scripts', 'enqueue_and_register_sph_script' );

function enqueue_and_register_sph_script(){

    wp_register_script( 'sph_clanska_checkout_script', plugins_url('/includes/js/sph-form.js', __FILE__), array('jquery'));
    wp_enqueue_script( 'sph_clanska_checkout_script' );
}

/**
 *  Remove "Company name" field from Billing section
 */
add_filter( 'woocommerce_checkout_fields' , 'sph_company_override_checkout_fields' );

function sph_company_override_checkout_fields( $fields ) {
     unset($fields['billing']['billing_company']);

     return $fields;
}


/**
 * Add our new fields to the checkout
 */
add_action( 'woocommerce_after_checkout_billing_form', 'sph_custom_checkout_field' );

function sph_custom_checkout_field( $checkout ) {
	
	echo '<div id="sph_custom_checkout_field"><h3>' . __( 'Član Sindikata Policije Hrvatske' ) . '</h3><p style="margin: 0 0 5px;">Član Sindikata Policije Hrvatske?</p>';
 
    woocommerce_form_field( 'sph_checkbox', array(
    'type'  => 'checkbox',
    'class' => array( 'sph-checkbox form-row-wide' ),
    'label' => __( 'Da' ),
    ), $checkout->get_value( 'sph_checkbox' ) );
 
    woocommerce_form_field( 'sph-clanske-iskaznice', array(
  'type'  => 'text',
  'class' => array( 'sph-text form-row-wide' ),
  'label' => __( 'Broj članske iskaznice' ),
  'placeholder'   => _x('Upišite broj članske iskaznice', 'placeholder', 'woocommerce'),
   ), $checkout->get_value( 'sph-clanske-iskaznice' ) );
   
   echo '</div>';

}


/**
 * Update the order meta with field value
 **/
 
add_action( 'woocommerce_checkout_update_order_meta', 'sph_clanska_iskaznica_checkout_field_update_order_meta' );
 
function soh_clanska_iskaznica_checkout_field_update_order_meta( $order_id ) {
 
   //check if $_POST has our custom fields
   if ( $_POST['sph_checkbox'] ) {
   //If it does: update post meta for this order
   update_post_meta( $order_id, 'Član Sindikata Policije Hrvatske', esc_attr( $_POST['sph_checkbox'] ) );
   }
   if ( $_POST['sph_clanske_iskaznice'] ) {
   update_post_meta( $order_id, 'Broj članske iskaznice', esc_attr( $_POST['sph_clanske_iskaznice'] ) );
   }
}

/**
 * Display fields values on the Order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'sph_broj_checkout_field_display_admin_order_meta', 10, 1 );

function sph_broj_checkout_field_display_admin_order_meta($order){
	
	$r1_checkbox = get_post_meta( $order->id, 'Član Sindikata Policije Hrvatske', true );
    if( !empty( $r1_checkbox ) ) {
	
    echo '<h4>'.__('Član Sindikata Policije Hrvatske').'</h4>';
    echo '<p><strong>'.__('Broj članske iskaznice').':</strong> ' . get_post_meta( $order->id, 'Broj članske iskaznice', true ) . '</p>';
	}
}

/**
 * Display fields values on the Order details page
 */
add_action( 'woocommerce_order_details_after_order_table', 'sph_broj_field_display_cust_order_meta', 10, 1 );

function sph_broj_field_display_cust_order_meta($order){
	
    $r1_checkbox = get_post_meta( $order->id, 'R1 račun', true );
    if( !empty( $r1_checkbox ) ) {
	
    echo '<h2>'.__('Član Sindikata Policije Hrvatske').'</h2>';
    echo '<p><strong>'.__('Broj članske iskaznice').':</strong> ' . get_post_meta( $order->id, 'Broj članske iskaznice', true ) . '</p>';
	}
}

/**
 * Let's add the fields to order emails
 **/
add_filter('woocommerce_email_order_meta_keys', 'sph_woocommerce_email_order_meta_keys');

function sph_woocommerce_email_order_meta_keys( $keys ) {
		
    if ( $_POST['sph_checkbox'] ) {
	echo '<h2>Član Sindikata Policije Hrvatske</h2>';

    $keys[] = 'Broj članske iskaznice';
	
	return $keys;
	}
}

/**
 * Add the R1 column in order administration
 **/

add_filter( 'manage_edit-shop_order_columns', 'sph_broj_order_column', 20 );
function sph_broj_order_column( $columns ) {
	$offset = 9;
	$updated_columns = array_slice( $columns, 0, $offset, true) +
	array( 'sph_checkbox' => esc_html__( 'Član Sindikata Policije Hrvatske', 'woocommerce' ) ) +
	array_slice($columns, $offset, NULL, true);
	return $updated_columns;
}

// Populate R1 column
add_action( 'manage_shop_order_posts_custom_column', 'sph_broj_order_column_values', 2 );
function sph_broj_order_column_values( $column ) {
	global $post;
 
	if ( $column == 'sph_checkbox' ) {
		$r1_checkbox = get_post_meta( $post->ID, 'Član Sindikata Policije Hrvatske', true );
		if ( $r1_checkbox > 0 )
			print("Da");
		else print '-';
	}
}
