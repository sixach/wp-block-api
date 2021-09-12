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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Functions::class ) ) :

	/**
	 * Functions Class.
	 *
	 * Contains functions to load and extract metadata from an `extension.json`
	 * as well as registering and enqueueing script and style handles.
	 */
	final class Functions {

		/**
		 * Name of the JSON configuration file.
		 *
		 * @since    1.0.0
		 * @var      string
		 */
		private const METADATA_FILE_NAME = 'extension.json';

		/**
		 * File prefix used in asset handlers in configuration file.
		 *
		 * Asset handle pointing to a file typically has the following structure:
		 *     `file:./build/index.js`
		 *
		 * @since    1.0.0
		 * @var      string
		 */
		private const PATH_PREFIX = 'file:';

		/**
		 * Register an extension with the metadata provided in the `extension.json`.
		 *
		 * @since     1.0.0
		 * @param     string    $file_or_folder    Path to the JSON configuration file.
		 *                                         Path may or may not include `extension.json` and also works
		 *                                         if it is the path to the directory containing `extension.json`.
		 * @return    void
		 */
		public static function register_extension_from_metadata( string $file_or_folder ): void {
			// Obtain metadata file path from passed path.
			$metadata_file = self::get_metadata_file_path_from_file_or_folder( $file_or_folder );
			if ( ! file_exists( $metadata_file ) ) {
				// Bail early if the given file does not exist.
				return;
			}

			// Extract metadata from passed configuration file.
			$metadata = json_decode( file_get_contents( $metadata_file ), true );
			if ( ! is_array( $metadata ) || empty( $metadata['name'] ) ) {
				// Bail early if the extracted object is invalid or if `name` is missing.
				return;
			}

			// Store path to metadata file in metadata for further processing.
			$metadata['file'] = $metadata_file;

			// Build the extension configuration map.
			$extension = array();
			$extension['name'] = $metadata['name'];

			// Register asset handles. Note that the assets are only registered
			// but not yet enqueued in the functions below.
			if ( ! empty( $metadata[ Asset_Type::SCRIPT ] ) ) {
				$extension[Asset_Type::SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::SCRIPT );
			}

			if ( ! empty( $metadata[ Asset_Type::STYLE ] ) ) {
				$extension[Asset_Type::STYLE] = self::register_extension_style_handle( $metadata, Asset_Type::STYLE );
			}

			if ( ! empty( $metadata[ Asset_Type::EDITOR_SCRIPT ] ) ) {
				$extension[Asset_Type::EDITOR_SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::EDITOR_SCRIPT );
			}

			if ( ! empty( $metadata[ Asset_Type::EDITOR_STYLE ] ) ) {
				$extension[Asset_Type::EDITOR_STYLE] = self::register_extension_style_handle( $metadata, Asset_Type::EDITOR_STYLE );
			}

			if ( ! empty( $metadata[ Asset_Type::FRONTEND_SCRIPT ] ) ) {
				$extension[Asset_Type::FRONTEND_SCRIPT] = self::register_extension_script_handle( $metadata, Asset_Type::FRONTEND_SCRIPT );
			}

			if ( ! empty( $metadata[ Asset_Type::FRONTEND_STYLE ] ) ) {
				$extension[Asset_Type::FRONTEND_STYLE] = self::register_extension_style_handle( $metadata, Asset_Type::FRONTEND_STYLE );
			}

			// Add the extension in the extension registry. This is used to enqueue extension assets subsequently.
			Extension_Registry::get_instance()->register( $extension );
		}

		/**
		 * Register an extension script with a unique script handle.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    The extracted and extended metadata for the current extension.
		 * @param     string    $type        The type (frontend, editor, both) of asset that is being registered.
		 *                                   The value passed is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    string                 The handle under which the script is registered.
		 */
		private static function register_extension_script_handle( array $metadata, string $type ): string {
			$handle = $metadata[$type];
			$path = self::remove_path_prefix( $handle );

			// Bail early if the passed path already is a handle (i.e. if it doesn't contain a 'file:' prefix).
			// In this case we do not need to build a custom handle and rely on the assets being enqueued by
			// the extension author.
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

			// Bail early if the script could not be registered.
			if ( ! $result ) {
				return '';
			}

			// TODO: do we need to add `wp_set_script_translations`?

			return $handle;
		}

		/**
		 * Register an extension style with a unique style handle.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    The extracted and extended metadata for the current extension.
		 * @param     string    $type        The type (frontend, editor, both) of asset that is being registered.
		 *                                   The value passed is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    string                 The handle under which the script is registered.
		 */
		private static function register_extension_style_handle( array $metadata, string $type ): string {
			$handle = $metadata[$type];
			$path = self::remove_path_prefix( $handle );

			// Bail early if the passed path already is a handle (i.e. if it doesn't contain a 'file:' prefix).
			// In this case we do not need to build a custom handle and rely on the assets being enqueued by
			// the extension author.
			if ( $handle === $path ) {
				return $handle;
			}

			$handle = self::build_asset_handle( $metadata['name'], $type );

			$result = wp_register_style(
				$handle,
				plugins_url( $path, $metadata['file'] )
			);

			// Bail early if the script could not be registered.
			if ( ! $result ) {
				return '';
			}

			return $handle;
		}

		/**
		 * Register action hooks for asset registration.
		 * Only register actions if they are not already present.
		 *
		 * This function must be called at least once anywhere during the loading process (before
		 * the enqueue hooks). Otherwise assets are not loaded. Currently this is handled by
		 * `Sixa_Blocks\Extension_Registry::get_instance()`.
		 *
		 * @see       Extension_Registry::get_instance()
		 * @since     1.0.0
		 * @return    void
		 */
		public static function add_enqueueing_actions(): void {
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

		/**
		 * Enqueue block assets (assets used in both editor and frontend).
		 * This function is hooked into `enqueue_block_assets`.
		 *
		 * @see       Functions::add_enqueueing_actions()
		 * @since     1.0.0
		 * @return    void
		 */
		public static function enqueue_block_assets(): void {
			self::enqueue_scripts_by_type( Asset_Type::SCRIPT );
			self::enqueue_styles_by_type( Asset_Type::STYLE );
		}

		/**
		 * Enqueue block editor assets (assets used only in the editor).
		 * This function is hooked into `enqueue_block_editor_assets`.
		 *
		 * @see       Functions::add_enqueueing_actions()
		 * @since     1.0.0
		 * @return    void
		 */
		public static function enqueue_editor_assets(): void {
			self::enqueue_scripts_by_type( Asset_Type::EDITOR_SCRIPT );
			self::enqueue_styles_by_type( Asset_Type::EDITOR_STYLE );
		}

		/**
		 * Enqueue frontend assets (assets used only in the frontend).
		 * This function is hooked into `wp_enqueue_scripts`.
		 *
		 * @see       Functions::add_enqueueing_actions()
		 * @since     1.0.0
		 * @return    void
		 */
		public static function enqueue_frontend_assets(): void {
			self::enqueue_scripts_by_type( Asset_Type::FRONTEND_SCRIPT );
			self::enqueue_styles_by_type( Asset_Type::FRONTEND_STYLE );
		}

		/**
		 * Enqueue all scripts of the given type.
		 *
		 * @since     1.0.0
		 * @param     string    $type    Type of the asset (frontend, editor, both).
		 *                               Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    void
		 */
		private static function enqueue_scripts_by_type( string $type ): void {
			$extension_registry = Extension_Registry::get_instance();
			foreach( $extension_registry->get_registered_extensions() as $extension ) {
				if ( ! empty( $extension[ $type ] ) ) {
					wp_enqueue_script( $extension[ $type ] );
				}
			}
		}

		/**
		 * Enqueue all styles of the given type.
		 *
		 * @since     1.0.0
		 * @param     string    $type    Type of the asset (frontend, editor, both).
		 *                               Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    void
		 */
		private static function enqueue_styles_by_type( string $type ): void {
			$extension_registry = Extension_Registry::get_instance();
			foreach( $extension_registry->get_registered_extensions() as $extension ) {
				if ( ! empty( $extension[ $type ] ) ) {
					wp_enqueue_style( $extension[ $type ] );
				}
			}
		}

		/**
		 * Find and return the JSON configuration file from the passed value.
		 * The path may or may not include the actual file. The following are examples for valid values:
		 *     `path/to/directory/`
		 *     `path/to/directory/extension.json`
		 *
		 * @since     1.0.0
		 * @param     string    $file_or_folder       Path to `extension.json` file or path to the
		 *                                            directory containing an `extension.json` file.
		 * @param     string    $metadata_filename    Name of the configuration file.
		 *                                            Defaults to `Sixa_Blocks\Functions::METADATA_FILE_NAME`.
		 * @return    string                          Path to `extension.json` file.
		 */
		private static function get_metadata_file_path_from_file_or_folder( string $file_or_folder, string $metadata_filename = self::METADATA_FILE_NAME ): string {
			if ( $metadata_filename !== substr( $file_or_folder, strlen( $metadata_filename ) * -1 ) ) {
				return sprintf( '%s%s', trailingslashit( $file_or_folder ), $metadata_filename );
			}
			return $file_or_folder;
		}

		/**
		 * Removes the file prefix for asset handles pointing to a file.
		 *
		 * @since     1.0.0
		 * @param     string    $path      Path to `extension.json` possibly containing a file prefix.
		 * @param     string    $prefix    Prefix to clean. Defaults to `Sixa_Blocks\Functions::PATH_PREFIX`.
		 * @return    string               Clean file path.
		 */
		private static function remove_path_prefix( string $path, string $prefix = self::PATH_PREFIX ): string {
			// Return the passed path if it does not contain the path prefix.
			if ( 0 !== strpos( $path, $prefix ) ) {
				return $path;
			}
			return substr( $path, strlen( $prefix ) );
		}

		/**
		 * Build and return an asset handle for the given extension name and asset type.
		 * The handle is simply the name of the extension suffixed by the type of asset.
		 *
		 * @since     1.0.0
		 * @param     string    $name    Name of the extension.
		 * @param     string    $type    Type of the asset. Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    string             Asset handle.
		 */
		private static function build_asset_handle( string $name, string $type ): string {
			return sprintf( '%s-%s', $name, Asset_Type::SLUGS[ $type ] );
		}

		/**
		 * Return the asset metadata from an `XY.asset.php` file.
		 * If no asset metadata file can be found, this function returns a set
		 * of default values for the fields `dependencies` and `version`.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    Extension metadata.
		 * @param     string    $type        Type of the asset. Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    array                  Asset metadata.
		 */
		private static function get_asset_meta( array $metadata, string $type ): array {
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
		 * Build and return the path to the asset metadata file from the path to the asset file.
		 * Asset metadata file is assumed to have the same name but with an `.asset.php` file extension.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    Extension metadata.
		 * @param     string    $type        Type of the asset. Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    string                 Path to the asset metadata file.
		 */
		private static function get_asset_meta_path( $metadata, $type ): string {
			$asset_path = self::remove_path_prefix( $metadata[$type ]);
			$asset_meta_path = substr_replace( $asset_path, '.asset.php', strlen( '.js' ) * -1 );
			return self::get_full_path_for_file( $metadata, $asset_meta_path );
		}

		/**
		 * Build and return the full path for the asset file with given type.
		 * The name and path to the asset file are extracted from the extension metadata.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    Extension metadata.
		 * @param     string    $type        Type of the asset. Type is a value from `Sixa_Blocks\Asset_Type`.
		 * @return    string                 Path to the asset file of given type.
		 */
		private static function get_full_asset_path( array $metadata, string $type ): string {
			$asset_path = self::remove_path_prefix( $metadata[$type] );
			return self::get_full_path_for_file( $metadata, $asset_path );
		}

		/**
		 * Build and return the full path for the given file from a relative path.
		 *
		 * @since     1.0.0
		 * @param     array     $metadata    Extension metadata.
		 * @param     string    $file        Relative path to a file.
		 * @return    string                 Full path to the given file.
		 */
		private static function get_full_path_for_file( $metadata, $file ): string {
			return realpath( sprintf( '%s%s', trailingslashit( dirname( $metadata['file'] ) ), $file ) );
		}

	}

endif;
