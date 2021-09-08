<?php
/**
 * Interface for Extensions with block assets.
 *
 * Use this interface if your extension loads block assets and add the assets
 * your extension should load in your implementation of `enqueue_block_assets`.
 *
 * `Extension` automatically adds `enqueue_block_assets` to the WordPress action
 * hook `enqueue_block_assets` if the extending class implements this interface.
 * That is, you need not add the hook yourself.
 *
 * Note that there are additional helper functions available in `Extension` to
 * simplify the registration of assets. These helper functions are
 * `Extension::enqueue_style` and `Extension::enqueue_script`.
 *
 * The default implementation of `enqueue_block_assets` could look as follows:
 *
 * ```PHP
 * class My_Extension extends Extension implements Extension_With_Block_Assets {
 *
 *     protected static string $name = 'sixa-my-extension';
 *
 *     public static function enqueue_block_assets(): void {
 *         self::enqueue_script( 'style-index.css' );
 *     }
 *
 * }
 * ```
 *
 * Block assets are loaded in the editor as well as the frontend.
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks\Extension_With_Block_Assets
 */

namespace Sixa_Blocks;

if ( ! interface_exists( Extension_With_Block_Assets::class ) ) :

	interface Extension_With_Block_Assets {

		public static function enqueue_block_assets(): void;

	}

endif;


