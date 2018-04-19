<?php

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

class VK_Widget_Pr_Content extends WP_Widget {


	/*-------------------------------------------*/
	/*  Widgetを登録する
	/*-------------------------------------------*/

	public static $version = '0.0.0';

	function __construct() {
		global $pr_content_textdomain;
		if ( function_exists( 'veu_get_short_name' ) ) {
			$prefix = veu_get_short_name();
		} else {
			$prefix = 'VK';
		}
		$widget_name = $prefix . '_' . __( 'PR Content', $pr_content_textdomain );
		parent::__construct(
			'VK_Widget_Pr_Content', //ID
			$widget_name, //widget_name
			array( 'description' => __( 'This widget can be used when 1 column display is selected.', $pr_content_textdomain ) ) //Widgetの説明
		);
		add_action( 'wp_head', array( $this, 'print_css' ), 2 );
		add_action( 'admin_print_footer_scripts', array( $this, 'print_admin_js' ), 2 );

		/*
		 PR Content ウィジェット用のCSSを デザインスキンなどで結合して出力し、
		 このディレクトリ内のcssの読み込みを停止する場合、
		 vk-widget-pr-config.php で $pr_content_dont_load_css に対して true を指定する
		 */
		global $pr_content_dont_load_css;
		if ( ! $pr_content_dont_load_css ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
		}
	}

	public static function add_script() {
		wp_register_script( 'vk-parallax-js', plugin_dir_url( __FILE__ ) . 'js/vk-prlx.min.js', array( 'jquery' ), self::$version );
		wp_enqueue_script( 'vk-parallax-js' );
		wp_enqueue_style( 'vk-widget-pr-content-style', plugin_dir_url( __FILE__ ) . 'css/vk-widget-pr-content.css', array(), self::$version, 'all' );
	}

	public static function print_admin_js() {
		wp_enqueue_script( 'admin-widget-color-js', plugin_dir_url( __FILE__ ) . 'js/admin-widget-color.min.js', array( 'jquery' ), date( 'His' ) );
	}

	/**
	 * 色を比率で明るくしたり暗くする
	 * @param  [type]  $color       #あり16進数
	 * @param  integer $change_rate 1 が 100%
	 * @return [type]               string （#あり16進数）
	 */
	function auto_color_mod( $color, $change_rate = 1 ) {

		$color = preg_replace( '/#/', '', $color );
		// 16進数を10進数に変換
		$array_btn_color_red   = hexdec( substr( $color, 0, 2 ) );
		$array_btn_color_green = hexdec( substr( $color, 2, 2 ) );
		$array_btn_color_blue  = hexdec( substr( $color, 4, 2 ) );

		// 10進数の状態で50%暗くして dechex で 16進数に戻す
		$new_color  = '#';
		$new_color .= dechex( $array_btn_color_red * $change_rate );
		$new_color .= dechex( $array_btn_color_green * $change_rate );
		$new_color .= dechex( $array_btn_color_blue * $change_rate );

		return $new_color;
	}

	/*-------------------------------------------*/
	/*  入力された値とデフォルト値を結合するメソッド
	/*-------------------------------------------*/

	function options( $instance = array() ) {
		$defaults = array(
			'title'              => '',
			'title_color'        => null,
			'text'               => '',
			'text_color'         => null,
			'media_image'        => null,
			'media_border_color' => null,
			'btn_text'           => '',
			'btn_url'            => '',
			'btn_blank'          => false,
			'btn_color'          => null,
			'btn_type'           => null,
			'bg_color'           => null,
			'bg_image'           => null,
			'bg_cover_color'     => null,
			'bg_cover_depth'     => '0',
			'margin_top'         => '0',
			'margin_bottom'      => '0',
			'layout_type'        => null,
		);
		return wp_parse_args( (array) $instance, $defaults );
	}


	/*-------------------------------------------*/
	/*  表側の Widget を出力する（表示用コード）
	/*-------------------------------------------*/

