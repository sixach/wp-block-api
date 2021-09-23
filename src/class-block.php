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

if ( ! class_exists( Block::class ) ) :

	/**
	 * Block Class containing default implementation.
	 */
	abstract class Block {

		/**
		 * Record if the block was initialized to make sure it is
		 * initialized at most once.
		 *
		 * @since    1.0.0
		 * @var      bool
		 */
		protected static $was_initialized = false;

		/**
		 * Initialize the block.
		 * Set up the WordPress hook to register the block.
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public static function init(): void {
			// Bail early if the block was already initialized.
			if ( static::$was_initialized ) {
				return;
			}

			add_action( 'init', array( static::class, 'register' ) );
		}

		/**
		 * Register the block using the metadata loaded from the `block.json` file.
		 * Behind the scenes, it also registers all assets so they can be enqueued
		 * through the block editor in the corresponding context.
		 *
		 * @see       https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
		 * @since     1.0.0
		 * @return    void
		 */
		abstract public static function register(): void;

	}

endif;
