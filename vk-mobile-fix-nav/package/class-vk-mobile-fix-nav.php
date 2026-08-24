<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

use VektorInc\VK_Font_Awesome_Versions\VkFontAwesomeVersions;
if ( method_exists( VkFontAwesomeVersions::class, 'init' ) ) {
	VkFontAwesomeVersions::init();
}

// add_action( 'after_setup_theme', 'vkmn_nav_add_customize_panel' );
//
// // カスタマイズパネルを出力するかどうかの判別
// function vkmn_nav_add_customize_panel() {
// カスタマイザーが利用されるので、独自のコントロールクラスを追加
//
// }

add_action( 'customize_register', 'vkmn_customize_register_add_control', 10 );

/*
  ExUnit Original Controls
/*-------------------------------------------*/
if ( ! function_exists( 'vkmn_customize_register_add_control' ) ) {
	function vkmn_customize_register_add_control() {

		/**
		 * Add text control description
		 */
		class MobileNav_Custom_Html extends WP_Customize_Control {
			public $type             = 'customtext';
			public $custom_title_sub = ''; // we add this for the extra custom_html.
			public $custom_html      = ''; // we add this for the extra custom_html.
			public function render_content() {
				if ( $this->label ) {
					echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
				}
				if ( $this->custom_title_sub ) {
					echo '<h3 class="admin-custom-h3">' . wp_kses_post( $this->custom_title_sub ) . '</h3>';
				}
				if ( $this->custom_html ) {
					echo '<div>' . wp_kses_post( $this->custom_html ) . '</div>';
				}
			} // public function render_content() {
		} // class MobileNav_Custom_Html extends WP_Customize_Control

	} // function veu_customize_register_add_control(){
} // if ( ! function_exists( 'vkmn_customize_register_add_control' ) ) {


