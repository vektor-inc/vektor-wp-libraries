<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Mobile_Nav' ) ) {
	require_once( 'vk-mobile-nav/class-vk-mobile-nav.php' );

	global $vk_mobile_nav_textdomain;
	$vk_mobile_nav_textdomain = 'XXXX_plugin_text_domain_XXXX';

}

/*
端末判定でモバイルの場合は強制的にモバイルメニューを利用する
端末判定でモバイルでない場合は画面が狭い時にモバイルメニューを利用する
 */
