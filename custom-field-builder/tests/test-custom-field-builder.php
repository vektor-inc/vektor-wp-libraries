<?php
/**
 * Class VK_Custom_Field_Builder_Test
 *
 * @package vektor-inc/VK_Custom_Field_Builder
 */

if ( ! class_exists( 'VK_Custom_Field_Builder' ) ) {
	require_once __DIR__ . '/../package/custom-field-builder.php';
}

if ( ! class_exists( 'VK_CFB_Injection_Probe' ) ) {
	/**
	 * 保存値からオブジェクトが生成されたことを検出するためのクラス.
	 *
	 * unserialize() がこのクラスをインスタンス化すると __wakeup() が呼ばれる.
	 * $woken が true になった場合、保存値がクラスのマジックメソッドに到達したことを意味する.
	 */
	class VK_CFB_Injection_Probe {

		/**
		 * __wakeup() が呼ばれたかどうか.
		 *
		 * @var bool
		 */
		public static $woken = false;

		/**
		 * unserialize() によってインスタンス化されたことを記録する.
		 *
		 * @return void
		 */
		public function __wakeup() {
			self::$woken = true;
		}
	}
}

/**
 * Class VK_Custom_Field_Builder_Test
 */
class VK_Custom_Field_Builder_Test extends WP_UnitTestCase {

	/**
	 * テスト用のチェックボックス項目の定義.
	 *
	 * @var string
	 */
	const FIELD_KEY = 'cfb_test_employment';

	/**
	 * 退避した $post.
	 *
	 * @var mixed
	 */
	private $original_post;

	/**
	 * 退避した $custom_field_builder_url.
	 *
	 * @var mixed
	 */
	private $original_custom_field_builder_url;

	/**
	 * 退避した $hook_suffix.
	 *
	 * @var mixed
	 */
	private $original_hook_suffix;

	/**
	 * テストの前処理.
	 *
	 * テストが書き換えるグローバルを退避する.
	 */
	public function setUp(): void {
		parent::setUp();
		global $post, $custom_field_builder_url;
		VK_CFB_Injection_Probe::$woken           = false;
		$this->original_post                     = isset( $post ) ? $post : null;
		$this->original_custom_field_builder_url = isset( $custom_field_builder_url ) ? $custom_field_builder_url : null;
		$this->original_hook_suffix              = isset( $GLOBALS['hook_suffix'] ) ? $GLOBALS['hook_suffix'] : null;
	}

	/**
	 * テストの後処理.
	 *
	 * テストが書き換えたグローバルを元に戻す.
	 */
	public function tearDown(): void {
		global $post, $custom_field_builder_url;
		$_POST                    = array();
		$post                     = $this->original_post;
		$custom_field_builder_url = $this->original_custom_field_builder_url;
		$GLOBALS['hook_suffix']   = $this->original_hook_suffix;
		// print_script() で登録したスクリプト・スタイルを、次のテストに残さない.
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
		wp_set_current_user( 0 );
		parent::tearDown();
	}

	/**
	 * テスト用のチェックボックス項目の定義を返す.
	 *
	 * @return array form_table() に渡すカスタムフィールドの定義.
	 */
	private function checkbox_fields_array() {
		return array(
			self::FIELD_KEY => array(
				'label'       => 'Employment',
				'type'        => 'checkbox',
				'description' => '',
				'options'     => array(
					'FULL_TIME' => 'Full time',
					'PART_TIME' => 'Part time',
				),
			),
		);
	}

	/**
	 * プローブ用クラスをシリアライズした保存値を返す.
	 *
	 * @return string シリアライズされたプローブ用クラス.
	 */
	private function probe_payload() {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- 復元時の挙動を検証するため、保存値を意図的に組み立てている.
		return serialize( new VK_CFB_Injection_Probe() );
	}

	/**
	 * 自分自身を指す配列の保存値を返す.
	 *
	 * 復元すると 0 番目の要素が配列そのものを指す形になる.
	 *
	 * @return string シリアライズされた、自分自身を指す配列.
	 */
	private function self_referencing_payload() {
		return 'a:1:{i:0;R:1;}';
	}

	/**
	 * 自分自身を指す配列を復元して返す.
	 *
	 * @return array 0 番目の要素が自分自身を指している配列.
	 */
	private function self_referencing_array() {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize -- allowed_classes => false でオブジェクトは生成されない。検証用の保存値を意図的に復元している.
		return @unserialize( $this->self_referencing_payload(), array( 'allowed_classes' => false ) );
	}

	/**
	 * 指定された階層数だけ配列で包んだ保存値を返す.
	 *
	 * 一番内側には整数の 1 を置いている.
	 *
	 * @param  int $nesting_depth 配列の入れ子の階層数.
	 * @return string シリアライズされた、指定階層の入れ子配列.
	 */
	private function nested_array_payload( $nesting_depth ) {
		return str_repeat( 'a:1:{i:0;', $nesting_depth ) . 'i:1;' . str_repeat( '}', $nesting_depth );
	}

	/**
	 * 復元結果が何階層の入れ子配列になっているかを返す.
	 *
	 * 破棄された値（空文字）は配列ではないため 0 階層として返る.
	 * nested_array_payload() で組み立てた保存値は必ず 1 階層以上あるので、
	 * 0 が返った場合は破棄されたことを意味する.
	 *
	 * @param  mixed $value maybe_unserialize_without_object() の返り値.
	 * @return int 入れ子の階層数.
	 */
	private function nesting_depth_of( $value ) {
		$nesting_depth = 0;

		while ( is_array( $value ) && isset( $value[0] ) ) {
			++$nesting_depth;
			$value = $value[0];
		}

		return $nesting_depth;
	}

	/**
	 * 入力フォームを生成して HTML を返す.
	 *
	 * form_table() は自前で nonce フィールドを出力するため、
	 * テスト結果に混ざらないようバッファに取る.
	 *
	 * @param  array $options 共通設定側の値.
	 * @return string 生成された入力フォームの HTML.
	 */
	private function render_form_table( $options = array() ) {
		ob_start();
		$form_html = VK_Custom_Field_Builder::form_table( $this->checkbox_fields_array(), '', false, $options );
		ob_end_clean();

		return $form_html;
	}

