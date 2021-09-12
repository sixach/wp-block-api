<?php
/**
 * WooCommerce Block class file.
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
 * @subpackage    Sixa_Blocks\WooCommerce_Block
 */

namespace Sixa_Blocks;

use Sixa_Snippets\Includes\Utils;

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
			if ( ! Utils::is_woocommerce_activated() ) {
				return;
			}

			// Initialize the block from the parent function.
			parent::init();
		}

	}

endif;
