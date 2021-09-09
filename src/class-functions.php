<?php

namespace Sixa_Blocks;


final class Functions {

	private const METADATA_FILE_NAME = 'extension.json';
	private const PATH_PREFIX = 'file:';

	public static function register_extension_from_metadata( string $file_or_folder, array $args = array() ): void {
		$metadata_file = self::get_metadata_file_path_from_file_or_folder( $file_or_folder );


		if ( ! file_exists( $metadata_file ) ) {
			// Bail early if the given file does not exist.
			return;
		}

		$metadata = json_decode( file_get_contents( $metadata_file ), true );
		if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
			// Bail early if the extracted object is invalid or if `name` is missing.
			return;
		}

		if ( ! empty( $metadata[ Asset_Type::STYLE ] ) ) {
			self::register_extension_script_handle( $metadata['name'], $metadata['script'], Asset_Type::STYLE );
		}

	}

	/**
	 * @param string $file_or_folder
	 * @param string $metadata_filename
	 * @return string
	 */
	private static function get_metadata_file_path_from_file_or_folder( string $file_or_folder, string $metadata_filename = self::METADATA_FILE_NAME ): string {
		if ( $metadata_filename !== substr( $file_or_folder, strlen( $metadata_filename ) * -1 ) ) {
			return sprintf( '%s%s', trailingslashit( $file_or_folder ), $metadata_filename );
		}
		return $file_or_folder;
	}

	private static function register_extension_script_handle( string $name, string $path, string $type ): string {
		$handle = self::remove_path_prefix( $path );
		// Bail early if the passed path already is a handle (i.e. if it doesn't contain a 'file:' prefix)
		if ( $handle === $path ) {
			return $handle;
		}

		$handle = self::get_asset_handle( $name, $type );
		print( $handle );
		exit();
	}

	/**
	 * @param string $path
	 * @return false|string
	 */
	private static function remove_path_prefix( string $path ) {
		if ( 0 !== strpos( $path, self::PATH_PREFIX ) ) {
			return $path;
		}
		return substr( $path, strlen( self::PATH_PREFIX ) );
	}

	private static function get_asset_handle( $name, $type ) {
		return sprintf( '%s-%s', $name, Asset_Type::SLUGS[ $type ] );
	}

}
