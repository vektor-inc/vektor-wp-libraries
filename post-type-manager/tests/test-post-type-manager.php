<?php
/**
 * Class VK_Post_Type_Manager_Test
 *
 * @package vektor-inc/vektor-wp-libraries
 */

if ( ! class_exists( 'VK_Post_Type_Manager' ) ) {
	require_once __DIR__ . '/../package/class.post-type-manager.php';
}

/**
 * 投稿タイプ設定の保存処理のテスト.
 */
class VK_Post_Type_Manager_Test extends WP_UnitTestCase {

	/**
	 * 投稿タイプと管理者の編集権限を用意する.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		VK_Post_Type_Manager::add_post_type_post_type_manage();
		VK_Post_Type_Manager::add_cap_post_type_manage();
	}

	/**
	 * 保存処理 save_cf_value() が nonce と投稿の編集権限を確認したうえで、送信された値を整形して保存することを確認する.
	 *
	 * @return void
	 */
	public function test_save_cf_value() {
		$admin_id  = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$author_id = self::factory()->user->create( array( 'role' => 'author' ) );

		$nonce_action = 'vk_post_type_manager';
		$saved        = 'event';

		$test_cases = array(
			array(
				'test_condition_name' => '管理者が nonce 付きで event2 を送信 => event2 が保存される',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'posted'              => 'event2',
				'expected'            => 'event2',
			),
			array(
				'test_condition_name' => '管理者が nonce 付きで大文字混じりの ID を送信 => sanitize_key 後の値が保存される',
				'user_id'             => $admin_id,
				'nonce'               => 'valid',
				'posted'              => 'Event_Type',
				'expected'            => 'event_type',
			),
			array(
				'test_condition_name' => '投稿者（投稿タイプ設定の編集権限なし）が nonce 付きで送信 => 保存されず既存の値のまま',
				'user_id'             => $author_id,
				'nonce'               => 'valid',
				'posted'              => 'event2',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce が無い => 保存されず既存の値のまま',
				'user_id'             => $admin_id,
				'nonce'               => 'none',
				'posted'              => 'event2',
				'expected'            => $saved,
			),
			array(
				'test_condition_name' => 'nonce の用途名が違う => 保存されず既存の値のまま',
				'user_id'             => $admin_id,
				'nonce'               => 'other',
				'posted'              => 'event2',
				'expected'            => $saved,
			),
		);

		foreach ( $test_cases as $case ) {
			$_POST = array();

			$post_id = self::factory()->post->create(
				array(
					'post_type'   => 'post_type_manage',
					'post_status' => 'publish',
					'post_author' => $admin_id,
				)
			);
			update_post_meta( $post_id, 'veu_post_type_id', $saved );

			wp_set_current_user( $case['user_id'] );
			if ( 'valid' === $case['nonce'] ) {
				$_POST['noncename__post_type_manager'] = wp_create_nonce( $nonce_action );
			} elseif ( 'other' === $case['nonce'] ) {
				$_POST['noncename__post_type_manager'] = wp_create_nonce( 'other_action' );
			}

			$_POST['veu_post_type_id'] = wp_slash( $case['posted'] );

			VK_Post_Type_Manager::save_cf_value( $post_id );

			$this->assertSame( $case['expected'], get_post_meta( $post_id, 'veu_post_type_id', true ), $case['test_condition_name'] );
		}

		$_POST = array();
		wp_set_current_user( 0 );
	}
}
