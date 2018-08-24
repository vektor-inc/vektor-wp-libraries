<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Lightning_Copyright_Custom' ) ) {
	require get_parent_theme_file_path( 'inc/copyright-customizer/class-copyright-customizer.php' );
	global $vk_copyright_customizer_prefix;
	$vk_copyright_customizer_prefix = lightning_get_theme_name() . ' ';
}
