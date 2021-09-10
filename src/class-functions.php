<?php
/**
 * Functions Class.
 *
 * This class contains a set of functions to enable extension registration with an
 * `extension.json` configuration file. The structure and functionality of these
 * functions mimics the approach that WordPress uses to register Blocks and Block Types
 * very closely and, essentially, applies the same logic but for extensions.
 *
 * @see           https://github.com/WordPress/WordPress/blob/5.8-branch/wp-includes/blocks.php#L193
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks/Extension_Registry
 */

namespace Sixa_Blocks;


final class Functions {

	private const METADATA_FILE_NAME = 'extension.json';
	private const PATH_PREFIX = 'file:';

	public static function register_extension_from_metadata( string $file_or_folder ): void {
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
		$metadata['file'] = $metadata_file;

		$extension = array();
		$extension['name'] = $metadata['name'];
		if ( ! empty( $metadata[ Asset_Type::SCRIPT ] ) ) {
			$extension[Asset_Type::SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::SCRIPT );
		}

		if ( ! empty( $metadata[ Asset_Type::EDITOR_SCRIPT ] ) ) {
			$extension[Asset_Type::EDITOR_SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::EDITOR_SCRIPT );
		}

		if ( ! empty( $metadata[ Asset_Type::FRONTEND_SCRIPT ] ) ) {
			$extension[Asset_Type::FRONTEND_SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::FRONTEND_SCRIPT );
		}

		Extension_Registry::get_instance()->register( $extension );
	}

	public static function add_enqueueing_actions() {
		if ( ! has_action( 'enqueue_block_editor_assets', array( self::class, 'enqueue_editor_assets' ) ) ) {
			add_action( 'enqueue_block_editor_assets', array( self::class, 'enqueue_editor_assets' ), 0 );
		}

		if ( ! has_action( 'enqueue_block_assets', array( self::class, 'enqueue_block_assets' ) ) ) {
			add_action( 'enqueue_block_assets', array( self::class, 'enqueue_block_assets' ), 0 );
		}

		if ( ! has_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_frontend_assets' ) ) ) {
			add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue_frontend_assets' ), 0 );
		}
	}

	public static function enqueue_block_assets() {
		self::enqueue_assets_by_type( Asset_Type::SCRIPT );
	}

	public static function enqueue_editor_assets() {
		self::enqueue_assets_by_type( Asset_Type::EDITOR_SCRIPT );
	}

	public static function enqueue_frontend_assets() {
		self::enqueue_assets_by_type( Asset_Type::FRONTEND_SCRIPT );
	}

	private static function enqueue_assets_by_type( $type ) {
		$extension_registry = Extension_Registry::get_instance();
		foreach( $extension_registry->get_registered_extensions() as $extension ) {
			if ( ! empty( $extension[ $type ] ) ) {
				wp_enqueue_script( $extension[ $type ] );
			}
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

	/**
	 * @param array $metadata
	 * @param string $type
	 * @return string
	 */
	private static function register_extension_script_handle( array $metadata, string $type ): string {
		$handle = $metadata[$type];
		$path = self::remove_path_prefix( $handle );

		// Bail early if the passed path already is a handle (i.e. if it doesn't contain a 'file:' prefix)
		if ( $handle === $path ) {
			return $handle;
		}

		$handle = self::build_asset_handle( $metadata['name'], $type );
		$asset_meta = self::get_asset_meta( $metadata, $type );

		$result = wp_register_script(
			$handle,
			plugins_url( $path, $metadata['file'] ),
			$asset_meta['dependencies'],
			$asset_meta['version']
		);

		if ( ! $result ) {
			// Bail early if the script could not be registered.
			return '';
		}

		// TODO: do we need to add `wp_set_script_translations`?

		return $handle;
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

	/**
	 * @param $name
	 * @param $type
	 * @return string
	 */
	private static function build_asset_handle( $name, $type ) {
		return sprintf( '%s-%s', $name, Asset_Type::SLUGS[ $type ] );
	}

	/**
	 * @param $metadata
	 * @param $type
	 * @return array|mixed
	 */
	private static function get_asset_meta( $metadata, $type ) {
		$asset_full_path = self::get_full_asset_path( $metadata, $type );
		$asset_meta_path = self::get_asset_meta_path( $metadata, $type );
		return file_exists( $asset_meta_path )
			? require $asset_meta_path
			: array(
				'dependencies' => array(),
				'version'      => filemtime( $asset_full_path ),
			);
	}

	/**
	 * @param $metadata
	 * @param $type
	 * @return false|string
	 */
	private static function get_asset_meta_path( $metadata, $type ) {
		$asset_path = self::remove_path_prefix( $metadata[$type ]);
		$asset_meta_path = substr_replace( $asset_path, '.asset.php', strlen( '.js' ) * -1 );
		return self::get_full_path_for_file( $metadata, $asset_meta_path );
	}

	/**
	 * @param $metadata
	 * @return false|string
	 */
	private static function get_full_asset_path( $metadata, $type ) {
		$asset_path = self::remove_path_prefix( $metadata[$type] );
		return self::get_full_path_for_file( $metadata, $asset_path );
	}

	/**
	 * @param $metadata
	 * @param $file
	 * @return false|string
	 */
	private static function get_full_path_for_file( $metadata, $file ) {
		return realpath( sprintf( '%s%s', trailingslashit( dirname( $metadata['file'] ) ), $file ) );
	}

}
