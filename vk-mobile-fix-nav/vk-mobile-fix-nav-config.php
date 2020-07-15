<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
// ページ下部に固定表示するメニュー
if ( ! class_exists( 'Vk_Mobile_Fix_Nav' ) ) {
	require_once( 'vk-mobile-fix-nav/class-vk-mobile-fix-nav.php' );

	// add_filter( 'lightning_theme_name', 'theme_name_change' );
	// function theme_name_change() {
	// 	return 'うおーい';
	// }

	global $vk_mobile_fix_nav_prefix;
	$vk_mobile_fix_nav_prefix = 'Lightning';

	global $vk_mobile_fix_nav_priority;
	$vk_mobile_fix_nav_priority = 550;

}
