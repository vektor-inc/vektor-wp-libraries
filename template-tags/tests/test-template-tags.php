<?php
/**
 * Class TemplateTagsTest
 *
 * @package Vk_All_In_One_Expansion_Unit
 */
 /*
 cd /app
 bash setup-phpunit.sh
 source ~/.bashrc
 cd $(wp plugin path --dir vk-all-in-one-expansion-unit)
 phpunit
 */


class TemplateTagsTest extends WP_UnitTestCase {

	function test_vk_the_post_type_check_list_saved_array_convert() {

		$tests = array(
			array(
				'option'  => array(
					'post' => true,
					'info' => '',
				),
				'correct' => array( 'post' ),
			),
			array(
				'option'  => array(
					'post' => true,
					'info' => true,
				),
				'correct' => array( 'post', 'info' ),
			),
			array(
				'option'  => array(
					'post' => 'true',
					'info' => true,
				),
				'correct' => array( 'post', 'info' ),
			),
		);

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'test_vk_the_post_type_check_list_saved_array_convert' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $tests as $key => $test_value ) {
			update_option( 'vkExUnit_Ads', $test_value['option'] );

			$return = vk_the_post_type_check_list_saved_array_convert( $test_value['option'] );

			// PHPunit
			$this->assertEquals( $test_value['correct'], $return );
			print PHP_EOL;
			// 帰り値が配列だから print してもエラーになるだけなのでコメントアウト
			// print 'return    :' . $return. PHP_EOL;
			// print 'correct   :' . $test_value['correct'] . PHP_EOL;
		}
	}


	/**
	 * vk_get_post_type() のテスト。
	 *
	 * - $_SERVER['REQUEST_URI'] が未設定（WP-CLI / cron 実行時など）でも、
	 *   strpos() へ null が渡って PHP の警告（E_WARNING / E_DEPRECATED）が
	 *   発生しないこと。
	 * - メインクエリの post_type が配列で指定された場合（pre_get_posts で
	 *   array( 'event', 'page' ) のように set された場合など、ExUnit#1375）に、
	 *   後続処理が文字列前提のため "Array to string conversion" 警告が
	 *   出ないよう、先頭要素へ正規化されること。
	 * - 管理画面の URL の post_type / post に配列が付いた場合に、配列が
	 *   sanitize_key() へ渡らず（WordPress 5.8 以前 + PHP 8.0 以上では
	 *   TypeError になる）、指定が無いときと同じ結果になること。
	 *   文字列で付いた通常の URL では、返る投稿タイプが変わらないこと。
	 *
	 * を、同じ前提（$_SERVER['REQUEST_URI'] の状態・$_GET の状態・
	 * $wp_query->query_vars['post_type'] の状態）をセットにした条件配列で
	 * まとめて確認する。
	 */
	function test_vk_get_post_type() {

		global $wp_query, $post;

		// 管理画面の URL の post に付ける、通常の投稿（投稿タイプ post）を作成
		$normal_post_id = self::factory()->post->create( array( 'post_type' => 'post' ) );

		// 配列を absint() に通すと 1 になるため、ID 1 の投稿を拾わないことを確かめられるよう、ID 1 の固定ページを用意
		if ( get_post( 1 ) ) {
			wp_update_post(
				array(
					'ID'        => 1,
					'post_type' => 'page',
				)
			);
		} else {
			wp_insert_post(
				array(
					'import_id'   => 1,
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => 'ID 1 の固定ページ',
				)
			);
		}
		$this->assertSame( 'page', get_post_type( 1 ), 'テストの前提: ID 1 の投稿が固定ページとして存在すること' );

		$tests = array(
			array(
				'test_condition_name' => 'REQUEST_URI が通常のURL文字列、post_type が単一の文字列の場合 => 警告なく slug が post で取得できる',
				'conditions'          => array(
					'request_uri'         => '/',
					'unset_request_uri'   => false,
					'post_type_query_var' => 'post',
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => 'REQUEST_URI が空文字の場合 => 警告なく slug が post で取得できる',
				'conditions'          => array(
					'request_uri'         => '',
					'unset_request_uri'   => false,
					'post_type_query_var' => 'post',
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => 'REQUEST_URI が未設定（isset() が false）の場合 => 警告なく slug が post で取得できる',
				'conditions'          => array(
					'request_uri'         => '',
					'unset_request_uri'   => true,
					'post_type_query_var' => 'post',
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => 'メインクエリの post_type が複数指定の配列の場合 => 配列の先頭要素が文字列として取得できる',
				'conditions'          => array(
					'request_uri'         => '/',
					'unset_request_uri'   => false,
					'post_type_query_var' => array( 'event', 'page' ),
				),
				'expected_slug'       => 'event',
			),
			array(
				// post_type_query_var が空配列は PHP では falsy なため、外側の
				// isset( ... ) && $wp_query->query_vars['post_type'] の判定を通らず、
				// else 側（post_type クエリ無しの扱い）へ落ちて 'post' にフォールバックする。
				// is_array() 判定内の reset() は、この経路（空配列）では実行されない
				// （真値を持つ配列が渡された場合にのみ到達する分岐）。
				'test_condition_name' => 'メインクエリの post_type が空配列の場合 => 空配列は falsy なので判定を通らず post にフォールバックする',
				'conditions'          => array(
					'request_uri'         => '/',
					'unset_request_uri'   => false,
					'post_type_query_var' => array(),
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => '管理画面で URL の post_type が文字列の場合 => その投稿タイプが取得できる',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/edit.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array( 'post_type' => 'page' ),
				),
				'expected_slug'       => 'page',
			),
			array(
				'test_condition_name' => '管理画面で URL の post_type が大文字や記号を含む文字列の場合 => キーに使える文字だけの小文字で取得できる',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/edit.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array( 'post_type' => 'My_Type-1<b>' ),
				),
				'expected_slug'       => 'my_type-1b',
			),
			array(
				'test_condition_name' => '管理画面で URL の post_type（文字列）と post の両方がある場合 => post_type の値が優先して取得できる',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/post-new.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array(
						'post_type' => 'page',
						'post'      => (string) $normal_post_id,
					),
				),
				'expected_slug'       => 'page',
			),
			array(
				'test_condition_name' => '管理画面で URL に post（文字列の投稿 ID）だけがある場合 => その投稿の投稿タイプが取得できる',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/post.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array( 'post' => (string) $normal_post_id ),
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => '管理画面で URL に post_type も post も無い場合 => 投稿タイプは取得できず false のまま',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/index.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array(),
				),
				'expected_slug'       => false,
			),
			array(
				'test_condition_name' => '管理画面で URL の post_type が配列の場合 => sanitize_key() に渡さず、post_type の指定が無いときと同じ false',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/edit.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array( 'post_type' => array( 'post' ) ),
				),
				'expected_slug'       => false,
			),
			array(
				'test_condition_name' => '管理画面で URL の post_type が配列で post（文字列の投稿 ID）もある場合 => post_type の指定が無いときと同じく post の投稿タイプが取得できる',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/post.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array(
						'post_type' => array( 'page' ),
						'post'      => (string) $normal_post_id,
					),
				),
				'expected_slug'       => 'post',
			),
			array(
				'test_condition_name' => '管理画面で URL の post が配列の場合 => ID 1 の投稿を拾わず、post の指定が無いときと同じ false',
				'conditions'          => array(
					'request_uri'         => '/wp-admin/post.php',
					'unset_request_uri'   => false,
					'post_type_query_var' => '',
					'get'                 => array( 'post' => array( (string) $normal_post_id ) ),
				),
				'expected_slug'       => false,
			),
		);

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'test_vk_get_post_type' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;

		// テスト前の global $wp_query / $post / $_SERVER['REQUEST_URI'] / $_GET を退避しておく
		$original_wp_query    = $wp_query;
		$original_post        = $post;
		$original_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : null;
		$original_get         = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- テスト後に元へ戻すための退避で、フォームの値は処理しない。

		// sanitize_key() に文字列以外が渡された回数を観測する（最新の WordPress は本体が配列を空文字にするため、返り値だけでは渡ったかどうかを見分けられない）
		$non_string_sanitize_key_args = array();
		/**
		 * 文字列以外が sanitize_key() に渡されたら記録する。
		 *
		 * @param string $sanitized_key sanitize_key() が整えた後の値。
		 * @param mixed  $raw_key       sanitize_key() に渡された元の値。
		 * @return string 整えた後の値をそのまま返す。
		 */
		$sanitize_key_observer = function ( $sanitized_key, $raw_key ) use ( &$non_string_sanitize_key_args ) {
			if ( ! is_string( $raw_key ) ) {
				$non_string_sanitize_key_args[] = $raw_key;
			}
			return $sanitized_key;
		};
		add_filter( 'sanitize_key', $sanitize_key_observer, 10, 2 );

		try {
			foreach ( $tests as $test_value ) {
				$conditions = $test_value['conditions'];

				if ( $conditions['unset_request_uri'] ) {
					unset( $_SERVER['REQUEST_URI'] );
				} else {
					$_SERVER['REQUEST_URI'] = $conditions['request_uri'];
				}

				// URL のクエリ（$_GET）を条件どおりに設定する。指定が無いケースは空にする
				$_GET = isset( $conditions['get'] ) ? $conditions['get'] : array();

				// 個別の投稿を指しておらず、メインクエリの post_type だけで判定させたいので $post は空にする
				$post                              = null;
				$wp_query                          = new WP_Query();
				$wp_query->query_vars['post_type'] = $conditions['post_type_query_var'];

				// PHP の警告・非推奨通知が発生したかどうかを検知するための一時的なエラーハンドラ
				$caught_warnings              = array();
				$non_string_sanitize_key_args = array();
				set_error_handler(
					function ( $errno, $errstr ) use ( &$caught_warnings ) {
						$caught_warnings[] = $errstr;
						return true;
					},
					E_WARNING | E_DEPRECATED | E_NOTICE
				);

				try {
					$post_type = vk_get_post_type();
				} finally {
					restore_error_handler();
				}

				// PHPunit
				$this->assertSame( array(), $caught_warnings, $test_value['test_condition_name'] . '（警告が発生しないこと）' );
				$this->assertSame( array(), $non_string_sanitize_key_args, $test_value['test_condition_name'] . '（sanitize_key() に文字列以外を渡さないこと）' );
				$this->assertSame( $test_value['expected_slug'], $post_type['slug'], $test_value['test_condition_name'] );
				print PHP_EOL;
				print $test_value['test_condition_name'] . PHP_EOL;
			}
		} finally {
			// フィルター / global / $_SERVER / $_GET の状態を元に戻す（途中で失敗しても他のテストへ持ち越さない）
			remove_filter( 'sanitize_key', $sanitize_key_observer, 10 );
			$_GET     = $original_get;
			$wp_query = $original_wp_query;
			$post     = $original_post;
			if ( null === $original_request_uri ) {
				unset( $_SERVER['REQUEST_URI'] );
			} else {
				$_SERVER['REQUEST_URI'] = $original_request_uri;
			}
		}
	}


	/**
	 * vk_sanitize_array() のテスト。
	 * 配列以外の値を渡した場合に「Undefined variable $return」警告を
	 * 出さずに空配列を返すこと（$return の初期化を is_array() 判定の
	 * 外へ出した修正）を確認する。
	 */
	function test_vk_sanitize_array() {

		$tests = array(
			array(
				'test_condition_name' => '連想配列を渡した場合 => wp_kses_post を通した配列で取得できる',
				'input'               => array(
					'a' => '<b>bold</b>',
					'b' => 'plain',
				),
				'expected'            => array(
					'a' => '<b>bold</b>',
					'b' => 'plain',
				),
			),
			array(
				'test_condition_name' => '空配列を渡した場合 => 空配列のまま取得できる',
				'input'               => array(),
				'expected'            => array(),
			),
			array(
				'test_condition_name' => '配列以外（文字列）を渡した場合 => 警告を出さずに空配列を取得できる',
				'input'               => 'not-an-array',
				'expected'            => array(),
			),
		);

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'test_vk_sanitize_array' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;

		foreach ( $tests as $test_value ) {

			// PHP の警告・非推奨通知が発生したかどうかを検知するための一時的なエラーハンドラ
			$caught_warnings = array();
			set_error_handler(
				function ( $errno, $errstr ) use ( &$caught_warnings ) {
					$caught_warnings[] = $errstr;
					return true;
				},
				E_WARNING | E_DEPRECATED | E_NOTICE
			);

			$actual = vk_sanitize_array( $test_value['input'] );

			restore_error_handler();

			// PHPunit
			$this->assertSame( array(), $caught_warnings, $test_value['test_condition_name'] . '（警告が発生しないこと）' );
			$this->assertEquals( $test_value['expected'], $actual, $test_value['test_condition_name'] );
			print PHP_EOL;
			print $test_value['test_condition_name'] . PHP_EOL;
		}
	}

}
