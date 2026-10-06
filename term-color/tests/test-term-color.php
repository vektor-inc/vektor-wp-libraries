<?php
/**
 * Class VkTermColorTest
 *
 * @package vektor-inc/vektor-wp-libraries
 */

if ( ! class_exists( 'Vk_term_color' ) ) {
	require_once __DIR__ . '/../package/class.term-color.php';
}

class VkTermColorTest extends WP_UnitTestCase {

	/**
	 * init 後の読み込みでもサニタイズコールバックが登録されること.
	 *
	 * @return void
	 */
	public function test_term_meta_color_registers_sanitize_callback() {
		// init 発火後にクラスがインスタンス化される状況を再現する.
		new Vk_term_color();

		$this->assertTrue(
			has_filter( 'sanitize_term_meta_term_color' ),
			'タームメタのサニタイズコールバックが登録されていません.'
		);
	}

	/**
	 * タームメタ保存時にカラー値がサニタイズされること.
	 *
	 * @return void
	 */
	public function test_term_color_meta_save_sanitizes_invalid_value() {
		// 各テストを単独実行してもサニタイズコールバックが有効になるよう登録する.
		new Vk_term_color();
		$term_id = self::factory()->term->create();

		// 不正なカラー値は空文字として保存される.
		update_term_meta( $term_id, 'term_color', 'notacolor000' );
		$this->assertSame( '', get_term_meta( $term_id, 'term_color', true ) );

		// 正しいカラー値は先頭の # を除いて保存される.
		update_term_meta( $term_id, 'term_color', '#ff0000' );
		$this->assertSame( 'ff0000', get_term_meta( $term_id, 'term_color', true ) );
	}

	/**
	 * 3桁・6桁の16進数カラーだけを許可すること.
	 *
	 * @return void
	 */
	public function test_sanitize_hex() {
		$test_cases = array(
			array(
				'test_condition_name' => '6桁のカラー値は先頭の # を除いて返す',
				'color'               => '#ff0000',
				'expected'            => 'ff0000',
			),
			array(
				'test_condition_name' => '3桁のカラー値はそのまま返す',
				'color'               => 'abc',
				'expected'            => 'abc',
			),
			array(
				'test_condition_name' => '16進数カラーではない値は空文字を返す',
				'color'               => 'notacolor000',
				'expected'            => '',
			),
		);

		foreach ( $test_cases as $test_case ) {
			$this->assertSame(
				$test_case['expected'],
				Vk_term_color::sanitize_hex( $test_case['color'] ),
				$test_case['test_condition_name']
			);
		}
	}
}
