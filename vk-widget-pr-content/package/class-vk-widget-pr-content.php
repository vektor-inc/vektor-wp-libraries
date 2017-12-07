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
  }

  /**
    * 入力された値とデフォルト値を結合するメソッド
  **/
  public static function options( $instance = array() )
  {
    $defaults = array(
      'pr_content_title'       => '',
      'pr_content_text'        => '',
      'pr_content_media_image' => null,
      'pr_content_media_alt'   => null,
      // 'pr_content_label' => __( 'PR Content area1 title', $pr_content_textdomain ),
      'pr_content_btn_text'    => '',
      'pr_content_btn_url'     => '',
      'pr_content_btn_blank'   => false,
      'pr_content_btn_color'   => null,
      'pr_content_bg_color'    => null,
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
    echo $args['before_widget'];
    if ( ! empty( $options['pr_content_bg_color'] ) ) {
      $bg_color = sanitize_hex_color( $options['pr_content_bg_color'] );
      echo '<div class="pr-content" style="background-color:'.$bg_color.';">';
    }
    echo '<div class="container">';
    ?>
    <div class="row">
    <div class="col-sm-6"><?php
    // media img
    // 画像IDから画像のURLを取得
    if ( ! empty( $options['pr_content_media_image'] ) ) {
      $image = wp_get_attachment_image_src( $options['pr_content_media_image'], 'large' );
      $image = $image[0];
    } else {
      $image = null;
    }
    // 画像が登録されている場合
    if ( ! empty( $image ) ) {
      echo  '<img class="pr_content_media_imgage" src="'.esc_url( $image ).'" alt="'.esc_attr( $options['pr_content_media_alt'] ).'" style="border: 1px solid #ccc;" class="card-img-top" />';
    }
    ?></div><!-- .col-sm-6 -->
    <div class="col-sm-6"><?php
    // title
    if ( $options['pr_content_title'] ) {
      echo '<h3 class="pr-content-title">'.esc_html( $options['pr_content_title'] ).'</h3>';
    }
    // text
    echo '<p>'.wp_kses_post( $options['pr_content_text'] ).'</p>';
    // link btn
    if ( ! empty ( $options['pr_content_btn_text'] ) && ! empty ( $options['pr_content_btn_url'] )) {
      $more_link_html = '<div>';
      if( ! empty( $options['pr_content_btn_blank'] ) ) {
        $blank = 'target="_blank"';
      } else {
        $blank = '';
      }
      $more_link_html .= '<a href="'.esc_url( $options['pr_content_btn_url'] ).'" class="btn btn-primary btn-block"'. $blank.'>'.wp_kses_post( $options['pr_content_btn_text'] ).'</a>';
      $more_link_html .= '</div>';
    } else {
      $more_link_html = '';
    }
    echo $more_link_html;
    ?></div><!-- .col-sm-6 -->
    </div><!-- .row -->
    </div><!-- .container -->

		<style>
      .pr-content {
        margin: 0 calc(50% - 50vw) 3em;
        padding: 3em calc(50vw - 50%);
      }

		  .pr-content-title {
		    background-color: transparent;
		    font-weight: bold;
		    padding: 0;
		  }

      h3.pr-content-title {
        border-bottom: none;
        box-shadow: none;
      }

      h3.pr-content-title:after {
        content: "";
        line-height: 0;
        display: block;
        overflow: hidden;
        position: absolute;
        bottom: -1px;
        width: 0;
        border-bottom: none;
      }
		</style>

    <?php
    echo '</div>'; // .pr-content
		echo $args['after_widget'];
  }

	/*-------------------------------------------*/
	/*  update
	/*-------------------------------------------*/

	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance[ 'pr_content_title' ] = wp_kses_post( $new_instance[ 'pr_content_title' ] );
		$instance[ 'pr_content_text' ] = wp_kses_post( $new_instance[ 'pr_content_text' ] );
		$instance[ 'pr_content_media_image' ] = wp_kses_post( $new_instance[ 'pr_content_media_image' ] );
		$instance[ 'pr_content_media_alt' ] = esc_attr( $new_instance[ 'pr_content_media_alt' ] );
		$instance[ 'pr_content_btn_text' ] = wp_kses_post( $new_instance[ 'pr_content_btn_text' ] );
		$instance[ 'pr_content_btn_url' ] = esc_url( $new_instance[ 'pr_content_btn_url' ] );
		$instance[ 'pr_content_btn_blank' ] = ( isset( $new_instance[ 'pr_content_btn_blank' ] ) && $new_instance[ 'pr_content_btn_blank' ] ) ? true : false;
		$instance[ 'pr_content_btn_color' ] = ( isset( $new_instance[ 'pr_content_btn_blank' ] ) && $new_instance[ 'pr_content_btn_color' ] ) ? sanitize_hex_color( $new_instance[ 'pr_content_btn_color' ]) : false ;
		$instance[ 'pr_content_bg_color' ] = sanitize_hex_color( $new_instance[ 'pr_content_bg_color' ] );
		return $instance;
	}


  function form( $instance )
  {
      global $pr_content_textdomain;
      $options = self::options( $instance );
      ?>
      <br>
      <label for="<?php echo $this->get_field_id('pr_content_title'); ?>" ><?php _e('Title:', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id('pr_content_title'); ?>" name="<?php echo $this->get_field_name('pr_content_title') ?>" style="width:100%; margin-bottom: 1.5em;" value="<?php echo esc_attr( $options['pr_content_title'] ); ?>"></input>

      <label for="<?php echo $this->get_field_id('pr_content_text'); ?>" ><?php _e('Text:', $pr_content_textdomain); ?></label>
      <textarea id="<?php echo $this->get_field_id('pr_content_text'); ?>" name="<?php echo $this->get_field_name('pr_content_text') ?>" style="width:100%; margin-bottom: 1.5em;"><?php echo esc_textarea( $options['pr_content_text'] ); ?></textarea>

			<?php
      $image = null;
      // ちゃんと数字が入っているかどうか？
      if ( is_numeric( $options['pr_content_media_image'] ) ) {
        // 数字だったら、その数字の画像を large サイズで取得
          $image = wp_get_attachment_image_src( $options['pr_content_media_image'], 'large' );
      }
      ?>
      <div class="pr_content_media_area" style="padding: 0.5em 0 3.0em;">
      <div class="_display" style="height:auto">
          <?php if ( $image ): ?>
              <img src="<?php echo esc_url( $image[0] ); ?>" style="width:100%; height:auto; border: 1px solid #ccc; margin: 0 0 15px;" />
          <?php endif; ?>

      </div>
      <button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:0;" onclick="javascript:pr_content_media_image_addiditional(this);return false;"><?php _e('Set image', $pr_content_textdomain ); ?></button>
      <button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:4px 0;" onclick="javascript:vk_title_bg_image_delete(this);return false;"><?php _e('Delete image', $pr_content_textdomain ); ?></button>
      <div class="_form" style="line-height: 2em">
          <input type="hidden" class="__id" name="<?php echo $this->get_field_name( 'pr_content_media_image' ); ?>" value="<?php echo esc_attr( $options['pr_content_media_image'] ); ?>" />
      </div>
      </div>
			<script type="text/javascript">
      // 画像登録処理
      if ( pr_content_media_image_addiditional == undefined ){
      var pr_content_media_image_addiditional = function(e){
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
      if ( vk_title_bg_image_delete == undefined ){
      var vk_title_bg_image_delete = function(e){
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
      <label for="<?php echo $this->get_field_id( 'pr_content_btn_url' );  ?>"><?php _e( 'Destination URL:', $pr_content_textdomain ); ?></label><br/>
      <input type="text" id="<?php echo $this->get_field_id( 'pr_content_btn_url' ); ?>" name="<?php echo $this->get_field_name( 'pr_content_btn_url' ); ?>" value="<?php echo esc_attr( $options['pr_content_btn_url'] ); ?>" style="margin-bottom: 0.5em;" />
      <br /><br />
      <label for="<?php echo $this->get_field_id( 'pr_content_btn_text' );  ?>"><?php _e( 'Notation text:', $pr_content_textdomain ); ?></label><br/>
      <input type="text" placeholder="詳細を見る ≫" id="<?php echo $this->get_field_id( 'pr_content_btn_text' ); ?>" name="<?php echo $this->get_field_name( 'pr_content_btn_text' ); ?>" value="<?php echo esc_attr( $options['pr_content_btn_text'] ); ?>" style="margin-bottom: 1.5em;" />
      <br>

      <?php // target blank ?>
      <input type="checkbox" id="<?php echo $this->get_field_id('pr_content_btn_blank'); ?>" name="<?php echo $this->get_field_name('pr_content_btn_blank'); ?>" value="true" <?php if($options['pr_content_btn_blank']) echo 'checked'; ?> />
      <label for="<?php echo $this->get_field_id('pr_content_btn_blank'); ?>" ><?php _e('Open with new tab', $pr_content_textdomain); ?></label>
      <br><br>

      <?php // bg color ?>
      <label for="<?php echo $this->get_field_id( 'pr_content_bg_color' ); ?>" class="color_picker_wrap"><?php _e( 'Background color:', $pr_content_textdomain); ?></label>
      <input type="text" id="<?php echo $this->get_field_id( 'pr_content_bg_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'pr_content_bg_color'); ?>" value="<?php if($options['pr_content_bg_color']) echo esc_attr( $options['pr_content_bg_color']); ?>" />
      <br><br>
  <?php
  }

} // class VK_Widget_Pr_Content extends WP_Widget {

if ( ! function_exists('vk_widget_register_pr_content') ){
	add_action('widgets_init', 'vk_widget_register_pr_content');
	function vk_widget_register_pr_content(){
		return register_widget("VK_Widget_Pr_Content");
	}
}
