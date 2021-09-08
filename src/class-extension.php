<?php

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Extension::class ) ) :

	/**
	 * Class to build WordPress extensions.
	 *
	 * Includes all relevant boilerplate and loading functions to enqueue assets for an
	 * extension. Different types of assets are supported by implementing any of the
	 * interfaces published in this library.
	 *
	 * This class is intended to be extended by the class of an actual extension. In addition,
	 * extension classes are intended to implement any number of extension interfaces.
	 *
	 * @see    Extension_With_Editor_Assets      Indicates that the implementing Extensino class loads editor assets.
	 * @see    Extension_With_Block_Assets       Indicates that the implementing Extension class loads block assets
	 * @see    Extension_With_Frontend_Assets    Indicates that the implementing Extension class loads frontend assets.
	 *
	 */
	class Extension {

		/**
		 * @var string
		 */
		protected static string $name;

		public static function init(): void {
			if ( ! isset( static::$name ) ) {
				throw new \LogicException( sprintf( '%s must have a $name', static::class ) );
			}

			static::add_actions();
		}

		/**
		 * Add WordPress actions to enqueue assets.
		 *
		 * Check if the used class implements any of the available interfaces. In case
		 * that an interface is implemented, automatically add the corresponding
		 * action for the required function (which is defined in the interface).
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		protected static function add_actions(): void {
			$implemented_interfaces = class_implements( static::class );

			if ( in_array( Extension_With_Editor_Assets::class, $implemented_interfaces ) ) {
				// Enqueue assets in the editor.
				add_action( 'enqueue_block_editor_assets', array( static::class, 'enqueue_editor_assets' ), 0 );
			}

			if ( in_array( Extension_With_Block_Assets::class, $implemented_interfaces ) ) {
				// Enqueue assets in the editor as well as the frontend.
				add_action( 'enqueue_block_assets', array( static::class, 'enqueue_block_assets' ), 0 );
			}

			if ( in_array( Extension_With_Frontend_Assets::class, $implemented_interfaces ) ) {
				// Enqueue assets in the frontend.
				add_action( 'wp_enqueue_scripts', array( static::class, 'enqueue_frontend_assets' ), 0 );
			}
		}

		protected static function get_class_file_name(): string {
			$reflector = new \ReflectionClass( static::class );
			return $reflector->getFileName();
		}

		/**
		 * Return the full absolute path of the build directory in an extension.
		 * This is the full absolute path to the root directory of the extension with the
		 * name of the build directory appended to it.
		 *
		 * Notice that this function assumes a certain structure of your extension package.
		 * Particularly, this function assumes that the directory that contains the PHP
		 * extension class is in a directory in the root directory of your extension.
		 *
		 * @since     1.0.0
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    string
		 */
		protected static function get_build_dir_path( string $build_dir = API_Constants::BUILD_DIR ): string {
			return trailingslashit( sprintf( '%s%s', trailingslashit( dirname( static::get_class_file_name(), 2 ) ), $build_dir ) );
		}

		/**
		 * Return the full absolute path of the passed build file in an extension.
		 * This is the full absolute path to the root directory of the extension with the
		 * build directory and file name attached. The passed file must be inside the build
		 * directory.
		 *
		 * @since     1.0.0
		 * @param     string    $file_name    Name of a file (including extension).
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    string
		 */
		protected static function get_build_file_path( string $file_name, string $build_dir = API_Constants::BUILD_DIR ): string {
			return sprintf( '%s%s', static::get_build_dir_path( $build_dir ), $file_name );
		}

		/**
		 * Return the URL file path for the passed build file in an extension.
		 *
		 * @since     1.0.0
		 * @param     string    $file_name    Name of a file (including extension).
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    string
		 */
		protected static function get_build_file_url( string $file_name, string $build_dir = API_Constants::BUILD_DIR ): string {
			return sprintf( '%s%s%s', plugin_dir_url( dirname( static::get_class_file_name() ) ), trailingslashit( $build_dir ), $file_name );
		}

		/**
		 * Return the full absolute path for a PHP asset file for a given build file
		 * in an extension.
		 *
		 * @since     1.0.0
		 * @param     string    $file_name    Name of a file (including extension).
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    string
		 */
		protected static function get_build_asset_path( string $file_name, string $build_dir = API_Constants::BUILD_DIR ): string {
			return static::get_build_file_path( static::get_asset_file_name( $file_name ), $build_dir );
		}

		/**
		 * Build and return the asset file name from a build file name.
		 *
		 * The asset file is typically auto-generated and follows
		 * a naming pattern relative to the original file name. For instance,
		 * index.asset.php for index.js. This function returns the asset file
		 * name following this pattern.
		 *
		 * @since     1.0.0
		 * @param     string    $build_file_name    Name of the original file (e.g. index.js).
		 * @return    string
		 */
		protected static function get_asset_file_name( string $build_file_name ): string {
			$file_name_parts = explode( '.', $build_file_name );

			if ( 2 !== count( $file_name_parts ) ) {
				return '';
			}

			$asset_parts = array( $file_name_parts[0], 'asset', 'php' );
			$asset_name = implode( '.', $asset_parts );
			return $asset_name;
		}

		/**
		 * Enqueue the passed script.
		 *
		 * In addition to simply passing the given file to the WordPress
		 * enqueue function, this function builds and extracts all
		 * relevant paths, names, and parameters.
		 *
		 * @since     1.0.0
		 * @param     string    $file_name    Name of the build file to be enqueued.
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    void
		 */
		protected static function enqueue_script( string $file_name, string $build_dir = API_Constants::BUILD_DIR ): void {
			$asset_path = static::get_build_file_url( $file_name, $build_dir );
			$asset_dependencies = static::get_asset_dependencies( $file_name );

			wp_enqueue_script(
				static::$name,
				$asset_path,
				$asset_dependencies['dependencies'] ?? array(),
				$asset_dependencies['version'] ?? null,
				false
			);
		}

		/**
		 * Enqueue the passed stylesheet.
		 *
		 * In addition to simply passing the given file to the WordPress
		 * enqueue function, this function builds and extracts all
		 * relevant paths, names, and parameters.
		 *
		 * @since     1.0.0
		 * @param     string     $file_name    Name of the build file to be enqueued.
		 * @return    void
		 */
		protected static function enqueue_style( string $file_name ): void {
			$asset_path = static::get_build_file_url( $file_name );
			$asset_dependencies = static::get_asset_dependencies( $file_name );

			wp_enqueue_style(
				static::$name,
				$asset_path,
				$asset_dependencies['dependencies'] ?? array(),
				$asset_dependencies['version'] ?? null,
				false
			);
		}

		/**
		 * Return asset dependencies and asset version for the
		 * given build file. If available, this information is extracted
		 * from an auto-generated PHP asset file. Otherwise, this function
		 * returns a set of fallback values.
		 *
		 * @since     1.0.0
		 * @param     string    $file_name    Name of the build file to be enqueued.
		 * @param     string    $build_dir    Name of the build directory.
		 * @return    array
		 */
		protected static function get_asset_dependencies( string $file_name, string $build_dir = API_Constants::BUILD_DIR ): array {
			$file_path = static::get_build_file_path( $file_name, $build_dir );
			$asset_path = static::get_build_asset_path( $file_name, $build_dir );
			$asset = file_exists( $asset_path )
				? require $asset_path
				: array(
					'dependencies' => array(),
					'version'      => filemtime( $file_path ),
				);

			return $asset;
		}

	}

endif;
