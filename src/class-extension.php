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

		public static function init(): void {
			if ( ! isset( static::$name ) ) {
				throw new \LogicException( sprintf( '%s must have a $name', static::class ) );
			}

			if ( ! isset( static::$path ) ) {
				throw new \LogicException( sprintf( '%s must have a $path', static::class ) );
			}
		}

	}

endif;
