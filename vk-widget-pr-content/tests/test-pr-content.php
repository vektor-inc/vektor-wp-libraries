<?php
/**
 * Class SampleTest
 *
 * @package Lightning_Media_Posts
 */

/**
 * Sample test case.
 */
class PrContentTest extends WP_UnitTestCase {

	function test_btn_text_color_style() {
		$test_array = array(
			// オプション値自体が保存されていない
			array(
				'btn_type'   => null,
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => '',
			),
			array(
				'btn_type'   => 'full',
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => '',
			),
			array(
				'btn_type'   => 'full',
				'btn_color'  => '#r00',
				'text_color' => null,
				'correct'    => '',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => 'color:#fff;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => '#f00',
				'correct'    => 'color:#f00;',
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_text_color_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
	}

}
