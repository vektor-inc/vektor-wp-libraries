<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
// ページ下部に固定表示するメニュー
if ( ! class_exists( 'Vk_Mobile_Fix_Nav' ) ) {
	require_once( 'vk-mobile-fix-nav/class-vk-mobile-fix-nav.php' );

	global $vk_mobile_fix_nav_textdomain;
	$vk_mobile_fix_nav_textdomain = 'XXXX_plugin_text_domain_XXXX';

	global $vk_mobile_fix_nav_prefix;
	$vk_mobile_fix_nav_prefix = 'Lightning';

}
