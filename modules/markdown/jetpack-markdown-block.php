<?php
/*

Description: Jetpack markdown block for Gutenberg
Version: 0.1
Author: Rene Cabral
*/

add_action( 'init', array( 'Jetpack_Markdown_Block', 'register_block_types' ) );
add_action( 'enqueue_block_editor_assets', array( 'Jetpack_Markdown_Block', 'enqueue_block_editor_assets' ) );


class Jetpack_Markdown_Block {

	public static function register_block_types() {

		register_meta(
			'post',
			'gutenberg_markdown',
			array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_block_type(
			'jetpack/markdown'
		);
	}

	public static function enqueue_block_editor_assets() {

		wp_enqueue_script(
			'markdown-it',
			plugins_url( 'assets/js/markdown-it.min.js', __FILE__ ),
			array()
		);

		wp_enqueue_script(
			'jetpack-markdown',
			plugins_url( 'assets/js/jetpack-markdown-block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'markdown-it' )
		);

		wp_enqueue_style(
			'jetpack-markdown',
			plugins_url( 'assets/css/editor.css', __FILE__ ),
			array()
		);

	}

}

