<?php
/**
 * VK Footer Customize Config
 *
 * @package VK Footer Customize
 */

if ( ! class_exists( 'Widget_Area_Setting' ) ) {
	global $vk_footer_customize_hook_style;
	$vk_footer_customize_hook_style = 'katawara-design-style';

	global $vk_footer_selector;
	$vk_footer_selector = '.l-site-footer-upper';

	require_once dirname( __FILE__ ) . '/package/class-widget-area-setting.php';
}
global $vk_footer_customize_prefix;
$vk_footer_customize_prefix = katawara_get_prefix_customize_panel();

global $vk_footer_setting_name;
$vk_footer_setting_name = 'katawara_widget_setting';

global $vk_footer_customize_priority;
$vk_footer_customize_priority = 540;
