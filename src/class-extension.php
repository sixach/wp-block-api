<?php
/**
 * Extension class file.
 *
 * The class in this file represents the default implementation
 * of a simple extension class. Particularly, the class implements a
 * simple initialization function that adds the registration
 * function to the `init` WordPress hook. The registration function
 * registers the block following the Gutenberg block registration API.
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks\Extension
 */

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Extension::class ) ) :

	/**
	 * Extension Class containing default implementation.
	 */
	abstract class Extension {

		/**
		 * Initialize the Extension.
		 * Set up the WordPress hook to register the block.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public static function init(): void {
			add_action( 'init', array( static::class, 'register' ) );
		}

		/**
		 * Register the extension using the metadata loaded from the `extension.json` file.
		 * Behind the scenes, it also registers all assets so they can be enqueued
		 * through the block editor in the corresponding context.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		abstract public static function register(): void;

	}

endif;
