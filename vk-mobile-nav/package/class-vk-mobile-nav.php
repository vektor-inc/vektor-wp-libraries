<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

if ( ! class_exists( 'Vk_Mobile_Nav' ) ) {
	class Vk_Mobile_Nav {

		public static $version = '0.3.3';

		public function __construct() {
			/* Can not call get_called_class() on PHP5.2 */
			if ( function_exists( 'get_called_class' ) ) {
				// 11 指定がないと Lightning G2系などテーマによってカスタマイザーで選択できない場合がある
				add_action( 'after_setup_theme', array( get_called_class(), 'setup_menu' ), 11 );
				add_action( 'widgets_init', array( get_called_class(), 'setup_widget' ) );
				$vk_mobile_nav_html_hook_point = apply_filters( 'vk_mobile_nav_html_hook_point', 'wp_footer' );
				add_action( $vk_mobile_nav_html_hook_point, array( get_called_class(), 'menu_set_html' ) );
				add_action( 'wp_enqueue_scripts', array( get_called_class(), 'add_script' ) );
				add_action( 'wp_enqueue_scripts', array( get_called_class(), 'add_inline_css' ), 30 );

				add_action( 'customize_register', array( $this, 'customize_register' ) ); // $thisじゃないとエラーになる
			}
			add_filter( 'body_class', array( $this, 'add_body_class_mobile_device' ) );
		}
		public static function init() {
			// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * body class 端末識別クラス追加
		 *
		 * @return [type] [description]
		 */
		function add_body_class_mobile_device( $class ) {
			if ( wp_is_mobile() ) {
				$class[] = 'device-mobile';
			} else {
				$class[] = 'device-pc';
			}
			return $class;
		}

		/**
		 * モバイル用メニュー追加
		 *
		 * @return [type] [description]
		 */
		public static function setup_menu() {
				register_nav_menus( array( 'vk-mobile-nav' => 'Mobile Navigation' ) );
		}

		/**
		 * モバイルメニュー用ウィジェットエリア追加
		 *
		 * @return [type] [description]
		 */
		static function setup_widget() {

			register_sidebar(
				array(
					'name'          => __( 'Mobile Nav Upper', 'vk_mobile_nav_textdomain' ),
					'id'            => 'vk-mobile-nav-upper',
					'before_widget' => '<aside class="widget vk-mobile-nav-widget %2$s" id="%1$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h1 class="widget-title subSection-title">',
					'after_title'   => '</h1>',
				)
			);
			register_sidebar(
				array(
					'name'          => __( 'Mobile Nav Bottom', 'vk_mobile_nav_textdomain' ),
					'id'            => 'vk-mobile-nav-bottom',
					'before_widget' => '<aside class="widget vk-mobile-nav-widget %2$s" id="%1$s">',
					'after_widget'  => '</aside>',
					'before_title'  => '<h1 class="widget-title subSection-title">',
					'after_title'   => '</h1>',
				)
			);
		}



		public static function menu_set_html() {

			$option               = self::get_option();
			$btn_additional_class = '';
			if ( 'right' === $option['position'] ) {
				$btn_additional_class = ' position-right';
			}

			$menu_btn_text = apply_filters( 'vk_mobile_nav_menu_btn_text', __( 'MENU', 'vk_mobile_nav_textdomain' ) );
			// aria-expanded / aria-controls をマークアップ側で初期付与する。
			// 属性が無いと「状態欠落」扱いになり、JS 初期化前に支援技術へ状態が伝わらないため必ず付与する。
			// aria-controls はメニュー本体の固定 id（vk-mobile-nav）を素の値で参照する。
			// role="button" / tabindex="0" を付与し、div のままキーボードフォーカス・操作を可能にする。
			// （button 化は .vk-mobile-nav-menu-btn セレクタへの CSS 波及が読めないため div + role 方式を採用）
			$menu_btn = '<div id="vk-mobile-nav-menu-btn" class="vk-mobile-nav-menu-btn' . $btn_additional_class . '" role="button" tabindex="0" aria-expanded="false" aria-controls="vk-mobile-nav">' . $menu_btn_text . '</div>';

			// wp_kses_post() のグローバル属性には tabindex が含まれず、そのまま通すと
			// キーボードフォーカス用の tabindex="0" が剥がされてしまう。
			// そこで post 用許可属性をベースに、開閉ボタンの div へ tabindex を追加で許可する。
			$menu_btn_allowed_html                         = wp_kses_allowed_html( 'post' );
			$menu_btn_allowed_html['div']                  = isset( $menu_btn_allowed_html['div'] ) ? $menu_btn_allowed_html['div'] : array();
			$menu_btn_allowed_html['div']['tabindex']      = true;
			$menu_btn_allowed_html['div']['role']          = true;
			$menu_btn_allowed_html['div']['aria-expanded'] = true;
			$menu_btn_allowed_html['div']['aria-controls'] = true;

			if ( class_exists( 'Vk_Mobile_Fix_Nav' ) ) {
				$fix_nav_options = Vk_Mobile_Fix_Nav::get_options();
				// fixナビ内にメニュー展開ボタンを表示しない || fixナビ自体を表示しない
				if ( ! $fix_nav_options['add_menu_btn'] || $fix_nav_options['hidden'] ) {
					echo wp_kses( $menu_btn, $menu_btn_allowed_html );
				}
			} else {
				echo wp_kses( $menu_btn, $menu_btn_allowed_html );
			}

			echo '<div class="vk-mobile-nav vk-mobile-nav-' . esc_attr( $option['slide_type'] ) . '" id="vk-mobile-nav">';
			if ( is_active_sidebar( 'vk-mobile-nav-upper' ) ) {
				dynamic_sidebar( 'vk-mobile-nav-upper' );
			} elseif ( current_user_can( 'edit_theme_options' ) ) {
					echo '<div class="veu_adminEdit alert alert-info">';
					echo '<p>' . sprintf( __( 'This is the widget area.<br>You can set widget item from [ <a href="%s">Appearance > Customize</a> ] Page -> "Widgets" panel -> "Mobile Nav Upper" Panel.', 'vk_mobile_nav_textdomain' ), admin_url( 'customize.php' ) ) . '</p>';
					echo '<p>' . __( '* This message is displayed only to users with editing authority.', 'vk_mobile_nav_textdomain' ) . '</p>';
					echo '</div>';
			}

			$menu_vk_mobile = wp_nav_menu(
				array(
					'theme_location' => 'vk-mobile-nav',
					'container'      => '',
					'items_wrap'     => '<nav class="vk-mobile-nav-menu-outer" role="navigation"><ul id="%1$s" class="vk-menu-acc %2$s">%3$s</ul></nav>',
					'fallback_cb'    => '',
					'echo'           => false,
					// 'depth'          => 1,
				)
			);
			global $default_nav;
			$menu_theme_default = wp_nav_menu(
				array(
					'theme_location' => $default_nav,
					'container'      => '',
					'items_wrap'     => '<nav class="vk-mobile-nav-menu-outer" role="navigation"><ul id="%1$s" class="vk-menu-acc %2$s">%3$s</ul></nav>',
					'fallback_cb'    => '',
					'echo'           => false,
					// 'depth'          => 1,
				)
			);
			if ( $menu_vk_mobile ) {
				echo $menu_vk_mobile;
			} elseif ( $menu_theme_default ) {
				echo $menu_theme_default;
			} elseif ( current_user_can( 'edit_theme_options' ) ) {
					echo '<div class="veu_adminEdit alert alert-danger">';
					echo '<p>' . sprintf( __( 'Menu is not set.<br>Please set menu from [ <a href="%s">Appearance > Customize</a> ] Page -> "Menus" panel -> Menu Locations "Mobile Navigation".', 'vk_mobile_nav_textdomain' ), admin_url( 'customize.php' ) ) . '</p>';
					echo '<p>' . __( '* This message is displayed only to users with editing authority.', 'vk_mobile_nav_textdomain' ) . '</p>';
					echo '</div>';
			}

			if ( is_active_sidebar( 'vk-mobile-nav-bottom' ) ) {
				dynamic_sidebar( 'vk-mobile-nav-bottom' );
			} elseif ( current_user_can( 'edit_theme_options' ) ) {
					echo '<div class="veu_adminEdit alert alert-info">';
					echo '<p>' . sprintf( __( 'This is the widget area.<br>You can set widget item from [ <a href="%s">Appearance > Customize</a> ] Page -> "Widgets" panel -> "Mobile Nav Bottom" Panel.', 'vk_mobile_nav_textdomain' ), admin_url( 'customize.php' ) ) . '</p>';
					echo '<p>' . __( '* This message is displayed only to users with editing authority.', 'vk_mobile_nav_textdomain' ) . '</p>';
					echo '</div>';
			}

			echo '</div>';
		}

		/*
			Load js & CSS
		/*-------------------------------------------*/

		public static function add_script() {
			global $library_url;
			wp_register_script( 'vk-mobile-nav-js', $library_url . '/js/vk-mobile-nav.min.js', array(), self::$version );
			// JS 側で動的生成する子階層開閉ボタン（.acc-btn）はアイコンのみで表示テキストを持たないため、
			// aria-label に使う翻訳済み文言を JS へ渡す。
			wp_localize_script(
				'vk-mobile-nav-js',
				'vkMobileNavL10n',
				array(
					'openSubMenu' => __( 'Submenu', 'vk_mobile_nav_textdomain' ),
				)
			);
			wp_enqueue_script( 'vk-mobile-nav-js' );
			wp_enqueue_style( 'vk-mobile-nav-css', $library_url . '/css/vk-mobile-nav-bright.css', array(), self::$version, 'all' );
		}

		/**
		 * Add vk mobile nav inline css
		 *
		 * @return void
		 */
		public static function add_inline_css() {
			global $library_url;
			$dynamic_css = '/* vk-mobile-nav */
			:root {
				--vk-mobile-nav-menu-btn-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-btn-black.svg' ) . '");
				--vk-mobile-nav-menu-btn-close-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-close-black.svg' ) . '");
				--vk-menu-acc-icon-open-black-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-acc-icon-open-black.svg' ) . '");
				--vk-menu-acc-icon-open-white-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-acc-icon-open-white.svg' ) . '");
				--vk-menu-acc-icon-close-black-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-close-black.svg' ) . '");
				--vk-menu-acc-icon-close-white-bg-src: url("' . esc_url( $library_url . '/images/vk-menu-close-white.svg' ) . '");
			}
			';
			// delete before after space
			$dynamic_css = trim( $dynamic_css );
			// convert tab and br to space
			$dynamic_css = preg_replace( '/[\n\r\t]/', '', $dynamic_css );
			// Change multiple spaces to single space
			$dynamic_css = preg_replace( '/\s(?=\s)/', '', $dynamic_css );
			global $vk_mobile_nav_inline_style_handle;
			wp_add_inline_style( $vk_mobile_nav_inline_style_handle, $dynamic_css );
		}


		public static function get_option() {
			$option = get_option( 'vk_mobile_nav_options' );
			$option = wp_parse_args( $option, self::default_options() );
			return $option;
		}

		public static function default_options() {
			$default_options = array(
				'position'   => 'left',
				'slide_type' => 'drop-in',
			);
			return $default_options;
		}

		/*
			Customizer
		/*-------------------------------------------*/

		public function customize_register( $wp_customize ) {

			// セクション、テーマ設定、コントロールを追加
			global $vk_mobile_nav_prefix;
			global $vk_mobile_nav_priority;
			if ( ! $vk_mobile_nav_priority ) {
				$vk_mobile_nav_priority = 900;
			}

			$default_options = $this->default_options();

			// セクション追加
			$wp_customize->add_section(
				'vk_mobile_nav_setting',
				array(
					'title'    => $vk_mobile_nav_prefix . __( 'Mobile Nav', 'vk_mobile_nav_textdomain' ),
					'priority' => $vk_mobile_nav_priority,
				)
			);

			// position セッティング
			$wp_customize->add_setting(
				'vk_mobile_nav_options[position]',
				array(
					'default'           => $default_options['position'],
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// position コントロール
			$wp_customize->add_control(
				'vk_mobile_nav_options[position]',
				array(
					'label'    => __( 'Menu button position', 'vk_mobile_nav_textdomain' ),
					'section'  => 'vk_mobile_nav_setting',
					'settings' => 'vk_mobile_nav_options[position]',
					'type'     => 'radio',
					'choices'  => array(
						'left'  => __( 'Left', 'vk_mobile_nav_textdomain' ),
						'right' => __( 'Right', 'vk_mobile_nav_textdomain' ),
					),
				)
			);

			// slide_type セッティング
			$wp_customize->add_setting(
				'vk_mobile_nav_options[slide_type]',
				array(
					'default'           => $default_options['slide_type'],
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			// slide_type コントロール
			$wp_customize->add_control(
				'vk_mobile_nav_options[slide_type]',
				array(
					'label'    => __( 'Menu slide direction', 'vk_mobile_nav_textdomain' ),
					'section'  => 'vk_mobile_nav_setting',
					'settings' => 'vk_mobile_nav_options[slide_type]',
					'type'     => 'radio',
					'choices'  => array(
						'drop-in'  => __( 'Drop', 'vk_mobile_nav_textdomain' ),
						'left-in'  => __( 'Left -> Right', 'vk_mobile_nav_textdomain' ),
						'right-in' => __( 'Right -> Left', 'vk_mobile_nav_textdomain' ),
					),
				)
			);

			/*
				Add Edit Customize Link Btn
			/*-------------------------------------------*/
			$wp_customize->selective_refresh->add_partial(
				'vk_mobile_nav_options[position]',
				array(
					'selector'        => '.vk-mobile-nav-menu-btn',
					'render_callback' => '',
				)
			);
		} // function customize_register( $wp_customize ) {
	} // class Vk_Mobile_Nav

	// Store in global variable so that hook in class can be removed
	global $vk_mobile_nav;
	$vk_mobile_nav = new Vk_Mobile_Nav();

}
