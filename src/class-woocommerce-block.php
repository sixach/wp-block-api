<?php
/**
 * Block class file.
 *
 * The class in this file represents the default implementation
 * of a simple WooCommerce block class. In addition to the default
 * implementation from `Block`, this class implements a check to
 * conditionally register blocks only if WooCommerce is installed.
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks\Block
 */

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WooCommerce_Block::class ) ) :

	/**
	 * WooCommerce Block Class containing the default implementation.
	 */
	abstract class WooCommerce_Block extends Block {

		/**
		 * Initialize the block.
		 * Check if WooCommerce is installed and skip registration
		 * if it is not.
		 *
		 * This `init` function is an extension of `init` in `Block`.
		 * Call the parent `init` function after performing the WooCommerce
		 * installation check.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public static function init(): void {
			// Bail early if WooCommerce is not activated.
			if ( ! static::is_woocommerce() ) {
				return;
			}

			// Initialize the block from the parent function.
			parent::init();
		}

		/**
		 * Return `true` if WooCommerce is installed and `false` otherwise.
		 *
		 * @since   1.0.0
		 * @return  bool
		 */
		private static function is_woocommerce(): bool {
			// This statement prevents from producing fatal errors,
			// in case the WooCommerce plugin is not activated on the site.
			$woocommerce_plugin     = apply_filters( 'sixa_blocks_woocommerce_path', 'woocommerce/woocommerce.php' );
			$subsite_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			$network_active_plugins = apply_filters( 'active_plugins', get_site_option( 'active_sitewide_plugins' ) );

			// Bail early in case the plugin is not activated on the website.
			if ( ( empty( $subsite_active_plugins ) || ! in_array( $woocommerce_plugin, $subsite_active_plugins ) ) && ( empty( $network_active_plugins ) || ! array_key_exists( $woocommerce_plugin, $network_active_plugins ) ) ) {
				return false;
			}

			return true;
		}

	}

endif;
