<?php
/**
 * VK Campaign Text
 *
 * @package Lightning Pro
 */

if ( ! class_exists( 'VK_Campaign_Text' ) ) {
	/**
	 * VK Campaign Text
	 */
	class VK_Campaign_Text {
		/**
		 * Constructor.
		 */
		public function __construct() {
			global $vk_campaign_text_hook_point;
			add_action( 'customize_register', array( __CLASS__, 'resister_customize' ) );
			add_action( $vk_campaign_text_hook_point, array( __CLASS__, 'display_html' ) );
			add_action( 'wp_head', array( __CLASS__, 'enqueue_style' ), 5 );
		}

        /**
		 * Default Option.
		 */
		public static function default_option() {
			$args = array(
				'display'                 => false,
				'icon'                    => '',
				'main_text_color'         => '#fff',
				'main_background_color'   => '#eab010',
				'button_text_color'       => '#4c4c4c',
				'button_background_color' => '#fff',
			);
			return $args;
		}

		/**
		 * Customizer.
		 *
		 * @param \WP_Customize_Manager $wp_customize Customizer.
		 */
		public static function resister_customize( $wp_customize ) {
			global $vk_campaign_text_prefix;
			$description = '';
			if ( class_exists( 'Vk_Font_Awesome_Versions' ) ) {
				$description = Vk_Font_Awesome_Versions::ex_and_link();
			}

			$wp_customize->add_section(
				'vk_campaign_text_setting',
				array(
					'title'    => $vk_campaign_text_prefix . __( 'Campaign Text', 'vk_campaign_text_textdomain' ),
					'priority' => 512,
				)
			);

			// Diaplay Setting.
			$wp_customize->add_setting(
				'vk_campaign_text[display]',
				array(
					'default'           => false,
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'veu_sanitize_boolean',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[display]',
				array(
					'label'    => __( 'Display Campaign Text', 'vk_campaign_text_textdomain' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[display]',
					'type'     => 'checkbox',
				)
			);

			// Icon.
			$wp_customize->add_setting(
				'vk_campaign_text[icon]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[icon]',
				array(
					'label'       => __( 'Icon', 'vk_campaign_text_textdomain' ),
					'section'     => 'vk_campaign_text_setting',
					'settings'    => 'vk_campaign_text[icon]',
					'type'        => 'text',
					'description' => __( 'To choose your favorite icon, and enter the class.', 'vk_campaign_text_textdomain' ) . '<br>' . $description,
				)
			);

			// Main Text.
			$wp_customize->add_setting(
				'vk_campaign_text[main_text]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[main_text]',
				array(
					'label'    => __( 'Main Text', 'vk_campaign_text_textdomain' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[main_text]',
					'type'     => 'text',
				)
			);

			// Main Text Color.
			$wp_customize->add_setting(
				'vk_campaign_text[main_text_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[main_text_color]',
					array(
						'label'    => __( 'Main Text Color', 'vk_campaign_text_textdomain' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[main_text_color]',
					)
				)
			);

			// Main Background Color.
			$wp_customize->add_setting(
				'vk_campaign_text[main_background_color]',
				array(
					'default'           => '#eab010',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[main_background_color]',
					array(
						'label'    => __( 'Main Background Color', 'vk_campaign_text_textdomain' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[main_background_color]',
					)
				)
			);

			// Button Text.
			$wp_customize->add_setting(
				'vk_campaign_text[button_text]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[button_text]',
				array(
					'label'    => __( 'Button Text', 'vk_campaign_text_textdomain' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[button_text]',
					'type'     => 'text',
				)
			);

			// Button Text Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_text_color]',
				array(
					'default'           => '#4c4c4c',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_text_color]',
					array(
						'label'    => __( 'Button Text Color', 'vk_campaign_text_textdomain' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_text_color]',
					)
				)
			);

			// Button Background Color.
			$wp_customize->add_setting(
				'vk_campaign_text[button_background_color]',
				array(
					'default'           => '#fff',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_hex_color',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'vk_campaign_text[button_background_color]',
					array(
						'label'    => __( 'Button Background Color', 'vk_campaign_text_textdomain' ),
						'section'  => 'vk_campaign_text_setting',
						'settings' => 'vk_campaign_text[button_background_color]',
					)
				)
			);

			// Button URL.
			$wp_customize->add_setting(
				'vk_campaign_text[button_url]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);

			$wp_customize->add_control(
				'vk_campaign_text[button_url]',
				array(
					'label'    => __( 'Button URL', 'vk_campaign_text_textdomain' ),
					'section'  => 'vk_campaign_text_setting',
					'settings' => 'vk_campaign_text[button_url]',
					'type'     => 'text',
				)
			);
		}

			/**
			 * Enqueue Style.
			 */
		public static function enqueue_style() {
            $options = get_option( 'vk_campaign_text' );
			$default = self::default_option();
            $options = wp_parse_args( $options, $default );

			$main_text_color   = isset( $options['main_text_color'] ) ? $options['main_text_color'] : '#ffffff';
			$main_bg_color     = isset( $options['main_background_color'] ) ? $options['main_background_color'] : '#eab010';
			$button_text_color = isset( $options['button_text_color'] ) ? $options['button_text_color'] : '#4c4c4c';
			$button_bg_color   = isset( $options['button_background_color'] ) ? $options['button_background_color'] : '#ffffff';

			$dynamic_css  = '.vk-campaign-text{';
			$dynamic_css .= 'background:' . $main_bg_color . ';';
			$dynamic_css .= 'color:' . $main_text_color . ';';
			$dynamic_css .= '}';
			$dynamic_css .= '.vk-campaign-text_btn{';
			$dynamic_css .= 'background:' . $button_bg_color . ';';
			$dynamic_css .= 'color:' . $button_text_color . ';';
			$dynamic_css .= '}';
			wp_add_inline_style( 'lightning-design-style', $dynamic_css );
		}

			/**
			 * Display HTML.
			 */
		public static function display_html() {
			$campaign_html = '';
			$options       = get_option( 'vk_campaign_text' );
			$default       = self::default_option();
			$options       = wp_parse_args( $options, $default );
			if ( isset( $options['display'] ) && true === $options['display'] ) {
				$icon        = isset( $options['icon'] ) ? '<i class="' . $options['icon'] . '"></i>' : '';
				$main_text   = isset( $options['main_text'] ) ? $options['main_text'] : '';
				$button_text = isset( $options['button_text'] ) ? $options['button_text'] : '';
				$button_url  = isset( $options['button_url'] ) ? $options['button_url'] : '';

				$campaign_html .= '<div class="vk-campaign-text">';
				if ( empty( $button_text ) ) {
					$campaign_html .= '<a class="vk-campaign-text-link" href="' . $button_url . '"><span>' . $icon . $main_text . '</span></a>';
				} else {
					$campaign_html .= '<span>' . $icon . $main_text . '</span>';
					$campaign_html .= '<a class="vk-campaign-text_btn" href="' . $button_url . '">' . $button_text . '</a>';
				}
				$campaign_html .= '</div>';
			}
			echo $campaign_html;
		}
	}
	new VK_Campaign_Text();
}

