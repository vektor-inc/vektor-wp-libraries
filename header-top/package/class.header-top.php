<?php
class Lightning_header_top 
{

	/*-------------------------------------------*/
	/*	Header top nav
	/*-------------------------------------------*/

	public static function lightning_header_top_prepend_item(){
		$header_prepend = '<div class="headerTop" id="headerTop">';
		$header_prepend .= '<div class="container">';
		$header_prepend .= '<p class="headerTop_description">'.get_bloginfo( 'description' ).'</p>';

	            $args = array(
	                'theme_location' => 'Header Top',
	                'container'      => 'nav',
	                'items_wrap'     => '<ul id="%1$s" class="%2$s nav">%3$s</ul>',
	                'fallback_cb'    => '',
	                'echo'           => false,
	            );
	            $header_top_menu = wp_nav_menu( $args ) ;
	            if ( $header_top_menu ) 
	            {
	    			$header_prepend .= $header_top_menu ;
	            }         

	    $header_prepend .= self::lightning_header_top_contact_btn();
		$header_prepend .= '</div><!-- [ / .container ] -->';
		$header_prepend .= '</div><!-- [ / #headerTop  ] -->';
		echo $header_prepend;
	}

	static function lightning_header_top_contact_btn(){
		$options = get_option('Lightning_theme_options');
		$vkExUnit_options = get_option( 'vkExUnit_contact' );

		if ( isset( $options['header_top_contact_txt'] ) && $options['header_top_contact_txt'] ) {
			$btn_txt = $options['header_top_contact_txt'];
		} else {
			if ( isset( $vkExUnit_options['short_text'] ) && $vkExUnit_options['short_text'] )
				$btn_txt = $vkExUnit_options['short_text'];
		}

		if ( isset( $options['header_top_contact_url'] ) && $options['header_top_contact_url'] ) {
			$link_url = esc_url( $options['header_top_contact_url'] );
		} else {
			if ( isset( $vkExUnit_options['contact_link'] ) && $vkExUnit_options['contact_link'] )
				$link_url = esc_url( $options['header_top_contact_url'] );
		}
		if ( isset( $link_url ) && $link_url && isset( $link_url ) && $link_url ){
			$contact_btn_html = '<div class="headerTop_contactBtn"><a href="'.$link_url.'" class="btn btn-primary">'.$btn_txt.'</a></div>';
			return $contact_btn_html;
		}
	}

	static function lightning_header_top_add_menu() {
		register_nav_menus( array( 'Header Top' => 'Header Top Navigation', ) );
	}

	
	static function ltg_header_top_add_script() {
	    wp_register_script( 'ltg_header_top_customizer_js' , plugin_dir_url( __FILE__ ).'/header-top-customizer.js', array( 'jquery','customize-preview' ), '20160801', true );
	    wp_enqueue_script( 'ltg_header_top_customizer_js' );
	}

    /*-------------------------------------------*/
    /*  実行
    /*-------------------------------------------*/
	// 	static function init(){
	// 		add_action( 'admin_init', array( __CLASS__, 'lightning_header_top_add_menu' ) );
	// }

    public function __construct(){
    	// add_action( 'after_setup_theme', array( $this, 'lightning_header_top_add_menu' ) );
    	add_action( 'lightning_header_prepend', array( $this, 'lightning_header_top_prepend_item' ) );
    	add_action( 'customize_preview_init', array( $this, 'ltg_header_top_add_script' ) );
    	add_action( 'plugins_loaded', array( $this, 'ltg_header_top_add_script' ) );
    }

} // class Lightning_header_top 

// Lightning_header_top::init();

