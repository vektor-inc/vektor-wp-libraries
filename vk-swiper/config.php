<?php
/**
 * VK Swiper Config
 *
 * @package VK Swiper
 */

if ( ! class_exists( 'VK_Swiper' ) ) {

	global $vk_swiper_url;
	global $vk_swiper_path;
	$vk_swiper_url = get_parent_theme_file_uri( 'inc/vk-swiper/package' );
	$vk_swiper_path = get_parent_theme_file_path( 'inc/vk-swiper/package' );
	require_once dirname( __FILE__ ) . '/package/class-vk-swiper.php';
}