	/**
	 * 出力 HTML の中に、指定した属性がすべてそろった要素があるかを返す.
	 *
	 * WordPress のバージョンによって wp_kses() の出力する属性の並び順が変わるため、並び順に依存せず属性の値で探す.
	 *
	 * @param string $html       検索対象の HTML.
	 * @param string $tag_name   探す要素名（例: input）.
	 * @param array  $attributes 属性名 => 期待する値. 値が true なら「属性がある」、false なら「属性が無い」ことを求める.
	 * @return bool 条件に合う要素があれば true.
	 */
	private function has_tag_with_attributes( $html, $tag_name, $attributes ) {
		$processor = new WP_HTML_Tag_Processor( $html );

		// 要素を先頭から順に見て、すべての属性条件に合う要素が見つかった時点で true を返す.
		while ( $processor->next_tag( $tag_name ) ) {
			$matched = true;
			foreach ( $attributes as $name => $expected ) {
				$actual = $processor->get_attribute( $name );
				if ( false === $expected ) {
					$matched = null === $actual;
				} elseif ( true === $expected ) {
					$matched = null !== $actual;
				} else {
					$matched = $expected === $actual;
				}
				if ( ! $matched ) {
					break;
				}
			}
			if ( $matched ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 保存値は復元されずそのままの形で取得される.
	 */
	public function test_get_raw_post_meta_returns_stored_value_without_restoring_it() {
		$post_id = self::factory()->post->create();
		$payload = $this->probe_payload();

		wp_cache_set( $post_id, array( self::FIELD_KEY => array( $payload ) ), 'post_meta' );

		VK_CFB_Injection_Probe::$woken = false;
		$stored_value                  = VK_Custom_Field_Builder::get_raw_post_meta( $post_id, self::FIELD_KEY );

		$this->assertFalse( VK_CFB_Injection_Probe::$woken );
		$this->assertSame( $payload, $stored_value );
	}

	/**
	 * 存在しないキーでは空文字を返す.
	 */
	public function test_get_raw_post_meta_returns_empty_string_for_missing_key() {
		$post_id = self::factory()->post->create();

		$this->assertSame( '', VK_Custom_Field_Builder::get_raw_post_meta( $post_id, 'cfb_test_missing' ) );
	}

	/**
	 * シリアライズされた文字列でない値はそのまま返る.
	 */
	public function test_maybe_unserialize_without_object_returns_plain_value_as_is() {
		$this->assertSame( 'FULL_TIME', VK_Custom_Field_Builder::maybe_unserialize_without_object( 'FULL_TIME' ) );
		$this->assertSame( 123, VK_Custom_Field_Builder::maybe_unserialize_without_object( 123 ) );
		$this->assertSame( array( 'a' ), VK_Custom_Field_Builder::maybe_unserialize_without_object( array( 'a' ) ) );
	}

	/**
	 * シリアライズされた配列は配列として復元される.
	 */
	public function test_maybe_unserialize_without_object_restores_array() {
		$expected = array( 'FULL_TIME', 'PART_TIME' );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- 復元前の保存値を組み立てるために必要.
		$stored_value = serialize( $expected );

		$this->assertSame( $expected, VK_Custom_Field_Builder::maybe_unserialize_without_object( $stored_value ) );
	}

	/**
	 * シリアライズされた false は失敗扱いにせず false として復元される.
	 */
	public function test_maybe_unserialize_without_object_restores_serialized_false() {
		$this->assertFalse( VK_Custom_Field_Builder::maybe_unserialize_without_object( 'b:0;' ) );
	}

	/**
	 * シリアライズされたオブジェクトは復元されず破棄される.
	 */
	public function test_maybe_unserialize_without_object_discards_object() {
		$this->assertSame( '', VK_Custom_Field_Builder::maybe_unserialize_without_object( 'O:8:"stdClass":1:{s:3:"foo";s:3:"bar";}' ) );
	}

	/**
	 * 配列の中に入れ込まれたオブジェクトも同様に破棄される.
	 */
	public function test_maybe_unserialize_without_object_discards_nested_object() {
		$payload = 'a:1:{i:0;a:1:{s:5:"inner";O:8:"stdClass":0:{}}}';

		$this->assertSame( '', VK_Custom_Field_Builder::maybe_unserialize_without_object( $payload ) );
	}

	/**
	 * 壊れたシリアライズデータは空文字になる.
	 */
	public function test_maybe_unserialize_without_object_discards_broken_data() {
		$this->assertSame( '', VK_Custom_Field_Builder::maybe_unserialize_without_object( 'a:2:{i:0;s:1:"a";}' ) );
	}

	/**
	 * 自分自身を指す配列は、復元しても使わずに破棄される.
	 *
	 * 復元結果をたどる処理が止まらなくなる形のため、値として受け取らない.
	 */
	public function test_maybe_unserialize_without_object_discards_self_referencing_array() {
		$this->assertSame( '', VK_Custom_Field_Builder::maybe_unserialize_without_object( $this->self_referencing_payload() ) );
	}

	/**
	 * 入れ子が 64 階層を超えた保存値は破棄され、64 階層までは復元される.
	 *
	 * 復元を打ち切る上限と、復元後に階層を検査する contains_object() の上限は
	 * 同じ 64 階層なので、どちらで打ち切られたかは返り値からは区別できない.
	 * このテストが押さえるのは「上限の値がずれていないこと」で、
	 * 上限を下げすぎると 64 階層の条件が失敗して気づける.
	 */
	public function test_maybe_unserialize_without_object_limits_nesting_depth() {
		// 階層ごとの期待結果. 上限は 64 階層で、expected は復元された階層数（0 は破棄されたことを意味する）.
		$test_cases = array(
			array(
				'test_condition_name' => '1 階層だけの入れ子の場合 => 1 階層として復元される',
				'nesting_depth'       => 1,
				'expected'            => 1,
			),
			array(
				'test_condition_name' => '上限以内の 64 階層の入れ子の場合 => 64 階層として復元される',
				'nesting_depth'       => 64,
				'expected'            => 64,
			),
			array(
				'test_condition_name' => '上限を超えた 65 階層の入れ子の場合 => 破棄される',
				'nesting_depth'       => 65,
				'expected'            => 0,
			),
			array(
				'test_condition_name' => 'PHP 既定の上限以内でも深すぎる 4000 階層の入れ子の場合 => 破棄される',
				'nesting_depth'       => 4000,
				'expected'            => 0,
			),
		);

		foreach ( $test_cases as $case ) {
			$stored_value = $this->nested_array_payload( $case['nesting_depth'] );
			$restored     = VK_Custom_Field_Builder::maybe_unserialize_without_object( $stored_value );

			$this->assertSame( $case['expected'], $this->nesting_depth_of( $restored ), $case['test_condition_name'] );

			// 破棄された場合は、階層数が 0 になるだけでなく空文字が返る.
			if ( 0 === $case['expected'] ) {
				$this->assertSame( '', $restored, $case['test_condition_name'] );
			}
		}
	}

	/**
	 * 復元でオブジェクトのマジックメソッドに到達しない.
	 */
	public function test_maybe_unserialize_without_object_does_not_wake_object() {
		$payload = $this->probe_payload();

		VK_CFB_Injection_Probe::$woken = false;
		$restored                      = VK_Custom_Field_Builder::maybe_unserialize_without_object( $payload );

		$this->assertFalse( VK_CFB_Injection_Probe::$woken );
		$this->assertSame( '', $restored );
	}

	/**
	 * オブジェクトは最上位でも配列の何階層目でも検出される.
	 */
	public function test_contains_object() {
		$this->assertTrue( VK_Custom_Field_Builder::contains_object( new stdClass() ) );
		$this->assertTrue( VK_Custom_Field_Builder::contains_object( array( 'a' => array( 'b' => new stdClass() ) ) ) );
		$this->assertFalse( VK_Custom_Field_Builder::contains_object( array( 'a' => array( 'b' => 'c' ) ) ) );
		$this->assertFalse( VK_Custom_Field_Builder::contains_object( 'string' ) );
	}

	/**
	 * 自分自身を指す配列を渡しても処理が止まり、使えない値として判定される.
	 *
	 * 打ち切りが効いていない場合、このテストはメモリを使い切って
	 * プロセスごと停止する（Fatal error）ため、値が返ること自体が確認対象になる.
	 */
	public function test_contains_object_stops_for_self_referencing_array() {
		$this->assertTrue( VK_Custom_Field_Builder::contains_object( $this->self_referencing_array() ) );
	}

	/**
	 * 入れ子の深さが上限を超えた配列は、オブジェクトを含まなくても使えない値として判定される.
	 */
	public function test_contains_object_limits_nesting_depth() {
		// 深さごとの期待結果. 上限は 64 階層.
		$test_cases = array(
			array(
				'test_condition_name' => '1 階層だけの配列の場合 => false',
				'nesting_depth'       => 1,
				'expected'            => false,
			),
			array(
				'test_condition_name' => '上限以内の 64 階層の配列の場合 => false',
				'nesting_depth'       => 64,
				'expected'            => false,
			),
			array(
				'test_condition_name' => '上限を超えた 65 階層の配列の場合 => true',
				'nesting_depth'       => 65,
				'expected'            => true,
			),
		);

		foreach ( $test_cases as $case ) {
			// 指定された階層数だけ配列で包んだ値を組み立てる.
			$value = 'FULL_TIME';
			for ( $i = 0; $i < $case['nesting_depth']; $i++ ) {
				$value = array( $value );
			}

			$this->assertSame( $case['expected'], VK_Custom_Field_Builder::contains_object( $value ), $case['test_condition_name'] );
		}
	}

	/**
	 * 入力フォームの生成でチェックボックスの保存値からオブジェクトを生成しない.
	 *
	 * 投稿編集画面からチェックボックスの項目に文字列が送信されて保存された状態を再現している.
	 * update_post_meta() はシリアライズ済みに見える文字列をもう一度シリアライズして保存する.
	 */
	public function test_form_table_does_not_instantiate_object_from_checkbox_meta() {
		global $post;

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		update_post_meta( $post_id, self::FIELD_KEY, $this->probe_payload() );

		VK_CFB_Injection_Probe::$woken = false;
		$form_html                     = $this->render_form_table();

		$this->assertFalse( VK_CFB_Injection_Probe::$woken );
		$this->assertIsString( $form_html );
	}

	/**
	 * シリアライズが 1 回だけかかった保存値でも同じであること.
	 *
	 * インポーターやデータベースへの直接書き込みで残る状態がこれに当たる.
	 */
	public function test_form_table_does_not_instantiate_object_from_single_serialized_meta() {
		global $post;

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		wp_cache_set(
			$post_id,
			array( self::FIELD_KEY => array( $this->probe_payload() ) ),
			'post_meta'
		);

		VK_CFB_Injection_Probe::$woken = false;
		$form_html                     = $this->render_form_table();

		$this->assertFalse( VK_CFB_Injection_Probe::$woken );
		$this->assertIsString( $form_html );
	}

	/**
	 * 保存済みの配列は従来どおりチェック状態として反映される.
	 */
	public function test_form_table_keeps_checkbox_selection_from_meta() {
		global $post;

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		update_post_meta( $post_id, self::FIELD_KEY, array( 'PART_TIME' ) );

		$form_html = $this->render_form_table();

		$expected_attributes = array(
			'value'   => 'PART_TIME',
			'checked' => true,
		);
		$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
		$expected_attributes = array(
			'value'   => 'FULL_TIME',
			'checked' => true,
		);
		$this->assertFalse( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
		// チェックされていない選択肢も出力されている（要素ごと消えて偽陽性になっていない）.
		$expected_attributes = array(
			'value'   => 'FULL_TIME',
			'checked' => false,
		);
		$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
	}

	/**
	 * 画像フィールドのプレビューは、保存値が URL ならその URL を、添付 ID なら添付画像を、それ以外なら画像なしを表示する.
	 * 読み込み失敗時に差し替える「画像なし」の URL（data-no-image）が、wp_kses を通っても出力されることも確かめる.
	 */
	public function test_form_table_image_preview() {
		global $post, $custom_field_builder_url;

		$default_builder_url      = 'https://example.com/cfb/';
		$custom_field_builder_url = $default_builder_url;
		$attachment_id            = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$attachment_src           = wp_get_attachment_image_src( $attachment_id, 'medium', false );
		$fields                   = array(
			'cfb_test_image' => array(
				'label'       => 'Image',
				'type'        => 'image',
				'description' => '',
			),
		);

		$test_cases = array(
			array(
				'test_condition_name' => 'URL で保存済みの場合 => その URL がプレビューになる（変更前は画像なしだった）',
				'saved'               => 'https://example.com/logo.png',
				'expected_src'        => 'https://example.com/logo.png',
			),
			array(
				'test_condition_name' => '添付 ID で保存済みの場合 => 添付画像がプレビューになる',
				'saved'               => (string) $attachment_id,
				'expected_src'        => $attachment_src[0],
			),
			array(
				'test_condition_name' => '空の場合 => 画像なし',
				'saved'               => '',
				'expected_src'        => 'https://example.com/cfb/images/no_image.png',
			),
			array(
				'test_condition_name' => 'javascript: の場合 => 画像なし',
				'saved'               => 'javascript:alert(1)',
				'expected_src'        => 'https://example.com/cfb/images/no_image.png',
			),
			array(
				'test_condition_name' => '存在しない添付 ID の場合 => 画像なし',
				'saved'               => '999999',
				'expected_src'        => 'https://example.com/cfb/images/no_image.png',
			),
			array(
				'test_condition_name' => '長さが上限（2048 バイト）を超える URL で保存済みの場合 => 画像なし',
				'saved'               => 'https://example.com/' . str_repeat( 'a', 2029 ),
				'expected_src'        => 'https://example.com/cfb/images/no_image.png',
			),
			array(
				'test_condition_name' => '「画像なし」の URL に & と引用符を含む場合 => data-no-image は & が 1 回だけエスケープされ、引用符は除去されて属性から飛び出さない',
				'saved'               => '',
				'builder_url'         => 'https://example.org/pkg/?a=1&b=2"x/',
				'expected_src_html'   => 'https://example.org/pkg/?a=1&amp;b=2x/images/no_image.png',
				'expected_no_image'   => 'https://example.org/pkg/?a=1&amp;b=2x/images/no_image.png',
				'should_not_contain'  => array( '&amp;amp;', '"onload="', 'b=2"x' ),
			),
		);

		foreach ( $test_cases as $case ) {
			// 「画像なし」の配置先 URL は、ケースで指定がなければ固定の URL を使う.
			$custom_field_builder_url = isset( $case['builder_url'] ) ? $case['builder_url'] : $default_builder_url;

			$post_id = self::factory()->post->create();
			$post    = get_post( $post_id );
			update_post_meta( $post_id, 'cfb_test_image', $case['saved'] );

			ob_start();
			$form_html = VK_Custom_Field_Builder::form_table( $fields, '', false );
			ob_end_clean();

			// & を含む URL は wp_kses を通ると &amp; になるため、そのケースだけ出力後の形で指定する.
			$expected_src_html   = isset( $case['expected_src_html'] ) ? $case['expected_src_html'] : esc_url( $case['expected_src'] );
			$expected_attributes = array(
				'src' => wp_specialchars_decode( $expected_src_html ),
				'id'  => 'thumb_cfb_test_image',
			);
			$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'img', $expected_attributes ), $case['test_condition_name'] );
			// 送信される hidden input の値は保存値のまま変わらない.
			$expected_attributes = array(
				'name'  => 'cfb_test_image',
				'id'    => 'cfb_test_image',
				'value' => $case['saved'],
			);
			$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ), $case['test_condition_name'] );

			// 読み込み失敗時・画像削除時に js が差し替える「画像なし」の URL が、保存値によらず出力される.
			$expected_no_image = isset( $case['expected_no_image'] ) ? $case['expected_no_image'] : 'https://example.com/cfb/images/no_image.png';
			$this->assertStringContainsString( 'data-no-image="' . $expected_no_image . '"', $form_html, $case['test_condition_name'] );

			if ( isset( $case['should_not_contain'] ) ) {
				foreach ( $case['should_not_contain'] as $not_expected ) {
					$this->assertStringNotContainsString( $not_expected, $form_html, $case['test_condition_name'] );
				}
			}
		}
	}

