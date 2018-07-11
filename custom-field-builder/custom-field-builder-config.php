<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/

if ( ! class_exists( 'VK_Custom_Field_Builder' ) )
{
	require_once( 'custom-field-builder/custom-field-builder.php' );

	global $custom_field_builder_url;
	if(defined('LIGHTNING_SKIN_JPNSTYLE_URL')){
		$custom_field_builder_url = LIGHTNING_SKIN_JPNSTYLE_URL.'inc/custom-field-builder/';
	}elseif (defined('LIGHTNING_PRO_THEME_URL')){
		$custom_field_builder_url = LIGHTNING_PRO_THEME_URL.'inc/custom-field-builder/';
	};
}
