<?php
add_action( 'customize_register', 'lightning_header_top_customize_register' );
function lightning_header_top_customize_register( $wp_customize ) {
	global $vk_header_top_textdomain;
	/*-------------------------------------------*/
	/*	Design setting
	/*-------------------------------------------*/
	$wp_customize->add_section( 'lightning_header_top', array(
		'title'				=> __('Lightning Header top settings', $vk_header_top_textdomain  ),
		'priority'			=> 450,
	) );
	$veu_options = get_option( 'vkExUnit_contact' );
	$default_btn_txt = ( isset( $veu_options['short_text'] ) && $veu_options['short_text'] ) ? $veu_options['short_text'] : __( 'CONTACT', 'ligthning' );
	$default_btn_url = ( isset( $veu_options['contact_link'] ) && $veu_options['contact_link'] ) ? esc_url( $veu_options['contact_link'] ) : '';
	$default_tel_number = ( isset( $veu_options['tel_number'] ) && $veu_options['tel_number'] ) ? esc_url( $veu_options['tel_number'] ) : '';

	$wp_customize->add_setting( 'lightning_theme_options[header_top_contact_txt]',	array(
		'default' 			=> $default_btn_txt,
		'type'				=> 'option',
		'capability' 		=> 'edit_theme_options',
		'transport'   		=> 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
		) );
	$wp_customize->add_setting( 'lightning_theme_options[header_top_contact_url]',	array(
		'default' 			=> $default_btn_url,
		'type'				=> 'option',
		'capability' 		=> 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		) );
	$wp_customize->add_setting( 'lightning_theme_options[header_top_tel_number]',	array(
		'default' 			=> $default_tel_number,
		'type'				=> 'option',
		'capability' 		=> 'edit_theme_options',
		'transport'   		=> 'postMessage',
		'sanitize_callback' => 'sanitize_text_field',
		) );

   class Custom_Text_Control_a extends WP_Customize_Control {
		public $type = 'customtext';
		public $description = ''; // we add this for the extra description
		public function render_content() {
		?>
		<label>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
			<span><?php echo esc_html( $this->description ); ?></span>
		</label>
		<?php
		}
	}
	$wp_customize->add_control( 'header_top_contact_txt', array(
		'label'     => __('Contact button text', $vk_header_top_textdomain ),
		'section'  => 'lightning_header_top',
		'settings' => 'lightning_theme_options[header_top_contact_txt]',
		'type' => 'text',
		'priority' => 10,
		) );
	$wp_customize->add_control( new Custom_Text_Control_a( $wp_customize, 'header_top_contact_url', array( 
		'label'     => __('Contact button link url', $vk_header_top_textdomain ),
		'section'  => 'lightning_header_top',
		'settings' => 'lightning_theme_options[header_top_contact_url]',
		'type' => 'text',
		'priority' => 11,
		'description' => __('Ex : http:www.aaa.com/contact/', $vk_header_top_textdomain ),
		) ) );
	$wp_customize->add_control( 'header_top_tel_number', array(
		'label'     => __('Contact Tel Number', $vk_header_top_textdomain ),
		'section'  => 'lightning_header_top',
		'settings' => 'lightning_theme_options[header_top_tel_number]',
		'type' => 'text',
		'priority' => 12,
		) );
}