<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

if ( ! class_exists( 'Vk_Mobile_Nav' ) ) {
	class Vk_Mobile_Nav {

		public static $version = '0.0.0';

		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'setup' ) );
			add_action( 'wp_footer', array( $this, 'menu_set_html' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
			add_filter( 'body_class', array( $this, 'add_body_class_mobile_device' ) );
		}
		public static function init() {

			// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * body class 端末識別クラス追加
		 * @return [type] [description]
		 */
		function add_body_class_mobile_device( $class ) {
			if ( wp_is_mobile() ) {
				$class[] = 'mobile-device';
			}
			return $class;
		}

		/**
		 * モバイル用メニュー追加
		 * @return [type] [description]
		 */
		public static function setup() {
				register_nav_menus( array( 'vk-mobile-nav' => 'Mobile Navigation' ) );
		}

		public static function menu_set_html() {
			echo '<div class="vk-mobile-nav-menu-btn">MENU</div>';
			echo '<div class="vk-mobile-nav">';
			wp_nav_menu(
				array(
					'theme_location' => 'vk-mobile-nav',
					'container'      => '',
					'items_wrap'     => '<nav class="global-nav"><ul id="%1$s" class="vk-menu-acc  %2$s">%3$s</ul></nav>',
					'fallback_cb'    => '',
					// 'depth'          => 1,
				)
			);
			echo '</div>';
		}

		/*-------------------------------------------*/
		/*  Load js & CSS
		/*-------------------------------------------*/

		public static function add_script() {
			wp_register_script( 'vk-mobile-nav-js', plugin_dir_url( __FILE__ ) . 'js/vk-mobile-nav.js', array( 'jquery' ), self::$version );
			wp_enqueue_script( 'vk-mobile-nav-js' );
			wp_enqueue_style( 'vk-mobile-nav-css', plugin_dir_url( __FILE__ ) . 'css/vk-mobile-nav-bright.css', array(), self::$version, 'all' );
		}

	} // class Vk_Mobile_Nav

	new Vk_Mobile_Nav();
}
