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
	class Vk_Font_Switching_Function_Customize
	{

		  public static $version = '0.0.0';

			public function __construct(){
				add_action( 'customize_register', array( $this, 'register' ) );
				add_action( 'wp_head', array( $this, 'dynamic_header_css' ),5 );
			}

			public static function fonts_array(){
				global $vk_font_switching_function_textdomain;
				$fonts_array = array(
					'mincho' => array(
						'label' => __( 'Mincho',$vk_font_switching_function_textdomain),
						'font-family' => 'serif',
					),
					'gothic' => array(
						'label' => __( 'Gothic',$vk_font_switching_function_textdomain),
						'font-family' => 'sans-serif',
					),
				);
				return $fonts_array;
			}

			public static function target_array(){
				global $vk_font_switching_function_textdomain;
				$target_array = array(
						'hlogo' => array(
							'label' => __('Header Logo', $vk_font_switching_function_textdomain),
							'selector' => '.navbar-brand.siteHeader_logo',
						),
						'menu' => array(
							'label' => __('Global Menu', $vk_font_switching_function_textdomain),
							'selector' => '.gMenu',
						),
						'title' => array(
							'label' => __('Title', $vk_font_switching_function_textdomain),
							'selector' => 'h1,h2,h3,h4,h5,h6',
						),
						'text' => array(
							'label' => __('Text', $vk_font_switching_function_textdomain),
							'selector' => 'body',
						),
					);
				return $target_array;
			}

			public static function register ( $wp_customize ) {

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
				foreach ($fonts_array as $key => $value) {
					$choices[$key] = $value['label'];
				}


				// フォント対象読み込み
				$target_arry = self::target_array();
				$targets = array();
				foreach ($target_arry as $key => $value) {
					$targets[$key] = $value['label'];
				}

				// $targets = array(
				// 	'title' => 'Title',
				// 	'text'  => 'Text',
				// );

				foreach ( $targets as $key => $label ) {
					$wp_customize->add_setting( 'vk_font_switching['.$key.']', array(
						'default'           => 'mincho',
						'type'				      => 'option',
						'capability'		    => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_text_field',
					) );
					$wp_customize->add_control( 'vk_font_switching['.$key.']', array(
						'label'		 =>  __( $label.':', $vk_font_switching_function_textdomain ),
						'section'	 => 'vk_font_switching_function_related_setting',
						'settings' => 'vk_font_switching['.$key.']',
						'type'		 => 'select',
						'choices'  => $choices,
						// 'choices'  => array(
						// 	'mincho' => __( 'Mincho', $vk_font_switching_function_textdomain ),
						// 	'gothic' => __( 'Gothic', $vk_font_switching_function_textdomain ),
						// ),
					));
				}
		} // public function vk_font_switching_function_customize_register( $wp_customize )

		/*-------------------------------------------*/
		/*  print head style
		/*-------------------------------------------*/

		public function dynamic_header_css() {

			global $vk_font_switching_function_textdomain;
			// どの場所にどのフォント指定をするのかが格納されている
			$options = get_option( 'vk_font_switching' );
			// $options = array(
			// 	    [title] => gothic,
			// 	    [menu] => gothic,
			// 	    [text] => gothic,
			// );

			// フォントリストの情報を読み込み
			$fonts_array = self::fonts_array();
			// $fonts_array = array(
			// 	'mincho' => array(
			// 		'label' => __( 'Mincho',$vk_font_switching_function_textdomain),
			// 		'font-family' => 'serif',
			// 	),
			// 	'gothic' => array(
			// 		'label' => __( 'Gothic',$vk_font_switching_function_textdomain),
			// 		'font-family' => 'sans-serif',
			// 	),
			// );
			$target_array = self::target_array();
			// $target_array = array(
			// 		'text' => array(
			// 			'label' => __('Text', $vk_font_switching_function_textdomain),
			// 			'selector' => 'body',
			// 		),
			// 		'title' => array(
			// 			'label' => __('Title', $vk_font_switching_function_textdomain),
			// 			'selector' => 'h1,h2,h3,h4,h5,h6',
			// 		),
			// 		'menu' => array(
			// 			'label' => __('Global Menu', $vk_font_switching_function_textdomain),
			// 			'selector' => '.gMenu',
			// 		),
			// 	);

			$dynamic_css ='';

			// フォントを指定するターゲット項目をループする
			foreach ($target_array as $target_key => $target_value) {

				// そのターゲットに対して、どのフォントが指定されているのかを取得
				// フォント指定先である $target_key（ title とか body とか） にフォント指定情報が保存されていたら
				if ( ! empty($options[$target_key]) ){
					// 指定されているフォントのキーを$font_keyに格納
					$font_key= $options[$target_key];

					// 指定されたフォントキーの実際のフォントファミリーの取得 と CSSの登録
					// そのフォントキーがフォントの配列に登録されていたら
					if ( isset( $fonts_array[$font_key] ) ){
						// 配列の中から実際のフォントファミリーを代入
						$font_family = $fonts_array[$font_key]['font-family'];
						// 出力するCSSに登録
						$dynamic_css .= $target_value['selector'].'{ font-family:'.$font_family.'}';
					}
				} // if ( ! empty($options[$target_key] ){
			}

			// 出力するインラインスタイルが存在していたら
			if ( $dynamic_css ){

				$dynamic_css = '/* Font switch */'.$dynamic_css;

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
