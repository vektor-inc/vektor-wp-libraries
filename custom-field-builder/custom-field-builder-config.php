<?php

/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'VK_Custom_Field_Builder' ) )
{
	require_once( 'custom-field-builder/custom-field-builder.php' );

	global $custom_field_builder_dir;
	$custom_field_builder_dir = LIGHTNING_SKIN_JPNSTYLE_URL.'/inc/custom-field-builder/';
}
