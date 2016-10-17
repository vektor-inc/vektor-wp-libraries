<?php
/*-------------------------------------------*/
/*  Load modules
/*-------------------------------------------*/
if ( ! class_exists( 'Lightning_media_posts' ) )
{
	global $vk_ltg_media_posts_textdomain;
	$vk_ltg_media_posts_textdomain = 'lightning-variety';

	require_once( 'media-posts/class.media-posts.php' );
	require_once( 'media-posts/class.widget.media-posts.php' );
	require_once( 'media-posts/views/class.loop_post_item.php' );
	require_once( 'media-posts/class.font-awesome-selector.php' );
	if ( is_admin() ) {
		require_once( 'media-posts/class.media-unit-admin.php' );
		add_action( 'admin_menu', array( 'Lightning_Media_Admin', 'init' ) );
	}
	/*  transrate
	/*-------------------------------------------*/
	function ltg_variety_translate(){
		// __( 'Color', 'lightning-variety' );
		// __( 'Author Archive', 'lightning-variety' );
		// __( 'Save Changes', 'lightning-variety' );
		// __( 'Home content top after left', 'lightning-variety' );
		// __( 'Home content top after right', 'lightning-variety' );
		// __( 'Home content top bottom widget', 'lightning-variety' );
		// __( 'Offset count', 'lightning-variety' );
		// __( 'Number of days to display the New icon', 'lightning-variety' );
		// __( 'Slug for the post type you want to display', 'lightning-variety' );
		// __( 'Term ID', 'lightning-variety' );
		// __( 'If you need filtering by term, add the term ID separate by ",".', 'lightning-variety' );
		// __( 'If empty this area, I will do not filtering.', 'lightning-variety' );
	}

	// メディアポストのCSSを個別で出力する場合は下記フックを使用
	// add_filter( 'lightning_print_media_posts_css_custom','ltg_variety_print_media_posts_css' );
	// function ltg_variety_print_media_posts_css($print_css_default){
	// 	$print_css_default = true;
	// 	return $print_css_default;
	// }

}