	function widget( $args, $instance ) {
		// 入力された値とデフォルトで指定した値をマージして$options にいれる
		$options = self::options( $instance );

		echo '<style type="text/css">';
		echo '.mainSection #' . $args['widget_id'] . '.widget_vk_widget_pr_content { margin-top:' . $options['margin_top'] . '; margin-bottom:' . $options['margin_bottom'] . ';}';
		echo '</style>';

		echo $args['before_widget'];

		$pr_content_style = '';

		if ( ( ! empty( $options['bg_color'] ) ) && ( empty( $options['bg_image'] ) ) ) {

			/*  背景 色指定あり && 背景 画像指定なし
			/*-------------------------------------------*/

			$pr_content_style = ' style="background-color:' . esc_attr( $options['bg_color'] ) . '"';

		} elseif ( ! empty( $options['bg_image'] ) ) {

			/*  背景 画像指定あり
			/*-------------------------------------------*/

			// 画像IDから画像のURLを取得
			$bg_image = wp_get_attachment_image_src( $instance['bg_image'], 'full' );
			$bg_image = esc_url( $bg_image[0] );
			// 画像に被せる色の処理
			// カラーコードの16進数を10進数に変換する
			// RGB数値に変換するカラーコード
			if ( ! empty( $options['bg_cover_color'] ) ) {
				$bg_cover_color = $options['bg_cover_color'];
				//「#******」のような形でカラーコードがわたってきた場合「#」を削除する
				$bg_cover_color = preg_replace( '/#/', '', $bg_cover_color );
				//「******」という形になっているはずなので、2つずつ「**」に区切る
				// そしてhexdec関数で変換して配列に格納する
				$bg_cover_color_red   = hexdec( substr( $bg_cover_color, 0, 2 ) );
				$bg_cover_color_green = hexdec( substr( $bg_cover_color, 2, 2 ) );
				$bg_cover_color_blue  = hexdec( substr( $bg_cover_color, 4, 2 ) );

				// 被せる色の濃さ（0以外）が入力されていたら値を小数に変換して代入
				if ( ! empty( $options['bg_cover_depth'] ) && $options['bg_cover_depth'] !== 0 ) {
					$bg_cover_depth = ( $options['bg_cover_depth'] ) / 100;
				} else {
					$bg_cover_depth = $options['bg_cover_depth'];
				}

				// background: linear-gradient で画像の上に $bg_cover_color を透過（$bg_cover_depth）させて被せる
				// →１個めの rgba() と２個目の rgba() の値を別々で設定すればグラデーションもできる
				$pr_content_style_bg = 'background: linear-gradient( rgba( ' . $bg_cover_color_red . ', ' . $bg_cover_color_green . ', ' . $bg_cover_color_blue . ', ' . $bg_cover_depth . '), rgba(' . $bg_cover_color_red . ', ' . $bg_cover_color_green . ', ' . $bg_cover_color_blue . ', ' . $bg_cover_depth . ') ), url(\'' . $bg_image . '\') no-repeat center center; ';

				$pr_content_style = ' style="' . $pr_content_style_bg . '"';

				// 画像に色を被せない場合
			} else {
				$pr_content_style = ' style="background:
				url( \'' . $bg_image . '\' ) no-repeat center center; background-size: cover;"';
			}
		}
		// $pr_content_style = '';
		echo '<div class="pr-content vk-prlx"' . $pr_content_style . '>';
		echo '<div class="container">';

		// レイアウトタイプを選択
		if ( $options['layout_type'] === 'left' ) {
			$layout_type = 'left';
		} else {
			$layout_type = 'right';
		}
		?>
	  <div class="row <?php echo $layout_type; ?>">
		<?php if ( $options['media_image'] ) { ?>
		<div class="col-sm-6 pr-content-col-img">
			<?php
			// media img
			// 画像IDから画像のURLを取得
			if ( ! empty( $options['media_image'] ) && is_numeric( $options['media_image'] ) ) {
				$media_border_color       = $options['media_border_color'];
				$media_border_color_style = ( isset( $media_border_color ) && $media_border_color ) ? 'border: 1px solid ' . $media_border_color . ';' : '';
				$attr                     = array(
					'class' => 'pr_content_media_imgage', //任意の class名を追記する
					'style' => $media_border_color_style, // スタイルを追加
				);
				echo wp_get_attachment_image( $options['media_image'], 'large', false, $attr );
			}
			?>
		</div><!-- .col-sm-6 -->
		<?php } ?>
		  <div class="col-sm-6 pr-content-col-text">
			<?php
			// title
			if ( $options['title'] ) {
				if ( $options['title_color'] ) {
					$color = ' style="color:' . esc_attr( $options['title_color'] ) . '"';
				} else {
					$color = '';
				}
				echo '<h3 class="pr-content-title"' . $color . '>' . esc_html( $options['title'] ) . '</h3>';
			}
			// text
			if ( $options['text'] ) {
				if ( $options['text_color'] ) {
					$color = ' style="color:' . esc_attr( $options['text_color'] ) . '"';
				} else {
					$color = '';
				}
				echo '<p' . $color . '>' . wp_kses_post( $options['text'] ) . '</p>';
			}

			/*  ボタンタイプを選択
			/*-------------------------------------------*/
			if ( empty( $options['btn_type'] ) ) {
				$options['btn_type'] = 'full';
			}

			/*  ボタンテキストカラーの設定
			/*-------------------------------------------*/
			// ゴーストボタンの時
			if ( $options['btn_type'] === 'ghost' ) {
				// ボタンカラーがあればボタンカラーを適用
				if ( isset( $options['btn_color'] ) && $options['btn_color'] ) {
					$btn_text_style = 'color:' . $options['btn_color'] . ';';
				} else {
					$btn_text_style = 'color:#fff;';
				}
				// 塗りボタンの時
			} elseif ( $options['btn_type'] === 'full' ) {
				// ボタンの初期状態の文字色が白なので指定する必要がない
				$btn_text_style = '';
			}

			/*  ボタン bg color の設定
			/*-------------------------------------------*/
			if ( $options['btn_type'] === 'ghost' ) {
				// 初期状態だと背景色が指定されているので透過にする
				$btn_bg_style = 'background: transparent; transition: .3s;';

			} elseif ( $options['btn_type'] === 'full' ) {

				// 色指定がない時
				if ( empty( $options['btn_color'] ) ) {
					// デフォルトでテーマなどから出力されるので指定しない
					$btn_bg_style = '';
					// 色指定がある時
				} elseif ( ! empty( $options['btn_color'] ) ) {
					// ボタンカラーが設定されている時
					$btn_bg_style = 'background-color:' . $options['btn_color'] . ';';
				}
			}

			/*  ボタン border の設定
			/*-------------------------------------------*/

			if ( $options['btn_type'] === 'ghost' ) {

				if ( empty( $options['btn_color'] ) ) {
					// 線の色指定がなければ白
					$btn_border_style = 'border-color:#ffffffcc;';
				} else {
					// 色指定があればその色を適用
					$btn_border_style = 'border-color:' . $options['btn_color'] . '99;';
				}
			} elseif ( $options['btn_type'] === 'full' ) {
				if ( empty( $options['btn_color'] ) ) {
					// 線の色指定がなければ白
					$btn_border_style = '';
				} else {
					// 色指定があれば濃い色を計算
					// 塗りボタンの場合は濃い色にしたいので指定色から0.5暗い色
					$color_dark       = self::auto_color_mod( $options['btn_color'], 0.8 );
					$btn_border_style = 'border-color:' . $color_dark . ';';
				}
			}

			/*  ボタンのCSS
			/*-------------------------------------------*/
			$btn_style = $btn_text_style . $btn_bg_style . $btn_border_style;
			if ( $btn_style ) {
				$btn_style = ' style="' . esc_attr( $btn_style ) . '"';
			}

			/*  ボタンのclass
			/*-------------------------------------------*/
			$btn_style = $btn_text_style . $btn_bg_style . $btn_border_style;
			if ( $btn_style ) {
				$btn_style = ' style="' . esc_attr( $btn_style ) . '"';
			}

			/*  ボタン出力
			/*-------------------------------------------*/
			if ( $options['btn_text'] && $options['btn_url'] ) {
				$more_link_html = '<div class="pr-content-btn">';
				// target_blank の指定
				if ( ! empty( $options['btn_blank'] ) ) {
					$blank = 'target="_blank"';
				} else {
					$blank = '';
				}

				// ボタンタイプによってクラスをつける
				if ( empty( $options['btn_type'] ) ) {
					$btn_class = ' btn-primary';
				} elseif ( isset( $options['btn_type'] ) && $options['btn_type'] === 'full' ) {
					$btn_class = ' btn-primary';
				} elseif ( isset( $options['btn_type'] ) && $options['btn_type'] === 'ghost' ) {
					// ゴーストの時
					$btn_class = 'btn-ghost';
				}

				echo '<style>';
				echo  '#' . $args['widget_id'] . ' .btn-ghost:hover{ background-color:' . $btn_color . ';border:' . $btn_color . '}';
				echo '</style>';

				$more_link_html .= '<a href="' . esc_url( $options['btn_url'] ) . '" class="btn btn-block btn-lg' . $btn_class . '" ' . $blank . $btn_style . '">' . wp_kses_post( $options['btn_text'] ) . '</a>';
				$more_link_html .= '</div>';
			} else {
				$more_link_html = '';
			}
			echo $more_link_html;
			?>

		  </div><!-- .col-sm-6 -->
		  </div><!-- .row -->
		  </div><!-- .container -->
			<?php
			echo '</div>'; // .pr-content
			echo $args['after_widget'];
	}


	/*-------------------------------------------*/
	/*  update
	/*-------------------------------------------*/

	function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['title']              = wp_kses_post( $new_instance['title'] );
		$instance['title_color']        = sanitize_hex_color( $new_instance['title_color'] );
		$instance['text']               = wp_kses_post( $new_instance['text'] );
		$instance['text_color']         = sanitize_hex_color( $new_instance['text_color'] );
		$instance['media_image']        = wp_kses_post( $new_instance['media_image'] );
		$instance['media_border_color'] = wp_kses_post( $new_instance['media_border_color'] );
		$instance['btn_text']           = wp_kses_post( $new_instance['btn_text'] );
		$instance['btn_url']            = esc_url( $new_instance['btn_url'] );
		$instance['btn_blank']          = ( isset( $new_instance['btn_blank'] ) && $new_instance['btn_blank'] ) ? true : false;
		$instance['btn_color']          = ( isset( $new_instance['btn_color'] ) ) ? sanitize_hex_color( $new_instance['btn_color'] ) : false;
		$instance['btn_type']           = esc_attr( $new_instance['btn_type'] );
		$instance['bg_color']           = ( isset( $new_instance['bg_color'] ) ) ? sanitize_hex_color( $new_instance['bg_color'] ) : false;
		$instance['bg_image']           = wp_kses_post( $new_instance['bg_image'] );
		$instance['bg_cover_color']     = ( isset( $new_instance['bg_cover_color'] ) ) ? sanitize_hex_color( $new_instance['bg_cover_color'] ) : false;
		$instance['bg_cover_depth']     = esc_attr( mb_convert_kana( $new_instance['bg_cover_depth'], 'a' ) );
		$instance['margin_top']         = wp_kses_post( mb_convert_kana( $new_instance['margin_top'], 'a' ) );
		$instance['margin_bottom']      = wp_kses_post( mb_convert_kana( $new_instance['margin_bottom'], 'a' ) );
		$instance['layout_type']        = esc_attr( $new_instance['layout_type'] );
		return $instance;
	}


