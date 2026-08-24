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
			public $num_step     = '';
			public $num_min      = '';
			public $num_max      = '';
			public function render_content() {
				?>
			<label>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php $style = ( $this->input_before || $this->input_after ) ? ' style="width:50%"' : ''; ?>
				<div>
				<?php echo wp_kses_post( $this->input_before ); ?>
				<?php
				$step = '';
				$min  = '';
				$max  = '';
				if ( $this->type == 'text' ) {
					$type = 'text';
				} elseif ( $this->type == 'number' ) {
					$type = 'number';
					if ( $this->num_step ) {
						$step = ' step="' . esc_attr( $this->num_step ) . '"';
					}
					if ( $this->num_min ) {
						$min = ' min="' . esc_attr( $this->num_min ) . '"';
					} else {
						$min = ' min="0"';
					}
					if ( $this->num_max ) {
						$max = ' max="' . esc_attr( $this->num_max ) . '"';
					}
				}
				?>
				<input type="<?php echo $type; ?>"<?php echo $step . $min . $max; ?> value="<?php echo esc_attr( $this->value() ); ?>"<?php echo $style; ?> <?php $this->link(); ?> />
				<?php echo wp_kses_post( $this->input_after ); ?>
				</div>
				<div><?php echo $this->description; ?></div>
			</label>
				<?php
			} // public function render_content() {
		} // class Vk_Page_Header_Custom_Text_Control extends WP_Customize_Control
	}

	class Vk_Page_Header {


		public static $version     = '0.1.2';
		private static $post_types = array( 'post' => 0 );

		public function __construct() {
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'wp_head', array( $this, 'dynamic_header_css' ), 5 );
			add_action( 'add_meta_boxes', array( $this, 'add_pagehead_setting_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_custom_fields' ), 10, 2 );
			// Register meta for REST API access (block editor native panel).
			// ブロックエディタのネイティブパネルから読み書きするためにメタキーを REST API に公開する。
			add_action( 'init', array( $this, 'register_page_header_meta' ) );
			// Enqueue block editor panel script.
			// ブロックエディタのサイドバーパネル用スクリプトを読み込む。
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_page_header_panel' ) );
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
		public static function get_page_for_posts() {
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

			if ( is_singular() ) {
				global $post;
			}

			// 固定ページの場合
			// 検索結果ページでも $post_type_info['slug'] == 'page' に反応してしまうため
			// ! is_search() && ! is_404() を追加.
			if ( 'page' === $post_type['slug'] && ! is_search() && ! is_404() ) {

				if ( 'sp' == $size ) {
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
				$display_type = 'displaytype_' . $post_type['slug'];
				// デフォルトレイアウトじゃない場合
				if ( isset( $options[ $display_type ] ) && $options[ $display_type ] !== 'default' ) {
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
				'vk_page_header_setting',
				array(
					'title'    => $customize_setting_prefix . __( 'Page Header Setting', 'vk_page_header_textdomain' ),
					'priority' => $customize_section_priority,
				)
			);

			global $vk_page_header_bg_color_hide;

			if ( ! $vk_page_header_bg_color_hide ) {

				// bgcolor
				$wp_customize->add_setting(
					'vk_page_header[bg_color]',
					array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_hex_color',
					)
				);
				$wp_customize->add_control(
					new WP_Customize_Color_Control(
						$wp_customize,
						'bg_color',
						array(
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
				'vk_page_header[cover_color]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'cover_color',
					array(
						'label'    => __( 'Cover color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[cover_color]',
					// 'priority' => $priority,
					)
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'vk_page_header[text_color]',
				array(
					'selector'        => $vk_page_header_output_class,
					'render_callback' => '',
				)
			);

			// text position
			$wp_customize->add_setting(
				'vk_page_header[cover_opacity]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);

			$wp_customize->add_control(
				new Vk_Page_Header_Custom_Text_Control(
					$wp_customize,
					'cover_opacity',
					array(
						'label'       => __( 'Cover opacity', 'vk_page_header_textdomain' ),
						'section'     => 'vk_page_header_setting',
						'settings'    => 'vk_page_header[cover_opacity]',
						'type'        => 'number',
						'num_step'    => 0.05,
						'num_min'     => 0,
						'num_max'     => 1,
						'description' => __( 'Please enter a number from 0 to 1', 'vk_page_header_textdomain' ),
					)
				)
			);

			// color
			$wp_customize->add_setting(
				'vk_page_header[text_color]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'text_color',
					array(
						'label'    => __( 'Text color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[text_color]',
					// 'priority' => $priority,
					)
				)
			);

			// color
			$wp_customize->add_setting(
				'vk_page_header[text_shadow_color]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'text_shadow_color',
					array(
						'label'    => __( 'Text shadow color', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[text_shadow_color]',
					// 'priority' => $priority,
					)
				)
			);

			// text position
			$wp_customize->add_setting(
				'vk_page_header[text_align]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);
			$wp_customize->add_control(
				'text_align',
				array(
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
				'vk_page_header[text_margin]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_attr',
				)
			);

			$wp_customize->add_control(
				new Vk_Page_Header_Custom_Text_Control(
					$wp_customize,
					'text_margin',
					array(
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
				new VK_Custom_Html_Control(
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
				'vk_page_header[image_basic]',
				array(
					'default'           => $vk_page_header_default['image_basic'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_url',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'page_header_image_basic',
					array(
						'label'       => __( 'PC', 'vk_page_header_textdomain' ),
						'section'     => 'vk_page_header_setting',
						'settings'    => 'vk_page_header[image_basic]',
						'description' => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_page_header[image_basic_sp]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'esc_url',
				)
			);
			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					'page_header_image_basic_sp',
					array(
						'label'    => __( 'Mobile', 'vk_page_header_textdomain' ),
						'section'  => 'vk_page_header_setting',
						'settings' => 'vk_page_header[image_basic_sp]',
					)
				)
			);

			/*
			  Post Type
			--------------------------------------------- */
			$custom_types = self::get_all_post_types_info();

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
					new VK_Custom_Html_Control(
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
					'vk_page_header[image_' . $name . ']',
					array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'esc_url',
					)
				);

				$wp_customize->add_control(
					new WP_Customize_Image_Control(
						$wp_customize,
						'page_header_image_' . $name,
						array(
							'label'    => __( 'PC', 'vk_page_header_textdomain' ),
							'section'  => 'vk_page_header_setting',
							'settings' => 'vk_page_header[image_' . $name . ']',
						)
					)
				);

				$wp_customize->add_setting(
					'vk_page_header[image_' . $name . '_sp]',
					array(
						'default'           => '',
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'esc_url',
					)
				);
				$wp_customize->add_control(
					new WP_Customize_Image_Control(
						$wp_customize,
						'page_header_image_' . $name . '_sp',
						array(
							'label'    => __( 'Mobile', 'vk_page_header_textdomain' ),
							'section'  => 'vk_page_header_setting',
							'settings' => 'vk_page_header[image_' . $name . '_sp]',
						)
					)
				);

			}

			global $vk_page_header_use_type;
			if ( $vk_page_header_use_type ) {
				// Single page display item
				$wp_customize->add_setting(
					'single_page_display_item',
					array(
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					new VK_Custom_Html_Control(
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
								'default' => 'default',
								'type'    => 'option',
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
			// Skip on block editor screens - the native sidebar panel replaces the metabox.
			// Classic Editor users still see the metabox.
			// ブロックエディタ画面ではネイティブパネルが代替するため登録しない。
			// クラシックエディタのユーザーには従来のメタボックスがそのまま表示される。
			$screen = get_current_screen();
			if ( $screen && $screen->is_block_editor ) {
				return;
			}
			// 投稿トップは固定ページでなくアーカイプページ判定されるので、
			// 投稿トップにわりあてた固定ページで指定したカラム数は反映されない。
			// よって、誤解を避けるためにレイアウト設定を含む Lightningデザイン設定のmetabox自体表示しないようにする
			if ( isset( $_GET['post'] ) && $_GET['post'] === get_option( 'page_for_posts' ) && 'page' === get_option( 'show_on_front' ) ) {
				return;
			}
			add_meta_box( 'vk_page_header_meta_box', __( 'Page Header Image', 'vk_page_header_textdomain' ), array( $this, 'vk_page_header_meta_box_content' ), 'page', 'normal', 'high', array( '__back_compat_meta_box' => true ) );
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
			if ( ! is_customize_preview() ) {
				$custom_fields_array = self::custom_fields_array();
				VK_Custom_Field_Builder::save_cf_value( $custom_fields_array );
			}
		}

		/**
		 * Register post meta for REST API access.
		 * ブロックエディタのサイドバーパネルから読み書きするためにメタキーを REST API に公開する。
		 *
		 * @return void
		 */
		public function register_page_header_meta() {
			// Page header images (page post type only).
			// ページヘッダー画像（固定ページのみ）。
			register_post_meta(
				'page',
				'vk_page_header_image',
				array(
					'type'              => 'integer',
					'single'            => true,
					'show_in_rest'      => true,
					'auth_callback'     => function () {
						return current_user_can( 'edit_pages' );
					},
					'sanitize_callback' => 'absint',
					'default'           => 0,
				)
			);

			register_post_meta(
				'page',
				'vk_page_header_image_sp',
				array(
					'type'              => 'integer',
					'single'            => true,
					'show_in_rest'      => true,
					'auth_callback'     => function () {
						return current_user_can( 'edit_pages' );
					},
					'sanitize_callback' => 'absint',
					'default'           => 0,
				)
			);
		}

		/**
		 * Enqueue block editor panel script for page header image.
		 * ページヘッダー画像のサイドバーパネル用スクリプトを読み込む。
		 *
		 * @return void
		 */
		public function enqueue_page_header_panel() {
			$current_screen = get_current_screen();
			// Only load on page edit screen.
			// 固定ページの編集画面でのみ読み込む。
			if ( ! $current_screen || 'page' !== $current_screen->post_type ) {
				return;
			}

			$script_dir  = wp_normalize_path( dirname( __FILE__ ) );
			$script_path = $script_dir . '/js/vk-page-header-panel.min.js';
			if ( ! file_exists( $script_path ) ) {
				return;
			}

			// テーマ配下・プラグイン配下・mu-plugins 配下のいずれに置かれても正しい URL になるよう、
			// get_directory_uri()（同ファイル内）で解決する。AWS Bitnami 等の
			// シンボリックリンク環境や、wp-content を WordPress 本体の外に置いた構成でも
			// 正しく解決できるため、以前の「解決できない場合は読み込みを中止する」対症療法は
			// 不要になった（issue #172）。
			$script_url = self::get_directory_uri( $script_dir ) . 'js/vk-page-header-panel.min.js';

			wp_enqueue_script(
				'vk-page-header-panel',
				$script_url,
				array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-block-editor' ),
				filemtime( $script_path ),
				true
			);

			wp_localize_script(
				'vk-page-header-panel',
				'vkPageHeaderPanelI18n',
				array(
					'pageHeaderTitle' => __( 'Page Header Image', 'vk_page_header_textdomain' ),
					'pageHeaderBg'    => __( 'Page header bg image', 'vk_page_header_textdomain' ),
					'mobile'          => __( 'Mobile', 'vk_page_header_textdomain' ),
					'selectImage'     => __( 'Select image' ),
					'changeImage'     => __( 'Change image' ),
					'removeImage'     => __( 'Remove image' ),
				)
			);
		}

		// vk-path-to-url:begin
		/**
		 * ファイルパスを URL へ変換する.
		 *
		 * [重要] この実装は vk-campaign-text / vk-mobile-fix-nav / vk-page-header の
		 * 3モジュールに、意図的に同一内容で存在する（VK_Helpers 等への共通化はしていない）。
		 * 理由: グローバルクラス名 VK_Helpers は、Lightning（_g3）や Katawara などの製品側で
		 * composer 版 vektor-inc/vk-helpers（クラス VkHelpers）のサブクラス・エイリアスに
		 * 差し替えられており、class_exists( 'VK_Helpers' ) による機能検出では「同じ名前だが
		 * 中身が違うクラス」を掴んでしまい、本来のメソッドが呼べない（issue #172 のレビューで
		 * 判明）。そのためモジュール固有のクラス名の中に private static として持たせている。
		 * このメソッドを修正する場合は、必ず他の2モジュールの同名メソッドも同じ内容に揃えること。
		 * 参照実装: vektor-inc/font-awesome-versions#57, vektor-inc/vk-swiper#14
		 *
		 * AWS Bitnami 等、シンボリックリンクで WordPress が配置された環境では、
		 * 引数 $path（呼び出し元は __FILE__ / __DIR__ ベース。シンボリックリンクを辿って実体パスを返す）と
		 * WP_PLUGIN_DIR 等の WordPress の定数（シンボリックリンクを辿らない文字列のまま）の表記が食い違い、
		 * 単純な前方一致では基準ディレクトリのどれにも一致しないことがある（issue #172）。
		 * また ABSPATH や WP_CONTENT_DIR を基準にした str_replace() による変換は、
		 * wp-content を WordPress 本体の外に置いた構成でも同様に壊れるため、この関数では使わない。
		 * そのため、次の4段階で解決を試みる。
		 * 通常構成（シンボリックリンク無し・標準配置）では段階 (0) の最初の一致で確定するため
		 * 後続の段階は一切実行されず、挙動・戻り値ともに従来から変化しない。
		 * (0) まず変換無しでそのまま突き合わせる（従来と完全に同一の経路）。
		 * (1) (0) で一致しなければ、WordPress 本体の対応表（$wp_plugin_paths）による変換を試す。
		 * (2) (1) でも一致しなければ、基準ディレクトリ側を realpath() で実体パスへ変換して再度突き合わせる。
		 * (3) それでも解決できなければ、空文字（ドメイン直結の壊れた URL の原因になる）を返さず、
		 *     content_url() を最後の手段として返す（詳細は各処理のコメントを参照）。
		 *
		 * @param string $path 変換対象のファイルパス.
		 * @return string 変換後の URL（末尾スラッシュ付き）.
		 */
		private static function get_directory_uri( $path ) {

			$path = wp_normalize_path( $path );

			// 子テーマ・親テーマ・プラグイン・mu-plugins・wp-content はそれぞれ独立にカスタマイズ
			// 可能なため、より具体的なディレクトリから順にマッチさせて URL を生成する。
			// テーマルートは get_theme_root()（引数無し）だと常に wp-content/themes 固定になり、
			// register_theme_directory() で追加された場所にあるテーマを解決できないため、
			// get_stylesheet() / get_template() を渡して現在有効なテーマ（子テーマ・親テーマ）の
			// 実際のテーマルートを取得する。WP_CONTENT_DIR は他すべての親ディレクトリのため
			// 必ず最後に置く。
			// 配列のキーにディレクトリを使うと、カスタマイズでキーが重複した際に後勝ちで
			// 上書きされてしまうため、dir / url の組の配列にしている.
			$directories = array(
				array(
					'dir' => wp_normalize_path( get_theme_root( get_stylesheet() ) ),
					'url' => get_theme_root_uri( get_stylesheet() ),
				),
				array(
					'dir' => wp_normalize_path( get_theme_root( get_template() ) ),
					'url' => get_theme_root_uri( get_template() ),
				),
				array(
					'dir' => wp_normalize_path( WP_PLUGIN_DIR ),
					'url' => plugins_url(),
				),
				array(
					'dir' => wp_normalize_path( WPMU_PLUGIN_DIR ),
					'url' => WPMU_PLUGIN_URL,
				),
				array(
					'dir' => wp_normalize_path( WP_CONTENT_DIR ),
					'url' => content_url(),
				),
			);

			// (0) まず従来どおり、変換無しでそのまま突き合わせる（通常構成はここで確定する）.
			$uri = self::match_directory_uri( $path, $directories );

			// (1) (0) で一致しなければ、WordPress 本体が持つシンボリックリンク対応表
			// （$wp_plugin_paths。plugin_basename() が内部で使っているもの）による変換を試す.
			if ( '' === $uri ) {
				$uri = self::match_directory_uri( self::resolve_symlinked_plugin_path( $path ), $directories );
			}

			// (2) (1) でも一致しなければ、基準ディレクトリ側を realpath() で実体パスへ解決してから、
			// 変換前の $path（シンボリックリンクを辿った実体パスのまま）と改めて突き合わせる.
			if ( '' === $uri ) {
				$uri = self::match_directory_uri( $path, self::realpath_directories( $directories ) );
			}

			if ( '' !== $uri ) {
				return $uri;
			}

			// (3) (0)(1)(2) のいずれでも解決できなかったときの最後の手段.
			// 空文字を返すと、呼び出し元で相対パスのまま wp_enqueue_style() 等に渡ることになり、
			// サイト URL（末尾スラッシュ無し）とドメインが直結した壊れた URL になる（issue #172）。
			// content_url() はファイルシステムとの突き合わせを行わない純粋な URL 生成関数のため、
			// このケースでも安全にサイト内の絶対 URL を返せる。実際に配置先ディレクトリと
			// 一致しない可能性はある（読み込むファイルが 404 になりうる）が、ドメイン直結の壊れた URL は避けられる.
			//
			// 解決できないまま処理が続くと運用者が気づけないため、原因追跡できるよう記録を残す。
			// この関数は1リクエスト中に複数回呼ばれることがあるため、static 変数で
			// 「同一リクエスト内・同じパスにつき1回だけ」記録するようガードする.
			static $logged_paths = array();
			if ( ! isset( $logged_paths[ $path ] ) ) {
				$logged_paths[ $path ] = true;

				// trigger_error() は使わない。WP_DEBUG が有効なとき WP_DEBUG_DISPLAY の既定（表示する）
				// により画面へ出てしまい、このメソッドの呼び出し元はフロント側（wp_enqueue_scripts 等）
				// のため、ログインしていない一般の閲覧者にもサーバー内の絶対パスが見えてしまう。
				// そのため error_log() の1本のみで記録し、画面には一切出さない。WP_DEBUG による
				// 条件分岐も付けない（本番環境でも原因追跡できるようにするため、常に記録する）。
				// ログはロケールによらず同じ文字列の方が調査しやすいため、翻訳・エスケープは行わない.
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- 本番環境でも原因追跡できるよう意図的に使用している.
				error_log( sprintf( 'VK Page Header: Could not resolve path to URL: %s', $path ) );
			}

			return trailingslashit( content_url() );
		}

		/**
		 * シンボリックリンク対応の変換.
		 *
		 * [重要] この実装は get_directory_uri() と同様、3モジュールに同一内容で存在する
		 * 意図的な重複である。修正時は他の2モジュールも揃えること。参照実装は
		 * get_directory_uri() の PHPDoc を参照.
		 *
		 * WordPress 本体が持つグローバル変数 $wp_plugin_paths（論理パス（定数ベース）→実体パス（realpath）の
		 * 対応表。plugin_basename() が内部で使っているもの）を使って、実体パス表記の $path を
		 * 論理パス（定数ベース）表記へ変換する。対応表に一致するエントリが無ければ $path をそのまま返す。
		 *
		 * この対応表を直接参照し plugin_basename() 自体は呼び出していない。plugin_basename() は
		 * WP_PLUGIN_DIR / WPMU_PLUGIN_DIR からの相対パス（basename）へ変換してしまうため、
		 * テーマ配下・wp-content 直下など他の基準ディレクトリの判定に使い回せなくなるためである。
		 * 対応表の構造・突き合わせ方（値が長いものを優先する arsort()）は plugin_basename() の実装に合わせている.
		 *
		 * @param string $path 変換対象のパス（wp_normalize_path 済み）.
		 * @return string 変換後のパス（対応表に一致しなければ $path のまま）.
		 */
		private static function resolve_symlinked_plugin_path( $path ) {
			global $wp_plugin_paths;

			if ( empty( $wp_plugin_paths ) || ! is_array( $wp_plugin_paths ) ) {
				return $path;
			}

			// 値（実体パス）が長いものから優先的に評価する。plugin_basename() と同じ arsort().
			$plugin_paths = $wp_plugin_paths;
			arsort( $plugin_paths );

			foreach ( $plugin_paths as $dir => $realdir ) {
				// $dir = 論理パス（定数ベース）, $realdir = 実体パス（realpath）.
				// 比較（境界判定）・切り出し（substr）・連結（返り値）の3箇所すべてで
				// 末尾スラッシュを取り除いた同じ基準の文字列を使う。基準が食い違うと、
				// $realdir の末尾にスラッシュが付いているだけでフォルダの区切りが消えた
				// 壊れたパスを返してしまう.
				$realdir = untrailingslashit( wp_normalize_path( $realdir ) );
				// ディレクトリ境界を正しく判定する。例: /srv/foo が /srv/foo-bar に誤マッチしないようにする。
				// $realdir が空文字の場合、PHP 8 では strpos( $path, '' ) が 0 を返し全マッチしてしまうため、
				// 空文字チェックも合わせて行う.
				if ( '' !== $realdir && ( $path === $realdir || 0 === strpos( $path, $realdir . '/' ) ) ) {
					return untrailingslashit( wp_normalize_path( $dir ) ) . substr( $path, strlen( $realdir ) );
				}
			}

			return $path;
		}

		/**
		 * 基準ディレクトリの配列（dir/url の組）を、dir 側を realpath() で実体パスへ解決した配列に変換する。
		 * realpath() は存在しないパスに false を返すため、その場合はマッチ対象から除外する
		 * （false のまま突き合わせに使うと strpos() 等で意図しない挙動になるため）。
		 *
		 * [重要] この実装は 3モジュールに同一内容で存在する意図的な重複である。
		 * 修正時は他の2モジュールも揃えること。参照実装は get_directory_uri() の PHPDoc を参照.
		 *
		 * @param array $directories dir/url の組の配列.
		 * @return array realpath 解決後の dir/url の組の配列（解決できなかったものは除外済み）.
		 */
		private static function realpath_directories( $directories ) {
			$resolved = array();
			foreach ( $directories as $directory ) {
				// match_directory_uri() と同じ入力検証（dir/url が揃っていない要素は候補から除外する）.
				if ( empty( $directory['dir'] ) || ! isset( $directory['url'] ) ) {
					continue;
				}
				// open_basedir 制限下のホストでは、制限外パスへの realpath() が E_WARNING を出すことがある。
				// 戻り値 false は後続で正しく処理できる想定のため、警告だけがログを汚さないよう '@' で抑制する.
				$real_dir = @realpath( $directory['dir'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- open_basedir 制限下での E_WARNING 抑制のため（false は後続で処理する）.
				if ( false === $real_dir ) {
					continue;
				}
				$resolved[] = array(
					'dir' => wp_normalize_path( $real_dir ),
					'url' => $directory['url'],
				);
			}
			return $resolved;
		}

		/**
		 * $path が $directories（dir/url の組の配列）のいずれかの dir と前方一致するか調べ、
		 * 一致したら URL を組み立てて返す。一致しなければ空文字を返す.
		 *
		 * [重要] この実装は 3モジュールに同一内容で存在する意図的な重複である。
		 * 修正時は他の2モジュールも揃えること。参照実装は get_directory_uri() の PHPDoc を参照.
		 *
		 * @param string $path 判定対象のパス.
		 * @param array  $directories dir/url の組の配列.
		 * @return string 一致した場合は URL（末尾スラッシュ付き）、しなければ空文字.
		 */
		private static function match_directory_uri( $path, $directories ) {
			foreach ( $directories as $directory ) {
				if ( empty( $directory['dir'] ) || ! isset( $directory['url'] ) ) {
					continue;
				}
				// ディレクトリ境界を正しく判定するため、末尾のスラッシュを取り除いてから比較する。
				// 例: /wp-content/plugins が /wp-content/plugins-extra に誤マッチしないようにする。
				// 比較に使う正規化済みの $dir を substr() の切り出し位置にもそのまま使う
				// （比較にだけ末尾スラッシュ除去後の値を使い、substr() は除去前の $directory['dir'] の
				// 長さを使うと、WP_CONTENT_DIR 等が末尾スラッシュ付きで define() されている環境で
				// 相対パスの先頭が1文字欠ける）.
				$dir = untrailingslashit( $directory['dir'] );
				// $directory['dir'] が '/' や '//' のように untrailingslashit() 後に空文字になるケースを弾く。
				// 空文字のままだと strpos( $path, '/' ) === 0 で全マッチしてしまう.
				if ( '' === $dir ) {
					continue;
				}
				if ( $path === $dir || 0 === strpos( $path, $dir . '/' ) ) {
					$relative_path = substr( $path, strlen( $dir ) );
					// url 側も末尾スラッシュを正規化してから連結する。正規化しないと、WP_PLUGIN_URL /
					// WP_CONTENT_URL 等を末尾スラッシュ付きで define() している環境でスラッシュが重複する
					// （dir 側は untrailingslashit() 済みのため、url 側も揃える）.
					return untrailingslashit( $directory['url'] ) . $relative_path . '/';
				}
			}
			return '';
		}
		// vk-path-to-url:end

		public static function custom_fields_array() {

			$custom_fields_array = array(
				'vk_page_header_image'    => array(
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
				$display_type = 'displaytype_' . get_post_type(); // カスタム分類アーカイブと違って取得ミスがないため標準関数を使用
				// 表示タイプが 標準レイアウトじゃない（記事タイトルや日付など）場合
				if ( ! empty( $options[ $display_type ] ) ) {
					$layout = $options[ $display_type ];
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
				$image_url    = self::header_image_url();
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
				} elseif ( self::get_layout() == 'post_title_and_meta' ) {
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
				if ( self::get_layout() === 'post_title_and_meta' ) {
					if ( empty( $options['cover_color'] ) ) {
						$options['cover_color'] = '#000';
					}
					if ( empty( $options['cover_opacity'] ) ) {
						$options['cover_opacity'] = '0.5';
					}
				}

				// カバー部分
				if ( ! empty( $options['cover_color'] ) || ! empty( $options['cover_opacity'] ) ) {

					$title_outer_dynamic_css .= $vk_page_header_output_class . '::before{
						content:"";
						position:absolute;
						top:0;
						left:0;';
					if ( isset( $options['cover_color'] ) ) {
						if ( ! empty( $options['cover_color'] ) ) {
							$title_outer_dynamic_css .= 'background-color:' . $options['cover_color'] . ';';
						}
					}
					// cover_opacity の値が 0 の場合も出力する必要があるので注意
					if ( isset( $options['cover_opacity'] ) ) {
						if ( ! empty( $options['cover_opacity'] ) || '0' === $options['cover_opacity'] ) {
							$title_outer_dynamic_css .= 'opacity:' . $options['cover_opacity'] . ';';
						}
					}

					$title_outer_dynamic_css .= '
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
