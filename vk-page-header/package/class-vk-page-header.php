<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

/*
  Template Tags
-------------------------------------------*/
/*
	  is_theme()
	  default_option()
	  options_load()
	  get_page_for_posts()
	  get_post_type()
	  get_all_post_types_info()
	  header_image_url()
/*
/*
  Customizer
  page meta box
  print head style
/*-------------------------------------------*/


if ( ! class_exists( 'Vk_Page_Header' ) ) {

	/*
	  customize_register
	/*-------------------------------------------*/
	add_action( 'customize_register', 'vk_page_header_customize_register' );
	function vk_page_header_customize_register( $wp_customize ) {

		/*
		  Add text control description
		/*-------------------------------------------*/
		class Vk_Page_Header_Custom_Text_Control extends WP_Customize_Control {
			public $type         = 'customtext';
			public $description  = ''; // we add this for the extra description
			public $input_before = '';
			public $input_after  = '';
			public $num_step  = '';
			public $num_min  = '';
			public $num_max  = '';
			public function render_content() {
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php $style = ( $this->input_before || $this->input_after ) ? ' style="width:50%"' : ''; ?>
				<div>
				<?php echo wp_kses_post( $this->input_before ); ?>
				<?php
				$step = '';
				$min = '';
				$max = '';
				if ( $this->type == 'text' ) {
					$type = 'text';
				} elseif ( $this->type == 'number' ) {
					$type = 'number';
					if ( $this->num_step ){
						$step = ' step="' . esc_attr(  $this->num_step ) . '"';
					}
					if ( $this->num_min ){
						$min = ' min="' . esc_attr( $this->num_min ) . '"';
					} else {
						$min = ' min="0"';
					}
					if ( $this->num_max ){
						$max = ' max="' . esc_attr(  $this->num_max ) . '"';
					}
				}
				?>
				<input type="<?php echo $type; ?>"<?php echo $step.$min.$max; ?> value="<?php echo esc_attr( $this->value() ); ?>"<?php echo $style; ?> <?php $this->link(); ?> />
				<?php echo wp_kses_post( $this->input_after ); ?>
				</div>
				<div><?php echo $this->description; ?></div>
			</label>
			<?php
			} // public function render_content() {
		} // class Vk_Page_Header_Custom_Text_Control extends WP_Customize_Control
	}

	class Vk_Page_Header {


		public static $version     = '0.1.0';
		private static $post_types = array( 'post' => 0 );

		public function __construct() {
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'wp_head', array( $this, 'dynamic_header_css' ), 5 );
			add_action( 'add_meta_boxes', array( $this, 'add_pagehead_setting_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_custom_fields' ), 10, 2 );

		}


		/*
		  Template Tags
		/*-------------------------------------------*/

		/*
		  テーマで使用されているかプラグインで使用されているか
		/*		is_theme()
		/*-------------------------------------------*/
		public static function is_theme() {
			$path = __FILE__;
			preg_match( '/\/themes\//', $path, $m );
			if ( $m ) {
				return true;
			} else {
				return false;
			}
		}

		/*
			  default_option()
		/*-------------------------------------------*/
		public static function default_option() {
			global $vk_page_header_default;
			return $option = apply_filters( 'vk_page_header_default_option', $vk_page_header_default );
		}

		/*
			  options_load()
		/*-------------------------------------------*/
		public static function options_load() {

			// オプション値を取得 / オプション値が存在しなかったらデフォルトオプションを取得
			$option = get_option( 'vk_page_header', self::default_option() );

			// オプション値が存在しているが空の場合はデフォルトオプションを返す
			// if ( is_array( $option ) && ! isset( $option['image_basic'] ) ) {
			// global $vk_page_header_default_bg_url;
			// $option['image_basic'] = $vk_page_header_default_bg_url;
			// }
			return $option;
		}

		/*
			  Chack use post top page
		/*		get_page_for_posts()
		/*-------------------------------------------*/
		public static   function get_page_for_posts() {
			// Get post top page by setting display page.
			$page_for_posts['post_top_id'] = get_option( 'page_for_posts' );

			// Set use post top page flag.
			$page_for_posts['post_top_use'] = ( isset( $page_for_posts['post_top_id'] ) && $page_for_posts['post_top_id'] ) ? true : false;

			// When use post top page that get post top page name.
			$page_for_posts['post_top_name'] = ( $page_for_posts['post_top_use'] ) ? get_the_title( $page_for_posts['post_top_id'] ) : '';

			return $page_for_posts;
		}

		/*
			  Chack post type info
		/*		get_post_type()
		/*-------------------------------------------*/
		public static function get_post_type() {

			$page_for_posts = self::get_page_for_posts();

			// Get post type slug
			/*-------------------------------------------*/
			$postType         = array(); // PHP7.2対策
			$postType['slug'] = get_post_type();
			if ( ! $postType['slug'] ) {
				global $wp_query;
				if ( ! empty( $wp_query->query_vars['post_type'] ) ) {
					$postType['slug'] = $wp_query->query_vars['post_type'];
				} elseif ( is_tax() ) {
					// Case of tax archive and no posts
					$taxonomy         = get_queried_object()->taxonomy;
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
				$postType['url'] = home_url() . '/?post_type=' . $postType['slug'];
			}

			$postType = apply_filters( 'vkExUnit_postType_custom', $postType );
			return $postType;
		}

		/*
			  get_all_post_types_info()
		/*-------------------------------------------*/
		public static function get_all_post_types_info() {

			// gets all custom post types set PUBLIC
			$args = array(
				'public' => true,
				// '_builtin' => false,
			);

			$custom_types        = get_post_types( $args, 'objects' );
			$custom_types_labels = array();

			foreach ( $custom_types as $custom_type ) {
				$custom_types_labels[ $custom_type->name ] = $custom_type->label;
			}

			return $custom_types_labels;
		}

		/*
			  header_image_url()
		/*-------------------------------------------*/
		public static function header_image_url( $size = '' ) {

			$options   = get_option( 'vk_page_header' );
			$post_type = self::get_post_type();

			if ( isset( $options['image_basic'] ) && $options['image_basic'] ) {

				// 普通に画像が登録されている場合
				$image_url = $options['image_basic'];

			} elseif ( ! isset( $options['image_basic'] ) ) {
				// この機能を新規インストールされた時のように画像が一度も登録されておらず、配列が存在しない場合
				$default_option = self::default_option();
				$image_url      = $default_option['image_basic'];

			} elseif ( isset( $options['image_basic'] ) && ! $options['image_basic'] ) {
				// 画像が意図的に未指定の場合
				$image_url = '';
			}

			if ( ! empty( $options['image_basic_sp'] ) && $size === 'sp' ) {
				$image_url = $options['image_basic_sp'];
			}

			// 投稿タイプ別の場合
			$image_url_field = 'image_' . $post_type['slug'];
			if ( isset( $options[ $image_url_field ] ) && $options[ $image_url_field ] ) {
				$image_url = $options[ $image_url_field ];
			}

			$image_url_field = 'image_' . $post_type['slug'] . '_sp';
			if ( ! empty( $options[ $image_url_field ] ) && $size === 'sp' ) {
				$image_url = $options[ $image_url_field ];
			}

			if ( is_singular() ){
				global $post;
			}

			// 固定ページの場合.
			// 検索結果ページでも $post_type['slug'] == 'page' に反応するため  ! is_search() && ! is_404() を追加.
			// is_page() でない理由は？？
			if ( $post_type['slug'] == 'page' && ! is_search() && ! is_404()  ) {

				if ( 'sp' == $size ){
					$target_field = 'vk_page_header_image_sp';
				} else {
					$target_field = 'vk_page_header_image';
				}

				$vk_page_header_image = get_post_meta( $post->ID, $target_field, true );
				if ( $vk_page_header_image ) {
					// 今の固定ページに画像が登録されていればそのまま使用
					$image_id = $vk_page_header_image;
				} else {
					// 先祖階層を取得
					$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
					   // array_push( $ancestors, $post->ID );
					foreach ( $ancestors as $ancestor ) {
						$vk_page_header_image = '';
						// 親階層から順に画像を取得し、下階層に画像があれば上書きしていく
						$vk_page_header_image = get_post_meta( $ancestor, $target_field, true );
						if ( $vk_page_header_image ) {
							$image_id = $vk_page_header_image;
						}
					}
				} // if ( $post->vk_page_header_image ){

				// 固定ページで画像の登録があった場合のみ $image_url を上書きする
				if ( isset( $image_id ) && $image_id ) {

					$image_url = wp_get_attachment_image_src( $image_id, 'full', false );
					// 元のメディアが削除されて画像が取得出来ない事があるため、画像がある時だけ上書き
					if ( $image_url ) {
						$image_url = $image_url[0];
					}
				}
			} elseif ( is_single() ) {
				$display_type = 'displaytype_'.$post_type['slug'];
				// デフォルトレイアウトじゃない場合
				if ( isset( $options[$display_type] ) &&  $options[$display_type] !== 'default' ){
					// アイキャッチ画像で上書き
					$image_url = get_the_post_thumbnail_url( $post->id, 'full' );
				}
			}

			return $image_url;
		}


		/*
		  Customizer
		/*-------------------------------------------*/
		public function customize_register( $wp_customize ) {

			global $customize_setting_prefix;
			global $customize_section_priority;
			global $vk_page_header_default;
			global $vk_page_header_output_class;

			$wp_customize->add_section(
				'vk_page_header_setting', array(
					'title'    => $customize_setting_prefix . __( 'Page Header Setting', 'vk_page_header_textdomain' ),
					'priority' => $customize_section_priority,
				)
			);

			global $vk_page_header_bg_color_hide;

			if ( ! $vk_page_header_bg_color_hide ){

				// bgcolor
				$wp_customize->add_setting(
					'vk_page_header[bg_color]', array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_hex_color',
					)
				);
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize, 'bg_color', array(
							'label'    => __( 'Background color', 'vk_page_header_textdomain' ),
							'section'  => 'vk_page_header_setting',
							'settings' => 'vk_page_header[bg_color]',
						// 'priority' => $priority,
						)
					)
				);
			}

			// cover color
			$wp_customize->add_setting(
				'vk_page_header[cover_color]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize, 'cover_color', array(
						'label'    => __( 'Cover color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[cover_color]',
					// 'priority' => $priority,
					)
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'vk_page_header[text_color]', array(
					'selector'        => $vk_page_header_output_class,
					'render_callback' => '',
				)
			);

			// text position
			$wp_customize->add_setting(
				'vk_page_header[cover_opacity]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);

			$wp_customize->add_control(
				new Vk_Page_Header_Custom_Text_Control(
					$wp_customize, 'cover_opacity', array(
						'label'       	=> __( 'Cover opacity', 'vk_page_header_textdomain' ),
						'section'     	=> 'vk_page_header_setting',
						'settings'    	=> 'vk_page_header[cover_opacity]',
						'type'        	=> 'number',
						'num_step'		=> 0.05,
						'num_min'		=> 0,
						'num_max'		=> 1,
						'description' 	=> __( 'Please enter a number from 0 to 1', 'vk_page_header_textdomain' ),
					)
				)
			);


			// color
			$wp_customize->add_setting(
				'vk_page_header[text_color]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize, 'text_color', array(
						'label'    => __( 'Text color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[text_color]',
					// 'priority' => $priority,
					)
				)
			);

			// color
			$wp_customize->add_setting(
				'vk_page_header[text_shadow_color]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize, 'text_shadow_color', array(
						'label'    => __( 'Text shadow color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[text_shadow_color]',
					// 'priority' => $priority,
					)
				)
			);

			// text position
			$wp_customize->add_setting(
				'vk_page_header[text_align]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);
			$wp_customize->add_control(
				'text_align', array(
					'label'    => __( 'Text align', 'vk_page_header_textdomain' ),
					'section'  => 'vk_page_header_setting',
					'settings' => 'vk_page_header[text_align]',
					'type'     => 'radio',
					// 'priority' => $priority,
					'choices'  => array(
						'left'   => __( 'Left', 'vk_page_header_textdomain' ),
						'center' => __( 'Center', 'vk_page_header_textdomain' ),
						'right'  => __( 'Right', 'vk_page_header_textdomain' ),
					),
				)
			);

			// text position
			$wp_customize->add_setting(
				'vk_page_header[text_margin]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);

			$wp_customize->add_control(
				new Vk_Page_Header_Custom_Text_Control(
					$wp_customize, 'text_margin', array(
						'label'       => __( 'Text margin (top and bottom)', 'vk_page_header_textdomain' ),
						'section'     => 'vk_page_header_setting',
						'settings'    => 'vk_page_header[text_margin]',
						'type'        => 'number',
						'description' => '',
						'input_after' => 'em',
					)
				)
			);


			/*
			  Basic
			--------------------------------------------- */

			// title
			$wp_customize->add_setting(
				'vk_page_header_title_common',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_page_header_title_common',
					array(
						'label'            => __( 'Page header bg image', 'vk_page_header_textdomain' ) . ' [ ' . __( 'Basic', 'vk_page_header_textdomain' ) . ' ]',
						'section'          => 'vk_page_header_setting',
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '<p>' . __( 'You can set the original image in the background of the page header part.', 'vk_page_header_textdomain' ) . '</p>',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_page_header[image_basic]', array(
					'default'           => $vk_page_header_default['image_basic'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_url',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize, 'page_header_image_basic', array(
						'label'       => __( 'PC', 'vk_page_header_textdomain' ),
						'section'     => 'vk_page_header_setting',
						'settings'    => 'vk_page_header[image_basic]',
						'description' => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_page_header[image_basic_sp]', array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_url',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize, 'page_header_image_basic_sp', array(
						'label'       => __( 'Mobile', 'vk_page_header_textdomain' ),
						'section'     => 'vk_page_header_setting',
						'settings'    => 'vk_page_header[image_basic_sp]',
					)
				)
			);

			/*
			  Post Type
			--------------------------------------------- */
			$custom_types = Vk_Page_Header::get_all_post_types_info();

			foreach ( $custom_types as $name => $label ) {

				if ( $name == 'page' ) {
					$description = __( 'If you want to change the image of a specific page, you can set it from the editing screen of each fixed page.', 'vk_page_header_textdomain' ) . '<br>';
				} else {
					$description = '';
				}
				$description .= __( 'When not set, the image of [ Basic ] is applied.', 'vk_page_header_textdomain' );

				// title
				$wp_customize->add_setting(
					'vk_page_header_title_' . $label,
					array(
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					new Custom_Html_Control(
						$wp_customize,
						'vk_page_header_title_' . $label,
						array(
							'label'            => __( 'Page header bg image', 'vk_page_header_textdomain' ) . ' [ ' . $label . ' ]',
							'section'          => 'vk_page_header_setting',
							'type'             => 'text',
							'custom_title_sub' => '',
							'custom_html'      => $description,
						)
					)
				);

				$wp_customize->add_setting(
					'vk_page_header[image_' . $name . ']', array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'esc_url',
					)
				);

				$wp_customize->add_control(
					new WP_Customize_Image_Control(
						$wp_customize, 'page_header_image_' . $name, array(
							'label'       => __( 'PC', 'vk_page_header_textdomain' ),
							'section'     => 'vk_page_header_setting',
							'settings'    => 'vk_page_header[image_' . $name . ']',
						)
					)
				);

				$wp_customize->add_setting(
					'vk_page_header[image_' . $name . '_sp]', array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'esc_url',
					)
				);
				$wp_customize->add_control(
					new WP_Customize_Image_Control(
						$wp_customize, 'page_header_image_' . $name . '_sp', array(
							'label'       => __( 'Mobile', 'vk_page_header_textdomain' ),
							'section'     => 'vk_page_header_setting',
							'settings'    => 'vk_page_header[image_' . $name . '_sp]',
						)
					)
				);

			}

			global $vk_page_header_use_type;
			if ( $vk_page_header_use_type ){
				// Single page display item
				$wp_customize->add_setting(
					'single_page_display_item',
					array(
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					new Custom_Html_Control(
						$wp_customize,
						'single_page_display_item',
						array(
							'label'            => __( 'Single page display item', 'katawara' ),
							'section'          => 'vk_page_header_setting',
							'type'             => 'text',
							'custom_title_sub' => '',
							'custom_html'      => '',
						)
					)
				);

				foreach ( $custom_types as $name => $label ) {
					if ( 'page' !== $name ) {
						$wp_customize->add_setting(
							'vk_page_header[displaytype_' . $name . ']',
							array(
								'default'           => 'default',
								'type'              => 'option',
							)
						);
						$wp_customize->add_control(
							'vk_page_header[displaytype_' . $name . ']',
							array(
								'label'       => __( 'Page header Content Setting', 'katawara' ) . ' [ ' . $label . ' ]',
								'section'     => 'vk_page_header_setting',
								'settings'    => 'vk_page_header[displaytype_' . $name . ']',
								'description' => '',
								'type'        => 'select',
								'choices'     => array(
									'default'             => __( 'Default', 'katawara' ),
									'thumbnail'           => __( 'Display only Thumbnail', 'katawara' ),
									'post_title_and_meta' => __( 'Display Title, Thumbnail and Post Meta', 'katawara' ),
								),
							)
						);
					}
				}
			} // if ( $vk_page_header_use_type ){
		}

		/*
		  page meta box
		/*-------------------------------------------*/
		/* static にすると環境によってmetabox内のコールバック関数が反応しない */
		public function add_pagehead_setting_meta_box() {

			add_meta_box( 'vk_page_header_meta_box', __( 'Page Header Image', 'vk_page_header_textdomain' ), array( $this, 'vk_page_header_meta_box_content' ), 'page', 'normal', 'high' );
		}

		public function vk_page_header_meta_box_content() {
				self::fields_form();
		}

		public function fields_form() {
			$custom_fields_array = self::custom_fields_array();
			$befor_custom_fields = '';
			VK_Custom_Field_Builder::form_table( $custom_fields_array, $befor_custom_fields );
		}

		public function save_custom_fields() {
			$custom_fields_array = self::custom_fields_array();
			VK_Custom_Field_Builder::save_cf_value( $custom_fields_array );
		}

		public static function custom_fields_array() {

			$custom_fields_array = array(
				'vk_page_header_image' => array(
					'label'       => __( 'Page header bg image', 'vk_page_header_textdomain' ),
					'type'        => 'image',
					'description' => '',
					'required'    => false,
				),
				'vk_page_header_image_sp' => array(
					'label'       => __( 'Page header bg image', 'vk_page_header_textdomain' ) . ' ( ' . __( 'Mobile', 'vk_page_header_textdomain' ) . ' )',
					'type'        => 'image',
					'description' => '',
					'required'    => false,
				),
			);
			return $custom_fields_array;
		} // custom_fields_array(){


		public static function get_layout( $layout = 'default' ) {
			$options = self::options_load();
			if ( is_single() ) {
				$display_type = 'displaytype_'.get_post_type(); // カスタム分類アーカイブと違って取得ミスがないため標準関数を使用
				// 表示タイプが 標準レイアウトじゃない（記事タイトルや日付など）場合
				if ( ! empty( $options[$display_type] ) ){
					$layout	= $options[$display_type];
				}
			}
			return $layout;
		}


		/*
		  print head style
		/*-------------------------------------------*/

		public function dynamic_header_css() {

			/*
			アウター部分のCSS
			/*-------------------------------------------*/
			if ( ! is_front_page() ) {

				$title_outer_dynamic_css = '';

				$options = self::options_load();

				// ヘッダー背景画像URL取得
				$image_url = self::header_image_url();
				$image_url_sp = self::header_image_url( 'sp' );

				$variables_dynamic_css = '';
				if ( $image_url ) {
					$variables_dynamic_css .= ':root{
						--vk-page-header-url : url(' . esc_url( $image_url ) . ');
					}';
				}
				if ( $image_url ) {
					$variables_dynamic_css .= '
					@media ( max-width:575.98px ){
						:root{
							--vk-page-header-url : url(' . esc_url( $image_url_sp ) . ');
						}
					}';
				}

				if ( isset( $options['text_color'] ) && $options['text_color'] ) {
					$title_outer_dynamic_css .= 'color:' . $options['text_color'] . ';';
				} else if ( self::get_layout() == 'post_title_and_meta' ){
					$title_outer_dynamic_css .= 'color:#fff;';
				}

				if ( isset( $options['text_shadow_color'] ) && $options['text_shadow_color'] ) {
					$title_outer_dynamic_css .= 'text-shadow:0px 0px 10px ' . $options['text_shadow_color'] . ';';
				}

				if ( isset( $options['text_align'] ) && $options['text_align'] ) {
					// left 指定の場合は出力しないようにしたかったが、中央揃えがデフォルトのスキンもあるので、leftでもcss出力
					// if ( $options['text_align'] != 'left' ){
					$title_outer_dynamic_css .= 'text-align:' . $options['text_align'] . ';';
					// }
				}

				if ( isset( $options['bg_color'] ) && $options['bg_color'] ) {
					$title_outer_dynamic_css .= 'background-color:' . $options['bg_color'] . ';';
				}

				if ( $image_url ) {
					$title_outer_dynamic_css .= 'background: var(--vk-page-header-url, url(' . esc_url( $image_url ) . ') ) no-repeat 50% center;';
					$title_outer_dynamic_css .= 'background-size: cover;';
				}

				// アウター部分のセレクタと結合
				if ( $title_outer_dynamic_css ) {
					// 対象とするclass名を取得
					global $vk_page_header_output_class;
					$title_outer_dynamic_css = $vk_page_header_output_class . '{ position:relative;' . $title_outer_dynamic_css . '}';

				}

				// 表示タイプが 標準レイアウトじゃない（記事タイトルや日付など）場合
				if ( self::get_layout() === 'post_title_and_meta' ){
					if ( empty( $options['cover_color'] ) ){
						$options['cover_color'] = '#000';
					}
					if ( empty( $options['cover_opacity'] ) ){
						$options['cover_opacity'] = '0.5';
					}
				}

				// カバー部分
				if ( ! empty( $options['cover_color'] ) || ! empty( $options['cover_opacity'] ) ) {

					$title_outer_dynamic_css .= $vk_page_header_output_class . '::before{
						content:"";
						position:absolute;
						top:0;
						left:0;
						background-color:' . $options['cover_color'] . ';
						opacity:' . $options['cover_opacity'] . ';
						width:100%;
						height:100%;
					}';
				}

				/*
				テキスト部分のCSS
				/*-------------------------------------------*/
				$title_text_dynamic_css = '';
				if ( isset( $options['text_margin'] ) && $options['text_margin'] ) {
					// if ( $options['text_align'] != 'left' ){
					$title_text_dynamic_css .= 'margin-top:' . $options['text_margin'] . 'em;';
					$title_text_dynamic_css .= 'margin-bottom:calc( ' . $options['text_margin'] . 'em - 0.1em );';

					// インナーセレクタとCSSの結合
					if ( $title_text_dynamic_css ) {
						global $vk_page_header_inner_class;
						$title_text_dynamic_css = $vk_page_header_inner_class . '{' . $title_text_dynamic_css . '}';
					}
				}

				// CSS が存在している場合のみ出力
				if ( $title_outer_dynamic_css || $title_text_dynamic_css ) {

					$dynamic_css = $variables_dynamic_css . $title_outer_dynamic_css . $title_text_dynamic_css;

					// delete before after space
					$dynamic_css = trim( $dynamic_css );
					// convert tab and br to space
					$dynamic_css = preg_replace( '/[\n\r\t]/', '', $dynamic_css );
					// Change multiple spaces to single space
					$dynamic_css = preg_replace( '/\s(?=\s)/', '', $dynamic_css );

					$dynamic_css = '/* page header */' . $dynamic_css;

					// 出力を実行
					global $vk_page_header_enqueue_handle_style;
					wp_add_inline_style( $vk_page_header_enqueue_handle_style, $dynamic_css );
				}
			} // if( !is_front_page() ){

		} // public function skin_dynamic_css(){

	} // class Vk_Page_Header

	// フックではずしやすいようにグローバル変数にいれている
	$vk_page_header = new Vk_Page_Header();

}
