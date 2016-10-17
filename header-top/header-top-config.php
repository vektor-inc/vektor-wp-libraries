<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Lightning_header_top' ) )
{
	require( 'header-top/class.header-top.php' );

	/*  transrate
	/*-------------------------------------------*/
	function XXXX_header_top_translate(){
		__( 'Color', 'XXXX_plugin_text_domain_XXXX' );
	}

	// Header top のCSSを単体で出力する時
	// add_filter( 'lightning_print_header_top_css_custom', 'XXXX_print_header_top_css' );
	// function XXXX_print_header_top_css( $print ){
	// 	$print = true;
	// 	return $print;
	// }

	global $vk_header_top_textdomain;
	$vk_header_top_textdomain = 'XXXX_plugin_text_domain_XXXX';

	// header_topのCSSを個別で出力する場合は下記フックを使用
	// add_filter( 'lightning_print_header_top_css_custom','ltg_variety_print_header_top_css' );
	// function ltg_variety_print_header_top_css($print_css_default){
	// 	$print_css_default = true;
	// 	return $print_css_default;
	// }

}