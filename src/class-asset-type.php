<?php
/**
 * Constants for asset types.
 *
 * We introduce these asset types for the extension JSON definition. However, the naming and
 * usages of these asset types follows the same definitions that WordPress uses for blocks.
 * That is, we apply the same naming such that the prefix indicates where the assets are loaded
 * and no prefix indicates that the assets are loaded in the editor and the frontend.
 *
 *     `style` -> editor + frontend
 *     `script` -> editor + frontend
 *     `editorStyle` -> editor
 *     `editorScript` -> editor
 *     `frontendStyle` -> frontend
 *     `frontendScript` -> frontend
 *
 * @link          https://sixa.ch
 * @author        sixa AG
 * @since         1.0.0
 *
 * @package       Sixa_Blocks
 * @subpackage    Sixa_Blocks/Asset_Type
 */

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( Asset_Type::class ) ) :

	/**
	 * Constants used throughout this library.
	 */
	class Asset_Type {

		/**
		 * Name of the style asset that is enqueued in the editor and the frontend.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const STYLE = 'style';

		/**
		 * Name of the script asset that is enqueued in the editor and the frontend.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const SCRIPT = 'script';

		/**
		 * Name of the script asset that is enqueued in the editor.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const EDITOR_SCRIPT = 'editorScript';

		/**
		 * Name of the style asset that is enqueued in the editor.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const EDITOR_STYLE = 'editorStyle';

		/**
		 * Name of the script asset that is enqueued in the frontend.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const FRONTEND_SCRIPT = 'frontendScript';

		/**
		 * Name of the script asset that is enqueued in the frontend.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const FRONTEND_STYLE = 'frontendStyle';

		/**
		 * Mapping of names to slugs.
		 *
		 * Names are used in the JSON definition whereas slugs are used in handles.
		 *
		 * @var      string
		 * @since    1.0.0
		 */
		public const SLUGS = array(
			Asset_Type::STYLE => 'style',
			Asset_Type::SCRIPT => 'script',
			Asset_Type::EDITOR_SCRIPT => 'editor-script',
			Asset_Type::EDITOR_STYLE => 'editor-style',
			Asset_Type::FRONTEND_SCRIPT => 'frontend-script',
			Asset_Type::FRONTEND_STYLE => 'frontend-style',
		);

	}

endif;
