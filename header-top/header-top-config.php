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

	global $vk_header_top_textdomain;
	$vk_header_top_textdomain = 'XXXX_plugin_text_domain_XXXX';

}