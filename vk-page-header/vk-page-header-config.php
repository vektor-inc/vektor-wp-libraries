<?php
/*-------------------------------------------*/
/*  Load modules ( master config )
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Page_Header' ) ) {
	require_once( 'vk-page-header/class-vk-page-header.php' );
	require_once( 'custom-field-builder-config.php' );

	global $vk_page_header_textdomain;
	$path = __FILE__;
	preg_match( '/\/themes\//', $path, $m );
	if ( $m ) {
		$vk_page_header_textdomain = 'lightning-pro';
	} else {
		$vk_page_header_textdomain = 'lightning_skin_jpnstyle';
	}

	global $customize_setting_prefix;
	$customize_setting_prefix = 'Lightning';

	global $vk_page_header_output_class;
	$vk_page_header_output_class = '.page-header';

	global $vk_page_header_default;
	$vk_page_header_default = array(
		'text_color' => '#333',
	);

	global $vk_page_header_default_bg_url;
	// このファイルがテーマで使われた場合の例
	$vk_page_header_default_bg_url = get_template_directory_uri( '/inc/vk-page-header/images/header-sample.jpg' );
	// プラグインの場合の例
	$vk_page_header_default_bg_url = plugins_url( '/images/header-sample.jpg', __FILE__ );

}

/*
Sample Image
https://pixabay.com/ja/%E4%BA%AC-%E6%97%A5%E6%9C%AC-%E7%AB%B9-%E3%83%9C%E3%82%B1%E5%91%B3-%E5%86%92%E9%99%BA-%E6%A3%AE%E6%9E%97-%E6%97%85%E8%A1%8C-1860521/
 */
