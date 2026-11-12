<?php
/**
 * VK Media Posts BS4
 *
 * *********************** CAUTION ***********************
 * The original of this file is located at:
 * https://github.com/vektor-inc/vektor-wp-libraries
 * If you want to change this file, please change the original file.
 *
 * @package vektor-inc/vektor-wp-libraries/media-posts-bs4
 * @license GPL-2.0+
 *
 * @version 1.2.2
 */

use VektorInc\VK_Component\VK_Component_Posts;

if ( ! class_exists( 'VK_Media_Posts_BS4' ) ) {

	/**
	 * VK Media Posts BS4
	 */
	class VK_Media_Posts_BS4 {

		/**
		 * 実行
		 */
		public function __construct() {

			add_action( 'init', array( __CLASS__, 'load_text_domain' ), 0 );

			require_once dirname( __FILE__ ) . '/class-vk-media-posts-bs4-admin.php';
			add_action( 'widgets_init', array( __CLASS__, 'register_widget' ) );

			global $is_extend_loop_name;
			add_filter( $is_extend_loop_name, array( __CLASS__, 'is_loop_layout_change' ) );

			global $do_extend_loop_name;
			add_action( $do_extend_loop_name, array( __CLASS__, 'loop_layout_change' ) );

			add_action( 'pre_get_posts', array( __CLASS__, 'posts_per_page_custom' ) );

		}

		/**
		 * Load TextDomain
		 */
		public static function load_text_domain() {
			$domain = 'vk-media-posts-bs4';
			$locale = apply_filters(
				'plugin_locale',
				( is_admin() && function_exists( 'get_user_locale' ) ) ? get_user_locale() : get_locale(),
				$domain
			);

			$moFile = $domain . '-' . $locale . '.mo';
			$path   = realpath( dirname( __FILE__ ) . '/languages' );

			if ( $path && file_exists( $path ) ) {
				load_textdomain( $domain, $path . '/' . $moFile );
			}
		}

		/**
		 * Patterns
		 */
		public static function patterns() {
			/*
			Cope with old vk-component version at VK Blocks Pro
			$patterns = VK_Component_Posts::get_patterns();
			*/
			$patterns = array(
				'card'            => array(
					'label'             => __( 'Card', 'vk-media-posts-bs4' ),
					'class_posts_outer' => '',
				),
				'card-noborder'   => array(
					'label'             => __( 'Card Noborder', 'vk-media-posts-bs4' ),
					'class_posts_outer' => '',
				),
				'card-intext'     => array(
					'label'             => __( 'Card Intext', 'vk-media-posts-bs4' ),
					'class_posts_outer' => '',
				),
				'card-horizontal' => array(
					'label'             => __( 'Card Horizontal', 'vk-media-posts-bs4' ),
					'class_posts_outer' => '',
				),
				'media'           => array(
					'label'             => __( 'Media', 'vk-media-posts-bs4' ),
					'class_posts_outer' => 'media-outer',
				),
				'postListText'    => array(
					'label'             => _x( 'Text 1 Column', 'post list type', 'vk-media-posts-bs4' ),
					'class_posts_outer' => 'postListText-outer',
				),
			);
			return $patterns;
		}

		/**
		 * Default Options
		 */
		public static function options_default() {

			$default_options = array(
				'order'                      => 'DESC',
				'orderby'                    => 'date',
				'layout'                     => 'card',
				'display_image'              => true,
				'display_image_overlay_term' => true,
				'display_excerpt'            => false,
				'display_author'             => false,
				'display_date'               => true,
				// 更新日の表示（デフォルトは非表示。既存サイトの表示が勝手に変わらないようにする）.
				'display_modified'           => false,
				'display_new'                => true,
				'display_taxonomies'         => false,
				'display_btn'                => false,
				'image_default_url'          => VK_MEDIA_POSTS_BS4_URL . '/images/no-image.png',
				'btn_text'                   => __( 'Read more', 'vk-media-posts-bs4' ),
				'btn_align'                  => 'text-right',
				'overlay'                    => false,
				'new_text'                   => __( 'New!!', 'vk-media-posts-bs4' ),
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
				'col_xxl'                    => 3,
			);
			$default_options = apply_filters( 'vk_media_posts_bs4_default_options', $default_options );
			return $default_options;
		}

		/**
		 * Pattern Select Option.
		 *
		 * @param string $selected selected item.
		 */
		public static function patterns_select_options( $selected ) {

			// $patterns = VK_Component_Posts::get_patterns();
			$patterns = self::get_patterns();
			global $system_name;
			if ( $system_name ) {
				$system_name = $system_name . ' ';
			}

			$selected_html = ( isset( $selected ) && ( 'default' === $selected ) ) ? ' selected' : '';
			$select_html   = '<option value="default">' . $system_name . __( 'default', 'vk-media-posts-bs4' ) . '</option>';

			foreach ( $patterns as $key => $value ) {
				$selected_html = ( isset( $selected ) && ( $selected === $key ) ) ? ' selected' : '';
				$select_html  .= '<option value="' . $key . '"' . $selected_html . '>' . $value['label'] . '</option>' . "\n";
			}
			$allowed_html = array(
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
			);
			echo wp_kses( $select_html, $allowed_html );
		}

		/**
		 * Get label names from theme options & translation file (if)
		 */
		public static function label_names() {

			$post_types_labels = array(
				'post' => __( 'Posts', 'vk-media-posts-bs4' ),
				'page' => __( 'Pages', 'vk-media-posts-bs4' ),
			);

			return $post_types_labels;
		}

		/**
		 * Get Custom Post Types.
		 */
		public static function get_custom_types() {

			// Gets all custom post types set PUBLIC.
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$custom_types = get_post_types( $args, 'names' );

			/*
			Is it Need?
			foreach ($custom_types as $name => $slug) {
				$custom_types[ $name ] = 0;
			}
			*/

			return $custom_types;
		}

		/**
		 * Get Custom Type Labels
		 */
		public static function get_custom_types_labels() {

			// gets all custom post types set PUBLIC.
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

		/**
		 * Register Widget
		 */
		public static function register_widget() {
			require_once dirname( __FILE__ ) . '/class-vk-media-posts-bs4-widget.php';
			register_widget( 'VK_Media_Posts_BS4_Widget' );
		}

		/**
		 * アーカイブループを改変するかどうかの指定
		 *
		 * 1. 一旦該当する投稿タイプが何か判別 $post_type_slug
		 * 2. その投稿タイプに改変指定があるか判別
		 * 3. 該当投稿タイプに改変指定がなくても検索ページの場合は検索レイアウトの改変指定があるか判別
		 *
		 * @param string $post_type_slug 改変する場合は投稿タイプ名を返す
		 */
		public static function is_loop_layout_change( $flag ) {

			$vk_post_type_archive = get_option( 'vk_post_type_archive' );

			$post_type_info = VK_Helpers::get_post_type_info();
			$post_type_slug = $post_type_info['slug'];

			if ( is_author() ) {
				$post_type_slug = 'author';
			} elseif ( is_search() ) {
				// 検索結果ページは、検索クエリの投稿タイプ指定で判定する.
				// get_post_type_info() は検索結果の1件目の投稿タイプを返すため、使うと検索語によって
				// 適用されるレイアウトが変わってしまう（Lightning 15.36.0 以降）.
				$post_type_slug = self::get_search_post_type_slug();
			}

			// 指定の投稿タイプアーカイブのレイアウトに値が存在する場合
			if ( ! empty( $vk_post_type_archive[ $post_type_slug ]['layout'] ) ) {
				// デフォルトじゃない場合.
				if ( 'default' !== $vk_post_type_archive[ $post_type_slug ]['layout'] ) {
					// 改変するので $flag に 投稿タイプslugを入れる
					$flag = $post_type_slug;
				}
			}

			// 投稿タイプ指定の検索だったが、その投稿タイプのレイアウト指定がなかった場合
			if ( is_search() && ! $flag ) {
				// 検索ページのレイアウトに値が存在する場合
				if ( ! empty( $vk_post_type_archive['search']['layout'] ) ) {
					// 検索レイアウトがデフォルトじゃない場合.
					if ( 'default' !== $vk_post_type_archive['search']['layout'] ) {
						// 改変するので $flag に 投稿タイプslugを入れる
						$flag = 'search';
					}
				}
			}

			return $flag;
		}

		/**
		 * 検索結果ページで、どの投稿タイプのレイアウト設定を使うかを返す
		 *
		 * 投稿タイプ指定の無い検索（WordPress が 'any' を入れる）は検索専用レイアウトの 'search' を返す。
		 * 投稿タイプ指定がある検索はその投稿タイプ名を返す。複数指定の場合は先頭の投稿タイプを使う。
		 *
		 * @return string 投稿タイプ名、または 'search'
		 */
		public static function get_search_post_type_slug() {
			global $wp_query;

			// query_vars の投稿タイプ配列は WP_Query がアルファベット順に並べ替えるため、
			// 複数指定時の「先頭」を指定順で判定できるよう、並べ替え前の元のクエリから取得する.
			if ( isset( $wp_query->query['post_type'] ) ) {
				$search_post_type = $wp_query->query['post_type'];
			} else {
				$search_post_type = get_query_var( 'post_type' );
			}

			// 複数の投稿タイプが指定された場合は先頭を使う.
			if ( is_array( $search_post_type ) ) {
				$search_post_type = current( $search_post_type );
			}
			$search_post_type = is_string( $search_post_type ) ? sanitize_key( $search_post_type ) : '';

			// 投稿タイプ指定が無い場合は検索専用レイアウト.
			if ( empty( $search_post_type ) || 'any' === $search_post_type ) {
				return 'search';
			}

			return $search_post_type;
		}

		/**
		 * 改変ループ出力実行
		 */
		public static function loop_layout_change() {

			$flag           = false;
			$post_type_slug = self::is_loop_layout_change( $flag );

			if ( $post_type_slug ) {

				$vk_post_type_archive = get_option( 'vk_post_type_archive' );

				$customize_options = $vk_post_type_archive[ $post_type_slug ];
				// Get default option.
				$customize_options_default = self::options_default();
				// Markge options.
				$options = wp_parse_args( $customize_options, $customize_options_default );

				global $wp_query;

				VK_Component_Posts::the_loop( $wp_query, $options );
			}
		}

		/**
		 * アーカイブページの表示件数改変
		 *
		 * @param object $query WP_Query.
		 */
		public static function posts_per_page_custom( $query ) {

			if ( is_admin() || ! $query->is_main_query() ) {
				return;
			}

			// アーカイブの時以外は関係ないので return.
			if ( ! $query->is_archive() && ! $query->is_home() && ! $query->is_search() ) {
				return;
			}

			// wooCommerce は独自の表示件数制御があるのでこちらからは操作しない.
			if ( ! empty( $query->query_vars['post_type'] ) ) {
				if ( class_exists( 'woocommerce' ) && 'product' === $query->query_vars['post_type'] ) {
					return $query;
				}
			}

			// アーカイブページの表示件数情報を取得.
			$vk_post_type_archive = get_option( 'vk_post_type_archive' );

			// Post Type
			$post_type_info = VK_Helpers::get_post_type_info();
			$post_type      = $post_type_info['slug'];

			if ( $query->is_home() && ! $query->is_front_page() && ! empty( $vk_post_type_archive['post']['count'] ) ) {
				$query->set( 'posts_per_page', $vk_post_type_archive['post']['count'] );
			}

			if ( $query->is_archive() || $query->is_home() ) {

				$page_for_posts['post_top_id'] = get_option( 'page_for_posts' );

				// post_type_archive & is_date and other.
				if ( ! empty( $query->query_vars['post_type'] ) ) {
					if ( isset( $vk_post_type_archive[ $post_type ]['count'] ) ) {
						$query->set( 'posts_per_page', $vk_post_type_archive[ $post_type ]['count'] );
					}
				}

				if ( isset( $vk_post_type_archive[ $post_type ]['orderby'] ) ) {
					$query->set( 'orderby', $vk_post_type_archive[ $post_type ]['orderby'] );
				}
				if ( isset( $vk_post_type_archive[ $post_type ]['order'] ) ) {
					$query->set( 'order', $vk_post_type_archive[ $post_type ]['order'] );
				}

				// カスタム分類アーカイブ.
				if ( ! empty( $query->tax_query->queries ) ) {
					$taxonomy  = $query->tax_query->queries[0]['taxonomy'];
					$post_type = get_taxonomy( $taxonomy )->object_type[0];
					if ( ! empty( $vk_post_type_archive[ $post_type ]['count'] ) ) {
						$query->set( 'posts_per_page', $vk_post_type_archive[ $post_type ]['count'] );
					}
				}
			}

			if ( is_author() || is_search() ) {
				if ( is_author() ) {
					$post_type = 'author';
				} elseif ( is_search() ) {
					$post_type = 'search';
				}
				if ( ! empty( $vk_post_type_archive[ $post_type ]['count'] ) ) {
					$query->set( 'posts_per_page', $vk_post_type_archive[ $post_type ]['count'] );
				}
				if ( isset( $vk_post_type_archive[ $post_type ]['orderby'] ) ) {
					$query->set( 'orderby', $vk_post_type_archive[ $post_type ]['orderby'] );
				}
				if ( isset( $vk_post_type_archive[ $post_type ]['order'] ) ) {
					$query->set( 'order', $vk_post_type_archive[ $post_type ]['order'] );
				}
			}

			return $query;

		}

	}

	new VK_Media_Posts_BS4();

}
