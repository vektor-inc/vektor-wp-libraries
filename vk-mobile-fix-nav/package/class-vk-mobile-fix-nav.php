<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

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

		/*
		  Add text control description
		/*-------------------------------------------*/
		class MobileNav_Custom_Html extends WP_Customize_Control {
			public $type             = 'customtext';
			public $custom_title_sub = ''; // we add this for the extra custom_html
			public $custom_html      = ''; // we add this for the extra custom_html
			public function render_content() {
				if ( $this->label ) {
					// echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
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

		public static $version = '0.0.0';

		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( get_called_class(), 'add_style' ) ); // get_called_class()じゃないと外しにくい
			add_action( 'customize_register', array( $this, 'vk_mobil_fix_nav_customize_register' ) ); // $thisじゃないとエラーになる
			add_filter( 'body_class', array( __CLASS__, 'add_body_class' ) );
			add_action( 'wp_footer', array( __CLASS__, 'vk_mobil_fix_nav_html' ) );
			if ( wp_is_mobile() ) {
				remove_action( 'wp_footer', 'veu_add_pagetop' );
			}
		}

		public static function default_options() {
			$default_options = array(
				'hidden'       => true,
				'add_menu_btn' => false,
				'link_text_0'  => 'MENU',
				'link_text_1'  => 'HOME',
				'link_icon_1'  => 'fas fa-home',
				'link_url_1'   => home_url(),
				'link_blank_1' => false,
				'link_text_2'  => 'アクセス',
				'link_icon_2'  => 'fas fa-map-marker-alt',
				'link_url_2'   => 'https://www.google.co.jp/maps/search/%E5%90%8D%E5%8F%A4%E5%B1%8B%E5%B8%82%E4%B8%AD%E5%8C%BA%E6%A0%84%E4%B8%80%E4%B8%81%E7%9B%AE%EF%BC%92%EF%BC%92%E7%95%AA%EF%BC%91%EF%BC%96%E5%8F%B7+%E3%83%9F%E3%83%8A%E3%83%9F%E6%A0%84%E3%83%93%E3%83%AB+302%E5%8F%B7%E5%AE%A4/@35.1645087,136.8922015,17z/data=!3m1!4b1',
				'link_blank_2' => true,
				'link_text_3'  => 'お問い合わせ',
				'link_icon_3'  => 'fas fa-envelope',
				'link_url_3'   => home_url( '/contact/' ),
				'link_blank_3' => false,
				'link_text_4'  => 'TEL',
				'link_icon_4'  => 'fas fa-phone-square',
				'link_url_4'   => 'tel:000-000-0000',
				'link_blank_4' => true,
			);

			// フックでメニューの数を増やされた時に カスタマイザーのデフォルト値のところで Undefined index にならないように
			$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
			// 追加するフィールド配列
			$keys = array(
				'link_text_',
				'link_icon_',
				'link_url_',
				'link_blank_',
			);
			// 空の配列を作成
			for ( $i = 1; $i <= $menu_num; $i ++ ) {
				foreach ( $keys as $key ) {
					$default_options_added[ $key . $i ] = '';
				}
			}
			// デフォルト値を結合
			$default_options = wp_parse_args( $default_options, $default_options_added );

			return $default_options;
		}

		public static function get_options() {
			$options         = get_option( 'vk_mobil_fix_nav_options' );
			$default_options = self::default_options();
			$options         = wp_parse_args( $options, $default_options );
			return $options;
		}

		public static function is_fix_nav_enable() {
			$options = self::get_options();
			if ( isset( $options['hidden'] ) && $options['hidden'] ) {
				return false;
			} else {
				return true;
			}
		}

		/*
		  Customizer
		/*-------------------------------------------*/

		public function vk_mobil_fix_nav_customize_register( $wp_customize ) {

			// セクション、テーマ設定、コントロールを追加
			global $vk_mobile_fix_nav_prefix;

			$default_options = $this->default_options();

			// セクション追加
			$wp_customize->add_section(
				'vk_mobil_fix_nav_setting',
				array(
					'title'    => $vk_mobile_fix_nav_prefix . __( 'Mobile Fix Nav', 'vk_mobile_fix_nav_textdomain' ),
					'priority' => 900,
				)
			);

			// hidden セッティング
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[hidden]',
				array(
					'default'           => $default_options['hidden'],
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'veu_sanitize_boolean',
				)
			);

			// hidden コントロール
			$wp_customize->add_control(
				'vk_mobil_fix_nav_options[hidden]',
				array(
					'label'    => __( 'Do not display Mobile Fix Nav', 'vk_mobile_fix_nav_textdomain' ),
					'section'  => 'vk_mobil_fix_nav_setting',
					'settings' => 'vk_mobil_fix_nav_options[hidden]',
					'type'     => 'checkbox',
				)
			);

			// nav_title
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

			// add_menu_btn セッティング
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[add_menu_btn]',
				array(
					'default'           => $default_options['add_menu_btn'],
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'veu_sanitize_boolean',
				)
			);

			// add_menu_btn コントロール
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
				 // link_text セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_text_0]',
					array(
						'default'           => $default_options['link_text_0'],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				 // link_text コントロール
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

			// メニュー数をカスタマイズできるフィルターフックを追加
			$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
			for ( $i = 1; $i <= $menu_num; $i ++ ) {

				// nav_title
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

				// link_text セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_text_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_text_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				// link_text コントロール
				$wp_customize->add_control(
					'link_text_' . $i,
					array(
						'label'    => __( 'Link text:', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[link_text_' . $i . ']',
						'type'     => 'text',
					)
				);

				// link_icon セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_icon_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_icon_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				$description = '';
				if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
					$description = Vk_Font_Awesome_Versions::ex_and_link();
				}

				// link_icon コントロール
				$wp_customize->add_control(
					'link_icon_' . $i,
					array(
						'label'       => __( 'Icon font class name:', 'vk_mobile_fix_nav_textdomain' ),
						'section'     => 'vk_mobil_fix_nav_setting',
						'settings'    => 'vk_mobil_fix_nav_options[link_icon_' . $i . ']',
						'type'        => 'text',
						'description' => __( 'To choose your favorite icon, and enter the class.', 'vk_mobile_fix_nav_textdomain' ) . '<br>' . $description,
					)
				);

				// link_url セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_url_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_url_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'esc_url_raw',
					)
				);

				// link_url コントロール
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

				// link_blank セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
					array(
						'default'           => $default_options[ 'link_blank_' . $i ],
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'veu_sanitize_boolean',
					)
				);

				// link_blank コントロール
				$wp_customize->add_control(
					'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
					array(
						'label'    => __( 'Open link new tab.', 'vk_mobile_fix_nav_textdomain' ),
						'section'  => 'vk_mobil_fix_nav_setting',
						'settings' => 'vk_mobil_fix_nav_options[link_blank_' . $i . ']',
						'type'     => 'checkbox',
					)
				);

				// Click event セッティング
				$wp_customize->add_setting(
					'vk_mobil_fix_nav_options[event_' . $i . ']',
					array(
						'default'           => '',
						'type'              => 'option', // 保存先 option or theme_mod
						'capability'        => 'edit_theme_options', // サイト編集者
						'sanitize_callback' => 'sanitize_text_field',
					)
				);

				// Click event コントロール
				$wp_customize->add_control(
					'event_' . $i,
					array(
						'label'       => __( 'Click event:', 'vk_mobile_fix_nav_textdomain' ),
						'section'     => 'vk_mobil_fix_nav_setting',
						'settings'    => 'vk_mobil_fix_nav_options[event_' . $i . ']',
						'type'        => 'text',
						'description' => __( 'Ex', 'vk_mobile_fix_nav_textdomain' ) . ") ga('send', 'event', 'Videos', 'play', 'Fall Campaign');",
					)
				);

			} // for ($i = 1; $i <= 4; $i++) {

				// nav_common
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

			// color セッティング
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[color]',
				array(
					'default'           => '#2e6da4',
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// color コントロール
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

			// nav_bg_color セッティング
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[nav_bg_color]',
				array(
					'default'           => '#FFF',
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// nav_bg_color コントロール
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

			// current_color セッティング
			$wp_customize->add_setting(
				'vk_mobil_fix_nav_options[current_color]',
				array(
					'default'           => '#16354f',
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			// current_color コントロール
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

		/*
		  Load js & CSS
		/*-------------------------------------------*/

		static function add_style() {
			wp_enqueue_style( 'vk-mobile-fix-nav', get_template_directory_uri( __FILE__ ) . '/inc/vk-mobile-fix-nav/css/vk-mobile-fix-nav.css', array(), self::$version, 'all' );
		}

		/**
		 * add body class
		 *
		 * @return [type] [description]
		 */
		static function add_body_class( $class ) {
			$current = self::get_options();
			if ( $current['add_menu_btn'] && ! $current['hidden'] ) {
				$class[] = 'mobile-fix-nav_add_menu_btn';
			}
			if ( self::is_fix_nav_enable() ) {
				$class[] = 'mobile-fix-nav_enable';
			}
			return $class;
		}

		/*
		  vk_mobil_fix_nav_html
		/*-------------------------------------------*/
		public static function vk_mobil_fix_nav_html() {

			$options = self::get_options();
			if ( isset( $options['hidden'] ) && $options['hidden'] ) {
				return;
			}

				// text color
			if ( isset( $options['color'] ) && $options['color'] ) {
				$color = $options['color'];
				// $color = $options['color'];
			} else {
				$color = '';
			}

				// bg color
			if ( isset( $options['nav_bg_color'] ) && $options['nav_bg_color'] ) {
				$nav_bg_color = $options['nav_bg_color'];
			} else {
				$nav_bg_color = '#FFF';
			}

			?>
			  <nav class="mobile-fix-nav">
				<ul class="mobile-fix-nav-menu" style="background-color: <?php echo sanitize_hex_color( $nav_bg_color ); ?>;">

						<?php

						// color
						if ( isset( $options['color'] ) && $options['color'] ) {
							$color = $options['color'];
						} else {
							$color = '#2e6da4';
						}

						// current color
						if ( isset( $options['current_color'] ) && $options['current_color'] ) {
							$current_color = $options['current_color'];
						} else {
							$current_color = '#16354f';
						}

						// add_menu_btn
						if ( ! empty( $options['add_menu_btn'] ) ) {
							echo '<li>';
							echo '<span class="vk-mobile-nav-menu-btn" style="color: ' . $color . ';"><span class="link-icon"><i class="fas fa fa-bars" aria-hidden="true"></i></span>' . esc_html( $options['link_text_0'] ) . '</span>';
							echo '</li>';
						}

						// メニュー数をカスタマイズできるフィルターフックを追加
						$menu_num = apply_filters( 'vk_mobil_fix_nav_menu_number', 4 );
						for ( $i = 1; $i <= $menu_num; $i ++ ) {

							// link text
							if ( ! empty( $options[ 'link_text_' . $i ] ) ) {
								$link_text = $options[ 'link_text_' . $i ];
							} else {
								$link_text = '';
							}

							// fontawesome icon
							if ( ! empty( $options[ 'link_icon_' . $i ] ) ) {
								$link_icon = $options[ 'link_icon_' . $i ];
							} else {
								$link_icon = '';
							}

							// link URL
							if ( ! empty( $options[ 'link_url_' . $i ] ) ) {
								$link_url = $options[ 'link_url_' . $i ];
							} else {
								$link_url = '';
							}

							// link_blank
							if ( ! empty( $options[ 'link_blank_' . $i ] ) ) {
								$blank = ' target="_blank"';
							} else {
								$blank = '';
							}

							// 実際に HTML を出力する
							if ( isset( $options[ 'link_text_' . $i ] ) && $options[ 'link_text_' . $i ] || isset( $options[ 'link_icon_' . $i ] ) && $options[ 'link_icon_' . $i ] ) {
								echo '<li>';
								// page-current
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

								// click event
								$event = '';
								// クリックイベントが入力されていたら
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

								$print_fa = '';
								if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
									$print_fa = Vk_Font_Awesome_Versions::print_fa();
								}

								echo '<a href="' . esc_url( $link_url ) . '" ' . $blank . ' style="color: ' . $color_style . ';"' . $event . '>
		            <span class="link-icon"><i class="' . $print_fa . esc_html( $link_icon ) . '"></i></span>' . esc_html( $link_text ) . '</a>';
								echo '</li>';
							}
						} // <?php for ( $i = 1; $i <= 4; $i++ ) {
						?>

				</ul>
			  </nav>

			<?php
		} // function vk_mobil_fix_nav() {

	} // class Vk_Mobile_Fix_Nav {

	$vk_mobile_fix_nav = new Vk_Mobile_Fix_Nav();

} // if ( ! class_exists('Vk_Mobile_Fix_Nav') )  {
