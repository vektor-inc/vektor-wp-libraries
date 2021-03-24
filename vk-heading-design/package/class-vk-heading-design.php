<?php
/**
 * VK Heqading Design
 *
 * @package VK Heqading Design
 */

/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。
修正の際は上記リポジトリのデータを修正してください。
編集権限を持っていない方で何か修正要望などありましたら
各プラグインのリポジトリにプルリクエストで結構です。
*/

if ( ! class_exists( 'VK_Headding_Design' ) ) {

	/**
	 * VK Heqading Design
	 */
	class VK_Headding_Design {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'customize_register', array( __CLASS__, 'customize_register' ) );
			add_action( 'wp_head', array( __CLASS__, 'print_headding_front_css' ), 4 );
			add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'print_headding_editor_css' ) );
		}

		/**
		 * いろいろな見出しのデザインの情報を取得する関数
		 *
		 * @param string $color_key Color Key.
		 */
		public static function get_headding_style_array( $color_key = '#c00' ) {
			// ※ margin:unset; にすると編集画面で左ベタ付きになるので注意
			$reset                        = '
				background-color:unset;
				position: relative;
				border:none;
				padding:unset;
				margin-left: auto;
				margin-right: auto;
				border-radius:unset;
				outline: unset;
				outline-offset: unset;
				box-shadow: unset;
				content:none;
				overflow: unset;
			';
			$brackets_before_after_common = '
				content:"";
				position: absolute;
				top: 0;
				width: 12px;
				height: 100%;
				display: inline-block;
				margin-left:0;
			';
			$styles                       = array(
				'plain'                           => array(
					'label'  => __( 'Plain', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . 'text-align:left;',
					'before' => $reset,
					'after'  => $reset,
				),
				'plain_center'                    => array(
					'label'  => __( 'Plain ( Align center )', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . 'text-align:center;',
					'before' => $reset,
					'after'  => $reset,
				),

				'speech_balloon_fill'             => array(
					'label'  => __( 'Speech balloon fill', 'heading_design_textdomain' ),
					'inner'  => 'color:#fff;',
					'normal' => $reset . '
						background-color:' . $color_key . ';
						position: relative;
						padding: 0.6em 0.8em 0.5em;
						margin-bottom:1.2em;
						color:#fff;
						border-radius:4px;
						text-align:left;
						',
					'before' => $reset . '
						content: "";
						position: absolute;
						top: auto;
						left: 40px;
						bottom: -20px;
						width: auto;
						margin-left: -10px;
						border: 10px solid transparent;
						border-top: 10px solid ' . $color_key . ';
						z-index: 2;
						height: auto;
						background-color: transparent !important;
						',
					'after'  => $reset,
				),
				'background_fill'                 => array(
					'label'  => __( 'Background fill', 'heading_design_textdomain' ),
					'inner'  => 'color:#fff;',
					'normal' => $reset . '
						background-color:' . $color_key . ';
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						color:#fff;
						border-radius:4px;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'background_fill_stitch'          => array(
					'label'  => __( 'Background fill stitch', 'heading_design_textdomain' ),
					'inner'  => 'color:#fff;',
					'normal' => $reset . '
						background-color:' . $color_key . ';
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						color:#fff;
						border-radius:4px;
						outline: dashed 1px #fff;
						outline-offset: -4px;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'background_fill_lightgray'       => array(
					'label'  => __( 'Background fill lightgray', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color: #333;
						background-color: #efefef;
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						border-radius: 4px;
						',
					'before' => $reset,
					'after'  => $reset,
				),

				'topborder_background_fill_none'  => array(
					'label'  => __( 'Top border keycolor background fill none', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color: #333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-top: 2px solid  ' . $color_key . ';
						border-bottom: 1px solid #ccc;
						',
					'before' => $reset,
					'after'  => $reset,
				),

				'topborder_background_fill_black' => array(
					'label'  => __( 'Top border keycolor background fill black', 'heading_design_textdomain' ),
					'inner'  => 'color:#fff;',
					'normal' => $reset . '
						background-color: #191919;
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						color: #fff;
						border-top: 2px solid  ' . $color_key . ';
						border-bottom: 1px solid #999;
						',
					'before' => $reset,
					'after'  => $reset,
				),

				'double'                          => array(
					'label'  => __( 'Double', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color: #333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-top: double 3px ' . $color_key . ';
						border-bottom: double 3px ' . $color_key . ';
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'double_black'                    => array(
					'label'  => __( 'Double black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-top: double 3px #333;
						border-bottom: double 3px #333;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'double_bottomborder'             => array(
					'label'  => __( 'Double bottom border', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: double 3px ' . $color_key . ';
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'double_bottomborder_black'       => array(
					'label'  => __( 'Double bottom border black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: double 3px #333;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'solid'                           => array(
					'label'  => __( 'Solid', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-top: solid 1px ' . $color_key . ';
						border-bottom: solid 1px ' . $color_key . ';
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'solid_black'                     => array(
					'label'  => __( 'Solid black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-top: solid 1px #333;
						border-bottom: solid 1px #333;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'solid_bottomborder'              => array(
					'label'  => __( 'Solid bottom border', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: solid 1px ' . $color_key . ';
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'solid_bottomborder_black'        => array(
					'label'  => __( 'Solid bottom border black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: solid 1px #333;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'solid_bottomborder_leftkeycolor' => array(
					'label'  => __( 'Solid bottom border left keycolor', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: 1px solid #ccc;
						background-color:transparent;
						text-align:left;
						',
					'before' => $reset,
					'after'  => $reset . '
						content: "";
						line-height: 0;
						display: block;
						overflow: hidden;
						position: absolute;
						left:0;
						bottom: -1px;
						width: 30%;
						border-bottom: 1px solid ' . $color_key . ';
						margin-left: 0;
						height:inherit;
					',
				),
				'dotted_bottomborder_black'       => array(
					'label'  => __( 'Dotted bottom border black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0 0.5em;
						margin-bottom:1.2em;
						border-bottom: 1px dotted #111;
						background-color:transparent;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'both_ends'                       => array(
					'label'  => __( 'Both ends', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						border:none;
						display: flex;
						align-items: center;
						text-align: center;
						margin-bottom:1.2em;
						padding:0;
						',
					'before' => '
						content: "";
						flex-grow: 1;
						border-bottom: 1px solid #333;
						margin-right: 1em;
						top: unset;
						position: unset;
						width: unset;
						border-top: none;
						',
					'after'  => '
						content: "";
						flex-grow: 1;
						border-bottom: 1px solid #333;
						margin-left: 1em;
						bottom: unset;
						position: unset;
						width: unset;
						border-top: none;
					',
				),
				'leftborder'                      => array(
					'label'  => __( 'Left border', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						border-left:solid 2px ' . $color_key . ';
						background-color: #efefef;
						text-align:left;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'leftborder_nobackground'         => array(
					'label'  => __( 'Left border nobackground', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						border:none;
						padding: 0.6em 0.7em 0.5em;
						margin-bottom:1.2em;
						border-left:solid 2px ' . $color_key . ';
						background-color:transparent;
						text-align:left;
						',
					'before' => $reset,
					'after'  => $reset,
				),
				'diagonal_stripe_bottomborder'    => array(
					'label'  => __( 'Diagonal stripe bottom border', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.5em 0 0.7em;
						margin-bottom:1.2em;
						',
					'before' => $reset,
					'after'  => $reset . '
						content:"";
						position: absolute;
						left: 0;
						bottom: 0;
						width: 100%;
						height: 7px;
						background: linear-gradient(
							-45deg,
							rgba(255,255,255,0.1) 25%, ' . $color_key . ' 25%,
							' . $color_key . ' 50%, rgba(255,255,255,0.1) 50%,
							rgba(255,255,255,0.1) 75%, ' . $color_key . ' 75%,
							' . $color_key . '
						);
						background-size: 5px 5px;
					',
				),
				'brackets'                        => array(
					'label'  => __( 'Brackets', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.7em;
						margin-bottom:1.2em;
						text-align: center;
						',
					'before' => $brackets_before_after_common . '
						border-top: solid 1px ' . $color_key . ';
						border-bottom: solid 1px ' . $color_key . ';
						border-left: solid 1px ' . $color_key . ';
						left: 0;
						',
					'after'  => $brackets_before_after_common . '
						border-top: solid 1px ' . $color_key . ';
						border-bottom: solid 1px ' . $color_key . ';
						border-right: solid 1px ' . $color_key . ';
						right: 0;
						left: auto;
					',
				),
				'brackets_black'                  => array(
					'label'  => __( 'Brackets black', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0.7em;
						margin-bottom:1.2em;
						text-align: center;
						',
					'before' => $brackets_before_after_common . '
						border-top: solid 1px #333;
						border-bottom: solid 1px #333;
						border-left: solid 1px #333;
						margin-left:0;
						left: 0;
						',
					'after'  => $brackets_before_after_common . '
						border-top: solid 1px #333;
						border-bottom: solid 1px #333;
						border-right: solid 1px #333;
						right: 0;
						left: auto;
					',
				),
				'small_bottomborder'              => array(
					'label'  => __( 'Small bottom border', 'heading_design_textdomain' ),
					'inner'  => 'color:#333;',
					'normal' => $reset . '
						color:#333;
						padding: 0;
						text-align: center;
						background-color:transparent;
						margin-bottom: 3em;
						',
					'before' => $reset,
					'after'  => $reset . '
						content: "";
						display: inline-block;
						position: absolute;
						left: 50%;
						margin-left: -19px;
						bottom: -24px;
						top: unset;
						width: 38px;
						border-top: solid 2px ' . $color_key . ';
					',
				),
			);
			return apply_filters( 'vk_headding_style_array', $styles );
		}

		/**
		 * Customize Register
		 *
		 * @param object $wp_customize WP_CCustomize.
		 */
		public static function customize_register( $wp_customize ) {

			global $headding_default_options;
			global $headding_selector_array;
			global $headding_customize_section;
			global $headding_theme_options;

			$default_option = call_user_func( $headding_default_options );

			// カスタマイザーに表示されるタイトルなど.
			$wp_customize->add_setting(
				'vk_headding_design',
				array(
					'default' => false,
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'vk_headding_design',
					array(
						'label'            => __( 'Headding Design', 'heading_design_textdomain' ),
						'section'          => $headding_customize_section,
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => __( '※ 配置する場所の背景色などの都合で適切に見えないものがあります。', 'heading_design_textdomain' ),
						'priority'         => 710,

					)
				)
			);

			$choices = array(
				'none' => __( 'No setting', 'heading_design_textdomain' ),
			);

			$styles = self::get_headding_style_array();
			foreach ( $styles as $key => $value ) {
				$choices[ $key ] = $value['label'];
			}

			$selectors = call_user_func( $headding_selector_array );
			foreach ( $selectors as $key => $value ) {
				$wp_customize->add_setting(
					'vk_headding_desigin[' . $key . '][style]',
					array(
						'default'           => $default_option[ $key ]['style'],
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'sanitize_text_field',
					)
				);
				$wp_customize->add_control(
					'vk_headding_desigin[' . $key . '][style]',
					array(
						'label'    => $value['label'],
						'section'  => $headding_customize_section,
						'settings' => 'vk_headding_desigin[' . $key . '][style]',
						'type'     => 'select',
						'choices'  => $choices,
						'priority' => 710,
					)
				);
			}
		}

		/**
		 * Print Headding CSS
		 *
		 * @param array $selectors Selectors of Headding.
		 */
		public static function headding_css( $selectors ) {

			global $headding_default_options;
			global $headding_theme_options;

			$theme_options = get_option( $headding_theme_options );

			$options = get_option( 'vk_headding_desigin' );
			$default = call_user_func( $headding_default_options );
			$options = wp_parse_args( $options, $default );
			if ( ! is_array( $options ) ) {
				return;
			}

			$dynamic_css = '';

			// キーカラーの色情報を取得.
			if ( ! empty( $theme_options['color_key'] ) ) {
				$color_key = esc_html( $theme_options['color_key'] );
			} else {
				$color_key = '#337ab7';
			}
			// $color_key_dark = esc_html( $colorkey['color_key_dark'] );

			// 見出しデザインの配列データを取得.
			$styles = self::get_headding_style_array( $color_key );

			// 見出しデザインを何にしたいか 保存された値をループ.
			foreach ( $options as $option_key => $option_value ) {
				/*
				$option_key : 対象の見出し （ h2など ）
				$option_value['style'] : デザインの種類の識別名
				*/

				if ( ! empty( $option_value['style'] ) && 'none' !== $option_value['style'] ) {

					$selected_design = $option_value['style'];

					/*
					指定した標準セレクタ / innerセレクタ / ::before / ::after の順でループしながら CSSを構成する
					 */
					$selector_types = array( 'normal', 'inner', 'before', 'after' );
					foreach ( $selector_types as $selecter_key => $selecter_value ) {
						$count = 0;
						if ( isset( $selectors[ $option_key ]['selector'] ) && is_array( $selectors[ $option_key ]['selector'] ) ) {
							foreach ( $selectors[ $option_key ]['selector'] as $key => $value ) {
								// 最初のセレクタじゃない場合は セレクタの前に , を追加.
								if ( $count ) {
									$dynamic_css .= ',';
								}

								// 出力するCSSのセレクタ部分を文字列で生成.
								if ( 'normal' === $selecter_value ) {
									$dynamic_css .= $value;
								} elseif ( 'inner' === $selecter_value ) {
									$dynamic_css .= $value . ' a';
								} elseif ( 'before' === $selecter_value ) {
									$dynamic_css .= $value . '::before';
								} elseif ( 'after' === $selecter_value ) {
									$dynamic_css .= $value . '::after';
								}

								// 最初かどうかの識別用変数に 1 を追加.
								$count ++;
							}
							$dynamic_css .= ' { ' . $styles[ $selected_design ][ $selecter_value ] . '}';
						}
					}
				}
			}

			// delete before after space.
			$dynamic_css = trim( $dynamic_css );
			// convert tab and br to space.
			$dynamic_css = preg_replace( '/[\n\r\t]/', '', $dynamic_css );
			// Change multiple spaces to single space.
			$dynamic_css = preg_replace( '/\s(?=\s)/', '', $dynamic_css );

			return $dynamic_css;

		}

		/**
		 * Print Heqadding Front CSS
		 */
		public static function print_headding_front_css() {
			global $headding_selector_array;
			global $headding_front_hook_style;

			$selectors   = call_user_func( $headding_selector_array );
			$dynamic_css = self::headding_css( $selectors );
			if ( ! empty( $dynamic_css ) ) {
				$dynamic_css = '/* Pro Title Design */ ' . $dynamic_css;
				wp_add_inline_style( $headding_front_hook_style, $dynamic_css );
			}
		}

		/**
		 * Print Heqadding Editor CSS
		 */
		public static function print_headding_editor_css() {
			global $headding_editor_hook_style;
			$headding_selector_array = array(
				'h2' => array(
					'label'    => __( 'H2', 'heading_design_textdomain' ),
					'selector' => array(
						'.edit-post-visual-editor .editor-styles-wrapper h2',
					),
				),
				'h3' => array(
					'label'    => __( 'H3', 'heading_design_textdomain' ),
					'selector' => array(
						'.edit-post-visual-editor .editor-styles-wrapper h3',
					),
				),
				'h4' => array(
					'label'    => __( 'H4', 'heading_design_textdomain' ),
					'selector' => array(
						'.edit-post-visual-editor .editor-styles-wrapper h4',
					),
				),
				'h5' => array(
					'label'    => __( 'H5', 'heading_design_textdomain' ),
					'selector' => array(
						'.edit-post-visual-editor .editor-styles-wrapper h5',
					),
				),
				'h6' => array(
					'label'    => __( 'H6', 'heading_design_textdomain' ),
					'selector' => array(
						'.edit-post-visual-editor .editor-styles-wrapper h6',
					),
				),
			);

			$dynamic_css = self::headding_css( $headding_selector_array );
			if ( ! empty( $dynamic_css ) ) {
				$dynamic_css = '/* Pro Title Design */ ' . $dynamic_css;
				wp_add_inline_style( $headding_editor_hook_style, $dynamic_css );
			}
		}
	}
	new VK_Headding_Design();
}



