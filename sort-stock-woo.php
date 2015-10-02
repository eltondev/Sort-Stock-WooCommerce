<?php
/**
 * Plugin Name:       Sort Stock WooCommerce
 * Plugin URI:        https://github.com/byanofsky/wc-sort-by-stock
 * Description:       Sort your products according to the amount you have in your stock.
 * Version:           1.0.1
 * Author:            EltonDEV
 * Author URI:        http://eltondev.com.br
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sort-stock-woo
 */

/** Die
*/
defined( 'ABSPATH' ) or die( 'Do not cheat!' );
/**
* Make stock column sortable
*/
add_filter( 'manage_edit-product_columns', 'sort_stock_woo' );
function sort_stock_woo( $sortable_columns ) {
   $sortable_columns[ 'is_in_stock' ] = 'Estoque Status';
   return $sortable_columns;
}
/**
* Adjust the order of the posts as they are output on the backend
*/
add_filter( 'posts_clauses', 'manage_wp_posts_clauses', 1, 2 );
function manage_wp_posts_clauses( $stock_order, $query ) {
  global $wpdb;
  /** 
  * Set variable for what is specified to orderby
  */ 
  $orderby = $query->get( 'orderby' );
  /**
  * Check for main query and if orderby is specified
  */
  if ( $query->is_main_query() && ( $query->get( 'orderby' ) == 'Estoque Status' ) ) {
    // Get the order query variable - ASC or DESC
    $order = strtoupper( $query->get( 'order' ) );
    // Make sure the order setting qualifies. If not, set default as ASC
    if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) )
      $order = 'ASC';
  		
    /**
    * Join postmeta to include stock_status and stock info
    */
    $stock_order[ 'join' ] .= " LEFT JOIN $wpdb->postmeta {$wpdb->prefix}stock_status ON {$wpdb->prefix}stock_status.post_id = {$wpdb->posts}.ID AND {$wpdb->prefix}stock_status.meta_key = '_stock_status' LEFT JOIN $wpdb->postmeta {$wpdb->prefix}stock ON {$wpdb->prefix}stock.post_id = {$wpdb->posts}.ID AND {$wpdb->prefix}stock.meta_key = '_stock'";
    //Set reverse order in a variable
    if($order == 'ASC') {
      $in_stock_order = 'DESC';
    } else {
      $in_stock_order = 'ASC';
    }
    
    //Specify orderby. Orderby stock status first in reverse order, then stock amount.
    $stock_order[ 'orderby' ] = "wp_stock_status.meta_value $in_stock_order, wp_stock.meta_value $order, " . $stock_order[ 'orderby' ];
	
    }
    return $stock_order;
}
//Starts ordination in WooCommerce catalog
function custom_woocommerce_get_catalog_ordering_args( $args ) {
  $orderby_value = isset( $_GET['orderby'] ) ? woocommerce_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
	if ( 'stock' == $orderby_value ) {
		$args['orderby'] = 'date';
		$args['order'] = 'ASC';
		$args['meta_key'] = '';
	}
	return $args;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby' );
function custom_woocommerce_catalog_orderby( $sortby ) {
	$sortby['stock'] = 'Estoque Status';
	return $sortby;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args' );
