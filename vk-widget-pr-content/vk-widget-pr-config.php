<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'VK_Widget_Pr_Content' ) ) {
	require_once( 'vk-widget-pr-content/class-vk-widget-pr-content.php' );

	global $pr_content_textdomain;
	$pr_content_textdomain = 'lightning_skin_jpnstyle';

	// PR Contet ウィジェットのCSSファイルを単独で読み込むかどうか
	global $pr_content_dont_load_css;
	$pr_content_dont_load_css = false;

	// global $vk_page_header_output_class;
	// $vk_page_header_output_class = '.page-header';
	//
	// global $customize_setting_prefix;
	// $customize_setting_prefix = 'Lightning';

}
