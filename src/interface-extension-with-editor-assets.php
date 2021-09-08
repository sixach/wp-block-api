<?php
/**
 * Interface for Extensions with editor assets.
 *
 * Use this interface if your extension loads editor assets and add the assets
 * your extension should load in your implementation of `enqueue_editor_assets`.
 *
 * `Extension` automatically adds `enqueue_editor_assets` to the WordPress action
 * hook `enqueue_editor_assets` if the extending class implements this interface.
 * That is, you need not add the hook yourself.
 *
 * Note that there are additional helper functions available in `Extension` to
 * simplify the registration of assets. These helper functions are
 * `Extension::enqueue_style` and `Extension::enqueue_script`.
 *
 * The default implementation of `enqueue_editor_assets` could look as follows:
 *
 * ```PHP
 * class My_Extension extends Extension implements Extension_With_Editor_Assets {
 *
 *     protected static string $name = 'sixa-my-extension';
 *
 *     public static function enqueue_editor_assets(): void {
 *         self::enqueue_script( 'index.js' );
 *     }
 *
 * }
 * ```
 *
 * Block assets are loaded in the editor.
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks\Extension_With_Editor_Assets
 */

namespace Sixa_Blocks;

if ( ! interface_exists( Extension_With_Editor_Assets::class ) ) :

	/**
	 * Interface class for Extensions that load editor assets.
	 *
	 * @see    Extension
	 */
	interface Extension_With_Editor_Assets {

		/**
		 * Enqueue editor assets for your extension.
		 *
		 * This function typically calls `Extension::enqueue_script` or `Extension::enqueue_style`.
		 * This function is automatically added to the WordPress action hook `enqueue_editor_assets`
		 * by `Extension` during initialization.

		 * @see       Extension
		 * @see       Extension::add_actions()
		 * @see       Extension::enqueue_style()
		 * @see       Extension::enqueue_script()
		 *
		 * @since     1.0.0
		 * @return    void
		 */
		public static function enqueue_editor_assets(): void;

	}

endif;
