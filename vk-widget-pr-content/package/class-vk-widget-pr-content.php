<?php

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

class VK_Widget_Pr_Content extends WP_Widget {
  /**
     * Widgetを登録する
  **/
  public static $version = '0.0.0';

  function __construct() {
    global $pr_content_textdomain;
    if ( function_exists('vkExUnit_get_short_name') ) {
      $prefix = vkExUnit_get_short_name();
    } else {
      $prefix = 'VK';
    }
    $widget_name = $prefix. '_' . __( 'PR Content', $pr_content_textdomain );
    parent::__construct(
        'VK_Widget_Pr_Content', //ID
        $widget_name, //widget_name
        array( 'description' => __( 'Content PR widget', $pr_content_textdomain ) ) //Widgetの説明
    );
		add_action( 'wp_head', array( $this, 'print_css' ), 2);
    add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
  }

  public static function add_script() {
    wp_enqueue_style( 'vk-admin-style', plugin_dir_url( __FILE__ ) . 'css/vk-widget-pr-content.css', array(), self::$version, 'all' );
  }


  /**
    * 入力された値とデフォルト値を結合するメソッド
  **/
  function options( $instance = array() )
  {
    $defaults = array(
      'title'          => '',
      'text'           => '',
      'media_image'    => null,
      'btn_text'       => '',
      'btn_url'        => '',
      'btn_blank'      => false,
      // 'btn_color'   => null,
      'bg_color'       => null,
      'margin_top'     => null,
      'margin_bottom'  => null,
      'layout_type'    => null,
    );
    return wp_parse_args( (array) $instance, $defaults );
  }

  /**
     * 表側の Widget を出力する（表示用コード）
  **/
  function widget( $args, $instance )
  {
    // 入力された値とデフォルトで指定した値を あーん して$options にいれる
    $options = self::options( $instance );
    echo '<style type="text/css">.mainSection #'.$args['widget_id'].'.widget_vk_widget_pr_content { margin-top:'.$options['margin_top'].'; margin-bottom:'.$options['margin_bottom'].';}</style>';
    echo $args['before_widget'];
    if ( ! empty( $options['bg_color'] ) ) {
      $bg_color = sanitize_hex_color( $options['bg_color'] );
      echo '<div class="pr-content" style="background-color:'.$bg_color.';">';
    } else {
      echo '<div class="pr-content">';
    }
    echo '<div class="container">';

    if ( $instance[ 'layout_type' ] === 'left' ) {
      $layout_type = 'left';
    } else {
      $layout_type = 'right';
    }
    ?>
    <div class="row <?php echo $layout_type ?>">
    <div class="col-sm-6 pr-content-col-img"><?php
    // media img
    // 画像IDから画像のURLを取得
    if ( ! empty( $options['media_image'] ) && is_numeric( $options['media_image'] ) ) {
      $attr = array(
          'class' => "pr_content_media_imgage", //任意の class名を追記する
      );
      echo wp_get_attachment_image( $options['media_image'], 'large', false, $attr );
    }

    ?></div><!-- .col-sm-6 -->
    <div class="col-sm-6 pr-content-col-text"><?php
    // title
    if ( $options['title'] ) {
      echo '<h3 class="pr-content-title">'.esc_html( $options['title'] ).'</h3>';
    }
    // text
    echo '<p>'.wp_kses_post( $options['text'] ).'</p>';
    // link btn
    if ( ! empty ( $options['btn_text'] ) && ! empty ( $options['btn_url'] )) {
      $more_link_html = '<div class="pr-content-btn">';
      if( ! empty( $options['btn_blank'] ) ) {
        $blank = 'target="_blank"';
      } else {
        $blank = '';
      }
      $more_link_html .= '<a href="'.esc_url( $options['btn_url'] ).'" class="btn btn-primary btn-block btn-lg"'. $blank.'>'.wp_kses_post( $options['btn_text'] ).'</a>';
      $more_link_html .= '</div>';
    } else {
      $more_link_html = '';
    }
    echo $more_link_html;
    ?></div><!-- .col-sm-6 -->
    </div><!-- .row -->
    </div><!-- .container -->
    <?php
    echo '</div>'; // .pr-content
		echo $args['after_widget'];
  }