	/**
	 * 投稿側に値が無い場合は共通設定側の値が使われる.
	 */
	public function test_form_table_falls_back_to_common_option_when_meta_is_empty() {
		global $post;

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$form_html = $this->render_form_table( array( self::FIELD_KEY => array( 'FULL_TIME' ) ) );

		$expected_attributes = array(
			'value'   => 'FULL_TIME',
			'checked' => true,
		);
		$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
		$expected_attributes = array(
			'value'   => 'PART_TIME',
			'checked' => true,
		);
		$this->assertFalse( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
		// チェックされていない選択肢も出力されている（要素ごと消えて偽陽性になっていない）.
		$expected_attributes = array(
			'value'   => 'PART_TIME',
			'checked' => false,
		);
		$this->assertTrue( $this->has_tag_with_attributes( $form_html, 'input', $expected_attributes ) );
	}

	/**
	 * 同梱した package の URL の取得のテスト.
	 *
	 * 各製品の設定ファイルが $custom_field_builder_url に入れた URL を、そのまま返すことを確かめる.
	 *
	 * @return void
	 */
	public function test_admin_directory_url() {
		global $custom_field_builder_url;

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => 'テーマに同梱した package の URL が設定済み => その URL を返す',
				'builder_url'         => 'https://example.com/wp-content/themes/sample/inc/custom-field-builder/package/',
				'expected'            => 'https://example.com/wp-content/themes/sample/inc/custom-field-builder/package/',
			),
			array(
				'test_condition_name' => 'プラグインに同梱した package の URL が設定済み => その URL を返す',
				'builder_url'         => 'https://example.com/wp-content/plugins/sample/inc/custom-field-builder/package/',
				'expected'            => 'https://example.com/wp-content/plugins/sample/inc/custom-field-builder/package/',
			),
			array(
				'test_condition_name' => '設定ファイルで URL が設定されていない => null を返す',
				'builder_url'         => null,
				'expected'            => null,
			),
		);

		foreach ( $test_cases as $case ) {
			// 設定ファイルで URL を入れた状態にする.
			$custom_field_builder_url = $case['builder_url'];

			// テスト関数を実行する.
			$actual = VK_Custom_Field_Builder::admin_directory_url();

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 管理画面の読み込みのテスト.
	 *
	 * 管理画面で読み込む js・css の URL が $custom_field_builder_url から作られること（空にならないこと）と、
	 * 既存のフィルター（cfb_*）が非推奨の通知を出さずに従来どおり使えることを確かめる.
	 * 非推奨の通知が出た場合は、WP_UnitTestCase がテストを失敗にする.
	 *
	 * @return void
	 */
	public function test_print_script() {
		global $custom_field_builder_url;

		// 設定ファイルで入れる package の URL.
		$package_url              = 'https://example.com/wp-content/plugins/sample/inc/custom-field-builder/package/';
		$custom_field_builder_url = $package_url;

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => '投稿編集画面 => すべて読み込み、URL は $custom_field_builder_url から作る',
				'conditions'          => array(
					'hook_suffix' => 'post.php',
					'filters'     => array(),
				),
				'expected'            => array(
					'datepicker'                 => $package_url . 'js/datepicker.js',
					'vk_mediauploader'           => $package_url . 'js/mediauploader.js',
					'flexible-table'             => $package_url . 'js/flexible-table.js',
					'cf-builder-style'           => $package_url . 'css/cf-builder.css',
					'cf-builder-jquery-ui-style' => $package_url . 'css/jquery-ui.css',
				),
			),
			array(
				'test_condition_name' => 'プロフィール画面 => メディアアップローダーだけ読み込まない',
				'conditions'          => array(
					'hook_suffix' => 'profile.php',
					'filters'     => array(),
				),
				'expected'            => array(
					'datepicker'                 => $package_url . 'js/datepicker.js',
					'vk_mediauploader'           => false,
					'flexible-table'             => $package_url . 'js/flexible-table.js',
					'cf-builder-style'           => $package_url . 'css/cf-builder.css',
					'cf-builder-jquery-ui-style' => $package_url . 'css/jquery-ui.css',
				),
			),
			array(
				'test_condition_name' => '既存の 3 つのフィルター（cfb_*）で投稿編集画面を除外 => どれも読み込まず、非推奨の通知も出ない',
				'conditions'          => array(
					'hook_suffix' => 'post.php',
					'filters'     => array( 'cfb_media_uploader_exclude', 'cfb_flexible_table_excludes', 'cfb_jquery_ui_excludes' ),
				),
				'expected'            => array(
					'datepicker'                 => $package_url . 'js/datepicker.js',
					'vk_mediauploader'           => false,
					'flexible-table'             => false,
					'cf-builder-style'           => $package_url . 'css/cf-builder.css',
					'cf-builder-jquery-ui-style' => false,
				),
			),
			array(
				'test_condition_name' => 'cfb_flexible_table_excludes だけで投稿編集画面を除外 => Flexible Table の js だけ読み込まない',
				'conditions'          => array(
					'hook_suffix' => 'post.php',
					'filters'     => array( 'cfb_flexible_table_excludes' ),
				),
				'expected'            => array(
					'datepicker'                 => $package_url . 'js/datepicker.js',
					'vk_mediauploader'           => $package_url . 'js/mediauploader.js',
					'flexible-table'             => false,
					'cf-builder-style'           => $package_url . 'css/cf-builder.css',
					'cf-builder-jquery-ui-style' => $package_url . 'css/jquery-ui.css',
				),
			),
		);

