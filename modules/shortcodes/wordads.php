<?php

/**
 * Embed WordAds 'add' in post
 *
 */
class Jetpack_WordAds_Shortcode {

	private $scripts_and_style_included = false;

	function __construct() {
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	 * Register our shortcode and enqueue necessary files.
	 */
	function action_init() {
		// Enqueue styles if [recipe] exists.
		// add_action( 'wp_head', array( $this, 'add_scripts' ), 1 );

		// Render [recipe], along with other shortcodes that can be nested within.
		add_shortcode( 'wordad', array( $this, 'wordads_shortcode' ) );
	}

	/**
	 * Add hooks according to screen.
	 *
	 * @param WP_Screen $screen Data about current screen.
	 */
	public static function add_hooks( $screen ) {
		if ( isset( $screen->base ) && 'post' === $screen->base ) {
			add_action( 'admin_notices', array( __CLASS__, 'handle_editor_view_js' ) );
			add_action( 'admin_head', array( __CLASS__, 'admin_head' ) );
		}
	}

	/**
	 * WordPress Shortcode Editor View JS Code
	 */
	public static function handle_editor_view_js() {
		add_filter( 'mce_external_plugins', array( __CLASS__, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
		wp_enqueue_script(
			'wordads-shortcode-editor-view',
			Jetpack::get_file_url_for_environment(
				'_inc/build/wordads/js/wordads-shortcode.min.js',
				'modules/wordads/js/wordads-shortcode.js'
			),
			array( 'wp-util', 'jquery', 'quicktags' ),
			false,
			true
		);
	}
	/**
	 * Enqueue scripts and styles
	 */
	function add_scripts() {
		if ( empty( $GLOBALS['posts'] ) || ! is_array( $GLOBALS['posts'] ) ) {
			return;
		}

		foreach ( $GLOBALS['posts'] as $p ) {
			if ( has_shortcode( $p->post_content, 'recipe' ) ) {
				$this->scripts_and_style_included = true;
				break;
			}
		}

		if ( ! $this->scripts_and_style_included ) {
			return;
		}

		wp_enqueue_style( 'jetpack-recipes-style', plugins_url( '/css/recipes.css', __FILE__ ), array(), '20130919' );
		wp_style_add_data( 'jetpack-recipes-style', 'rtl', 'replace' );

		// add $themecolors-defined styles.
		wp_add_inline_style( 'jetpack-recipes-style', self::themecolor_styles() );

		wp_enqueue_script(
			'jetpack-recipes-printthis',
			Jetpack::get_file_url_for_environment( '_inc/build/shortcodes/js/recipes-printthis.min.js', 'modules/shortcodes/js/recipes-printthis.js' ),
			array( 'jquery' ),
			'20170202'
		);

		wp_enqueue_script(
			'jetpack-recipes-js',
			Jetpack::get_file_url_for_environment( '_inc/build/shortcodes/js/recipes.min.js', 'modules/shortcodes/js/recipes.js' ),
			array( 'jquery', 'jetpack-recipes-printthis' ),
			'20131230'
		);

		$title_var     = wp_title( '|', false, 'right' );
		$rtl           = is_rtl() ? '-rtl' : '';
		$print_css_var = plugins_url( "/css/recipes-print{$rtl}.css", __FILE__ );

		wp_localize_script(
			'jetpack-recipes-js',
			'jetpack_recipes_vars',
			array(
				'pageTitle' => $title_var,
				'loadCSS' => $print_css_var,
			)
		);
	}

	/**
	 * Our [recipe] shortcode.
	 * Prints recipe data styled to look good on *any* theme.
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Post content.
	 *
	 * @return string HTML for recipe shortcode.
	 */
	static function wordads_shortcode( $atts, $content = '' ) {
		$atts = shortcode_atts(
			array(
			), $atts, 'wordads'
		);

		return self::wordads_shortcode_html( $atts, $content );
	}

	/**
	 * The recipe output
	 *
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $content Post content.
	 *
	 * @return string HTML output
	 */
	static function wordads_shortcode_html( $atts, $content = '' ) {
		global $wordads;

		if ( empty( $wordads ) ) {
			return __( '<div>The WordAds module is not active</div>' );
		}

		$html = '<div class="jetpack-wordad" itemscope itemtype="https://schema.org/WPAdBlock">';

		$html .= '</div>';

		$html = $wordads->insert_ad( $html );

		return $html;
	}

	public static function admin_head() {
		remove_action( 'media_buttons', 'wordads_media_button', 9999 );
		add_action( 'media_buttons', array( __CLASS__, 'wordads_media_button' ), 9999 );
	}

	public static function wordads_media_button() {
		$title = __( 'Insert Ad', 'jetpack' );
		?>

		<button type="button" id="insert-jetpack-wordads-inline-ad" class="button" title="<?php echo esc_attr( $title ); ?>" href="javascript:;">
			<span class="jetpack-wordads-inline-ad-icon jetpack-contact-form-icon"></span>
			<?php echo esc_html( $title ); ?>
		</button>

		<?php
	}
}

new Jetpack_WordAds_Shortcode();
add_action( 'current_screen', array( 'Jetpack_WordAds_Shortcode', 'add_hooks' ) );
