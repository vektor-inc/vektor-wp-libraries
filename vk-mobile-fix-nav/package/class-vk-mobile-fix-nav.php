<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

// add_action( 'after_setup_theme', 'vkmn_nav_add_customize_panel' );
//
// // カスタマイズパネルを出力するかどうかの判別
// function vkmn_nav_add_customize_panel() {
// 		// カスタマイザーが利用されるので、独自のコントロールクラスを追加
//
// }

add_action( 'customize_register', 'vkmn_customize_register_add_control', 10 );

/*-------------------------------------------*/
/*	ExUnit Original Controls
/*-------------------------------------------*/
if ( ! function_exists( 'vkmn_customize_register_add_control' ) ) {
function vkmn_customize_register_add_control() {

	/*	Add text control description
	/*-------------------------------------------*/
	class MobileNav_Custom_Html extends WP_Customize_Control {
		public $type             = 'customtext';
		public $custom_title_sub = ''; // we add this for the extra custom_html
		public $custom_html      = ''; // we add this for the extra custom_html
		public function render_content() {
			if ( $this->label ) {
				// echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
				echo '<h2 class="admin-custom-h2">' . wp_kses_post( $this->label ) . '</h2>';
			}
			if ( $this->custom_title_sub ) {
				echo '<h3 class="admin-custom-h3">' . wp_kses_post( $this->custom_title_sub ) . '</h3>';
			}
			if ( $this->custom_html ) {
				echo '<div>' . wp_kses_post( $this->custom_html ) . '</div>';
			}
		} // public function render_content() {
	} // class MobileNav_Custom_Html extends WP_Customize_Control

} // function veu_customize_register_add_control(){
} // if ( ! function_exists( 'vkmn_customize_register_add_control' ) ) {


