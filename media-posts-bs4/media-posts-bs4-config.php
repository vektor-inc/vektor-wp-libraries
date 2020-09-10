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

	function lightning_media_post_bs4_size( $sizes ) {
		$sizes = array(
			'xs' => array( 'label' => __( 'Extra small', 'lightning-pro' ) ),
			'sm' => array( 'label' => __( 'Small', 'lightning-pro' ) ),
			'md' => array( 'label' => __( 'Medium', 'lightning-pro' ) ),
			'lg' => array( 'label' => __( 'Large', 'lightning-pro' ) ),
			'xl' => array( 'label' => __( 'Extra large', 'lightning-pro' ) ),
		);
		return $sizes;
	}
	add_filter( 'vk_media_post_bs4_size', 'lightning_media_post_bs4_size' );

	// プリフィックス
	global $vk_media_post_prefix;
	$vk_media_post_prefix = lightning_get_prefix();

}
