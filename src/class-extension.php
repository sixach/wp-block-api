<?php

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Extension::class ) ) :

	class Extension {

		protected static string $name;
		protected static string $path;

		protected static array $editor_assets = array();
		protected static array $block_assets = array();
		protected static array $frontend_assets = array();

		public static function init(): void {
			if ( ! isset( static::$name ) ) {
				throw new \LogicException( sprintf( '%s must have a $name', static::class ) );
			}

			if ( ! isset( static::$path ) ) {
				throw new \LogicException( sprintf( '%s must have a $path', static::class ) );
			}

			static::enqueue_assets();
		}

		public static function enqueue_assets(): void {
			if ( count( static::$editor_assets ) > 0 ) {
				add_action( 'enqueue_block_editor_assets', array( static::class, 'enqueue_editor_assets' ), 0 );
			}

			if ( count( static::$block_assets ) > 0 ) {
				add_action( 'enqueue_block_assets', array( static::class, 'enqueue_block_assets' ), 0 );
			}

			if ( count( static::$frontend_assets ) ) {
				static::enqueue_frontend_assets();
			}
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
		public static function get_build_dir_path( $build_dir = API_Constants::BUILD_DIR ): string {
			return trailingslashit( sprintf( '%s%s', trailingslashit( dirname( static::$path, 2 ) ), $build_dir ) );
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
		public static function get_build_file_path( $file_name, $build_dir = API_Constants::BUILD_DIR ): string {
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
		public static function get_build_file_url( $file_name, $build_dir = API_Constants::BUILD_DIR ): string {
			return sprintf( '%s%s%s', plugin_dir_url( dirname( static::$path ) ), trailingslashit( $build_dir ), $file_name );
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
		public static function get_build_asset_path( $file_name, $build_dir = API_Constants::BUILD_DIR ): string {
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
		public static function get_asset_file_name( $build_file_name ): string {
			$file_name_parts = explode( '.', $build_file_name );

			if ( 2 !== count( $file_name_parts ) ) {
				return '';
			}

			$asset_parts = array( $file_name_parts[0], 'asset', 'php' );
			$asset_name = implode( '.', $asset_parts );
			return $asset_name;
		}

		/**
		 * Enqueue for editor.
		 */
		public static function enqueue_editor_assets(): void {
			static::enqueue_script( 'index.js' );
		}

		/**
		 * Enqueue for editor and frontend.
		 */
		public static function enqueue_block_assets(): void {
			static::enqueue_style( 'style-index.css' );
		}

		/**
		 * Enqueue for frontend.
		 */
		public static function enqueue_frontend_assets(): void {
			print( 'enqueue_frontend_assets <br><br>' );
		}

		/**
		 * Enqueue the passed script.
		 *
		 * In addition to simply passing the given file to the WordPress
		 * enqueue function, this function builds and extracts all
		 * relevant paths, names, and parameters.
		 *
		 * @since     1.0.0
		 * @param     $file_name    Name of the build file to be enqueued.
		 * @return    void
		 */
		public static function enqueue_script( $file_name ): void {
			$asset_path = static::get_build_file_url( $file_name );
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
		 * @param     $file_name    Name of the build file to be enqueued.
		 * @return    void
		 */
		public static function enqueue_style( $file_name ): void {
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
		 * @param     $file_name    Name of the build file to be enqueued.
		 * @return    array
		 */
		public static function get_asset_dependencies( $file_name ): array {
			$file_path = static::get_build_file_path( $file_name );
			$asset_path = static::get_build_asset_path( $file_name );
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
