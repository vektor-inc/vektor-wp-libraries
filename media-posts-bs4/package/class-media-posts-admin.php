<?php
/*-------------------------------------------*/
/*	カスタマイズ画面で標準レイアウトを選択された時に不要入力欄を非表示にする
/*-------------------------------------------*/
add_action( 'customize_register', 'lmu_add_customize_class_bs4', 1 );
function lmu_add_customize_class_bs4( $wp_customize ) {
	class Lightning_Default_Layout_Form_Hider extends WP_Customize_Control {
		public function render_content() {

			// アーカイブページのある投稿タイプを取得
			$post_types           = array( 'post' => 0 );
			$post_types           = Lightning_Media_Posts_BS4::get_custom_types() + $post_types;
			$post_types['author'] = 'author';

			// ページを開いた状態のoption値を取得
			$option = get_option( 'vk_post_type_archive' );

			// 投稿タイプでループ
			foreach ( $post_types as $post_type => $value ) {
				?>
				<script type="text/javascript">
				;
				(function($) {
				// 投稿タイプslugのついたセレクタ
				var target_select = '#customize-control-vk_post_type_archive-<?php echo $post_type; ?>-layout select';

				// 非表示にする項目
				var control_targets = ['-col_xs', '-col_sm', '-col_md', '-col_lg', '-col_xl', '-display_item_title', '-display_image','-display_image_overlay_term', '-display_excerpt', '-display_date', '-display_new', '-display_btn','-new_mark_title', '-new_date', '-new_text','-btn_setting_title','-btn_text','-btn_align'];

				<?php
				// ページを開いた時点で 標準 が選択してあったら 不要項目を非表示に
				if ( empty( $option[ $post_type ]['layout'] ) || $option[ $post_type ]['layout'] == 'default' ) {
				?>
				$(document).ready(function() {
					control_targets.forEach(function( value ) {
						// デフォルトの場合に設定項目を非表示に
							 $('#customize-control-vk_post_type_archive-<?php echo $post_type; ?>' + value).css({"display":"none"});
					}); // control_targets.forEach(function( value ) {
				});
				<?php } ?>

				// レイアウトが変更された時
				$(target_select).change(function() {
					var val = $(this).val();
					control_targets.forEach(function( value ) {

						// レイアウト選択が 標準 になったら
						 if ( val === 'default' ){
							 // 不要項目を消す
							 $('#customize-control-vk_post_type_archive-<?php echo $post_type; ?>' + value).css({"display":"none"});

						// レイアウト選択が 標準 以外の時
						 } else {
							 $('#customize-control-vk_post_type_archive-<?php echo $post_type; ?>' + value).css({"display":"list-item"});
						 }
					}); // control_targets.forEach(function( value ) {
				});
				})(jQuery);
				</script>
			<?php
			} // foreach ( $post_types as $post_type => $value ) {
		} // public function render_content() {
	} // class Lightning_Default_Layout_form_hide extends WP_Customize_Control {
}


class Lightning_Media_Admin_BS4 {

	// controls post types that can be displayed with grid layout on front-end (archives list pages)
	private static $post_types        = array( 'post' => 0 );
	private static $post_types_labels = array();

	public static function init() {

		// gets custom post types too
		self::$post_types = Lightning_Media_Posts_BS4::get_custom_types() + self::$post_types;

		// all labels
		self::$post_types_labels = Lightning_Media_Posts_BS4::labelNames() + Lightning_Media_Posts_BS4::get_custom_types_labels();

		add_action( 'customize_register', array( __CLASS__, 'archive_layout_customize_register' ) );

	}

