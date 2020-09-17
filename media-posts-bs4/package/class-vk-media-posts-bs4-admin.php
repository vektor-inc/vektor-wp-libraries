<?php
/**
 * VK Media Posts BS4 Admin
 *
 * @package VK Media Posts BS4
 */

/**
 * VK Media Posts BS4 Admin
 */
class VK_Media_Posts_BS4_Admin {

	/**
	 * Controls post types that can be displayed with grid layout on front-end (archives list pages)
	 *
	 * @var array $post_types
	 */
	private static $post_types = array( 'post' => 0 );

	/**
	 * Label of the post type.
	 *
	 * @var array $post_types_labels
	 */
	private static $post_types_labels = array();

	/**
	 * Init Action
	 */
	public static function init() {

		// gets custom post types too.
		self::$post_types = VK_Media_Posts_BS4::get_custom_types() + self::$post_types;

		// all labels.
		self::$post_types_labels = VK_Media_Posts_BS4::label_names() + VK_Media_Posts_BS4::get_custom_types_labels();

		add_action( 'customize_register', array( __CLASS__, 'archive_layout_customize_register' ) );

	}

	/**
	 * Customize Register
	 *
	 * @param object $wp_customize WP Customize Object.
	 */
	public static function archive_layout_customize_register( $wp_customize ) {

		global $customize_section_name;
		global $system_name;

		require_once dirname( __FILE__ ) . '/class-vk-default-layout-form-hider.php';

		$wp_customize->add_panel(
			'vk_post_type_archive_setting',
			array(
				'priority'       => 535,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => $customize_section_name . __( 'Archive Page Setting', 'media-post-bs4-textdomain' ),
			)
		);

		$post_types           = array( 'post' => 0 );
		$post_types           = VK_Media_Posts_BS4::get_custom_types() + $post_types;
		$post_types['author'] = 'author';

		$post_types_labels           = VK_Media_Posts_BS4::label_names() + VK_Media_Posts_BS4::get_custom_types_labels();
		$post_types_labels['author'] = __( 'Author', 'media-post-bs4-textdomain' );

		$patterns['default']['label'] = $system_name . ' ' . __( 'default', 'media-post-bs4-textdomain' );

		/*
		Cope with old vk-component version at VK Blocks Pro
		$patterns = $patterns + VK_Component_Posts::get_patterns();
		*/

		$patterns = $patterns + VK_Media_Posts_BS4::patterns();

		foreach ( $patterns as $key => $value ) {
			$layouts[ $key ] = $value['label'];
		}

		// Works Unit のカスタム投稿タイプがある場合は除外する.
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'lightning-works-unit/lightning-works-unit.php' ) ) {
			$works_unit = get_option( 'lightning_works_unit' );
			if ( ! empty( $works_unit['post_name'] ) ) {
				$works_unit_slug = $works_unit['post_name'];
				unset( $post_types[ $works_unit_slug ] );
			}
		}

		// Get default option.
		$customize_options_default = VK_Media_Posts_BS4::options_default_post_type();

		foreach ( $post_types as $type => $value ) {

			$post_type_label = $post_types_labels[ $type ];

			$wp_customize->add_section(
				'vk_post_type_archive_setting_' . $type,
				array(
					'title' => $post_type_label,
					'panel' => 'vk_post_type_archive_setting',
				)
			);

			// Display conditions.
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][display_conditions_title]',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_post_type_archive[' . $type . '][display_conditions_title]',
					array(
						'label'            => __( 'Display conditions', 'media-post-bs4-textdomain' ) . ' [ ' . $post_type_label . ' ]',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => __( 'Number of posts per page', 'media-post-bs4-textdomain' ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][count]',
				array(
					'default'           => $customize_options_default['count'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => array( 'VK_Helpers', 'sanitize_number' ),
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][count]',
				array(
					'label'    => '',
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][count]',
					'type'     => 'number',
				)
			);

			// Layout.
			$wp_customize->add_setting(
				'post_type_title_' . $type,
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'post_type_title_' . $type,
					array(
						// translators: Display type and columns of posttype.
						'label'            => sprintf( __( 'Display type and columns [ %s ]', 'media-post-bs4-textdomain' ), $post_type_label ),
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'vk_post_type_archive[' . $type . '][layout]',
				array(
					'selector'        => '.mainSection .vk_posts-postType-' . $type,
					'render_callback' => '',
				)
			);
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][layout]',
				array(
					'default'           => $customize_options_default['layout'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][layout]',
				array(
					'label'    => __( 'Display type', 'media-post-bs4-textdomain' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][layout]',
					'type'     => 'select',
					'choices'  => $layouts,
				)
			);

			$wp_customize->add_setting(
				'layout_form_control_' . $type,
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new VK_Default_Layout_Form_Hider(
					$wp_customize,
					'layout_form_control_' . $type,
					array(
						'label'   => '',
						'section' => 'vk_post_type_archive_setting_' . $type,
					)
				)
			);

			// Columns.
			$sizes = array(
				'xs'  => array( 'label' => __( 'Extra small', 'media-post-bs4-textdomain' ) ),
				'sm'  => array( 'label' => __( 'Small', 'media-post-bs4-textdomain' ) ),
				'md'  => array( 'label' => __( 'Medium', 'media-post-bs4-textdomain' ) ),
				'lg'  => array( 'label' => __( 'Large', 'media-post-bs4-textdomain' ) ),
				'xl'  => array( 'label' => __( 'Extra large', 'media-post-bs4-textdomain' ) ),
				'xxl' => array( 'label' => __( 'XX large', 'media-post-bs4-textdomain' ) ),
			);
			$sizes = apply_filters( 'vk_media_post_bs4_size', $sizes );

			foreach ( $sizes as $key => $value ) {
				$wp_customize->add_setting(
					'vk_post_type_archive[' . $type . '][col_' . $key . ']',
					array(
						'default'           => $customize_options_default[ 'col_' . $key ],
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => array( 'VK_Helpers', 'sanitize_number' ),
					)
				);
				$wp_customize->add_control(
					'vk_post_type_archive[' . $type . '][col_' . $key . ']',
					array(
						// translators: Column of Screen sizes of xs, sm, md, lg, xl, xxl.
						'label'    => sprintf( __( 'Column ( Screen size : %s )', 'media-post-bs4-textdomain' ), $value['label'] ),
						'section'  => 'vk_post_type_archive_setting_' . $type,
						'settings' => 'vk_post_type_archive[' . $type . '][col_' . $key . ']',
						'type'     => 'number',
					)
				);
			}

			// Display item.
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][display_item_title]',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_post_type_archive[' . $type . '][display_item_title]',
					array(
						// translators: Display item of each post type.
						'label'            => sprintf( __( 'Display item [ %s ]', 'media-post-bs4-textdomain' ), $post_type_label ),
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			$items = array(
				'display_image'              => array(
					'label'   => __( 'Image', 'media-post-bs4-textdomain' ),
					'default' => true,
				),
				'display_image_overlay_term' => array(
					'label' => __( 'Term name', 'media-post-bs4-textdomain' ),
					// 'default' => true,
				),
				'display_excerpt'            => array(
					'label' => __( 'Excerpt', 'media-post-bs4-textdomain' ),
					// 'default' => false,
				),
				'display_date'               => array(
					'label' => __( 'Date', 'media-post-bs4-textdomain' ),
					// 'default' => true,
				),
				'display_new'                => array(
					'label' => __( 'New mark', 'media-post-bs4-textdomain' ),
					// 'default' => true,
				),
				'display_btn'                => array(
					'label' => __( 'Button', 'media-post-bs4-textdomain' ),
					// 'default' => false,
				),
			);
			foreach ( $items as $key => $value ) {
				$wp_customize->add_setting(
					'vk_post_type_archive[' . $type . '][' . $key . ']',
					array(
						'default'           => $customize_options_default[ $key ],
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => array( 'VK_Helpers', 'sanitize_checkbox' ),
					)
				);
				$wp_customize->add_control(
					'vk_post_type_archive[' . $type . '][' . $key . ']',
					array(
						'label'    => $value['label'],
						'section'  => 'vk_post_type_archive_setting_' . $type,
						'settings' => 'vk_post_type_archive[' . $type . '][' . $key . ']',
						'type'     => 'checkbox',
					)
				);
			}

			// new mark setting.
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_mark_title]',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_post_type_archive[' . $type . '][new_mark_title]',
					array(
						'label'            => '',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						// translators: New mark options of each post type.
						'custom_title_sub' => sprintf( __( 'New mark option [ %s ]', 'media-post-bs4-textdomain' ), $post_type_label ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_date]',
				array(
					'default'           => $customize_options_default['new_date'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => array( 'VK_Helpers', 'sanitize_number' ),
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][new_date]',
				array(
					'label'    => __( 'Number of days to display the new post mark', 'media-post-bs4-textdomain' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][new_date]',
					'type'     => 'text',
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_text]',
				array(
					'default'           => $customize_options_default['new_text'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][new_text]',
				array(
					'label'    => __( 'New mark text', 'media-post-bs4-textdomain' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][new_text]',
					'type'     => 'text',
				)
			);

			// new mark setting.
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_setting_title]',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_post_type_archive[' . $type . '][btn_setting_title]',
					array(
						'label'            => '',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						// translators: Button option of each post type.
						'custom_title_sub' => sprintf( __( 'Button option [ %s ]', 'media-post-bs4-textdomain' ), $post_type_label ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_text]',
				array(
					'default'           => $customize_options_default['btn_text'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][btn_text]',
				array(
					'label'    => __( 'Button text', 'media-post-bs4-textdomain' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][btn_text]',
					'type'     => 'text',
				)
			);

			$text_aligns = array(
				'text-left'   => __( 'Left', 'media-post-bs4-textdomain' ),
				'text-center' => __( 'Center', 'media-post-bs4-textdomain' ),
				'text-right'  => __( 'Right', 'media-post-bs4-textdomain' ),
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_align]',
				array(
					'default'           => $customize_options_default['btn_align'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][btn_align]',
				array(
					'label'    => __( 'Text align', 'media-post-bs4-textdomain' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][btn_align]',
					'type'     => 'select',
					'choices'  => $text_aligns,
				)
			);

		} // foreach ( $post_types as $type => $value ) {

		return $wp_customize;
	}

}
VK_Media_Posts_BS4_Admin::init();
