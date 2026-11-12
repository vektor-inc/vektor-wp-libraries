<?php
/**
 * Class VK_Media_Posts_BS4_Test
 *
 * @package VK_Media_Posts_BS4
 */

class VK_Media_Posts_BS4_Test extends WP_UnitTestCase {

	/**
	 * テスト用の投稿など登録
	 */
	public static function set_test_env_data() {
		register_post_type(
			'event',
			array(
				'label'       => 'event',
				'has_archive' => true,
				'public'      => true,
			)
		);
		register_taxonomy(
			'event_cat',
			'event',
			array(
				'label'        => 'Event Category',
				'rewrite'      => array( 'slug' => 'event_cat' ),
				'hierarchical' => true,
			)
		);

		// Create test category.
		$catarr            = array(
			'cat_name' => 'test_category',
		);
		$return['cate_id'] = wp_insert_category( $catarr );

		$catarr                  = array(
			'cat_name'        => 'test_category_child',
			'category_parent' => $return['cate_id'],
		);
		$return['cate_child_id'] = wp_insert_category( $catarr );

		$catarr                    = array(
			'cat_name' => 'no_post_category',
		);
		$return['cate_no_post_id'] = wp_insert_category( $catarr );

		// Create test term.
		$args                    = array(
			'slug' => 'event_category_name',
		);
		$term_info               = wp_insert_term( 'event_category_name', 'event_cat', $args );
		$return['event_term_id'] = $term_info['term_id'];

		// Create test post.
		$post              = array(
			'post_title'    => 'test',
			'post_status'   => 'publish',
			'post_content'  => 'content',
			'post_category' => array( $return['cate_id'] ),
		);
		$return['post_id'] = wp_insert_post( $post );
		// 投稿にカテゴリー指定.
		wp_set_object_terms( $return['post_id'], 'test_category_child', 'category' );

		// Create test page.
		$post                     = array(
			'post_title'   => 'normal page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$return['normal_page_id'] = wp_insert_post( $post );

		$post = array(
			'post_title'   => 'child page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
			'post_parent'  => $return['normal_page_id'],

		);
		$return['child_page_id'] = wp_insert_post( $post );

		// Create test home page.
		$post                   = array(
			'post_title'   => 'post_top',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$return['home_page_id'] = wp_insert_post( $post );

		// Create test home page.
		$post                    = array(
			'post_title'   => 'front_page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$return['front_page_id'] = wp_insert_post( $post );

		// custom post type.
		$post                    = array(
			'post_title'   => 'event-test-post',
			'post_type'    => 'event',
			'post_status'  => 'publish',
			'post_content' => 'content',
		);
		$return['event_post_id'] = wp_insert_post( $post );
		// set event category to event post.
		wp_set_object_terms( $return['event_post_id'], 'event_category_name', 'event_cat' );

		return $return;
	}

	public function test_get_options_post_type() {

		$test_env_data = self::set_test_env_data();
		$option_field  = 'vk_post_type_archive';

		// 検索結果の1件目が「投稿」になるよう、この投稿にしか無い語をタイトルに持つ投稿を作成.
		wp_insert_post(
			array(
				'post_title'   => 'onlypostkeyword',
				'post_status'  => 'publish',
				'post_content' => 'content',
			)
		);

		$tests_array = array(
			// カスタム投稿タイプ event 改変なし.
			array(
				'option'     => array(),
				'target_url' => get_post_type_archive_link( 'event' ),
				'correct'    => false,
			),
			// カスタム投稿タイプ event 投稿タイプアーカイブ改変.
			array(
				'option'     => array(
					'event' => array(
						'layout' => 'card',
					),
				),
				'target_url' => get_post_type_archive_link( 'event' ),
				'correct'    => 'event',
			),
			// カスタム投稿タイプ event カスタム分類 event_cat改変.
			array(
				'option'     => array(
					'event' => array(
						'layout' => 'card',
					),
				),
				'target_url' => get_term_link( $test_env_data['event_term_id'], 'event_cat' ),
				'correct'    => 'event',
			),
			// 通常のキーワード検索 改変指定なし.
			array(
				'option'     => null,
				'target_url' => home_url( '/' ) . '?s=post',
				'correct'    => false,
			),
			// 通常のキーワード検索 改変指定あり.
			array(
				'option'     => array(
					'search' => array(
						'layout' => 'card-noborder',
					),
				),
				'target_url' => home_url( '/' ) . '?s=post_top',
				'correct'    => 'search',
			),
			// 通常のキーワード検索で1件目が投稿 投稿・検索ともにレイアウト改変指定あり → 投稿ではなく検索のレイアウト.
			array(
				'option'     => array(
					'post'   => array(
						'layout' => 'postListText',
					),
					'search' => array(
						'layout' => 'card-noborder',
					),
				),
				'target_url' => home_url( '/' ) . '?s=onlypostkeyword',
				'correct'    => 'search',
			),
			// 通常のキーワード検索で1件目が投稿 投稿のみレイアウト改変指定あり → 検索は Lightning 標準なので改変しない.
			array(
				'option'     => array(
					'post' => array(
						'layout' => 'postListText',
					),
				),
				'target_url' => home_url( '/' ) . '?s=onlypostkeyword',
				'correct'    => false,
			),
			// 投稿タイプ複数指定の検索 先頭の投稿タイプのレイアウト改変指定あり → 先頭の投稿タイプのレイアウト.
			array(
				'option'     => array(
					'event'  => array(
						'layout' => 'card',
					),
					'search' => array(
						'layout' => 'card-noborder',
					),
				),
				'target_url' => home_url( '/' ) . '?s=test&post_type[]=event&post_type[]=post',
				'correct'    => 'event',
			),
			// 投稿タイプを post, event の順で指定した検索 両方レイアウト改変指定あり → 指定順の先頭の post のレイアウト.
			array(
				'option'     => array(
					'post'  => array(
						'layout' => 'postListText',
					),
					'event' => array(
						'layout' => 'card',
					),
				),
				'target_url' => home_url( '/' ) . '?s=test&post_type[]=post&post_type[]=event',
				'correct'    => 'post',
			),
			// 投稿タイプ指定あり検索 指定投稿タイプのレイアウト改変指定あり 検索のレイアウト改変指定あり.
			array(
				'option'     => array(
					'event'  => array(
						'layout' => 'card',
					),
					'search' => array(
						'layout' => 'card-noborder',
					),
				),
				'target_url' => home_url( '/' ) . '?s=post_top&post_type=event',
				'correct'    => 'event',
			),
			// 投稿タイプ指定あり検索 指定投稿タイプのレイアウト改変指定なし 検索のレイアウト改変指定あり.
			// → 本当は event 返すはずだが、event のレイアウト情報登録がないので search を返す.
			array(
				'option'     => array(
					'search' => array(
						'layout' => 'card-noborder',
					),
				),
				'target_url' => home_url( '/' ) . '?s=post_top&post_type=event',
				'correct'    => 'search',
			),
		);

		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'VK_Media_Posts_BS4_Test' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		foreach ( $tests_array as $test ) {
			delete_option( $option_field );
			if ( isset( $test['option'] ) ) {
				update_option( $option_field, $test['option'] );
			}
			$this->go_to( $test['target_url'] );
			$return = VK_Media_Posts_BS4::is_loop_layout_change( false );
			print PHP_EOL;
			print $test['target_url'] . PHP_EOL;
			print 'return : ' . $return . PHP_EOL;
			print 'correct : ' . $test['correct'] . PHP_EOL;
			$this->assertEquals( $return, $test['correct'] );
		}
	}

	/**
	 * get_search_post_type_slug() が、検索結果の1件目ではなく
	 * 検索クエリの投稿タイプ指定から投稿タイプ名を返すかをテストする.
	 */
	public function test_get_search_post_type_slug() {

		self::set_test_env_data();

		// 検索結果の1件目が「投稿」になるよう、この投稿にしか無い語をタイトルに持つ投稿を作成.
		wp_insert_post(
			array(
				'post_title'   => 'onlypostkeyword',
				'post_status'  => 'publish',
				'post_content' => 'content',
			)
		);

		$test_cases = array(
			array(
				'test_condition_name' => '投稿タイプ指定なしの検索で1件目が投稿 => search',
				'target_url'          => home_url( '/' ) . '?s=onlypostkeyword',
				'expected'            => 'search',
			),
			array(
				'test_condition_name' => '投稿タイプ指定なしの検索で1件目が固定ページ => search',
				'target_url'          => home_url( '/' ) . '?s=post_top',
				'expected'            => 'search',
			),
			array(
				'test_condition_name' => '投稿タイプ event を指定した検索 => event',
				'target_url'          => home_url( '/' ) . '?s=test&post_type=event',
				'expected'            => 'event',
			),
			array(
				'test_condition_name' => '投稿タイプを複数指定した検索 => 先頭の event',
				'target_url'          => home_url( '/' ) . '?s=test&post_type[]=event&post_type[]=post',
				'expected'            => 'event',
			),
			array(
				'test_condition_name' => '投稿タイプを post, event の順で指定した検索 => 並べ替え後の event ではなく指定順の先頭の post',
				'target_url'          => home_url( '/' ) . '?s=test&post_type[]=post&post_type[]=event',
				'expected'            => 'post',
			),
			array(
				'test_condition_name' => '投稿タイプ指定なしで0件ヒットの検索 => search',
				'target_url'          => home_url( '/' ) . '?s=nohitkeyword',
				'expected'            => 'search',
			),
		);

		foreach ( $test_cases as $case ) {
			// テスト URL に移動して判定結果を確認.
			$this->go_to( $case['target_url'] );
			$actual = VK_Media_Posts_BS4::get_search_post_type_slug();
			$this->assertSame( $case['expected'], $actual, $case['test_condition_name'] );
		}
	}

	/**
	 * options_default() に 更新日表示オプション( display_modified )が
	 * 期待通りのデフォルト値で含まれているかをテストする.
	 */
	public function test_options_default() {

		// 取得したデフォルトオプション.
		$defaults = VK_Media_Posts_BS4::options_default();

		// テストの配列( キー => 期待するデフォルト値 ).
		$test_cases = array(
			array(
				'test_condition_name' => '更新日表示( display_modified )のデフォルトは非表示( false )',
				'key'                 => 'display_modified',
				'expected'            => false,
			),
			array(
				'test_condition_name' => '公開日表示( display_date )のデフォルトは表示( true )',
				'key'                 => 'display_date',
				'expected'            => true,
			),
		);

		foreach ( $test_cases as $case ) {
			// キーが存在する事を確認.
			$this->assertArrayHasKey( $case['key'], $defaults, $case['test_condition_name'] );
			// デフォルト値が期待通りである事を確認.
			$this->assertSame( $case['expected'], $defaults[ $case['key'] ], $case['test_condition_name'] );
		}
	}

	/**
	 * get_view() の出力で、display_modified の真偽に応じて
	 * 更新日のマークアップ( .modified )が出し分けされるかをテストする.
	 */
	public function test_get_view() {

		// テスト用の投稿を作成.
		$post_id = wp_insert_post(
			array(
				'post_title'   => 'test post for display_modified',
				'post_status'  => 'publish',
				'post_content' => 'content',
			)
		);
		$post    = get_post( $post_id );

		// 公開日・更新日のマークアップ判定に使う部分文字列.
		// card レイアウトでは published / modified の class が付与される.
		$published_markup = 'card-date published';
		$modified_markup  = 'card-date modified';

		// テストの配列.
		$test_cases = array(
			// 正常系1: 公開日・更新日ともに表示 => 両方のマークアップが出力される.
			array(
				'test_condition_name' => '公開日表示ON・更新日表示ON => 公開日と更新日の両方が出力される',
				'options'             => array(
					'layout'           => 'card',
					'display_date'     => true,
					'display_modified' => true,
				),
				'expected'            => array(
					'contains'     => array( $published_markup, $modified_markup ),
					'not_contains' => array(),
				),
			),
			// 正常系2: 更新日のみ非表示 => 更新日のマークアップは出力されない.
			array(
				'test_condition_name' => '公開日表示ON・更新日表示OFF => 更新日は出力されない',
				'options'             => array(
					'layout'           => 'card',
					'display_date'     => true,
					'display_modified' => false,
				),
				'expected'            => array(
					'contains'     => array( $published_markup ),
					'not_contains' => array( $modified_markup ),
				),
			),
			// 境界値: 公開日OFF・更新日ONで、更新日だけ出力される.
			array(
				'test_condition_name' => '公開日表示OFF・更新日表示ON => 更新日のみ出力される',
				'options'             => array(
					'layout'           => 'card',
					'display_date'     => false,
					'display_modified' => true,
				),
				'expected'            => array(
					'contains'     => array( $modified_markup ),
					'not_contains' => array( $published_markup ),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			// プラグインのデフォルトオプションに、テスト条件のオプションを上書きする.
			$options = array_merge( VK_Media_Posts_BS4::options_default(), $case['options'] );

			// 描画を実行.
			$html = \VektorInc\VK_Component\VK_Component_Posts::get_view( $post, $options );

			// 出力に含まれるべき文字列を確認.
			foreach ( $case['expected']['contains'] as $needle ) {
				$this->assertStringContainsString( $needle, $html, $case['test_condition_name'] );
			}
			// 出力に含まれてはいけない文字列を確認.
			foreach ( $case['expected']['not_contains'] as $needle ) {
				$this->assertStringNotContainsString( $needle, $html, $case['test_condition_name'] );
			}
		}

		// 後始末.
		wp_delete_post( $post_id, true );
	}
}
