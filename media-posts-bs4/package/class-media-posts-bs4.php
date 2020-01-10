<?php
if ( ! class_exists( 'Lightning_Media_Posts_BS4' ) ) {
	class Lightning_Media_Posts_BS4 {


		static function init() {
			define( 'VK_MEDIA_POSTS_BS4_URL', get_template_directory_uri() . '/inc/media-posts-bs4/package/' );
			define( 'VK_MEDIA_POSTS_BS4_DIR', dirname( __FILE__ ) );
			define( 'VK_MEDIA_POSTS_BS4_VERSION', '1.0' );
		}

		public static function patterns() {

			$patterns = array(
				'card'            => array(
					'label'             => __( 'Card', 'lightning-pro' ),
					'class_posts_outer' => '',
				),
				'card-horizontal' => array(
					'label'             => __( 'Card Horizontal', 'lightning-pro' ),
					'class_posts_outer' => '',
				),
				'media'           => array(
					'label'             => __( 'Media', 'lightning-pro' ),
					'class_posts_outer' => 'media-outer',
				),
			);
			return $patterns;
		}

		public static function options_default_post_type() {

			$default_options = array(
				'layout'                     => 'card',
				'display_image'              => true,
				'display_image_overlay_term' => true,
				'display_excerpt'            => false,
				'display_date'               => true,
				'display_new'                => true,
				'display_btn'                => false,
				'image_default_url'          => VK_MEDIA_POSTS_BS4_URL . '/images/no-image.png',
				'btn_text'                   => __( 'Read more', 'lightning-pro' ),
				'btn_align'                  => 'text-right',
				'overlay'                    => false,
				'new_text'                   => __( 'New!!', 'lightning-pro' ),
				'new_date'                   => 7,
				'class_title'                => '',
				'body_prepend'               => '',
				'body_append'                => '',
				'count'                      => 6,
				'col_xs'                     => 1,
				'col_sm'                     => 2,
				'col_md'                     => 2,
				'col_lg'                     => 2,
				'col_xl'                     => 3,
			);
			return $default_options;
		}

		public static function patterns_select_options( $selected ) {

			$patterns = Lightning_Media_Posts_BS4::patterns();
			global $system_name;
			if ( $system_name ) {
				$system_name = $system_name . ' ';
			}

			$selected_html = ( isset( $selected ) && ( $selected == 'default' ) ) ? ' selected' : '';
			$select_html   = '<option value="default">' . $system_name . __( 'default', 'lightning-pro' ) . '</option>';

			foreach ( $patterns as $key => $value ) {
				$selected_html = ( isset( $selected ) && ( $selected == $key ) ) ? ' selected' : '';
				$select_html  .= '<option value="' . $key . '"' . $selected_html . '>' . $value['label'] . '</option>' . "\n";
			}
			echo $select_html;
		}

		// get label names from theme options & translation file (if)
		public static function labelNames() {

			$post_types_labels = array(
				'post' => __( 'Posts' ),
				'page' => __( 'Pages', 'lightning-pro' ),
			);

			return $post_types_labels;
		}

		public static function get_custom_types() {

			// gets all custom post types set PUBLIC
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$custom_types = get_post_types( $args, 'names' );

			// foreach ($custom_types as $name => $slug) {
			// $custom_types[ $name ] = 0;
			// }
			return $custom_types;
		}

		public static function get_custom_types_labels() {

			// gets all custom post types set PUBLIC
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$custom_types        = get_post_types( $args, 'objects' );
			$custom_types_labels = array();

			foreach ( $custom_types as $custom_type ) {
				$custom_types_labels[ $custom_type->name ] = $custom_type->label;
			}

			return $custom_types_labels;
		}

		/*
		  実行
		/*-------------------------------------------*/

		public function __construct() {

			require_once( 'class-media-posts-admin.php' );
			require_once( 'class-media-posts-widget.php' );

		}

	} // class Lightning_Media_Posts_BS4

	new Lightning_Media_Posts_BS4();
	Lightning_Media_Posts_BS4::init();

	/*
	  Archive Loop change
	/*-------------------------------------------*/
	/* アーカイブループのレイアウトを改変するかどうかの判定 */
	function lmu_is_loop_layout_change_bs4_flag_bs4( $post_type = 'post', $flag = false ) {
		$vk_post_type_archive = get_option( 'vk_post_type_archive' );
		// 指定の投稿タイプアーカイブのレイアウトに値が存在する場合
		if ( ! empty( $vk_post_type_archive[ $post_type ]['layout'] ) ) {
			// デフォルトじゃない場合
			if ( $vk_post_type_archive[ $post_type ]['layout'] != 'default' ) {
				$flag = true;
			} // if ( $vk_post_type_archive[ $postType ] ['layout']!= 'default' ) {
		}
		return $flag;
	}

	/* アーカイブループを改変するかどうかの指定 */
	add_filter( 'is_lightning_extend_loop', 'lmu_is_loop_layout_change_bs4' );
	function lmu_is_loop_layout_change_bs4( $flag ) {
		$post_type_info = lightning_get_post_type();
		$post_type      = $post_type_info['slug'];

		if ( is_author() ) {
			$postType = 'author';
		}

		$flag = lmu_is_loop_layout_change_bs4_flag_bs4( $post_type, $flag );
		return $flag;
	}


	add_action( 'lightning_extend_loop', 'lmu_do_loop_layout_change_bs4' );
	function lmu_do_loop_layout_change_bs4() {

		$vk_post_type_archive = get_option( 'vk_post_type_archive' );

		$post_type      = lightning_get_post_type();
		$post_type_slug = $post_type['slug'];
		$post_type_slug = ( is_author() ) ? 'author' : $post_type['slug'];

		$flag = lmu_is_loop_layout_change_bs4_flag_bs4( $post_type_slug );
		if ( $flag ) {

			$customize_options = $vk_post_type_archive[ $post_type_slug ];
			// Get default option
			$customize_options_default = Lightning_Media_Posts_BS4::options_default_post_type();
			// Markge options
			$options = wp_parse_args( $customize_options, $customize_options_default );

			$class_outer = '';
			if ( ! empty( VK_Component_Posts::get_col_size_classes( $options ) ) ) {
				$options['class_outer'] = VK_Component_Posts::get_col_size_classes( $options );
			}

			$patterns = Lightning_Media_Posts_BS4::patterns();

			$options_loop = array(
				'class_loop_outer' => 'vk_posts-postType-' . $post_type_slug . ' ' . $patterns[ $options['layout'] ]['class_posts_outer'],
			);
			global $wp_query;
			VK_Component_Posts::the_loop( $wp_query, $options, $options_loop );
		}
	}

	/*
		posts_per_page_custom
	/*-------------------------------------------*/
	add_action( 'pre_get_posts', 'lmu_posts_per_page_custom_bs4' );
	function lmu_posts_per_page_custom_bs4( $query ) {

		if ( is_admin() || ! $query->is_main_query() ) {
			 return;
		}

		// アーカイブの時以外は関係ないので return
		if ( ! $query->is_archive() && ! $query->is_home() ) {
			return;
		}

		// アーカイブページの表示件数情報を取得
		// $vk_post_type_archive = get_option['count']ion( 'vk_post_type_archive_count' );
		$vk_post_type_archive = get_option( 'vk_post_type_archive' );

		if ( $query->is_home() && ! $query->is_front_page() && ! empty( $vk_post_type_archive['post']['count'] ) ) {
				return $query->set( 'posts_per_page', $vk_post_type_archive['post']['count'] );
		}

		// authhor archive
		if ( $query->is_author() && ! empty( $vk_post_type_archive['author']['count'] ) ) {
			return $query->set( 'posts_per_page', $vk_post_type_archive['author']['count'] );
		}

		if ( $query->is_archive() ) {

			$page_for_posts['post_top_id'] = get_option( 'page_for_posts' );

			// post_type_archive & is_date and other
			if ( ! empty( $query->query_vars['post_type'] ) ) {
				$post_type = $query->query_vars['post_type'];
				if ( is_array( $post_type ) ) {
					$post_type = current( $post_type );
				}
				if ( isset( $vk_post_type_archive[ $post_type ]['count'] ) ) {
					return $query->set( 'posts_per_page', $vk_post_type_archive[ $post_type ]['count'] );
				}
			}

			// カスタム分類アーカイブ
			if ( ! empty( $query->tax_query->queries ) ) {
				$taxonomy  = $query->tax_query->queries[0]['taxonomy'];
				$post_type = get_taxonomy( $taxonomy )->object_type[0];
				if ( ! empty( $vk_post_type_archive[ $post_type ]['count'] ) ) {
					return $query->set( 'posts_per_page', $vk_post_type_archive[ $post_type ]['count'] );
				}
			}
		}

		return $query;

	}
} // if ( ! class_exists( 'Lightning_Media_Posts_BS4' ) )
