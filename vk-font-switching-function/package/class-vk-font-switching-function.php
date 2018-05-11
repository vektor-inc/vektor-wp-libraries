<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

/*-------------------------------------------*/
/*	Customizer
/*-------------------------------------------*/

if ( ! class_exists( 'Vk_Font_Switching_Function_Customize' ) ) {
	class Vk_Font_Switching_Function_Customize {


		public static $version = '0.0.0';

		public function __construct() {
			add_action( 'customize_register', array( $this, 'register' ) );
			add_action( 'wp_head', array( $this, 'dynamic_header_css' ), 5 );
		}

		public static function fonts_array() {
			global $vk_font_switching_function_textdomain;
			$fonts_array = array(
				'mincho' => array(
					'label'       => __( 'Mincho', $vk_font_switching_function_textdomain ),
					'font-family' => 'serif',
				),
				'gothic' => array(
					'label'       => __( 'Gothic', $vk_font_switching_function_textdomain ),
					'font-family' => 'sans-serif',
				),
			);
			return $fonts_array;
		}

		public static function target_array() {
			global $vk_font_switching_function_textdomain;
			$target_array = array(
				'text'  => array(
					'label'    => __( 'Text', $vk_font_switching_function_textdomain ),
					'selecrot' => 'body',
				),
				'title' => array(
					'label'    => __( 'Title', $vk_font_switching_function_textdomain ),
					'selecrot' => 'h1,h2,h3,h4,h5,h6',
				),
				'menu'  => array(
					'label'    => __( 'めぬー', $vk_font_switching_function_textdomain ),
					'selecrot' => '.gMenu',
				),
			);
			return $target_array;
		}

		public static function register( $wp_customize ) {

			// セクション、テーマ設定、コントロールを追加
			global $vk_font_switching_function_textdomain;

			// セクション追加
			$wp_customize->add_section(
				'vk_font_switching_function_related_setting', array(
					'title'    => __( 'Font Switching Function', $vk_font_switching_function_textdomain ),
					'priority' => 900,
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
					'vk_font_switching[' . $key . ']', array(
						'default'           => 'mincho',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					'vk_font_switching[' . $key . ']', array(
						'label'    => __( $label . ':', $vk_font_switching_function_textdomain ),
						'section'  => 'vk_font_switching_function_related_setting',
						'settings' => 'vk_font_switching[' . $key . ']',
						'type'     => 'select',
						'choices'  => $choices,
					// 'choices'  => array(
					// 	'mincho' => __( 'Mincho', $vk_font_switching_function_textdomain ),
					// 	'gothic' => __( 'Gothic', $vk_font_switching_function_textdomain ),
					// ),
					)
				);
			}
		} // public function vk_font_switching_function_customize_register( $wp_customize )

		/*-------------------------------------------*/
		/*  print head style
		/*-------------------------------------------*/

		public function dynamic_header_css() {

			global $vk_font_switching_function_textdomain;

			$options = get_option( 'vk_font_switching' );

			// フォントリストの情報を読み込み
			$fonts_array  = self::fonts_array();
			$target_array = self::target_array();

			$dynamic_css = '';

			// フォントを指定する項目をループする
			foreach ( $target_array as $key => $value ) {

				// フォント指定情報が保存されていたら
				if ( ! empty( $options[ $key ] ) ) {
					// 指定されているフォントのキーを$font_keyに格納
					$font_key = $options[ $key ];
					// そのフォントキーがフォントの配列に登録されていたら
					if ( isset( $fonts_array[ $font_key ] ) ) {
						// 配列の中から実際のフォントファミリーを代入
						$font_family = $fonts_array[ $font_key ]['font-family'];
						// 出力するCSSに登録
						$dynamic_css .= $value['selecrot'] . '{ font-family:' . $font_family . '}';
					}
				} // if ( ! empty($options[$key] ){
			}

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

	} // class Vk_Font_Switching_Function_Customize
	// テーマ設定やコントロールをセットアップします。

	new Vk_Font_Switching_Function_Customize();
} // if ( ! class_exists( 'Vk_Font_Switching_Function_Customize' ) ) {


// /*-------------------------------------------*/
// /*	LTG_ADV Original Controls
// /*-------------------------------------------*/
// // if ( ! function_exists( 'vkfs_customize_register_add_control' ) ) {
// function vkfs_customize_register_add_control() {
//
// 	/*	Add text control description
// 	/*-------------------------------------------*/
// 	class FontSwitch_Custom_Html extends WP_Customize_Control {
// 		public $label             = 'customtext';
// 		public $custom_title_sub = ''; // we add this for the extra custom_html
// 		public $custom_html      = ''; // we add this for the extra custom_html
// 		public function render_content() {
// 			if ( $this->label ) {
// 				// echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
// 				echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
// 			}
// 			if ( $this->custom_title_sub ) {
// 				echo '<h3 class="admin-custom-h3">' . wp_kses_post( $this->custom_title_sub ) . '</h3>';
// 			}
// 			if ( $this->custom_html ) {
// 				echo '<div>' . wp_kses_post( $this->custom_html ) . '</div>';
// 			}
// 		} // public function render_content() {
// 	} // class FontSwitch_Custom_Html extends WP_Customize_Control
//
// } // function veu_customize_register_add_control(){
// } // if ( ! function_exists( 'vkfs_customize_register_add_control' ) ) {


// if ( ! class_exists( 'Vk_Font_Switching_Function' ) ) {

  // class Vk_Font_Switching_Function
  // {
	//
  //   public static $version = '0.0.0';
	//
  //   /*-------------------------------------------*/
  //   /*	Customizer
  //   /*-------------------------------------------*/
	//
  //   public function __construct() {
  //     add_action( 'customize_register', 'vk_font_switching_function_customize_register' );
  //   }
	//
  //   public function vk_font_switching_function_customize_register( $wp_customize ) {
	//
  //     // セクション、テーマ設定、コントロールを追加
  //     global $vk_font_switching_function_textdomain;
	//
  //     // セクション追加
  //     $wp_customize->add_section(
  //         'vk_font_switching_function_related_setting', array(
  //         'title'    => __( 'Font Switching function', $vk_font_switching_function_textdomain ),
  //         'priority' => 900,
  //         )
  //     );







	  /*-------------------------------------------*/
	  /*	Add Edit Customize Link Btn
	  /*-------------------------------------------*/
	  // $wp_customize->selective_refresh->add_partial(
	  //   'vk_font_switching_function_related_options[nav_bg_color]', array(
	  //     'selector'        => '.mobil-fix-nav',
	  //     'render_callback' => '',
	  //   )
	  // );
		// } // public function vk_font_switching_function_customize_register( $wp_customize )

  // } // class Vk_Font_Switching_Function {

  // $vk_font_switching_function = new Vk_Font_Switching_Function();

// } // if ( ! class_exists('Vk_Font_Switching_Function') )  {

// add_action( 'wp_footer', 'vk_font_switching_function' );
// function vk_font_switching_function() {
// } // function vk_font_switching_function() {
