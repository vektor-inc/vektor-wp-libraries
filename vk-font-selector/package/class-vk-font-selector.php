<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/
/*-------------------------------------------*/
/*	Customizer
/*-------------------------------------------*/

add_action( 'customize_register', 'vkfs_customize_register_add_control', 10 );

/*-------------------------------------------*/
/*	ExUnit Original Controls
/*-------------------------------------------*/
if ( ! function_exists( 'vkfs_customize_register_add_control' ) ) {
	function vkfs_customize_register_add_control() {

		/*	Add text control description
		/*-------------------------------------------*/
		class Vk_Font_Selector_Custom_Html extends WP_Customize_Control {
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
		} // class VkVk_Font_Selector_Custom_Html extends WP_Customize_Control

	} // function veu_customize_register_add_control(){
} // if ( ! function_exists( 'vkfs_customize_register_add_control' ) ) {


if ( ! class_exists( 'Vk_Font_Selector_Customize' ) ) {

	class Vk_Font_Selector_Customize {

		public static $version = '0.1.1';

		public function __construct() {
			add_action( 'customize_register', array( $this, 'register' ) );
			add_action( 'wp_head', array( $this, 'dynamic_header_css' ), 5 );
			add_action( 'wp_footer', array( $this, 'load_web_fonts' ) );
		}

		public static function fonts_array() {
			$fonts_array = array(
				'mincho'                => array(
					'label'       => __( 'Mincho', 'vk_font_selector_textdomain' ),
					'font-family' => 'Hiragino Mincho ProN,"游明朝",serif',
				),
				'hira-kaku'             => array(
					'label'       => __( 'Hiragino Kaku Gothic', 'vk_font_selector_textdomain' ),
					'font-family' => '"ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN,"游ゴシック Medium","Yu Gothic Medium","游ゴシック体",YuGothic, "Helvetica Neue", sans-serif',
				),
				'yu-gothic'             => array(
					'label'       => __( 'Yu Gothic', 'vk_font_selector_textdomain' ),
					'font-family' => '"游ゴシック Medium","Yu Gothic Medium","游ゴシック体",YuGothic,"ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN,sans-serif',
				),
				'meiryo'                => array(
					'label'       => __( 'Meiryo', 'vk_font_selector_textdomain' ),
					'font-family' => '"メイリオ",Meiryo,"ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN,sans-serif',
				),
				'san-francisco'         => array(
					'label'       => __( 'San Francisco', 'vk_font_selector_textdomain' ),
					'font-family' => '-apple-system,BlinkMacSystemFont,"メイリオ",Meiryo,"ヒラギノ角ゴ ProN W3", Hiragino Kaku Gothic ProN,sans-serif',
				),
				// 'Noto+Sans+JP:100'      => array(
				// 	'label'           => 'Noto Sans JP 100 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans JP",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+JP',
				// 	'font-weight'     => 100,
				// ),
				'Noto+Sans+JP:300'      => array(
					'label'           => 'Noto Sans JP 300 ( Google Fonts )',
					'font-family'     => '"Noto Sans JP",sans-serif',
					'font-family-key' => 'Noto+Sans+JP',
					'font-weight'     => 300,
				),
				'Noto+Sans+JP:400'      => array(
					'label'           => 'Noto Sans JP 400 ( Google Fonts )',
					'font-family'     => '"Noto Sans JP",sans-serif',
					'font-family-key' => 'Noto+Sans+JP',
					'font-weight'     => 400,
				),
				'Noto+Sans+JP:500'      => array(
					'label'           => 'Noto Sans JP 500 ( Google Fonts )',
					'font-family'     => '"Noto Sans JP",sans-serif',
					'font-family-key' => 'Noto+Sans+JP',
					'font-weight'     => 500,
				),
				'Noto+Sans+JP:700'      => array(
					'label'           => 'Noto Sans JP 700 ( Google Fonts )',
					'font-family'     => '"Noto Sans JP",sans-serif',
					'font-family-key' => 'Noto+Sans+JP',
					'font-weight'     => 700,
				),
				// 'Noto+Sans+JP:900'      => array(
				// 	'label'           => 'Noto Sans JP 900 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans JP",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+JP',
				// 	'font-weight'     => 900,
				// ),
				// 'Noto+Sans+TC:100'      => array(
				// 	'label'           => 'Noto Sans TC 100 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans TC",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+TC',
				// 	'font-weight'     => 100,
				// ),
				'Noto+Sans+TC:300'      => array(
					'label'           => 'Noto Sans TC 300 ( Google Fonts )',
					'font-family'     => '"Noto Sans TC",sans-serif',
					'font-family-key' => 'Noto+Sans+TC',
					'font-weight'     => 300,
				),
				'Noto+Sans+TC:400'      => array(
					'label'           => 'Noto Sans TC 400 ( Google Fonts )',
					'font-family'     => '"Noto Sans TC",sans-serif',
					'font-family-key' => 'Noto+Sans+TC',
					'font-weight'     => 400,
				),
				'Noto+Sans+TC:500'      => array(
					'label'           => 'Noto Sans TC 500 ( Google Fonts )',
					'font-family'     => '"Noto Sans TC",sans-serif',
					'font-family-key' => 'Noto+Sans+TC',
					'font-weight'     => 500,
				),
				'Noto+Sans+TC:700'      => array(
					'label'           => 'Noto Sans TC 700 ( Google Fonts )',
					'font-family'     => '"Noto Sans TC",sans-serif',
					'font-family-key' => 'Noto+Sans+TC',
					'font-weight'     => 700,
				),
				// 'Noto+Sans+TC:900'      => array(
				// 	'label'           => 'Noto Sans TC 900 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans TC",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+TC',
				// 	'font-weight'     => 900,
				// ),
				// 'Noto+Sans+SC:100'      => array(
				// 	'label'           => 'Noto Sans SC 100 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans SC",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+SC',
				// 	'font-weight'     => 100,
				// ),
				'Noto+Sans+SC:300'      => array(
					'label'           => 'Noto Sans SC 300 ( Google Fonts )',
					'font-family'     => '"Noto Sans SC",sans-serif',
					'font-family-key' => 'Noto+Sans+SC',
					'font-weight'     => 300,
				),
				'Noto+Sans+SC:400'      => array(
					'label'           => 'Noto Sans SC 400 ( Google Fonts )',
					'font-family'     => '"Noto Sans SC",sans-serif',
					'font-family-key' => 'Noto+Sans+SC',
					'font-weight'     => 400,
				),
				'Noto+Sans+SC:500'      => array(
					'label'           => 'Noto Sans SC 500 ( Google Fonts )',
					'font-family'     => '"Noto Sans SC",sans-serif',
					'font-family-key' => 'Noto+Sans+SC',
					'font-weight'     => 500,
				),
				'Noto+Sans+SC:700'      => array(
					'label'           => 'Noto Sans SC 700 ( Google Fonts )',
					'font-family'     => '"Noto Sans SC",sans-serif',
					'font-family-key' => 'Noto+Sans+SC',
					'font-weight'     => 700,
				),
				// 'Noto+Sans+SC:900'      => array(
				// 	'label'           => 'Noto Sans SC 900 ( Google Fonts )',
				// 	'font-family'     => '"Noto Sans SC",sans-serif',
				// 	'font-family-key' => 'Noto+Sans+SC',
				// 	'font-weight'     => 900,
				// ),
				'Noto+Serif+TC:200'     => array(
					'label'           => 'Noto Serif TC 200 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 200,
				),
				'Noto+Serif+TC:300'     => array(
					'label'           => 'Noto Serif TC 300 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 300,
				),
				'Noto+Serif+TC:400'     => array(
					'label'           => 'Noto Serif TC 400 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 400,
				),
				'Noto+Serif+TC:500'     => array(
					'label'           => 'Noto Serif TC 500 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 500,
				),
				'Noto+Serif+TC:600'     => array(
					'label'           => 'Noto Serif TC 600 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 600,
				),
				'Noto+Serif+TC:700'     => array(
					'label'           => 'Noto Serif TC 700 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 700,
				),
				'Noto+Serif+TC:900'     => array(
					'label'           => 'Noto Serif TC 900 ( Google Fonts )',
					'font-family'     => '"Noto Serif TC",sans-serif',
					'font-family-key' => 'Noto+Serif+TC',
					'font-weight'     => 900,
				),
				'Noto+Serif+SC:200'     => array(
					'label'           => 'Noto Serif SC 200 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 200,
				),
				'Noto+Serif+SC:300'     => array(
					'label'           => 'Noto Serif SC 300 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 300,
				),
				'Noto+Serif+SC:400'     => array(
					'label'           => 'Noto Serif SC 400 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 400,
				),
				'Noto+Serif+SC:500'     => array(
					'label'           => 'Noto Serif SC 500 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 500,
				),
				'Noto+Serif+SC:600'     => array(
					'label'           => 'Noto Serif SC 600 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 600,
				),
				'Noto+Serif+SC:700'     => array(
					'label'           => 'Noto Serif SC 700 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 700,
				),
				'Noto+Serif+SC:900'     => array(
					'label'           => 'Noto Serif SC 900 ( Google Fonts )',
					'font-family'     => '"Noto Serif SC",sans-serif',
					'font-family-key' => 'Noto+Serif+SC',
					'font-weight'     => 900,
				),
				'Noto+Serif+JP:200'     => array(
					'label'           => 'Noto Serif JP 200 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 200,
				),
				'Noto+Serif+JP:300'     => array(
					'label'           => 'Noto Serif JP 300 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 300,
				),
				'Noto+Serif+JP:400'     => array(
					'label'           => 'Noto Serif JP 400 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 400,
				),
				'Noto+Serif+JP:500'     => array(
					'label'           => 'Noto Serif JP 500 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 500,
				),
				'Noto+Serif+JP:600'     => array(
					'label'           => 'Noto Serif JP 600 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 600,
				),
				'Noto+Serif+JP:700'     => array(
					'label'           => 'Noto Serif JP 700 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 700,
				),
				'Noto+Serif+JP:900'     => array(
					'label'           => 'Noto Serif JP 900 ( Google Fonts )',
					'font-family'     => '"Noto Serif JP",sans-serif',
					'font-family-key' => 'Noto+Serif+JP',
					'font-weight'     => 900,
				),
				// 'M+PLUS+1p:100'         => array(
				// 	'label'           => 'M PLUS 1p 100 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS 1p",sans-serif',
				// 	'font-family-key' => 'M+PLUS+1p',
				// 	'font-weight'     => 100,
				// ),
				'M+PLUS+1p:300'         => array(
					'label'           => 'M PLUS 1p 300 ( Google Fonts )',
					'font-family'     => '"M PLUS 1p",sans-serif',
					'font-family-key' => 'M+PLUS+1p',
					'font-weight'     => 300,
				),
				'M+PLUS+1p:400'         => array(
					'label'           => 'M PLUS 1p 400 ( Google Fonts )',
					'font-family'     => '"M PLUS 1p",sans-serif',
					'font-family-key' => 'M+PLUS+1p',
					'font-weight'     => 400,
				),
				'M+PLUS+1p:500'         => array(
					'label'           => 'M PLUS 1p 500 ( Google Fonts )',
					'font-family'     => '"M PLUS 1p",sans-serif',
					'font-family-key' => 'M+PLUS+1p',
					'font-weight'     => 500,
				),
				'M+PLUS+1p:700'         => array(
					'label'           => 'M PLUS 1p 700 ( Google Fonts )',
					'font-family'     => '"M PLUS 1p",sans-serif',
					'font-family-key' => 'M+PLUS+1p',
					'font-weight'     => 700,
				),
				// 'M+PLUS+1p:800'         => array(
				// 	'label'           => 'M PLUS 1p 800 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS 1p",sans-serif',
				// 	'font-family-key' => 'M+PLUS+1p',
				// 	'font-weight'     => 800,
				// ),
				// 'M+PLUS+1p:900'         => array(
				// 	'label'           => 'M PLUS 1p 900 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS 1p",sans-serif',
				// 	'font-family-key' => 'M+PLUS+1p',
				// 	'font-weight'     => 900,
				// ),
				// 'M+PLUS+Rounded+1c:100' => array(
				// 	'label'           => 'M PLUS Rounded 1c 100 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS Rounded 1c",sans-serif',
				// 	'font-family-key' => 'M+PLUS+Rounded+1c',
				// 	'font-weight'     => 100,
				// ),
				'M+PLUS+Rounded+1c:300' => array(
					'label'           => 'M PLUS Rounded 1c 300 ( Google Fonts )',
					'font-family'     => '"M PLUS Rounded 1c",sans-serif',
					'font-family-key' => 'M+PLUS+Rounded+1c',
					'font-weight'     => 300,
				),
				'M+PLUS+Rounded+1c:400' => array(
					'label'           => 'M PLUS Rounded 1c 400 ( Google Fonts )',
					'font-family'     => '"M PLUS Rounded 1c",sans-serif',
					'font-family-key' => 'M+PLUS+Rounded+1c',
					'font-weight'     => 400,
				),
				'M+PLUS+Rounded+1c:500' => array(
					'label'           => 'M PLUS Rounded 1c 500 ( Google Fonts )',
					'font-family'     => '"M PLUS Rounded 1c",sans-serif',
					'font-family-key' => 'M+PLUS+Rounded+1c',
					'font-weight'     => 500,
				),
				'M+PLUS+Rounded+1c:700' => array(
					'label'           => 'M PLUS Rounded 1c 700 ( Google Fonts )',
					'font-family'     => '"M PLUS Rounded 1c",sans-serif',
					'font-family-key' => 'M+PLUS+Rounded+1c',
					'font-weight'     => 700,
				),
				// 'M+PLUS+Rounded+1c:800' => array(
				// 	'label'           => 'M PLUS Rounded 1c 800 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS Rounded 1c",sans-serif',
				// 	'font-family-key' => 'M+PLUS+Rounded+1c',
				// 	'font-weight'     => 800,
				// ),
				// 'M+PLUS+Rounded+1c:900' => array(
				// 	'label'           => 'M PLUS Rounded 1c 900 ( Google Fonts )',
				// 	'font-family'     => '"M PLUS Rounded 1c",sans-serif',
				// 	'font-family-key' => 'M+PLUS+Rounded+1c',
				// 	'font-weight'     => 900,
				// ),
				'Sawarabi+Mincho'       => array(
					'label'           => 'Sawarabi Mincho ( Google Fonts )',
					'font-family'     => '"Sawarabi Mincho",sans-serif',
					'font-family-key' => 'Sawarabi+Mincho',
					'web-fonts'       => 'google',
				),
				'Sawarabi+Gothic'       => array(
					'label'           => 'Sawarabi Gothic ( Google Fonts )',
					'font-family'     => '"Sawarabi Gothic",sans-serif',
					'font-family-key' => 'Sawarabi+Gothic',
					'web-fonts'       => 'google',
				),
				'Kosugi+Maru'           => array(
					'label'           => 'Kosugi Maru ( Google Fonts )',
					'font-family'     => '"Kosugi Maru",sans-serif',
					'font-family-key' => 'Kosugi+Maru',
					'web-fonts'       => 'google',
				),
				'Kosugi'                => array(
					'label'           => 'Kosugi ( Google Fonts )',
					'font-family'     => '"Kosugi",sans-serif',
					'font-family-key' => 'Kosugi',
					'web-fonts'       => 'google',
				),
			);
			return apply_filters( 'vk_font_family_array', $fonts_array );
		}

		public static function target_array() {
			$target_array = array(
				'hlogo' => array(
					'label'    => __( 'Header Logo', 'vk_font_selector_textdomain' ),
					'selector' => '.navbar-brand.siteHeader_logo',
				),
				'menu'  => array(
					'label'    => __( 'Global Menu', 'vk_font_selector_textdomain' ),
					'selector' => '.gMenu',
				),
				'title' => array(
					'label'    => __( 'Title', 'vk_font_selector_textdomain' ),
					'selector' => 'h1,h2,h3,h4,h5,h6',
				),
				'text'  => array(
					'label'    => __( 'Text', 'vk_font_selector_textdomain' ),
					'selector' => 'body',
				),
			);
			return apply_filters( 'vk_font_target_array', $target_array );
		}

		public static function register( $wp_customize ) {

			// セクション、テーマ設定、コントロールを追加

			global $vk_font_selector_prefix;

			// セクション追加
			$wp_customize->add_section(
				'vk_font_selector_related_setting', array(
					'title'    => $vk_font_selector_prefix . __( 'Font Setting', 'vk_font_selector_textdomain' ),
					'priority' => 900,
				)
			);

			$wp_customize->add_setting( 'font_mobile_caution', array( 'sanitize_callback' => 'sanitize_text_field' ) );
			$html  = '<ul>';
			$html .= '<li>' . __( 'If you want to apply the same font on mobile devices, please select web font (Google Fonts).', 'vk_font_selector_textdomain' ) . '</li>';
			$html .= '<li>' . __( 'The web font (Google Fonts) loads the font data, so the display will be slightly late.', 'vk_font_selector_textdomain' ) . '</li>';
			$html .= '</ul>';
			$wp_customize->add_control(
				new Vk_Font_Selector_Custom_Html(
					$wp_customize, 'font_mobile_caution', array(
						'label'            => '',
						'section'          => 'vk_font_selector_related_setting',
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => $html,
					)
				)
			);

			// フォントセット読み込み
			$fonts_array = self::fonts_array();
			// プルダウン用の項目
			$choices = array();
			foreach ( $fonts_array as $key => $value ) {
				$choices[ $key ] = $value['label'];
			}

			// フォント対象読み込み
			$target_arry = self::target_array();
			$targets     = array();
			foreach ( $target_arry as $key => $value ) {
				$targets[ $key ] = $value['label'];
			}

			// $targets = array(
			// 	'title' => 'Title',
			// 	'text'  => 'Text',
			// );

			foreach ( $targets as $key => $label ) {
				$wp_customize->add_setting(
					'vk_font_selector[' . $key . ']', array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					'vk_font_selector[' . $key . ']', array(
						'label'    => __( $label . ':', 'vk_font_selector_textdomain' ),
						'section'  => 'vk_font_selector_related_setting',
						'settings' => 'vk_font_selector[' . $key . ']',
						'type'     => 'select',
						'choices'  => $choices,
					// 'choices'  => array(
					// 	'mincho' => __( 'Mincho', 'vk_font_selector_textdomain' ),
					// 	'gothic' => __( 'Gothic', 'vk_font_selector_textdomain' ),
					// ),
					)
				);
			}
		} // public function vk_font_selector_function_customize_register( $wp_customize )


		/*-------------------------------------------*/
		/*  print head style
		/*-------------------------------------------*/
		public function get_selected_fonts_info() {
			// どの場所にどのフォント指定をするのかが格納されている
			$options = get_option( 'vk_font_selector' );
			// $options = array(
			// 	    [title] => gothic,
			// 	    [menu] => gothic,
			// 	    [text] => gothic,
			// );

			// フォントリストの情報を読み込み
			$fonts_array = self::fonts_array();
			// $fonts_array = array(
			// 	'mincho' => array(
			// 		'label' => __( 'Mincho','vk_font_selector_textdomain'),
			// 		'font-family' => 'serif',
			// 	),
			// 	'gothic' => array(
			// 		'label' => __( 'Gothic','vk_font_selector_textdomain'),
			// 		'font-family' => 'sans-serif',
			// 	),
			// );
			$target_array = self::target_array();
			// $target_array = array(
			// 		'text' => array(
			// 			'label' => __('Text', 'vk_font_selector_textdomain'),
			// 			'selector' => 'body',
			// 		),
			// 		'title' => array(
			// 			'label' => __('Title', 'vk_font_selector_textdomain'),
			// 			'selector' => 'h1,h2,h3,h4,h5,h6',
			// 		),
			// 		'menu' => array(
			// 			'label' => __('Global Menu', 'vk_font_selector_textdomain'),
			// 			'selector' => '.gMenu',
			// 		),
			// 	);

			$dynamic_css       = '';
			$selected_webFonts = array();

			// フォントを指定するターゲット項目をループする
			foreach ( $target_array as $target_key => $target_value ) {

				// そのターゲットに対して、どのフォントが指定されているのかを取得
				// フォント指定先である $target_key（ title とか body とか） にフォント指定情報が保存されていたら
				if ( ! empty( $options[ $target_key ] ) ) {
					// 指定されているフォントのキーを$font_keyに格納
					$font_key = $options[ $target_key ];

					// 指定されたフォントキーの実際のフォントファミリーの取得 と CSSの登録
					// そのフォントキーがフォントの配列に登録されていたら
					if ( isset( $fonts_array[ $font_key ] ) ) {
						// 配列の中から実際のフォントファミリーを代入
						$font_family = 'font-family:' . $fonts_array[ $font_key ]['font-family'] . ';';

						// Google Fonts など weight指定があるものは追加
						$font_weight = '';
						if ( isset( $fonts_array[ $font_key ]['font-weight'] ) ) {
							$font_weight = 'font-weight:' . $fonts_array[ $font_key ]['font-weight'] . ';';
						}
						// 出力するCSSに登録
						$dynamic_css .= $target_value['selector'] . '{ ' . $font_family . $font_weight . '}';
					}

					// ウェブフォントを使用していたらウェブフォント情報を取得
					if ( isset( $fonts_array[ $font_key ]['font-family-key'] ) ) {
						$selected_webFonts[ $font_key ] = $fonts_array[ $font_key ];
					}
				} // if ( ! empty( $options[ $target_key ] ) ) {
			}

			// 動的に書き出すCSS情報
			$selected_fonts_info['dynamic_css'] = $dynamic_css;
			// ウェブフォントが使用されていた場合のウェブフォント情報（ウェブフォントを取得するため）
			$selected_fonts_info['selected_webFonts'] = $selected_webFonts;
			return $selected_fonts_info;
		}


		/*-------------------------------------------*/
		/*  print head style
		/*-------------------------------------------*/

		public function dynamic_header_css() {

			$selected_fonts_info = $this::get_selected_fonts_info();
			$dynamic_css         = $selected_fonts_info['dynamic_css'];

			// 出力するインラインスタイルが存在していたら
			if ( $dynamic_css ) {

				$dynamic_css = '/* Font switch */' . $dynamic_css;

				// delete before after space
				$dynamic_css = trim( $dynamic_css );
				// convert tab and br to space
				$dynamic_css = preg_replace( '/[\n\r\t]/', '', $dynamic_css );
				// Change multiple spaces to single space
				$dynamic_css = preg_replace( '/\s(?=\s)/', '', $dynamic_css );

				// 出力を実行
				wp_add_inline_style( 'lightning-design-style', $dynamic_css );
			}

		} // public function skin_dynamic_css(){

		public function load_web_fonts() {
			$selected_fonts_info = $this::get_selected_fonts_info();
			if ( ! empty( $selected_fonts_info['selected_webFonts'] ) ) {

				// 同じフォントでウェイト違いが入ってくるので、フォントごとにまとめた配列を生成する
				foreach ( $selected_fonts_info['selected_webFonts'] as $key => $value ) {

					$family = $value['font-family-key'];
					if ( isset( $value['font-weight'] ) ) {
						$fonts[ $family ]['weight'][] = $value['font-weight'];
					} else {
						$fonts[ $family ] = '';
					}
				} // foreach ( $selected_fonts_info['selected_webFonts'] as $key => $value ) {

				// Googleに投げるパラメーターの生成
				$family_parameter = '';
				$before_family    = '';
				$count_family     = 0;
				foreach ( $fonts as $family => $family_info ) {

					// font-familyが2つ目以降はセパレーターを追加
					if ( $count_family ) {
						$family_parameter .= '|';
					}
					$family_parameter .= $family;

					// font-weight 指定がある場合
					if ( isset( $family_info['weight'] ) && is_array( $family_info['weight'] ) ) {
						$count_weight      = 0;
						$family_parameter .= ':';
						foreach ( $family_info['weight'] as $key => $value ) {
									// font-weightが2つ目以降はセパレーターを追加
							if ( $count_weight ) {
								$family_parameter .= ',';
							}
							$family_parameter .= $value;
							$count_weight++;
						}
					}

					$count_family++;
				}
				echo '<link href="https://fonts.googleapis.com/css?family=' . $family_parameter . '" rel="stylesheet">';
			} // if ( ! empty( $selected_fonts_info['selected_webFonts'] ) ) {
		} // public function load_web_fonts() {

	} // class Vk_Font_Selector_Customize

	new Vk_Font_Selector_Customize();

} // if ( ! class_exists( 'Vk_Font_Selector_Customize' ) ) {