	/*-------------------------------------------*/
	/*  form
	/*-------------------------------------------*/

	function form( $instance ) {
		global $pr_content_textdomain;
		$options = self::options( $instance );
		?>
		<?php // title ?>
		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Title', $pr_content_textdomain ); ?></h2>
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>-title" name="<?php echo $this->get_field_name( 'title' ); ?>" class="admin_widget_input" value="<?php echo esc_attr( $options['title'] ); ?>">

		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'title_color' ); ?>"><?php _e( 'Text color', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'title_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'title_color' ); ?>" value="<?php echo esc_attr( $options['title_color'] ); ?>" />
		</p>
		</div><!-- [ /.admin_widget_section ] -->

		<?php // text ?>
		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Text', $pr_content_textdomain ); ?></h2>
		<textarea id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" class="admin_widget_input"><?php echo esc_textarea( $options['text'] ); ?></textarea>

		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'text_color' ); ?>"><?php _e( 'Text color', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'text_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'text_color' ); ?>" value="<?php echo esc_attr( $options['text_color'] ); ?>" />
		</p>
		</div>

		<?php // Image ?>
		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Image', $pr_content_textdomain ); ?></h2>
		<?php
		// media_image
		$image = null;
		// ちゃんと数字が入っているかどうか？
		if ( is_numeric( $options['media_image'] ) ) {
			// 数字だったら、その数字の画像を large サイズで取得
			$image = wp_get_attachment_image_src( $options['media_image'], 'large' );
		}
		?>
		<div class="pr_content_media_area">
		<div class="_display" style="height:auto">
			<?php if ( $image ) : ?>
			  <img src="<?php echo esc_url( $image[0] ); ?>" style="width:100%; height:auto; border: 1px solid #ccc; margin: 0 0 15px; box-sizing: border-box;" />
			<?php endif; ?>
		</div>
		<button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:0;" onclick="javascript:media_image_addiditional( this );return false;"><?php _e( 'Set image', $pr_content_textdomain ); ?></button>
		<button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:4px 0;" onclick="javascript:vk_title_bg_image_delete( this );return false;"><?php _e( 'Delete image', $pr_content_textdomain ); ?></button>
		<div class="_form" style="line-height: 2em">
		  <input type="hidden" class="__id" name="<?php echo $this->get_field_name( 'media_image' ); ?>" value="<?php echo esc_attr( $options['media_image'] ); ?>" />
		</div>

		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'media_border_color' ); ?>"><?php _e( 'Media border color', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'media_border_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'media_border_color' ); ?>" value="<?php echo esc_attr( $options['media_border_color'] ); ?>" />
		</p>

		</div>
		<script type="text/javascript">
		// 画像登録処理
		if (media_image_addiditional == undefined){
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
		if (media_image_delete == undefined){
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

		<!-- [ 画像の枠線のカラーピッカーはこのあたりに入る想定 ] -->

		</div><!-- [ /.admin_widget_section ] -->

		<?php // Link button ?>
		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Link button', $pr_content_textdomain ); ?></h2>
		<p>
		<label for="<?php echo $this->get_field_id( 'btn_url' ); ?>"><?php _e( 'Link URL', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'btn_url' ); ?>" name="<?php echo $this->get_field_name( 'btn_url' ); ?>" value="<?php echo esc_attr( $options['btn_url'] ); ?>" class="admin_widget_input" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'btn_text' ); ?>"><?php _e( 'Notation text', $pr_content_textdomain ); ?> : </label>
		<input type="text" placeholder="詳細を見る ≫" id="<?php echo $this->get_field_id( 'btn_text' ); ?>" name="<?php echo $this->get_field_name( 'btn_text' ); ?>" value="<?php echo esc_attr( $options['btn_text'] ); ?>" class="admin_widget_input" /><br>
		<?php _e( 'Ex', $pr_content_textdomain ); ?> ) <?php _e( 'Read more  ≫', $pr_content_textdomain ); ?>
		</p>

		<?php // target blank ?>
		<p>
		<input type="checkbox" id="<?php echo $this->get_field_id( 'btn_blank' ); ?>" name="<?php echo $this->get_field_name( 'btn_blank' ); ?>" value="true"
		<?php
		if ( $options['btn_blank'] ) {
			echo 'checked';
		}
		?>
		/>
		<label for="<?php echo $this->get_field_id( 'btn_blank' ); ?>" ><?php _e( 'Open with new tab', $pr_content_textdomain ); ?></label>
		</p>

		<?php // btn_type ?>
		<p><?php _e( 'Select button type:', $pr_content_textdomain ); ?><br>
		<?php
		$checked = '';
		if (
			// $instance[ 'layout_type' ] が定義されていて、値がleftの場合
			( isset( $options['btn_type'] ) && $options['btn_type'] === 'full' ) ||
			// $options[ 'btn_type' ] が定義されていない場合
			empty( $options['btn_type'] )
			 ) {
				// ' checked'を指定する
				$checked = ' checked';
		}
		?>
		<label>
			<input type="radio" name="<?php echo $this->get_field_name( 'btn_type' ); ?>" value="full" <?php echo $checked; ?> />
			<?php _e( 'Fill button', $pr_content_textdomain ); ?>
		</label>
		<br>
		<?php $checked = ( isset( $options['btn_type'] ) && $options['btn_type'] === 'ghost' ) ? ' checked' : ''; ?>
		<label>
			<input type="radio" name="<?php echo $this->get_field_name( 'btn_type' ); ?>" value="ghost" <?php echo $checked; ?> />
			<?php _e( 'Ghost button', $pr_content_textdomain ); ?>
		</label>
		</p>

		<?php // Button color ?>
		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'btn_color' ); ?>"><?php _e( 'Button color', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'btn_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'btn_color' ); ?>" value="<?php echo esc_attr( $options['btn_color'] ); ?>" />
		</p>

		</div><!-- [ /.admin_widget_section ] -->

		<?php // Background ?>
		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Background', $pr_content_textdomain ); ?></h2>
		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'bg_color' ); ?>"><?php _e( 'Background color:', $pr_content_textdomain ); ?></label>
		<input type="text" id="<?php echo $this->get_field_id( 'bg_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'bg_color' ); ?>" value="<?php echo esc_attr( $options['bg_color'] ); ?>" />
		</p>

		<div class="admin_widget_section">
		<?php
		// bg img
		$bg_image = null;
		// ちゃんと数字が入っているかどうか？
		if ( is_numeric( $options['bg_image'] ) ) {
			// 数字だったら、その数字の画像を large サイズで取得
			$bg_image = wp_get_attachment_image_src( $options['bg_image'], 'large' );
		}
		?>
		<label for="<?php echo $this->get_field_id( 'bg_image' ); ?>"><?php _e( 'Background image', $pr_content_textdomain ); ?> : </label>
		<p><?php _e( 'If both the background color and the background image are set, the background image is reflected.', $pr_content_textdomain ); ?></p>
		<div class="_display" style="height:auto">
			<?php if ( $bg_image ) : ?>
			  <img src="<?php echo esc_url( $bg_image[0] ); ?>" style="width:100%; height:auto; border: 1px solid #ccc; box-sizing: border-box;" />
			<?php endif; ?>
		</div>
		<button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:15px 0 0;" onclick="javascript:bg_image_addiditional(this);return false;"><?php _e( 'Set image', $pr_content_textdomain ); ?></button>
		<button class="button button-default button-block" style="display:block;width:100%;text-align: center; margin:4px 0;" onclick="javascript:vk_title_bg_image_delete(this);return false;"><?php _e( 'Delete image', $pr_content_textdomain ); ?></button>
		<div class="_form" style="line-height: 2em">
		  <input type="hidden" class="__id" name="<?php echo $this->get_field_name( 'bg_image' ); ?>" value="<?php echo esc_attr( $options['bg_image'] ); ?>" />
		</div>
		<script type="text/javascript">
		// 画像登録処理
		if (bg_image_addiditional == undefined){
		var bg_image_addiditional = function(ef){
			  // プレビュー画像を表示するdiv
		  var de=jQuery(ef).parent().children("._display");
			  // 画像IDを保存するinputタグ
		  var wx=jQuery(ef).parent().children("._form").children('.__id')[0];
		  var uv=wp.media({library:{type:'image'},multiple:false}).on('select', function(ef){
			  uv.state().get('selection').each(function(fg){
						  de.children().remove();
						  de.append(jQuery('<img style="width:100%;mheight:auto">').attr('src',fg.toJSON().url));
						  jQuery(wx).val(fg.toJSON().id).change();
					  });
		  });
		  uv.open();
		};
		}
		// 背景画像削除処理
		if (bg_image_delete == undefined){
		var bg_image_delete = function(ef){
			  // プレビュー画像を表示するdiv
			  var de=jQuery(ef).parent().children("._display");
			  // 画像IDを保存するinputタグ
			  var wx=jQuery(ef).parent().children("._form").children('.__id')[0];

			  // プレビュー画像のimgタグを削除
			  de.children().remove();
			  // w.attr("value","");
			  jQuery(ef).parent().children("._form").children('.__id').attr("value","").change();
		};
		}
		</script>
		</div><!-- [ /.admin_widget_section ] -->

		<?php // bg cover color ?>
		<p class="color_picker_wrap">
		<label for="<?php echo $this->get_field_id( 'bg_cover_color' ); ?>"><?php _e( 'Cover color', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'bg_cover_color' ); ?>"  class="color_picker" name="<?php echo $this->get_field_name( 'bg_cover_color' ); ?>" value="<?php echo esc_attr( $options['bg_cover_color'] ); ?>" />
		</p>

		<?php // cover color depth ?>
		<label for="<?php echo $this->get_field_id( 'bg_cover_depth' ); ?>" ><?php _e( 'Depth of color to cover', $pr_content_textdomain ); ?> : </label>
		<p><?php _e( 'To cancel the color overlay overlay on the image, enter "0" to this value.', $pr_content_textdomain ); ?></p>
		<input type="text" id="<?php echo $this->get_field_id( 'bg_cover_depth' ); ?>" name="<?php echo $this->get_field_name( 'bg_cover_depth' ); ?>" style="width:50%; margin-bottom: 1.2em;" value="<?php echo esc_attr( $options['bg_cover_depth'] ); ?>" />&nbsp;%
		</div><!-- [ /.admin_widget_section ] -->

		<div class="admin_widget_section">
		<h2 class="admin_widget_h2"><?php _e( 'Layout', $pr_content_textdomain ); ?></h2>

		<?php // layout_type ?>
		<p><?php _e( 'Select layout type:', $pr_content_textdomain ); ?><br>
		<?php
		$checked = '';
		if (
		  // $instance[ 'layout_type' ] が定義されていて、値がleftの場合
		  ( isset( $instance['layout_type'] ) && $instance['layout_type'] === 'left' ) ||
		  // $instance[ 'layout_type' ] が定義されていない場合
		  empty( $instance['layout_type'] )
		   ) {
			  // ' checked'を指定する
			  $checked = ' checked';
		}
		?>
		<label>
			<input type="radio" name="<?php echo $this->get_field_name( 'layout_type' ); ?>" value="left" <?php echo $checked; ?> />
			<?php _e( 'Put the image to the left', $pr_content_textdomain ); ?>
		</label>
		<br>
		<?php $checked = ( isset( $instance['layout_type'] ) && $instance['layout_type'] === 'right' ) ? ' checked' : ''; ?>
		<label>
			<input type="radio" name="<?php echo $this->get_field_name( 'layout_type' ); ?>" value="right" <?php echo $checked; ?> />
			<?php _e( 'Put the image to the right', $pr_content_textdomain ); ?>
		</label>
		</p>
		
		<?php // margin_top . margin_bottom ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'margin_top' ); ?>" ><?php _e( 'Margin-top', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'margin_top' ); ?>-margin_top" name="<?php echo $this->get_field_name( 'margin_top' ); ?>" class="admin_widget_input" value="<?php echo esc_attr( $options['margin_top'] ); ?>" />
		<?php _e( 'Ex', $pr_content_textdomain ); ?> ) 30px
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'margin_bottom' ); ?>" ><?php _e( 'Margin-bottom', $pr_content_textdomain ); ?> : </label>
		<input type="text" id="<?php echo $this->get_field_id( 'margin_bottom' ); ?>-margin_bottom" name="<?php echo $this->get_field_name( 'margin_bottom' ); ?>" class="admin_widget_input" value="<?php echo esc_attr( $options['margin_bottom'] ); ?>" />
		<?php _e( 'Ex', $pr_content_textdomain ); ?> ) 30px
		</p>
		</div><!-- [ /.admin_widget_section ] -->

	<?php
	} // function form( $instance )


	/*-------------------------------------------*/
	/*  Position Change
	/*-------------------------------------------*/

	function print_css() {
			$custom_css = '';
			// 両サイドのスペースを消す
			$custom_css = trim( $custom_css );
			// 改行、タブをスペースへ
			$custom_css = preg_replace( '/[\n\r\t]/', '', $custom_css );
			// 複数スペースを一つへ
			$custom_css = preg_replace( '/\s(?=\s)/', '', $custom_css );
			wp_add_inline_style( 'lightning-design-style', $custom_css );
	}

} // class VK_Widget_Pr_Content extends WP_Widget {

if ( ! function_exists( 'vk_widget_register_pr_content' ) ) {
	add_action( 'widgets_init', 'vk_widget_register_pr_content' );
	function vk_widget_register_pr_content() {
		return register_widget( 'VK_Widget_Pr_Content' );
	}
}
