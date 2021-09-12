<?php
/**
 * Extension Registry.
 *
 * This class is used to register extensions and retrieve all registered extensions
 * subsequently to enqueue extension assets.
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks/Extension_Registry
 */

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Extension_Registry::class ) ) :

	/**
	 * Extension_Registry Class.
	 *
	 * Used to register extensions and retrieve registered extensions subsequently
	 * for further processing (e.g. asset enqueueing).
	 */
	final class Extension_Registry {

		/**
		 * Map of all registered extensions.
		 * Holds the data of an extension with its name as the key.
		 *
		 * @since    1.0.0
		 * @var      array
		 */
		private array $registered_extensions = array();

		/**
		 * An instance of this class.
		 *
		 * @since    1.0.0
		 * @var      Extension_Registry|null
		 */
		private static ?Extension_Registry $instance = null;

		/**
		 * Register an extension.
		 *
		 * @since     1.0.0
		 * @param     array $extension
		 * @return    void
		 */
		public function register( array $extension ): void {
			// TODO: add _doing_it_wrong or WP_Error here if $extension is ill-defined?
			$this->registered_extensions[ $extension['name'] ] = $extension;
		}

		/**
		 * Return all registered extensions.
		 *
		 * @since     1.0.0
		 * @return    array
		 */
		public function get_registered_extensions(): array {
			return $this->registered_extensions;
		}

		/**
		 * Return an instance of this class.
		 * Either returns the existing instance or creates a new instance.
		 *
		 * @since     1.0.0
		 * @return    Extension_Registry
		 */
		public static function get_instance(): Extension_Registry {
			if ( null === self::$instance ) {
				// Add actions that perform asset enqueueing.
				// Inside `Extension_Registry::get_instance()` seems to be a convenient location
				// for this as i) it is guaranteed to be called if at least one extension is registered
				// and ii) the condition `null === self::$instance` is guaranteed to be true at most once.
				// Thus, we can guarantee that we are only adding the actions once (and would in fact be able
				// to skip the checks that we include).
				Functions::add_enqueueing_actions();
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

endif;
