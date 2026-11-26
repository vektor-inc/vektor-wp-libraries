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
	 * テストの前の REST API サーバー（テストの中で作り直すため、元に戻せるように控える）.
	 *
	 * @var WP_REST_Server|null
	 */
	private $rest_server_before_test = null;

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
		$this->rest_server_before_test     = isset( $GLOBALS['wp_rest_server'] ) ? $GLOBALS['wp_rest_server'] : null;
		$this->vk_page_header              = new Vk_Page_Header();
		$this->vk_page_header->register_page_header_meta();
	}

	/**
	 * テストで登録した投稿メタの登録を解除する（WP_UnitTestCase は登録済みの投稿メタの一覧を元に戻さないため）.
	 * あわせて、テストの中で作り直した REST API サーバーを元に戻す.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		if ( ! $this->meta_registered_before_test ) {
			unregister_post_meta( 'page', 'vk_page_header_image' );
			unregister_post_meta( 'page', 'vk_page_header_image_sp' );
		}
		$GLOBALS['wp_rest_server'] = $this->rest_server_before_test;
		parent::tearDown();
	}

	/**
	 * ページヘッダーに出す画像の URL の決め方をテストする（固定ページ）.
	 *
	 * 画像を取り出せない値（古い形式の画像の URL・削除済みの画像の ID）は未設定と同じに扱い、
	 * 今のページ → 近い親 → 遠い祖先の順に最初に取り出せた画像、どこにも無ければカスタマイザーの画像になること.
	 *
	 * @return void
	 */
	public function test_header_image_url() {
		$default_url    = 'https://example.com/default.jpg';
		$default_url_sp = 'https://example.com/default-sp.jpg';
		$legacy_url     = 'https://example.com/wp-content/uploads/legacy.jpg';
		$upload_url     = wp_get_upload_dir()['baseurl'];

		// ページに設定する画像（添付ファイル）を用意する.
		$image_ids = array();
		foreach ( array( 'self', 'parent', 'grandparent', 'self-sp', 'parent-sp', 'deleted' ) as $image_name ) {
			$image_ids[ $image_name ] = self::factory()->attachment->create_object(
				array(
					'file'           => $image_name . '.jpg',
					'post_mime_type' => 'image/jpeg',
					'post_parent'    => 0,
				)
			);
		}

		// メディアライブラリから画像を削除したあとに残る、画像の ID を用意する.
		wp_delete_attachment( $image_ids['deleted'], true );

		$options = array(
			'image_basic'    => $default_url,
			'image_basic_sp' => $default_url_sp,
		);

		$options_without_image = array(
			'image_basic'    => '',
			'image_basic_sp' => '',
		);

		$test_cases = array(
			array(
				'test_condition_name' => '値なし・親なし => カスタマイザーの画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array(),
				),
				'expected'            => $default_url,
			),
			array(
				'test_condition_name' => '値なし・親が有効な画像の ID => 親の画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => array( 'vk_page_header_image' => $image_ids['parent'] ),
					'self'        => array(),
				),
				'expected'            => $upload_url . '/parent.jpg',
			),
			array(
				'test_condition_name' => '有効な画像の ID => 自分の画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array( 'vk_page_header_image' => $image_ids['self'] ),
				),
				'expected'            => $upload_url . '/self.jpg',
			),
			array(
				'test_condition_name' => '有効な画像の ID・親も有効な画像の ID => 自分の画像（自分が優先）',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => array( 'vk_page_header_image' => $image_ids['parent'] ),
					'self'        => array( 'vk_page_header_image' => $image_ids['self'] ),
				),
				'expected'            => $upload_url . '/self.jpg',
			),
			array(
				'test_condition_name' => '値なし・親も祖父も有効な画像の ID => 親の画像（近い親が優先）',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => array( 'vk_page_header_image' => $image_ids['grandparent'] ),
					'parent'      => array( 'vk_page_header_image' => $image_ids['parent'] ),
					'self'        => array(),
				),
				'expected'            => $upload_url . '/parent.jpg',
			),
			array(
				'test_condition_name' => '古い形式の画像の URL のみ・親なし => カスタマイザーの画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array( 'vk_page_header_image' => $legacy_url ),
				),
				'expected'            => $default_url,
			),
			array(
				'test_condition_name' => '削除済みの画像の ID のみ・親なし => カスタマイザーの画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array( 'vk_page_header_image' => $image_ids['deleted'] ),
				),
				'expected'            => $default_url,
			),
			array(
				'test_condition_name' => '古い形式の画像の URL・親が有効な画像の ID => 親の画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => array( 'vk_page_header_image' => $image_ids['parent'] ),
					'self'        => array( 'vk_page_header_image' => $legacy_url ),
				),
				'expected'            => $upload_url . '/parent.jpg',
			),
			array(
				'test_condition_name' => '削除済みの画像の ID・親が有効な画像の ID => 親の画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => array( 'vk_page_header_image' => $image_ids['parent'] ),
					'self'        => array( 'vk_page_header_image' => $image_ids['deleted'] ),
				),
				'expected'            => $upload_url . '/parent.jpg',
			),
			array(
				'test_condition_name' => '値なし・親が古い形式の画像の URL・祖父が有効な画像の ID => 祖父の画像',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => array( 'vk_page_header_image' => $image_ids['grandparent'] ),
					'parent'      => array( 'vk_page_header_image' => $legacy_url ),
					'self'        => array(),
				),
				'expected'            => $upload_url . '/grandparent.jpg',
			),
			array(
				'test_condition_name' => 'カスタマイザーの画像が空・古い形式の画像の URL のみ => 空文字',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options_without_image,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array( 'vk_page_header_image' => $legacy_url ),
				),
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'カスタマイザーの画像が空・値なし・親なし => 空文字',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options_without_image,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array(),
				),
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'モバイル用: モバイル用が有効な画像の ID => モバイル用の画像',
				'conditions'          => array(
					'size'        => 'sp',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array( 'vk_page_header_image_sp' => $image_ids['self-sp'] ),
				),
				'expected'            => $upload_url . '/self-sp.jpg',
			),
			array(
				'test_condition_name' => 'モバイル用: モバイル用が古い形式の画像の URL・PC 用は有効な画像の ID => モバイル用のカスタマイザーの画像（PC 用の画像は使わない）',
				'conditions'          => array(
					'size'        => 'sp',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array(
						'vk_page_header_image'    => $image_ids['self'],
						'vk_page_header_image_sp' => $legacy_url,
					),
				),
				'expected'            => $default_url_sp,
			),
			array(
				'test_condition_name' => 'モバイル用: モバイル用は値なし・親のモバイル用が有効な画像の ID => 親のモバイル用の画像',
				'conditions'          => array(
					'size'        => 'sp',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => array( 'vk_page_header_image_sp' => $image_ids['parent-sp'] ),
					'self'        => array(),
				),
				'expected'            => $upload_url . '/parent-sp.jpg',
			),
			array(
				'test_condition_name' => 'PC 用: PC 用が古い形式の画像の URL・モバイル用は有効な画像の ID => PC 用のカスタマイザーの画像（モバイル用の画像は使わない）',
				'conditions'          => array(
					'size'        => '',
					'options'     => $options,
					'grandparent' => null,
					'parent'      => null,
					'self'        => array(
						'vk_page_header_image'    => $legacy_url,
						'vk_page_header_image_sp' => $image_ids['self-sp'],
					),
				),
				'expected'            => $default_url,
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];
			update_option( 'vk_page_header', $conditions['options'] );

			// 祖父 → 親 → 今のページの順に作り、それぞれのページヘッダー画像の値を保存する.
			$parent_id = 0;
			foreach ( array( 'grandparent', 'parent', 'self' ) as $page_role ) {
				if ( null === $conditions[ $page_role ] ) {
					continue;
				}
				$page_id = self::factory()->post->create(
					array(
						'post_type'   => 'page',
						'post_parent' => $parent_id,
					)
				);
				foreach ( $conditions[ $page_role ] as $meta_key => $meta_value ) {
					update_post_meta( $page_id, $meta_key, $meta_value );
				}
				$parent_id = $page_id;
			}

			// 今のページを表示した状態で、ページヘッダーの画像の URL を決める.
			$this->go_to( get_permalink( $page_id ) );
			$actual = Vk_Page_Header::header_image_url( $conditions['size'] );

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
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

	/**
	 * 固定ページを REST API で保存するとき、保存済みの値が型（整数）に合わないキーに送られた null が取り除かれ、
	 * 保存できることをテストする.
	 *
	 * ブロックエディターと同じく、編集用に読み出した meta を（ケースによって一部のキーを変えて）そのまま送り返す.
	 *
	 * @return void
	 */
	public function test_remove_null_image_meta_with_invalid_stored_value() {
		global $wpdb;

		$image_url = 'https://example.com/wp-content/uploads/header.jpg';

		// 保存する人・権限のない人と、選び直す画像を用意する.
		$admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'header-new.jpg',
				'post_mime_type' => 'image/jpeg',
				'post_parent'    => 0,
			)
		);

		// 対象外であることの確認に使う投稿メタを登録する（登録は WP_UnitTestCase の tearDown がすべて解除する）.
		register_post_meta(
			'page',
			'vk_test_other_image',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);
		register_post_meta(
			'post',
			'vk_page_header_image',
			array(
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// 投稿メタの登録を反映させるため、REST API サーバーを作り直す（tearDown で元に戻す）.
		$GLOBALS['wp_rest_server'] = null;

		$test_cases = array(
			array(
				'test_condition_name' => '保存済みが画像の URL で、読み出した meta をそのまま送る（ブロックエディターでほかの設定を変えて保存） => 200・URL が残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image' => $image_url ),
				),
			),
			array(
				'test_condition_name' => 'モバイル用だけ保存済みが画像の URL => 200・モバイル用の URL が残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image_sp' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image_sp' => $image_url ),
				),
			),
			array(
				'test_condition_name' => 'PC 用が画像の URL・モバイル用が画像の ID => 200・URL も ID も残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array(
						'vk_page_header_image'    => $image_url,
						'vk_page_header_image_sp' => (string) $attachment_id,
					),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array(
						'vk_page_header_image'    => $image_url,
						'vk_page_header_image_sp' => (string) $attachment_id,
					),
				),
			),
			array(
				'test_condition_name' => 'POST で送る（PUT と同じく更新になる） => 200・URL が残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'POST',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array(
						'vk_page_header_image'    => $image_url,
						'vk_page_header_image_sp' => $image_url,
					),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array(
						'vk_page_header_image'    => $image_url,
						'vk_page_header_image_sp' => $image_url,
					),
				),
			),
			array(
				'test_condition_name' => '保存済みが URL 以外の整数として読めない値（abc） => 200・値が残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => 'abc' ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image' => 'abc' ),
				),
			),
			array(
				'test_condition_name' => '保存済みが画像の URL で、画像の ID を送る（画像を選び直した） => 200・画像の ID に置き換わる',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array( 'vk_page_header_image' => $attachment_id ),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image' => (string) $attachment_id ),
				),
			),
			array(
				'test_condition_name' => '保存済みが画像の URL で、0 を送る（選び直した画像を削除した） => 200・URL は残らず "0" に置き換わる（仕様）',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array( 'vk_page_header_image' => 0 ),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image' => '0' ),
				),
			),
			array(
				'test_condition_name' => '保存済みが有効な画像の ID で、null を送る => 200・従来どおり削除されて既定値の 0',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => (string) $attachment_id ),
					'send_meta'   => array( 'vk_page_header_image' => null ),
				),
				'expected'            => array(
					'status'     => 200,
					'error_code' => null,
					'meta'       => array( 'vk_page_header_image' => 0 ),
				),
			),
			array(
				'test_condition_name' => '陽性対照: フックを外して、保存済みが画像の URL で読み出した meta をそのまま送る => 500（rest_invalid_stored_value）・URL は残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => true,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 500,
					'error_code' => 'rest_invalid_stored_value',
					'meta'       => array( 'vk_page_header_image' => $image_url ),
				),
			),
			array(
				'test_condition_name' => '権限のない人（購読者）が送る => 403・URL が残る',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'subscriber',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 403,
					'error_code' => 'rest_cannot_edit',
					'meta'       => array( 'vk_page_header_image' => $image_url ),
				),
			),
			array(
				'test_condition_name' => '対象外: 別のメタ（整数で登録した vk_test_other_image）に画像の URL が保存済み => 500 のまま',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_test_other_image' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 500,
					'error_code' => 'rest_invalid_stored_value',
					'meta'       => array( 'vk_test_other_image' => $image_url ),
				),
			),
			array(
				'test_condition_name' => '対象外: 別の投稿タイプ（投稿）の同じキーに画像の URL が保存済み => 500 のまま',
				'conditions'          => array(
					'post_type'   => 'post',
					'route_base'  => '/wp/v2/posts',
					'is_create'   => false,
					'method'      => 'PUT',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array( 'vk_page_header_image' => $image_url ),
					'send_meta'   => array(),
				),
				'expected'            => array(
					'status'     => 500,
					'error_code' => 'rest_invalid_stored_value',
					'meta'       => array( 'vk_page_header_image' => $image_url ),
				),
			),
			array(
				'test_condition_name' => '新規作成（POST /wp/v2/pages）で null を送る => 201・既定値の 0',
				'conditions'          => array(
					'post_type'   => 'page',
					'route_base'  => '/wp/v2/pages',
					'is_create'   => true,
					'method'      => 'POST',
					'role'        => 'administrator',
					'remove_hook' => false,
					'stored_meta' => array(),
					'send_meta'   => array(
						'vk_page_header_image'    => null,
						'vk_page_header_image_sp' => null,
					),
				),
				'expected'            => array(
					'status'     => 201,
					'error_code' => null,
					'meta'       => array(
						'vk_page_header_image'    => 0,
						'vk_page_header_image_sp' => 0,
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];
			wp_set_current_user( $admin_id );

			if ( $conditions['is_create'] ) {
				// 新規作成では、保存済みの値が無いので、送る meta をそのまま使う.
				$route     = $conditions['route_base'];
				$send_meta = $conditions['send_meta'];
			} else {
				$post_id = self::factory()->post->create( array( 'post_type' => $conditions['post_type'] ) );
				$route   = $conditions['route_base'] . '/' . $post_id;

				// 古い製品が保存した値を、今の整え方を通さずに入れておく（当時は投稿メタを登録していなかった）.
				foreach ( $conditions['stored_meta'] as $meta_key => $meta_value ) {
					$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- 整え方を通さずに古い値を入れるため.
						$wpdb->postmeta,
						array(
							'post_id'    => $post_id,
							'meta_key'   => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- テストデータの用意.
							'meta_value' => $meta_value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- テストデータの用意.
						)
					);
				}
				wp_cache_delete( $post_id, 'post_meta' );

				// ブロックエディターと同じく、編集用に読み出した meta を元にして、ケースで指定したキーだけ変える.
				$read_request = new WP_REST_Request( 'GET', $route );
				$read_request->set_param( 'context', 'edit' );
				$read_data = rest_do_request( $read_request )->get_data();
				$send_meta = array_merge( $read_data['meta'], $conditions['send_meta'] );
			}

			// 陽性対照では、このフックを外して WordPress 本体だけの動きにする.
			if ( $conditions['remove_hook'] ) {
				remove_filter( 'rest_pre_insert_page', array( 'Vk_Page_Header', 'remove_null_image_meta_with_invalid_stored_value' ), 10 );
			}

			// ブロックエディターと同じく、JSON で保存を送る.
			wp_set_current_user( 'subscriber' === $conditions['role'] ? $subscriber_id : $admin_id );
			$request = new WP_REST_Request( $conditions['method'], $route );
			$request->set_header( 'Content-Type', 'application/json' );
			$request->set_body( wp_json_encode( array( 'meta' => $send_meta ) ) );
			$response = rest_do_request( $request );

			if ( $conditions['remove_hook'] ) {
				add_filter( 'rest_pre_insert_page', array( 'Vk_Page_Header', 'remove_null_image_meta_with_invalid_stored_value' ), 10, 2 );
			}

			// 返ってきた状態と、保存後の値を確かめる.
			$saved_post_id = $conditions['is_create'] ? $response->get_data()['id'] : $post_id;
			$actual_meta   = array();
			foreach ( array_keys( $case['expected']['meta'] ) as $meta_key ) {
				$actual_meta[ $meta_key ] = get_post_meta( $saved_post_id, $meta_key, true );
			}
			$actual = array(
				'status'     => $response->get_status(),
				'error_code' => $response->is_error() ? $response->as_error()->get_error_code() : null,
				'meta'       => $actual_meta,
			);

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}
}