if ( ! class_exists( 'Vk_Mobile_Fix_Nav' ) ) {

  class Vk_Mobile_Fix_Nav
  {

    public static $version = '0.0.0';

		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
      add_action( 'customize_register', array( $this, 'vk_mobil_fix_nav_customize_register' ) );
    }

    /*-------------------------------------------*/
    /*	Customizer
    /*-------------------------------------------*/

    public function vk_mobil_fix_nav_customize_register( $wp_customize ) {

      // セクション、テーマ設定、コントロールを追加
      global $vk_mobile_fix_nav_textdomain;

      // セクション追加
      $wp_customize->add_section(
          'vk_mobil_fix_nav_related_setting', array(
          'title'    => __( 'Mobil Fix Nav', $vk_mobile_fix_nav_textdomain ),
          'priority' => 900,
          )
      );

			// first_btn_menu_setting セッティング
			$wp_customize->add_setting( 'vk_mobil_fix_nav_related_options[first_btn_menu_setting]', array(
					'default'			      => false,
					'type'				      => 'option', // 保存先 option or theme_mod
					'capability'		    => 'edit_theme_options', // サイト編集者
					'sanitize_callback' => 'veu_sanitize_boolean',
					)
			);

			// first_btn_menu_setting コントロール
			$wp_customize->add_control( 'vk_mobil_fix_nav_related_options[first_btn_menu_setting]', array(
					'label'     => __( 'Make the leftmost button open and close the menu.', $vk_mobile_fix_nav_textdomain ),
					'section'   => 'vk_mobil_fix_nav_related_setting',
					'settings'  => 'vk_mobil_fix_nav_related_options[first_btn_menu_setting]',
					'type'		  => 'checkbox',
					)
			);

			// 「first_btn_menu_setting」にチェックが入っているときの処理
			$first_btn_menu_setting = ['first_btn_menu_setting'];
			if ( isset( $first_btn_menu_setting ) && $first_btn_menu_setting == true ) {
				 // link_text セッティング
				 $wp_customize->add_setting(
				 		'vk_mobil_fix_nav_related_options[link_text_0]', array(
				 		'default'           => '',
				 		'type'              => 'option', // 保存先 option or theme_mod
				 		'capability'        => 'edit_theme_options', // サイト編集者
				 		'sanitize_callback' => 'sanitize_text_field',
				 		)
				 );

				 // link_text コントロール
				 $wp_customize->add_control(
				 		'link_text_0', array(
				 		'label'    => __( 'Link text:', $vk_mobile_fix_nav_textdomain ),
				 		'section'  => 'vk_mobil_fix_nav_related_setting',
				 		'settings' => 'vk_mobil_fix_nav_related_options[link_text_0]',
				 		'type'     => 'text',
				 		)
				 );
			} // if ( isset( $first_btn_menu_setting ) && $first_btn_menu_setting == true ) {

      for ( $i = 1; $i <= 4; $i++ ) {

        // nav_title
        $wp_customize->add_setting( 'nav_title_'.$i, array(
          'sanitize_callback' => 'sanitize_text_field'
          )
        );
        $wp_customize->add_control(
          new MobileNav_Custom_Html(
            $wp_customize, 'nav_title_'.$i, array(
              'label'            => __( 'Navi Settings', $vk_mobile_fix_nav_textdomain ).' [ '.$i.' ]',
              'section'          => 'vk_mobil_fix_nav_related_setting',
              'type'             => 'text',
              'custom_title_sub' => '',
              'custom_html'      => '',
            )
          )
        );

        // link_text セッティング
        $wp_customize->add_setting(
            'vk_mobil_fix_nav_related_options[link_text_'.$i.']', array(
            'default'           => '',
            'type'              => 'option', // 保存先 option or theme_mod
            'capability'        => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'sanitize_text_field',
            )
        );

        // link_text コントロール
        $wp_customize->add_control(
            'link_text_'.$i, array(
            'label'    => __( 'Link text:', $vk_mobile_fix_nav_textdomain ),
            'section'  => 'vk_mobil_fix_nav_related_setting',
            'settings' => 'vk_mobil_fix_nav_related_options[link_text_'.$i.']',
            'type'     => 'text',
            )
        );

        // link_icon セッティング
        $wp_customize->add_setting(
            'vk_mobil_fix_nav_related_options[link_icon_'.$i.']', array(
            'default'           => '',
            'type'              => 'option', // 保存先 option or theme_mod
            'capability'        => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'sanitize_text_field',
            )
        );

        // link_icon コントロール
        $wp_customize->add_control(
            'link_icon_'.$i, array(
            'label'       => __( 'Icon font class name:', $vk_mobile_fix_nav_textdomain ),
            'section'     => 'vk_mobil_fix_nav_related_setting',
            'settings'    => 'vk_mobil_fix_nav_related_options[link_icon_'.$i.']',
            'type'        => 'text',
            'description' => __( '[ <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">Font Awesome Icons</a> ]<br>To choose your favorite icon, and enter the class.<br>ex:fas fa-home', $vk_mobile_fix_nav_textdomain ),
            )
        );

        // link_url セッティング
        $wp_customize->add_setting(
            'vk_mobil_fix_nav_related_options[link_url_'.$i.']', array(
            'default'           => '',
            'type'              => 'option', // 保存先 option or theme_mod
            'capability'        => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'sanitize_text_field',
            )
        );

        // link_url コントロール
        $wp_customize->add_control(
            'link_url_'.$i, array(
            'label'       => __( 'Link URL:', $vk_mobile_fix_nav_textdomain ),
            'section'     => 'vk_mobil_fix_nav_related_setting',
            'settings'    => 'vk_mobil_fix_nav_related_options[link_url_'.$i.']',
            'type'        => 'text',
            'description' => __( 'ex ) https://vccw.text/', $vk_mobile_fix_nav_textdomain ),
            )
        );

        // link_blank セッティング
        $wp_customize->add_setting( 'vk_mobil_fix_nav_related_options[link_blank_'.$i.']', array(
            'default'			      => false,
            'type'				      => 'option', // 保存先 option or theme_mod
            'capability'		    => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'veu_sanitize_boolean',
            )
        );

        // link_blank コントロール
        $wp_customize->add_control( 'vk_mobil_fix_nav_related_options[link_blank_'.$i.']', array(
            'label'     => __( 'Open link new tab.', $vk_mobile_fix_nav_textdomain ),
            'section'   => 'vk_mobil_fix_nav_related_setting',
            'settings'  => 'vk_mobil_fix_nav_related_options[link_blank_'.$i.']',
            'type'		  => 'checkbox',
            )
        );

        // Click event セッティング
        $wp_customize->add_setting(
            'vk_mobil_fix_nav_related_options[event_'.$i.']', array(
            'default'           => '',
            'type'              => 'option', // 保存先 option or theme_mod
            'capability'        => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'sanitize_text_field',
            )
        );

        // Click event コントロール
        $wp_customize->add_control(
            'event_'.$i, array(
            'label'    => __( 'Click event:', $vk_mobile_fix_nav_textdomain ),
            'section'  => 'vk_mobil_fix_nav_related_setting',
            'settings' => 'vk_mobil_fix_nav_related_options[event_'.$i.']',
            'type'     => 'text',
						'description' => __( "ex ) ga('send', 'event', 'Videos', 'play', 'Fall Campaign');", $vk_mobile_fix_nav_textdomain ),
            )
        );

      } // for ($i = 1; $i <= 4; $i++) {

				// nav_common
				$wp_customize->add_setting( 'nav_common', array(
					'sanitize_callback' => 'sanitize_text_field'
					)
				);
				$wp_customize->add_control(
					new MobileNav_Custom_Html(
						$wp_customize, 'nav_common', array(
							'label'            => __( 'Navi Common Settings', $vk_mobile_fix_nav_textdomain ),
							'section'          => 'vk_mobil_fix_nav_related_setting',
							'type'             => 'text',
							'custom_title_sub' => '',
							'custom_html'      => '',
						)
					)
				);

      // color セッティング
      $wp_customize->add_setting(
          'vk_mobil_fix_nav_related_options[color]', array(
          'default'           => '#2e6da4',
          'type'              => 'option', // 保存先 option or theme_mod
          'capability'        => 'edit_theme_options', // サイト編集者
          'sanitize_callback' => 'sanitize_hex_color',
          )
      );

      // color コントロール
      $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
          'color', array(
          'label'       => __( 'Text Color:', $vk_mobile_fix_nav_textdomain ),
          'section'     => 'vk_mobil_fix_nav_related_setting',
          'settings'    => 'vk_mobil_fix_nav_related_options[color]',
          )
        )
      );

      // nav_bg_color セッティング
      $wp_customize->add_setting(
          'vk_mobil_fix_nav_related_options[nav_bg_color]', array(
          'default'           => '#FFF',
          'type'              => 'option', // 保存先 option or theme_mod
          'capability'        => 'edit_theme_options', // サイト編集者
          'sanitize_callback' => 'sanitize_hex_color',
          )
      );

      // nav_bg_color コントロール
      $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
          'nav_bg_color', array(
          'label'       => __( 'Background Color:', $vk_mobile_fix_nav_textdomain ),
          'section'     => 'vk_mobil_fix_nav_related_setting',
          'settings'    => 'vk_mobil_fix_nav_related_options[nav_bg_color]',
          )
        )
      );

      // current_color セッティング
      $wp_customize->add_setting(
          'vk_mobil_fix_nav_related_options[current_color]', array(
          'default'           => '#16354f',
          'type'              => 'option', // 保存先 option or theme_mod
          'capability'        => 'edit_theme_options', // サイト編集者
          'sanitize_callback' => 'sanitize_hex_color',
          )
      );

      // current_color コントロール
      $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize,
          'current_color', array(
          'label'       => __( 'Current Color:', $vk_mobile_fix_nav_textdomain ),
          'section'     => 'vk_mobil_fix_nav_related_setting',
          'settings'    => 'vk_mobil_fix_nav_related_options[current_color]',
          )
        )
      );


      /*-------------------------------------------*/
      /*	Add Edit Customize Link Btn
      /*-------------------------------------------*/
      $wp_customize->selective_refresh->add_partial(
        'vk_mobil_fix_nav_related_options[nav_bg_color]', array(
          'selector'        => '.mobil-fix-nav',
          'render_callback' => '',
        )
      );

    } // function vk_mobil_fix_nav_customize_register( $wp_customize ) {

			/*-------------------------------------------*/
			/*  Load js & CSS
			/*-------------------------------------------*/

			static function add_script() {
				wp_enqueue_style( 'vk-mobile-fix-nav', get_template_directory_uri( __FILE__ ) . '/inc/vk-mobile-fix-nav/css/fix-nav.css', array(), self::$version, 'all' );
			}

  } // class Vk_Mobile_Fix_Nav {

  $vk_mobile_fix_nav = new Vk_Mobile_Fix_Nav();

} // if ( ! class_exists('Vk_Mobile_Fix_Nav') )  {

