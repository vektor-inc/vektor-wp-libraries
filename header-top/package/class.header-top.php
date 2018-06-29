<?php

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。
修正の際は上記リポジトリのデータを修正してください。
編集権限を持っていない方で何か修正要望などありましたら
各プラグインのリポジトリにプルリクエストで結構です。

*/

if ( ! class_exists( 'Lightning_header_top' ) ) {
	class Lightning_header_top {

		/*-------------------------------------------*/
		/*  実行
		/*-------------------------------------------*/
		// static function init(){
		// 	add_action( 'after_setup_theme', array( __CLASS__, 'header_top_add_menu' ) );
		// }

		public function __construct() {
			define( 'LTG_HEADER_TOP_URL', plugin_dir_url( __FILE__ ) );
			define( 'LTG_HEADER_TOP_DIR', plugin_dir_path( __FILE__ ) );
			define( 'LTG_HEADER_TOP_VERSION', '1.1' );
			add_action( 'after_setup_theme', array( $this, 'header_top_add_menu' ) );
			add_action( 'lightning_header_prepend', array( $this, 'header_top_prepend_item' ) );
			add_action( 'customize_preview_init', array( $this, 'header_top_add_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'header_top_add_css' ) );
			require_once( 'header-top-customizer.php' );
		}
		/*-------------------------------------------*/
		/*	Header top html
		/*-------------------------------------------*/

		public static function header_top_prepend_item() {

			/*
			カスタマイズプレビュー画面で get_option が最新の内容で取得できないので、
			やや処理が複雑になっている
			 */

			$options = get_option( 'Lightning_theme_options' );

			// 非表示にチェックが入っていてカスタマイズ画面でない場合には表示しない
			// /*
			// プレビュー画面で get_option がリアルタイムで取れていないため、
			// プレビュー画面においては 表示・非表示はjsで制御している。
			// よって、最初から表示されてないとjsでdom要素を追加しないといけなくなってしまうので、
			// プレビュー画面ではdom要素も強制的に表示処理としている。
			//  */
			if ( ! empty( $options['header_top_hidden'] ) && ! is_customize_preview() ) {
				return;
			}

			$header_top_style = '';
			// ヘッダートップ非表示指定 で カスタマイズ画面の時
			if ( ! empty( $options['header_top_hidden'] ) && is_customize_preview() ) {
				$header_top_style = ' style="display:none;"';
			}

			$header_prepend  = '<div class="headerTop" id="headerTop"' . $header_top_style . '>';
			$header_prepend .= '<div class="container">';
			$header_prepend .= '<p class="headerTop_description">' . get_bloginfo( 'description' ) . '</p>';

			if ( isset( $options['header_top_tel_number'] ) && $options['header_top_tel_number'] ) {
				$tel_number = mb_convert_kana( esc_attr( $options['header_top_tel_number'] ), 'n' );

				if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
					$current_info = Vk_Font_Awesome_Versions::current_info();
					if ( $current_info['version'] == '5.0' ) {
						$icon = 'fas fa-phone';
					} else {
						$icon = 'fa fa-phone';
					}
				} else {
					$icon = 'fa fa-phone';
				}

				/* ここで追加するHTMLは header-top-customizer.js でも修正する必要があるので注意 */
				$contact_tel = '';
				/* スキンによって使用しないものがある */
				if ( apply_filters( 'header-top-tel', true ) ) {
					$contact_tel .= '<li class="headerTop_tel">';
					if ( wp_is_mobile() ) {
						$contact_tel .= '<a class="headerTop_tel_wrap" href="tel:' . $tel_number . '"><i class="' . $icon . '"></i>' . $tel_number . '</a>';
					} else {
						$contact_tel .= '<span class="headerTop_tel_wrap"><i class="' . $icon . '"></i>' . $tel_number . '</span>';
					}
					$contact_tel .= '</li>';
				}
			} else {
				$contact_tel = '';
			}

			$args            = array(
				'theme_location' => 'header-top',
				'container'      => 'nav',
				'items_wrap'     => '<ul id="%1$s" class="%2$s nav">%3$s' . $contact_tel . '</ul>',
				'fallback_cb'    => '',
				'echo'           => false,
			);
			$header_top_menu = wp_nav_menu( $args );
			if ( $header_top_menu ) {
				$header_prepend .= apply_filters( 'Lightning_headerTop_menu', $header_top_menu );
			} elseif ( $contact_tel || is_customize_preview() ) {
				$header_prepend .= '<nav><ul id="%1$s" class="%2$s nav">' . $contact_tel . '</ul></nav>';
			}

			if ( apply_filters( 'header-top-contact', true ) ) {
				$header_prepend .= self::header_top_contact_btn();
			}
			$header_prepend .= '</div><!-- [ / .container ] -->';
			$header_prepend .= '</div><!-- [ / #headerTop  ] -->';
			echo $header_prepend;
		}

		static function header_top_contact_btn() {
			$options          = get_option( 'Lightning_theme_options' );
			$vkExUnit_contact = get_option( 'vkExUnit_contact' );

			if ( isset( $options['header_top_contact_txt'] ) && $options['header_top_contact_txt'] ) {
				$btn_txt = esc_html( $options['header_top_contact_txt'] );
				// } elseif ( isset( $options['header_top_contact_txt'] ) && !$options['header_top_contact_txt'] ) {
				// 	$btn_txt = '';
				// } elseif ( !isset( $options['header_top_contact_txt'] ) && isset( $vkExUnit_contact['short_text'] ) ) {
				// 	$btn_txt = esc_html( $vkExUnit_contact['short_text'] );
			} else {
				$btn_txt = '';
			}

			if ( isset( $options['header_top_contact_url'] ) && $options['header_top_contact_url'] ) {
				$link_url = esc_url( $options['header_top_contact_url'] );
				// } elseif ( isset( $options['header_top_contact_url'] ) && !$options['header_top_contact_url'] ) {
				// 	$link_url = '';
				// } elseif ( !isset( $options['header_top_contact_url'] ) && isset( $vkExUnit_contact['contact_link'] ) ) {
				// 	$link_url = esc_url( $vkExUnit_contact['contact_link'] );
			} else {
				$link_url = '';
			}

			if ( isset( $btn_txt ) && $btn_txt && isset( $link_url ) && $link_url ) {

				if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
					$current_info = Vk_Font_Awesome_Versions::current_info();
					if ( $current_info['version'] == '5.0' ) {
						$icon = 'far fa-envelope';
					} else {
						$icon = 'fa fa-envelope-o';
					}
				} else {
					$icon = 'fa fa-envelope-o';
				}

				$contact_btn_html = '<div class="headerTop_contactBtn"><a href="' . $link_url . '" class="btn btn-primary"><i class="' . $icon . '"></i>' . $btn_txt . '</a></div>';
				return $contact_btn_html;
			}
		}

		static function header_top_add_menu() {
			register_nav_menus( array( 'header-top' => 'Header Top Navigation' ) );
		}

		/*-------------------------------------------*/
		/*  Add header top css
		/*-------------------------------------------*/
		static function header_top_add_css() {
			// デフォルトでは出力しない
			$print_css_default = false;
			if ( apply_filters( 'lightning_print_header_top_css_custom', false ) ) {
				wp_enqueue_style( 'lightning-header-top', LTG_HEADER_TOP_URL . 'css/header_top.css', array( 'lightning-design-style' ), LTG_HEADER_TOP_VERSION, 'all' );
			}
		}

		static function header_top_add_script() {
			wp_register_script( 'ltg_header_top_customizer_js', plugin_dir_url( __FILE__ ) . '/header-top-customizer.js', array( 'jquery', 'customize-preview' ), LTG_HEADER_TOP_VERSION, true );
			wp_enqueue_script( 'ltg_header_top_customizer_js' );
		}

	} // class Lightning_header_top

	new Lightning_header_top();
}

// Lightning_header_top::init();
