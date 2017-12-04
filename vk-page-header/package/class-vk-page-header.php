<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

/*-------------------------------------------*/
/*  Template Tags
/*		is_theme()
/*		default_option()
/*		options_load()
/*		get_page_for_posts()
/*		get_post_type()
/*		get_all_post_types_info()
/*		header_image_url()
/*-------------------------------------------*/
/*  Customizer
/*-------------------------------------------*/
/*  page meta box
/*-------------------------------------------*/
/*  print head style
/*-------------------------------------------*/


if ( ! class_exists( 'Vk_Page_Header' ) ) {
	class Vk_Page_Header
	{

		public static $version = '0.0.0';
		private static $post_types = array( 'post' => 0 );

		public function __construct(){
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'wp_head', array( $this, 'dynamic_header_css' ), 3 );
			// add_action( 'customize_preview_init', array( $this, 'customize_pageinfo' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_pagehead_setting_meta_box' ) );
			add_action( 'save_post' , array( $this, 'save_custom_fields'), 10, 2);

		}

		/*-------------------------------------------*/
		/*  Template Tags
		/*-------------------------------------------*/

		/*	テーマで使用されているかプラグインで使用されているか
		/*		is_theme()
		/*-------------------------------------------*/
		public static function is_theme()
		{
			$path = __FILE__;
			preg_match('/\/themes\//', $path, $m);
			if ( $m ) {
				return true;
			} else {
				return false;
			}
		}

		/*		default_option()
		/*-------------------------------------------*/
		public static function default_option(){

			if ( self::is_theme() ){
				// このファイルがテーマで使われた場合
				$sample_image_url = get_template_directory_uri('/inc/vk-page-header/images/header-sample.jpg');
			} else {

				// プラグインの場合
				$sample_image_url = plugins_url('/images/header-sample.jpg',__FILE__);
			}

			$option['image_basic'] = $sample_image_url;

			return $option = apply_filters( 'vk_page_header_default_option', $option );
		}

		/*		options_load()
		/*-------------------------------------------*/
		public static function options_load(){

			// オプション値を取得 / オプション値が存在しなかったらデフォルトオプションを取得
			$option = get_option( 'vk_page_header', self::default_option() );

			// オプション値が存在しているが空の場合はデフォルトオプションを返す
			if( !$option ){ $option = self::default_option(); }

			return $option;
		}

		/*  	Chack use post top page
		/*		get_page_for_posts()
		/*-------------------------------------------*/
		public static 	function get_page_for_posts() {
			// Get post top page by setting display page.
			$page_for_posts['post_top_id'] = get_option( 'page_for_posts' );

			// Set use post top page flag.
			$page_for_posts['post_top_use'] = ( isset( $page_for_posts['post_top_id'] ) && $page_for_posts['post_top_id'] ) ? true : false ;

			// When use post top page that get post top page name.
			$page_for_posts['post_top_name'] = ( $page_for_posts['post_top_use'] ) ? get_the_title( $page_for_posts['post_top_id'] ) : '';

			return $page_for_posts;
		}

		/*  	Chack post type info
		/*		get_post_type()
		/*-------------------------------------------*/
		public static function get_post_type() {

			$page_for_posts = self::get_page_for_posts();

			// Get post type slug
			/*-------------------------------------------*/
			$postType['slug'] = get_post_type();
			if ( ! $postType['slug'] ) {
				global $wp_query;
				if ( $wp_query->query_vars['post_type'] ) {
					$postType['slug'] = $wp_query->query_vars['post_type'];
				} elseif ( is_tax() ) {
			  	// Case of tax archive and no posts
					$taxonomy = get_queried_object()->taxonomy;
					$postType['slug'] = get_taxonomy( $taxonomy )->object_type[0];
			  } else {
					// This is necessary that when no posts.
			  	$postType['slug'] = 'post';
			  }
			}

			// Get post type name
			/*-------------------------------------------*/
			$post_type_object = get_post_type_object( $postType['slug'] );
			if ( $post_type_object ) {
				if ( $page_for_posts['post_top_use'] && $postType['slug'] == 'post' ) {
					$postType['name'] = esc_html( get_the_title( $page_for_posts['post_top_id'] ) );
				} else {
					$postType['name'] = esc_html( $post_type_object->labels->name );
				}
			}

			// Get post type archive url
			/*-------------------------------------------*/
			if ( $page_for_posts['post_top_use'] && $postType['slug'] == 'post' ) {
				$postType['url'] = get_the_permalink( $page_for_posts['post_top_id'] );
			} else {
				$postType['url'] = home_url().'/?post_type='.$postType['slug'];
			}

			$postType = apply_filters( 'vkExUnit_postType_custom',$postType );
			return $postType;
		}

		/*		get_all_post_types_info()
		/*-------------------------------------------*/
		public static function get_all_post_types_info() {

			//gets all custom post types set PUBLIC
			$args = array(
				'public'   => true,
				// '_builtin' => false,
			);

			$custom_types = get_post_types( $args, 'objects' );
			$custom_types_labels = array();

			foreach ( $custom_types as $custom_type ) {
				$custom_types_labels[ $custom_type->name ] = $custom_type->label;
			}

			return $custom_types_labels;
		}

		/*		header_image_url()
		/*-------------------------------------------*/
		public function header_image_url(){

			$options = self::options_load();
			$post_type = self::get_post_type();

			if ( isset( $options['image_basic'] ) && $options['image_basic'] )
			{
				$image_url = $options['image_basic'];
			} else {
				// $options['image_basic']は空だが$optionsの他の値は入ってるというイレギュラーな状況の対応（本来は不要な範囲）
				// 共通設定の画像を削除された場合などにデフォルトのサンプル画像を表示する
				$default_option = self::default_option();
				$image_url = $default_option['image_basic'];
			}

			$image_url_field = 'image_'.$post_type['slug'];
			if ( isset( $options[$image_url_field] ) && $options[$image_url_field] ){
				$image_url = $options[$image_url_field];
			}

			// 固定ページの場合
			if ( $post_type['slug'] == 'page' ){
				 global $post;
				 if ( $post->vk_page_header_image ){
					 // 今の固定ページに画像が登録されていればそのまま使用
					 $image_id = $post->vk_page_header_image;
				 } else {
					 // 先祖階層を取得
						$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
						// array_push( $ancestors, $post->ID );
						foreach ( $ancestors as $ancestor ) {
							$vk_page_header_image = '';
							// 親階層から順に画像を取得し、下階層に画像があれば上書きしていく
							$vk_page_header_image = get_post_meta( $ancestor,'vk_page_header_image', true );
							if ( $vk_page_header_image ) {
								$image_id = $vk_page_header_image;
							}
						}
				 } // if ( $post->vk_page_header_image ){

				// 固定ページで画像の登録があった場合のみ $image_url を上書きする
				 if ( isset( $image_id ) && $image_id ) {
						$image_url = wp_get_attachment_image_src( $image_id, 'full', false);
						$image_url = $image_url[0];
				 }
			} // if ( $post_type == 'page' ){
			return $image_url;
		}


		/*-------------------------------------------*/
		/*  Customizer
		/*-------------------------------------------*/
		public function customize_register( $wp_customize )
		{

			global $vk_page_header_textdomain;
			global $customize_setting_prefix;

			$wp_customize->add_section( 'vk_page_header_setting', array(
				'title' => $customize_setting_prefix.' ページヘッダー 設定',
				'priority' => 700,
			) );

			$wp_customize->selective_refresh->add_partial( 'vk_page_header[text_color]', array(
	      'selector' => '.page-header .container',
	      'render_callback' => '',
	    ) );

			// color
			$wp_customize->add_setting( 'vk_page_header[text_color]', array(
				'default'			=> '',
				'type'				=> 'option',
				'capability'		=> 'edit_theme_options',
				'sanitize_callback' => 'sanitize_hex_color',
			) );
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'text_color', array(
				'label'    => __( 'Text color', $vk_page_header_textdomain ),
				'section'  => 'vk_page_header_setting',
				'settings' => 'vk_page_header[text_color]',
				// 'priority' => $priority,
			)));

			// color
			$wp_customize->add_setting( 'vk_page_header[text_shadow_color]', array(
				'default'			=> '',
				'type'				=> 'option',
				'capability'		=> 'edit_theme_options',
				'sanitize_callback' => 'sanitize_hex_color',
			) );
			$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'text_shadow_color', array(
				'label'    => __( 'Text shadow color', $vk_page_header_textdomain ),
				'section'  => 'vk_page_header_setting',
				'settings' => 'vk_page_header[text_shadow_color]',
				// 'priority' => $priority,
			)));

			// text position
	    $wp_customize->add_setting( 'vk_page_header[text_align]',  array(
	      'default'           => '',
	      'type'              => 'option',
	      'capability'        => 'edit_theme_options',
	      'sanitize_callback' => 'esc_attr',
	    ) );
	    $wp_customize->add_control( 'text_align', array(
	  		'label'     => __( 'Text align', $vk_page_header_textdomain ),
	  		'section'   => 'vk_page_header_setting',
	  		'settings'  => 'vk_page_header[text_align]',
	  		'type' => 'radio',
	      // 'priority' => $priority,
	  		'choices' => array(
					'left' => __( 'Left', $vk_page_header_textdomain ),
	  			'center' => __( 'Center', $vk_page_header_textdomain ),
	  			'right' => __( 'Right', $vk_page_header_textdomain ),
	  			),
	  	));


			/*	background common image
			--------------------------------------------- */
			$wp_customize->add_setting( 'vk_page_header[image_basic]', array(
				'default' => '',
				'type' => 'option',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'esc_url'
			) );
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'page_header_image_basic', array(
					'label' => __( 'Page header bg image', $vk_page_header_textdomain ). ' [ '.__('Basic',$vk_page_header_textdomain ).' ]',
					'section' => 'vk_page_header_setting',
					'settings' => 'vk_page_header[image_basic]',
					'description' => __( 'You can set the original image in the background of the page header part.', $vk_page_header_textdomain ),
				)
			));

			/*	background post type image
			--------------------------------------------- */
			$custom_types = Vk_Page_Header::get_all_post_types_info();
			foreach ($custom_types as $name => $label) {

				$wp_customize->add_setting( 'vk_page_header[image_'.$name.']', array(
					'default' => '',
					'type' => 'option',
					'capability' => 'edit_theme_options',
					'sanitize_callback' => 'esc_url'
				) );

				if ( $name == 'page' ){
					$description = __('If you want to change the image of a specific page, you can set it from the editing screen of each fixed page.', $vk_page_header_textdomain).'<br>';
				} else {
					$description = '';
				}
				$description .= '未設定の場合は [ 基本 ] の画像が適用されます。';

				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'page_header_image_'.$name, array(
						'label' => __( 'Page header bg image', $vk_page_header_textdomain ). ' [ '.$label.' ]',
						'section' => 'vk_page_header_setting',
						'settings' => 'vk_page_header[image_'.$name.']',
						'description' => $description,
					)
				));

			}

		}

		/*-------------------------------------------*/
		/*  page meta box
		/*-------------------------------------------*/
		/* static にすると環境によってmetabox内のコールバック関数が反応しない */
		public function add_pagehead_setting_meta_box(){
			global $vk_page_header_textdomain;
			add_meta_box( 'vk_page_header_meta_box', __( 'Page Header Image', $vk_page_header_textdomain ), array($this,'vk_page_header_meta_box_content'), 'page', 'normal', 'high' );
		}

		public function vk_page_header_meta_box_content(){
				self::fields_form();
		}

		public function fields_form()
		{
			$custom_fields_array = self::custom_fields_array();
			$befor_custom_fields = '';
			VK_Custom_Field_Builder::form_table( $custom_fields_array, $befor_custom_fields );
		}

		public function save_custom_fields(){
			$custom_fields_array = self::custom_fields_array();
			VK_Custom_Field_Builder::save_cf_value( $custom_fields_array );
		}

		public static function custom_fields_array(){
			global $vk_page_header_textdomain;
			$custom_fields_array = array(
				'vk_page_header_image' => array(
					'label' => __('Page header bg image', $vk_page_header_textdomain ),
					'type' => 'image',
					'description' => '',
					'required' => false,
				),
			);
			return $custom_fields_array;
		} // custom_fields_array(){

		/*-------------------------------------------*/
		/*  print head style
		/*-------------------------------------------*/

		public function dynamic_header_css(){

			// ページヘッダーPC画像設定
			if( !is_front_page() ){

				$dynamic_css = '';

				// ヘッダー背景画像URL取得
				$image_url = self::header_image_url();
				if ( $image_url )
				{
					$dynamic_css .= 'background: url('.esc_url( $image_url ).') no-repeat 50% center;';
					$dynamic_css .= 'background-size: cover;';
				}

				$options = self::options_load();

				if ( isset( $options['text_color'] ) && $options['text_color'] ){
					$dynamic_css .= 'color:'.$options['text_color'].';';
				}

				if ( isset( $options['text_shadow_color'] ) && $options['text_shadow_color'] ){
					$dynamic_css .= 'text-shadow:0px 0px 10px '.$options['text_shadow_color'].';';
				}

				if ( isset( $options['text_align'] ) && $options['text_align'] ) {
					// left 指定の場合は出力しないようにしたかったが、中央揃えがデフォルトのスキンもあるので、leftでもcss出力
					// if ( $options['text_align'] != 'left' ){
					$dynamic_css .= 'text-align:'.$options['text_align'].';';
					// }
				}

				// CSS が存在している場合のみ出力
				if ( $dynamic_css ) {
					// 対象とするclass名を取得
					global $vk_page_header_output_class;
					$dynamic_css = $vk_page_header_output_class."{".$dynamic_css."}";
					// 出力を実行
					wp_add_inline_style( 'lightning-design-style', $dynamic_css );
				}

			} // if( !is_front_page() ){

		} // public function skin_dynamic_css(){

	} // class Vk_Page_Header

	new Vk_Page_Header();
	// Vk_Page_Header::init();
}
