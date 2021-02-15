<?php
/**
 * VK Media Posts BS4 Config
 *
 * @package VK Media Posts BS4
 */

if ( ! class_exists( 'VK_MEDIA_POSTS_BS4' ) ) {

	define( 'VK_MEDIA_POSTS_BS4_URL', get_template_directory_uri() . '/inc/media-posts-bs4/package/' );
	define( 'VK_MEDIA_POSTS_BS4_DIR', dirname( __FILE__ ) );
	define( 'VK_MEDIA_POSTS_BS4_VERSION', '1.1' );

	global $system_name;
	$system_name = lightning_get_theme_name();

	global $vk_media_post_prefix;
	$vk_media_post_prefix = lightning_get_prefix();

	global $customize_section_name;
	if ( function_exists( 'lightning_get_prefix_customize_panel' ) ) {
		// 空のパネル名を設定出来るように最後に空白は入れない.
		$customize_section_name = lightning_get_prefix_customize_panel();
	} else {
		$customize_section_name = 'Lightning ';
	}

	global $post_type_info;
	$post_type_info = lightning_get_post_type();

	global $is_extend_loop_name;
	$is_extend_loop_name = 'is_lightning_extend_loop';

	global $do_extend_loop_name;
	$do_extend_loop_name = 'lightning_extend_loop';

	global $vk_mpbs4_archive_layout_class;
	$vk_mpbs4_archive_layout_class = '.mainSection';

	require_once dirname( __FILE__ ) . '/package/class-vk-media-posts-bs4.php';

	/**
	 * Column size setting
	 *
	 * @param array $sizes size of using on media post bs4.
	 */
	function lightning_media_post_bs4_size( $sizes ) {
		$sizes = array(
			'xs' => array( 'label' => __( 'Extra small', 'media-post-bs4-textdomain' ) ),
			'sm' => array( 'label' => __( 'Small', 'media-post-bs4-textdomain' ) ),
			'md' => array( 'label' => __( 'Medium', 'media-post-bs4-textdomain' ) ),
			'lg' => array( 'label' => __( 'Large', 'media-post-bs4-textdomain' ) ),
			'xl' => array( 'label' => __( 'Extra large', 'media-post-bs4-textdomain' ) ),
		);
		return $sizes;
	}
	add_filter( 'vk_media_post_bs4_size', 'lightning_media_post_bs4_size' );

	/**
	 * Default Options
	 *
	 * @param array $default_options default options of using on media post bs4.
	 */
	function lightning_media_post_bs4_default_options( $default_options ) {
		$default_options = array(
			'layout'                     => 'card',
			'display_image'              => true,
			'display_image_overlay_term' => true,
			'display_excerpt'            => false,
			'display_date'               => true,
			'display_new'                => true,
			'display_btn'                => false,
			'image_default_url'          => VK_MEDIA_POSTS_BS4_URL . '/images/no-image.png',
			'btn_text'                   => __( 'Read more', 'media-post-bs4-textdomain' ),
			'btn_align'                  => 'text-right',
			'overlay'                    => false,
			'new_text'                   => __( 'New!!', 'media-post-bs4-textdomain' ),
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
	add_filter( 'vk_media_post_bs4_default_options', 'lightning_media_post_bs4_default_options' );

	/**
	 * Default Options of Widget
	 *
	 * @param array $default_options default options of using on media post bs4 widget.
	 */
	function lightning_media_post_bs4_widget_default_options( $default_options ) {
		$default_options = array(
			'count'                      => 6,
			'offset'                     => '',
			'title'                      => __( 'Recent Posts', 'media-post-bs4-textdomain' ),
			'post_type'                  => array( 'post' => 1 ), // クエリに投げる形式は違うので要変換.
			'terms'                      => '',
			'layout'                     => 'media',
			'col_xs'                     => 1,
			'col_sm'                     => 1,
			'col_md'                     => 1,
			'col_lg'                     => 1,
			'col_xl'                     => 1,
			'display_image'              => true,
			'display_image_overlay_term' => true,
			'display_excerpt'            => false,
			'display_date'               => true,
			'display_new'                => true,
			'new_date'                   => 7,
			'new_text'                   => 'New!!',
			'btn_text'                   => __( 'Read more', 'media-post-bs4-textdomain' ),
			'btn_align'                  => 'text-right',
		);
		return $default_options;
	}
	add_filter( 'vk_media_posts_bs4_widget_default_options', 'lightning_media_post_bs4_widget_default_options' );

}
