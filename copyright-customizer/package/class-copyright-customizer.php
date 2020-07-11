<?php
/*
このファイルの元ファイルは
https://github.com/vektor-inc/vektor-wp-libraries
にあります。修正の際は上記リポジトリのデータを修正してください。
*/

class Lightning_Copyright_Custom {

	private static $instance;

	private function __construct() {

	}

	public static function instance() {
		if ( isset( self::$instance ) ) {
			return self::$instance;
		}

		self::$instance = new Lightning_Copyright_Custom;
		self::$instance->run_init();

		return self::$instance;
	}

	protected function run_init() {
		add_filter( 'lightning_footerCopyRightCustom', array( $this, 'footerCopyRightCustom' ), 499, 1 );
		add_filter( 'lightning_footerPoweredCustom', array( $this, 'footerPoweredCustom' ), 499, 1 );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'wp_head', array( $this, 'admin_css' ), 10, 2 );
	}

	public function admin_css() {
		if ( is_customize_preview() ) {
				?>
				<style type="text/css">
				.copySection .customize-partial-edit-shortcut button { top:-30px; }
				</style>
				<?php
		}
	}
	public function customize_register( $wp_customize ) {
		global $vk_copyright_customizer_prefix;
		global $vk_copyright_customizer_priority;
		if ( ! $vk_copyright_customizer_priority ){
			$vk_copyright_customizer_priority = 900;
		}
		$wp_customize->add_section(
			'lightning_copyright_section', array(
				'title'    => $vk_copyright_customizer_prefix . __( 'Copyright Setting', 'lightning-pro' ),
				'priority' => $vk_copyright_customizer_priority,
			)
		);

		$add_setting_array = array(
			'default'           => self::get_option(),
			'type'              => 'option',
			'capability'        => 'edit_theme_options', // 操作権限
			'sanitize_callback' => array( $this, 'sanitize_callback' ),
		);

		$wp_customize->add_setting( 'lightning_copyright', $add_setting_array );

		$wp_customize->add_control(
			'lightning_copyright',
			array(
				'label'       => $vk_copyright_customizer_prefix . __( 'Copyright Setting', 'lightning_footerPowerCustom' ),
				'section'     => 'lightning_copyright_section',
				'settings'    => 'lightning_copyright',
				'type'        => 'textarea',
				'priority'    => 21,
				'description' => __( 'Please fill box to footer html text you want.', 'lightning_footerPowerCustom' )
								 . '<br/>'
								 . __( 'If you fill noting, Footer will display noting.', 'lightning_footerPowerCustom' ),
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'lightning_copyright', array(
				'selector'        => '.copySection',
				'render_callback' => '',
			)
		);

	}

	public static function get_option() {
		$default_copyright = 'Copyright &copy; ' . get_bloginfo( 'name' ) . ' All Rights Reserved.';
		return get_option( 'lightning_copyright', $default_copyright );
	}

	public function footerCopyRightCustom( $lightning_footerCopyRight ) {
		$lightning_copyright = self::get_option();
		if ( $lightning_copyright === null ) {
			$text = $lightning_footerCopyRight;
		} elseif ( $lightning_copyright ) {
			$text = '<p>' . wp_kses_post( self::get_option() ) . '</p>';
		} else {
			$text = '';
		}
		return $text;
	}

	public function footerPoweredCustom( $powerd ) {
		$lightning_copyright = self::get_option();
		if ( $lightning_copyright === null ) {
			$text = $powerd;
		} else {
			$text = '';
		}
		return $text;
	}

	public function sanitize_callback( $option ) {
		$option = stripslashes( $option );

		return $option;
	}

}

Lightning_Copyright_Custom::instance();
