<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

if (! class_exists('Vk_Mobile_Fix_Nav')) {

  class Vk_Mobile_Fix_Nav
  {

    public static $version = '0.0.0';

    /*-------------------------------------------*/
    /*	Customizer
    /*-------------------------------------------*/

    public function __construct() {
      add_action('customize_register', array( $this, 'vk_mobil_fix_nav_customize_register'));
    }

    public function vk_mobil_fix_nav_customize_register($wp_customize) {

      // セクション、テーマ設定、コントロールを追加
      global $vk_mobile_fix_nav_textdomain;

      // セクション追加
      $wp_customize->add_section(
          'vk_mobil_fix_nav_related_setting', array(
          'title'    => __('Mobil Fix Nav', $vk_mobile_fix_nav_textdomain),
          'priority' => 900,
          )
      );

      for ($i = 1; $i <= 4; $i++) {

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
            'label'    => '['.$i.']'.__('Link text:', $vk_mobile_fix_nav_textdomain),
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
            'label'       => '['.$i.']'.__('Icon font class name:', $vk_mobile_fix_nav_textdomain),
            'section'     => 'vk_mobil_fix_nav_related_setting',
            'settings'    => 'vk_mobil_fix_nav_related_options[link_icon_'.$i.']',
            'type'        => 'text',
            'description' => __('[ <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">Font Awesome Icons</a> ]<br>To choose your favorite icon, and enter the class.<br>ex:fas fa-home', $vk_mobile_fix_nav_textdomain),
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
            'label'       => '['.$i.']'.__('Link URL:', $vk_mobile_fix_nav_textdomain),
            'section'     => 'vk_mobil_fix_nav_related_setting',
            'settings'    => 'vk_mobil_fix_nav_related_options[link_url_'.$i.']',
            'type'        => 'text',
            )
        );

        // link_blank セッティング
        $wp_customize->add_setting('vkExUnit_sns_options[link_blank_'.$i.']', array(
            'default'			      => false,
            'type'				      => 'option', // 保存先 option or theme_mod
            'capability'		    => 'edit_theme_options', // サイト編集者
            'sanitize_callback' => 'veu_sanitize_boolean',
            )
        );

        // link_blank コントロール
        $wp_customize->add_control('vkExUnit_sns_options[link_blank_'.$i.']', array(
            'label'     => '['.$i.']'.__('Open link new tab.', $vk_mobile_fix_nav_textdomain),
            'section'   => 'vk_mobil_fix_nav_related_setting',
            'settings'  => 'vkExUnit_sns_options[link_blank_'.$i.']',
            'type'		  => 'checkbox',
            )
        );

      } // for ($i = 1; $i <= 4; $i++) {

      // color セッティング
      $wp_customize->add_setting(
          'vk_mobil_fix_nav_related_options[color]', array(
          'default'           => '',
          'type'              => 'option', // 保存先 option or theme_mod
          'capability'        => 'edit_theme_options', // サイト編集者
          'sanitize_callback' => 'sanitize_hex_color',
          )
      );

      // color コントロール
      $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,
          'color', array(
          'label'       => __('Text Color:', $vk_mobile_fix_nav_textdomain),
          'section'     => 'vk_mobil_fix_nav_related_setting',
          'settings'    => 'vk_mobil_fix_nav_related_options[color]',
          )
        )
      );

      // nav_bg_color セッティング
      $wp_customize->add_setting(
          'vk_mobil_fix_nav_related_options[nav_bg_color]', array(
          'default'           => '',
          'type'              => 'option', // 保存先 option or theme_mod
          'capability'        => 'edit_theme_options', // サイト編集者
          'sanitize_callback' => 'sanitize_hex_color',
          )
      );

      // nav_bg_color コントロール
      $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize,
          'nav_bg_color', array(
          'label'       => __('Background Color:', $vk_mobile_fix_nav_textdomain),
          'section'     => 'vk_mobil_fix_nav_related_setting',
          'settings'    => 'vk_mobil_fix_nav_related_options[nav_bg_color]',
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

  } // class Vk_Mobile_Fix_Nav {

  $vk_mobile_fix_nav = new Vk_Mobile_Fix_Nav();

} // if ( ! class_exists('Vk_Mobile_Fix_Nav') )  {

add_action('wp_footer', 'vk_mobil_fix_nav');
function vk_mobil_fix_nav() {
  if (wp_is_mobile()) {
    $options = get_option( 'vk_mobil_fix_nav_related_options' );

    // text color
    if (isset($options['color']) && $options['color']) {
      $color = $options['color'];
    } else {
      $color = '#FFFFFF';
    }

    // bg color
    if (isset($options['nav_bg_color']) && $options['nav_bg_color']) {
      $nav_bg_color = $options['nav_bg_color'];
    } else {
      $nav_bg_color = '#000000';
    }

    ?>
    <nav class="footer-mobil-fix-nav">
      <ul class="mobil-fix-nav" style="background-color: <?php echo sanitize_hex_color($nav_bg_color) ?>;">

        <?php for ($i = 1; $i <= 4; $i++) {

          // link text
          if (! empty($options['link_text_'.$i])) {
            $link_text = $options['link_text_'.$i];
          } else {
            $link_text = '';
          }

          // fontawesome icon
          if (! empty($options['link_icon_'.$i])) {
            $link_icon = $options['link_icon_'.$i];
          } else {
            $link_icon = '';
          }

          // link URL
          if (! empty($options['link_url_'.$i])) {
            $link_url = $options['link_url_'.$i];
          } else {
            $link_url = '';
          }

          echo '<li class="">';
            $blank = (isset($options['link_blank_'.$i]) && $options['link_blank_'.$i]) ? 'target="_blank"':'';
            echo '<a href="'.esc_url($link_url).'" '.$blank.' style="color: '.sanitize_hex_color($color).';">
            <span class="link-icon"><i class="'.esc_html($link_icon).'"></i></span><br>'.esc_html($link_text).'</a>';
          echo '</li>';

        } ?>
      </ul>
    </nav>
  <?php
  }
}
?>
