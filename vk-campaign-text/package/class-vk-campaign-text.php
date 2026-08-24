<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。
修正の際は上記リポジトリのデータを修正してください。
編集権限を持っていない方で何か修正要望などありましたら
各プラグインのリポジトリにプルリクエストで結構です。
*/

/**
 * VK Campaign Text
 *
 * @package Lightning Pro
 */

if ( ! class_exists( 'VK_Campaign_Text' ) ) {
	/**
	 * VK Campaign Text
	 */
	class VK_Campaign_Text {
		/**
		 * Constructor.
		 */

		public static $version = '0.1.0';

		public function __construct() {
			add_action( 'customize_register', array( __CLASS__, 'resister_customize' ) );
			add_action( 'wp_head', array( __CLASS__, 'enqueue_style' ), 5 );
			add_action( 'after_setup_theme', array( __CLASS__, 'change_old_option' ) );
			add_action( 'wp', array( __CLASS__, 'launch_action' ) );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_css' ) );
			add_filter( 'vk_css_tree_shaking_array', array( __CLASS__, 'css_tree_shaking_array' ) );
		}

		public static function load_css() {
			if ( apply_filters( 'vk_campaign_text_print_css', false ) ) {
				$css_url = self::get_css_url();
				wp_enqueue_style( 'vk-campaign-text', $css_url, array(), self::$version );
			}
		}

		public static function css_tree_shaking_array( $vk_css_tree_shaking_array ) {
			$css_url                     = self::get_css_url();
			$vk_css_tree_shaking_array[] = array(
				'id'      => 'vk-campaign-text',
				'url'     => $css_url,
				'path'    => dirname( __FILE__ ) . '/css/vk-campaign-text.css',
				'version' => self::$version,
			);
			return $vk_css_tree_shaking_array;
		}

		/**
		 * CSS の URL を取得する.
		 *
		 * URL への変換自体は get_directory_uri()（同ファイル内）が行う。詳細はそちらの PHPDoc を参照.
		 *
		 * @return string CSS の URL.
		 */
		private static function get_css_url() {
			$path = wp_normalize_path( dirname( __FILE__ ) );
			return self::get_directory_uri( $path ) . 'css/vk-campaign-text.css';
		}

		// vk-path-to-url:begin
		/**
		 * ファイルパスを URL へ変換する.
		 *
		 * [重要] この実装は vk-campaign-text / vk-mobile-fix-nav / vk-page-header の
		 * 3モジュールに、意図的に同一内容で存在する（VK_Helpers 等への共通化はしていない）。
		 * 理由: グローバルクラス名 VK_Helpers は、Lightning（_g3）や Katawara などの製品側で
		 * composer 版 vektor-inc/vk-helpers（クラス VkHelpers）のサブクラス・エイリアスに
		 * 差し替えられており、class_exists( 'VK_Helpers' ) による機能検出では「同じ名前だが
		 * 中身が違うクラス」を掴んでしまい、本来のメソッドが呼べない（issue #172 のレビューで
		 * 判明）。そのためモジュール固有のクラス名の中に private static として持たせている。
		 * このメソッドを修正する場合は、必ず他の2モジュールの同名メソッドも同じ内容に揃えること。
		 * 参照実装: vektor-inc/font-awesome-versions#57, vektor-inc/vk-swiper#14
		 *
		 * AWS Bitnami 等、シンボリックリンクで WordPress が配置された環境では、
		 * 引数 $path（呼び出し元は __FILE__ / __DIR__ ベース。シンボリックリンクを辿って実体パスを返す）と
		 * WP_PLUGIN_DIR 等の WordPress の定数（シンボリックリンクを辿らない文字列のまま）の表記が食い違い、
		 * 単純な前方一致では基準ディレクトリのどれにも一致しないことがある（issue #172）。
		 * また ABSPATH や WP_CONTENT_DIR を基準にした str_replace() による変換は、
		 * wp-content を WordPress 本体の外に置いた構成でも同様に壊れるため、この関数では使わない。
		 * そのため、次の4段階で解決を試みる。
		 * 通常構成（シンボリックリンク無し・標準配置）では段階 (0) の最初の一致で確定するため
		 * 後続の段階は一切実行されず、挙動・戻り値ともに従来から変化しない。
		 * (0) まず変換無しでそのまま突き合わせる（従来と完全に同一の経路）。
		 * (1) (0) で一致しなければ、WordPress 本体の対応表（$wp_plugin_paths）による変換を試す。
		 * (2) (1) でも一致しなければ、基準ディレクトリ側を realpath() で実体パスへ変換して再度突き合わせる。
		 * (3) それでも解決できなければ、空文字（ドメイン直結の壊れた URL の原因になる）を返さず、
		 *     content_url() を最後の手段として返す（詳細は各処理のコメントを参照）。
		 *
		 * @param string $path 変換対象のファイルパス.
		 * @return string 変換後の URL（末尾スラッシュ付き）.
		 */
		private static function get_directory_uri( $path ) {

			$path = wp_normalize_path( $path );

			// 子テーマ・親テーマ・プラグイン・mu-plugins・wp-content はそれぞれ独立にカスタマイズ
			// 可能なため、より具体的なディレクトリから順にマッチさせて URL を生成する。
			// テーマルートは get_theme_root()（引数無し）だと常に wp-content/themes 固定になり、
			// register_theme_directory() で追加された場所にあるテーマを解決できないため、
			// get_stylesheet() / get_template() を渡して現在有効なテーマ（子テーマ・親テーマ）の
			// 実際のテーマルートを取得する。WP_CONTENT_DIR は他すべての親ディレクトリのため
			// 必ず最後に置く。
			// 配列のキーにディレクトリを使うと、カスタマイズでキーが重複した際に後勝ちで
			// 上書きされてしまうため、dir / url の組の配列にしている.
			$directories = array(
				array(
					'dir' => wp_normalize_path( get_theme_root( get_stylesheet() ) ),
					'url' => get_theme_root_uri( get_stylesheet() ),
				),
				array(
					'dir' => wp_normalize_path( get_theme_root( get_template() ) ),
					'url' => get_theme_root_uri( get_template() ),
				),
				array(
					'dir' => wp_normalize_path( WP_PLUGIN_DIR ),
					'url' => plugins_url(),
				),
				array(
					'dir' => wp_normalize_path( WPMU_PLUGIN_DIR ),
					'url' => WPMU_PLUGIN_URL,
				),
				array(
					'dir' => wp_normalize_path( WP_CONTENT_DIR ),
					'url' => content_url(),
				),
			);

			// (0) まず従来どおり、変換無しでそのまま突き合わせる（通常構成はここで確定する）.
			$uri = self::match_directory_uri( $path, $directories );

			// (1) (0) で一致しなければ、WordPress 本体が持つシンボリックリンク対応表
			// （$wp_plugin_paths。plugin_basename() が内部で使っているもの）による変換を試す.
			if ( '' === $uri ) {
				$uri = self::match_directory_uri( self::resolve_symlinked_plugin_path( $path ), $directories );
			}

			// (2) (1) でも一致しなければ、基準ディレクトリ側を realpath() で実体パスへ解決してから、
			// 変換前の $path（シンボリックリンクを辿った実体パスのまま）と改めて突き合わせる.
			if ( '' === $uri ) {
				$uri = self::match_directory_uri( $path, self::realpath_directories( $directories ) );
			}

			if ( '' !== $uri ) {
				return $uri;
			}

			// (3) (0)(1)(2) のいずれでも解決できなかったときの最後の手段.
			// 空文字を返すと、呼び出し元で相対パスのまま wp_enqueue_style() 等に渡ることになり、
			// サイト URL（末尾スラッシュ無し）とドメインが直結した壊れた URL になる（issue #172）。
			// content_url() はファイルシステムとの突き合わせを行わない純粋な URL 生成関数のため、
			// このケースでも安全にサイト内の絶対 URL を返せる。実際に配置先ディレクトリと
			// 一致しない可能性はある（読み込むファイルが 404 になりうる）が、ドメイン直結の壊れた URL は避けられる.
			//
			// 解決できないまま処理が続くと運用者が気づけないため、原因追跡できるよう記録を残す。
			// この関数は1リクエスト中に複数回呼ばれることがあるため、static 変数で
			// 「同一リクエスト内・同じパスにつき1回だけ」記録するようガードする.
			static $logged_paths = array();
			if ( ! isset( $logged_paths[ $path ] ) ) {
				$logged_paths[ $path ] = true;

				// trigger_error() は使わない。WP_DEBUG が有効なとき WP_DEBUG_DISPLAY の既定（表示する）
				// により画面へ出てしまい、このメソッドの呼び出し元はフロント側（wp_enqueue_scripts 等）
				// のため、ログインしていない一般の閲覧者にもサーバー内の絶対パスが見えてしまう。
				// そのため error_log() の1本のみで記録し、画面には一切出さない。WP_DEBUG による
				// 条件分岐も付けない（本番環境でも原因追跡できるようにするため、常に記録する）。
				// ログはロケールによらず同じ文字列の方が調査しやすいため、翻訳・エスケープは行わない.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- 本番環境でも原因追跡できるよう意図的に使用している.
				error_log( sprintf( 'VK Campaign Text: Could not resolve path to URL: %s', $path ) );
			}

			return trailingslashit( content_url() );
		}

		/**
		 * シンボリックリンク対応の変換.
		 *
		 * [重要] この実装は get_directory_uri() と同様、3モジュールに同一内容で存在する
		 * 意図的な重複である。修正時は他の2モジュールも揃えること。参照実装は
		 * get_directory_uri() の PHPDoc を参照.
		 *
		 * WordPress 本体が持つグローバル変数 $wp_plugin_paths（論理パス（定数ベース）→実体パス（realpath）の
		 * 対応表。plugin_basename() が内部で使っているもの）を使って、実体パス表記の $path を
		 * 論理パス（定数ベース）表記へ変換する。対応表に一致するエントリが無ければ $path をそのまま返す。
		 *
		 * この対応表を直接参照し plugin_basename() 自体は呼び出していない。plugin_basename() は
		 * WP_PLUGIN_DIR / WPMU_PLUGIN_DIR からの相対パス（basename）へ変換してしまうため、
		 * テーマ配下・wp-content 直下など他の基準ディレクトリの判定に使い回せなくなるためである。
		 * 対応表の構造・突き合わせ方（値が長いものを優先する arsort()）は plugin_basename() の実装に合わせている.
		 *
		 * @param string $path 変換対象のパス（wp_normalize_path 済み）.
		 * @return string 変換後のパス（対応表に一致しなければ $path のまま）.
		 */
		private static function resolve_symlinked_plugin_path( $path ) {
			global $wp_plugin_paths;

			if ( empty( $wp_plugin_paths ) || ! is_array( $wp_plugin_paths ) ) {
				return $path;
			}

			// 値（実体パス）が長いものから優先的に評価する。plugin_basename() と同じ arsort().
			$plugin_paths = $wp_plugin_paths;
			arsort( $plugin_paths );

			foreach ( $plugin_paths as $dir => $realdir ) {
				// $dir = 論理パス（定数ベース）, $realdir = 実体パス（realpath）.
				// 比較（境界判定）・切り出し（substr）・連結（返り値）の3箇所すべてで
				// 末尾スラッシュを取り除いた同じ基準の文字列を使う。基準が食い違うと、
				// $realdir の末尾にスラッシュが付いているだけでフォルダの区切りが消えた
				// 壊れたパスを返してしまう.
				$realdir = untrailingslashit( wp_normalize_path( $realdir ) );
				// ディレクトリ境界を正しく判定する。例: /srv/foo が /srv/foo-bar に誤マッチしないようにする。
				// $realdir が空文字の場合、PHP 8 では strpos( $path, '' ) が 0 を返し全マッチしてしまうため、
				// 空文字チェックも合わせて行う.
				if ( '' !== $realdir && ( $path === $realdir || 0 === strpos( $path, $realdir . '/' ) ) ) {
					return untrailingslashit( wp_normalize_path( $dir ) ) . substr( $path, strlen( $realdir ) );
				}
			}

			return $path;
		}

		/**
		 * 基準ディレクトリの配列（dir/url の組）を、dir 側を realpath() で実体パスへ解決した配列に変換する。
		 * realpath() は存在しないパスに false を返すため、その場合はマッチ対象から除外する
		 * （false のまま突き合わせに使うと strpos() 等で意図しない挙動になるため）。
		 *
		 * [重要] この実装は 3モジュールに同一内容で存在する意図的な重複である。
		 * 修正時は他の2モジュールも揃えること。参照実装は get_directory_uri() の PHPDoc を参照.
		 *
		 * @param array $directories dir/url の組の配列.
		 * @return array realpath 解決後の dir/url の組の配列（解決できなかったものは除外済み）.
		 */
		private static function realpath_directories( $directories ) {
			$resolved = array();
			foreach ( $directories as $directory ) {
				// match_directory_uri() と同じ入力検証（dir/url が揃っていない要素は候補から除外する）.
				if ( empty( $directory['dir'] ) || ! isset( $directory['url'] ) ) {
					continue;
				}
				// open_basedir 制限下のホストでは、制限外パスへの realpath() が E_WARNING を出すことがある。
				// 戻り値 false は後続で正しく処理できる想定のため、警告だけがログを汚さないよう '@' で抑制する.
				$real_dir = @realpath( $directory['dir'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- open_basedir 制限下での E_WARNING 抑制のため（false は後続で処理する）.
				if ( false === $real_dir ) {
					continue;
				}
				$resolved[] = array(
					'dir' => wp_normalize_path( $real_dir ),
					'url' => $directory['url'],
				);
			}
			return $resolved;
		}

		/**
		 * $path が $directories（dir/url の組の配列）のいずれかの dir と前方一致するか調べ、
		 * 一致したら URL を組み立てて返す。一致しなければ空文字を返す.
		 *
		 * [重要] この実装は 3モジュールに同一内容で存在する意図的な重複である。
		 * 修正時は他の2モジュールも揃えること。参照実装は get_directory_uri() の PHPDoc を参照.
		 *
		 * @param string $path 判定対象のパス.
		 * @param array  $directories dir/url の組の配列.
		 * @return string 一致した場合は URL（末尾スラッシュ付き）、しなければ空文字.
		 */
		private static function match_directory_uri( $path, $directories ) {
			foreach ( $directories as $directory ) {
				if ( empty( $directory['dir'] ) || ! isset( $directory['url'] ) ) {
					continue;
				}
				// ディレクトリ境界を正しく判定するため、末尾のスラッシュを取り除いてから比較する。
				// 例: /wp-content/plugins が /wp-content/plugins-extra に誤マッチしないようにする。
				// 比較に使う正規化済みの $dir を substr() の切り出し位置にもそのまま使う
				// （比較にだけ末尾スラッシュ除去後の値を使い、substr() は除去前の $directory['dir'] の
				// 長さを使うと、WP_CONTENT_DIR 等が末尾スラッシュ付きで define() されている環境で
				// 相対パスの先頭が1文字欠ける）.
				$dir = untrailingslashit( $directory['dir'] );
				// $directory['dir'] が '/' や '//' のように untrailingslashit() 後に空文字になるケースを弾く。
				// 空文字のままだと strpos( $path, '/' ) === 0 で全マッチしてしまう.
				if ( '' === $dir ) {
					continue;
				}
				if ( $path === $dir || 0 === strpos( $path, $dir . '/' ) ) {
					$relative_path = substr( $path, strlen( $dir ) );
					// url 側も末尾スラッシュを正規化してから連結する。正規化しないと、WP_PLUGIN_URL /
					// WP_CONTENT_URL 等を末尾スラッシュ付きで define() している環境でスラッシュが重複する
					// （dir 側は untrailingslashit() 済みのため、url 側も揃える）.
					return untrailingslashit( $directory['url'] ) . $relative_path . '/';
				}
			}
			return '';
		}
		// vk-path-to-url:end

		/**
		 * Launch Action
		 */
		public static function launch_action() {
			global $vk_campaign_text_hook_point;
			global $vk_campaign_text_display_position_array;
			$options = get_option( 'vk_campaign_text' );
			$default = self::default_option();
			$options = wp_parse_args( $options, $default );

			// 保存されている出力位置
			$position = $options['display_position'];
			global $vk_campaign_text_display_position_array;
			if ( ! $vk_campaign_text_hook_point ) {
				if ( 'show_in_front_page' === $options['display'] && is_front_page() || 'show_in_full_page' === $options['display'] ) {
					foreach ( (array) $vk_campaign_text_display_position_array[ $position ]['hookpoint'] as $hook_point ) {
						add_action( $hook_point, array( __CLASS__, 'display_html' ) );
					}
				}
			} else {
				foreach ( (array) $vk_campaign_text_hook_point as $hook_point ) {
					add_action( $hook_point, array( __CLASS__, 'display_html' ) );
				}
			}
		}

		/**
		 * Change Old Option.
		 */
		public static function change_old_option() {
			$options = get_option( 'vk_campaign_text' );
			$default = self::default_option();
			$options = wp_parse_args( $options, $default );

			if ( true === $options['display'] ) {
				$options['display'] = 'show_in_full_page';
				update_option( 'vk_campaign_text', $options );
			} elseif ( false === $options['display'] ) {
				$options['display'] = 'hide';
				update_option( 'vk_campaign_text', $options );
			}
		}

		/**
		 * Default Option.
		 */
		public static function default_option() {
			global $vk_campaign_text_display_position_array;
			$display_position = key( array_slice( $vk_campaign_text_display_position_array, 0, 1, true ) );

			$options = get_option( 'vk_campaign_text' );

			$display_position_array = array_keys( $vk_campaign_text_display_position_array );
			if ( ! empty( $options['display_position'] ) && ! in_array( $options['display_position'], $display_position_array, true ) ) {
				$options['display_position'] = $display_position;
				update_option( 'vk_campaign_text', $options );
			}

			$args = array(
				'display'                       => 'hide',
				'display_position'              => $display_position,
				'icon'                          => '',
				'main_text_color'               => '#fff',
				'main_background_color'         => '#eab010',
				'button_text_color'             => '#4c4c4c',
				'button_background_color'       => '#fff',
				'button_text_hover_color'       => '#fff',
				'button_background_hover_color' => '#eab010',
				'link_target'                   => false,
			);
			return $args;
		}

		/**
		 * Customizer.
		 *
		 * @param \WP_Customize_Manager $wp_customize Customizer.
		 */
		public static function resister_customize( $wp_customize ) {
			global $vk_campaign_text_prefix;
			global $vk_campaign_text_display_position_array;
			$display_position = key( array_slice( $vk_campaign_text_display_position_array, 0, 1, true ) );

			$description = '';
			if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
				$description = Vk_Font_Awesome_Versions::ex_and_link();
			}

			$wp_customize->add_section(
				'vk_campaign_text_setting',
				array(
					'title'    => $vk_campaign_text_prefix . __( 'Campaign Text', 'lightning-pro' ),
					'priority' => 513,
				)
			);

			// Diaplay Setting.
			$wp_customize->add_setting(
				'vk_campaign_text[display]',
				array(
					'default'           => 'hide',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => '',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[display]',
				array(
					'label'    => __( 'Display Campaign Text', 'lightning-pro' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[display]',
					'type'     => 'select',
					'choices'  => array(
						'hide'               => __( 'Hide', 'lightning-pro' ),
						'show_in_front_page' => __( 'Show in Front Page', 'lightning-pro' ),
						'show_in_full_page'  => __( 'Show in Full Page', 'lightning-pro' ),
					),
				)
			);

			$wp_customize->add_setting(
				'vk_campaign_text[display_position]',
				array(
					'default'           => $display_position,
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => '',
				)
			);

			global $vk_campaign_text_display_position_array;
			foreach ( $vk_campaign_text_display_position_array as $key => $value ) {
				$choices[ $key ] = $value['label'];
			}

			$wp_customize->add_control(
				'vk_campaign_text[display_position]',
				array(
					'label'       => __( 'Display Position', 'lightning-pro' ),
					'section'     => 'vk_campaign_text_setting',
					'settings'    => 'vk_campaign_text[display_position]',
					'type'        => 'select',
					'choices'     => $choices,
					'description' => __( '* If you save and reload after making changes, the position of campaign text will change.', 'lightning-pro' ),
				)
			);

			// Icon.
			$wp_customize->add_setting(
				'vk_campaign_text[icon]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[icon]',
				array(
					'label'       => __( 'Icon', 'lightning-pro' ),
					'section'     => 'vk_campaign_text_setting',
					'settings'    => 'vk_campaign_text[icon]',
					'type'        => 'text',
					'description' => __( 'To choose your favorite icon, and enter the class.', 'lightning-pro' ) . '<br>' . $description,
				)
			);

			// Main Text.
			$wp_customize->add_setting(
				'vk_campaign_text[main_text]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[main_text]',
				array(
					'label'    => _x( 'Text', 'campaign text', 'lightning-pro' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[main_text]',
					'type'     => 'text',
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'vk_campaign_text[main_text]',
				array(
					'selector'        => '.vk-campaign-text .vk-campaign-text_text',
					'render_callback' => '',
				)
			);

			// Main Text Color.
			$wp_customize->add_setting(
				'vk_campaign_text[main_text_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[main_text_color]',
					array(
						'label'    => __( 'Text Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[main_text_color]',
					)
				)
			);

			// Main Background Color.
			$wp_customize->add_setting(
				'vk_campaign_text[main_background_color]',
				array(
					'default'           => '#eab010',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[main_background_color]',
					array(
						'label'    => __( 'Background Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[main_background_color]',
					)
				)
			);

			// Button Text.
			$wp_customize->add_setting(
				'vk_campaign_text[button_text]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[button_text]',
				array(
					'label'    => __( 'Button Text', 'lightning-pro' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[button_text]',
					'type'     => 'text',
				)
			);

			// Button Text Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_text_color]',
				array(
					'default'           => '#4c4c4c',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_text_color]',
					array(
						'label'    => __( 'Button Text Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_text_color]',
					)
				)
			);

			// Button Background Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_background_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_background_color]',
					array(
						'label'    => __( 'Button Background Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_background_color]',
					)
				)
			);

			// Button Text Hover Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_text_hover_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_text_hover_color]',
					array(
						'label'    => __( 'Button Text Hover Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_text_hover_color]',
					)
				)
			);

			// Button Background Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_background_hover_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_background_hover_color]',
					array(
						'label'    => __( 'Button Background Hover Color', 'lightning-pro' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_background_hover_color]',
					)
				)
			);

			// Link URL.
			$wp_customize->add_setting(
				'vk_campaign_text[button_url]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_url',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[button_url]',
				array(
					'label'    => __( 'Link URL', 'lightning-pro' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[button_url]',
					'type'     => 'text',
				)
			);

			// Link Target.
			$wp_customize->add_setting(
				'vk_campaign_text[link_target]',
				array(
					'default'           => false,
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'veu_sanitize_boolean',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[link_target]',
				array(
					'label'    => __( 'Open in New Tab', 'lightning-pro' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[link_target]',
					'type'     => 'checkbox',
				)
			);

		}

		/**
		 * Get Option
		 */
		public static function get_option() {
			$options = get_option( 'vk_campaign_text' );
			$default = self::default_option();
			$options = wp_parse_args( $options, $default );
			$options = apply_filters( 'vk_campaign_text_options', $options );
			return $options;
		}

		/**
		 * Enqueue Style.
		 */
		public static function enqueue_style() {
			global $vk_campaign_text_hook_style;

			$options = self::get_option();

			$main_text_color               = $options['main_text_color'];
			$main_bg_color                 = $options['main_background_color'];
			$button_text_color             = $options['button_text_color'];
			$button_bg_color               = $options['button_background_color'];
			$button_text_hover_color       = $options['button_text_hover_color'];
			$button_background_hover_color = $options['button_background_hover_color'];

			$dynamic_css  = '.vk-campaign-text{';
			$dynamic_css .= 'background:' . $main_bg_color . ';';
			$dynamic_css .= 'color:' . $main_text_color . ';';
			$dynamic_css .= '}';
			$dynamic_css .= '.vk-campaign-text_btn,';
			$dynamic_css .= '.vk-campaign-text_btn:link,';
			$dynamic_css .= '.vk-campaign-text_btn:visited,';
			$dynamic_css .= '.vk-campaign-text_btn:focus,';
			$dynamic_css .= '.vk-campaign-text_btn:active{';
			$dynamic_css .= 'background:' . $button_bg_color . ';';
			$dynamic_css .= 'color:' . $button_text_color . ';';
			$dynamic_css .= '}';
			$dynamic_css .= 'a.vk-campaign-text_btn:hover{';
			$dynamic_css .= 'background:' . $button_background_hover_color . ';';
			$dynamic_css .= 'color:' . $button_text_hover_color . ';';
			$dynamic_css .= '}';
			$dynamic_css .= '.vk-campaign-text_link,';
			$dynamic_css .= '.vk-campaign-text_link:link,';
			$dynamic_css .= '.vk-campaign-text_link:hover,';
			$dynamic_css .= '.vk-campaign-text_link:visited,';
			$dynamic_css .= '.vk-campaign-text_link:active,';
			$dynamic_css .= '.vk-campaign-text_link:focus{';
			$dynamic_css .= 'color:' . $main_text_color . ';';
			$dynamic_css .= '}';
			wp_add_inline_style( $vk_campaign_text_hook_style, $dynamic_css );
		}

		/**
		 * Display HTML.
		 */
		public static function display_html() {
			$campaign_html = '';

			$allowed_html = array(
				'div'  => array(
					'class' => array(),
				),
				'a'    => array(
					'class'  => array(),
					'href'   => array(),
					'target' => array(),
				),
				'span' => array(
					'class' => array(),
				),
				'i'    => array(
					'class' => array(),
				),

			);

			$options = self::get_option();

			if ( isset( $options['display'] ) && 'hide' !== $options['display'] ) {
				$icon        = ! empty( $options['icon'] ) ? '<i class="' . $options['icon'] . '"></i>' : '';
				$main_text   = ! empty( $options['main_text'] ) ? $options['main_text'] : '';
				$button_text = ! empty( $options['button_text'] ) ? $options['button_text'] : '';
				$button_url  = ! empty( $options['button_url'] ) ? $options['button_url'] : '';
				$link_target = ! empty( $options['link_target'] ) ? ' target="_blank"' : '';

				$campaign_html .= '<div class="vk-campaign-text">';
				if ( empty( $button_text ) ) {
					if ( ! empty( $button_url ) ) {
						$campaign_html .= '<a class="vk-campaign-text_link" href="' . $button_url . '"' . $link_target . '>';
						$campaign_html .= '<span class="vk-campaign-text_text">' . $icon . $main_text . '</span>';
						$campaign_html .= '</a>';
					} else {
						$campaign_html .= '<span class="vk-campaign-text_text">' . $icon . $main_text . '</span>';
					}
				} else {
					$campaign_html .= '<span class="vk-campaign-text_text">' . $icon . $main_text . '</span>';
					if ( ! empty( $button_url ) ) {
						$campaign_html .= '<a class="vk-campaign-text_btn" href="' . $button_url . '"' . $link_target . '>' . $button_text . '</a>';
					} else {
						$campaign_html .= '<span class="vk-campaign-text_btn">' . $button_text . '</span>';
					}
				}
				$campaign_html .= '</div>';
			}

			echo wp_kses( $campaign_html, $allowed_html );
		}
	}
	$VK_Campaign_Text = new VK_Campaign_Text();

}
