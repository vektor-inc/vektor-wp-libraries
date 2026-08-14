<?php
/**
 * Class VK_Font_Selector_Test
 *
 * @package vektor-inc/VK_Font_Selector
 */

class VK_Font_Selector_Test extends WP_UnitTestCase {

	/**
	 * テスト前のオプション値.
	 *
	 * @var array
	 */
	private $original_font_option;

	/**
	 * テスト前のグローバル値.
	 *
	 * @var array
	 */
	private $original_globals;

	/**
	 * テストの前処理.
	 */
	public function setUp(): void {
		parent::setUp();

		$missing                    = new stdClass();
		$original_font_option       = get_option( 'vk_font_selector', $missing );
		$this->original_font_option = array(
			'exists' => $missing !== $original_font_option,
			'value'  => $original_font_option,
		);

		$this->original_globals = array();
		foreach ( array( 'current_screen', 'vk_font_selector_editor_style' ) as $global_name ) {
			$this->original_globals[ $global_name ] = array(
				'exists' => array_key_exists( $global_name, $GLOBALS ),
				'value'  => array_key_exists( $global_name, $GLOBALS ) ? $GLOBALS[ $global_name ] : null,
			);
		}
	}

	/**
	 * Get font url
	 */
	public function test_get_web_fonts_url() {

		// テスト配列 .
		$test_array = array(
			array(
				'vk_font_selector' => null,
				'correct'          => '',
			),
			// 700が先でもURLでは500が先になるかどうか .
			array(
				'vk_font_selector' => array(
					'title' => 'Noto+Serif+JP:700',
					'text'  => 'Noto+Serif+JP:500',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@500;700&display=swap&subset=japanese',
			),
			// 自動で700が追加されるかどうか .
			array(
				'vk_font_selector' => array(
					'text' => 'Noto+Serif+JP:500',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@500;700&display=swap&subset=japanese',
			),
			array(
				'vk_font_selector' => array(
					'title' => 'Noto+Sans+JP:500',
					'text'  => 'Noto+Serif+JP:500',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500&family=Noto+Serif+JP:wght@500;700&display=swap&subset=japanese',
			),
			array(
				'vk_font_selector' => array(
					'text' => 'Sawarabi+Mincho',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&display=swap&subset=japanese',
			),
			array(
				'vk_font_selector' => array(
					'title' => 'Noto+Sans+JP:500',
					'text'  => 'Sawarabi+Mincho',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500&family=Sawarabi+Mincho&display=swap&subset=japanese',
			),
			array(
				'vk_font_selector' => array(
					'title' => 'Sawarabi+Mincho',
					'text'  => 'Noto+Sans+JP:500',
				),
				'correct'          => 'https://fonts.googleapis.com/css2?family=Sawarabi+Mincho&family=Noto+Sans+JP:wght@500;700&display=swap&subset=japanese',
			),
		);

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'Vk_Font_Selector::get_web_fonts_url()' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			update_option( 'vk_font_selector', $value['vk_font_selector'] );
			$return = Vk_Font_Selector::get_web_fonts_url();
			print 'return : ' . esc_attr( $return ) . PHP_EOL;
			print 'correct : ' . esc_attr( $value['correct'] ) . PHP_EOL;
			$this->assertEquals( $value['correct'], $return );
		}
	}

	/**
	 * Iframe 用アセット収集時にフォント設定の動的 CSS が含まれること.
	 */
	public function test__wp_get_iframed_editor_assets() {
		update_option( 'vk_font_selector', array( 'title' => 'mincho' ) );
		set_current_screen( 'post' );
		$GLOBALS['vk_font_selector_editor_style'] = 'vk-font-selector-test';

		wp_styles();
		wp_scripts();
		wp_register_style( 'vk-font-selector-test', false, array(), Vk_Font_Selector::$version );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_test_editor_style' ), 10 );

		$assets = _wp_get_iframed_editor_assets();

		remove_action( 'enqueue_block_assets', array( $this, 'enqueue_test_editor_style' ), 10 );

		$this->assertStringContainsString( '/* Font switch */', $assets['styles'] );
		$this->assertStringContainsString( 'font-family:Hiragino Mincho ProN,"游明朝",serif;', $assets['styles'] );
	}

	/**
	 * テスト用のスタイルを iframe の収集対象へ追加する.
	 */
	public function enqueue_test_editor_style() {
		wp_enqueue_style( 'vk-font-selector-test' );
	}

	/**
	 * 公開画面では編集画面用の動的 CSS を追加しないこと.
	 */
	public function test_dynamic_editor_css_on_front() {
		update_option( 'vk_font_selector', array( 'title' => 'mincho' ) );
		set_current_screen( 'front' );
		$GLOBALS['vk_font_selector_editor_style'] = 'vk-font-selector-test';

		wp_register_style( 'vk-font-selector-test', false, array(), Vk_Font_Selector::$version );
		Vk_Font_Selector::dynamic_editor_css();

		$this->assertFalse( wp_styles()->get_data( 'vk-font-selector-test', 'after' ) );
	}

	/**
	 * テストの後処理.
	 */
	public function tearDown(): void {
		remove_action( 'enqueue_block_assets', array( $this, 'enqueue_test_editor_style' ), 10 );
		wp_dequeue_style( 'vk-font-selector-test' );
		wp_deregister_style( 'vk-font-selector-test' );

		if ( $this->original_font_option['exists'] ) {
			update_option( 'vk_font_selector', $this->original_font_option['value'] );
		} else {
			delete_option( 'vk_font_selector' );
		}

		parent::tearDown();

		foreach ( $this->original_globals as $global_name => $original_global ) {
			if ( $original_global['exists'] ) {
				$GLOBALS[ $global_name ] = $original_global['value'];
			} else {
				unset( $GLOBALS[ $global_name ] );
			}
		}
	}

}
