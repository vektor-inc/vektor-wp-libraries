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
}
