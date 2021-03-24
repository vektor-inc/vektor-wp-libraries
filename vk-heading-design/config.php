<?php
/**
 * VK Heading Config
 *
 * @package lightning-pro
 */

/**
 * Hedding Default Options
 */
function lightning_headding_default_option() {
	$default_option = array(
		'h2'                           => array(
			'style' => 'none',
		),
		'siteContent_subSection-title' => array(
			'style' => 'none',
		),
		'siteFooter_subSection-title'  => array(
			'style' => 'none',
		),
		'h3'                           => array(
			'style' => 'none',
		),
		'h4'                           => array(
			'style' => 'none',
		),
		'h5'                           => array(
			'style' => 'none',
		),
		'h6'                           => array(
			'style' => 'none',
		),
	);
	return $default_option;
}

/**
 * Hedding Selecters
 */
function lightning_get_headding_selector_array() {
	/*
	 セレクタは ::before や ::after を自動生成するために配列で格納している */
	/* h3以前は .siteContent をつけないとスライダーのタイトルが左揃えになったりする */
	$selectors = array(
		'h2'                           => array(
			'label'    => __( 'H2 and main section title', 'headding_design_textdomain' ),
			'selector' => array(
				/* .entry-bodyクラスをつけないとLanding Page for Page Builderテンプレートで効かなくなる */
				'h2',
				'.mainSection .cart_totals h2', // wooCommerce Cart Page
				'h2.mainSection-title',
			),
		),
		'siteContent_subSection-title' => array(
			'label'    => __( 'Sub section title', 'headding_design_textdomain' ),
			'selector' => array(
				'.siteContent .subSection-title',
				'.siteContent .widget .subSection-title',
			),
		),
		'siteFooter_subSection-title'  => array(
			'label'    => __( 'Footer section title', 'headding_design_textdomain' ),
			'selector' => array(
				'.siteFooter .subSection-title',
			),
		),
		'h3'                           => array(
			'label'    => __( 'H3', 'headding_design_textdomain' ),
			'selector' => array(
				'h3',
			),
		),
		'h4'                           => array(
			'label'    => __( 'H4', 'headding_design_textdomain' ),
			'selector' => array(
				'h4',
			),
		),
		'h5'                           => array(
			'label'    => __( 'H5', 'headding_design_textdomain' ),
			'selector' => array(
				'h5',
			),
		),
		'h6'                           => array(
			'label'    => __( 'H6', 'headding_design_textdomain' ),
			'selector' => array(
				'h6',
			),
		),
	);
	return apply_filters( 'vk_headding_selector_array', $selectors );
}

if ( ! class_exists( 'VK_Headding_Design' ) ) {
	global $headding_default_options;
	$headding_default_options = 'lightning_headding_default_option';

	global $headding_selector_array;
	$headding_selector_array = 'lightning_get_headding_selector_array';

	global $headding_customize_section;
	$headding_customize_section = 'lightning_design';

	global $headding_theme_options;
	$headding_theme_options = 'lightning_theme_options';

	global $headding_front_hook_style;
	$headding_front_hook_style = 'lightning-design-style';

	global $headding_editor_hook_style;
	$headding_editor_hook_style = 'lightning-common-editor-gutenberg';

	require_once plugin_dir_path( __FILE__ ) . '/package/class-vk-heading-design.php';

}
