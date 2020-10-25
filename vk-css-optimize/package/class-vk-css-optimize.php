<?php
/*
The original of this file is located at:
https://github.com/vektor-inc/vektor-wp-libraries
If you want to change this file, please change the original file.
*/

/**
 * VK CSS Optimize
 *
 */

/**
 * VK CSS Tree Shaking Class
 */
if ( ! class_exists( 'VK_CSS_Optimize' ) ) {
	class VK_CSS_Optimize {

		public function __construct() {
			add_action( 'get_header', array( __CLASS__, 'get_html_start' ), 2147483647 );
			add_action( 'shutdown', array( __CLASS__, 'get_html_end' ), 0 );
			add_action( 'customize_register', array( __CLASS__, 'customize_register' ) );
			if ( VK_CSS_Optimize::is_preload() ){
				add_filter( 'style_loader_tag', array( __CLASS__, 'css_preload' ), 10, 4 );
			}
		}

		public static function customize_register( $wp_customize ){
			global $prefix_customize_panel;
			$wp_customize->add_section(
				'css_optimize', array(
					'title'    => $prefix_customize_panel . __( 'CSS Optimize ( Speed up ) Settings', 'css_optimize_textdomain' ),
					'priority' => 450,
				)
			);
		
			// Tree shaking
			////////////////////////////////////////////////////////////////

			$wp_customize->add_setting(
				'tree_shaking_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'tree_shaking_title',
					array(
						'label'            => __( 'Tree shaking', 'css_optimize_textdomain' ),
						'section'          => 'css_optimize',
						'type'             => 'text',
						'custom_title_sub' => '',
						// 'custom_html'      => __( 'Move part of CSS and JS to the footer to improve display speed.', 'css_optimize_textdomain' ),
					)
				)
			);
		
			$wp_customize->add_setting(
				'vk_css_optimize_options[tree_shaking]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => array( 'VK_Helpers', 'sanitize_choice' ),
				)
			);
			$wp_customize->add_control(
				'vk_css_optimize_options[tree_shaking]',
				array(
					'label'    => __( 'Active tree shaking (Beta)', 'css_optimize_textdomain' ),
					'section'  => 'css_optimize',
					'settings' => 'vk_css_optimize_options[tree_shaking]',
					'type'     => 'select',
					'choices'  => array(
						''			=> __( 'Nothing to do', 'css_optimize_textdomain' ),
						'active'	=> __( 'Active Tree shaking', 'css_optimize_textdomain' ),
					),
				)
			);
		
			$wp_customize->add_setting(
				'vk_css_optimize_options[tree_shaking_class_exclude]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_css_optimize_options[tree_shaking_class_exclude]',
				array(
					'label'       => __( 'Exclude class of tree shaking', 'css_optimize_textdomain' ),
					'section'     => 'css_optimize',
					'settings'    => 'vk_css_optimize_options[tree_shaking_class_exclude]',
					'type'        => 'textarea',
					'description' => __( 'If you choose "Optimize All CSS" that delete the useless css.If you using active css class that please fill in class name. Ex) btn-active,slide-active,scrolled', 'css_optimize_textdomain' ),
				)
			);

			// Preload
			////////////////////////////////////////////////////////////////
			$wp_customize->add_setting(
				'css_preload_title',
				array(
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				new Custom_Html_Control(
					$wp_customize,
					'css_preload_title',
					array(
						'label'            => __( 'CSS Preload', 'css_optimize_textdomain' ),
						'section'          => 'css_optimize',
						'type'             => 'text',
						'custom_title_sub' => '',
						// 'custom_html'      => __( 'Move part of CSS and JS to the footer to improve display speed.', 'css_optimize_textdomain' ),
					)
				)
			);

			$wp_customize->add_setting(
				'vk_css_optimize_options[preload]',
				array(
					'default'           => '',
					'type'              => 'option',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'vk_css_optimize_options[preload]',
				array(
					'label'       => __( 'Active preload', 'css_optimize_textdomain' ),
					'section'     => 'css_optimize',
					'settings'    => 'vk_css_optimize_options[preload]',
					'type'        => 'select',
					'choices'  => array(
						''   	 	=> __( 'Nothing to do', 'css_optimize_textdomain' ),
						'active'    => __( 'All preload css', 'css_optimize_textdomain' ),
					),
				)
			);

		}

		public static function get_css_optimize_options(){

			$vk_css_optimize_options = get_option( 'vk_css_optimize_options' );

			// fall back function
			// Actualy other array exist but optimize_css is most important
			if ( ! isset( $vk_css_optimize_options['optimize_css'] ) ) {

				$theme_textdomain = wp_get_theme()->get( 'TextDomain' );

				if ( 'lightning' === $theme_textdomain || 'lightning-pro' === $theme_textdomain ){
					$options = get_option( 'lightning_theme_options' );
				} else if ( 'katawara' === $theme_textdomain ){
					$options = get_option( 'katawara_theme_options' );
				} else {
					$options = get_option( 'vk_blocks_options' );
				}
				if ( isset( $options['optimize_css'] ) ){
					$vk_css_optimize_options['optimize_css'] = $options['optimize_css'];
				}
				if ( isset( $options['tree_shaking_class_exclude'] ) ){
					$vk_css_optimize_options['tree_shaking_class_exclude'] = $options['tree_shaking_class_exclude'];
				}
				update_option( 'vk_css_optimize_options', $vk_css_optimize_options );
			}

			return $vk_css_optimize_options;
		}


		public static function is_preload(){
			$options = VK_CSS_Optimize::get_css_optimize_options();
			if ( ! empty( $options['preload'] ) && 'preload-all' === $options['preload'] ) {
				return true;
			}
		}

		public static function get_html_start() {
			ob_start( 'VK_CSS_Optimize::css_optimize' );
		}

		public static function get_html_end() {
			if ( ob_get_length() ){
				ob_end_flush();
			}
		}

		public static function css_optimize( $buffer ) {

			$options = VK_CSS_Optimize::get_css_optimize_options();

			// CSS Tree Shaking.
			require_once dirname( __FILE__ ) . '/class-css-tree-shaking.php';
			global $vk_css_tree_shaking_array;
			foreach ( $vk_css_tree_shaking_array as $vk_css_array ) {
				$options['ssl']['verify_peer']      = false;
				$options['ssl']['verify_peer_name'] = false;

				require_once(ABSPATH.'wp-admin/includes/file.php');
				$path_name = $vk_css_array['path'];
				if( WP_Filesystem() ){
					global $wp_filesystem;
					$css = $wp_filesystem->get_contents($path_name);
				}

				$css                                = celtislab\CSS_tree_shaking::extended_minify( $css, $buffer );
				$buffer                             = str_replace(
					'<link rel=\'stylesheet\' id=\'' . $vk_css_array['id'] . '-css\'  href=\'' . $vk_css_array['url'] . '?ver=' . $vk_css_array['version'] . '\' type=\'text/css\' media=\'all\' />',
					'<style id=\'' . $vk_css_array['id'] . '-css\' type=\'text/css\'>' . $css . '</style>',
					$buffer
				);
				$buffer                             = str_replace(
					'<link rel=\'stylesheet\' id=\'' . $vk_css_array['id'] . '-css\'  href=\'' . $vk_css_array['url'] . '\' type=\'text/css\' media=\'all\' />',
					'<style id=\'' . $vk_css_array['id'] . '-css\' type=\'text/css\'>' . $css . '</style>',
					$buffer
				);

			}

			return $buffer;
		}

		public static function css_preload( $tag, $handle, $href, $media ) {
			$tag = "<link rel='preload' id='".$handle."-css' href='".$href."' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"/>\n";
			$tag .= "<link rel='stylesheet' id='".$handle."-css' href='".$href."' media='print' onload=\"this.media='all'; this.onload=null;\">\n";
			return $tag;
		}
		
	}
	new VK_CSS_Optimize();
}
