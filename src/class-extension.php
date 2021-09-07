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

		public static function enqueue_assets() {
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
		 * @return string
		 */
		public static function get_build_dir_path(): string {
			return trailingslashit( sprintf( '%sbuild', trailingslashit( dirname( static::$path, 2 ) ) ) );
		}

		/**
		 * @param $file_name
		 * @return string
		 */
		public static function get_build_file_path( $file_name ) {
			return sprintf( '%s%s', static::get_build_dir_path(), $file_name );
		}

		/**
		 * @param $file_name
		 * @return string
		 */
		public static function get_build_file_url( $file_name ) {
			return sprintf( '%sbuild/%s', plugin_dir_url( dirname( static::$path ) ), $file_name );
		}

		/**
		 * @param $file_name
		 * @return string
		 */
		public static function get_build_asset_path( $file_name ) {
			return static::get_build_file_path( static::get_asset_file_name( $file_name ) );
		}

		/**
		 * @param $build_file_name
		 * @return string
		 */
		public static function get_asset_file_name( $build_file_name ) {
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
		public static function enqueue_editor_assets() {
			static::enqueue_script( 'index.js' );
		}

		/**
		 * Enqueue for editor and frontend.
		 */
		public static function enqueue_block_assets() {
			static::enqueue_style( 'style-index.css' );
		}

		/**
		 * Enqueue for frontend.
		 */
		public static function enqueue_frontend_assets() {
			print( 'enqueue_frontend_assets <br><br>' );
		}

		public static function enqueue_script( $file_name ) {
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

		public static function enqueue_style( $file_name ) {
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

		public static function get_asset_dependencies( $file_name ) {
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
