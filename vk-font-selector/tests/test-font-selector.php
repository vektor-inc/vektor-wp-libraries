<?php
/**
 * Class VK_Font_Selector_Test
 *
 * @package vektor-inc/VK_Font_Selector
 */

class VK_Font_Selector_Test extends WP_UnitTestCase {

	/**
	 * Get font url
	 */
	public function test_get_web_fonts_url() {

		// テスト配列 .
		$test_array = array(
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

}