	/*-------------------------------------------*/
	/*  update
	/*-------------------------------------------*/

	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance[ 'title' ] = wp_kses_post( $new_instance[ 'title' ] );
		$instance[ 'text' ] = wp_kses_post( $new_instance[ 'text' ] );
		$instance[ 'media_image' ] = wp_kses_post( $new_instance[ 'media_image' ] );
		$instance[ 'btn_text' ] = wp_kses_post( $new_instance[ 'btn_text' ] );
		$instance[ 'btn_url' ] = esc_url( $new_instance[ 'btn_url' ] );
		$instance[ 'btn_blank' ] = ( isset( $new_instance[ 'btn_blank' ] ) && $new_instance[ 'btn_blank' ] ) ? true : false;
		$instance[ 'bg_color' ] = ( isset( $new_instance[ 'bg_color' ] ) ) ? sanitize_hex_color( $new_instance[ 'bg_color' ]) : false ;
		$instance[ 'margin_top' ] = wp_kses_post( mb_convert_kana($new_instance[ 'margin_top' ], 'a') );
		$instance[ 'margin_bottom' ] = wp_kses_post( mb_convert_kana($new_instance[ 'margin_bottom' ], 'a') );
    $instance[ 'layout_type' ] = $new_instance[ 'layout_type' ];
		return $instance;
	}

  /*-------------------------------------------*/
  /*  form
  /*-------------------------------------------*/

  function form( $instance )
  {
      global $pr_content_textdomain;
      $options = self::options( $instance );
      ?>
      <br>
      <label for="<?php echo $this->get_field_id('title'); ?>" ><?php _e('Title:', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id('title'); ?>-title" name="<?php echo $this->get_field_name('title'); ?>" style="width:100%; margin-bottom: 1.5em;" value="<?php echo esc_attr( $options['title'] ); ?>"></input>

      <label for="<?php echo $this->get_field_id('text'); ?>" ><?php _e('Text:', $pr_content_textdomain); ?></label>
      <textarea id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" style="width:100%; margin-bottom: 1.5em;"><?php echo esc_textarea( $options['text'] ); ?></textarea>

			<?php
      $image = null;
      // ちゃんと数字が入っているかどうか？
      if ( is_numeric( $options['media_image'] ) ) {
        // 数字だったら、その数字の画像を large サイズで取得
          $image = wp_get_attachment_image_src( $options['media_image'], 'large' );
      }
      ?>
      <div class="pr_content_media_area" style="padding: 0.5em 0 3.0em;">
      <div class="_display" style="height:auto">
          <?php if ( $image ): ?>
              <img src="<?php echo esc_url( $image[0] ); ?>" style="width:100%; height:auto; border: 1px solid #ccc; margin: 0 0 15px;" />
          <?php endif; ?>

      </div>
      <button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:0;" onclick="javascript:media_image_addiditional(this);return false;"><?php _e('Set image', $pr_content_textdomain ); ?></button>
      <button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:4px 0;" onclick="javascript:vk_title_bg_image_delete(this);return false;"><?php _e('Delete image', $pr_content_textdomain ); ?></button>
      <div class="_form" style="line-height: 2em">
          <input type="hidden" class="__id" name="<?php echo $this->get_field_name( 'media_image' ); ?>" value="<?php echo esc_attr( $options['media_image'] ); ?>" />
      </div>
      </div>
			<script type="text/javascript">
      // 画像登録処理
      if ( media_image_addiditional == undefined ){
      var media_image_addiditional = function(e){
      		// プレビュー画像を表示するdiv
          var d=jQuery(e).parent().children("._display");
      		// 画像IDを保存するinputタグ
          var w=jQuery(e).parent().children("._form").children('.__id')[0];
          var u=wp.media({library:{type:'image'},multiple:false}).on('select', function(e){
              u.state().get('selection').each(function(f){
      					d.children().remove();
      					d.append(jQuery('<img style="width:100%;mheight:auto">').attr('src',f.toJSON().url));
      					jQuery(w).val(f.toJSON().id).change();
      				});
          });
          u.open();
      };
      }
      // 背景画像削除処理
      if ( media_image_delete == undefined ){
      var media_image_delete = function(e){
      		// プレビュー画像を表示するdiv
      		var d=jQuery(e).parent().children("._display");
      		// 画像IDを保存するinputタグ
      		var w=jQuery(e).parent().children("._form").children('.__id')[0];

      		// プレビュー画像のimgタグを削除
      		d.children().remove();
      		// w.attr("value","");
      		jQuery(e).parent().children("._form").children('.__id').attr("value","").change();
      };
      }
      </script>

			<?php // Read more ?>
      <label for="<?php echo $this->get_field_id( 'btn_url' );  ?>"><?php _e( 'Destination URL:', $pr_content_textdomain ); ?></label><br/>
      <input type="text" id="<?php echo $this->get_field_id( 'btn_url' ); ?>" name="<?php echo $this->get_field_name( 'btn_url' ); ?>" value="<?php echo esc_attr( $options['btn_url'] ); ?>" style="margin-bottom: 0.5em;" />
      <br /><br />
      <label for="<?php echo $this->get_field_id( 'btn_text' );  ?>"><?php _e( 'Notation text:', $pr_content_textdomain ); ?></label><br/>
      <input type="text" placeholder="詳細を見る ≫" id="<?php echo $this->get_field_id( 'btn_text' ); ?>" name="<?php echo $this->get_field_name( 'btn_text' ); ?>" value="<?php echo esc_attr( $options['btn_text'] ); ?>" style="margin-bottom: 1.5em;" />
      <br>

      <?php // target blank ?>
      <input type="checkbox" id="<?php echo $this->get_field_id('btn_blank'); ?>" name="<?php echo $this->get_field_name('btn_blank'); ?>" value="true" <?php if($options['btn_blank']) echo 'checked'; ?> />
      <label for="<?php echo $this->get_field_id('btn_blank'); ?>" ><?php _e('Open with new tab', $pr_content_textdomain); ?></label>
      <br><br>

      <?php // bg color ?>
      <label for="<?php echo $this->get_field_id( 'bg_color' ); ?>" class="color_picker_wrap"><?php _e( 'Background color:', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id( 'bg_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'bg_color'); ?>" value="<?php if($options['bg_color']) echo esc_attr( $options['bg_color']); ?>" />
      <br><br>

      <?php // margin_top . margin_bottom ?>
      <label for="<?php echo $this->get_field_id('margin_top'); ?>" ><?php _e('Margin-top<br>Please also enter the unit. (Example: 30px):', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id('margin_top'); ?>-margin_top" name="<?php echo $this->get_field_name('margin_top'); ?>" style="width:100%; margin-bottom: 1.5em;" value="<?php echo esc_attr( $options['margin_top'] ); ?>"></input>

      <label for="<?php echo $this->get_field_id('margin_bottom'); ?>" ><?php _e('Margin-bottom<br>Please also enter the unit. (Example: 30px):', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id('margin_bottom'); ?>-margin_bottom" name="<?php echo $this->get_field_name('margin_bottom'); ?>" style="width:100%; margin-bottom: 1.5em;" value="<?php echo esc_attr( $options['margin_bottom'] ); ?>"></input>

      <?php // layout_type ?>
      <p><?php _e('Select layout type:', $pr_content_textdomain); //レイアウトタイプを選択 ?><br>
        <?php
        $checked = '';
        if (
          // $instance[ 'layout_type' ] が定義されていて、値がleftの場合
          ( isset( $instance[ 'layout_type' ] ) && $instance[ 'layout_type' ] === 'left' ) ||
          // $instance[ 'layout_type' ] が定義されていない場合
          empty( $instance[ 'layout_type' ] )
         ) {
          // ' checked'を指定する
          $checked = ' checked';
        } ?>
        <input type="radio" name="<?php echo $this->get_field_name( 'layout_type' ); ?>" value="left" <?php echo $checked ?> />
        <label for="<?php echo $this->get_field_id( 'layout_type' ); ?>"> <?php _e( 'Put the image to the left', $pr_content_textdomain ); ?></label>
        <br>
        <?php $checked = ( isset( $instance[ 'layout_type' ] ) && $instance[ 'layout_type' ] === 'right' ) ? ' checked' : ''; ?>
        <input type="radio" name="<?php echo $this->get_field_name( 'layout_type' ); ?>" value="right" <?php echo $checked ?> />
        <label for="<?php $this->get_field_id( 'layout_type' ); ?>"> <?php _e( 'Put the image to the right', $pr_content_textdomain ); ?></label>
      </p>
      <br><br>


  <?php
  }


	/*-------------------------------------------*/
	/*  Position Change
	/*-------------------------------------------*/

	function print_css(){
			$custom_css = '';
			// 両サイドのスペースを消す
			$custom_css = trim($custom_css);
			// 改行、タブをスペースへ
			$custom_css = preg_replace('/[\n\r\t]/', '', $custom_css);
			// 複数スペースを一つへ
			$custom_css = preg_replace('/\s(?=\s)/', '', $custom_css);
			wp_add_inline_style( 'lightning-design-style', $custom_css );
	}


} // class VK_Widget_Pr_Content extends WP_Widget {

if ( ! function_exists('vk_widget_register_pr_content') ){
	add_action('widgets_init', 'vk_widget_register_pr_content');
	function vk_widget_register_pr_content(){
		return register_widget("VK_Widget_Pr_Content");
	}
}
