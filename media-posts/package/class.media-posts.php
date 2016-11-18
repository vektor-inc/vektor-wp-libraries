<?php
if ( ! class_exists( 'Lightning_media_posts' ) )
{
	class Lightning_media_posts
	{

		static function init(){
				define( 'LTG_MEDIA_POSTS_URL', plugin_dir_url( __FILE__ ) );
				define( 'LTG_MEDIA_POSTS_DIR', plugin_dir_path( __FILE__ ) );
				define( 'LTG_MEDIA_POSTS_VERSION', '1.1' );
		}

		public static function patterns(){
			global $vk_ltg_media_posts_textdomain;
			$patterns = array(
				'image_1st' => array( 
					'label' => __( 'Image card 1st feature', 'lightning-media-unit' ),
					'class_outer' => 'image_1st',
					// 1stは２件目以降とpostのクラスが異なるためここに値が無い
				 ),
				'image_3' => array( 
					'label' => __( 'Image card 3 colmun', 'lightning-media-unit' ),
					'class_outer' => 'row image_3',
					'class_post_outer' => 'col-sm-6 col-lg-4 col-md-4',
					'class_post_item' => ' image_card',
				 ),
				'image_1' => array( 
					'label' => __( 'Image card 1 colmun', 'lightning-media-unit' ),
					'class_outer' => 'row image_1',
					'class_post_outer' => 'col-sm-12',
					'class_post_item' => ' image_card'
				 ),
				'vert_3' => array( 
					'label' => __( 'Vertical card 3 column', 'lightning-media-unit' ),
					'class_outer' => 'row vert_3 flex_height',
					'class_post_outer' => 'col-sm-6 col-lg-4 col-md-4 flex_height_col',
					'class_post_item' => ' vertical_card normal_image'
				 ),
				'vert_1' => array( 
					'label' => __( 'Vertical card 1 column', 'lightning-media-unit' ),
					'class_outer' => 'row vert_1',
					'class_post_outer' => 'col-sm-12',
					'class_post_item' => ' vertical_card normal_image'
				 ),
				'vert_large_image_3' => array( 
					'label' => __( 'Vertical card large image 3 column', 'lightning-media-unit' ),
					'class_outer' => 'row vert_large_image_3 flex_height',
					'class_post_outer' => 'col-sm-6 col-lg-4 col-md-4 flex_height_col',
					'class_post_item' => ' vertical_card large_image'
				 ),
				'vert_large_image_1' => array( 
					'label' => __( 'Vertical card large image 1 column', 'lightning-media-unit' ),
					'class_outer' => 'row vert_large_image_1',
					'class_post_outer' => 'col-sm-12',
					'class_post_item' => ' vertical_card large_image'
				 ),
				'oblong_1' => array( 
					'label' => __( 'Oblong card 1 column', 'lightning-media-unit' ),
					'class_outer' => 'row oblong_1',
					'class_post_outer' => 'col-sm-12',
					'class_post_item' => ' oblong_card normal_image'
				 ),
				'oblong_large_image_1' => array( 
					'label' => __( 'Oblong card large image 1 column', 'lightning-media-unit' ),
					'class_outer' => 'row oblong_large_image_1',
					'class_post_outer' => 'col-sm-12',
					'class_post_item' => ' oblong_card large_image'
				 ),
				);
			return $patterns;
		}

		public static function patterns_select_options( $selected ){
			global $vk_ltg_media_posts_textdomain;
			$patterns = Lightning_media_posts::patterns();

			$selected_html =  ( isset( $selected ) && ( $selected == 'default' ) ) ? ' selected' : '';
			$select_html = '<option value="default">'.__('Lightning default', $vk_ltg_media_posts_textdomain).'</option>';

			foreach ( $patterns as $key => $value ) {
				$selected_html =  ( isset( $selected ) && ( $selected == $key ) ) ? ' selected' : '';
				$select_html .= '<option value="'.$key.'"'.$selected_html.'>'.$value['label'].'</option>'."\n";
			}
			echo $select_html;
		}

		//get label names from theme options & translation file (if)
		public static function labelNames() {

			$post_types_labels = array(
				'post' => __( 'Posts' ),
				'page' => __( 'Pages' , 'lightning-media-unit' ),
			);

			return $post_types_labels;
		}

		public static function get_custom_types() {

			//gets all custom post types set PUBLIC
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$custom_types = get_post_types( $args, 'names' );

			// foreach ($custom_types as $name => $slug) {
			//  	$custom_types[ $name ] = 0;
			// }

			return $custom_types;
		}

		public static function get_custom_types_labels() {

			//gets all custom post types set PUBLIC
			$args = array(
				'public'   => true,
				'_builtin' => false,
			);

			$custom_types = get_post_types( $args, 'objects' );
			$custom_types_labels = array();

			foreach ( $custom_types as $custom_type ) {
				$custom_types_labels[ $custom_type->name ] = $custom_type->label;
			}

			return $custom_types_labels;
		}

		/*-------------------------------------------*/
		/*  Widgets init
		/*-------------------------------------------*/
		static function widgets_init(){
			global $vk_ltg_media_posts_textdomain;
			register_sidebar( array(
				'name' => __( 'Home content top after left', $vk_ltg_media_posts_textdomain ),
				'id' => 'home-content-top-after-left-widget-area',
				'before_widget' => '<section class="widget %2$s" id="%1$s">',
				'after_widget' => '</section>',
				'before_title' => '<h1 class="mainSection-title">',
				'after_title' => '</h1>',
			) );
			register_sidebar( array(
				'name' => __( 'Home content top after right', $vk_ltg_media_posts_textdomain ),
				'id' => 'home-content-top-after-right-widget-area',
				'before_widget' => '<section class="widget %2$s" id="%1$s">',
				'after_widget' => '</section>',
				'before_title' => '<h1 class="mainSection-title">',
				'after_title' => '</h1>',
			) );
			register_sidebar( array(
				'name' => __( 'Home content top bottom widget', $vk_ltg_media_posts_textdomain ),
				'id' => 'home-content-top-bottom-widget-area',
				'before_widget' => '<section class="widget %2$s" id="%1$s">',
				'after_widget' => '</section>',
				'before_title' => '<h1 class="mainSection-title">',
				'after_title' => '</h1>',
			) );
		}
		
		static function add_widget_area()
		{
			if ( 
				is_active_sidebar( 'home-content-top-after-left-widget-area' ) || 
				is_active_sidebar( 'home-content-top-after-right-widget-area' )
				) :
				echo '<div class="row">';
				if ( is_active_sidebar( 'home-content-top-after-left-widget-area' ) ) :
					echo '<div class="col-sm-6">';
			        dynamic_sidebar( 'home-content-top-after-left-widget-area' );
			        echo '</div>';
			    endif;
				if ( is_active_sidebar( 'home-content-top-after-right-widget-area' ) ) :
					echo '<div class="col-sm-6">';
			        dynamic_sidebar( 'home-content-top-after-right-widget-area' );
			        echo '</div>';
			    endif;
			    echo '</div>';
			endif;
			if ( is_active_sidebar( 'home-content-top-bottom-widget-area' ) ) :
				echo '<div class="row">';
				echo '<div class="col-sm-12">';
				dynamic_sidebar( 'home-content-top-bottom-widget-area' );
				echo '</div>';
				echo '</div>';
			endif;
		}

		/*-------------------------------------------*/
		/*  Add media unit css
		/*-------------------------------------------*/
		
		static function print_css() 
		{
			// デフォルトでは出力しない
			$print_css_default = false;
			if( apply_filters('lightning_print_media_posts_css_custom', $print_css_default ) ){
				wp_enqueue_style( 'lightning_media_posts_style', LTG_MEDIA_POSTS_URL.'css/media_posts.css', array(), LTG_MEDIA_POSTS_VERSION, 'all' );
			}
		}

	    /*-------------------------------------------*/
	    /*  実行
	    /*-------------------------------------------*/

	    public function __construct(){
	    	
	    	add_action( 'wp_enqueue_scripts',  array( $this, 'print_css' ) );
	    	add_action( 'lightning_home_content_top_widget_area_after', array( $this, 'add_widget_area' ) );
	    	add_action( 'widgets_init', array( $this, 'widgets_init' ), 100 );
	    	// 0.618 = 1:1.618 = 0.38 : 0.62
			add_image_size('media_thumbnail', 600, 371 , true );
			require_once( 'class.widget.media-posts.php' );
			require_once( 'views/class.loop-post-view.php' );
			require_once( 'class.font-awesome-selector.php' );
			require_once( 'class.media-unit-admin.php' );
	    }

	} // class Lightning_media_posts

	new Lightning_media_posts();
	Lightning_media_posts::init();

	/*-------------------------------------------*/
	/*  Archive Loop change
	/*-------------------------------------------*/
	add_filter( 'is_lightning_extend_loop', 'lmu_is_loop_layout_change' );
	function lmu_is_loop_layout_change( $flag ){
		$ltg_media_unit_archive_loop_layout = get_option( 'ltg_media_unit_archive_loop_layout' );
		$post_type = lightning_get_post_type();
		$postType = $post_type['slug'];

		if ( is_author() ){
				$postType = 'author';
		}

		if ( isset ( $ltg_media_unit_archive_loop_layout[$postType] ) ) {

			if ( 
				( $ltg_media_unit_archive_loop_layout[$postType] != 'default' ) || 
				( !$ltg_media_unit_archive_loop_layout[$postType] )
				) 
			{
					add_action( 'lightning_extend_loop', 'lmu_do_loop_layout_change' );
					$flag = true;
			}
		} // if ( isset ( $ltg_media_unit_archive_loop_layout[$postType] ) ) {
		return $flag;
	}

	function lmu_do_loop_layout_change(){
		$ltg_media_unit_archive_loop_layout = get_option( 'ltg_media_unit_archive_loop_layout' );
		$post_type = lightning_get_post_type();
		$post_type_slug = $post_type['slug'];
		$post_type_slug = ( is_author() ) ? 'author' : $post_type['slug'];
		if ( isset( $ltg_media_unit_archive_loop_layout[$post_type_slug] ) ){

			$layout = $ltg_media_unit_archive_loop_layout[$post_type_slug];
			$instance['new_icon_display'] = 7;
			echo '<div class="loop_outer">';

			Ltg_Media_Post_View::post_loop($layout, $instance);

			echo '</div>';
		}
	}

} // if ( ! class_exists( 'Lightning_media_posts' ) )