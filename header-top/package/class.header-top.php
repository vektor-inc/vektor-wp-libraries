<?php

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

if ( ! class_exists( 'Lightning_header_top' ) )
{
	class Lightning_header_top 
	{

		/*-------------------------------------------*/
		/*	Header top nav
		/*-------------------------------------------*/
		
		public static function header_top_prepend_item(){
			$header_prepend = '<div class="headerTop" id="headerTop">';
			$header_prepend .= '<div class="container">';
			$header_prepend .= '<p class="headerTop_description">'.get_bloginfo( 'description' ).'</p>';

			global $options;
			global $vkExUnit_contact;
			if ( isset( $options['header_top_tel_number'] ) && $options['header_top_tel_number'] ) {
				$tel_number = mb_convert_kana ( esc_attr( $options['header_top_tel_number'] ), 'n' );
				/* ここで追加するHTMLは header-top-customizer.js でも修正する必要があるので注意 */
				if ( wp_is_mobile() ){
					$contact_tel = '<li class="headerTop_tel"><a class="headerTop_tel_wrap" href="tel:'.$tel_number.'">'.$tel_number.'</a></li>';
				} else {
					$contact_tel = '<li class="headerTop_tel"><span class="headerTop_tel_wrap">'.$tel_number.'</span></li>';
				}
			} else {
				$contact_tel = '';
			}

			$args = array(
				'theme_location' => 'header-top',
				'container'      => 'nav',
				'items_wrap'     => '<ul id="%1$s" class="%2$s nav">%3$s'.$contact_tel.'</ul>',
				'fallback_cb'    => '',
				'echo'           => false,
			);
			$header_top_menu = wp_nav_menu( $args ) ;
			if ( $header_top_menu ) {
				$header_prepend .= apply_filters( 'Lightning_headerTop_menu', $header_top_menu );
			} else if ( $contact_tel || is_customize_preview() ) {
				$header_prepend .= '<nav><ul id="%1$s" class="%2$s nav">'.$contact_tel.'</ul></nav>';
			}

		    $header_prepend .= self::header_top_contact_btn();
			$header_prepend .= '</div><!-- [ / .container ] -->';
			$header_prepend .= '</div><!-- [ / #headerTop  ] -->';
			echo $header_prepend;
		}

		static function header_top_contact_btn(){
			global $options;
			global $vkExUnit_contact;

			if ( isset( $options['header_top_contact_txt'] ) && $options['header_top_contact_txt'] ) {
				$btn_txt = esc_html( $options['header_top_contact_txt'] );
			} elseif ( isset( $options['header_top_contact_txt'] ) && !$options['header_top_contact_txt'] ) {
				$btn_txt = '';
			} elseif ( !isset( $options['header_top_contact_txt'] ) && isset( $vkExUnit_contact['short_text'] ) ) {
				$btn_txt = esc_html( $vkExUnit_contact['short_text'] );
			}

			if ( isset( $options['header_top_contact_url'] ) && $options['header_top_contact_url'] ) {
				$link_url = esc_url( $options['header_top_contact_url'] );
			} elseif ( isset( $options['header_top_contact_url'] ) && !$options['header_top_contact_url'] ) {
				$link_url = '';
			} elseif ( !isset( $options['header_top_contact_url'] ) && isset( $vkExUnit_contact['contact_link'] ) ) {
				$link_url = esc_url( $vkExUnit_contact['contact_link'] );
			}

			if ( isset( $btn_txt ) && $btn_txt && isset( $link_url ) && $link_url ){
				$contact_btn_html = '<div class="headerTop_contactBtn"><a href="'.$link_url.'" class="btn btn-primary">'.$btn_txt.'</a></div>';
				return $contact_btn_html;
			}
		}

		static function header_top_add_menu() {
			register_nav_menus( array( 'header-top' => 'Header Top Navigation', ) );
		}

		/*-------------------------------------------*/
		/*  Add header top css
		/*-------------------------------------------*/
		static function header_top_add_css() {
			wp_enqueue_style( 'lightning-header-top', LTG_HEADER_TOP_URL.'css/header_top.css', array( 'lightning-design-style' ), LTG_HEADER_TOP_VERSION, 'all' );
		}
		
		static function header_top_add_script() {
		    wp_register_script( 'ltg_header_top_customizer_js' , plugin_dir_url( __FILE__ ).'/header-top-customizer.js', array( 'jquery','customize-preview' ), LTG_HEADER_TOP_VERSION, true );
		    wp_enqueue_script( 'ltg_header_top_customizer_js' );
		}

	    /*-------------------------------------------*/
	    /*  実行
	    /*-------------------------------------------*/
		// static function init(){
		// 	add_action( 'after_setup_theme', array( __CLASS__, 'header_top_add_menu' ) );
		// }

	    public function __construct(){
			define( 'LTG_HEADER_TOP_URL', plugin_dir_url( __FILE__ ) );
			define( 'LTG_HEADER_TOP_DIR', plugin_dir_path( __FILE__ ) );
			define( 'LTG_HEADER_TOP_VERSION', '1.0d' );
	    	global $options;
	    	global $vkExUnit_contact;
			$options = get_option('Lightning_theme_options');
			$vkExUnit_contact = get_option( 'vkExUnit_contact' );
	    	add_action( 'after_setup_theme', array( $this, 'header_top_add_menu' ) );
	    	add_action( 'lightning_header_prepend', array( $this, 'header_top_prepend_item' ) );
	    	add_action( 'customize_preview_init', array( $this, 'header_top_add_script' ) );
	    	add_action( 'plugins_loaded', array( $this, 'header_top_add_script' ) );
	    	add_action( 'wp_enqueue_scripts',  array( $this, 'header_top_add_css' ) );
	    	require_once( 'header-top-customizer.php' );
	    }

	} // class Lightning_header_top 

	new Lightning_header_top();
}

// Lightning_header_top::init();

