<?php

namespace Sixa_Blocks;


final class Functions {

	private const METADATA_FILE_NAME = 'extension.json';

	public static function register_extension_from_metadata( string $file_or_folder, array $args = array() ): void {
		$metadata_file = Functions::get_metadata_file_path_from_file_or_folder( $file_or_folder );

		if ( ! file_exists( $metadata_file ) ) {
			// Bail early if the given file does not exist.
			return;
		}

		$metadata = json_decode( file_get_contents( $metadata_file ), true );
		if ( ! is_array( $metadata ) || empty( $metadata ) ) {
			// Bail early if the extracted object is invalid or empty.
			return;
		}
	}

	private static function get_metadata_file_path_from_file_or_folder( string $file_or_folder, string $metadata_filename = Functions::METADATA_FILE_NAME ): string {
		if ( $metadata_filename !== substr( $file_or_folder, strlen( $metadata_filename ) * -1 ) ) {
			return sprintf( '%s%s', trailingslashit( $file_or_folder ), $metadata_filename );
		}
		return $file_or_folder;
	}

}
