<?php
/**
 * Class VkPageHeaderTest
 *
 * @package vektor-inc/vektor-wp-libraries
 */

// 投稿の保存時に呼ばれる save_custom_fields() がカスタムフィールドビルダーを使うため、同梱先と同じく先に読み込む.
if ( ! class_exists( 'VK_Custom_Field_Builder' ) ) {
	require_once __DIR__ . '/../../custom-field-builder/package/custom-field-builder.php';
}

if ( ! class_exists( 'Vk_Page_Header' ) ) {
	require_once __DIR__ . '/../package/class-vk-page-header.php';
}

/**
 * ページヘッダー画像の投稿メタ（vk_page_header_image / vk_page_header_image_sp）のテスト.
 */
class VkPageHeaderTest extends WP_UnitTestCase {

	/**
	 * テスト対象のインスタンス.
	 *
	 * @var Vk_Page_Header
	 */
	private $vk_page_header;

	/**
	 * テストの前から投稿メタが登録済みだったか.
	 *
	 * @var bool
	 */
	private $meta_registered_before_test = false;

	/**
	 * ページヘッダー画像の投稿メタを登録する.
	 *
	 * 本来は init で登録されるが、テストファイルは init の後に読み込まれるため、ここで登録する.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$this->meta_registered_before_test = registered_meta_key_exists( 'post', 'vk_page_header_image', 'page' );
		$this->vk_page_header              = new Vk_Page_Header();
		$this->vk_page_header->register_page_header_meta();
	}

	/**
	 * テストで登録した投稿メタの登録を解除する（WP_UnitTestCase は登録済みの投稿メタの一覧を元に戻さないため）.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( ! $this->meta_registered_before_test ) {
			unregister_post_meta( 'page', 'vk_page_header_image' );
			unregister_post_meta( 'page', 'vk_page_header_image_sp' );
		}
		parent::tearDown();
	}

	/**
	 * 保存する値の整え方をテストする（添付 ID は整数に、http / https の画像の URL はそのまま残す）.
	 *
	 * @return void
	 */
	public function test_sanitize_page_header_image_meta() {
		$test_cases = array(
			array(
				'test_condition_name' => '添付 ID の文字列 12 => 12',
				'value'               => '12',
				'expected'            => 12,
			),
			array(
				'test_condition_name' => '添付 ID の整数 12 => 12',
				'value'               => 12,
				'expected'            => 12,
			),
			array(
				'test_condition_name' => 'https の画像の URL => そのまま',
				'value'               => 'https://example.com/wp-content/uploads/header.jpg',
				'expected'            => 'https://example.com/wp-content/uploads/header.jpg',
			),
			array(
				'test_condition_name' => 'http の画像の URL => そのまま',
				'value'               => 'http://example.com/wp-content/uploads/header.jpg',
				'expected'            => 'http://example.com/wp-content/uploads/header.jpg',
			),
			array(
				'test_condition_name' => '前後に空白がある画像の URL => 空白を除いた URL',
				'value'               => '  https://example.com/wp-content/uploads/header.jpg  ',
				'expected'            => 'https://example.com/wp-content/uploads/header.jpg',
			),
			array(
				'test_condition_name' => '空文字 => 0',
				'value'               => '',
				'expected'            => 0,
			),
			array(
				'test_condition_name' => 'null => 0',
				'value'               => null,
				'expected'            => 0,
			),
			array(
				'test_condition_name' => 'javascript: で始まる値 => 0',
				'value'               => 'javascript:alert(1)',
				'expected'            => 0,
			),
			array(
				'test_condition_name' => 'http / https 以外のスキームの URL => 0',
				'value'               => 'ftp://example.com/wp-content/uploads/header.jpg',
				'expected'            => 0,
			),
			array(
				'test_condition_name' => '制御文字（改行）を含む URL => 0',
				'value'               => "https://example.com/wp-content/uploads/hea\nder.jpg",
				'expected'            => 0,
			),
			array(
				'test_condition_name' => '末尾に NUL がある URL（trim() で消える制御文字） => 0',
				'value'               => "https://example.com/wp-content/uploads/header.jpg\x00",
				'expected'            => 0,
			),
			array(
				'test_condition_name' => '末尾に改行がある URL（trim() で消える制御文字） => 0',
				'value'               => "https://example.com/wp-content/uploads/header.jpg\n",
				'expected'            => 0,
			),
			array(
				'test_condition_name' => 'ホスト名の無い URL => 0',
				'value'               => 'https:/wp-content/uploads/header.jpg',
				'expected'            => 0,
			),
			array(
				'test_condition_name' => '整えるとホスト名が消える URL => 0',
				'value'               => 'https://<>/header.jpg',
				'expected'            => 0,
			),
			array(
				'test_condition_name' => '空白を含む URL => 空白を %20 に変えた URL',
				'value'               => 'https://example.com/wp-content/uploads/my header.jpg',
				'expected'            => 'https://example.com/wp-content/uploads/my%20header.jpg',
			),
		);

		foreach ( $test_cases as $case ) {
			$actual = Vk_Page_Header::sanitize_page_header_image_meta( $case['value'] );
			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 登録した投稿メタを保存したときに、画像の URL が 0 に変わらないことをテストする.
	 *
	 * クラシックエディターの「更新」では、WordPress 本体の「カスタムフィールド」欄が値を送り直し、
	 * edit_post() → update_meta() → sanitize_meta() の順に、登録した整え方がかかる.
	 *
	 * @return void
	 */
	public function test_register_page_header_meta() {
		// 「カスタムフィールド」欄の保存処理 update_meta() を使うため、管理画面の関数を読み込む.
		require_once ABSPATH . 'wp-admin/includes/post.php';

		$image_url = 'https://example.com/wp-content/uploads/header.jpg';

		$test_cases = array(
			array(
				'test_condition_name' => 'update_post_meta() で添付 ID 12 を保存 => 12',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image',
					'stored_value'  => null,
					'save_function' => 'update_post_meta',
					'save_value'    => '12',
				),
				'expected'            => '12',
			),
			array(
				'test_condition_name' => 'update_post_meta() で画像の URL を保存 => URL のまま',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image',
					'stored_value'  => null,
					'save_function' => 'update_post_meta',
					'save_value'    => $image_url,
				),
				'expected'            => $image_url,
			),
			array(
				'test_condition_name' => 'update_post_meta() でモバイル用に画像の URL を保存 => URL のまま',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image_sp',
					'stored_value'  => null,
					'save_function' => 'update_post_meta',
					'save_value'    => $image_url,
				),
				'expected'            => $image_url,
			),
			array(
				'test_condition_name' => 'update_post_meta() で javascript: で始まる値を保存 => 0',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image',
					'stored_value'  => null,
					'save_function' => 'update_post_meta',
					'save_value'    => 'javascript:alert(1)',
				),
				'expected'            => '0',
			),
			array(
				'test_condition_name' => '画像の URL が保存済みで、「カスタムフィールド」欄から同じ値が送り直される（update_meta()） => URL のまま',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image',
					'stored_value'  => $image_url,
					'save_function' => 'update_meta',
					'save_value'    => $image_url,
				),
				'expected'            => $image_url,
			),
			array(
				'test_condition_name' => '添付 ID 12 が保存済みで、「カスタムフィールド」欄から同じ値が送り直される（update_meta()） => 12',
				'conditions'          => array(
					'meta_name'     => 'vk_page_header_image',
					'stored_value'  => '12',
					'save_function' => 'update_meta',
					'save_value'    => '12',
				),
				'expected'            => '12',
			),
		);

		foreach ( $test_cases as $case ) {
			$post_id    = self::factory()->post->create( array( 'post_type' => 'page' ) );
			$conditions = $case['conditions'];

			if ( 'update_meta' === $conditions['save_function'] ) {
				// 古い製品が保存した値を、今の整え方を通さずに入れておく（当時は投稿メタを登録していなかった）.
				unregister_post_meta( 'page', $conditions['meta_name'] );
				$meta_id = add_post_meta( $post_id, $conditions['meta_name'], $conditions['stored_value'], true );
				$this->vk_page_header->register_page_header_meta();

				// クラシックエディターの「更新」で、「カスタムフィールド」欄が同じ値を送り直す.
				update_meta( $meta_id, $conditions['meta_name'], $conditions['save_value'] );
			} else {
				update_post_meta( $post_id, $conditions['meta_name'], $conditions['save_value'] );
			}

			$this->assertSame( $case['expected'], get_post_meta( $post_id, $conditions['meta_name'], true ), $case['test_condition_name'] );
		}
	}
}
