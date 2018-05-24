<?php
require_once( dirname( __FILE__ ) . '/../../../../modules/markdown.php');

class WP_Test_Markdown extends WP_UnitTestCase {

	private $block_editor_post;

	private $fixture_block_editor_post_insert;

	private $fixture_block_editor_post_rendered;

	private function add_test_block_editor_markdown_post() {

		$post_id = $this->factory->post->create(
			array(
				'post_content' => $this->fixture_block_editor_post_insert,
				'post_content_filtered' => $this->fixture_block_editor_post_insert,
				'post_title' => 'Test Block Editor Post',
			)
		);

		return $post_id;
	}

	/**
	 * Setup environment for Markdown Tests.
	 *
	 * @since 4.4.0
	 */
	public function setUp() {

		parent::setUp();

		$this->fixture_block_editor_post_insert = file_get_contents(
			dirname( __FILE__ ) .'/fixtures/block-editor-markdown-block-insert.txt'
		);
		$this->fixture_block_editor_post_rendered = file_get_contents(
			dirname( __FILE__ ) .'/fixtures/block-editor-markdown-block-rendered.txt'
		);

		update_option( 'wpcom_publish_posts_with_markdown', true );
		do_action( 'init' );

		// If authorized role is not set, post will not be sanitized in an accurate way.
		$author_id = $this->factory->user->create(
			array( 'role' => 'editor', )
		);

		wp_set_current_user( $author_id );

		$post_id = $this->add_test_block_editor_markdown_post();
		$this->block_editor_post = get_post( $post_id );
		
	}

	/**
	 * Test if _wpcom_is_markdown postmeta is set as true
	 * after a block_editor insert containing Markdown blocks.
	 *
	 * @since 4.6.0
	 */
	public function test_if_wpcom_is_markdown_meta_was_set_as_true() {

		$this->assertEquals(
			WPCom_Markdown::get_instance()->is_markdown($this->block_editor_post->ID)
			, "1"
		);
	}

	/**
	 * Test if Markdown and other blocks from block_editor are saved intact
	 * in post_content_filtered after being processed by the Markdown module.
	 *
	 * @since 4.6.0
	 */
	public function test_if_block_editor_markdown_post_content_filtered_is_saved_correctly() {

		$this->assertEquals(
			$this->fixture_block_editor_post_insert,
			$this->block_editor_post->post_content_filtered
		);
	}

	/**
	 * Test if the Markdown block and other blocks are retrieved correctly when supplied
	 * to block_editor for editing.
	 *
	 * @since 4.6.0
	 */
	public function test_if_block_editor_markdown_post_is_retrieved_correctly_for_editing() {

		$post_type_object = get_post_type_object( $this->block_editor_post->post_type );
		$request = new WP_REST_Request(
			'GET',
			sprintf( '/wp/v2/%s/%d', $post_type_object->rest_base, $this->block_editor_post->ID )
		);
		$request->set_param( 'context', 'edit' );
		$response = rest_do_request( $request );
		$rest_post = rest_get_server()->response_to_data( $response, false );

		$post_to_edit = apply_filters( 'after_block_editor_gets_post_to_edit', $rest_post );

		$this->assertEquals(
			$this->fixture_block_editor_post_insert,
			$post_to_edit['content']['raw']
		);

	}

	/**
	 * Test if the Markdown block and other blocks are rendered correctly when supplied to
	 * a user for viewing.
	 *
	 * @since 4.6.0
	 */
	public function test_if_block_editor_markdown_post_is_rendered_correctly_for_viewing() {

		$this->assertEquals(
			$this->fixture_block_editor_post_rendered,
			$this->block_editor_post->post_content
		);
	}

}