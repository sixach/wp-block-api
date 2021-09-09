<?php

namespace Sixa_Blocks;

final class Extension_Registry {

	private $registered_extensions = array();

	private static $instance = null;

	public function register( $extension ) {
		// TODO: add _doing_it_wrong or WP_Error here
		$this->registered_extensions[ $extension['name'] ] = $extension;
	}

	public function get_all_registered() {
		return $this->registered_extensions;
	}

	/**
	 * Return an instance of this class.
	 * Either returns the existing instance or creates a new instance.
	 *
	 * @since     1.0.0
	 * @return    Extension_Registry
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
