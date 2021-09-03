<?php
/**
 * Block class file.
 *
 * The class in this file represents the default implementation
 * of a simple block class. Particularly, the class implements a
 * simple initialization function that adds the registration
 * function to the `init` WordPress hook. The registration function
 * registers the block following the Gutenberg block registration API.
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
	 * Block Class containing default implementation.
	 */
	abstract class WooCommerce_Block extends Block {

		/**
		 * Initialize the block.
		 * Set up the WordPress hook to register the block.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public static function init(): void {
			// Bail early if WooCommerce is not activated
			if ( ! function_exists( 'is_woocommerce' ) || ! is_woocommerce() ) {
				return;
			}

			parent::init();
		}

	}

endif;
