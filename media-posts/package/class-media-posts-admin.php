<?php

class Lightning_Media_Admin {

	// controls post types that can be displayed with grid layout on front-end (archives list pages)
	private static $post_types        = array( 'post' => 0 );
	private static $post_types_labels = array();

	public static function init() {

		// Add a link to this plugin's settings page
		// add_filter('plugin_action_links_'.LTG_MEDIA_BASENAME , array( __CLASS__, 'set_plugin_meta'), 10, 1);
		// gets custom post types too
		self::$post_types = Lightning_media_posts::get_custom_types() + self::$post_types;

		// all labels
		self::$post_types_labels = Lightning_media_posts::labelNames() + Lightning_media_posts::get_custom_types_labels();

		add_action( 'customize_register', array( __CLASS__, 'archive_layout_customize_register' ) );

	}

	public static function archive_layout_customize_register( $wp_customize ) {

		global $customize_section_name;
		global $system_name;

		$wp_customize->add_section(
			'vk_post_type_archive', array(
				'title'    => $customize_section_name . __( 'Archive Layouts', 'vk_media_posts_textdomain' ),
				'priority' => 800,
			)
		);

		$post_types           = array( 'post' => 0 );
		$post_types           = Lightning_media_posts::get_custom_types() + $post_types;
		$post_types['author'] = 'author';

		$post_types_labels           = Lightning_media_posts::labelNames() + Lightning_media_posts::get_custom_types_labels();
		$post_types_labels['author'] = __( 'Author', 'vk_media_posts_textdomain' );

		$patterns['default']['label'] = $system_name . ' ' . __( 'default', 'vk_media_posts_textdomain' );
		$patterns                     = $patterns + Lightning_media_posts::patterns();
		foreach ( $patterns as $key => $value ) {
			$layouts[ $key ] = $value['label'];
		}

		// Works Unit のカスタム投稿タイプがある場合は除外する
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'lightning-works-unit/lightning-works-unit.php' ) ) {
			$works_unit = get_option( 'lightning_works_unit' );
			if ( ! empty( $works_unit['post_name'] ) ) {
				$works_unit_slug = $works_unit['post_name'];
				unset( $post_types[ $works_unit_slug ] );
			}
		}

		foreach ( $post_types as $type => $value ) {

			$wp_customize->add_setting(
				'ltg_media_unit_archive_loop_layout[' . $type . ']', array(
					'default'           => 'default',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'ltg_media_unit_archive_loop_layout[' . $type . ']', array(
					'label'    => sprintf( __( 'Archive Page Layout [ %s ]', 'vk_media_posts_textdomain' ), $post_types_labels[ $type ] ),
					'section'  => 'vk_post_type_archive',
					'settings' => 'ltg_media_unit_archive_loop_layout[' . $type . ']',
					'type'     => 'select',
					'choices'  => $layouts,
					'priority' => 500,
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive_count[' . $type . ']', array(
					'default'           => null,
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'vk_sanitize_number',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive_count[' . $type . ']', array(
					'label'    => sprintf( __( 'Post counts per pages [ %s ]', 'vk_media_posts_textdomain' ), $post_types_labels[ $type ] ),
					'section'  => 'vk_post_type_archive',
					'settings' => 'vk_post_type_archive_count[' . $type . ']',
					'type'     => 'number',
					'choices'  => $layouts,
					'priority' => 500,
				)
			);

		}

		return $wp_customize;
	}

}
Lightning_Media_Admin::init();
