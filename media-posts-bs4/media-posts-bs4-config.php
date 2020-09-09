<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Lightning_Media_Posts_BS4' ) ) {

	require_once( 'package/class-media-posts-bs4.php' );

	global $system_name;
	$system_name = lightning_get_theme_name();

	global $customize_section_name;
	if ( function_exists( 'lightning_get_prefix_customize_panel' ) ) {
		// 空のパネル名を設定出来るように最後に空白は入れない
		$customize_section_name = lightning_get_prefix_customize_panel();
	} else {
		$customize_section_name = 'Lightning ';
	}

	// プリフィックス
	global $vk_media_post_prefix;
	$vk_media_post_prefix = lightning_get_prefix();

}
