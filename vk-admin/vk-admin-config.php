<?php

/*
-------------------------------------------*/
/*
  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Vk_Admin' ) ) {
	require_once 'vk-admin/class-vk-admin.php';
}


/*
-------------------------------------------*/
/*
  $admin_pages の配列にいれる識別値は下記をコメントアウト解除すればブラウザのコンソールで確認出来る
/*-------------------------------------------*/

// add_action("admin_head", 'suffix2console');
// function suffix2console() {
// global $hook_suffix;
// if (is_user_logged_in()) {
// $str = "<script type=\"text/javascript\">console.log('%s')</script>";
// printf($str, $hook_suffix);
// }
// }

$admin_pages = array(
	'toplevel_page_vkExUnit_setting_page',
	'vk-exunit_page_vkExUnit_main_setting',
	'',
);
Vk_Admin::admin_scripts( $admin_pages );

/*
-------------------------------------------*/
/*
  Setting Page
/*-------------------------------------------*/
function XXXX_add_customSettingPage() {
	$get_page_title = __( 'XXXXX setting', 'XXXX(text domain)' );
	$get_logo_html  = '';
	$get_menu_html  = '<li><a href="#XXXX_a">' . __( 'XXXX_A Setting', 'XXXX(text domain)' ) . '</a></li>';
	$get_menu_html .= '<li><a href="#XXXX_b">' . __( 'XXXX_B Setting', 'XXXX(text domain)' ) . '</a></li>';
	Vk_Admin::admin_page_frame( $get_page_title, 'XXXX_the_admin_body', $get_logo_html, $get_menu_html );
}

// function XXXX_the_admin_body(){

// }
