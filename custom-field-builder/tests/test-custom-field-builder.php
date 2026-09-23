<?php
/**
 * Class VK_Custom_Field_Builder_Test
 *
 * @package vektor-inc/VK_Custom_Field_Builder
 */

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
	 * テストの前処理.
	 */
	public function setUp(): void {
		parent::setUp();
		VK_CFB_Injection_Probe::$woken = false;
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

		$this->assertMatchesRegularExpression( '/value="PART_TIME"[^>]*checked/', $form_html );
		$this->assertDoesNotMatchRegularExpression( '/value="FULL_TIME"[^>]*checked/', $form_html );
	}

	/**
	 * 投稿側に値が無い場合は共通設定側の値が使われる.
	 */
	public function test_form_table_falls_back_to_common_option_when_meta_is_empty() {
		global $post;

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$form_html = $this->render_form_table( array( self::FIELD_KEY => array( 'FULL_TIME' ) ) );

		$this->assertMatchesRegularExpression( '/value="FULL_TIME"[^>]*checked/', $form_html );
		$this->assertDoesNotMatchRegularExpression( '/value="PART_TIME"[^>]*checked/', $form_html );
	}
}
