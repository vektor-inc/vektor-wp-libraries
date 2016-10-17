<?php

class Lightning_Media_Admin {

	//controls post types that can be displayed with grid layout on front-end (archives list pages)
	private static $post_types = array( 'post' => 0 );
	private static $post_types_labels = array();

	public static function init() {

		// Add a link to this plugin's settings page
        // add_filter('plugin_action_links_'.LTG_MEDIA_BASENAME , array( __CLASS__, 'set_plugin_meta'), 10, 1);

		//gets custom post types too
		self::$post_types = Lightning_media_posts::get_custom_types() + self::$post_types;

		//all labels
		self::$post_types_labels = Lightning_media_posts::labelNames() + Lightning_media_posts::get_custom_types_labels();
	}

	//displays Media Unit plugin page content
	public static function displaySettings() {

		$mess = self::validSettings();

		$post_types_label 		= self::$post_types_labels;
		$post_types 			= self::$post_types;

		require_once( 'views/setting-page.php');
	}

	//valid admin form and save post types selected by user in DB
	private static function validSettings() 
	{
		global $vk_ltg_media_posts_textdomain;

		$mess = '';

		//valid form
		if ( isset($_POST) && !empty($_POST)
			&& isset($_POST['lightning-media-unit-nonce']) && wp_verify_nonce( $_POST['lightning-media-unit-nonce'], 'lightning-media-unit-submit' ) ) {

			$post_types = self::$post_types;
			$post_types['author'] = 0;

			foreach ( $post_types as $type => $value ) {
				if( isset( $_POST['ltg_media_unit_archive_loop_layout'][$type] ) ) {
					$ltg_media_unit_archive_loop_layout[ $type ] = esc_html(  $_POST['ltg_media_unit_archive_loop_layout'][$type] );
				}
			}

			//save in DB
			update_option( 'ltg_media_unit_archive_loop_layout', $ltg_media_unit_archive_loop_layout );

			$mess = '<div id="message" class="updated"><p>' . __( 'Your settings were saved.', $vk_ltg_media_posts_textdomain ) . '</p></div>';
		}
		return $mess;
	}

}
