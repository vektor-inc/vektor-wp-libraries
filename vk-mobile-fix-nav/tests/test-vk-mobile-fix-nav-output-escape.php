<?php
/**
 * モバイル固定ナビの公開画面の出力（ボタンの色）のテスト
 *
 * @package vektor-inc/vektor-wp-libraries
 */

// テスト対象のクラスを読み込む.
require_once __DIR__ . '/../package/class-vk-mobile-fix-nav.php';

/**
 * Vk_Mobile_Fix_Nav::vk_mobil_fix_nav_html() のボタンの色の出力のテスト
 */
class VkMobileFixNavOutputEscapeTest extends WP_UnitTestCase {

	/**
	 * 退避した $_SERVER
	 *
	 * @var array
	 */
	private $original_server;

	/**
	 * グローバルを退避する
	 */
	public function set_up() {
		parent::set_up();
		$this->original_server = $_SERVER;
	}

	/**
	 * グローバル・保存値を元に戻す
	 */
	public function tear_down() {
		delete_option( 'vk_mobil_fix_nav_options' );
		wp_set_current_user( 0 );
		$_SERVER = $this->original_server;
		parent::tear_down();
	}

	/**
	 * 公開画面のボタンの色の出力のテスト
	 *
	 * メニューボタンと通常のリンクは color、現在のページのリンクは current_color が style 属性に出る。
	 * 有効な値は出力が変わらないこと、引用符などを含む値は属性値として安全な形で出ることを確かめる。
	 * 保存時の確認を通さずに入った値を想定し、update_option で直接保存して作る。
	 */
	public function test_vk_mobil_fix_nav_html() {
		// 現在のページとして表示する投稿を作る.
		$post_id     = self::factory()->post->create();
		$current_url = get_permalink( $post_id );

		// テストの配列.
		$test_cases = array(
			array(
				'test_condition_name' => '色が #rrggbb のとき、メニューボタンと通常のリンクの 5 か所にそのまま出る',
				'conditions'          => array(
					'current_page'  => false,
					'color'         => '#2e6da4',
					'current_color' => '#16354f',
				),
				'expected'            => array(
					'contains'     => array(
						'<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: #2e6da4;">',
					),
					'counts'       => array(
						' style="color: #2e6da4;">' => 5,
						' style="color: #16354f;">' => 0,
					),
					'not_contains' => array(),
				),
			),
			array(
				'test_condition_name' => '色が rgba 形式のとき、メニューボタンと通常のリンクの 5 か所にそのまま出る',
				'conditions'          => array(
					'current_page'  => false,
					'color'         => 'rgba(0,0,0,0.5)',
					'current_color' => '#16354f',
				),
				'expected'            => array(
					'contains'     => array(
						'<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: rgba(0,0,0,0.5);">',
					),
					'counts'       => array(
						' style="color: rgba(0,0,0,0.5);">' => 5,
						' style="color: #16354f;">' => 0,
					),
					'not_contains' => array(),
				),
			),
			array(
				'test_condition_name' => '現在のページのリンクは current_color、ほかの 4 か所は color でそのまま出る（rgba 形式）',
				'conditions'          => array(
					'current_page'  => true,
					'color'         => '#2e6da4',
					'current_color' => 'rgba(22, 53, 79, 0.8)',
				),
				'expected'            => array(
					'contains'     => array(
						'<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: #2e6da4;">',
						'<a href="' . esc_url( $current_url ) . '"  style="color: rgba(22, 53, 79, 0.8);">',
					),
					'counts'       => array(
						' style="color: #2e6da4;">' => 4,
						' style="color: rgba(22, 53, 79, 0.8);">' => 1,
					),
					'not_contains' => array(),
				),
			),
			array(
				'test_condition_name' => 'color に二重引用符を含む値が入っていても、メニューボタンと通常のリンクの style 属性の中に収まる',
				'conditions'          => array(
					'current_page'  => false,
					'color'         => 'red"blue',
					'current_color' => '#16354f',
				),
				'expected'            => array(
					'contains'     => array(
						'<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: red&quot;blue;">',
					),
					'counts'       => array(
						' style="color: red&quot;blue;">' => 5,
					),
					'not_contains' => array(
						'red"blue',
					),
				),
			),
			array(
				'test_condition_name' => 'color に引用符・山括弧・アンパサンドを含む値が入っていても、属性値として安全な形で出る',
				'conditions'          => array(
					'current_page'  => false,
					'color'         => "a'b<c>&d",
					'current_color' => '#16354f',
				),
				'expected'            => array(
					'contains'     => array(
						'<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: a&#039;b&lt;c&gt;&amp;d;">',
					),
					'counts'       => array(
						' style="color: a&#039;b&lt;c&gt;&amp;d;">' => 5,
					),
					'not_contains' => array(
						"a'b<c>&d",
					),
				),
			),
			array(
				'test_condition_name' => 'current_color に二重引用符を含む値が入っていても、現在のページのリンクの style 属性の中に収まる',
				'conditions'          => array(
					'current_page'  => true,
					'color'         => '#2e6da4',
					'current_color' => 'red"blue',
				),
				'expected'            => array(
					'contains'     => array(
						'<a href="' . esc_url( $current_url ) . '"  style="color: red&quot;blue;">',
					),
					'counts'       => array(
						' style="color: #2e6da4;">'       => 4,
						' style="color: red&quot;blue;">' => 1,
					),
					'not_contains' => array(
						'red"blue',
					),
				),
			),
		);

		foreach ( $test_cases as $case ) {
			$conditions = $case['conditions'];

			// 保存済みの設定（保存時の確認を通さずに入った値を含む）を持たせる.
			$options = array(
				'hidden'        => false,
				'add_menu_btn'  => true,
				'color'         => $conditions['color'],
				'current_color' => $conditions['current_color'],
			);
			if ( $conditions['current_page'] ) {
				// 1 つ目のボタンのリンク先を、いま表示している投稿にする.
				$options['link_url_1'] = $current_url;
			}
			update_option( 'vk_mobil_fix_nav_options', $options );

			// 訪問者（ログインなし）として、投稿のページを表示する.
			wp_set_current_user( 0 );
			$this->go_to( $current_url );

			// 出力を取得する.
			ob_start();
			Vk_Mobile_Fix_Nav::vk_mobil_fix_nav_html();
			$html = ob_get_clean();

			foreach ( $case['expected']['contains'] as $needle ) {
				$this->assertStringContainsString( $needle, $html, $case['test_condition_name'] . '（含む）' );
			}
			foreach ( $case['expected']['counts'] as $needle => $count ) {
				$this->assertSame( $count, substr_count( $html, $needle ), $case['test_condition_name'] . '（出現回数: ' . $needle . '）' );
			}
			foreach ( $case['expected']['not_contains'] as $needle ) {
				$this->assertStringNotContainsString( $needle, $html, $case['test_condition_name'] . '（含まない）' );
			}
		}
	}
}
