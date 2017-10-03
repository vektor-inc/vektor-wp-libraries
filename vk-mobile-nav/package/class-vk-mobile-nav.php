<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

if ( ! class_exists( 'Vk_Mobile_Nav' ) ) {
	class Vk_Mobile_Nav
	{
		public function __construct(){
			add_action( 'after_setup_theme', array( $this, 'setup' ) );
// add_action( 'plugins_loaded', array( $this, 'setup' ) );
//
// add_action( 'init', array( $this, 'setup' ) );
// add_action( 'muplugins_loaded', array( $this, 'setup' ) );
// add_action( 'wp_loaded', array( $this, 'setup' ) );
		// 		add_action( 'init', array( $this, 'term_meta_color' ) );
		// 		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
		public static function init()
		{

			// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		public static function setup(){
				register_nav_menus( array( 'vk-mobile-nav' => 'Mobile Navigation', ) );
		}
	} // class Vk_Mobile_Nav

	new Vk_Mobile_Nav();
}