		/**
		 * 表示中の画面を除外リストに足すフィルター.
		 *
		 * @param array $excludes 読み込みから外す画面の一覧.
		 * @return array 表示中の画面を足した一覧.
		 */
		$exclude_current_screen = function ( $excludes ) {
			$excludes[] = $GLOBALS['hook_suffix'];
			return $excludes;
		};

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];

			// 読み込み状態を初期化し、表示中の画面とフィルターを設定する.
			$GLOBALS['wp_scripts']  = null;
			$GLOBALS['wp_styles']   = null;
			$GLOBALS['hook_suffix'] = $conditions['hook_suffix'];
			foreach ( $conditions['filters'] as $filter_name ) {
				add_filter( $filter_name, $exclude_current_screen );
			}

			// テスト関数を実行する.
			VK_Custom_Field_Builder::print_script( $conditions['hook_suffix'] );

			// 読み込まれたファイルの URL を集める（読み込まれていなければ false）.
			$actual = array(
				'datepicker'                 => wp_script_is( 'datepicker', 'enqueued' ) ? wp_scripts()->registered['datepicker']->src : false,
				'vk_mediauploader'           => wp_script_is( 'vk_mediauploader', 'enqueued' ) ? wp_scripts()->registered['vk_mediauploader']->src : false,
				'flexible-table'             => wp_script_is( 'flexible-table', 'enqueued' ) ? wp_scripts()->registered['flexible-table']->src : false,
				'cf-builder-style'           => wp_style_is( 'cf-builder-style', 'enqueued' ) ? wp_styles()->registered['cf-builder-style']->src : false,
				'cf-builder-jquery-ui-style' => wp_style_is( 'cf-builder-jquery-ui-style', 'enqueued' ) ? wp_styles()->registered['cf-builder-jquery-ui-style']->src : false,
			);

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );

			// フィルターを外す.
			foreach ( $conditions['filters'] as $filter_name ) {
				remove_filter( $filter_name, $exclude_current_screen );
			}
		}
	}

	/**
	 * 必須項目の目印のテスト.
	 *
	 * 文言のテキストドメインが、各製品のビルドで置き換える仮の名前（custom_field_builder_textdomain）になっていることを確かめる.
	 *
	 * @return void
	 */
	public function test_form_required() {
		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => '翻訳なし => 英語の文言で出力される',
				'translate_domain'    => null,
				'expected'            => '<span class="required">Required</span>',
			),
			array(
				'test_condition_name' => 'テキストドメイン custom_field_builder_textdomain の翻訳あり => 翻訳した文言で出力される',
				'translate_domain'    => 'custom_field_builder_textdomain',
				'expected'            => '<span class="required">必須</span>',
			),
			array(
				'test_condition_name' => '別製品のテキストドメイン（vk-google-job-posting-manager）の翻訳だけあり => 翻訳されない',
				'translate_domain'    => 'vk-google-job-posting-manager',
				'expected'            => '<span class="required">Required</span>',
			),
		);

		foreach ( $test_cases as $case ) {
			$translate_domain = $case['translate_domain'];

			/**
			 * 指定したテキストドメインの「Required」だけを翻訳するフィルター.
			 *
			 * @param string $translation 翻訳済みの文言.
			 * @param string $text        翻訳前の文言.
			 * @param string $domain      テキストドメイン.
			 * @return string 翻訳した文言.
			 */
			$translate = function ( $translation, $text, $domain ) use ( $translate_domain ) {
				if ( 'Required' === $text && $translate_domain === $domain ) {
					return '必須';
				}
				return $translation;
			};
			add_filter( 'gettext', $translate, 10, 3 );

			// テスト関数を実行する.
			$actual = VK_Custom_Field_Builder::form_required();

			remove_filter( 'gettext', $translate, 10 );

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 入力欄に表示する値の取得のテスト.
	 *
	 * 送信された値を入力欄に表示し直すのは、正しい nonce が付いているときだけであることを確かめる.
	 *
	 * @return void
	 */
	public function test_form_post_value() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$key      = 'cfb_test_form_value';

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => '送信がない（通常の編集画面） => 保存済みの値を属性値として安全な形で返す',
				'conditions'          => array(
					'nonce'  => 'none',
					'posted' => null,
					'type'   => false,
				),
				'expected'            => 'A &amp; &quot;B&quot;',
			),
			array(
				'test_condition_name' => '正しい nonce 付きで値を送信 => 送信値を、バックスラッシュを付けずに返す',
				'conditions'          => array(
					'nonce'  => 'valid',
					'posted' => "O'Reilly",
					'type'   => false,
				),
				'expected'            => 'O&#039;Reilly',
			),
			array(
				'test_condition_name' => '別画面の nonce 付きで値を送信 => 送信値ではなく保存済みの値を返す',
				'conditions'          => array(
					'nonce'  => 'other',
					'posted' => 'injected',
					'type'   => false,
				),
				'expected'            => 'A &amp; &quot;B&quot;',
			),
			array(
				'test_condition_name' => 'nonce なしで値を送信 => 送信値ではなく保存済みの値を返す',
				'conditions'          => array(
					'nonce'  => 'none',
					'posted' => 'injected',
					'type'   => false,
				),
				'expected'            => 'A &amp; &quot;B&quot;',
			),
			array(
				'test_condition_name' => '正しい nonce 付きで textarea に HTML を送信 => 投稿本文で使えるタグだけを残して返す',
				'conditions'          => array(
					'nonce'  => 'valid',
					'posted' => '<strong>a</strong><script>x</script>',
					'type'   => 'textarea',
				),
				'expected'            => '<strong>a</strong>x',
			),
			array(
				'test_condition_name' => '正しい nonce 付きで 0 を送信 => 0 を返す',
				'conditions'          => array(
					'nonce'  => 'valid',
					'posted' => '0',
					'type'   => false,
				),
				'expected'            => '0',
			),
			array(
				'test_condition_name' => '正しい nonce 付きで空文字を送信 => 空文字を返す',
				'conditions'          => array(
					'nonce'  => 'valid',
					'posted' => '',
					'type'   => false,
				),
				'expected'            => '',
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];

			// 保存済みの値を持つ投稿を用意する.
			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			update_post_meta( $post_id, $key, 'A & "B"' );

			global $post;
			$post = get_post( $post_id );
			wp_set_current_user( $admin_id );

			// nonce と送信値を用意する.
			if ( 'valid' === $conditions['nonce'] ) {
				$_POST['noncename__fields'] = wp_create_nonce( 'vk_custom_field_builder' );
			} elseif ( 'other' === $conditions['nonce'] ) {
				$_POST['noncename__fields'] = wp_create_nonce( 'other_action' );
			}
			if ( null !== $conditions['posted'] ) {
				$_POST[ $key ] = wp_slash( $conditions['posted'] );
			}

			// テスト関数を実行する.
			$actual = VK_Custom_Field_Builder::form_post_value( $key, $conditions['type'] );

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 入力フォームの表示のテスト.
	 *
	 * 保存済みの値（既存ユーザーの値）が、入力欄の種類ごとに属性値・textarea の中身として安全な形で表示されることを確かめる.
	 * 出力全体を許可リスト（wp_kses）に通すため、期待値は許可リストを通したあとの形（style 末尾の ; が無いなど）で書く.
	 * 画像欄のプレビューは test_form_table_image_preview() で確かめる.
	 *
	 * @return void
	 */
	public function test_form_table() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$key      = 'cfb_test_form_field';

		// 選択肢を持つ入力欄の選択肢.
		$choices = array(
			'a' => 'A',
			'b' => 'B',
			'c' => 'C',
		);

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => 'テキスト欄に、記号を含む値が保存済み => 属性値として安全な形で表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'text' ),
					'stored'       => 'A & "B" <b>',
					'options'      => array(),
				),
				'expected'            => array(
					'<input class="form-control" type="text" id="' . $key . '" name="' . $key . '" value="A &amp; &quot;B&quot; &lt;b&gt;" size="70">',
				),
				'not_expected'        => array( '"B"', '<b>' ),
			),
			array(
				'test_condition_name' => 'テキスト欄に 0 が保存済み => 0 が表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'text' ),
					'stored'       => '0',
					'options'      => array(),
				),
				'expected'            => array( 'name="' . $key . '" value="0" size="70">' ),
				'not_expected'        => array(),
			),
			array(
				'test_condition_name' => 'URL 欄に、クエリ付きの URL が保存済み => & を実体参照にして表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'url' ),
					'stored'       => 'https://example.com/?a=1&b=2',
					'options'      => array(),
				),
				'expected'            => array( 'name="' . $key . '" value="https://example.com/?a=1&amp;b=2" size="70">' ),
				'not_expected'        => array(),
			),
			array(
				'test_condition_name' => 'textarea に HTML が保存済み => タグを文字として表示する',
				'conditions'          => array(
					'field_config' => array( 'type' => 'textarea' ),
					'stored'       => '<strong>説明</strong>',
					'options'      => array(),
				),
				'expected'            => array(
					'<textarea class="form-control cf_textarea_wysiwyg" name="' . $key . '" cols="70" rows="3">&lt;strong&gt;説明&lt;/strong&gt;</textarea>',
				),
				'not_expected'        => array( '<strong>' ),
			),
			array(
				'test_condition_name' => 'textarea に終了タグを含む値が保存済み => textarea の外に出ない',
				'conditions'          => array(
					'field_config' => array( 'type' => 'textarea' ),
					'stored'       => 'x</textarea><script>alert(1)</script>',
					'options'      => array(),
				),
				'expected'            => array( 'rows="3">x&lt;/textarea&gt;&lt;script&gt;alert(1)&lt;/script&gt;</textarea>' ),
				'not_expected'        => array( '</textarea><script>', '<script>' ),
			),
			array(
				'test_condition_name' => '日付欄に日付が保存済み => その日付が表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'datepicker' ),
					'stored'       => '2026-11-16',
					'options'      => array(),
				),
				'expected'            => array( '<input class="form-control datepicker" type="text" id="' . $key . '" name="' . $key . '" value="2026-11-16" size="70">' ),
				'not_expected'        => array(),
			),
			array(
				'test_condition_name' => 'セレクトボックスに b が保存済み => b が選択された状態で表示される',
				'conditions'          => array(
					'field_config' => array(
						'type'    => 'select',
						'options' => $choices,
					),
					'stored'       => 'b',
					'options'      => array(),
				),
				'expected'            => array(
					'<select id="' . $key . '" class="form-control" name="' . $key . '">',
					'<option value="a">A</option>',
					'<option value="b" selected="selected">B</option>',
				),
				'not_expected'        => array( '<option value="a" selected="selected">' ),
			),
			array(
				'test_condition_name' => 'ラジオボタンに b が保存済み => b がチェックされた状態で表示される',
				'conditions'          => array(
					'field_config' => array(
						'type'    => 'radio',
						'options' => $choices,
					),
					'stored'       => 'b',
					'options'      => array(),
				),
				'expected'            => array( 'value="b" checked' ),
				'not_expected'        => array( 'value="a" checked', 'value="c" checked' ),
			),
			array(
				'test_condition_name' => 'チェックボックスに a と c が保存済み => a と c がチェックされた状態で表示される',
				'conditions'          => array(
					'field_config' => array(
						'type'    => 'checkbox',
						'options' => $choices,
					),
					'stored'       => array( 'a', 'c' ),
					'options'      => array(),
				),
				'expected'            => array( 'value="a" checked', 'value="c" checked' ),
				'not_expected'        => array( 'value="b" checked' ),
			),
			array(
				'test_condition_name' => 'ファイル欄に URL が保存済み => URL と、ページを送信しないボタン・別タブで開くリンクが表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'file' ),
					'stored'       => 'https://example.com/a.pdf',
					'options'      => array(),
				),
				'expected'            => array(
					'<input name="' . $key . '" id="' . $key . '" value="https://example.com/a.pdf" style="width:60%" />',
					'<button type="button" id="media_src_' . $key . '" class="cfb_media_btn',
					'<a href="https://example.com/a.pdf" target="_blank" rel="noopener noreferrer" class="btn btn-default button button-default">',
				),
				'not_expected'        => array(),
			),
			array(
				'test_condition_name' => '画像欄に添付 ID が保存済み => ID が送信用の欄に入り、ページを送信しないボタンが表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'image' ),
					'stored'       => '12',
					'options'      => array(),
				),
				'expected'            => array(
					'<input type="hidden" name="' . $key . '" id="' . $key . '" value="12" style="width:60%" />',
					'<button type="button" id="media_' . $key . '" class="cfb_media_btn',
				),
				'not_expected'        => array(),
			),
			array(
				'test_condition_name' => '保存値がなく、呼び出し側の初期値に二重引用符を含む値 => 属性の外に出ない',
				'conditions'          => array(
					'field_config' => array( 'type' => 'text' ),
					'stored'       => null,
					'options'      => array( $key => 'say "hi"' ),
				),
				'expected'            => array( 'name="' . $key . '" value="say &quot;hi&quot;" size="70">' ),
				'not_expected'        => array( 'value="say "hi""' ),
			),
			array(
				'test_condition_name' => '保存値がなく、呼び出し側の初期値（textarea）に終了タグとスクリプトを含む値 => textarea の外に出ない',
				'conditions'          => array(
					'field_config' => array( 'type' => 'textarea' ),
					'stored'       => null,
					'options'      => array( $key => '</textarea><script>alert(1)</script>' ),
				),
				'expected'            => array( 'rows="3">&lt;/textarea&gt;alert(1)</textarea>' ),
				'not_expected'        => array( '<script>', '</textarea></textarea>' ),
			),
			array(
				'test_condition_name' => '保存値も初期値もない => 空の入力欄が表示される',
				'conditions'          => array(
					'field_config' => array( 'type' => 'text' ),
					'stored'       => null,
					'options'      => array(),
				),
				'expected'            => array( 'name="' . $key . '" value="" size="70">' ),
				'not_expected'        => array(),
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];

			// 保存済みの値を持つ投稿を、編集画面で開いている状態にする.
			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			if ( null !== $conditions['stored'] ) {
				update_post_meta( $post_id, $key, wp_slash( $conditions['stored'] ) );
			}

			global $post;
			$post = get_post( $post_id );
			wp_set_current_user( $admin_id );

			// フィールドの定義（ラベルと説明は共通）.
			$fields = array(
				$key => array_merge(
					array(
						'label'       => 'Label',
						'description' => '',
						'required'    => false,
					),
					$conditions['field_config']
				),
			);

			// テスト関数を実行する（$echo が false でも nonce フィールドを出力するため、バッファに取る）.
			ob_start();
			$actual = VK_Custom_Field_Builder::form_table( $fields, '', false, $conditions['options'] );
			ob_end_clean();

			foreach ( $case['expected'] as $expected ) {
				$this->assertStringContainsString( $expected, $actual, $case['test_condition_name'] );
			}
			foreach ( $case['not_expected'] as $not_expected ) {
				$this->assertStringNotContainsString( $not_expected, $actual, $case['test_condition_name'] );
			}
		}
	}

	/**
	 * 項目変動・多行（Flexible Table）の入力フォームの表示のテスト.
	 *
	 * 保存済みの行（既存ユーザーの値）が二重にエスケープされずに表示されることと、
	 * 行のキー・列のキー・textarea の値に記号が含まれていても、属性や textarea の外に出ないことを確かめる.
	 * textarea に保存済みの太字・改行・リンクは、ブラウザーが表示する textarea の値として保存値のまま残り、
	 * その値をそのまま送信し直しても保存値が変わらないことも確かめる.
	 *
	 * @return void
	 */
	public function test_form_table_flexible() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$field    = 'cfb_test_form_flexible';

		// 標準の列の定義.
		$standard_items = array(
			'name' => array(
				'label' => 'Name',
				'type'  => 'text',
			),
			'memo' => array(
				'label' => 'Memo',
				'type'  => 'textarea',
			),
			'kind' => array(
				'label'   => 'Kind',
				'type'    => 'select',
				'options' => array(
					'a' => 'A',
					'b' => 'B',
				),
			),
			'date' => array(
				'label' => 'Date',
				'type'  => 'datepicker',
			),
		);

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => '通常の行（キー 0）が保存済み => 各列が保存値のまま表示される（二重にエスケープしない）',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => array(
						0 => array(
							'name' => 'A & "B"',
							'memo' => '<strong>太字</strong>',
							'kind' => 'b',
							'date' => '2026-11-16',
						),
					),
				),
				'expected'            => array(
					'<th class="cell-name">Name</th>',
					'<td class="cell-name"><input class="flexible-field-item" type="text" id="' . $field . '[0][name]" name="' . $field . '[0][name]" value="A &amp; &quot;B&quot;"></td>',
					'name="' . $field . '[0][memo]" cols="100" rows="3">&lt;strong&gt;太字&lt;/strong&gt;</textarea>',
					'<select id="' . $field . '[0][kind]" class="flexible-field-item" name="' . $field . '[0][kind]">',
					'<option value="b" selected="selected">B</option>',
					'<input class="flexible-field-item datepicker" type="text" id="' . $field . '[0][date]" name="' . $field . '[0][date]" value="2026-11-16" size="70">',
				),
				'not_expected'        => array( '&amp;amp;', '&amp;quot;' ),
				'expected_textarea'   => '<strong>太字</strong>',
			),
			array(
				'test_condition_name' => 'textarea に太字・改行・リンクを含む HTML が保存済み => 取り除かれずに textarea の値として表示され、そのまま送信し直しても保存値が変わらない',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => array(
						0 => array(
							'name' => 'n',
							'memo' => '<strong>太字</strong><br><a href="https://example.com/?a=1&amp;b=2">link</a>',
							'kind' => 'a',
							'date' => '',
						),
					),
				),
				'expected'            => array(
					'cols="100" rows="3">&lt;strong&gt;太字&lt;/strong&gt;&lt;br&gt;&lt;a href=&quot;https://example.com/?a=1&amp;amp;b=2&quot;&gt;link&lt;/a&gt;</textarea>',
				),
				'not_expected'        => array( 'rows="3">太字link</textarea>' ),
				'expected_textarea'   => '<strong>太字</strong><br><a href="https://example.com/?a=1&amp;b=2">link</a>',
			),
			array(
				'test_condition_name' => 'textarea に太字と実体参照（&lt;widget&gt;）が保存済み => 実体参照が保たれ、そのまま送信し直しても保存値が変わらない',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => array(
						0 => array(
							'name' => 'n',
							'memo' => '<strong>&lt;widget&gt;</strong>',
							'kind' => 'a',
							'date' => '',
						),
					),
				),
				'expected'            => array(
					'cols="100" rows="3">&lt;strong&gt;&amp;lt;widget&amp;gt;&lt;/strong&gt;</textarea>',
				),
				'not_expected'        => array(),
				'expected_textarea'   => '<strong>&lt;widget&gt;</strong>',
			),
			array(
				'test_condition_name' => '実体参照の文字列そのものが保存済みの行が 2 行 => 行ごとのキーで表示され、実体参照は二重にならない',
				'conditions'          => array(
					'items'  => array( 'name' => $standard_items['name'] ),
					'stored' => array(
						0 => array( 'name' => 'Tom &amp; Jerry' ),
						1 => array( 'name' => '2 行目' ),
					),
				),
				'expected'            => array(
					'name="' . $field . '[0][name]" value="Tom &amp; Jerry">',
					'name="' . $field . '[1][name]" value="2 行目">',
				),
				'not_expected'        => array( '&amp;amp;' ),
			),
			array(
				'test_condition_name' => '行のキーに記号を含む行が保存済み => id・name の属性の外に出ない',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => array(
						'1"><b>x' => array(
							'name' => 'n',
							'memo' => 'm',
							'kind' => 'a',
							'date' => '',
						),
					),
				),
				'expected'            => array(
					'id="' . $field . '[1&quot;&gt;&lt;b&gt;x][name]" name="' . $field . '[1&quot;&gt;&lt;b&gt;x][name]" value="n">',
					'name="' . $field . '[1&quot;&gt;&lt;b&gt;x][memo]" cols="100"',
				),
				'not_expected'        => array( '"><b>x', '<b>' ),
			),
			array(
				'test_condition_name' => '列のキーに記号を含む定義 => class の属性の外に出ない',
				'conditions'          => array(
					'items'  => array(
						'a"b' => array(
							'label' => 'AB',
							'type'  => 'text',
						),
					),
					'stored' => array(
						0 => array( 'a"b' => 'v' ),
					),
				),
				'expected'            => array(
					'<th class="cell-a&quot;b">AB</th>',
					'<td class="cell-a&quot;b">',
				),
				'not_expected'        => array( 'class="cell-a"b"' ),
			),
			array(
				'test_condition_name' => 'textarea の値に終了タグとスクリプトを含む行が保存済み => textarea の外に出ない',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => array(
						0 => array(
							'name' => 'n',
							'memo' => 'x</textarea><script>alert(1)</script>',
							'kind' => 'a',
							'date' => '',
						),
					),
				),
				'expected'            => array( 'cols="100" rows="3">x&lt;/textarea&gt;alert(1)</textarea>' ),
				'not_expected'        => array( '<script>', 'x</textarea>alert(1)' ),
			),
			array(
				'test_condition_name' => '保存値がない => 初期の行数だけ空の入力欄が表示される',
				'conditions'          => array(
					'items'  => $standard_items,
					'stored' => null,
				),
				'expected'            => array( 'id="' . $field . '[0][name]" name="' . $field . '[0][name]" value="">' ),
				'not_expected'        => array( '[1][name]' ),
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];
			$fields     = array(
				'field_name'  => $field,
				'row_default' => 1,
				'items'       => $conditions['items'],
			);

			// 保存済みの行を持つ投稿を、編集画面で開いている状態にする.
			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			if ( null !== $conditions['stored'] ) {
				update_post_meta( $post_id, $field, wp_slash( $conditions['stored'] ) );
			}

			global $post;
			$post = get_post( $post_id );
			wp_set_current_user( $admin_id );

			// テスト関数を実行する（nonce フィールドとフォームを出力するため、バッファに取る）.
			ob_start();
			VK_Custom_Field_Builder_Flexible_Table::form_table_flexible( $fields );
			$actual = ob_get_clean();

			foreach ( $case['expected'] as $expected ) {
				$this->assertStringContainsString( $expected, $actual, $case['test_condition_name'] );
			}
			foreach ( $case['not_expected'] as $not_expected ) {
				$this->assertStringNotContainsString( $not_expected, $actual, $case['test_condition_name'] );
			}

			if ( ! isset( $case['expected_textarea'] ) ) {
				continue;
			}

			// ブラウザーが表示する textarea の値（実体参照を戻した値）を取り出す.
			$this->assertSame( 1, preg_match( '#name="' . preg_quote( $field, '#' ) . '\[0\]\[memo\]"[^>]*>(.*?)</textarea>#s', $actual, $matches ), $case['test_condition_name'] . '（textarea が出力される）' );
			$textarea_value = html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' );
			$this->assertSame( $case['expected_textarea'], $textarea_value, $case['test_condition_name'] . '（textarea の値）' );

			// 表示された値をそのまま送信し直しても、保存値が変わらないことを確かめる.
			$resubmitted                     = $conditions['stored'];
			$resubmitted[0]['memo']          = $textarea_value;
			$_POST                           = array();
			$_POST[ 'noncename__' . $field ] = wp_create_nonce( 'vk_custom_field_flexible_table' );
			$_POST[ $field ]                 = wp_slash( $resubmitted );
			VK_Custom_Field_Builder_Flexible_Table::save_cf_value( $fields );
			$this->assertSame( $conditions['stored'], get_post_meta( $post_id, $field, true ), $case['test_condition_name'] . '（送信し直したあとの保存値）' );
		}
	}

	/**
	 * 項目変動・多行（Flexible Table）の表示用の行（tbody の中身）のテスト.
	 *
	 * 保存済みの行が、列の class・align の指定どおりに出力されることと、
	 * 出力する HTML が投稿本文で使える HTML に整えられる（太字・改行・リンクは残り、script タグや on* 属性は残らない）ことを確かめる.
	 *
	 * @return void
	 */
	public function test_get_view_table_body() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$field    = 'cfb_test_view_flexible';

		// 標準の列の定義.
		$standard_items = array(
			'name' => array( 'type' => 'text' ),
			'memo' => array( 'type' => 'textarea' ),
		);

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => 'テキストと、投稿本文で使える HTML を含む行が保存済み => そのまま 1 行で出力される',
				'conditions'          => array(
					'items'             => $standard_items,
					'row_empty_display' => false,
					'stored'            => array(
						0 => array(
							'name' => '名前',
							'memo' => '<strong>太字</strong>',
						),
					),
				),
				'expected'            => '<tr><td>名前</td><td><strong>太字</strong></td></tr>',
			),
			array(
				'test_condition_name' => 'セルに太字・改行・リンクを含む値が保存済み => 取り除かれずに出力される',
				'conditions'          => array(
					'items'             => $standard_items,
					'row_empty_display' => false,
					'stored'            => array(
						0 => array(
							'name' => '名前',
							'memo' => '<strong>太字</strong><br><a href="https://example.com/">link</a>',
						),
					),
				),
				'expected'            => '<tr><td>名前</td><td><strong>太字</strong><br><a href="https://example.com/">link</a></td></tr>',
			),
			array(
				'test_condition_name' => '列に class と align の指定あり => td に class と text-{align} を出力する',
				'conditions'          => array(
					'items'             => array(
						'name' => array(
							'type'  => 'text',
							'class' => 'cell-name',
							'align' => 'right',
						),
					),
					'row_empty_display' => false,
					'stored'            => array(
						0 => array( 'name' => '名前' ),
					),
				),
				'expected'            => '<tr><td class="cell-name text-right">名前</td></tr>',
			),
			array(
				'test_condition_name' => '空の行があり、空の行を表示しない指定 => 値のある行だけ出力される',
				'conditions'          => array(
					'items'             => array( 'name' => array( 'type' => 'text' ) ),
					'row_empty_display' => false,
					'stored'            => array(
						0 => array( 'name' => '名前' ),
						1 => array( 'name' => '' ),
					),
				),
				'expected'            => '<tr><td>名前</td></tr>',
			),
			array(
				'test_condition_name' => '空の行があり、空の行も表示する指定 => 空の行も出力される',
				'conditions'          => array(
					'items'             => array( 'name' => array( 'type' => 'text' ) ),
					'row_empty_display' => true,
					'stored'            => array(
						0 => array( 'name' => '名前' ),
						1 => array( 'name' => '' ),
					),
				),
				'expected'            => '<tr><td>名前</td></tr><tr><td></td></tr>',
			),
			array(
				'test_condition_name' => 'セルに script タグを含む値が保存済み => script タグを取り除いて出力される',
				'conditions'          => array(
					'items'             => $standard_items,
					'row_empty_display' => false,
					'stored'            => array(
						0 => array(
							'name' => '名前',
							'memo' => '<script>alert(1)</script>',
						),
					),
				),
				'expected'            => '<tr><td>名前</td><td>alert(1)</td></tr>',
			),
			array(
				'test_condition_name' => 'セルに onclick 属性を持つタグを含む値が保存済み => onclick 属性を取り除いて出力される',
				'conditions'          => array(
					'items'             => $standard_items,
					'row_empty_display' => false,
					'stored'            => array(
						0 => array(
							'name' => '名前',
							'memo' => '<span onclick="alert(1)">メモ</span>',
						),
					),
				),
				'expected'            => '<tr><td>名前</td><td><span>メモ</span></td></tr>',
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];

			// 保存済みの行を持つ投稿を、表示している状態にする.
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			update_post_meta( $post_id, $field, wp_slash( $conditions['stored'] ) );

			global $post;
			$post = get_post( $post_id );

			// テスト関数を実行する.
			$actual = VK_Custom_Field_Builder_Flexible_Table::get_view_table_body(
				array(
					'field_name'        => $field,
					'row_empty_display' => $conditions['row_empty_display'],
					'items'             => $conditions['items'],
				)
			);

			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * 保存処理 save_cf_value() が nonce と投稿の編集権限を確認したうえで、送信された値を整形して保存することを確認する.
	 *
	 * フィールド形式（テキスト・URL・セレクトボックス・ラジオボタン・textarea・チェックボックス）ごとに整えて保存されることと、
	 * nonce も値も送られない保存（このフォームを通らない保存）では保存済みの値が残ることも確かめる.
	 *
	 * @return void
	 */
	public function test_save_cf_value() {
		$admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$author_id     = self::factory()->user->create( array( 'role' => 'author' ) );
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		$nonce_action = 'vk_custom_field_builder';
		$saved        = 'FULL_TIME';

		// 選択肢を持つフィールドの選択肢.
		$choices = array(
			'a' => 'A',
			'b' => 'B',
		);

		$test_cases = array(
			array(
				'test_condition_name' => '管理者が nonce 付きで PART_TIME を送信 => PART_TIME が保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => array( 'PART_TIME' ),
			),
			array(
				'test_condition_name' => '投稿者が自分の投稿を nonce 付きで送信 => 保存される',
				'user_id'             => $author_id,
				'post_author'         => $author_id,
				'nonce'               => 'valid',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => array( 'PART_TIME' ),
			),
			array(
				'test_condition_name' => '投稿者が他人の投稿を nonce 付きで送信 => 保存されず既存の値のまま',
				'user_id'             => $author_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => '購読者（投稿の編集権限なし）が nonce 付きで送信 => 保存されず既存の値のまま',
				'user_id'             => $subscriber_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce が無い => 保存されず既存の値のまま',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'none',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce の用途名が違う => 保存されず既存の値のまま',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'other',
				'posted'              => array( 'PART_TIME' ),
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'テキスト欄に HTML を含む値を送信 => タグを取り除いて保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array( 'type' => 'text' ),
				'stored'              => 'old',
				'posted'              => '<b>名前</b> & 会社',
				'expected'            => '名前 & 会社',
			),
			array(
				'test_condition_name' => 'URL 欄にクエリ付きの URL を送信 => URL として保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array( 'type' => 'url' ),
				'stored'              => 'old',
				'posted'              => 'https://example.com/?a=1&b=2',
				'expected'            => 'https://example.com/?a=1&b=2',
			),
			array(
				'test_condition_name' => 'URL 欄に javascript: の値を送信 => 空になる',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array( 'type' => 'url' ),
				'stored'              => 'https://example.com/old',
				'posted'              => 'javascript:alert(1)',
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'セレクトボックスに選択肢の値を送信 => その値が保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array(
					'type'    => 'select',
					'options' => $choices,
				),
				'stored'              => 'a',
				'posted'              => 'b',
				'expected'            => 'b',
			),
			array(
				'test_condition_name' => 'ラジオボタンにタグを含む値を送信 => タグを取り除いて保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array(
					'type'    => 'radio',
					'options' => $choices,
				),
				'stored'              => 'b',
				'posted'              => '<b>a</b>',
				'expected'            => 'a',
			),
			array(
				'test_condition_name' => 'textarea に HTML を送信 => 投稿本文で使えるタグだけを残して保存される',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array( 'type' => 'textarea' ),
				'stored'              => 'old',
				'posted'              => '<strong>a</strong><script>x</script>',
				'expected'            => '<strong>a</strong>x',
			),
			array(
				'test_condition_name' => 'チェックボックスをすべて外して送信（値が送られない） => 保存済みの値を消す',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'valid',
				'field_config'        => array(
					'type'    => 'checkbox',
					'options' => $choices,
				),
				'stored'              => array( 'a' ),
				'expected'            => '',
			),
			array(
				'test_condition_name' => 'nonce も値も送られない保存（クイック編集・REST API・自動保存など、このフォームを通らない保存） => 保存済みの値を消さない',
				'user_id'             => $admin_id,
				'post_author'         => $admin_id,
				'nonce'               => 'none',
				'field_config'        => array(
					'type'    => 'select',
					'options' => $choices,
				),
				'stored'              => 'b',
				'expected'            => 'b',
			),
		);

		foreach ( $test_cases as $case ) {
			$_POST = array();

			// 指定がないケースは、チェックボックスの項目に $saved が保存済みの状態で始める.
			$fields = isset( $case['field_config'] )
				? array( self::FIELD_KEY => $case['field_config'] )
				: $this->checkbox_fields_array();
			$stored = array_key_exists( 'stored', $case ) ? $case['stored'] : $saved;

			$post_id = self::factory()->post->create( array( 'post_author' => $case['post_author'] ) );
			update_post_meta( $post_id, self::FIELD_KEY, wp_slash( $stored ) );

			global $post;
			$post = get_post( $post_id );

			// 送信するユーザー・nonce・値を用意する（posted が無いケースは値を送らない）.
			wp_set_current_user( $case['user_id'] );
			if ( 'valid' === $case['nonce'] ) {
				$_POST['noncename__fields'] = wp_create_nonce( $nonce_action );
			} elseif ( 'other' === $case['nonce'] ) {
				$_POST['noncename__fields'] = wp_create_nonce( 'other_action' );
			}
			if ( array_key_exists( 'posted', $case ) ) {
				$_POST[ self::FIELD_KEY ] = wp_slash( $case['posted'] );
			}

			VK_Custom_Field_Builder::save_cf_value( $fields );

			$this->assertSame( $case['expected'], get_post_meta( $post_id, self::FIELD_KEY, true ), $case['test_condition_name'] );
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}

	/**
	 * 入れ子配列の各フィールドを形式に応じて整え、画像 ID と URL も保持する.
	 *
	 * @return void
	 */
	public function test_save_cf_value_sanitizes_nested_values_by_field_type() {
		$admin_id   = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$field_key  = 'cfb_test_nested_values';
		$fields     = array(
			$field_key => array(
				'nested' => array(
					'textarea'  => array( 'type' => 'textarea' ),
					'url'       => array( 'type' => 'url' ),
					'plain'     => array( 'type' => 'text' ),
					'image_id'  => array( 'type' => 'image' ),
					'image_url' => array( 'type' => 'image' ),
				),
			),
		);
		$test_cases = array(
			array(
				'test_condition_name' => '入れ子の textarea・URL・テキスト・画像 ID・画像 URL を送信 => 各形式に応じた値が保存される',
				'posted'              => array(
					'nested' => array(
						'textarea'  => '<strong>説明</strong>',
						'url'       => 'https://example.com/page',
						'plain'     => '<b>通常テキスト</b>',
						'image_id'  => '0012',
						'image_url' => '  https://example.com/image.png  ',
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => '<strong>説明</strong>',
						'url'       => 'https://example.com/page',
						'plain'     => '通常テキスト',
						'image_id'  => '12',
						'image_url' => 'https://example.com/image.png',
					),
				),
			),
			array(
				'test_condition_name' => '不正な URL・画像値を含む入れ子配列を送信 => 該当する値は空になる',
				'posted'              => array(
					'nested' => array(
						'textarea'  => '<em>補足</em>',
						'url'       => 'javascript:alert(1)',
						'plain'     => '通常テキスト',
						'image_id'  => 'not-an-id',
						'image_url' => 'ftp://example.com/image.png',
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => '<em>補足</em>',
						'url'       => '',
						'plain'     => '通常テキスト',
						'image_id'  => '',
						'image_url' => '',
					),
				),
			),
			array(
				'test_condition_name' => '空文字と画像 ID 0 を送信 => 空文字が保存される',
				'posted'              => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => '',
						'plain'     => '',
						'image_id'  => '0',
						'image_url' => '',
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => '',
						'plain'     => '',
						'image_id'  => '',
						'image_url' => '',
					),
				),
			),
			array(
				'test_condition_name' => '入れ子の値が長さの上限（2048 バイト）を超える場合 => textarea は切られず、テキストは 2048 バイトに切り詰められ、URL・画像 URL は空になる',
				'posted'              => array(
					'nested' => array(
						'textarea'  => str_repeat( 'a', 3000 ),
						'url'       => 'https://example.com/' . str_repeat( 'a', 2029 ),
						'plain'     => str_repeat( 'a', 2049 ),
						'image_id'  => '12',
						'image_url' => 'https://example.com/' . str_repeat( 'a', 2029 ),
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => str_repeat( 'a', 3000 ),
						'url'       => '',
						'plain'     => str_repeat( 'a', 2048 ),
						'image_id'  => '12',
						'image_url' => '',
					),
				),
			),
			array(
				'test_condition_name' => '整形すると空白が %20 に変わり 2050 バイトになる URL（整形前は上限ちょうどの 2048 バイト）を送信 => URL・画像 URL は空になる',
				'posted'              => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => 'https://example.com/' . str_repeat( 'a', 2026 ) . ' b',
						'plain'     => '',
						'image_id'  => '12',
						'image_url' => 'https://example.com/' . str_repeat( 'a', 2026 ) . ' b',
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => '',
						'plain'     => '',
						'image_id'  => '12',
						'image_url' => '',
					),
				),
			),
			array(
				'test_condition_name' => '整形すると空白が %20 に変わりちょうど上限（2048 バイト）になる URL（整形前は 2046 バイト）を送信 => URL・画像 URL は %20 に置き換えて保たれる',
				'posted'              => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => 'https://example.com/' . str_repeat( 'a', 2024 ) . ' b',
						'plain'     => '',
						'image_id'  => '12',
						'image_url' => 'https://example.com/' . str_repeat( 'a', 2024 ) . ' b',
					),
				),
				'expected'            => array(
					'nested' => array(
						'textarea'  => '',
						'url'       => 'https://example.com/' . str_repeat( 'a', 2024 ) . '%20b',
						'plain'     => '',
						'image_id'  => '12',
						'image_url' => 'https://example.com/' . str_repeat( 'a', 2024 ) . '%20b',
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			update_post_meta( $post_id, $field_key, array( 'old' ) );

			global $post;
			$post = get_post( $post_id );

			wp_set_current_user( $admin_id );
			$_POST['noncename__fields'] = wp_create_nonce( 'vk_custom_field_builder' );
			$_POST[ $field_key ]        = wp_slash( $case['posted'] );

			VK_Custom_Field_Builder::save_cf_value( $fields );

			$this->assertSame(
				$case['expected'],
				get_post_meta( $post_id, $field_key, true ),
				$case['test_condition_name']
			);
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}

	/**
	 * type が image のときは添付 ID と http / https の URL を保存し、それ以外（整形の前後どちらかで長さが上限を超える URL を含む）は空にする.
	 *
	 * @return void
	 */
	public function test_save_cf_value_image_id() {
		$admin_id  = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$field_key = 'cfb_test_image';
		$fields    = array(
			$field_key => array(
				'label'       => 'Image',
				'type'        => 'image',
				'description' => '',
				'required'    => false,
			),
		);

		// 長さがちょうど上限（2048 バイト）の URL と、1 バイト超える URL.
		$max_length_url  = 'https://example.com/' . str_repeat( 'a', 2028 );
		$over_length_url = $max_length_url . 'a';
		$this->assertSame( 2048, strlen( $max_length_url ), 'テスト用の URL がちょうど 2048 バイトであること' );

		// 空白を 1 つ含み、整形すると空白が %20 に変わって 2 バイト長くなる URL（整形前 2048 バイトと 2046 バイト）.
		$escaped_over_length_url = 'https://example.com/' . str_repeat( 'a', 2026 ) . ' b';
		$escaped_max_length_url  = 'https://example.com/' . str_repeat( 'a', 2024 ) . ' b';
		$this->assertSame( 2048, strlen( $escaped_over_length_url ), 'テスト用の空白を含む URL が整形前にちょうど 2048 バイトであること' );
		$this->assertSame( 2046, strlen( $escaped_max_length_url ), 'テスト用の空白を含む URL が整形前に 2046 バイトであること' );

		$cases = array(
			'数値 ID は保存される' => array( '12', '12' ),
			'数値の文字列 ID は保存される' => array( '0012', '12' ),
			'0 は空になる' => array( '0', '' ),
			'空は空になる' => array( '', '' ),
			'URL は消えずに保たれる（変更前は空になっていた）' => array( 'https://example.com/logo.png', 'https://example.com/logo.png' ),
			'http の URL も保たれる' => array( 'http://example.com/a/logo.png?v=1', 'http://example.com/a/logo.png?v=1' ),
			'前後の空白は除いて URL が保たれる' => array( '  https://example.com/logo.png  ', 'https://example.com/logo.png' ),
			'%20 を含む URL は %20 のまま残る' => array( 'https://example.com/my%20logo.png', 'https://example.com/my%20logo.png' ),
			'URL でない文字列は空になる' => array( 'not-an-id', '' ),
			'script タグは空になる' => array( '<script>alert(1)</script>', '' ),
			'javascript: は空になる' => array( 'javascript:alert(1)', '' ),
			'ftp は空になる' => array( 'ftp://example.com/logo.png', '' ),
			'スキーム省略（//host）は空になる' => array( '//example.com/logo.png', '' ),
			'改行を含む値は空になる' => array( "https://example.com/a\nb.png", '' ),
			'タブを含む値は空になる' => array( "https://example.com/a\tb.png", '' ),
			'<> を含むホストは空になる' => array( 'https://<>/logo.png', '' ),
			'長さがちょうど上限（2048 バイト）の URL は保たれる' => array( $max_length_url, $max_length_url ),
			'長さが上限を 1 バイト超える URL（2049 バイト）は空になる' => array( $over_length_url, '' ),
			'整形すると 2050 バイトになる URL（整形前は上限ちょうどの 2048 バイト）は空になる' => array( $escaped_over_length_url, '' ),
			'整形するとちょうど上限（2048 バイト）になる URL（整形前は 2046 バイト）は %20 に置き換えて保たれる' => array( $escaped_max_length_url, 'https://example.com/' . str_repeat( 'a', 2024 ) . '%20b' ),
		);

		foreach ( $cases as $label => list( $posted, $expected ) ) {
			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			update_post_meta( $post_id, $field_key, '99' );

			global $post;
			$post = get_post( $post_id );

			wp_set_current_user( $admin_id );
			$_POST['noncename__fields'] = wp_create_nonce( 'vk_custom_field_builder' );
			$_POST[ $field_key ]        = wp_slash( $posted );

			VK_Custom_Field_Builder::save_cf_value( $fields );

			$this->assertSame( $expected, get_post_meta( $post_id, $field_key, true ), $label );
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}

	/**
	 * Flexible Table の入れ子配列で textarea・URL・通常テキストを形式に応じて整える.
	 *
	 * @return void
	 */
	public function test_flexible_table_save_cf_value_sanitizes_nested_values_by_field_type() {
		$admin_id   = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$field      = 'cfb_test_flexible_nested_values';
		$fields     = array(
			'field_name' => $field,
			'items'      => array(
				'textarea' => array( 'type' => 'textarea' ),
				'url'      => array( 'type' => 'url' ),
				'plain'    => array( 'type' => 'text' ),
			),
		);
		$test_cases = array(
			array(
				'test_condition_name' => 'textarea・URL・通常テキストの行を送信 => 各形式に応じた値が保存される',
				'posted'              => array(
					0 => array(
						'textarea' => '<strong>説明</strong>',
						'url'      => 'https://example.com/first',
						'plain'    => '<b>通常テキスト</b>',
					),
				),
				'expected'            => array(
					0 => array(
						'textarea' => '<strong>説明</strong>',
						'url'      => 'https://example.com/first',
						'plain'    => '通常テキスト',
					),
				),
			),
			array(
				'test_condition_name' => '複数行と不正な URL を送信 => 全行がサニタイズされ、不正な URL は空になる',
				'posted'              => array(
					0 => array(
						'textarea' => '<em>補足</em>',
						'url'      => 'javascript:alert(1)',
						'plain'    => '行 1',
					),
					1 => array(
						'textarea' => '<strong>詳細</strong>',
						'url'      => 'https://example.com/second',
						'plain'    => '<i>行 2</i>',
					),
				),
				'expected'            => array(
					0 => array(
						'textarea' => '<em>補足</em>',
						'url'      => '',
						'plain'    => '行 1',
					),
					1 => array(
						'textarea' => '<strong>詳細</strong>',
						'url'      => 'https://example.com/second',
						'plain'    => '行 2',
					),
				),
			),
			array(
				'test_condition_name' => '全項目が空の行を送信 => 空の行が削除される',
				'posted'              => array(
					0 => array(
						'textarea' => '',
						'url'      => '',
						'plain'    => '',
					),
				),
				'expected'            => array(),
			),
			array(
				'test_condition_name' => 'type という列と不正な URL を送信 => type 列を保持し URL は空になる',
				'items'               => array(
					'type' => array( 'type' => 'text' ),
					'url'  => array( 'type' => 'url' ),
				),
				'posted'              => array(
					0 => array(
						'type' => '<strong>列名</strong>',
						'url'  => 'javascript:alert(1)',
					),
				),
				'expected'            => array(
					0 => array(
						'type' => '列名',
						'url'  => '',
					),
				),
			),
			array(
				'test_condition_name' => '整数キーの列と不正な URL を行 0 で送信 => 列ごとの形式で処理され URL は空になる',
				'items'               => array(
					0     => array( 'type' => 'textarea' ),
					'url' => array( 'type' => 'url' ),
				),
				'posted'              => array(
					0 => array(
						0     => '<strong>説明</strong>',
						'url' => 'javascript:alert(1)',
					),
				),
				'expected'            => array(
					0 => array(
						0     => '<strong>説明</strong>',
						'url' => '',
					),
				),
			),
			array(
				'test_condition_name' => 'URL 列に長さがちょうど上限（2048 バイト）の URL と、1 バイト超える URL を送信 => 上限ちょうどは保たれ、超えた URL は空になる',
				'items'               => array(
					'plain' => array( 'type' => 'text' ),
					'url'   => array( 'type' => 'url' ),
				),
				'posted'              => array(
					0 => array(
						'plain' => '行 1',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2028 ),
					),
					1 => array(
						'plain' => '行 2',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2029 ),
					),
				),
				'expected'            => array(
					0 => array(
						'plain' => '行 1',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2028 ),
					),
					1 => array(
						'plain' => '行 2',
						'url'   => '',
					),
				),
			),
			array(
				'test_condition_name' => 'URL 列に、整形すると空白が %20 に変わりちょうど上限（2048 バイト）になる URL と、2050 バイトになる URL を送信 => 上限以内は %20 に置き換えて保たれ、超えた URL は空になる',
				'items'               => array(
					'plain' => array( 'type' => 'text' ),
					'url'   => array( 'type' => 'url' ),
				),
				'posted'              => array(
					0 => array(
						'plain' => '行 1',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2024 ) . ' b',
					),
					1 => array(
						'plain' => '行 2',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2026 ) . ' b',
					),
				),
				'expected'            => array(
					0 => array(
						'plain' => '行 1',
						'url'   => 'https://example.com/' . str_repeat( 'a', 2024 ) . '%20b',
					),
					1 => array(
						'plain' => '行 2',
						'url'   => '',
					),
				),
			),
			array(
				'test_condition_name' => 'テキスト列に 3 バイトの日本語で 2048 バイト目が文字の途中に来る値、image 列に上限（2048 バイト）を超える値を送信 => テキストは文字の途中で切らずに 2046 バイト（682 文字）に切り詰められ、image は空になる',
				'items'               => array(
					'plain' => array( 'type' => 'text' ),
					'image' => array( 'type' => 'image' ),
				),
				'posted'              => array(
					0 => array(
						'plain' => str_repeat( 'あ', 700 ),
						'image' => 'https://example.com/' . str_repeat( 'a', 2029 ),
					),
				),
				'expected'            => array(
					0 => array(
						'plain' => str_repeat( 'あ', 682 ),
						'image' => '',
					),
				),
			),
			array(
				'test_condition_name' => 'file 列に長さが上限（2048 バイト）を 1 バイト超える https の URL を送信 => 切り詰めずに空になる',
				'items'               => array(
					'plain' => array( 'type' => 'text' ),
					'file'  => array( 'type' => 'file' ),
				),
				'posted'              => array(
					0 => array(
						'plain' => '行 1',
						'file'  => 'https://example.com/' . str_repeat( 'a', 2029 ),
					),
				),
				'expected'            => array(
					0 => array(
						'plain' => '行 1',
						'file'  => '',
					),
				),
			),
			array(
				'test_condition_name' => 'image 列に 0012 を送信 => 文字列 0012 が保持される',
				'items'               => array(
					'image' => array( 'type' => 'image' ),
				),
				'posted'              => array(
					0 => array( 'image' => '0012' ),
				),
				'expected'            => array(
					0 => array( 'image' => '0012' ),
				),
			),
			array(
				'test_condition_name' => '行のキーに記号を含む行を送信 => キーはそのまま、値は整えて保存される',
				'posted'              => array(
					'1"><b>x' => array(
						'textarea' => '<strong>説明</strong>',
						'url'      => 'https://example.com/',
						'plain'    => '<b>通常</b>',
					),
				),
				'expected'            => array(
					'1"><b>x' => array(
						'textarea' => '<strong>説明</strong>',
						'url'      => 'https://example.com/',
						'plain'    => '通常',
					),
				),
			),
			array(
				'test_condition_name' => 'textarea 列に太字・改行・リンクを含む HTML を送信 => そのまま保存される',
				'posted'              => array(
					0 => array(
						'textarea' => '<strong>太字</strong><br><a href="https://example.com/">link</a>',
						'url'      => '',
						'plain'    => '行 1',
					),
				),
				'expected'            => array(
					0 => array(
						'textarea' => '<strong>太字</strong><br><a href="https://example.com/">link</a>',
						'url'      => '',
						'plain'    => '行 1',
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$fields['items'] = isset( $case['items'] ) ? $case['items'] : array(
				'textarea' => array( 'type' => 'textarea' ),
				'url'      => array( 'type' => 'url' ),
				'plain'    => array( 'type' => 'text' ),
			);

			$_POST   = array();
			$post_id = self::factory()->post->create( array( 'post_author' => $admin_id ) );
			update_post_meta( $post_id, $field, array( 'old' ) );

			global $post;
			$post = get_post( $post_id );

			wp_set_current_user( $admin_id );
			$_POST[ 'noncename__' . $field ] = wp_create_nonce( 'vk_custom_field_flexible_table' );
			$_POST[ $field ]                 = wp_slash( $case['posted'] );

			VK_Custom_Field_Builder_Flexible_Table::save_cf_value( $fields );

			$this->assertSame(
				$case['expected'],
				get_post_meta( $post_id, $field, true ),
				$case['test_condition_name']
			);
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}
}