add_action( 'wp_footer', 'vk_mobil_fix_nav' );
function vk_mobil_fix_nav() {
  if ( wp_is_mobile() ) {
    $options = get_option( 'vk_mobil_fix_nav_related_options' );

    // text color
    if ( isset( $options['color'] ) && $options['color'] ) {
      $color =  $options['color'];
      // $color = $options['color'];
    } else {
      $color = '';
    }

    // bg color
    if ( isset( $options['nav_bg_color'] ) && $options['nav_bg_color'] ) {
      $nav_bg_color = $options['nav_bg_color'];
    } else {
      $nav_bg_color = '#FFF';
    }


    ?>
    <nav class="footer-mobil-fix-nav">
      <ul class="mobil-fix-nav" style="background-color: <?php echo sanitize_hex_color( $nav_bg_color ) ?>;">

				<?php

				// first_btn_menu_setting
				if ( ! empty( $options['first_btn_menu_setting'] ) ) {
					echo '<li>';
					// echo '<div class="vk-mobile-nav-menu-btn">';
					// echo '<a class="" href="'.esc_url( '#' ).'">
					// <div id="menuBtn" class="vk-mobile-nav-menu-btn btn btn-default menuBtn menuBtn_left"><i class="fa fa-bars" aria-hidden="true"></i>MENU</div><br>'.esc_html( $options['link_text_0'] ).'</a>';
					echo '<a href="#" class="btn btn-default menuBtn menuBtn_left menuClose" id="menuBtn"><i class="fa fa-bars" aria-hidden="true"></i><br>'.esc_html( $options['link_text_0'] ).'</a>';
					// echo '</div>';
					echo '</li>';
				}

        for ( $i = 1; $i <= 4; $i++ ) {

          // link text
          if ( ! empty( $options['link_text_'.$i] ) ) {
            $link_text = $options['link_text_'.$i];
          } else {
            $link_text = '';
          }

          // fontawesome icon
          if ( ! empty( $options['link_icon_'.$i] ) ) {
            $link_icon = $options['link_icon_'.$i];
          } else {
            $link_icon = '';
          }

          // link URL
          if ( ! empty( $options['link_url_'.$i] ) ) {
            $link_url = $options['link_url_'.$i];
          } else {
            $link_url = '';
          }

          // link_blank
          if ( ! empty( $options['link_blank_'.$i] ) ) {
            $blank = ' target="_blank"';
          } else {
            $blank = '';
          }

          // color
          if ( isset( $options['color'] ) && $options['color'] ) {
            $color = $options['color'];
          } else {
            $color = '#2e6da4';
          }

          // current color
          if ( isset( $options['current_color'] ) && $options['current_color'] ) {
            $current_color = $options['current_color'];
          } else {
            $current_color = '#16354f';
          }

          // 実際に HTML を出力する
          if ( isset( $options['link_text_'.$i] ) && $options['link_text_'.$i] || isset( $options['link_icon_'.$i] ) && $options['link_icon_'.$i] ) {
            echo '<li>';
            // page-current
            $get_current_link = get_the_permalink();
						$postid = url_to_postid( get_permalink() );
						// $get_current_link_cat = get_category_link( $postid );
						$get_current_link_cat = get_the_category_list( $postid );
						// $get_current_link_cat = get_post_type_archive_link( $postid );
						// $get_current_link_cat = get_post_type_archive_link( get_post_type() );
            if ( ( ! empty( $options['link_url_'.$i] ) && ( $get_current_link == $options['link_url_'.$i] ) ) || ( ! empty( $options['link_url_'.$i] ) && ( $get_current_link_cat == $options['link_url_'.$i] ) ) ) {
              // $page_current = ' class="page-current"';
              $color_style = $current_color;
            } else {
              $color_style = $color;
            }

						// click event
						$event = '';
						// クリックイベントが入力されていたら
						 if ( ! empty( $options['event_'.$i] ) && $options['event_'.$i] ){
							 /*
					 		onclickはクリックが終わった瞬間に発生するイベント
					 		クリック終了後にイベントが発生し、Googleにビーコンを送信しますが、
					 		ビーコンが送られる前に次のページに遷移してしまうとカウントされない場合がある
					 		*/
					 		if ( wp_is_mobile() ) {
					 			$event = ' ontouchstart="';
					 		} else {
					 			$event = ' onmousedown="';
					 		}
							 $event .= $options['event_'.$i].'"';
						 } // if ( ! empty( $options['event_'.$i] ) && $options['event_'.$i] ){

            echo '<a href="'.esc_url( $link_url ).'" '.$blank.' style="color: '.$color_style.';"'.$event.'>
            <span class="link-icon"><i class="'.esc_html( $link_icon ).'"></i></span><br>'.esc_html( $link_text ).'</a>';
          	echo '</li>';
          }

        } // <?php for ( $i = 1; $i <= 4; $i++ ) { ?>

      </ul>
    </nav>

  <?php
  } //if ( wp_is_mobile() ) {
} // function vk_mobil_fix_nav() {
?>
