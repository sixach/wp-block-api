# WordPress Block API

Small collection of helpful interfaces and abstract classes
to make the development of WordPress Blocks and Extensions easier
and more consistent.

## Requirements

* PHP version 7.4 or greater
* WordPress version 5.6 or greater

## Installation

You will need [Composer](https://getcomposer.org/) installed
on your computer in order to build and use this package.

* Add this package to the `require` field in your `composer.json`
* Import `vendor/autoload.php` in your project

---

# Usage

## In Blocks & Extensions
Simply extend the block or extension class that fits the requirements of your package
and extend all relevant interfaces (for extensions):

```PHP
namespace Sixa_Blocks;

class My_Block extends Block {

	public static function register(): void {
		register_block_type_from_metadata( dirname( __FILE__, 2 ) );
	}

}
```

```PHP
namespace Sixa_Blocks;

class My_Extension extends Extension implements Extension_With_Editor_Assets {

	protected static string $name = 'sixa-wp-extension-my-extension';

	public static function enqueue_editor_assets(): void {
		self::enqueue_script( 'index.js' );
	}

}
```

Development and usage is simplified if all blocks and extensions use `namespace Sixa_Blocks`.

## In Projects
Each block and extension includes an `init` function that can be called to initialize
the class. No other function needs to be called.

### As a Plugin
To run your block or extension as a standalone plugin, simply create a plugin file 
(e.g. `index.php`) that calls the `init` function of your class.

```PHP
<?php
/**
 * My Block.
 *
 * @wordpress-plugin
 * Plugin Name:          Sixa - My Block
 * Description:          My block for WordPress editor.
 * Version:              1.0.0
 * Requires at least:    5.7
 * Requires PHP:         7.4
 * Author:               sixa AG
 * License:              GPL v3 or later
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          sixa
 *
 * @package              sixa
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Include the namespace of this block.
 */
use Sixa_Blocks\My_Block;

/**
 * Composer autoload is needed in this package even if
 * it doesn't use any libraries to autoload the classes
 * from this package.
 *
 * @see    https://getcomposer.org/doc/01-basic-usage.md#autoloading
 */
require __DIR__ . '/vendor/autoload.php';

/**
 * Initialize your block.
 *
 * Other than this function call, this file should not include any logic
 * and should merely be used as an entry point to use this package as
 * a WordPress plugin.
 */
My_Block::init();
```

### As a package
Install your block or extension as a composer package and import `vendor/autoload.php`
somewhere in your project.

Next, simply call the `init` function:

```PHP
Sixa_Blocks\My_Block::init();
```

---
# Available Classes and Interfaces

In this section we elaborate on the available classes and interfaces and outline
how we intend them to be used.

## Abstract Class Block

A simple block class that includes the default implementation of a block class.
Particularly, `Sixa_Blocks\Block` includes and performs block initialization
and adds `Sixa_Blocks\Block::register()` to the WordPress action hook `init`.

`Sixa_Blocks\Block::register()` is not implemented. In its most basic form, a
simple block only needs to implement this function and perform `register_block_type_from_metadata`
according to the block requirements.

### Basic Example

```PHP
namespace Sixa_Blocks;

final class My_Basic_Block extends Block {

	public static function register(): void {
		register_block_type_from_metadata( dirname( __FILE__, 2 ) );
	}

}
```

Notice the level `2` used in `dirname`. This is because the block class is inside a
directory in the root of the project (i.e. not inside the same directory as `block.json`).

### Intermediate Example

```PHP
namespace Sixa_Blocks;

final class My_Block extends Block {

	public static function register(): void {
		self::some_setup();
		register_block_type_from_metadata(
			dirname( __DIR__ ),
			array(
				'render_callback' => array( __CLASS__, 'render' ),
			)
		);
	}
	
	public static function render( array $attributes = array() ): string {
		// Add your render callback here.
	}

	private static function some_setup(): void {
		// Add your setup code here (e.g. post type registration).
	}
}
```

## Abstract Class WooCommerce Block

A simple block class that extends `Sixa_Blocks\Block` with a few additional convenience
functions to enable WooCommerce specific block initialization.
Particularly, for a set of WooCommerce blocks (i.e. blocks that require WooCommerce), 
initialization and registration of the block is only necessary if WooCommerce is also installed.
In some cases the block might even produce a fatal error if WooCommerce is not activated.

For these instances, we intend to skip block registration.
`Sixa_Blocks\WooCommerce_Block` extends `Sixa_Blocks\Block` with a simple check in
`Sixa_Blocks\WooCommerce_Block::init()` that prevents the block from being registered 
(i.e. `Sixa_Blocks\Block::register()` from being added in `init`) if WooCommerce is not installed
and activated.

Other than that, `Sixa_Blocks\WooCommerce_Block` is identical to `Sixa_Blocks\Block`.


### Basic Example


```PHP
namespace Sixa_Blocks;

final class My_WooCommerce_Block extends WooCommerce_Block {

	public static function register(): void {
		register_block_type_from_metadata( dirname( __FILE__, 2 ) );
	}

}
```

`Sixa_Blocks\My_WooCommerce_Block::register()` is automatically **not** called if 
WooCommerce is not installed.

## Class Extension
