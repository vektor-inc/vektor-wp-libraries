<?php
/**
 * Class SampleTest
 *
 * @package Lightning_Media_Posts
 */

/**
 * Sample test case.
 */
class MediaPostsTest extends WP_UnitTestCase {

	function test_lmu_is_loop_layout_change_flag() {
		$test_array = array(
			// オプション値自体が保存されていない
			array(
				'options'   => null,
				'post_type' => 'post',
				'correct'   => false,
			),
			array(
				'options'   => array(
					'post' => 'default',
				),
				'post_type' => 'post',
				'correct'   => false,
			),
			array(
				'options'   => array(
					'post' => 'image_1st',
				),
				'post_type' => 'post',
				'correct'   => true,
			),

			array(
				'options'   => array(
					'author' => null,
				),
				'post_type' => 'author',
				'correct'   => false,
			),
			array(
				'options'   => array(
					'author' => 'image_1st',
				),
				'post_type' => 'author',
				'correct'   => true,
			),
		);
		//
		print PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		print 'Lightning_Media_Posts' . PHP_EOL;
		print '------------------------------------' . PHP_EOL;
		// 現状のオプション値を保存
		$saved_option = get_option( 'ltg_media_unit_archive_loop_layout' );
		delete_option( 'ltg_media_unit_archive_loop_layout' );
		foreach ( $test_array as $key => $value ) {
			update_option( 'ltg_media_unit_archive_loop_layout', $value['options'] );
			$result = lmu_is_loop_layout_change_flag( $value['post_type'] );
			print 'return  :' . $result . PHP_EOL;
			print 'correct :' . $value['correct'] . PHP_EOL;
			$this->assertEquals( $value['correct'], $result );

		}
		// テスト前のオプション値に戻す
		update_option( 'ltg_media_unit_archive_loop_layout', $saved_option );
	}

}
