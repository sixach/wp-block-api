<?php

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( Extension::class ) ) :

	abstract class Extension {

		private static string $name;
		private static string $path;

	}

endif;
