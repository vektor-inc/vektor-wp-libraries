<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Lightning_media_posts' ) ) {

	require_once( 'media-posts/class.media-posts.php' );

	global $vk_media_post_prefix;
	$vk_media_post_prefix = lightning_get_theme_name() . ' ';

	global $vk_media_post_prefix_short;
	$vk_media_post_prefix_short = lightning_get_theme_name_short() . ' ';

	// メディアポストのCSSを個別で出力する場合は下記フックを使用
	// add_filter( 'lightning_print_media_posts_css_custom','ltg_variety_print_media_posts_css' );
	// function ltg_variety_print_media_posts_css($print_css_default){
	// 	$print_css_default = true;
	// 	return $print_css_default;
	// }

}
