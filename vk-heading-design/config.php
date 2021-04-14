
<?php
/**
 * VK Heading Config
 *
 * @package Katawara
 */

/**
 * Hedding Default Options
 */
function katawara_headding_default_option() {
	$default_option = array(
		'main-section_title' => array(
			'style' => 'plain_center',
		),
		'side-section_title' => array(
			'style' => 'solid_bottomborder_leftkeycolor',
		),
		'site-footer_title'  => array(
			'style' => 'both_ends',
		),
		'h2'                 => array(
			'style' => 'brackets_black',
		),
		'h3'                 => array(
			'style' => 'background_fill_lightgray',
		),
		'h4'                 => array(
			'style' => 'leftborder_nobackground',
		),
		'h5'                 => array(
			'style' => 'none',
		),
		'h6'                 => array(
			'style' => 'none',
		),
	);
	return $default_option;
}

/**
 * Hedding Selecters
 */
function katawara_get_headding_selector_array() {
	/* セレクタは ::before や ::after を自動生成するために配列で格納している */
	$headding_selectors = array(
		'main-section_title' => array(
			'label'    => __( 'Main section title', 'katawara' ),
			'selector' => array(
				'.l-main-section .l-main-section_title',
			),
		),
		'side-section_title' => array(
			'label'    => __( 'Side section title', 'katawara' ),
			'selector' => array(
				'.l-side-section .p-widget-side_title',
				// ↓ 特定のwidgetのタイトル下の余白を消すCSSが効かなくなるのでもし戻す事がある場合は注意
				// '.l-side-section_inner .p-widget .p-widget_title',
			),
		),
		'site-footer_title'  => array(
			'label'    => __( 'Footer section title', 'katawara' ),
			'selector' => array(
				'.p-widget-footer .p-widget-footer_title',
			),
		),
		'h2'                 => array(
			'label'    => __( 'H2', 'katawara' ),
			'selector' => array(
				'h2',
			),
		),
		'h3'                 => array(
			'label'    => __( 'H3', 'katawara' ),
			'selector' => array(
				'h3',
			),
		),
		'h4'                 => array(
			'label'    => __( 'H4', 'katawara' ),
			'selector' => array(
				'h4',
			),
		),
		'h5'                 => array(
			'label'    => __( 'H5', 'katawara' ),
			'selector' => array(
				'h5',
			),
		),
		'h6'                 => array(
			'label'    => __( 'H6', 'katawara' ),
			'selector' => array(
				'h6',
			),
		),
	);
	return $headding_selectors;
}

if ( ! class_exists( 'VK_Headding_Design' ) ) {
	global $headding_default_options;
	$headding_default_options = katawara_headding_default_option();

	global $headding_selector_array;
	$headding_selector_array = katawara_get_headding_selector_array();

	global $headding_customize_section;
	$headding_customize_section = 'katawara_design';

	global $headding_theme_options;
	$headding_theme_options = get_option( 'katawara_theme_options' );

	global $headding_front_hook_style;
	$headding_front_hook_style = 'katawara-design-style';

	global $headding_editor_hook_style;
	$headding_editor_hook_style = 'katawara-common-editor-gutenberg';

	require_once plugin_dir_path( __FILE__ ) . '/package/class-vk-headding-design.php';

}
