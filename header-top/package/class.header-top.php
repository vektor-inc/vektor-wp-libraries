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
		
		public static function lightning_header_top_prepend_item(){
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
			if ( $header_top_menu ) 
			{
				$header_prepend .= apply_filters( 'Lightning_headerTop_menu', $header_top_menu );
			}

		    $header_prepend .= self::lightning_header_top_contact_btn();
			$header_prepend .= '</div><!-- [ / .container ] -->';
			$header_prepend .= '</div><!-- [ / #headerTop  ] -->';
			echo $header_prepend;
		}

		static function lightning_header_top_contact_btn(){
			global $options;
			global $vkExUnit_contact;

			if ( isset( $options['header_top_contact_txt'] ) && $options['header_top_contact_txt'] ) {
				$btn_txt = $options['header_top_contact_txt'];
			} else {
				if ( isset( $vkExUnit_contact['short_text'] ) && $vkExUnit_contact['short_text'] )
					$btn_txt = $vkExUnit_contact['short_text'];
			}

			if ( isset( $options['header_top_contact_url'] ) && $options['header_top_contact_url'] ) {
				$link_url = esc_url( $options['header_top_contact_url'] );
			} else {
				if ( isset( $vkExUnit_contact['contact_link'] ) && $vkExUnit_contact['contact_link'] )
					$link_url = esc_url( $vkExUnit_contact['contact_link'] );
			}
			if ( isset( $link_url ) && $link_url && isset( $link_url ) && $link_url ){
				$contact_btn_html = '<div class="headerTop_contactBtn"><a href="'.$link_url.'" class="btn btn-primary">'.$btn_txt.'</a></div>';
				return $contact_btn_html;
			}
		}

		static function lightning_header_top_add_menu() {
			register_nav_menus( array( 'header-top' => 'Header Top Navigation', ) );
		}

		
		static function ltg_header_top_add_script() {
		    wp_register_script( 'ltg_header_top_customizer_js' , plugin_dir_url( __FILE__ ).'/header-top-customizer.js', array( 'jquery','customize-preview' ), '20160809b', true );
		    wp_enqueue_script( 'ltg_header_top_customizer_js' );
		}

	    /*-------------------------------------------*/
	    /*  実行
	    /*-------------------------------------------*/
		// static function init(){
		// 	add_action( 'after_setup_theme', array( __CLASS__, 'lightning_header_top_add_menu' ) );
		// }

	    public function __construct(){
	    	global $options;
	    	global $vkExUnit_contact;
			$options = get_option('Lightning_theme_options');
			$vkExUnit_contact = get_option( 'vkExUnit_contact' );
	    	add_action( 'after_setup_theme', array( $this, 'lightning_header_top_add_menu' ) );
	    	add_action( 'lightning_header_prepend', array( $this, 'lightning_header_top_prepend_item' ) );
	    	add_action( 'customize_preview_init', array( $this, 'ltg_header_top_add_script' ) );
	    	add_action( 'plugins_loaded', array( $this, 'ltg_header_top_add_script' ) );
	    	require_once( 'header-top-customizer.php' );
	    }

	} // class Lightning_header_top 

	new Lightning_header_top();
}

// Lightning_header_top::init();

