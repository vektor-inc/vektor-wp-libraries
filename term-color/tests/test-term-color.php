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
	 * タームメタ登録処理が init 前の読み込みで init に予約されること.
	 *
	 * @return void
	 */
	public function test_term_meta_color_registers_init_callback_before_init() {
		global $wp_actions;

		$init_action_count   = $wp_actions['init'];
		$callback            = array( 'Vk_term_color', 'term_meta_color' );
		$registered_priority = has_action( 'init', $callback );

		// 他のテストが残したフックを外し、init 未発火の状態を再現する.
		if ( false !== $registered_priority ) {
			remove_action( 'init', $callback, $registered_priority );
		}

		try {
			unset( $wp_actions['init'] );

			$this->assertFalse(
				has_action( 'init', $callback ),
				'テスト開始前からタームメタ登録処理が init に登録されています.'
			);

			// init 前にクラスを読み込むと、登録処理は init まで保留される.
			new Vk_term_color();

			$this->assertTrue(
				false !== has_action( 'init', $callback ),
				'タームメタ登録処理が init に登録されていません.'
			);
		} finally {
			// 後続テストへ init の実行状態とフックを持ち越さない.
			remove_action( 'init', $callback );
			$wp_actions['init'] = $init_action_count;

			if ( false !== $registered_priority ) {
				add_action( 'init', $callback, $registered_priority );
			}
		}
	}

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
			array(
				'test_condition_name' => '末尾に改行を付けた値は空文字を返す（PCREの$は末尾改行の直前にもマッチするため）',
				'color'               => "abc\n",
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

	/**
	 * ターム色の保存処理 save_term_meta_color() が nonce とタームの編集権限を確認したうえで、送信された色を整形して保存することを確認する.
	 * 管理画面での保存と同じく wp_update_term() → edited_category フック経由（route = update）と、
	 * 新規ターム作成と同じく wp_insert_term() → create_category フック経由（route = insert）で呼び出し、フックの配線ごと検証する.
	 *
	 * @return void
	 */
	public function test_save_term_meta_color() {
		// カテゴリーの編集権限（manage_categories）は管理者にはあり、投稿者・購読者には無い.
		$admin_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$author_id     = self::factory()->user->create( array( 'role' => 'author' ) );
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );

		// nonce のアクション名はクラスファイルの basename( __FILE__ ) と一致させる必要がある.
		$nonce_action = 'class.term-color.php';

		// 各ケースの前に入れておく保存済みの値（sanitize_hex を通るため # は除かれて保存される）.
		$saved = '00ff00';

		$test_cases = array(
			array(
				'test_condition_name' => '管理者が nonce 付きで #ff0000 を送信 => ff0000 が保存される',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => '#ff0000',
				'expected'            => 'ff0000',
			),
			array(
				'test_condition_name' => '管理者が nonce 付きで # なしの ff0000 を送信 => ff0000 が保存される',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => 'ff0000',
				'expected'            => 'ff0000',
			),
			array(
				'test_condition_name' => '管理者が nonce 付きで 3 桁の #abc を送信 => abc が保存される',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => '#abc',
				'expected'            => 'abc',
			),
			array(
				'test_condition_name' => '投稿者（カテゴリーの編集権限なし）が nonce 付きで送信 => 保存されず既存の値のまま',
				'route'               => 'update',
				'user_id'             => $author_id,
				'nonce'               => 'valid',
				'term_color'          => '#ff0000',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => '購読者（カテゴリーの編集権限なし）が nonce 付きで送信 => 保存されず既存の値のまま',
				'route'               => 'update',
				'user_id'             => $subscriber_id,
				'nonce'               => 'valid',
				'term_color'          => '#ff0000',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce が無い => 保存されず既存の値のまま',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'none',
				'term_color'          => '#ff0000',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce のアクション名が違う => 保存されず既存の値のまま',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'other',
				'term_color'          => '#ff0000',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'term_color が送信されない => 既存の値のまま',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => null,
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => '配列を送信 => 警告を出さずに空文字が保存される',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => array( '#ff0000' ),
				'expected'            => '',
			),
			array(
				'test_condition_name' => '16進数カラーではない値を送信 => 空文字が保存される',
				'route'               => 'update',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => 'notacolor000',
				'expected'            => '',
			),
			array(
				'test_condition_name' => '管理者が nonce 付きで #ff0000 を送信して新規ターム作成 => 新規タームに ff0000 が保存される',
				'route'               => 'insert',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'term_color'          => '#ff0000',
				'expected'            => 'ff0000',
			),
			array(
				'test_condition_name' => '購読者（カテゴリーの編集権限なし）が nonce 付きで送信して新規ターム作成 => 保存されず空のまま',
				'route'               => 'insert',
				'user_id'             => $subscriber_id,
				'nonce'               => 'valid',
				'term_color'          => '#ff0000',
				'expected'            => '',
			),
		);

		foreach ( $test_cases as $case ) {
			// ターム作成時にも create_category 経由で save_term_meta_color() が走るため、前のケースの $_POST を先に空にする.
			$_POST = array();

			// update 経路は保存済みの値を持つタームを先に用意する（insert 経路は wp_insert_term() 自体が検証対象）.
			$term_id = 0;
			if ( 'update' === $case['route'] ) {
				$term_id = self::factory()->term->create( array( 'taxonomy' => 'category' ) );
				update_term_meta( $term_id, 'term_color', '#' . $saved );
			}

			// nonce は現在のユーザーに紐づくため、ユーザーを切り替えてから作る.
			wp_set_current_user( $case['user_id'] );
			if ( 'valid' === $case['nonce'] ) {
				$_POST['term_color_nonce'] = wp_create_nonce( $nonce_action );
			} elseif ( 'other' === $case['nonce'] ) {
				$_POST['term_color_nonce'] = wp_create_nonce( 'other_action' );
			}

			// WordPress が $_POST に付けるスラッシュを再現する.
			if ( null !== $case['term_color'] ) {
				$_POST['term_color'] = wp_slash( $case['term_color'] );
			}

			if ( 'insert' === $case['route'] ) {
				// 管理画面の「新規カテゴリーを追加」と同じく create_category フック経由で呼び出す.
				$inserted = wp_insert_term( uniqid( 'term-color-insert-' ), 'category' );
				$this->assertNotWPError( $inserted, $case['test_condition_name'] . '（前提: タームが作成できること）' );
				$term_id = $inserted['term_id'];
			} else {
				// 管理画面での保存と同じく edited_category フック経由で呼び出す.
				wp_update_term( $term_id, 'category', array( 'description' => 'updated' ) );
			}

			$this->assertSame( $case['expected'], get_term_meta( $term_id, 'term_color', true ), $case['test_condition_name'] );
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}

	/**
	 * ターム色の取得 get_term_color() が保存済みの値を # 付きで返し、# 付きで保存された旧い値でも同じ色を返すことを確認する.
	 *
	 * @return void
	 */
	public function test_get_term_color() {
		$test_cases = array(
			array(
				'test_condition_name' => '# なしで保存された値 => # 付きで返す',
				'saved'               => '00ff00',
				'expected'            => '#00ff00',
			),
			array(
				'test_condition_name' => '# 付きで保存された旧い値 => # を重ねずに同じ色を返す',
				'saved'               => '#00ff00',
				'expected'            => '#00ff00',
			),
			array(
				'test_condition_name' => '保存値が無い => 既定色を返す',
				'saved'               => null,
				'expected'            => '#999999',
			),
		);

		foreach ( $test_cases as $case ) {
			$term_id = self::factory()->term->create( array( 'taxonomy' => 'category' ) );

			// 旧い保存値（# 付き）を再現するため、sanitize_callback を通さずに保存する.
			if ( null !== $case['saved'] ) {
				$sanitize_callback = array( 'Vk_term_color', 'sanitize_hex' );
				remove_filter( 'sanitize_term_meta_term_color', $sanitize_callback );
				update_term_meta( $term_id, 'term_color', $case['saved'] );
				add_filter( 'sanitize_term_meta_term_color', $sanitize_callback, 10, 4 );
				$this->assertSame( $case['saved'], get_term_meta( $term_id, 'term_color', true ), $case['test_condition_name'] . '（前提: 保存値がそのまま入っていること）' );
			}

			$this->assertSame( $case['expected'], Vk_term_color::get_term_color( $term_id ), $case['test_condition_name'] );
		}
	}
}
