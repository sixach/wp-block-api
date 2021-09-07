<?php
/**
 * Constants for block and extension class library.
 *
 * @link      https://sixa.ch
 * @author    sixa AG
 * @since     1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks/API_Constants
 */

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( API_Constants::class ) ) :

	/**
	 * Constants used throughout this library.
	 */
	class API_Constants {

		/**
		 * Name of the directory inside an extension that contains the build assets.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const BUILD_DIR = 'build';

	}

endif;