	public static function archive_layout_customize_register( $wp_customize ) {

		global $customize_section_name;
		global $system_name;

		$wp_customize->add_panel(
			'vk_post_type_archive_setting', array(
				'priority'       => 800,
				'capability'     => 'edit_theme_options',
				'theme_supports' => '',
				'title'          => $customize_section_name . __( 'Archive Page Setting', 'lightning-pro' ),
			)
		);

		$post_types           = array( 'post' => 0 );
		$post_types           = Lightning_Media_Posts_BS4::get_custom_types() + $post_types;
		$post_types['author'] = 'author';

		$post_types_labels           = Lightning_Media_Posts_BS4::labelNames() + Lightning_Media_Posts_BS4::get_custom_types_labels();
		$post_types_labels['author'] = __( 'Author', 'lightning-pro' );

		$patterns['default']['label'] = $system_name . ' ' . __( 'default', 'lightning-pro' );
		$patterns                     = $patterns + Lightning_Media_Posts_BS4::patterns();
		foreach ( $patterns as $key => $value ) {
			$layouts[ $key ] = $value['label'];
		}

		// Works Unit のカスタム投稿タイプがある場合は除外する
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'lightning-works-unit/lightning-works-unit.php' ) ) {
			$works_unit = get_option( 'lightning_works_unit' );
			if ( ! empty( $works_unit['post_name'] ) ) {
				$works_unit_slug = $works_unit['post_name'];
				unset( $post_types[ $works_unit_slug ] );
			}
		}

		// Get default option
		$customize_options_default = Lightning_Media_Posts_BS4::options_default_post_type();

		foreach ( $post_types as $type => $value ) {

			$post_type_label = $post_types_labels[ $type ];

			$wp_customize->add_section(
				'vk_post_type_archive_setting_' . $type, array(
					'title' => $post_type_label,
					'panel' => 'vk_post_type_archive_setting',
				)
			);

			/*-------------------------------------------*/
			/*	Display conditions
			/*-------------------------------------------*/
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][display_conditions_title]', array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize, 'vk_post_type_archive[' . $type . '][display_conditions_title]', array(
						'label'            => __( 'Display conditions', 'lightning-pro' ) . ' [ ' . $post_type_label . ' ]',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => __( 'Number of posts per page', 'lightning-pro' ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][count]', array(
					'default'           => $customize_options_default['count'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'lightning_sanitize_number',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][count]', array(
					'label'    => '',
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][count]',
					'type'     => 'number',
				)
			);

			/*-------------------------------------------*/
			/*	Layout
			/*-------------------------------------------*/

			$wp_customize->add_setting(
				'post_type_title_' . $type, array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize, 'post_type_title_' . $type, array(
						'label'            => sprintf( __( 'Display type and columns [ %s ]', 'lightning-pro' ), $post_type_label ),
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->selective_refresh->add_partial(
				'vk_post_type_archive[' . $type . '][layout]', array(
					'selector'        => '.mainSection .vk_posts-postType-' . $type,
					'render_callback' => '',
				)
			);
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][layout]', array(
					'default'           => $customize_options_default['layout'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][layout]', array(
					'label'    => __( 'Display type', 'lightning-pro' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][layout]',
					'type'     => 'select',
					'choices'  => $layouts,
				)
			);

			$wp_customize->add_setting(
				'layout_form_control_' . $type, array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Lightning_Default_Layout_Form_Hider(
					$wp_customize, 'layout_form_control_' . $type, array(
						'label'   => '',
						'section' => 'vk_post_type_archive_setting_' . $type,
					)
				)
			);

			/*
			/*	Columns
			/*-------------------------------------------*/

			$sizes = array(
				'xs' => array( 'label' => __( 'Extra small', 'lightning-pro' ) ),
				'sm' => array( 'label' => __( 'Small', 'lightning-pro' ) ),
				'md' => array( 'label' => __( 'Medium', 'lightning-pro' ) ),
				'lg' => array( 'label' => __( 'Large', 'lightning-pro' ) ),
				'xl' => array( 'label' => __( 'Extra large', 'lightning-pro' ) ),
			);

			foreach ( $sizes as $key => $value ) {
				$wp_customize->add_setting(
					'vk_post_type_archive[' . $type . '][col_' . $key . ']', array(
						'default'           => $customize_options_default[ 'col_' . $key ],
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'lightning_sanitize_number',
					)
				);
				$wp_customize->add_control(
					'vk_post_type_archive[' . $type . '][col_' . $key . ']', array(
						'label'    => sprintf( __( 'Column ( Screen size : %s )', 'lightning-pro' ), $value['label'] ),
						'section'  => 'vk_post_type_archive_setting_' . $type,
						'settings' => 'vk_post_type_archive[' . $type . '][col_' . $key . ']',
						'type'     => 'number',
					)
				);
			}

			/*-------------------------------------------*/
			/*	Display item
			/*-------------------------------------------*/
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][display_item_title]', array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize, 'vk_post_type_archive[' . $type . '][display_item_title]', array(
						'label'            => sprintf( __( 'Display item [ %s ]', 'lightning-pro' ), $post_type_label ),
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => '',
						'custom_html'      => '',
					)
				)
			);

			$items = array(
				'display_image'              => array(
					'label'   => __( 'Image', 'lightning-pro' ),
					'default' => true,
				),
				'display_image_overlay_term' => array(
					'label' => __( 'Term name', 'lightning-pro' ),
					// 'default' => true,
				),
				'display_excerpt'            => array(
					'label' => __( 'Excerpt', 'lightning-pro' ),
					// 'default' => false,
				),
				'display_date'               => array(
					'label' => __( 'Date', 'lightning-pro' ),
					// 'default' => true,
				),
				'display_new'                => array(
					'label' => __( 'New mark', 'lightning-pro' ),
					// 'default' => true,
				),
				'display_btn'                => array(
					'label' => __( 'Button', 'lightning-pro' ),
					// 'default' => false,
				),
			);
			foreach ( $items as $key => $value ) {
				$wp_customize->add_setting(
					'vk_post_type_archive[' . $type . '][' . $key . ']', array(
						'default'           => $customize_options_default[ $key ],
						'type'              => 'option',
						'capability'        => 'edit_theme_options',
						'sanitize_callback' => 'lightning_sanitize_checkbox',
					)
				);
				$wp_customize->add_control(
					'vk_post_type_archive[' . $type . '][' . $key . ']', array(
						'label'    => $value['label'],
						'section'  => 'vk_post_type_archive_setting_' . $type,
						'settings' => 'vk_post_type_archive[' . $type . '][' . $key . ']',
						'type'     => 'checkbox',
					)
				);
			}

			/*-------------------------------------------*/
			/*	new mark setting
			/*-------------------------------------------*/
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_mark_title]', array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize, 'vk_post_type_archive[' . $type . '][new_mark_title]', array(
						'label'            => '',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => sprintf( __( 'New mark option [ %s ]', 'lightning-pro' ), $post_type_label ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_date]', array(
					'default'           => $customize_options_default['new_date'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'lightning_sanitize_number',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][new_date]', array(
					'label'    => __( 'Number of days to display the new post mark', 'lightning-pro' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][new_date]',
					'type'     => 'text',
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][new_text]', array(
					'default'           => $customize_options_default['new_text'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][new_text]', array(
					'label'    => __( 'New mark text', 'lightning-pro' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][new_text]',
					'type'     => 'text',
				)
			);

			/*-------------------------------------------*/
			/*	new mark setting
			/*-------------------------------------------*/
			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_setting_title]', array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize, 'vk_post_type_archive[' . $type . '][btn_setting_title]', array(
						'label'            => '',
						'section'          => 'vk_post_type_archive_setting_' . $type,
						'type'             => 'text',
						'custom_title_sub' => sprintf( __( 'Button option [ %s ]', 'lightning-pro' ), $post_type_label ),
						'custom_html'      => '',
					)
				)
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_text]', array(
					'default'           => $customize_options_default['btn_text'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][btn_text]', array(
					'label'    => __( 'Button text', 'lightning-pro' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][btn_text]',
					'type'     => 'text',
				)
			);

			$text_aligns = array(
				'text-left'   => __( 'Left', 'lightning-pro' ),
				'text-center' => __( 'Center', 'lightning-pro' ),
				'text-right'  => __( 'Right', 'lightning-pro' ),
			);

			$wp_customize->add_setting(
				'vk_post_type_archive[' . $type . '][btn_align]', array(
					'default'           => $customize_options_default['btn_align'],
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_post_type_archive[' . $type . '][btn_align]', array(
					'label'    => __( 'Text align', 'lightning-pro' ),
					'section'  => 'vk_post_type_archive_setting_' . $type,
					'settings' => 'vk_post_type_archive[' . $type . '][btn_align]',
					'type'     => 'select',
					'choices'  => $text_aligns,
				)
			);

		} // foreach ( $post_types as $type => $value ) {

		return $wp_customize;
	}

}
Lightning_Media_Admin_BS4::init();
