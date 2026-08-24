<?php
/**
 * VK Helpers
 *
 * @package VK Helpers
 */

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。
修正の際は上記リポジトリのデータを修正してください。
編集権限を持っていない方で何か修正要望などありましたら
各プラグインのリポジトリにプルリクエストで結構です。
*/

if ( ! class_exists( 'VK_Helpers' ) ) {
	/**
	 * VK Helpers
	 */
	class VK_Helpers {

		/*
		get_post_top_info
		get_post_type_info
		sanitize_checkbox
		sanitize_number_percentage
		sanitize_choice
		sanitize_textarea
		sanitize_boolean
		color_auto_modifi
		color_adjust_under_ff
		color_mode_check
		color_convert_rgba
		deactivate_plugin
		*/

		public function __construct() {
			add_action( 'customize_register', array( __CLASS__, 'add_customize_class' ), 0 );
		}

		public static function add_customize_class( $wp_customize ) {
			if ( ! class_exists( 'VK_Custom_Html_Control' ) ) {
				require_once dirname( __FILE__ ) . '/class-vk-custom-html-control.php';
			}
			if ( ! class_exists( 'VK_Custom_Text_Control' ) ) {
				require_once dirname( __FILE__ ) . '/class-vk-custom-text-control.php';
			}
		}

		public static function get_post_top_info() {

			$post_top_info = array();

			// Get post top page by setting display page.
			$post_top_info['id'] = get_option( 'page_for_posts' );

			// Set use post top page flag.
			$post_top_info['use'] = ( $post_top_info['id'] ) ? true : false;

			// When use post top page that get post top page name.
			$post_top_info['name'] = ( $post_top_info['use'] ) ? get_the_title( $post_top_info['id'] ) : '';

			$post_top_info['url'] = ( $post_top_info['use'] ) ? get_permalink( $post_top_info['id'] ) : '';

			return $post_top_info;
		}


		public static function get_post_type_info() {
			// Check use post top page
			$post_top_info = self::get_post_top_info();

			$woocommerce_shop_page_id = get_option( 'woocommerce_shop_page_id' );

			// Get post type slug
			/*
			-------------------------------------------*/
			// When WooCommerce taxonomy archive page , get_post_type() is does not work properly
			// $post_type_info['slug'] = get_post_type();

			global $wp_query;
			if ( is_page() ) {
				$post_type_info['slug'] = 'page';
			} elseif ( ! empty( $wp_query->query_vars['post_type'] ) ) {

				$post_type_info['slug'] = $wp_query->query_vars['post_type'];
				// Maybe $wp_query->query_vars['post_type'] is usually an array...
				if ( is_array( $post_type_info['slug'] ) ) {
					$post_type_info['slug'] = current( $post_type_info['slug'] );
				}
			} elseif ( is_tax() ) {
				// Case of tax archive and no posts
				// 存在しないタームのURLにアクセスされで普通に get_queried_object()->taxonomy を走らせるとエラーになるため
				if ( ! empty( get_queried_object()->taxonomy ) ){
					$taxonomy               = get_queried_object()->taxonomy;
					$post_type_info['slug'] = get_taxonomy( $taxonomy )->object_type[0];
				} else {
					$post_type_info['slug'] = '404';
				}
			} else {
				// This is necessary that when no posts.
				$post_type_info['slug'] = 'post';
			}

			// Get custom post type name
			/*-------------------------------------------*/
			$post_type_object = get_post_type_object( $post_type_info['slug'] );
			if ( $post_type_object ) {
				$allowed_html = array(
					'span' => array( 'class' => array() ),
					'b'    => array(),
				);
				if ( $post_top_info['use'] && $post_type_info['slug'] == 'post' ) {
					$post_type_info['name'] = wp_kses( get_the_title( $post_top_info['id'] ), $allowed_html );
				} elseif ( $woocommerce_shop_page_id && $post_type_info['slug'] == 'product' ) {
					$post_type_info['name'] = wp_kses( get_the_title( $woocommerce_shop_page_id ), $allowed_html );
				} else {
					$post_type_info['name'] = esc_html( $post_type_object->labels->name );
				}
			}

			// Get custom post type archive url
			/*-------------------------------------------*/
			if ( $post_top_info['use'] && $post_type_info['slug'] == 'post' ) {
				$post_type_info['url'] = esc_url( get_the_permalink( $post_top_info['id'] ) );
			} elseif ( $woocommerce_shop_page_id && $post_type_info['slug'] == 'product' ) {
				$post_type_info['url'] = esc_url( get_the_permalink( $woocommerce_shop_page_id ) );
			} else {
				$post_type_info['url'] = esc_url( get_post_type_archive_link( $post_type_info['slug'] ) );
			}

			$post_type_info = apply_filters( 'vk_get_post_type_info', $post_type_info );
			return $post_type_info;
		}


		public static function get_display_taxonomies( $post_id = null, $args = null ) {
			if ( ! $post_id ) {
				global $post;
				$post_id = $post->ID;
			}
			$taxonomies = get_the_taxonomies( $post_id, $args );

			// 非公開のタクソノミーを自動的に除外
			foreach ( $taxonomies as $taxonomy => $value ) {
				$taxonomy_info = get_taxonomy( $taxonomy );
				if ( empty( $taxonomy_info->public ) ) {
					unset( $taxonomies[ $taxonomy ] );
				}
			}

			// 上記を後で実装したので以下の処理は事実上不要と思われるが、
			// 公開タクソノミーで意図的に表示したくないものもあるかもしれないのでフィルターは消さない
			$exclusion = array( 'post_tag', 'product_type' );
			$exclusion = apply_filters( 'vk_get_display_taxonomies_exclusion', $exclusion );
			if ( is_array( $exclusion ) ) {
				foreach ( $exclusion as $key => $value ) {
					unset( $taxonomies[ $value ] );
				}
			}
			return $taxonomies;
		}

		/**
		 * Sanitize Check Box
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_checkbox( $input ) {
			if ( 'true' === $input || true === $input ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Sanitize Number
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_number( $input ) {
			$input = mb_convert_kana( $input, 'a' );
			if ( is_numeric( $input ) ) {
				return $input;
			} else {
				return 0;
			}
		}

		/**
		 * Sanitize Number Percentage
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_number_percentage( $input ) {
			$input = self::sanitize_number( $input );
			if ( 0 <= $input && $input <= 100 ) {
				return $input;
			} else {
				return 0;
			}
		}

		/**
		 * Sanitize Choice
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_choice( $input ) {
			return esc_attr( $input );
		}


		/**
		 * Sanitize Text Area
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_textarea( $input ) {
			$allowed_html = array(
				'a'      => array(
					'id'    => array(),
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'role'  => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'i'      => array(
					'class' => array(),
				),
			);
			return wp_kses( $input, $allowed_html );
		}

		/**
		 * Sanitize Boolean
		 *
		 * @param string $input $input.
		 */
		public static function sanitize_boolean( $input ) {
			if ( $input == true ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * [ 非推奨 ] 色を比率で明るくしたり暗くする
		 * 既にCSSのfilterなどでなどで同じような事ができるため非推奨
		 *
		 * @param  string  $color       #あり16進数.
		 * @param  integer $change_rate 1 が 100%.
		 */
		public static function color_auto_modifi( $color, $change_rate = 1 ) {

			if ( ! $color ) {
				return;
			}

			$color = preg_replace( '/#/', '', $color );
			// 16進数を10進数に変換
			$r = hexdec( substr( $color, 0, 2 ) );
			$g = hexdec( substr( $color, 2, 2 ) );
			$b = hexdec( substr( $color, 4, 2 ) );

			$color_array = array();
			// 10進数の状態で変更レートを掛けて16進数で受け取る.
			$color_array['r'] = self::color_auto_modifi_single( $r, $change_rate );
			$color_array['g'] = self::color_auto_modifi_single( $g, $change_rate );
			$color_array['b'] = self::color_auto_modifi_single( $b, $change_rate );

			$new_color = '#';

			foreach ( $color_array as $key => $value ) {
				$new_color .= $color_array[ $key ];
			}
			return $new_color;
		}

		/**
		 * [ 非推奨 ] RGBの個別の色をレートで変換して16進数で返す
		 * color_auto_modifi でのみ使用されている
		 *
		 * @param  string  $color_num : RGBの単色の10進数の数値.
		 * @param  integer $change_rate : 1 が 100%.
		 * @return string $color : RGBの単色の16進数の数値.
		 */
		public static function color_auto_modifi_single( $color_num, $change_rate = 1 ) {

			$color_num = $color_num * $change_rate;
			if ( $color_num >= 255 ) {
				$color_num = 255;
			}

			// レートをかけて四捨五入.
			$rounded = round( $color_num );

			// 結果を16進数に変換.
			$hex = dechex( $rounded );

			// 結果がもし1桁なら2桁になるように0で埋める.
			$color = str_pad( $hex, 2, '0', STR_PAD_LEFT );

			return $color;
		}

		/**
		 * [color_mode_check description]
		 *
		 * @param string $input input color code.
		 */
		public static function color_mode_check( $input = '#ffffff' ) {
			$color['input'] = $input;
			// delete #.
			$color['input'] = preg_replace( '/#/', '', $color['input'] );

			$color_len = strlen( $color['input'] );

			// Only 3 character.
			if ( 3 === $color_len ) {
				$color_red   = substr( $color['input'], 0, 1 ) . substr( $color['input'], 0, 1 );
				$color_green = substr( $color['input'], 1, 1 ) . substr( $color['input'], 1, 1 );
				$color_blue  = substr( $color['input'], 2, 1 ) . substr( $color['input'], 2, 1 );
			} elseif ( 6 === $color_len ) {
				$color_red   = substr( $color['input'], 0, 2 );
				$color_green = substr( $color['input'], 2, 2 );
				$color_blue  = substr( $color['input'], 4, 2 );
			} else {
				$color_red   = 'ff';
				$color_green = 'ff';
				$color_blue  = 'ff';
			}

			// change 16 to 10 number.
			$color['color_red']   = hexdec( $color_red );
			$color['color_green'] = hexdec( $color_green );
			$color['color_blue']  = hexdec( $color_blue );

			$color['number_sum'] = $color['color_red'] + $color['color_green'] + $color['color_blue'];

			$color['brightness'] = 0.00130718954 * $color['number_sum'];

			if ( $color['brightness'] < 0.5 ) {
				$color['mode'] = 'dark';
			} else {
				$color['mode'] = 'bright';
			}

			return $color;

		}

		/**
		 * 16進数をRGBAに変換する
		 *
		 * @param  string $input hex color code.
		 * @param  num    $alpha transparnt value.
		 */
		public static function color_convert_rgba( $input = '#FFFFFF', $alpha = 1 ) {
			$color = self::color_mode_check( $input );
			$rgba .= 'rgba(' . $color['color_red'] . ', ' . $color['color_green'] . ', ' . $color['color_blue'] . ', ' . $alpha . ')';
			return esc_html( $rgba );
		}

		/**
		 * 有効化されているプラグインを無効化する
		 *
		 * @param string $plugin_path path of plugin.
		 */
		public static function deactivate_plugin( $plugin_path ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if ( is_plugin_active( $plugin_path ) ) {
				$active_plugins = get_option( 'active_plugins' );
				// delete item.
				$active_plugins = array_diff( $active_plugins, array( $plugin_path ) );
				// re index.
				$active_plugins = array_values( $active_plugins );
				update_option( 'active_plugins', $active_plugins );
			}
		}

		/**
		 * ファイルパスを URL へ変換する
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
		public static function get_directory_uri( $path ) {

			$path = wp_normalize_path( $path );

			// WP_PLUGIN_DIR / WPMU_PLUGIN_DIR / テーマルート / WP_CONTENT_DIR は
			// それぞれ独立にカスタマイズ可能なため、より具体的なディレクトリから順にマッチさせて URL を生成する.
			// 配列のキーにディレクトリを使うと、カスタマイズでキーが重複した際に後勝ちで上書きされてしまうため、
			// dir / url の組の配列にしている.
			$directories = array(
				array(
					'dir' => wp_normalize_path( WP_PLUGIN_DIR ),
					'url' => plugins_url(),
				),
				array(
					'dir' => wp_normalize_path( WPMU_PLUGIN_DIR ),
					'url' => WPMU_PLUGIN_URL,
				),
				array(
					'dir' => wp_normalize_path( get_theme_root() ),
					'url' => get_theme_root_uri(),
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
			return trailingslashit( content_url() );
		}

		/**
		 * シンボリックリンク対応の変換.
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
		 * このライブラリの外から呼ぶ用途を想定していないため private static とする.
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
		 * このメソッドを private static にしている理由は resolve_symlinked_plugin_path() の PHPDoc を参照.
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
		 * このメソッドを private static にしている理由は resolve_symlinked_plugin_path() の PHPDoc を参照.
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

	}
	new VK_Helpers();
}
