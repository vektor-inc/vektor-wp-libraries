<?php
/**
 * Class VkGtmSanitizeIdTest
 *
 * @package vektor-inc/vektor-wp-libraries
 */

if ( ! function_exists( 'vk_gtm_sanitize_id' ) ) {
	require_once __DIR__ . '/../package/class-vk-google-tag-manager.php';
}

/**
 * vk_gtm_sanitize_id() / vk_gtm_head() / vk_gtm_body() のテスト.
 */
class VkGtmSanitizeIdTest extends WP_UnitTestCase {

	/**
	 * テストごとに option を削除する.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		delete_option( 'vk_google_tag_manager_related_options' );
		parent::tearDown();
	}

	/**
	 * ID の許可リストをテストする.
	 *
	 * @return void
	 */
	public function test_vk_gtm_sanitize_id() {
		$test_array = array(
			// 既存の保存値（レガシー）は変わらない.
			array( 'ABC123', 'ABC123' ),
			array( 'abc123', 'abc123' ),
			array( ' ABC123 ', 'ABC123' ),
			// 「GTM-」付きで保存されていた値は接頭辞を取り除く.
			array( 'GTM-ABC123', 'ABC123' ),
			array( '', '' ),
			array( null, '' ),
			// 変更前は通っていた不正な値は空になる.
			array( "ABC123');alert(1);//", '' ),
			array( 'ABC123"><script>', '' ),
			array( 'ABC 123', '' ),
			array( 'ABC-123', '' ),
			array( array( 'ABC123' ), '' ),
		);
		foreach ( $test_array as $value ) {
			$this->assertSame( $value[1], vk_gtm_sanitize_id( $value[0] ) );
		}
	}

	/**
	 * head / body の出力をテストする（不正な ID では何も出力しない）.
	 *
	 * @return void
	 */
	public function test_vk_gtm_output() {
		$test_array = array(
			array( 'ABC123', true ),
			array( 'GTM-ABC123', true ),
			array( "ABC123');alert(1);//", false ),
		);
		foreach ( $test_array as $value ) {
			update_option( 'vk_google_tag_manager_related_options', array( 'gtm_id' => $value[0] ) );
			ob_start();
			vk_gtm_head();
			$head = ob_get_clean();
			ob_start();
			vk_gtm_body();
			$body = ob_get_clean();
			if ( $value[1] ) {
				// 「GTM-」が二重にならず 1 回だけ出力される.
				$this->assertStringContainsString( "'GTM-ABC123'", $head );
				$this->assertStringContainsString( 'id=GTM-ABC123"', $body );
			} else {
				$this->assertSame( '', $head );
				$this->assertSame( '', $body );
			}
		}
	}
}
