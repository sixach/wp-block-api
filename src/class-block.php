<?php

namespace Sixa_Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class Block {

    /**
     * Record if the block was initialized to make sure it is
     * initialized at most once.
     *
     * @since    1.0.0
     * @var      bool
     */
    private static $was_initialized = false;

    /**
     * Initialize the block.
     * Set up the WordPress hook to register the block.
     *
     * @since     1.0.0
     * @return    void
     */
    public static function init(): void {
        // Bail early if the block was already initialized.
        if ( static::$was_initialized ) {
            return;
        }

        add_action( 'init', array( static::class, 'register' ) );
    }

    /**
     * Registers the block using the metadata loaded from the `block.json` file.
     * Behind the scenes, it registers also all assets so they can be enqueued
     * through the block editor in the corresponding context.
     *
     * @see       https://developer.wordpress.org/block-editor/tutorials/block-tutorial/writing-your-first-block-type/
     * @since     1.0.0
     * @return    void
     */
    public static function register(): void {
        register_block_type_from_metadata( dirname( static::class ) );
    }

}