if ( ! class_exists( 'Vk_Mobile_Fix_Nav' ) ) {

	class Vk_Mobile_Fix_Nav {

		public static $version = '0.1.0';

		public function __construct() {

			/**
			 * Reason of Using through the after_setup_theme is
			 * to be able to change the action hook point of css load from theme..
			 */
			// get_called_class()じゃないと外しにくい.
			add_action( 'wp_enqueue_scripts', array( get_called_class(), 'add_style' ) );
			add_action( 'customize_register', array( $this, 'vk_mobil_fix_nav_customize_register' ) ); // $thisじゃないとエラーになる
			add_filter( 'body_class', array( __CLASS__, 'add_body_class' ) );
			$vk_mobil_fix_nav_html_hook_point = apply_filters( 'vk_mobile_fix_nav_html_hook_point', 'wp_footer' );
			add_action( $vk_mobil_fix_nav_html_hook_point, array( __CLASS__, 'vk_mobil_fix_nav_html' ) );
			// モバイル固定ナビの高さをCSSカスタムプロパティとして出力
			add_action( $vk_mobil_fix_nav_html_hook_point, array( __CLASS__, 'print_mobile_fix_nav_height_css' ), 20 );
			add_action( 'widgets_init', array( __CLASS__, 'widgets_init' ) );
			add_filter( 'vk_css_tree_shaking_handles', array( __CLASS__, 'css_tree_shaking_handles' ) );
		}

		public static function widgets_init() {
			register_sidebar(
				array(
					'name'          => __( 'Widget area of mobile fix nav', 'vk_mobile_fix_nav_textdomain' ),
					'id'            => 'mobile-fix-nav-widget-area',
					'before_widget' => '<aside class="mobile-fix-nav-widget %2$s" id="%1$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h4 class="widget-title subSection-title">',
					'after_title'   => '</h4>',
				)
			);
		}

		public static function default_options() {
			$default_options = array(
				'hidden'         => true,
				'widget_padding' => false,
				'add_menu_btn'   => false,
				'link_text_0'    => 'MENU',
				'link_text_1'    => 'HOME',
				'link_icon_1'    => 'fa-solid fa-house',
				'link_url_1'     => home_url(),
				'link_blank_1'   => false,
				'link_text_2'    => 'アクセス',
				'link_icon_2'    => 'fa-solid fa-location-dot',
				'link_url_2'     => 'https://www.google.co.jp/maps/search/%E5%90%8D%E5%8F%A4%E5%B1%8B%E5%B8%82%E4%B8%AD%E5%8C%BA%E6%A0%84%E4%B8%80%E4%B8%81%E7%9B%AE%EF%BC%92%EF%BC%92%E7%95%AA%EF%BC%91%EF%BC%96%E5%8F%B7+%E3%83%9F%E3%83%8A%E3%83%9F%E6%A0%84%E3%83%93%E3%83%AB+302%E5%8F%B7%E5%AE%A4/@35.1645087,136.8922015,17z/data=!3m1!4b1',
				'link_blank_2'   => true,
				'link_text_3'    => 'お問い合わせ',
				'link_icon_3'    => 'fa-solid fa-envelope',
				'link_url_3'     => home_url( '/contact/' ),
				'link_blank_3'   => false,
				'link_text_4'    => 'TEL',
				'link_icon_4'    => 'fa-solid fa-square-phone',
				'link_url_4'     => 'tel:000-000-0000',
				'link_blank_4'   => true,
			);

			// フックでメニューの数を増やされた時に カスタマイザーのデフォルト値のところで Undefined index にならないように.
			$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
			// 追加するフィールド配列.
			$keys = array(
				'link_text_',
				'link_icon_',
				'link_url_',
				'link_blank_',
			);
			// 空の配列を作成.
			for ( $i = 1; $i <= $menu_num; $i ++ ) {
				foreach ( $keys as $key ) {
					$default_options_added[ $key . $i ] = '';
				}
			}
			// デフォルト値を結合.
			$default_options = wp_parse_args( $default_options, $default_options_added );

			return $default_options;
		}

		public static function get_options() {
			$options         = get_option( 'vk_mobil_fix_nav_options' );
			$default_options = self::default_options();
			$options         = wp_parse_args( $options, $default_options );
			return apply_filters( 'vk_mobil_fix_nav_options', $options );
		}

		public static function is_fix_nav_enable() {
			$options = self::get_options();
			if ( isset( $options['hidden'] ) && $options['hidden'] ) {
				return false;
			} else {
				return true;
			}
		}

		public static function sanitize_boolean( $input ) {
			if ( $input == true ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Customizer
		 *
		 * @param object $wp_customize .
		 * @return void
		 */
		public function vk_mobil_fix_nav_customize_register( $wp_customize ) {

			// セクション、テーマ設定、コントロールを追加.
			global $vk_mobile_fix_nav_prefix;
			global $vk_mobile_fix_nav_priority;
			if ( ! $vk_mobile_fix_nav_priority ) {
				$vk_mobile_fix_nav_priority = 900;
			}

			$default_options = $this->default_options();

			// セクション追加.
			$wp_customize->add_section(
				'vk_mobil_fix_nav_setting',
				array(
					'title'    => $vk_mobile_fix_nav_prefix . __( 'Mobile Fix Nav', 'vk_mobile_fix_nav_textdomain' ),
					'priority' => $vk_mobile_fix_nav_priority,
				)
			);

			// hidden セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[hidden]',
				array(
					'default'           => $default_options['hidden'],
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => array( 'Vk_Mobile_Fix_Nav', 'sanitize_boolean' ),
				)
			);

			// hidden コントロール.
			$wp_customize->add_control(
				'vk_mobil_fix_nav_options[hidden]',
				array(
					'label'    => __( 'Do not display Mobile Fix Nav', 'vk_mobile_fix_nav_textdomain' ),
					'section'  => 'vk_mobil_fix_nav_setting',
					'settings' => 'vk_mobil_fix_nav_options[hidden]',
					'type'     => 'checkbox',
				)
			);

			// nav_title.
			$wp_customize->add_setting(
				'nav_title_widget',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new MobileNav_Custom_Html(
					$wp_customize,
					'nav_title_widget',
					array(
						'label'            => __( 'Mobile Fix Nav Widget Area', 'vk_mobile_fix_nav_textdomain' ),
						'section'          => 'vk_mobil_fix_nav_setting',
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			// hidden セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[widget_padding]',
				array(
					'default'           => $default_options['widget_padding'],
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => array( 'Vk_Mobile_Fix_Nav', 'sanitize_boolean' ),
				)
			);

			// widget_padding コントロール.
			$wp_customize->add_control(
				'vk_mobil_fix_nav_options[widget_padding]',
				array(
					'label'    => __( 'Add Widget Area Padding', 'vk_mobile_fix_nav_textdomain' ),
					'section'  => 'vk_mobil_fix_nav_setting',
					'settings' => 'vk_mobil_fix_nav_options[widget_padding]',
					'type'     => 'checkbox',
				)
			);

			// nav_title.
			$wp_customize->add_setting(
				'nav_title_0',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new MobileNav_Custom_Html(
					$wp_customize,
					'nav_title_0',
					array(
						'label'            => __( 'Add menu open and close button', 'vk_mobile_fix_nav_textdomain' ),
						'section'          => 'vk_mobil_fix_nav_setting',
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			// add_menu_btn セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[add_menu_btn]',
				array(
					'default'           => $default_options['add_menu_btn'],
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => array( 'Vk_Mobile_Fix_Nav', 'sanitize_boolean' ),
				)
			);

			// add_menu_btn コントロール.
			$wp_customize->add_control(
				'vk_mobil_fix_nav_options[add_menu_btn]',
				array(
					'label'       => __( 'Add menu open and close button to first.', 'vk_mobile_fix_nav_textdomain' ),
					'section'     => 'vk_mobil_fix_nav_setting',
					'settings'    => 'vk_mobil_fix_nav_options[add_menu_btn]',
					'type'        => 'checkbox',
					'description' => __( '* When using Font Awesome 5 SVG, the icon does not switch at menu open.', 'vk_mobile_fix_nav_textdomain' ),
				)
			);

			// 「add_menu_btn」にチェックが入っているときの処理
			$add_menu_btn = array( 'add_menu_btn' );
			if ( isset( $add_menu_btn ) && $add_menu_btn == true ) {
				// link_text セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_text_0]',
					array(
						'default'           => $default_options['link_text_0'],
						'type'              => 'option', // 保存先 option or theme_mod.
						'capability'        => 'edit_theme_options', // サイト編集者.
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				// link_text コントロール.
				$wp_customize->add_control(
					'link_text_0',
					array(
						'label'    => __( 'Link text:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[link_text_0]',
						'type'     => 'text',
					)
				);

			} // if ( isset( $add_menu_btn ) && $add_menu_btn == true ) {

			// メニュー数をカスタマイズできるフィルターフックを追加.
			$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
			for ( $i = 1; $i <= $menu_num; $i ++ ) {

				// nav_title.
				$wp_customize->add_setting(
					'nav_title_' . $i,
					array(
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					new MobileNav_Custom_Html(
						$wp_customize,
						'nav_title_' . $i,
						array(
							'label'            => __( 'Fix Navi button', 'vk_mobile_fix_nav_textdomain' ) . ' [ ' . $i . ' ]',
							'section'          => 'vk_mobil_fix_nav_setting',
							'type'             => 'text',
							'custom_title_sub' => '',
							'custom_html'      => '',
						)
					)
				);

				// link_text セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_text_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_text_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod.
						'capability'        => 'edit_theme_options', // サイト編集者.
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				// link_text コントロール.
				$wp_customize->add_control(
					'link_text_' . $i,
					array(
						'label'    => __( 'Link text:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[link_text_' . $i . ']',
						'type'     => 'text',
					)
				);

				// link_icon セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_icon_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_icon_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod.
						'capability'        => 'edit_theme_options', // サイト編集者.
						'sanitize_callback' => 'wp_kses_post',
					)
				);

				$description = '';
				if ( method_exists( VkFontAwesomeVersions::class, 'ex_and_link' ) ) {
					$description = VkFontAwesomeVersions::ex_and_link();
				}

				// link_icon コントロール.
				$wp_customize->add_control(
					'link_icon_' . $i,
					array(
						'label'       => __( 'Font Awesome icon font', 'vk_mobile_fix_nav_textdomain' ),
						'section'     => 'vk_mobil_fix_nav_setting',
						'settings'    => 'vk_mobil_fix_nav_options[link_icon_' . $i . ']',
						'type'        => 'text',
						'description' => __( 'To choose your favorite icon, and enter the icon html tag.', 'vk_mobile_fix_nav_textdomain' ) . '<br>' . $description,
					)
				);

				// link_url セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_url_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_url_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod.
						'capability'        => 'edit_theme_options', // サイト編集者.
						'sanitize_callback' => 'esc_url_raw',
					)
				);

				// link_url コントロール.
				$wp_customize->add_control(
					'link_url_' . $i,
					array(
						'label'       => __( 'Link URL:', 'vk_mobile_fix_nav_textdomain' ),
						'section'     => 'vk_mobil_fix_nav_setting',
						'settings'    => 'vk_mobil_fix_nav_options[link_url_' . $i . ']',
						'type'        => 'text',
						'description' => __( 'Ex', 'vk_mobile_fix_nav_textdomain' ) . ') https://vccw.text/',
					)
				);

				// link_blank セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_blank_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => array( 'Vk_Mobile_Fix_Nav', 'sanitize_boolean' ),
					)
				);

				// link_blank コントロール.
				$wp_customize->add_control(
					'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
					array(
						'label'    => __( 'Open link new tab.', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
						'type'     => 'checkbox',
					)
				);

				// Click event セッティング.
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[event_' . $i . ']',
					array(
						'default'           => '',
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				// Click event コントロール.
				$wp_customize->add_control(
					'event_' . $i,
					array(
						'label'       => __( 'Click event:', 'vk_mobile_fix_nav_textdomain' ),
						'section'     => 'vk_mobil_fix_nav_setting',
						'settings'    => 'vk_mobil_fix_nav_options[event_' . $i . ']',
						'type'        => 'text',
						'description' => __( 'Ex', 'vk_mobile_fix_nav_textdomain' ) . " ) gtag('event', 'play', { 'event_category': 'Videos', 'event_label': 'Fall Campaign'});",
					)
				);

			} // for ($i = 1; $i <= 4; $i++) {

				// nav_common.
				$wp_customize->add_setting(
					'nav_common',
					array(
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					new MobileNav_Custom_Html(
						$wp_customize,
						'nav_common',
						array(
							'label'            => __( 'Navi Common Settings', 'vk_mobile_fix_nav_textdomain' ),
							'section'          => 'vk_mobil_fix_nav_setting',
							'type'             => 'text',
							'custom_title_sub' => '',
							'custom_html'      => '',
						)
					)
				);

			// color セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[color]',
				array(
					'default'           => '#2e6da4',
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// color コントロール.
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'color',
					array(
						'label'    => __( 'Text Color:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[color]',
					)
				)
			);

			// nav_bg_color セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[nav_bg_color]',
				array(
					'default'           => '#FFF',
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// nav_bg_color コントロール.
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'nav_bg_color',
					array(
						'label'    => __( 'Background Color:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[nav_bg_color]',
					)
				)
			);

			// current_color セッティング.
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[current_color]',
				array(
					'default'           => '#16354f',
					'type'              => 'option', // 保存先 option or theme_mod.
					'capability'        => 'edit_theme_options', // サイト編集者.
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// current_color コントロール.
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'current_color',
					array(
						'label'    => __( 'Current Color:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[current_color]',
					)
				)
			);

			/*
			  Add Edit Customize Link Btn
			/*-------------------------------------------*/
			$wp_customize->selective_refresh->add_partial(
				'vk_mobil_fix_nav_options[add_menu_btn]',
				array(
					'selector'        => '.mobile-fix-nav',
					'render_callback' => '',
				)
			);

		} // function vk_mobil_fix_nav_customize_register( $wp_customize ) {

		/**
		 * CSS の URL を取得する.
		 *
		 * URL への変換自体は get_directory_uri()（同ファイル内）が行う。詳細はそちらの PHPDoc を参照.
		 *
		 * @return string CSS の URL.
		 */
		public static function style_url() {
			$path = wp_normalize_path( dirname( __FILE__ ) );
			return self::get_directory_uri( $path ) . 'css/vk-mobile-fix-nav.css';
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
				error_log( sprintf( 'VK Mobile Fix Nav: Could not resolve path to URL: %s', $path ) );
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
		 * フロント用CSSを読み込む。
		 *
		 * アセットのバージョンにモジュールのバージョン（self::$version）を使うことで、
		 * CSSを更新した際は $version を上げればブラウザキャッシュが更新される。
		 * $version の上げ忘れは CI（version guard）で検知する。
		 *
		 * @return void
		 */
		static function add_style() {
			$css_url = self::style_url();
			wp_enqueue_style( 'vk-mobile-fix-nav', $css_url, array(), self::$version, 'all' );
		}

		public static function css_tree_shaking_handles( $vk_css_tree_shaking_handles ) {
			$vk_css_tree_shaking_handles = array_merge(
				$vk_css_tree_shaking_handles,
				array(
					'vk-mobile-fix-nav'
				)
			);

			return $vk_css_tree_shaking_handles;
		}

		/**
		 * add body class.
		 *
		 * @return [type] [description]
		 */
		static function add_body_class( $class ) {
			$current = self::get_options();
			if ( $current['add_menu_btn'] && ! $current['hidden'] ) {
				$class[] = 'mobile-fix-nav_add_menu_btn';
			}
			if ( self::is_menu_enable() ) {
				$class[] = 'mobile-fix-nav_enable';
			}
			return $class;
		}


		/**
		 * ウィジェットとナビゲーション両方非表示にするかどうか
		 *
		 * @return bool
		 */
		public static function is_hidden_all() {
			$is_menu_enable = self::is_menu_enable();
			// ナビが非表示指定でウィジュエットも登録されていない場合.
			if ( ! $is_menu_enable && ! is_active_sidebar( 'mobile-fix-nav-widget-area' ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * ナビゲーション部分を非表示にするかどうか
		 *
		 * @return bool
		 */
		public static function is_menu_enable() {
			$options = self::get_options();
			if ( empty( $options['hidden'] ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Output custom property for mobile fix nav height.
		 */
		public static function print_mobile_fix_nav_height_css() {
			// モバイル固定ナビもウィジェットも空なら何もしない
			if ( self::is_hidden_all() ) {
				return;
			}
			// CSSカスタムプロパティの初期値はテーマ側のCSSで書いてあるのでここでは書かない
			// jsで高さを取得してCSSカスタムプロパティを書き換える
			$script_raw = <<<'JS'
(function(){
	const STYLE_ID = 'vk-mobile-fix-nav-height-css';
	let styleEl = document.getElementById(STYLE_ID);
	if (! styleEl) {
		styleEl = document.createElement('style');
		styleEl.id = STYLE_ID;
		document.head.appendChild(styleEl);
	}
	function updateNavHeight() {
		const nav = document.querySelector('.mobile-fix-nav');
		if (! nav) {
			return;
		}
		styleEl.textContent = ':root{--vk-mobile-fix-nav-height:' + Math.round(nav.getBoundingClientRect().height) + 'px;}';
	}
	function observeNav() {
		const nav = document.querySelector('.mobile-fix-nav');
		if (! nav || typeof MutationObserver === 'undefined') {
			return;
		}
		new MutationObserver(updateNavHeight).observe(nav, { attributes: true, childList: true, subtree: true });
	}
	function init() {
		updateNavHeight();
		observeNav();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
	['load', 'resize', 'orientationchange'].forEach(function (eventName) {
		window.addEventListener(eventName, updateNavHeight);
	});
})();
JS;
			wp_print_inline_script_tag( $script_raw, array( 'id' => 'vk-mobile-fix-nav-height-js' ) );
		}

		/*
		  vk_mobil_fix_nav_html
		/*-------------------------------------------*/
		public static function vk_mobil_fix_nav_html() {

			if ( self::is_hidden_all() ) {
				return;
			}

			$options = self::get_options();

			// bg color.
			if ( isset( $options['nav_bg_color'] ) && $options['nav_bg_color'] ) {
				$nav_bg_color = $options['nav_bg_color'];
			} else {
				$nav_bg_color = '#FFF';
			}

			// color.
			if ( isset( $options['color'] ) && $options['color'] ) {
				$color = $options['color'];
			} else {
				$color = '#2e6da4';
			}

			// current color.
			if ( isset( $options['current_color'] ) && $options['current_color'] ) {
				$current_color = $options['current_color'];
			} else {
				$current_color = '#16354f';
			}
			?>
			<nav class="mobile-fix-nav" style="background-color: <?php echo sanitize_hex_color( $nav_bg_color ); ?>;">

				<?php if ( is_active_sidebar( 'mobile-fix-nav-widget-area' ) ) : ?>
					<?php
					$padding_class = '';
					if ( ! empty( $options['widget_padding'] ) ) {
						$padding_class = ' mobile-fix-nav-top-padding-true';
					}
					?>
					<div class="mobile-fix-nav-top<?php echo $padding_class; ?>">
						<?php dynamic_sidebar( 'mobile-fix-nav-widget-area' ); ?>
					</div>
				<?php endif; ?>

				<?php if ( self::is_menu_enable() ) : ?>
				<ul class="mobile-fix-nav-menu">

						<?php
						// add_menu_btn.
						if ( ! empty( $options['add_menu_btn'] ) ) {
							echo '<li>';
							echo '<span id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn" style="color: ' . $color . ';"><span class="link-icon"><i class="fa-solid fa-bars"></i></span>' . esc_html( $options['link_text_0'] ) . '</span>';
							echo '</li>';
						}

						// メニュー数をカスタマイズできるフィルターフックを追加.
						$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
						for ( $i = 1; $i <= $menu_num; $i ++ ) {

							// link text.
							if ( ! empty( $options[ 'link_text_' . $i ] ) ) {
								$link_text = $options[ 'link_text_' . $i ];
							} else {
								$link_text = '';
							}

							// fontawesome icon.
							if ( ! empty( $options[ 'link_icon_' . $i ] ) ) {
								$link_icon = $options[ 'link_icon_' . $i ];
							} else {
								$link_icon = '';
							}

							// link URL.
							if ( ! empty( $options[ 'link_url_' . $i ] ) ) {
								$link_url = $options[ 'link_url_' . $i ];
							} else {
								$link_url = '';
							}

							// link_blank.
							if ( ! empty( $options[ 'link_blank_' . $i ] ) ) {
								$blank = ' target="_blank"';
							} else {
								$blank = '';
							}

							// 実際に HTML を出力する.
							if ( isset( $options[ 'link_text_' . $i ] ) && $options[ 'link_text_' . $i ] || isset( $options[ 'link_icon_' . $i ] ) && $options[ 'link_icon_' . $i ] ) {
								echo '<li>';
								// page-current.
								$get_current_link = get_the_permalink();
								$postid           = url_to_postid( get_permalink() );
								// $get_current_link_cat = get_category_link( $postid );
								$get_current_link_cat = get_the_category_list( $postid );
								// $get_current_link_cat = get_post_type_archive_link( $postid );
								// $get_current_link_cat = get_post_type_archive_link( get_post_type() );
								if ( ( ! empty( $options[ 'link_url_' . $i ] ) && ( $get_current_link == $options[ 'link_url_' . $i ] ) ) || ( ! empty( $options[ 'link_url_' . $i ] ) && ( $get_current_link_cat == $options[ 'link_url_' . $i ] ) ) ) {
									// $page_current = ' class="page-current"';
									$color_style = $current_color;
								} else {
									$color_style = $color;
								}

								// click event.
								$event = '';
								// クリックイベントが入力されていたら.
								if ( ! empty( $options[ 'event_' . $i ] ) && $options[ 'event_' . $i ] ) {
									/*
									onclickはクリックが終わった瞬間に発生するイベント
									クリック終了後にイベントが発生し、Googleにビーコンを送信しますが、
									ビーコンが送られる前に次のページに遷移してしまうとカウントされない場合がある
									*/
									if ( wp_is_mobile() ) {
										$event = ' ontouchstart="';
									} else {
										$event = ' onmousedown="';
									}
									$event .= $options[ 'event_' . $i ] . '"';
								} // if ( ! empty( $options['event_'.$i] ) && $options['event_'.$i] ){

								$icon_html = '';
								if ( method_exists( VkFontAwesomeVersions::class, 'get_icon_tag' ) && ! empty( $link_icon ) ) {
									$icon_html = VkFontAwesomeVersions::get_icon_tag( $link_icon );
								}

								echo '<a href="' . esc_url( $link_url ) . '" ' . $blank . ' style="color: ' . $color_style . ';"' . $event . '>
		            <span class="link-icon">' . wp_kses_post( $icon_html ) . '</span>' . esc_html( $link_text ) . '</a>';
								echo '</li>';
							}
						} // <?php for ( $i = 1; $i <= 4; $i++ ) {
						?>

				</ul>
				<?php endif; ?>
			  </nav>

			<?php
		} // function vk_mobil_fix_nav() {

	} // class Vk_Mobile_Fix_Nav {

	$vk_mobile_fix_nav = new Vk_Mobile_Fix_Nav();

} // if ( ! class_exists('Vk_Mobile_Fix_Nav') )  {
