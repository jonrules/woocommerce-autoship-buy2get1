<?php
/*
Plugin Name: WC Autoship Buy 2 Get 1
Plugin URI: http://wooautoship.com
Description: Buy 2 get 1 free with coupon code buy2get1
Version: 1.0
Author: Patterns in the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce-autoship/woocommerce-autoship.php' ) ) {
	
	define( 'WC_AUTOSHIP_BUY2GET1_SKU', 'TRIGO-MAX-90' );
	define( 'WC_AUTOSHIP_BUY2GET1_CODE', 'buy2get1' );
	
	function wc_autoship_buy2get1_install() {

	}
	register_activation_hook( __FILE__, 'wc_autoship_buy2get1_install' );
	
	function wc_autoship_buy2get1_deactivate() {
	
	}
	register_deactivation_hook( __FILE__, 'wc_autoship_buy2get1_deactivate' );
	
	function wc_autoship_buy2get1_uninstall() {

	}
	register_uninstall_hook( __FILE__, 'wc_autoship_buy2get1_uninstall' );
	
	function wc_autoship_buy2get1_totals() {
		$woocommerce = WC();
		
		if ( ! $woocommerce->cart->has_discount( WC_AUTOSHIP_BUY2GET1_CODE ) ) {
			// Default behavior
			return;
		}
		
		$cart_items = $woocommerce->cart->get_cart();
		$item_count = 0;
		$autoship_item_count = 0;
		foreach ( $cart_items as $values ) {
			if ( $values['data'] instanceof WC_Product ) {
				$sku = $values['data']->get_sku();
				if ( $sku == WC_AUTOSHIP_BUY2GET1_SKU ) {
					$item_count += $values['quantity'];
					if ( isset( $values['wc_autoship_option_id'] ) ) {
						$autoship_item_count += $values['quantity'];
					}
				}
			}
		}
		
		if ( $autoship_item_count < 2 ) {
			$message = __( 'You must have at least 2 Auto-Ship items in your cart to use the coupon code '
				. WC_AUTOSHIP_BUY2GET1_CODE, 'wc-autoship' );
			if ( ! wc_has_notice( $message, 'error' ) ) {
				wc_add_notice( $message, 'error' );
			}
			$woocommerce->cart->remove_coupon( WC_AUTOSHIP_BUY2GET1_CODE );
		} elseif( $item_count < 3 ) {
			$message = __( 'You must have at least 3 items in your cart to use the coupon code '
				. WC_AUTOSHIP_BUY2GET1_CODE, 'wc-autoship' );
			if ( ! wc_has_notice( $message, 'error' ) ) {
				wc_add_notice( $message, 'error' );
			}
			$woocommerce->cart->remove_coupon( WC_AUTOSHIP_BUY2GET1_CODE );
		}
	}
	add_action( 'woocommerce_calculate_totals', 'wc_autoship_buy2get1_totals' );
	
	/**
	 * Check if coupon is valid
	 * @param boolean $valid
	 * @param WC_Coupon $coupon
	 * @return boolean
	 */
	function wc_autoship_buy2get1_coupon_is_valid( $valid, $coupon ) {
		if ( $coupon->code != WC_AUTOSHIP_BUY2GET1_CODE ) {
			// Return default
			return $valid;
		}
		
		$woocommerce = WC();
	
		$cart_items = $woocommerce->cart->get_cart();
		$item_count = 0;
		$autoship_item_count = 0;
		foreach ( $cart_items as $values ) {
			if ( $values['data'] instanceof WC_Product ) {
				$sku = $values['data']->get_sku();
				if ( $sku == WC_AUTOSHIP_BUY2GET1_SKU ) {
					$item_count += $values['quantity'];
					if ( isset( $values['wc_autoship_option_id'] ) ) {
						$autoship_item_count += $values['quantity'];
					}
				}
			}
		}
	
		if ( $autoship_item_count < 2 ) {
			$message = __( 'You must have at least 2 Auto-Ship items in your cart to use the coupon code '
				. WC_AUTOSHIP_BUY2GET1_CODE, 'wc-autoship' );
			if ( ! wc_has_notice( $message, 'error' ) ) {
				wc_add_notice( $message, 'error' );
			}
			return false;
		} elseif( $item_count < 3 ) {
			$message = __( 'You must have at least 3 items in your cart to use the coupon code '
				. WC_AUTOSHIP_BUY2GET1_CODE, 'wc-autoship' );
			if ( ! wc_has_notice( $message, 'error' ) ) {
				wc_add_notice( $message, 'error' );
			}
			return false;
		}
		
		// Return default
		return $valid;
	}
	add_filter( 'woocommerce_coupon_is_valid', 'wc_autoship_buy2get1_coupon_is_valid', 10, 2 );

	function wc_autoship_buy2get1_shipping_rates( $rates, $package ) {
		if ( isset( $rates['free_shipping'] ) ) {
			// Return only free shipping rate
			return array( $rates['free_shipping'] );
		}
		
		// Return default rates
		return $rates;
	}
	add_filter( 'woocommerce_package_rates', 'wc_autoship_buy2get1_shipping_rates', 10, 2 );
		
	function wc_autoship_buy2get1_get_product_id_by_sku( $sku ) {
	
		global $wpdb;
	
		$product_id = $wpdb->get_var( $wpdb->prepare( 
			"SELECT post_id 
			FROM $wpdb->postmeta WHERE meta_key='_sku' 
			AND meta_value='%s' LIMIT 1", 
			$sku 
		) );
	
		return $product_id;
	}
}
