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
// 		// カスタマイザーが利用されるので、独自のコントロールクラスを追加
//
// }


if ( ! class_exists( 'Vk_Goole_Tag_Manager' ) ) {

  class Vk_Goole_Tag_Manager
  {

    public static $version = '0.0.0';

    /*-------------------------------------------*/
    /*	Customizer
    /*-------------------------------------------*/

    public function __construct() {
      add_action( 'customize_register', array( $this, 'vk_google_tag_manager_customize_register' ) );
    }

    public function vk_google_tag_manager_customize_register( $wp_customize ) {

      // セクション、テーマ設定、コントロールを追加
      global $vk_google_tag_manager_textdomain;

      // セクション追加
      $wp_customize->add_section(
          'vk_google_tag_manager_related_setting', array(
          'title'    => __( 'Lightning Google Tag Manager', $vk_google_tag_manager_textdomain ),
          'priority' => 900,
          )
      );

			// 	// nav_common
			// 	$wp_customize->add_setting( 'nav_common', array(
			// 		'sanitize_callback' => 'sanitize_text_field'
			// 		)
			// 	);
			// 	$wp_customize->add_control(
			// 		new MobileNav_Custom_Html(
			// 			$wp_customize, 'nav_common', array(
			// 				'label'            => __( 'Navi Common Settings', $vk_google_tag_manager_textdomain ),
			// 				'section'          => 'vk_google_tag_manager_related_setting',
			// 				'type'             => 'text',
			// 				'custom_title_sub' => '',
			// 				'custom_html'      => '',
			// 			)
			// 		)
			// 	);
			//

			// link_text セッティング
			$wp_customize->add_setting(
					'vk_google_tag_manager_related_options[link_text]', array(
					'default'           => '',
					'type'              => 'option', // 保存先 option or theme_mod
					'capability'        => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'sanitize_text_field',
					)
			);

			// link_text コントロール
			$wp_customize->add_control(
					'link_text', array(
					'label'    => __( 'Link text:', $vk_google_tag_manager_textdomain ),
					'section'  => 'vk_google_tag_manager_related_setting',
					'settings' => 'vk_google_tag_manager_related_options[link_text]',
					'type'     => 'text',
					)
			);



      /*-------------------------------------------*/
      /*	Add Edit Customize Link Btn
      /*-------------------------------------------*/
      // $wp_customize->selective_refresh->add_partial(
      //   'vk_google_tag_manager_related_options[nav_bg_color]', array(
      //     'selector'        => '.mobil-fix-nav',
      //     'render_callback' => '',
      //   )
      // );

    } // function vk_google_tag_manager_customize_register( $wp_customize ) {

  } // class Vk_Goole_Tag_Manager {

  $vk_mobile_fix_nav = new Vk_Goole_Tag_Manager();

} // if ( ! class_exists('Vk_Goole_Tag_Manager') )  {

// add_action( 'wp_footer', 'vk_mobil_fix_nav' );
// function vk_mobil_fix_nav() {
// } // function vk_mobil_fix_nav() {
?>
