<?php

namespace Sixa_Blocks;

final class Extension_Registry {

	private $registered_extensions = array();

	private static $instance = null;

	public function register( $extension ) {
		// TODO: add _doing_it_wrong or WP_Error here
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
