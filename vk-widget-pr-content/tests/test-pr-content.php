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

	function test_btn_text_style() {
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
				'btn_color'  => '#f00',
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
		print 'PR Content btn text style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_text_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
	}

	function test_btn_bg_style() {
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
				'btn_color'  => '#f00',
				'text_color' => null,
				'correct'    => 'background-color:#f00;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => 'background:transparent;transition: .3s;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => '#f00',
				'text_color' => null,
				'correct'    => 'background:transparent;transition: .3s;',
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content btn bg style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_bg_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );
		}
	}

	function test_btn_border_style() {
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
			// 塗りボタンの線はボタン色を元にした自動変換が適用される
			// array(
			// 	'btn_type'   => 'full',
			// 	'btn_color'  => '#f00',
			// 	'text_color' => null,
			// 	'correct'    => '',
			// ),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => 'border-color:#fff;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => '#f00',
				'correct'    => 'border-color:#f00;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => '#00f',
				'text_color' => '#f00',
				'correct'    => 'border-color:#f00;',
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content btn border style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_border_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
	}


	function test_btn_text_hover_style() {
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
				'btn_color'  => '#f00',
				'text_color' => null,
				'correct'    => '',
			),
			array(
				'btn_type'   => 'full',
				'btn_color'  => '#f00',
				'text_color' => '#f00',
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
				'correct'    => 'color:#fff;',
			),
			// ボタンカラーに白を指定されたら文字色を白に
			// この処理は重要度が低いので実装してない
			// array(
			// 	'btn_type'   => 'ghost',
			// 	'btn_color'  => '#fff',
			// 	'text_color' => '#f00',
			// 	'correct'    => 'color:#000;',
			// ),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content btn text hover style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_text_hover_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
	}

	function test_btn_bg_hover_style() {
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
			// ボタンカラーをもとに自動計算で明るくなる
			// array(
			// 	'btn_type'   => 'full',
			// 	'btn_color'  => '#f00',
			// 	'text_color' => null,
			// 	'correct'    => 'background-color:#f00;',
			// ),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => null,
				'text_color' => null,
				'correct'    => 'background-color:#337ab7;',
			),
			array(
				'btn_type'   => 'ghost',
				'btn_color'  => '#f00',
				'text_color' => null,
				'correct'    => 'background-color:#f00;',
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content btn hover bg style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_bg_hover_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );
		}
	}

	function test_btn_border_hover_style() {
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
			// 塗りボタンのホバーの線はボタン色を適用される
			array(
				'btn_type'   => 'full',
				'btn_color'  => '#f00',
				'text_color' => null,
				'correct'    => 'border-color:#f00;',
			),
			array(
				'btn_type'  => 'ghost',
				'btn_color' => null,
				'correct'   => 'border-color:#337ab7;',
			),
			array(
				'btn_type'  => 'ghost',
				'btn_color' => '#f00',
				'correct'   => 'border-color:#f00;',
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'PR Content btn border style' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $test_array as $key => $value ) {
			$result = VK_Widget_Pr_Content::btn_border_hover_style( $value );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
	}

}
