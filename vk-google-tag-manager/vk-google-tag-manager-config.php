<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Goole_Tag_Manager' ) )
{
	require_once( 'vk-google-tag-manager/class.vk-google-tag-manager.php' );

	global $vk_google_tag_manager_textdomain;
	$vk_google_tag_manager_textdomain = 'lightning';

}
