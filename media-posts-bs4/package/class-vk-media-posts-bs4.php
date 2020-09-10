<?php
/**
 * VK Media Posts BS4
 *
 * @package VK Media Posts BS4
 */

if ( ! class_exists( 'VK_Media_Posts_BS4' ) ) {

	/**
	 * VK Media Posts BS4
	 */
	class VK_Media_Posts_BS4 {

		/**
		 * Init Action
		 */
		public static function init() {
			define( 'VK_MEDIA_POSTS_BS4_URL', get_template_directory_uri() . '/inc/media-posts-bs4/package/' );
			define( 'VK_MEDIA_POSTS_BS4_DIR', dirname( __FILE__ ) );
			define( 'VK_MEDIA_POSTS_BS4_VERSION', '1.0' );
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
				'postListText'    => array(
					'label'             => _x( 'Text 1 Column', 'post list type', 'lightning-pro' ),
					'class_posts_outer' => 'postListText-outer',
				),
			);
			return $patterns;
		}

		/**
		 * Default Options
		 */
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
				'col_xxl'                    => 3,
			);
			$default_options = apply_filters( 'vk_media_post_bs4_default_options', $default_options );
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
			$select_html   = '<option value="default">' . $system_name . __( 'default', 'lightning-pro' ) . '</option>';

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
				'post' => __( 'Posts', 'lightning-pro' ),
				'page' => __( 'Pages', 'lightning-pro' ),
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
		 * 実行
		 */
		public function __construct() {

			require_once dirname( __FILE__ ) . '/class-media-posts-admin.php';
			require_once dirname( __FILE__ ) . '/class-media-posts-widget.php';

		}

	} // class VK_Media_Posts_BS4

	new VK_Media_Posts_BS4();
	VK_Media_Posts_BS4::init();

} // if ( ! class_exists( 'VK_Media_Posts_BS4' ) )
