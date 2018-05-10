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

		  /*-------------------------------------------*/
		  /*	Customizer
		  /*-------------------------------------------*/

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
				$type = array( "title", "text", );
				foreach ( $type as $key => $type ) {
					$wp_customize->add_setting( 'vk_font_switching['.$type.']', array(
						'default'			      => 'default',
						'type'				      => 'option',
						'capability'		    => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_text_field',
					) );
					$font_family = array( '明朝体', 'ゴシック体' );
					$wp_customize->add_control( 'vk_font_switching['.$type.']', array(
						'label'		 =>  __( $type.':', $vk_font_switching_function_textdomain ),
						'section'	 => 'vk_font_switching_function_related_setting',
						'settings' => 'vk_font_switching['.$type.']',
						'type'		 => 'select',
						'choices'  => $font_family,
					));
				}
		} // public function vk_font_switching_function_customize_register( $wp_customize )

	} // class Vk_Font_Switching_Function_Customize
	// テーマ設定やコントロールをセットアップします。

	add_action( 'customize_register' , array( 'Vk_Font_Switching_Function_Customize' , 'register' ) );
	new Vk_Font_Switching_Function_Customize();
} // if ( ! class_exists( 'Vk_Mobile_Nav' ) ) {

// /*-------------------------------------------*/
// /*	LTG_ADV Original Controls
// /*-------------------------------------------*/
// // if ( ! function_exists( 'vkfs_customize_register_add_control' ) ) {
// function vkfs_customize_register_add_control() {
//
// 	/*	Add text control description
// 	/*-------------------------------------------*/
// 	class FontSwitch_Custom_Html extends WP_Customize_Control {
// 		public $type             = 'customtext';
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
?>
