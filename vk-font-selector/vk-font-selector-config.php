<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Font_Selector_Customize' ) ) {
	require_once( 'vk-font-switching-function/class-vk-font-selector.php' );

	global $vk_font_selector_textdomain;
	$vk_font_selector_textdomain = 'XXXX_plugin_text_domain_XXXX';

	global $vk_font_selector_enqueue_handle_style;
	$vk_font_selector_enqueue_handle_style = 'デザインのcssハンドル名';

	// カスタマイズ画面でのプライオリティ
	global $vk_font_selector_priority;
	$vk_font_selector_priority = 900;

}
